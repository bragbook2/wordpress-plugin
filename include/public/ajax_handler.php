<?php
namespace mvpbrag;

class Ajax_Handler
{

    public $seoData;
    public function __construct()
    {

        add_action('admin_menu', [$this, 'form_entry_menu_page']);
        add_action('wp_ajax_handle_form_submission', [$this, 'handle_form_submission']);
        add_action('wp_ajax_nopriv_handle_form_submission', [$this, 'handle_form_submission']);

        add_action('wp_ajax_consultation-pagination-load-posts', [$this, 'bb_consultation_pagination_load_posts']);

        add_action('wp_ajax_nopriv_consultation-pagination-load-posts', [$this, 'bb_consultation_pagination_load_posts']);
        add_action('wp_ajax_bragbook_my_favorite', [$this, 'bragbook_my_favorite_handler']);
        add_action('wp_ajax_nopriv_bragbook_my_favorite', [$this, 'bragbook_my_favorite_handler']);
        add_action('wp_ajax_bb_save_bragbook_settings', [$this, 'bb_save_bragbook_settings']);
        add_action('wp_ajax_nopriv_bb_save_bragbook_settings', [$this, 'bb_save_bragbook_settings']);
        add_action('wp_ajax_bb_setting_remove_row', [$this, 'bb_setting_remove_row']);

        add_action('wp_ajax_bb_update_api', [$this, 'bb_update_api']);
        add_action('wp_ajax_nopriv_bb_update_api', [$this, 'bb_update_api']);

        add_action('save_post', [$this, 'bb_update_slugs']);
        add_action('wp', [$this, 'bb_seo']);
        $this->seoData = $this->get_custom_bragbook_title_and_description();

        add_action('wp_trash_post', [$this, 'bb_delete_default_page']);
        add_action('wp_footer', [$this, 'bb_footer_gallery_modal']);
        add_action('delete_post', [$this, 'bb_permanent_delete_empty_trash']);
        add_action('wp_ajax_load_more_procedures', [$this, 'load_more_procedures']);
        add_action('wp_ajax_nopriv_load_more_procedures', [$this, 'load_more_procedures']);
        add_action('wp_ajax_bb_fetch_favorite_data', [$this, 'bb_fetch_favorite_data']);
        add_action('wp_ajax_nopriv_bb_fetch_favorite_data', [$this, 'bb_fetch_favorite_data']);
        add_action('wp_ajax_bb_generate_pagination', [$this, 'bb_generate_pagination']);
        add_action('wp_footer', [$this, 'bb_nudity_warning']);
        add_action('wp_ajax_nopriv_bb_generate_pagination', [$this, 'bb_generate_pagination']);
        add_action('wp_ajax_bb_case_api', [$this, 'handle_bb_case_api']);
        add_action('wp_ajax_nopriv_bb_case_api', [$this, 'handle_bb_case_api']);
        add_filter('wpseo_sitemap_index', array($this, 'add_bragbook_sitemap_to_yoast'));

    }

    function handle_bb_case_api()
    {
        // Check if the necessary data is set
        if (isset($_POST['count'], $_POST['pageSlug'], $_POST['procedureId'], $_POST['apiToken'], $_POST['websitePropertyId'])) {

            get_option('combine_gallery_page_id');
            // Retrieve data sent from the JavaScript
            $count = sanitize_text_field($_POST['count']);
            $apiToken = sanitize_text_field($_POST['apiToken']);
            $websitePropertyId = sanitize_text_field($_POST['websitePropertyId']);
            $procedureId = sanitize_text_field($_POST['procedureId']);
            $caseId = sanitize_text_field($_POST['caseId']);
            $seoSuffixUrl = sanitize_text_field($_POST['seoSuffixUrl']);
            // $bb_set_transient_urls = get_option( 'bb_set_transient_url_sidebar', [] );
            $page_slug = sanitize_text_field($_POST['pageSlug']);
            $staticFilter = sanitize_text_field($_POST['staticFilter']);
            $dynamicFilter = sanitize_text_field($_POST['dynamicFilter']);
            $combine_gallery_page_slug = get_option('combine_gallery_slug');

            // }
            // if ( ! is_array( $bb_set_transient_urls ) ) {
            //     $bb_set_transient_urls = [];
            // } 
            
            $filter_get = '';
            //if(!empty($staticFilter) || !empty($dynamicFilter)){ 
            if ($page_slug == $combine_gallery_page_slug) {
                $url = BB_BASE_URL . "/api/plugin/combine/filters";
                $response = wp_remote_post($url, array(
                    'method' => 'POST',
                    'body' => json_encode(array(
                        'apiTokens' => explode(", ", $apiToken),
                        'procedureIds' => array_map('intval', explode(", ", $procedureId)),
                        'websitePropertyIds' => array_map('intval', explode(", ", $websitePropertyId)),
                    )),
                    'headers' => array(
                        'Content-Type' => 'application/json',
                    ),
                ));

                if (is_wp_error($response)) {
                    wp_send_json_error(array('message' => $response->get_error_message()));
                }

                $filter_get = wp_remote_retrieve_body($response);
            } else {
                $filter_api = BB_BASE_URL . "/api/plugin/filters?apiToken={$apiToken}&procedureId={$procedureId}&websitePropertyId={$websitePropertyId}";
                if (get_transient($filter_api) == false) {
                    $filter_get = self::case_and_filter_api($filter_api);
                } else {
                    $filter_get = get_transient($filter_api);
                }
            }

            // }
           //var_dump($caseId);
            if ($caseId !== "" || $seoSuffixUrl!== "") {
                if ($page_slug == $combine_gallery_page_slug) {
                    $caseId=$caseId?$caseId:'123';
                    $url = BB_BASE_URL . "/api/plugin/combine/cases/$caseId?seoSuffixUrl=$seoSuffixUrl";
                    $response = wp_remote_post($url, array(
                        'method' => 'POST',
                        'body' => json_encode(array(
                            'apiTokens' => explode(", ", $apiToken),
                            'procedureIds' => array_map('intval', explode(", ", $procedureId)),
                            'websitePropertyIds' => array_map('intval', explode(", ", $websitePropertyId)),
                        )),
                        'headers' => array(
                            'Content-Type' => 'application/json',
                        ),
                    ));

                    if (is_wp_error($response)) {
                        wp_send_json_error(array('message' => $response->get_error_message()));
                    }

                    $data = wp_remote_retrieve_body($response);

                } else {
                    $url = BB_BASE_URL . "/api/plugin/cases?websitepropertyId={$websitePropertyId}&apiToken={$apiToken}&caseId={$caseId}&seoSuffixUrl={$seoSuffixUrl}&procedureId={$procedureId}";

                    if (get_transient($url) !== false) {
                        $data = get_transient($url);
                        
                    } else {
                        $data = self::case_and_filter_api($url);
                    }
                }
              //  die('here two');

            } else {

                if ($page_slug == $combine_gallery_page_slug) {
                   
                    $dynamicFilterCombineAPIBody = [];

                    if (isset($_POST['gender']) && !empty($_POST['gender'])) {
                        $dynamicFilterCombineAPIBody['gender'] = preg_replace('/\\\"/', '', $_POST['gender']);
                    }

                    if (isset($_POST['height']) && !empty($_POST['height'])) {
                        $dynamicFilterCombineAPIBody['height'] = $_POST['height'];
                    }

                    if (isset($_POST['weight']) && !empty($_POST['weight'])) {
                        $dynamicFilterCombineAPIBody['weight'] = $_POST['weight'];
                    }

                    if (isset($_POST['ethnicity']) && !empty($_POST['ethnicity'])) {
                        $dynamicFilterCombineAPIBody['ethnicity'] = preg_replace('/\\\"/', '', $_POST['ethnicity']);
                    }

                    if (isset($_POST['age']) && !empty($_POST['age'])) {
                        $dynamicFilterCombineAPIBody['age'] = $_POST['age'];
                    }

                    if (isset($_POST['dynamicFilterCombine']) && !empty($_POST['dynamicFilterCombine'])) {
            
                        $dynamicFilterCombine = stripslashes($_POST['dynamicFilterCombine']);
            
                        $decodedFilters = json_decode($dynamicFilterCombine, true); 
                        
                        if ($decodedFilters === null) {
                        
                            echo "Invalid filter JSON format: " . $_POST['dynamicFilterCombine'];
                            exit;
                        }
                        
                        // Now assign the decoded filters to the API body
                        $dynamicFilterCombineAPIBody['filters'] = $decodedFilters;
                    }
                    
                    $dynamicFilterCombineAPIBody['apiTokens'] = explode(", ", $apiToken);
                    $dynamicFilterCombineAPIBody['count'] = (int)$count;
                    $dynamicFilterCombineAPIBody['procedureIds'] = array_map('intval', explode(", ", $procedureId));
                    $dynamicFilterCombineAPIBody['websitePropertyIds'] = array_map('intval', explode(", ", $websitePropertyId));



                    $url = BB_BASE_URL . "/api/plugin/combine/cases";

                    $response = wp_remote_post($url, array(
                        'method' => 'POST',
                        'body' => json_encode($dynamicFilterCombineAPIBody),
                        'headers' => array(
                            'Content-Type' => 'application/json',
                        ),
                    ));
                  //  die('here flow');
                    // Handle the API response
                    if (is_wp_error($response)) {
                        wp_send_json_error(array('message' => $response->get_error_message()));
                    }

                    $data = wp_remote_retrieve_body($response);
                    $data_in = $dynamicFilterCombineAPIBody;
                    
                } else {
                    $url = BB_BASE_URL . "/api/plugin/cases/paginate?websitePropertyId={$websitePropertyId}&count={$count}&apiToken={$apiToken}&procedureId={$procedureId}{$staticFilter}{$dynamicFilter}";
                    if (get_transient($url) !== false) {
                        $data = get_transient($url);
                    } else {
                        $data = self::case_and_filter_api($url);
                    }
                }

            }

            $case_fav = [];
            $sidebar_list = '';
            // if (isset($_COOKIE['wordpress_favorite_email'])) {
                $cookieValue = $_COOKIE['wordpress_favorite_email'];
                $decodedValue = urldecode($cookieValue);
                $favorite_email_id = htmlspecialchars($decodedValue);

                update_option('bragbook_favorite_email', $favorite_email_id);
                $api_tokens = get_option('bragbook_api_token', []);
                
                $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
                $gallery_slugs = get_option('bb_gallery_page_slug', []);
                $seo_pages_title = get_option('bb_seo_page_title', []);
                $seo_pages_description = get_option('bb_seo_page_description', []);
               
                $favorite_data_bb = [];
                $tc = 0;
                $fav_token = [];
                $web_id = [];
                foreach ($api_tokens as $index => $apiToken) {
                    $fav_token[] = $apiToken;
                    $tc++;
                    $websitePropertyId = $websiteproperty_ids[$index] ?? '';
                    $web_id[] = (int)$websitePropertyId;
                    $page_slug_bb = $gallery_slugs[$index] ?? '';
                    
                    if($page_slug == $page_slug_bb){
                        $seo_page_title = $seo_pages_title[$index] ?? '';
                        $seo_page_description = $seo_pages_description[$index] ?? '';
                        $pageSlugBB = $page_slug_bb;
                        $url_fav = BB_BASE_URL . "/api/plugin/favorites?apiToken={$apiToken}&websitepropertyId={$websitePropertyId}&email={$favorite_email_id}";

                        $response = wp_remote_get( $url_fav );
                        if ( is_wp_error( $response ) ) {
                            $error_message = $response->get_error_message();
                            echo "Something went wrong: $error_message";
                        } else {
                            $favorite_data_brag_json = wp_remote_retrieve_body( $response );
                        }
                        $favorite_data_brag = json_decode($favorite_data_brag_json);
                       
                        foreach ($favorite_data_brag->favorites as $favorite) {
                            foreach ($favorite->cases as $caseItem) {
                                $case_fav[] = $caseItem->id;
                            }
                        }
                        $bb_sidebar_url = BB_BASE_URL . "/api/plugin/sidebar?apiToken={$apiToken}";
                        
                        $cacheKey = "$procedureSlug-single";
                        $sidebar_list = get_transient($cacheKey);
                        if (!$sidebar_list) {
                            // If cache is empty, fetch data from API
                            $response = wp_remote_get($bb_sidebar_url);
                            if (is_wp_error($response)) {
                                return null;
                            }
                            $sidebar_list = wp_remote_retrieve_body($response);
                            set_transient($cacheKey, $sidebar_list, HOUR_IN_SECONDS);
                        }
                        if ($_POST['favorites'] == 'favorites') {
                            $data = $favorite_data_brag_json;
                        }
                    }elseif($page_slug == $combine_gallery_page_slug && $tc == count($api_tokens)) {
                       // /api/plugin/combine/favorites/list
                        $url_fav = BB_BASE_URL . "/api/plugin/combine/favorites/list";
                        $response = wp_remote_post($url_fav, array(
                            'method' => 'POST',
                            'body' => json_encode(array(
                                'apiTokens' => $fav_token,
                                'websitePropertyIds' => $web_id,
                                'email' => $favorite_email_id
                            )),
                            'headers' => array(
                                'Content-Type' => 'application/json',
                            ),
                        ));
    
                        if (is_wp_error($response)) {
                            wp_send_json_error(array('message' => $response->get_error_message()));
                        }
                       
                        $favorite_data_brag_json = wp_remote_retrieve_body($response);
                        $bb_sidebar_url = BB_BASE_URL . "/api/plugin/combine/sidebar";

                        $response_sidebar = wp_remote_post($bb_sidebar_url, array(
                            'method'    => 'POST',
                            'body'      => json_encode(array(
                                'apiTokens'    => $fav_token
                            )),
                            'headers'   => array(
                                'Content-Type' => 'application/json',
                            ),
                        ));
                        
                        if (is_wp_error($response_sidebar)) {
                            wp_send_json_error(array('message' => $response_sidebar->get_error_message()));
                        }

                        $sidebar_list = wp_remote_retrieve_body($response_sidebar);
                        if ($_POST['favorites'] == 'favorites') {
                            $data = $favorite_data_brag_json;
                        }
                        $favorite_data_brag = json_decode($favorite_data_brag_json);
                       
                        // Loop through the favorites and cases
                        //var_dump($favorite_data_brag->favorites);

                        foreach ($favorite_data_brag->favorites as $favorite) {
                            foreach ($favorite->cases as $caseItem) {
                                $case_fav[] = $caseItem->id;
                            }
                        }
                    }
                }
            // }
            if (!empty($sidebar_list)) {
                $sidebar_list = json_encode($sidebar_list);
            } else {
                $sidebar_list = '';
            }
            if (!empty($case_fav)) {
                json_encode($case_fav);
            } else {
                $case_fav = '';
            }
            $info = $data_in;
            $response = [
                'status' => 'success',
                'message' => 'Data received successfully.',
                'data' => [
                    'url' => $url,
                    'case_set' => $data,
                    'filter_data' => $filter_get,
                    'bragbook_favorite' => $case_fav,
                    'sidebar_api' => $sidebar_list,
                    'info' => $info,
                    'page_slug' => $page_slug,
                    'page_slug_bb' => $pageSlugBB,
                    'seo_page_title' => $seo_page_title,
                    'seo_page_description' => $seo_page_description,
                ]
            ];
            // Return the response as JSON
            wp_send_json($response);
        } else {
            // If any required parameter is missing, return an error response
            wp_send_json_error(['message' => 'Missing required parameters.']);
        }
    }
    public static function case_and_filter_api($url)
    {
        $response = wp_remote_get( $url );
        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
        } else {
            $data = wp_remote_retrieve_body( $response );
        }

        set_transient($url, $data, 1800);
        return $data;
    }
    public static function update_url($new_case_id, $page_id_via_slug, $url_bb)
    {
        $path_parts = explode('/', $url_bb);
        $procedure_id_bb = get_option($new_case_id . '_bb_procedure_id_f_' . $page_id_via_slug);
        $procedure_title = get_option($procedure_id_bb . '_title');

        $converted_procedure_name = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($procedure_title));
        $converted_procedure_name = self::removeAccents_brag_favorite($converted_procedure_name);
        $path_parts[count($path_parts) - 3] = $converted_procedure_name;
        $path_parts[count($path_parts) - 2] = get_option($new_case_id);

        return implode('/', $path_parts);
    }

    public static function generate_pagination($current_case_id, $case_id_list, $page_id_via_slug, $url_bb)
    {
        $currentIndex = array_search($current_case_id, $case_id_list);
        $prevIndex = max($currentIndex - 1, 0);
        $nextIndex = min($currentIndex + 1, count($case_id_list) - 1);
        $start = max($currentIndex - 1, 0);
        $end = min($start + 3, count($case_id_list) - 1);

        $end = min($end, $start + 3);

        echo '<ul>';

        if ($currentIndex == 0) {
            echo '<li style="display:none;"><a href="#">Previous</a></li>';
        } else {
            echo '<li><a href="' . self::update_url($case_id_list[$prevIndex], $page_id_via_slug, $url_bb) . '"> &lt; <span>Previous</span></a></li>';
        }
        $page_count = $start + 1;
        for ($i = $start; $i <= $end; $i++) {
            $case_id = $case_id_list[$i];
            $activeClass = ($case_id == $current_case_id) ? 'active' : '';
            if (!empty($activeClass)) {
                update_option('bb_current_case_page_count_f', $page_count);
                ?>
                <script>
                    var page_c_title = "<?php echo $page_count; ?>";
                    var elements = document.querySelectorAll('.bb-patient-row h2 span');
                    elements.forEach(function (element) {
                        element.textContent = page_c_title;
                    });
                </script>
                <?php
            }
            echo '<li class="' . $activeClass . ' bb-single-case"><a href="' . self::update_url($case_id, $page_id_via_slug, $url_bb) . '">' . $page_count++ . '</a></li>';
        }
        if ($page_count <= 2) {
            ?>
            <script>
                var elements = document.querySelectorAll('.bb-single-case');
                elements.forEach(function (element) {
                    element.style.display = 'none';
                });
            </script>
            <?php
        }

        if ($currentIndex == count($case_id_list) - 1) {
            echo '<li style="display:none;"><a href="#">Next</a></li>';
        } else {
            if ($nextIndex > -1) {
                echo '<li><a href="' . self::update_url($case_id_list[$nextIndex], $page_id_via_slug, $url_bb) . '"><span>Next</span> &gt;</a></li>';
            }
        }

        echo '</ul>';
    }

    public static function bb_generate_pagination()
    {

        $page_id_via_slug = sanitize_text_field($_POST['page_id_via_slug']);
        $url_bb = sanitize_text_field($_POST['page_url']);
        // Call your existing pagination function
        ob_start();
        $path_parts = explode('/', $url_bb);
        $case_id_list = json_decode(get_option('bb_caseids_list_f'));
        $current_case_id = $path_parts[4];

        if (is_numeric($current_case_id)) {
            $current_case_id = (int) $current_case_id;
        } else {
            $current_case_id = get_option($current_case_id);
        }
        self::generate_pagination($current_case_id, $case_id_list, $page_id_via_slug, $url_bb);
        $output = ob_get_clean();

        // Return the result to be used in the AJAX callback
        echo $output;
        wp_die();
    }

    // AJAX handler to fetch favorite data
    public static function bb_limitWords($text, $wordLimit)
    {
        $words = explode(' ', $text);
        $words = array_slice($words, 0, $wordLimit);
        $limitedText = implode(' ', $words);
        return $limitedText;
    }

    public static function convertKeys($array)
    {
        $result = array();

        if (!is_array($array) && !is_object($array)) {
            $array = [];
        }

        if (!empty($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $result[$key] = self::convertKeys($value);
                } else {
                    $newKey = str_replace(' ', '_', $key);
                    $result[$newKey] = $value;
                }
            }
        }
        return $result;
    }
    public static function formatArrayToString($array)
    {
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $result[] = self::formatArrayToString($value);
            } else {
                $result[] = "{$key}-{$value}";
            }
        }
        return implode(' ', $result);
    }
    public static function bb_fetch_favorite_data()
    {

        // $favorite_email_id = get_option('bragbook_favorite_email');
        // $favorite_caseIds_count = 0;
        // $favorite_caseIds = [];
        // $page_name = $_POST['page_name'];
        // $combine_gallery_page_slug = get_option('combine_gallery_slug');

        // if(isset($_COOKIE['wordpress_favorite_email'])) {
        //     $cookieValue = $_COOKIE['wordpress_favorite_email'];
        //     $decodedValue = urldecode($cookieValue);
        //     $favorite_email_id = htmlspecialchars($decodedValue);

        //     update_option('bragbook_favorite_email', $favorite_email_id);
        //     $api_tokens = get_option('bragbook_api_token', []);
        //     $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
        //     $gallery_slugs = get_option('bb_gallery_page_slug', []);
        //     $favorite_data_bb = [];
        //     foreach ($api_tokens as $index => $api_token) {
        //         $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
        //         $page_slug_bb = $gallery_slugs[$index] ?? '';
        //         if(($page_slug_bb == $page_name) || ($combine_gallery_page_slug == $page_name)) {
        //             if (empty($api_token) || empty($websiteproperty_id)) {
        //                 continue;
        //             }

        //             $url = BB_BASE_URL . "/api/plugin/favorites?apiToken='.$api_token.'&websitepropertyId='.$websiteproperty_id.'&email='.$favorite_email_id;
        //             $ch = curl_init();
        //             curl_setopt($ch, CURLOPT_URL, $url);
        //             curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //             curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        //             $favorite_data_brag = curl_exec($ch);
        //             curl_close($ch);

        //             $favorite_data_bb[] = json_decode($favorite_data_brag, true);
        //         }
        //     }
        //     $favorite_procedure_ids = [];

        //     if(!empty($favorite_data_bb) && is_array($favorite_data_bb)) {
        //         foreach($favorite_data_bb as $favorite_data) {
        //             if(!empty($favorite_data) && is_array($favorite_data)) {
        //                 foreach ($favorite_data as $favorite) {
        //                     foreach($favorite as $nested_favorite) {
        //                         if (isset($nested_favorite['cases']) && is_array($nested_favorite['cases'])) {
        //                             foreach ($nested_favorite['cases'] as $case) {
        //                                 if (isset($case['id'])) {
        //                                     $favorite_caseIds[] = $case['id'];
        //                                     foreach($case['procedureIds'] as $bb_pro_id) {
        //                                         $favorite_procedure_ids[] = $bb_pro_id; 
        //                                     }
        //                                 }
        //                             }
        //                         }
        //                     } 
        //                 }
        //             }
        //         }

        //         $favorite_caseIds = array_unique($favorite_caseIds);
        //         $expireTime = time() + (365 * 24 * 60 * 60); // 1 year
        //         $caseIdsString = implode(',', $favorite_caseIds);
        //         setcookie('wordpress_favorite_case_id', $caseIdsString, $expireTime, '/');
        //         $favorite_caseIds_count = count($favorite_caseIds);
        //         update_option('bb_favorite_caseIds_count', $favorite_caseIds_count);
        //         $favorite_procedure_ids = array_unique($favorite_procedure_ids);
        //         update_option('bb_favorite_procedure_ids', $favorite_procedure_ids); 
        //         update_option('favorite_caseIds_ajax', $favorite_caseIds);

        //         $matching_data_all = [];
        //         $procedure_counts = [];
        //         $bb_case_ids_list_a = [];
        //         $patient_count = 1;

        //         $favorite_procedure_ids = get_option('bb_favorite_procedure_ids');
        //         $favorite_caseIds = get_option('favorite_caseIds_ajax');
        //         if($_POST['value'] == 'combine') {
        //             $properties_data = get_option("bb_combine_api_data");
        //         } else {
        //             $properties_data = get_option('bb_api_data');
        //         }
        //         $properties_data_ajax_bb = json_decode($properties_data);

        //         if(!empty($properties_data_ajax_bb) && (is_array($properties_data_ajax_bb) || is_object($properties_data_ajax_bb))) {

        //             foreach ($properties_data_ajax_bb as $token_key_bb => $token_bb) {
        //                 foreach ($token_bb as $bb_website_id => $website_id_bb) { 
        //                     foreach($website_id_bb as $api_item) {

        //                         foreach($api_item->api_data as $item){
        //                             if(isset($favorite_procedure_ids) && !empty($favorite_procedure_ids)) {
        //                                 foreach($favorite_procedure_ids as $bb_favorite_procedure_id) {
        //                                     if (in_array($bb_favorite_procedure_id, $item->procedureIds)) {
        //                                         if(in_array($item->id, $favorite_caseIds)) {

        //                                             $item->procedure_id = $bb_favorite_procedure_id;
        //                                             $item_id = $item->id;
        //                                             $matching_data_all[$item_id] = $item;
        //                                         }

        //                                     }
        //                                 }
        //                             }
        //                         }
        //                     }
        //                 }
        //             }
        //         }

        //     $matching_data_all  =  array_values($matching_data_all); 
        //     $patient_count = 1;


        //         $page_id_via_slug = $_POST['page_id'];
        //         foreach($matching_data_all as $procedure_data) {

        //             $convertedArray = self::convertKeys($procedure_data->procedureDetails);
        //             $formattedString = self::formatArrayToString($convertedArray);

        //             if(!empty($procedure_data->photoSets)) { 
        //                 $p_c_count = $patient_count;
        //                 if(isset($procedure_data->procedure_case_count) && $procedure_data->procedure_case_count == NULL) {
        //                 $p_c_count = $patient_count;
        //                 }elseif(isset($procedure_data->procedure_case_count)) {
        //                 $p_c_count = $procedure_data->procedure_case_count;
        //                 }
        //                 $pro_title = get_option($procedure_data->procedure_id . '_title');
        //                 $bb_page_name = '/' .$page_name. '/favorites/';
        //                 $bb_new_image_case = isset($procedure_data->photoSets[0]->highResPostProcessedImageLocation) && !is_null($procedure_data->photoSets[0]->highResPostProcessedImageLocation)
        //                                         ? $procedure_data->photoSets[0]->highResPostProcessedImageLocation 
        //                                             : (isset($procedure_data->photoSets[0]->postProcessedImageLocation) && !is_null($procedure_data->photoSets[0]->postProcessedImageLocation) 
        //                                                 ? $procedure_data->photoSets[0]->postProcessedImageLocation 
        //                                                 : $procedure_data->photoSets[0]->originalBeforeLocation);
        //                 $converted_procedure_name = preg_replace('/[^a-zA-Z0-9]+/', '-', $pro_title);
        //                 $converted_procedure_name = strtolower($converted_procedure_name);
        //                 $html_content .= '<div class="bb-content-box ' . $formattedString . '"><div 
        //                 class="bb-content-thumbnail"><a 
        //                 href="' . $bb_page_name . self::removeAccents_brag_favorite($converted_procedure_name) . '/' . $procedure_data->photoSets[0]->caseId . '/"><img 
        //                 src="' . $bb_new_image_case . '" alt="' . (isset($procedure_data->photoSets[0]->seoAltText) ? $procedure_data->photoSets[0]->seoAltText : '') . '"></a><img
        //                 class="bb-heart-icon" data-case-id="' . $procedure_data->photoSets[0]->caseId . '" 
        //                 src="' . BB_PLUGIN_DIR_PATH . 'assets/images/red-heart.svg" alt="heart"></div><div class="bb-content-box-inner"><div 
        //                 class="bb-content-box-inner-left"><h5>' . $pro_title . ' : Patient ' . $p_c_count . '</h5><p>' . (!empty($procedure_data->details) ? self::bb_limitWords($procedure_data->details, 50) : "") . '</p><button 
        //                 type="button"><a href="' . $bb_page_name . self::removeAccents_brag_favorite($converted_procedure_name) . '/' . $procedure_data->photoSets[0]->caseId . '/">View More</a></button></div><div 
        //                 class="bb-content-box-inner-right"><img class="" data-case-id="' . $procedure_data->photoSets[0]->caseId . '" src="' . BB_PLUGIN_DIR_PATH . 'assets/images/red-heart-outline.svg" alt="heart"></div></div></div>';
        //                 $bb_case_ids_list_a[] = $procedure_data->photoSets[0]->caseId;
        //                 update_option($procedure_data->photoSets[0]->caseId . '_bb_procedure_id_f_' . $page_id_via_slug, $procedure_data->procedure_id);

        //                 $patient_count++;
        //             }
        //         }

        //         $bb_encode_caseids_list = json_encode($bb_case_ids_list_a); 
        //         update_option('bb_caseids_list_f', $bb_encode_caseids_list);
        //     }

        //     // Return the data to the frontend
        //     wp_send_json_success(array(
        //         'html' => $html_content,
        //         'favorite_case_ids' => $favorite_caseIds,
        //         'favorite_procedure_ids' => $favorite_procedure_ids,
        //         'favorite_case_count' => $favorite_caseIds_count
        //     ));
        // } else {
        //     update_option('bb_favorite_caseIds_count', '');

        //     update_option('bb_favorite_procedure_ids', []); 
        //     update_option('favorite_caseIds_ajax', []);
        //     wp_send_json_error(array('message' => 'No favorite email found.'));
        // }
    }
    public static function removeAccents_brag_favorite($string)
    {
        $accents = [
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'a',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ē' => 'e',
            'ė' => 'e',
            'ě' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ī' => 'i',
            'į' => 'i',
            'ì' => 'i',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ō' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ū' => 'u',
            'ų' => 'u',
            'ű' => 'u',
            'ý' => 'y',
            'ÿ' => 'y',
            'ç' => 'c',
            'ć' => 'c',
            'č' => 'c',
            'ĉ' => 'c',
            'ċ' => 'c',
            'ñ' => 'n',
            'ń' => 'n',
            'ņ' => 'n',
            'ň' => 'n',
            'ś' => 's',
            'š' => 's',
            'ş' => 's',
            'ž' => 'z',
            'ź' => 'z',
            'ż' => 'z',
        ];

        foreach ($accents as $accented => $unaccented) {
            $string = str_replace($accented, $unaccented, $string);
        }
        return $string;
    }

    public function bb_update_api()
    {
        $bb_set_transient_urls = get_option('bb_set_transient_url', []);

        foreach ($bb_set_transient_urls as $bb_set_transient_url => $bb_set_transient_url_data) {
            set_transient($bb_set_transient_url, $bb_set_transient_url_data, 2);
        }

        $filepath = trailingslashit(ABSPATH) . 'bragbook-sitemap.xml';

        if(file_exists($filepath)){
            $this->create_bragbook_sitemap();
        }
        wp_send_json_success('API Updated Successfully.');
        die();
    }

    public static function removeAccents_brag_ajax($string)
    {
        $accents = [
            'à' => 'a',
            'á' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'ä' => 'a',
            'å' => 'a',
            'æ' => 'a',
            'è' => 'e',
            'é' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'ē' => 'e',
            'ė' => 'e',
            'ě' => 'e',
            'ì' => 'i',
            'í' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ī' => 'i',
            'į' => 'i',
            'ì' => 'i',
            'ò' => 'o',
            'ó' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ō' => 'o',
            'ù' => 'u',
            'ú' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ū' => 'u',
            'ų' => 'u',
            'ű' => 'u',
            'ý' => 'y',
            'ÿ' => 'y',
            'ç' => 'c',
            'ć' => 'c',
            'č' => 'c',
            'ĉ' => 'c',
            'ċ' => 'c',
            'ñ' => 'n',
            'ń' => 'n',
            'ņ' => 'n',
            'ň' => 'n',
            'ś' => 's',
            'š' => 's',
            'ş' => 's',
            'ž' => 'z',
            'ź' => 'z',
            'ż' => 'z',
        ];

        foreach ($accents as $accented => $unaccented) {
            $string = str_replace($accented, $unaccented, $string);
        }
        return $string;
    }

    public static function bb_limitWords_ajax($text, $wordLimit)
    {
        if (!is_string($text)) {
            $text = '';
        }

        $words = explode(' ', $text);
        $words = array_slice($words, 0, $wordLimit);
        $limitedText = implode(' ', $words);

        return $limitedText;
    }

    function load_more_procedures()
    {
        $offset = isset($_POST['offset']) ? (int) $_POST['offset'] : 0;
        $items_per_page = isset($_POST['items_per_page']) ? (int) $_POST['items_per_page'] : 10;
        $matching_case_data = get_option('bb_matching_case_data_for_ajax');
        $bb_ajax_path = get_option('bb_ajax_path');

        $paged_data = array_slice($matching_case_data, $offset, $items_per_page);
        $patient_count = 1;
        $bb_isNude = "";
        $favorite_caseIds = get_option('favorite_caseIds_ajax');

        $bbrag_case_url_bb = strtok($_SERVER["REQUEST_URI"], '?');
        $bbragbook_case_url_bb = trim($bbrag_case_url_bb, '/');
        $parts_page_name = explode('/', $bbragbook_case_url_bb);
        $page_bb_data = get_page_by_path($parts_page_name[0]);
        $page_id_via_slug = $page_bb_data->ID;
        ob_start();
        foreach ($paged_data as $procedure_data) {
            $formattedString = '';
            $bb_procedure_id = $procedure_data['procedureIds'][0];
            if (!is_null($procedure_data['procedureDetails'])) {
                if (isset($procedure_data['procedureDetails'][$bb_procedure_id])) {
                    $bb_procedureDetails = $procedure_data['procedureDetails'][$bb_procedure_id];
                    $advanced_filters_result_string = '';

                    foreach ($bb_procedureDetails as $bb_procedureDetails_key => $bb_procedureDetails_value) {
                        $bb_procedureDetails_key = strtolower(str_replace(' ', '_', $bb_procedureDetails_key));

                        if (is_array($bb_procedureDetails_value)) {
                            foreach ($bb_procedureDetails_value as $bb_procedureDetails_item) {
                                $advanced_filters_result_string .= $bb_procedureDetails_key . strtolower($bb_procedureDetails_item) . ' ';
                            }
                        } else {
                            $bb_procedureDetails_value = strtolower(str_replace(' ', '_', $bb_procedureDetails_value));
                            $advanced_filters_result_string .= $bb_procedureDetails_key . $bb_procedureDetails_value . ' ';
                        }
                    }
                    $advanced_filters_result_string = trim($advanced_filters_result_string);
                }
            } else {
                $advanced_filters_result_string = "";
            }

            $classes = [
                'bb-content-box',
                'height-' . (isset($procedure_data['height']) ? $procedure_data['height'] : ''),
                'weight-' . (isset($procedure_data['weight']) ? $procedure_data['weight'] : ''),
                'gender-' . (isset($procedure_data['gender']) ? $procedure_data['gender'] : ''),
                'race-' . (isset($procedure_data['ethnicity']) ? $procedure_data['ethnicity'] : ''),
                'age-' . (isset($procedure_data['age']) ? $procedure_data['age'] : ''),
                $advanced_filters_result_string
            ];
            $classString = implode(' ', $classes);

            if (!empty($procedure_data['photoSets'])) { ?>
                <div class="<?php echo $classString; ?> <?php echo $formattedString; ?>">
                    <div class="bb-content-thumbnail">
                        <?php
                        $p_c_count = $procedure_data['procedure_case_count'] == NULL ? $patient_count : $procedure_data['procedure_case_count'];
                        $category_match_id = empty($category_to_match) ? $procedure_data['procedure_id'] : $category_to_match;
                        $pro_title = empty($procedure_title) ? $procedure_data['procedure_title'] : $procedure_title;
                        $bb_case_ids_list[] = $procedure_data['photoSets'][0]['caseId'];
                        $converted_procedure_name = str_replace(' ', '-', $pro_title);

                        $bb_seo_detail = isset($procedure_data['caseDetails'][0]) ? $procedure_data['caseDetails'][0] : [];
                        if (isset($bb_seo_detail['seoSuffixUrl']) && !empty($bb_seo_detail['seoSuffixUrl'])) {
                            $formatted_heading = $bb_seo_detail['seoSuffixUrl'];
                        } else {
                            update_option($procedure_data['photoSets'][0]['id'], $procedure_data['photoSets'][0]['caseId']);
                            $formatted_heading = $procedure_data['photoSets'][0]['caseId'];
                        }
                        if (isset($procedure_data['photoSets'][0]) && $bb_isNude == "") {
                            $bb_isNude = $procedure_data['photoSets'][0]['isNude'];
                        }
                        ?>
                        <a
                            href="<?php echo "/" . $bb_ajax_path . "/" . self::removeAccents_brag_ajax(strtolower($converted_procedure_name)) . "/" . $formatted_heading . "/"; ?>">
                            <?php
                            $bb_new_image_procedure_data = isset($procedure_data['photoSets'][0]['highResPostProcessedImageLocation']) && !is_null($procedure_data['photoSets'][0]['highResPostProcessedImageLocation'])
                                ? $procedure_data['photoSets'][0]['highResPostProcessedImageLocation']
                                : (isset($procedure_data['photoSets'][0]['postProcessedImageLocation']) && !is_null($procedure_data['photoSets'][0]['postProcessedImageLocation'])
                                    ? $procedure_data['photoSets'][0]['postProcessedImageLocation']
                                    : $procedure_data['photoSets'][0]['originalBeforeLocation']);

                            ?>
                            <img src="<?php echo $bb_new_image_procedure_data; ?>"
                                alt="<?php echo isset($procedure_data['photoSets'][0]['seoAltText']) ? $procedure_data['photoSets'][0]['seoAltText'] : ''; ?>">
                        </a>
                        <?php
                        if (in_array($procedure_data['photoSets'][0]['caseId'], $favorite_caseIds)) {
                            ?>
                            <img class="bb-heart-icon bb-open-fav-modal"
                                data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>"
                                data-bb_api_token="<?php echo $procedure_data['bb_api_token']; ?>"
                                data-bb_website_id="<?php echo $procedure_data['bb_website_id']; ?>"
                                src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart-outline.svg" alt="heart">

                            <?php
                        } else {
                            ?>
                            <img class="bb-heart-icon bb-open-fav-modal"
                                data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>"
                                data-bb_api_token="<?php echo $procedure_data['bb_api_token']; ?>"
                                data-bb_website_id="<?php echo $procedure_data['bb_website_id']; ?>"
                                src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart.svg" alt="heart">

                            <?php
                        }
                        ?>
                    </div>
                    <div class="bb-content-box-inner">
                        <div class="bb-content-box-inner-left">
                            <?php
                            if (isset($bb_seo_detail['seoHeadline']) && !empty($bb_seo_detail['seoHeadline'])) {
                                ?>
                                <h5><?php echo $bb_seo_detail['seoHeadline']; ?></h5>
                                <?php
                            } else {
                                ?>
                                <h5><?php echo $pro_title; ?> : Patient <?php echo $p_c_count ?></h5>
                                <?php
                            }
                            ?>
                            <p>
                                <?php
                                $bb_details_description = self::bb_limitWords_ajax($procedure_data['details'], 50);
                                echo $bb_details_description;
                                ?>
                            </p>

                            <?php
                            update_option($procedure_data['photoSets'][0]['caseId'] . '_bb_procedure_id_' . $page_id_via_slug, $bbrag_procedure_id);
                            update_option($formatted_heading, $procedure_data['photoSets'][0]['caseId']);
                            update_option($procedure_data['photoSets'][0]['caseId'], $formatted_heading);
                            ?>

                        </div>
                        <div class="bb-content-box-inner-right">
                            <?php
                            if (isset($favorite_caseIds) && !empty($favorite_caseIds) && in_array($procedure_data['photoSets'][0]['caseId'], $favorite_caseIds)) {
                                ?>
                                <img class="bb-open-fav-modal" data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>"
                                    data-bb_api_token="<?php echo $procedure_data['bb_api_token']; ?>"
                                    data-bb_website_id="<?php echo $procedure_data['bb_website_id']; ?>"
                                    src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart-outline.svg" alt="heart">

                                <?php
                            } else {
                                ?>
                                <img class="bb-open-fav-modal" data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>"
                                    data-bb_api_token="<?php echo $procedure_data['bb_api_token']; ?>"
                                    data-bb_website_id="<?php echo $procedure_data['bb_website_id']; ?>"
                                    src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart.svg" alt="heart">

                                <?php
                            }
                            ?>
                        </div>
                    </div>
                    <div class="bb-content-box-cta"><a class="view-more-btn"
                            href="<?php echo "/" . $bb_ajax_path . "/" . self::removeAccents_brag_ajax(strtolower($converted_procedure_name)) . "/" . $formatted_heading . "/"; ?>">View
                            More</a></div>

                </div>

                <?php
                $patient_count++;
            ?>
            <?php
            }
        }

        $items_html = ob_get_clean();
        $has_more = count($matching_case_data) > ($offset + $items_per_page);

        wp_send_json_success(array(
            'items_html' => $items_html,
            'has_more' => $has_more
        ));
    }

    public function bb_permanent_delete_empty_trash($post_id)
    {
        if (get_post_type($post_id) === 'page') {
            $page_slug = get_post_field('post_name', $post_id);

            $combine_gallery_slug = get_option('combine_gallery_slug');
            if ($page_slug == $combine_gallery_slug) {
                update_option('combine_gallery_slug', "");
            }

            $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids');
            $bb_remove_id = array_search($post_id, $bb_page_list_gallery);

            $bb_id_list = get_option('bb_gallery_stored_pages');
            $bb_id_list_gallery = get_option('bb_gallery_page_slug');
            $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids');
            $bragbook_websiteproperty_id = get_option('bragbook_websiteproperty_id');
            $bragbook_api_token = get_option('bragbook_api_token');

            update_option('bb_remove_pages_from_setting', $bb_id_list_gallery);
            update_option('bb_remove_combine_gallery_from_setting', $combine_gallery_slug);

            unset($bb_id_list_gallery[$bb_remove_id]);
            unset($bb_id_list[$bb_remove_id]);
            unset($bb_page_list_gallery[$bb_remove_id]);
            unset($bragbook_websiteproperty_id[$bb_remove_id]);
            unset($bragbook_api_token[$bb_remove_id]);

            update_option('bb_gallery_page_slug', $bb_id_list_gallery);
            update_option('bb_gallery_stored_pages_ids', $bb_page_list_gallery);
            update_option('bragbook_websiteproperty_id', $bragbook_websiteproperty_id);
            update_option('bragbook_api_token', $bragbook_api_token);
            update_option('bb_gallery_stored_pages', $bb_id_list);

        }
    }

    public function bb_footer_gallery_modal()
    {
        ?>
        <div id="bbrag_modal" class="bbrag_modal">
            <span class="bbrag_close">&times;</span>
            <img class="bbrag_modal_content" id="bbrag_modalImage">
            <a class="bbrag_prev">&#10094;</a>
            <a class="bbrag_next">&#10095;</a>
        </div>
        <?php
    }

    public function bb_setting_remove_row()
    {
        $bb_remove_id = $_POST['bb_remove_id'];
        $bb_id_list = get_option('bb_gallery_stored_pages');
        $bb_id_list_gallery = get_option('bb_gallery_page_slug');
        $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids');
        $bragbook_websiteproperty_id = get_option('bragbook_websiteproperty_id');
        $bragbook_api_token = get_option('bragbook_api_token');

        self::bb_delete_page_by_slug($bb_id_list[$bb_remove_id]);
        unset($bb_id_list_gallery[$bb_remove_id]);
        unset($bb_id_list[$bb_remove_id]);
        unset($bb_page_list_gallery[$bb_remove_id]);
        unset($bragbook_websiteproperty_id[$bb_remove_id]);
        unset($bragbook_api_token[$bb_remove_id]);

        update_option('bb_gallery_page_slug', $bb_id_list_gallery);
        update_option('bb_gallery_stored_pages_ids', $bb_page_list_gallery);
        update_option('bragbook_websiteproperty_id', $bragbook_websiteproperty_id);
        update_option('bragbook_api_token', $bragbook_api_token);

        $bb_result = update_option('bb_gallery_stored_pages', $bb_id_list);
        echo $bb_result;
        exit();
    }

    public static function bb_delete_page_by_slug($slug)
    {
        $page = get_page_by_path($slug, OBJECT, 'page');
        if ($page) {
            wp_delete_post($page->ID, true);
        }
    }

    public function bb_update_slugs($post_id)
    {
        if (get_post_type($post_id) !== 'page') {
            return;
        }

        $new_slug = get_post_field('post_name', $post_id);
        $stored_pages_associative = get_option('bb_gallery_stored_pages', []);
        $bb_pages_associative = get_option('bb_gallery_page_slug', []);

        $option_name = 'bb_gallery_stored_pages_ids';
        $current_option = get_option($option_name);

        if ($current_option && is_array($current_option)) {
            foreach ($current_option as $key => $bb_page_id) {
                if ($post_id === $bb_page_id) {
                    $stored_pages_associative[$key] = $new_slug;
                    $bb_pages_associative[$key] = $new_slug;
                    update_option('bb_gallery_stored_pages', $stored_pages_associative);
                    update_option('bb_gallery_page_slug', $bb_pages_associative);
                }
            }

            $new_title = ucwords(str_replace('-', ' ', $new_slug));

            $current_title = get_the_title($post_id);


            $current_slug_bb = "";
            $current_bb_slug = get_page_by_title($current_title, OBJECT, 'page');
            if ($current_bb_slug) {
                $current_slug_bb = $current_bb_slug->post_name;
            }
            $bb_remove_pages_from_setting = get_option('bb_remove_pages_from_setting');
            $bb_remove_combine_gallery_from_setting = get_option('bb_remove_combine_gallery_from_setting');

            if (empty($current_title) && empty($new_slug)) {
                wp_update_post([
                    'ID' => $post_id,
                    'post_title' => $new_title
                ]);
            
                $page_bb_combine = get_option('combine_gallery_page_id');
            
                if ($page_bb_combine == $post_id) {
                    if (get_post_status($post_id) === 'trash') {
                        update_option('combine_gallery_slug', "");
                    } else {
                        update_option('combine_gallery_slug', $new_slug);
                    }
                }
            }
        }
    }

    public function bb_save_bragbook_settings()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You are not allowed to perform this action.');
            return;
        }
        $bb_token_based_page_keys = $_POST['bb_page_keys'];

        parse_str($_POST['form_data'], $form_data);
        update_option('bragbook_landing_page_text', wp_kses_post($form_data['bragbook_landing_page_text']));
        update_option('bb_seo_plugin_selector', sanitize_text_field($form_data['bb_seo_plugin_selector']));
        $current_user_id = get_current_user_id();
        if (isset($form_data['combine_gallery_slug']) && !empty($form_data['combine_gallery_slug'])) {
            $bb_old_combine_gallery = get_option('combine_gallery_slug');
            $slug_input = sanitize_text_field($form_data['combine_gallery_slug']);

            update_option('combine_gallery_slug', $slug_input);
            $bb_combine_slug = get_option('combine_gallery_slug');

            $bb_formatted_string = ucwords(str_replace('-', ' ', $bb_combine_slug));
            $existing_page = get_page_by_path($bb_combine_slug, OBJECT, 'page');

            if ($existing_page) {
                if ($existing_page->post_status == 'trash' || $existing_page->post_status == 'draft') {
                    $existing_page->post_status = 'publish';
                }
                wp_update_post([
                    'ID' => $existing_page->ID,
                    'post_name' => $bb_combine_slug,
                    'post_title' => $bb_formatted_string,
                    'post_status' => $existing_page->post_status
                ]);
            } else {
                $old_slug = $bb_old_combine_gallery;
                $old_page = get_page_by_path($old_slug, OBJECT, 'page');

                if ($old_page) {
                    wp_update_post([
                        'ID' => $old_page->ID,
                        'post_name' => $bb_combine_slug,
                        'post_title' => $bb_formatted_string,
                    ]);
                } else {
                    $new_page_id = wp_insert_post([
                        'post_title' => $bb_formatted_string,
                        'post_name' => $bb_combine_slug,
                        'post_status' => 'publish',
                        'post_type' => 'page',
                        'post_author' => $current_user_id,
                        'page_template' => 'bb-brag.php'
                    ]);

                    if (is_wp_error($new_page_id)) {
                        error_log('Error creating page: ' . $new_page_id->get_error_message());
                    } else {
                        update_post_meta($new_page_id, '_wp_page_template', 'template/bb-brag.php');
                        update_option('combine_gallery_page_id', $new_page_id);
                    }
                }
            }
        }

        if (isset($form_data['bragbook_api_token'])) {
            update_option('bragbook_api_token', array_map('sanitize_text_field', $form_data['bragbook_api_token']));
        }
        if (isset($form_data['bragbook_websiteproperty_id'])) {
            update_option('bragbook_websiteproperty_id', array_map('sanitize_text_field', $form_data['bragbook_websiteproperty_id']));
        }
        if (isset($form_data['bb_gallery_page_slug'])) {
            update_option('bb_gallery_page_slug', array_map('sanitize_text_field', $form_data['bb_gallery_page_slug']));
        }
        if (isset($form_data['bb_seo_page_title'])) {
            update_option('bb_seo_page_title', array_map('sanitize_text_field', $form_data['bb_seo_page_title']));
        }
        if (isset($form_data['bb_seo_page_description'])) {
            update_option('bb_seo_page_description', array_map('sanitize_text_field', $form_data['bb_seo_page_description']));
        }
        $bb_pages_slugs = get_option('bb_gallery_page_slug', []);
        self::bb_token_base_page_creation($bb_token_based_page_keys, $bb_pages_slugs, $current_user_id);

        $seoPluginOptions = get_option('bb_seo_plugin_selector');
        $api_tokens = get_option('bragbook_api_token', []);

        $filepath = trailingslashit(ABSPATH) . 'bragbook-sitemap.xml';

        if(!empty($seoPluginOptions) && !empty($api_tokens) && !file_exists($filepath)){
            $this->create_bragbook_sitemap();
        }elseif(empty($seoPluginOptions) && file_exists($filepath)){
            unlink($filepath);
        }
        // Send success response
        wp_send_json_success('Settings saved successfully.');
        exit();
    }

    public static function get_page_id_by_slug($slug)
    {
        $page = get_page_by_path($slug);
        if ($page) {
            return $page->ID;
        }
        return null;
    }

    public static function bb_token_base_page_creation($bb_token_based_page_keys, $bb_pages_slugs, $current_user_id)
    {
        $stored_pages = get_option('bb_gallery_stored_pages', []);
        $bb_pages_ids = get_option('bb_gallery_stored_pages_ids', []);
        $updated_stored_pages = [];
        $stored_pages_ids = [];
        foreach ($bb_token_based_page_keys as $key => $slug) {
            if (!empty($stored_pages) && array_key_exists($slug['key'], $stored_pages)) {
                if ($stored_pages[$slug['key']] === $slug['value']) {
                    continue;
                }

                $bb_current_slug = $stored_pages[$slug['key']];
                $page_id = self::get_page_id_by_slug($bb_current_slug);
                wp_update_post([
                    'ID' => $page_id,
                    'post_name' => $slug['value'],
                ]);

                $updated_stored_pages[$slug['key']] = $slug['value'];
                $stored_pages_ids[$slug['key']] = $page_id;
            } else {
                $formatted_string = ucwords(str_replace('-', ' ', $slug['value']));
                $new_page_id = wp_insert_post([
                    'post_title' => $formatted_string,
                    'post_name' => $slug['value'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_author' => $current_user_id,
                    'page_template' => 'bb-brag.php'
                ]);

                if ($new_page_id) {
                    update_post_meta($new_page_id, '_wp_page_template', 'template/bb-brag.php');
                    $updated_stored_pages[$slug['key']] = $slug['value'];
                    $stored_pages_ids[$slug['key']] = $new_page_id;
                }
            }
        }

        $bb_merge_array = $updated_stored_pages + $stored_pages;
        $bb_merge_ids = $stored_pages_ids + $bb_pages_ids;
        update_option('bb_gallery_stored_pages', $bb_merge_array);
        update_option('bb_gallery_stored_pages_ids', $bb_merge_ids);
    }

    public static function send_form_data_and_create_post($data, $url, $name, $description, $email, $phone)
    {
        $jsonData = json_encode($data);

        $response = wp_remote_post( $url, array(
            'body'    => $jsonData,
            'headers' => array(
                'Content-Type'   => 'application/json',
                'Content-Length' => strlen( $jsonData ), // optional, WP usually sets this
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
        } else {
            $body = wp_remote_retrieve_body( $response );
            $responseData = json_decode( $body, true );
            // Now $responseData is your decoded array
        }

        if (isset($responseData['success']) && $responseData['success'] === true) {
            $post_id = wp_insert_post(array(
                'post_title' => $name,
                'post_content' => $description,
                'post_type' => 'form-entries',
                'post_status' => 'publish'
            ));

            if ($post_id) {
                update_post_meta($post_id, 'bb_email', $email);
                update_post_meta($post_id, 'bb_phone', $phone);
                wp_send_json_success('Thank you!');
            } else {
                wp_send_json_error('Form submission failed.');
            }
        }
    }

    public function handle_form_submission()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
            $name = sanitize_text_field($_POST['name']);
            $email = sanitize_email($_POST['email']);
            $phone = sanitize_text_field($_POST['phone']);
            $description = sanitize_textarea_field($_POST['description']);
            $api_tokens = get_option('bragbook_api_token');
            $websiteproperty_ids = get_option('bragbook_websiteproperty_id');
            $bb_gallery_stored_pages = get_option('bb_gallery_stored_pages');
            $combine_gallery_slug = get_option('combine_gallery_slug');

            $current_url = $_SERVER['HTTP_REFERER'];
            $parsed_url = parse_url($current_url);
            $parsed_url_parts = $parsed_url['path'];
            $bbragbook_url_trim = trim($parsed_url_parts, '/');
            $parts = explode('/', $bbragbook_url_trim);
            $index = array_search($parts[0], $bb_gallery_stored_pages);

            $data = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'details' => $description
            ];

            if ($parts[0] == $combine_gallery_slug) {
                foreach ($api_tokens as $api_token_index => $api_token_value) {
                    $websiteproperty_id = $websiteproperty_ids[$api_token_index];

                    $url = BB_BASE_URL . "/api/plugin/consultations?apiToken=" . $api_token_value . "&websitepropertyId=" . $websiteproperty_id;
                    self::send_form_data_and_create_post($data, $url, $name, $description, $email, $phone);
                }
            } else {
                $url = BB_BASE_URL . "/api/plugin/consultations?apiToken=" . $api_tokens[$index] . "&websitepropertyId=" . $websiteproperty_ids[$index];
                self::send_form_data_and_create_post($data, $url, $name, $description, $email, $phone);
            }

        } else {
            wp_send_json_error('Form submission failed.');
        }
        die();
    }

    public function bb_consultation_pagination_load_posts()
    {
        global $wpdb;
        $msg = '';
        if (isset($_POST['page'])) {
            $page = sanitize_text_field($_POST['page']);

            $cur_page = $page;
            $page -= 1;

            $per_page = 10;
            $previous_btn = true;
            $next_btn = true;
            $first_btn = true;
            $last_btn = true;
            $start = $page * $per_page;

            $table_name = $wpdb->prefix . "posts";
            $all_blog_posts = $wpdb->get_results($wpdb->prepare("
                SELECT * FROM " . $table_name . " WHERE post_type = 'form-entries' AND post_status = 'publish' ORDER BY post_date DESC LIMIT %d, %d", $start, $per_page));

            $count = $wpdb->get_var($wpdb->prepare("
                SELECT COUNT(ID) FROM " . $table_name . " WHERE post_type = 'form-entries' AND post_status = 'publish'", array()));

            foreach ($all_blog_posts as $key => $post):
                $post_date = date('Y-m-d H:i:s', strtotime($post->post_date));
                $msg .= '
                <tr>
                    <td>' . $post->post_title . '</td>
                    <td>' . get_post_meta($post->ID, 'bb_email', true) . '</td>
                    <td> ' . get_post_meta($post->ID, 'bb_phone', true) . '</td>
                    <td>' . $post_date . '</td>
                    <td colspan="4">' . $post->post_content . '</td>
                </tr>';
            endforeach;

            $no_of_paginations = ceil($count / $per_page);
            $start_loop = $cur_page;
            if ($no_of_paginations > $cur_page) {
                $end_loop = $cur_page;
            } else {
                $end_loop = $no_of_paginations;
            }

            $pag_container .= "
                <div class='bb-universal-pagination'>
                    <ul>";
            $pag_container .= "<li class='selected'>$count items</li>";
            if ($first_btn && $cur_page > 1) {
                $pag_container .= "<li p='1' class='active'><<</li>";
            } else if ($first_btn) {
                $pag_container .= "<li p='1' class='inactive'><<</li>";
            }

            if ($previous_btn && $cur_page > 1) {
                $pre = $cur_page - 1;
                $pag_container .= "<li p='$pre' class='active'><</li>";
            } else if ($previous_btn) {
                $pag_container .= "<li class='inactive'><</li>";
            }

            for ($i = $start_loop; $i <= $end_loop; $i++) {
                if ($cur_page == $i)
                    $pag_container .= "<li p='$i' class = 'selected' >$i of $no_of_paginations</li>";
                else
                    $pag_container .= "<li p='$i' class='active'>$i of $no_of_paginations</li>";
            }

            if ($next_btn && $cur_page < $no_of_paginations) {
                $nex = $cur_page + 1;
                $pag_container .= "<li p='$nex' class='active'>></li>";
            } else if ($next_btn) {
                $pag_container .= "<li class='inactive'>></li>";
            }

            if ($last_btn && $cur_page < $no_of_paginations) {
                $pag_container .= "<li p='$no_of_paginations' class='active'>>></li>";
            } else if ($last_btn) {
                $pag_container .= "<li p='$no_of_paginations' class='inactive'>>></li>";
            }

            $pag_container = $pag_container . "
                    </ul>
                </div>";

            $data = [
                'message' => $msg,
                'pagination' => $pag_container,
            ];
            $json_data = json_encode($data);
            header('Content-Type: application/json');
            echo $json_data;
        }
        exit();
    }

    public function form_entry_menu_page()
    {
        add_menu_page(
            'BRAG book Settings',
            'BRAG book Settings',
            'manage_options',
            'bragbook-settings',
            array($this, 'display_api_settings'),
            'dashicons-admin-generic',
            100
        );

        add_submenu_page(
            'bragbook-settings',
            'Consultation',
            'Consultation',
            'manage_options',
            'bb-consultation',
            array($this, 'display_form_entries')
        );

    }

    public function get_page_slug_by_id($page_id)
    {
        $slug = get_post_field('post_name', $page_id);
        return $slug ? $slug : '';
    }

    // Function to display the API Setting page
    public function display_api_settings()
    {
        ?>
        <div class="wrap">
            <h1><b>BRAG book API Settings</b></h1>
            <div id="accordion">
                <h3>Api Credentials</h3>
                <div>
                    <form method="post" id="bragbook_setting_page">
                        <?php
                        $combine_gallery_slug = '';
                        if (!empty(get_option('combine_gallery_slug'))) {
                            $combine_gallery_slug = get_option('combine_gallery_slug');
                        }
                        ?>
                        <div class="dynamic-api-table">
                            <table id="dynamicTable">
                                <thead>
                                    <tr>
                                        <th>API Token</th>
                                        <th>Website Property ID</th>
                                        <th>Gallery Page</th>
                                        <th>Seo Page Title</th>
                                        <th>Seo Page Description</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stored_pages = get_option('bb_gallery_stored_pages', []);
                                    $gallery_slugs = get_option('bb_gallery_page_slug', []);
                                    $api_tokens = get_option('bragbook_api_token', []);
                                    $website_ids = get_option('bragbook_websiteproperty_id', []);
                                    $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids');
                                    $bb_seo_pages_title = get_option('bb_seo_page_title', []);
                                    $bb_seo_pages_description = get_option('bb_seo_page_description', []);

                                    $num_rows = max(count($api_tokens), count($website_ids));
                                    $used_slugs = [];
                                    $i = 1;

                                    foreach ($stored_pages as $key => $value):
                                        if (isset($api_tokens[$key])) {
                                            $api_token = isset($api_tokens[$key]) ? esc_attr($api_tokens[$key]) : '';
                                            $website_id = isset($website_ids[$key]) ? esc_attr($website_ids[$key]) : '';
                                            $bb_seo_page_title = isset($bb_seo_pages_title[$key]) ? esc_attr($bb_seo_pages_title[$key]) : '';
                                            $bb_seo_page_description = isset($bb_seo_pages_description[$key]) ? esc_attr($bb_seo_pages_description[$key]) : '';
                                            $gallery_slug = $value;

                                            if (in_array($gallery_slug, $used_slugs)) {
                                                continue;
                                            } else {
                                                $used_slugs[] = $gallery_slug;
                                            }

                                            $page_id = '';
                                            $bb_slug_page = get_page_by_path($gallery_slug);
                                            if ($bb_slug_page) {
                                                $page_id = $bb_slug_page->ID;
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <input type="text" data-key="<?php echo $key; ?>"
                                                        name="bragbook_api_token[<?php echo $key; ?>]" value="<?php echo $api_token; ?>"
                                                        required>
                                                </td>
                                                <td>
                                                    <input type="text" data-key="<?php echo $key; ?>"
                                                        name="bragbook_websiteproperty_id[<?php echo $key; ?>]"
                                                        value="<?php echo $website_id; ?>" required>
                                                </td>
                                                <td>
                                                    <input type="text" data-key="<?php echo $key; ?>"
                                                        name="bb_gallery_page_slug[<?php echo $key; ?>]" value="<?php echo $value; ?>"
                                                        required>
                                                    <input type="hidden" name="bb_hidden_page_slug_with_id[<?php echo $i; ?>]"
                                                        value="<?php echo $page_id . '_' . $gallery_slug; ?>">

                                                </td>
                                                <td>
                                                    <input type="text" data-key="<?php echo $key; ?>"
                                                        name="bb_seo_page_title[<?php echo $key; ?>]"
                                                        value="<?php echo $bb_seo_page_title; ?>" required>
                                                </td>
                                                <td>
                                                    <input type="text" data-key="<?php echo $key; ?>"
                                                        name="bb_seo_page_description[<?php echo $key; ?>]"
                                                        value="<?php echo $bb_seo_page_description; ?>" required>
                                                </td>
                                                <td>
                                                    <button type="button" class="removeRow">Remove Row</button>
                                                    <button type="button" class="addRow">Add Row</button>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        $i++;
                                    endforeach; ?>

                                    <?php if (empty($api_tokens)): ?>
                                        <tr>
                                            <td>
                                                <input type="text" data-key="page_1" name="bragbook_api_token[page_1]" value=""
                                                    required>
                                            </td>
                                            <td>
                                                <input type="text" data-key="page_1" name="bragbook_websiteproperty_id[page_1]"
                                                    value="" required>
                                            </td>
                                            <td>
                                                <input type="text" data-key="page_1" name="bb_gallery_page_slug[page_1]" value=""
                                                    required>
                                            </td>
                                            <td>
                                                <button type="button" class="removeRow">Remove Row</button>
                                                <button type="button" class="addRow">Add Row</button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    const tableBody = document.querySelector("#dynamicTable tbody");

                                    function addRow() {
                                        let lastPageNumber = 0;
                                        const rows = tableBody.querySelectorAll('tr');

                                        rows.forEach(row => {
                                            const input = row.querySelector('input[data-key^="page_"]');
                                            if (input) {
                                                const currentPageNumber = parseInt(input.getAttribute('data-key').split('_')[1], 10);
                                                lastPageNumber = Math.max(lastPageNumber, currentPageNumber);
                                            }
                                        });

                                        const newRowNumber = lastPageNumber + 1;
                                        const newRow = document.createElement('tr');
                                        newRow.innerHTML = `
                                            <td>
                                                <input type="text" data-key="page_${newRowNumber}" name="bragbook_api_token[page_${newRowNumber}]" value="" required>
                                            </td>
                                            <td>
                                                <input type="text" data-key="page_${newRowNumber}" name="bragbook_websiteproperty_id[page_${newRowNumber}]" value="" required>
                                            </td>
                                            <td>
                                                <input type="text" data-key="page_${newRowNumber}" name="bb_gallery_page_slug[page_${newRowNumber}]" value="" required>
                                            </td>
                                            <td>
                                                <input type="text" data-key="page_${newRowNumber}" name="bb_seo_page_title[page_${newRowNumber}]" value="" required>
                                            </td>
                                            <td>
                                                <input type="text" data-key="page_${newRowNumber}" name="bb_seo_page_description[page_${newRowNumber}]" value="" required>
                                            </td>
                                            <td>
                                                <button type="button" class="removeRow">Remove Row</button>
                                                <button type="button" class="addRow">Add Row</button>
                                            </td>
                                        `;

                                        tableBody.appendChild(newRow);
                                        newRow.querySelector(".removeRow").addEventListener("click", function () {
                                            removeRow(newRow);
                                        });

                                        updateButtonVisibility();
                                    }

                                    function removeRow(row) {
                                        if (tableBody.rows.length > 1) {
                                            const parser = new DOMParser();
                                            const doc = parser.parseFromString(row.innerHTML, "text/html");
                                            const inputElement = doc.querySelector('[data-key^="page_"]');

                                            var bb_remove_id = '';
                                            if (inputElement) {
                                                var bb_remove_id = inputElement.dataset.key;

                                            } else {
                                                console.log("No element found with an id starting with 'page_'.");
                                            }

                                            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                                            jQuery.ajax({
                                                type: 'POST',
                                                url: ajaxurl,
                                                data: {
                                                    action: 'bb_setting_remove_row',
                                                    bb_remove_id: bb_remove_id
                                                },
                                                success: function (response) {
                                                    var successMessage = response.data;
                                                },
                                                error: function (xhr, status, error) {
                                                }
                                            });
                                            tableBody.removeChild(row);

                                            updateRowNumbers();
                                            updateButtonVisibility();
                                        }
                                    }

                                    function updateRowNumbers() {
                                        const rows = tableBody.querySelectorAll("tr");
                                        rows.forEach((row) => {
                                            const dataKeyValue = row.getAttribute('data-key');
                                            const rowNumber = dataKeyValue ? dataKeyValue.split('_')[1] : '';

                                            if (rowNumber) {
                                                row.querySelector("input[name^='bragbook_api_token']").setAttribute('name', `bragbook_api_token[page_${rowNumber}]`);
                                                row.querySelector("input[name^='bragbook_websiteproperty_id']").setAttribute('name', `bragbook_websiteproperty_id[page_${rowNumber}]`);
                                                row.querySelector("input[name^='bb_gallery_page_slug']").setAttribute('name', `bb_gallery_page_slug[page_${rowNumber}]`);
                                            }
                                        });
                                    }

                                    function updateButtonVisibility() {
                                        const rows = tableBody.querySelectorAll("tr");
                                        rows.forEach((row, index) => {
                                            const addButton = row.querySelector(".addRow");
                                            const removeButton = row.querySelector(".removeRow");

                                            addButton.style.display = (index === rows.length - 1) ? "inline-block" : "none";
                                            removeButton.style.display = (rows.length > 1) ? "inline-block" : "none";
                                        });
                                    }

                                    tableBody.addEventListener("click", function (event) {
                                        if (event.target && event.target.classList.contains("addRow")) {
                                            addRow();
                                        }
                                    });

                                    tableBody.addEventListener("click", function (event) {
                                        if (event.target && event.target.classList.contains("removeRow")) {
                                            const row = event.target.closest("tr");
                                            removeRow(row);
                                        }
                                    });
                                    updateButtonVisibility();

                                    const createCombineGalleryBtn = document.getElementById("createCombineGallery");
                                    const slugFieldContainer = document.getElementById("slugFieldContainer");
                                    const combineGallerySlug = document.querySelector('.combineGallerySlug').value;
                                    if (combineGallerySlug === "") {
                                        createCombineGalleryBtn.style.display = 'block';
                                        slugFieldContainer.style.display = 'none';
                                        createCombineGalleryBtn.addEventListener("click", function () {
                                            if (slugFieldContainer.style.display === "none" || slugFieldContainer.style.display === "") {
                                                slugFieldContainer.style.display = "block";
                                            } else {
                                                slugFieldContainer.style.display = "none";
                                            }
                                        });
                                    } else {
                                        createCombineGalleryBtn.style.display = 'none';
                                        slugFieldContainer.style.display = 'block';
                                    }
                                }); 
                            </script>
                        </div>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">Gallery Slug</th>
                                <td>
                                    <button type="button" id="createCombineGallery" style="display: none">Create Combine Gallery
                                        Page</button>
                                    <div id="slugFieldContainer" style="display: none">
                                        <br>
                                        <input type="text" id="combine_gallery_slug" class="combineGallerySlug"
                                            name="combine_gallery_slug" value="<?php echo esc_attr($combine_gallery_slug); ?>">
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">BRAG book Landing Page</th>
                                <td>
                                    <?php
                                    $content = get_option('bragbook_landing_page_text');
                                    if (empty($content)) {
                                        $content = '<h2>Go ahead, browse our before &amp; afters... visualize your possibilities.</h2>
                                        Our gallery is full of our real patients. Keep in mind results vary.
                                        [bragbook_carousel_shortcode category="Laser Skin Resurfacing" start="0" limit="10" title="0" details="0"]';
                                    }
                                    wp_editor($content, 'bragbook_landing_page_text', array('textarea_name' => 'bragbook_landing_page_text'));
                                    ?>
                                </td>
                            </tr>
                        </table>

                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">Select Installed SEO Plugin</th>
                                <td>
                                    <?php $selected_plugin = get_option('bb_seo_plugin_selector', ''); ?>
                                    <select name="bb_seo_plugin_selector" id="bb_seo_plugin_selector">
                                        <option value="">No SEO Plugins</option>
                                        <option value="1" <?php selected($selected_plugin, '1'); ?>>Yoast SEO</option>
                                        <option value="2" <?php selected($selected_plugin, '2'); ?>>All in One SEO</option>
                                        <option value="3" <?php selected($selected_plugin, '3'); ?>>Rank Math</option>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Update API</th>
                                <td>
                                    <button type="button" id="bb_update_api">Update API</button>
                                    <p class="update-api-status"></p>
                                    <span class="update-api"></span>
                                </td>
                            </tr>
                        </table>
                        <table class="form-table submit-button-table">
                            <tr valign="top">
                                <td>
                                    <?php submit_button(); ?>
                                    <p class="bb-save-api-settings-status"></p>
                                    <span class="bb-save-api-status"></span>
                                </td>
                            </tr>
                        </table>

                    </form>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $("#accordion").accordion();
            });
        </script>
        <?php
    }

    // Display form entries
    function display_form_entries()
    {
        ?>
        <div class="content">
            <div class="inner-box content no-right-margin darkviolet">
                <script type="text/javascript">
                    jQuery(document).ready(function ($) {
                        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                        function bb_load_all_posts(page) {
                            $(".bb_pag_loading").fadeIn().css('background', '#ccc');
                            var data = {
                                page: page,
                                action: "consultation-pagination-load-posts"
                            };

                            $.post(ajaxurl, data, function (response) {
                                $(".bb_universal_container").empty().append(response.message);
                                $(".bb-pagination-nav").empty().append(response.pagination);
                                $(".bb_pag_loading").css({ 'background': 'none', 'transition': 'all 1s ease-out' });
                            });
                        }

                        bb_load_all_posts(1);
                        $(document).on('click', '.bb-universal-pagination li.active', function () {
                            var page = $(this).attr('p');
                            bb_load_all_posts(page);
                        });
                    }); 
                </script>
                <div class="bb_pag_loading">
                    <div class="wrap">
                        <h2>BB Consultation</h2>
                        <table class="wp-list-table widefat fixed striped form-entries-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Date</th>
                                    <th colspan="4">Description</th>
                                </tr>
                            </thead>
                            <tbody class="bb_universal_container">
                            </tbody>
                        </table>
                        <div class="bb-pagination-nav"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    function bragbook_my_favorite_handler()
    {
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $name = sanitize_text_field($_POST['name']);
        $caseId = (int)$_POST['caseIds'][0];
        $caseIds = $_POST['caseIds'];
        $bbApiTokens = $_POST['bbApiTokens'];
        $bbWebsiteIds = $_POST['bbWebsiteIds'];
    
        $api_token = $bbApiTokens[0];
        $bbApiTokens = explode(", ", $bbApiTokens[0]); // Splitting the tokens by ", "
        $websiteproperty_id_array = array_map('intval', explode(", ", $bbWebsiteIds[0]));

        $response = wp_remote_post(BB_BASE_URL . '/api/plugin/combine/favorites/add', array(
            'method' => 'POST',
            'body' => json_encode(array(
                "apiTokens"=> $bbApiTokens,
                "websitePropertyIds"=> $websiteproperty_id_array,
                'email' => $email,
                'phone' => $phone,
                'name' => $name,
                'caseId' => $caseId,
            )),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));
        // echo "<pre>";
        // print_r(json_encode(array(
        //     "apiTokens"=> $bbApiTokens,
        //     "websitePropertyIds"=> $websiteproperty_id_array,
        //     'email' => $email,
        //     'phone' => $phone,
        //     'name' => $name,
        //     'caseId' => $caseIds,
        // )));
        // echo "</pre>";
        // die('here');
        // $response = wp_remote_post(BB_BASE_URL . '/api/plugin/favorites?apiToken=' . $api_token . '&websitepropertyId=' . $websiteproperty_id, array(
        //     'method' => 'POST',
        //     'body' => json_encode(array(
        //         'email' => $email,
        //         'phone' => $phone,
        //         'name' => $name,
        //         'caseIds' => $caseIds,
        //     )),
        //     'headers' => array(
        //         'Content-Type' => 'application/json',
        //     ),
        // ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body);

        if (isset($data->success) && $data->success) {
            $expireTime = time() + (365 * 24 * 60 * 60); // 1 year
            if (isset($_COOKIE['wordpress_favorite_email']) && $_COOKIE['wordpress_favorite_email'] === $email) {
                $newCaseIds = $caseIds;
                if (isset($_COOKIE['wordpress_favorite_case_id'])) {
                    $existingCaseIds = explode(',', $_COOKIE['wordpress_favorite_case_id']);
                    $allCaseIds = array_unique(array_merge($existingCaseIds, $newCaseIds));
                } else {
                    $allCaseIds = $newCaseIds;
                }
                $caseIdsString = implode(',', $allCaseIds);
            } else {
                $caseIdsString = implode(',', $caseIds);
            }
            $cookie_path = '/';

            setcookie('wordpress_favorite_email', $email, $expireTime, $cookie_path);
            setcookie('wordpress_favorite_case_id', $caseIdsString, $expireTime, $cookie_path);
            setcookie('wordpress_favorite_name', $name, $expireTime, $cookie_path);
            setcookie('wordpress_favorite_phone', $phone, $expireTime, $cookie_path);

            wp_send_json_success($data);
        } else {
            wp_send_json_error(array('message' => 'Failed to save favorite.'));
        }
        die();
    }

    // SEO stuff for plugin selection
    public function bb_get_current_url()
    {
        $current_link = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $current_link .= "s";
        }
        $current_link .= "://";
        $current_link .= "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        return $current_link;
    }

    public function get_custom_bragbook_title_and_description()
    {
        $site_title = get_bloginfo('name');
        $brag_page_data = get_option('bragbook_landing_page_text');
        $explode_string = explode('[', $brag_page_data);
        if (isset($explode_string[1])) {
            $explode_string[1] = '[' . $explode_string[1];
        }

        $bb_content = $explode_string['0'];
        if (preg_match('/^(<[^>]+>.*?<\/[^>]+>)(.*)$/s', $bb_content, $matches)) {
            $first_element = trim($matches[1]);
            $text_after_first_tag = trim($matches[2]);
        }
        $bbrag_case_url = strtok($_SERVER["REQUEST_URI"], '?');
        $bbragbook_case_url = trim($bbrag_case_url, '/');

        $parts = explode('/', $bbragbook_case_url);

        $bb_seo_title = null;
        $bb_seo_description = null;
        $combine_gallery_page_id = get_option('combine_gallery_page_id');
        $combine_gallery_page = get_post($combine_gallery_page_id);
        $combine_gallery_page_slug = get_option('combine_gallery_slug');
        $api_tokens = get_option('bragbook_api_token', []);
        $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
        $gallery_slugs = get_option('bb_gallery_page_slug', []);
        $bb_seo_pages_title = get_option('bb_seo_page_title', []);
        $bb_seo_pages_description = get_option('bb_seo_page_description', []);

        //Get caseId from URL if exists
        $caseId = null;
        $seoSuffixUrl = null;
        $procedureName = null;
        $procedureTotalCase = null;

        if(isset($parts[0]) && empty($parts[1]) && empty($parts[2])){
            if($combine_gallery_page_slug == $parts[0]){

            } else {
                foreach ($api_tokens as $index => $api_token) {
                        $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
                        $page_slug_bb = $gallery_slugs[$index] ?? '';
                    if (($page_slug_bb == $parts[0])) {
                        $bb_seo_title = $bb_seo_pages_title[$index] ?? '';
                        $bb_seo_description = $bb_seo_pages_description[$index] ?? '';
                        if (empty($api_token) || empty($websiteproperty_id)) {
                            continue;
                        }
                    }
                }
            }
        }
        if(isset($parts[1]) && empty($parts[2])){
            if($combine_gallery_page_slug == $parts[0]){
                $bb_sidebar_url = BB_BASE_URL . "/api/plugin/combine/sidebar";
                $procedureIdsName = $this->getProcedureIDFromSidebar(array_values($api_tokens), $parts[1], $bb_sidebar_url, true);
                $procedureTotalCase = $procedureIdsName["procedureTotalCase"];
            } else {
                foreach ($api_tokens as $index => $api_token) {
                    $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
                    $page_slug_bb = $gallery_slugs[$index] ?? '';
                    if (($page_slug_bb == $parts[0]) || ($combine_gallery_page_slug == $parts[0])) {
                        if (empty($api_token) || empty($websiteproperty_id)) {
                            continue;
                        }
                        $bb_sidebar_url = BB_BASE_URL . "/api/plugin/sidebar?apiToken={$api_token}";
                        $procedureIdsName = $this->getProcedureIDFromSidebar($api_token, $parts[1], $bb_sidebar_url, false);
                        $procedureTotalCase = $procedureIdsName["procedureTotalCase"];
                    }
                }
            }
        }
        if (isset($parts[2]) && !empty($parts[2])) {
            if (strpos($parts[2], 'bb-case') !== false) {
                // Use preg_match to extract the number after 'bb-case-'
                preg_match('/\d+/', $parts[2], $matches);
                $caseId = isset($matches[0]) ? (int) $matches[0] : 'Default string';
            } else {
                $seoSuffixUrl = $parts[2];
            }
            // Get case data for combine pages
            if ($combine_gallery_page_slug == $parts[0]) {
                // get procedureIds from sidebar API
                $bb_sidebar_url = BB_BASE_URL . "/api/plugin/combine/sidebar";
                $procedureIdsName = $this->getProcedureIDFromSidebar(array_values($api_tokens), $parts[1], $bb_sidebar_url, true);
                $procedureIds = $procedureIdsName["bb_procedure_id"];
                $procedureName = $procedureIdsName["bb_procedure_name"];
                $caseId = $caseId ? $caseId : '123';
                $url = BB_BASE_URL . "/api/plugin/combine/cases/$caseId?seoSuffixUrl=$seoSuffixUrl";

                $json_body = json_encode(array(
                    'apiTokens' => array_values($api_tokens),
                    'procedureIds' => $procedureIds,
                    'websitePropertyIds' => array_map('intval', array_values($websiteproperty_ids)),
                ));

                $response = wp_remote_post($url, array(
                    'method' => 'POST',
                    'body' => $json_body,
                    'headers' => array(
                        'Content-Type' => 'application/json',
                    ),
                ));
                
                if (is_wp_error($response)) {
                    wp_send_json_error(array('message' => $response->get_error_message()));
                }
                
                $data = wp_remote_retrieve_body($response);
                
                $bb_api_data = json_decode($data, true);
        
            } else {
                // Get case data for single page
                foreach ($api_tokens as $index => $api_token) {
                    $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
                    $page_slug_bb = $gallery_slugs[$index] ?? '';
    
                    if (($page_slug_bb == $parts[0]) || ($combine_gallery_page_slug == $parts[0])) {
                        if (empty($api_token) || empty($websiteproperty_id)) {
                            continue;
                        }
                        // get procedureIds from sidebar API
                        $bb_sidebar_url = BB_BASE_URL . "/api/plugin/sidebar?apiToken={$api_token}";
                        $procedureIdsName = $this->getProcedureIDFromSidebar($api_token, $parts[1], $bb_sidebar_url, false);
                        $procedureId = $procedureIdsName["bb_procedure_id"];
                        $procedureName = $procedureIdsName["bb_procedure_name"];
                        $url = BB_BASE_URL . "/api/plugin/cases?websitepropertyId={$websiteproperty_id}&apiToken={$api_token}&caseId={$caseId}&seoSuffixUrl={$seoSuffixUrl}&procedureId={$procedureId}";
                        $data = get_transient($url);

						if ( false === $data ) {
							$response = wp_remote_get( $url );
							if ( is_wp_error( $response ) ) return;
							$data = wp_remote_retrieve_body( $response );
							set_transient( md5( $url ), $data, 3600 );
						}
    
                        $bb_api_data = json_decode($data, true);
                    }
                }
            }
            if (strpos($parts[2], 'bb-case') !== false) {
                // Use preg_match to extract the caseID after 'bb-case-'
                preg_match('/\d+/', $parts[2], $matches);
                $bbrag_case_id = isset($matches[0]) ? (int)$matches[0] : '';
            } else {
                $bbrag_case_id = $parts[2];
            }

            $bb_response = $bb_api_data['data'][0];
            $bb_case_ids =  $bb_api_data['data'][0]['caseIds'];
            $bb_seo_case_title = "";
            $bb_seo_case_description = "";
            $bb_case_number = null;
            foreach ($bb_case_ids as $key => $bb_case_item) {
                if (($bb_case_item['id'] == $bbrag_case_id && strpos($parts[2], 'bb-case') !== false) || 
                    ($bb_case_item['seoSuffixUrl'] == $bbrag_case_id && strpos($parts[2], 'bb-case') === false)) {
                    $bb_case_number = ++$key;
                    break;
                }
            }
            if (!empty($bb_api_data) && is_array($bb_api_data)) {
                if ($bbrag_case_id == $bb_response['id'] || $bbrag_case_id == $bb_response['caseDetails'][0]['seoSuffixUrl']) {
                    if (isset($bb_response['caseDetails'][0]) && !empty($bb_response['caseDetails'][0]['seoPageTitle']) && $bb_seo_case_title == "") {
                        $bb_seo_title = $bb_seo_case_title = isset($bb_response['caseDetails'][0]) ? $bb_response['caseDetails'][0]['seoPageTitle'] . " - " . $site_title : "";
                    } else {
                        $bb_seo_title = "Before and After " . $procedureName . ": Patient " . $bb_case_number . " - " . $site_title;
                    }
                    if (isset($bb_response['caseDetails'][0]) && !empty($bb_response['caseDetails'][0]['seoPageDescription']) && $bb_seo_case_description == "") {
                        $bb_seo_description = $bb_seo_case_description = isset($bb_response['caseDetails'][0]) ? $bb_response['caseDetails'][0]['seoPageDescription'] : "";
                    }
                }
            }
        }
        if (count($parts) == 2) {
            if($parts[1] == "favorites" || $parts[1] == "consultation"){
                $procedureTotalCase = null;
            }
            $bbrag_procedure_title = $parts[1];
            $bb_pro_title_all_seo = ucwords(str_replace("-", " ", $bbrag_procedure_title));
            $bb_seo_title = "Before and After " . $bb_pro_title_all_seo . " Gallery, " . $procedureTotalCase . " Cases - " . $site_title;
        }
        $bb_title_description_array = ['bb_title' => $bb_seo_title, 'bb_description' => $bb_seo_description, 'bb_procedure_name' => $procedureName];
        return $bb_title_description_array;
    }

    public function getProcedureIDFromSidebar($api_tokens, $procedureSlug, $bb_sidebar_url, $iscombine) {
        $procedureName = [];
        $bbprocedureTotalCase = [];
        if($iscombine) {
            $cacheKey = "$procedureSlug-combine";
            // Get sidebar data from cache
            // $sidebar_list = get_transient($cacheKey);
            $sidebar_list = '';
            if (!$sidebar_list) {
                $response = wp_remote_post($bb_sidebar_url, array(
                    'method' => 'POST',
                    'body' => json_encode(array(
                        'apiTokens' => $api_tokens
                    )),
                    'headers' => array(
                        'Content-Type' => 'application/json',
                    ),
                ));
                
                if (is_wp_error($response)) {
                    return null;
                }
                $sidebar_list = wp_remote_retrieve_body($response);
                set_transient($cacheKey, $sidebar_list, HOUR_IN_SECONDS); 
                $sidebar = json_decode($sidebar_list);
                $procedureIds = [];
                if (isset($sidebar) && isset($sidebar->data)) {
                    foreach ($sidebar->data as $category) {
                        foreach ($category->procedures as $procedure) {
                            if ($procedure->slugName == $procedureSlug) {
                                $procedureIds = $procedure->ids; 
                                $procedureName = $procedure->name;
                                $bbprocedureTotalCase = $procedure->totalCase;  
                            }
                        }
                    }
                }
            }
        } else {
            $cacheKey = "$procedureSlug-single";
            $sidebar_list = get_transient($cacheKey);
            if (!$sidebar_list) {
                // If cache is empty, fetch data from API
                $response = wp_remote_get($bb_sidebar_url);
                if (is_wp_error($response)) {
                    return null;
                }
                $sidebar_list = wp_remote_retrieve_body($response);
                set_transient($cacheKey, $sidebar_list, HOUR_IN_SECONDS);
            }
    
            $sidebar = json_decode($sidebar_list);
            $procedureIds = null;
            if (isset($sidebar) && isset($sidebar->data)) {
                foreach ($sidebar->data as $category) {
                    foreach ($category->procedures as $procedure) {
                        if ($procedure->slugName == $procedureSlug) {
                            $procedureIds = $procedure->id; 
                            $procedureName = $procedure->name; 
                            $bbprocedureTotalCase = $procedure->totalCase; 
                            break 2; 
                        }
                    }
                }
            }
        }
        $bb_procedure_id_name_array = ['bb_procedure_id' => $procedureIds, 'bb_procedure_name' => $procedureName, 'procedureTotalCase' => $bbprocedureTotalCase];
        return $bb_procedure_id_name_array;
    }

    public function getSingleProcedureIDFromSidebar($api_token, $procedureSlug)
    {
        $bb_sidebar_url = BB_BASE_URL . "/api/plugin/sidebar?apiToken={$api_token}";
        $sidebar_list = get_transient($bb_sidebar_url);

        if (!$sidebar_list) {
            // If cache is empty, fetch data from API
            $response = wp_remote_get($bb_sidebar_url);
            if (is_wp_error($response)) {
                return null;
            }
            $sidebar_list = wp_remote_retrieve_body($response);
            set_transient($bb_sidebar_url, $sidebar_list, HOUR_IN_SECONDS); 
        }

        $sidebar = json_decode($sidebar_list);
        $procedureId = null;
        if (isset($sidebar) && isset($sidebar->data)) {
            foreach ($sidebar->data as $category) {
                foreach ($category->procedures as $procedure) {
                    if ($procedure->slugName == $procedureSlug) {
                        $procedureId = $procedure->id; 
                        break 2; 
                    }
                }
            }
        }
        return $procedureId;
    }


    public function bb_get_custom_bragbook_title()
    {
        // Get SEO data from constructor
        $brag_book_title = $this->seoData;
        return $brag_book_title['bb_title'];
    }

    public function bb_get_custom_bragbook_description()
    {
        $brag_book_description = $this->seoData;
        return $brag_book_description['bb_description'];
    }

    public function bb_nudity_warning() 
    { 
        ?>
        <div class="popup-overlay" id="popup">
            <div class="popup">
                <h3><span>WARNING:</span> These galleries contain nudity.</h3>
                <p> if you are offended by such material or are under 18 years of age, please do not proceed</p>
                <div class="popup-btn">
                <button onclick="closePopup()">proceed</button>
                <button onclick="leavePopup()">do not proceed</button>
                </div>
            </div>
        </div>
    <?php 
    }

    public function bb_print_custom_bragbook_description()
    {
        echo '<meta name="description" content="' . $this->bb_get_custom_bragbook_description() . '">';
    }

    public function bb_print_canonical()
    {
        echo '<link rel="canonical" href="' . $this->bb_get_current_url() . '">';
    }

    public static function bb_page_format_strings($input)
    {
        $lowercaseUnderscore = strtolower($input);
        $lowercaseUnderscore = preg_replace('/[^\w]/', '-', $lowercaseUnderscore);
        $lowercaseUnderscore = str_replace(' ', '-', $lowercaseUnderscore);

        $capitalizedSpace = preg_replace('/[^\w]/', ' ', $input);
        $capitalizedSpace = ucwords(strtolower($capitalizedSpace));
        $capitalizedSpace = trim($capitalizedSpace);

        return [$lowercaseUnderscore, $capitalizedSpace];
    }

    function bb_delete_default_page($post_id)
    {
        if (get_post_type($post_id) === 'page') {
            $page_slug = get_post_field('post_name', $post_id);
            $combine_gallery_slug = get_option('combine_gallery_slug');
            if ($page_slug == $combine_gallery_slug) {
                update_option('combine_gallery_slug', "");
            }

            $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids');
            $bb_remove_id = array_search($post_id, $bb_page_list_gallery);

            $bb_id_list = get_option('bb_gallery_stored_pages');
            $bb_id_list_gallery = get_option('bb_gallery_page_slug');
            $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids');
            $bragbook_websiteproperty_id = get_option('bragbook_websiteproperty_id');
            $bragbook_api_token = get_option('bragbook_api_token');

            update_option('bb_remove_pages_from_setting', $bb_id_list_gallery);
            update_option('bb_remove_combine_gallery_from_setting', $combine_gallery_slug);

            unset($bb_id_list_gallery[$bb_remove_id]);
            unset($bb_id_list[$bb_remove_id]);
            unset($bb_page_list_gallery[$bb_remove_id]);
            unset($bragbook_websiteproperty_id[$bb_remove_id]);
            unset($bragbook_api_token[$bb_remove_id]);

            update_option('bb_gallery_page_slug', $bb_id_list_gallery);
            update_option('bb_gallery_stored_pages_ids', $bb_page_list_gallery);
            update_option('bragbook_websiteproperty_id', $bragbook_websiteproperty_id);
            update_option('bragbook_api_token', $bragbook_api_token);
            update_option('bb_gallery_stored_pages', $bb_id_list);
        }
    }

    private function update_page_slug_by_id($page_id, $new_slug)
    {
        if ($page_id) {
            $updated = wp_update_post(array(
                'ID' => $page_id,
                'post_name' => sanitize_title($new_slug),
            ));
            if (is_wp_error($updated)) {
                return false;
            }
            return true;
        }
        return false;
    }

    function bb_get_sitemap_data(){

        $api_tokens = get_option('bragbook_api_token', []);
        $websiteproperty_ids = array_values(get_option('bragbook_websiteproperty_id', []));

        $url = BB_BASE_URL . "/api/plugin/sitemap";
        $response = wp_remote_post($url, array(
            'method' => 'POST',
            'body' => json_encode(array(
                'apiTokens' => array_values($api_tokens),
                'websitePropertyIds' => array_map('intval', $websiteproperty_ids),
            )),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        }

        $response_body = wp_remote_retrieve_body($response);
        $response = json_decode($response_body);

        return $response;
    }

     function create_bragbook_sitemap(){

        $pageURL = (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") ? 'https://' : 'http://';
        $siteURL = $pageURL . $_SERVER['HTTP_HOST'] . '/';

        $pluginURL = plugin_dir_url(__DIR__) . '../bragbook-sitemap-style.xsl';

        $revXmlSitemapOutput = '<?xml version="1.0" encoding="UTF-8"?>';
        $revXmlSitemapOutput .= '<?xml-stylesheet type="text/xsl" href="' . $pluginURL . '"?>';
        $revXmlSitemapOutput .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $bb_sitemap_data = $this->bb_get_sitemap_data()->data;

        $gallery_slugs = array_values(get_option('bb_gallery_page_slug', []));
        foreach ($bb_sitemap_data as $index => $org_data) {
   
            foreach ($org_data as $sitemap_slug) {
                $sitemap_url =  home_url() . "/" . $gallery_slugs[$index] . $sitemap_slug->url;
                $sitemap_url_date = $sitemap_slug->updatedAt ?? "";
                $revXmlSitemapOutput .= '<url><loc>' . $sitemap_url . '</loc><lastmod>' . $sitemap_url_date . '</lastmod></url>';
            }
        }
        $revXmlSitemapOutput .= '</urlset>';

        $dom = new \DOMDocument;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($revXmlSitemapOutput);
        $dom->save($_SERVER['DOCUMENT_ROOT'] . '/bragbook-sitemap.xml');
      }

    function add_bragbook_sitemap_to_yoast() {

        $timezone = get_option('timezone_string');
        if (!$timezone) {
            $gmt_offset = get_option('gmt_offset');
            $timezone = timezone_name_from_abbr('', $gmt_offset * 3600, 0);

            if ($timezone === false) {
                $timezone = 'UTC';
            }
        }
        $date = new \DateTime('now', new \DateTimeZone($timezone));

        $appended_text = '';

        $static_post_link = home_url() . '/bragbook-sitemap.xml';
        $static_lastmod = $date->format('c');
        $appended_text .= '<sitemap>' .
                            '<loc>' . esc_url($static_post_link) . '</loc>' .
                            '<lastmod>' . $static_lastmod . '</lastmod>' .
                        '</sitemap>';

        return $appended_text;
    }

      public function bb_seo()
    {
        if (is_admin()) {
            return;
        }

        $current_page_id = get_queried_object_id();
        $stored_pages_ids = get_option('bb_gallery_stored_pages_ids');
        $combine_gallery_page_id = get_option('combine_gallery_page_id');

        if ((is_array($stored_pages_ids) && in_array($current_page_id, $stored_pages_ids)) || $current_page_id == $combine_gallery_page_id) {
             if (get_option('bb_seo_plugin_selector') == 1) {
                add_filter('wpseo_canonical', array($this, 'bb_get_current_url'));
                add_filter('wpseo_title', array($this, 'bb_get_custom_bragbook_title'));
                add_filter('wpseo_metadesc', array($this, 'bb_get_custom_bragbook_description'));
                add_filter('wpseo_canonical', array($this, 'bb_get_current_url'));
                add_filter('wpseo_opengraph_title', array($this, 'bb_get_custom_bragbook_title'));
                add_filter('wpseo_opengraph_desc', array($this, 'bb_get_custom_bragbook_description'));
                add_filter('wpseo_opengraph_url', array($this, 'bb_get_current_url'));
            } 
             else {
                if (get_option('bb_seo_plugin_selector') == 2) {
                    add_filter('aioseo_canonical_url', array($this, 'bb_get_current_url'));
                    add_filter('aioseo_title', array($this, 'bb_get_custom_bragbook_title'));
                    add_filter('aioseo_description', array($this, 'bb_get_custom_bragbook_description'));
                } elseif (get_option('bb_seo_plugin_selector') == 3) {
                    add_filter('rank_math/frontend/canonical', array($this, 'bb_get_current_url'));
                    add_filter('rank_math/frontend/title', array($this, 'bb_get_custom_bragbook_title'));
                    add_filter('rank_math/frontend/description', array($this, 'bb_get_custom_bragbook_description'));
                } else {
                    add_filter('wp_title', array($this, 'bb_get_custom_bragbook_title'), 999, 0);
                    add_filter('pre_get_document_title', array($this, 'bb_get_custom_bragbook_title'), 999, 0);
                    add_action('wp_head', array($this, 'bb_print_custom_bragbook_description'));
                    remove_action('wp_head', 'rel_canonical');
                    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
                    add_action('wp_head', array($this, 'bb_print_canonical'));
                }
            }
        }
    }
}
<?php
namespace mvpbrag;

class Shortcode {
    
    public static function register() {
        // Hook into 'init' to add custom rewrite rules
        add_action('init', [ __CLASS__, 'custom_rewrite_flush']);
        add_shortcode('brag_book_gallery', [ __CLASS__, 'mvp_brag_shortcode' ]);
        add_shortcode('bragbook_carousel_shortcode', [ __CLASS__, 'mvp_carousel_shortcode' ]); 
        add_shortcode('bb_bragbook_category', [ __CLASS__, 'bb_mvp_category_shortcode' ]); 
        add_shortcode('bb_bragbook_procedure', [ __CLASS__, 'bb_mvp_category_shortcode' ]); 
        add_shortcode('bb_bragbook_set', [ __CLASS__, 'mvp_bragbook_set_shortcode' ]); 
        add_shortcode('bb_bragbook_home_menu', [ __CLASS__, 'mvp_bragbook_home_menu_shortcode' ]);
 
    }
    
    public static function bb_limitWords($text, $wordLimit) {
        if (!is_string($text)) {
            $text = '';
        }
        $words = explode(' ', $text);
        $words = array_slice($words, 0, $wordLimit);
        $limitedText = implode(' ', $words);
    
        return $limitedText;
    }

    // Define the custom rewrite rule function
    public static function custom_rewrite_rules() {
        
       $page_id = ''; 
       $stored_pages = get_option('bb_gallery_stored_pages', []);
       foreach($stored_pages as $bb_page_key => $bb_page_value) {
            $page = get_page_by_path($bb_page_value, OBJECT, 'page');
            if ($page) {
                $page_id = $page->ID; 
            }
           
            $bragbook_post = get_post($page_id);
            if(isset($bragbook_post->post_name)) {
                $page_slug = $bragbook_post->post_name;
    
                add_rewrite_rule(
                    "^$page_slug/([^/]+)/([^/]+)/?$",
                    'index.php?pagename=' . $page_slug . '&procedure_title=$matches[1]&case_id=$matches[2]',
                    'top'
                );
    
                add_rewrite_rule(
                    "^$page_slug/([^/]+)/?$",
                    'index.php?pagename=' . $page_slug . '&procedure_title=$matches[1]',
                    'top'
                );
                
                add_rewrite_rule(
                    "^$page_slug/favorites/([^/]+)/([^/]+)/?$",
                    'index.php?pagename=' . $page_slug . '&favorites_section=$matches[1]&procedure_title=$matches[2]&case_id=$matches[3]',
                    'top'
                );

                add_rewrite_rule(
                    "^$page_slug/favorites/([^/]+)/?$",
                    'index.php?pagename=' . $page_slug . '&favorites_section=$matches[1]&procedure_title=$matches[2]',
                    'top'
                );
            }
        }

        $combine_gallery_page_id =  get_option('combine_gallery_page_id');
        $combine_gallery_page = get_post($combine_gallery_page_id);
       
        $combine_gallery_page_slug = "";
        if($combine_gallery_page !== "" && is_a($combine_gallery_page, 'WP_Post')) {
            $combine_gallery_page_slug = $combine_gallery_page->post_name;
            
            if(isset($combine_gallery_page->post_name)) {
                $combine_gallery_page_slug = $combine_gallery_page->post_name;
    
                add_rewrite_rule(
                    "^$combine_gallery_page_slug/([^/]+)/([^/]+)/?$",
                    'index.php?pagename=' . $combine_gallery_page_slug . '&procedure_title=$matches[1]&case_id=$matches[2]',
                    'top'
                );
                
                add_rewrite_rule(
                    "^$combine_gallery_page_slug/([^/]+)/?$",
                    'index.php?pagename=' . $combine_gallery_page_slug . '&procedure_title=$matches[1]',
                    'top'
                );
                add_rewrite_rule(
                    "^$combine_gallery_page_slug/favorites/([^/]+)/([^/]+)/?$",
                    'index.php?pagename=' . $combine_gallery_page_slug . '&favorites_section=$matches[1]&procedure_title=$matches[2]&case_id=$matches[3]',
                    'top'
                );
    
                add_rewrite_rule(
                    "^$combine_gallery_page_slug/favorites/([^/]+)/?$",
                    'index.php?pagename=' . $combine_gallery_page_slug . '&favorites_section=$matches[1]&procedure_title=$matches[2]',
                    'top'
                );
            }
        }
    } 
    
    public static function custom_rewrite_flush() {
        self::custom_rewrite_rules();
        flush_rewrite_rules();
    }

    // Get JSON file for category feed
	public static function bb_get_grabbook_category_feed($url) {
		$cats_json = self::bb_get_grabbook_api($url);
		return $cats_json;
	}

    public static function bb_get_grabbook_api($url) {
        $bb_set_transient_urls = get_option( 'bb_set_transient_url', [] );
        if ( ! is_array( $bb_set_transient_urls ) ) {
            $bb_set_transient_urls = [];
        }

		if (get_transient($url) !== false) {
			return get_transient($url);
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$data = curl_exec($ch);
		curl_close($ch);

        $bb_set_transient_urls[$url] = $data;
        update_option( 'bb_set_transient_url', $bb_set_transient_urls );
        
		set_transient($url, $data, 1800);
		return $data;
	}

    public static function mvp_brag_shortcode($atts) {
        $api_tokens = get_option('bragbook_api_token', []);
        $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
        $gallery_slugs = get_option('bb_gallery_page_slug', []);

        $all_results = [];
        $bb_website_property_id_slug = [];
        foreach ($api_tokens as $index => $api_token) {
            $websiteproperty_id = isset($websiteproperty_ids[$index]) ? $websiteproperty_ids[$index] : '';
            $page_slug_bb = isset($gallery_slugs[$index]) ? $gallery_slugs[$index] : '';
            if (empty($api_token) || empty($websiteproperty_id)) {
                continue;
            }
    
            $cat_url = "https://bragbookv2.com/api/plugin/categories?apiToken=" . $api_token . "&websitepropertyId=" . $websiteproperty_id;
            $category_list = self::bb_get_grabbook_category_feed($cat_url);
    
            $cat_set = json_decode($category_list, true);
    
            $url = "https://bragbookv2.com/api/plugin/cases?apiToken=" . $api_token . "&websitepropertyId=" . $websiteproperty_id;
            $data = self::bb_get_grabbook_api($url);
            $api_data = json_decode($data, true);
    
            $result = [
                'categories' => $cat_set,
                'api_data' => $api_data
            ];
    
            $all_results[$page_slug_bb] = $result;
            $bb_website_property_id_slug[$page_slug_bb] = $websiteproperty_id;
            update_option('bb_website_property_id_slug', $bb_website_property_id_slug);
        }
    
        $bragbook_api_information = json_encode($all_results);
        update_option("bb_api_data_short", $bragbook_api_information);

        return $bragbook_api_information;
	}
    
    public static function mvp_carousel_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'category' => '',
                'procedure' => '',
                'limit' => 10,
                'title' => '0',
                'details' => '0',
                'start' => '0',
                'website_property_id' => '0'
            ), 
            $atts
        );
        $url = "https://nextjs-bragbook-app-dev.vercel.app/api/plugin/cases/paginate?websitePropertyId=2&count=1&apiToken=0a910fcd-371e-454f-9615-749807690c92&procedureId=138";
        $short_data = get_transient($url);
        
        $cat_name = empty($atts['category']) ? $atts['procedure'] : $atts['category'];
        $cat_limit = $atts['limit'];
        $cat_title = $atts['title'];
        $cat_details = $atts['details'];
        $cat_start = $atts['start'];
        $cat_website_property_id = $atts['website_property_id'];
        
       // self::mvp_brag_shortcode($atts);
       // $data = get_option('bb_api_data_short');
       // $result = json_decode($data, true);
        $result = json_decode($short_data, true);
        $api_data = [];
        $categories = [];
        $bb_slug_count = 1;
        
        // foreach ($result as $key => $value) {
        //    // if(!empty($value['api_data']) && is_array($value['api_data'])) {
        //         foreach ($value['api_data'] as $index => $api_item) {
        //             $new_data = ["page_slug" => $key];
        //             $id_position = array_search('id', array_keys($api_item));
        //             $result[$key]['api_data'][$index] = array_merge(
        //                 array_slice($api_item, 0, $id_position + 1),
        //                 $new_data,
        //                 array_slice($api_item, $id_position + 1)
        //             );
        //         }
        //    // }
        // }

        // foreach ($result as $page_slug => $item) {
        //     if (isset($item['api_data'])) {
        //         $api_data = array_merge($api_data, $item['api_data']);
        //     }
        //     if (isset($item['categories'])) {
        //         $categories = array_merge($categories, $item['categories']);
        //     }
        // }
        
        // $categorized_procedures = [];
        // $bb_categorized_procedures_count = 1;
        // if (!empty($categories) && is_array($categories)) {
        //     foreach ($categories as $category_key => $category) {
        //         $case_counts = [];
        //         foreach ($category['procedures'] as $procedure_key => $procedure) {
        //             $p_case_count = 0; 
        //             if (!empty($api_data) && is_array($api_data)) {
        //                 foreach ($api_data as $key => $item) {
        //                     if (in_array($procedure['id'], $item['procedureIds'])) {
        //                         if (!empty($item['photoSets'])) {
        //                             $p_case_count++;
        //                         }
        //                         $bb_categorized_procedures_count++;
        //                     }
        //                 }
        //             }
        //             $case_counts[$procedure_key] = $p_case_count;
        //         }

        //         foreach ($category['procedures'] as $procedure_key => $procedure) {
        //             $categories[$category_key]['procedures'][$procedure_key]['case_count'] = $case_counts[$procedure_key];
        //         }
        //     }
        // }

        // if (!empty($categories) && is_array($categories)) {
        //     foreach ($categories as $category) {
        //         $procedures_cat_data = [];
        //         $bb_procedures_cat_data_count = 1;
        //         if (!empty($api_data) && is_array($api_data)) {
        //             foreach ($api_data as $key => $item) {
        //                 foreach ($category['procedures'] as $procedure) {
        //                     if (in_array($procedure['id'], $item['procedureIds'])) {
        //                         if (!empty($item['photoSets'])) { 
        //                             $procedures_cat_data[] = $procedure['id']; 
        //                         }
        //                         $bb_procedures_cat_data_count++;
        //                     }
        //                 }
        //             }
        //         }
        //         $categorized_procedures[$category['id']] = [
        //             'category_name' => $category['name'],
        //             'procedures_count' => count($procedures_cat_data),
        //             'procedures_data' => $category['procedures'],
        //         ];
        //     }
        // }

        // $matching_data = [];
        // $bb_matching_data_count = 1;
        // if (!empty($api_data) && is_array($api_data)) {
        //     foreach ($api_data as $key => $item) {
        //         foreach ($categorized_procedures as $category_id => $category_data) {
        //             $procedures_data = $category_data['procedures_data'];
        //             if (is_array($procedures_data) && is_array($item)) {
        //                 foreach ($procedures_data as $complete_category) {
        //                     $b_converted_procedure_name = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($complete_category['name']));
        //                     if (in_array($complete_category['id'], $item['procedureIds']) && ($cat_name == $complete_category['name'] || $cat_name == $b_converted_procedure_name)) {
        //                         if (!empty($item['photoSets'])) { 
        //                             if (!isset($procedure_counts[$complete_category['id']])) {
        //                                 $procedure_counts[$complete_category['id']] = 0;
        //                             }
        //                             $procedure_counts[$complete_category['id']]++;
        //                             $item['procedure_title'] = $complete_category['name'];
        //                             $item['procedure_case_count'] = $procedure_counts[$complete_category['id']];
        //                             $item['procedure_id'] = $complete_category['id'];
        //                             $item['description'] = $complete_category['description'];
        //                             $matching_data[] = $item;
        //                         }
        //                     }
        //                 }
        //             }
        //         }
        //         $bb_matching_data_count++;
        //     }
        // }
        
        $bb_all_gallery_slugs = get_option('bb_gallery_page_slug', []);
        $bb_combine_gallery_slug = get_option('combine_gallery_slug');
        $bbrag_case_url = strtok($_SERVER["REQUEST_URI"], '?');
        $bbragbook_case_url = trim($bbrag_case_url, '/');
        $parts = explode('/', $bbragbook_case_url);
        $page_url_combine = get_page_by_path($bb_combine_gallery_slug);

        if ($page_url_combine) {
            $bb_page_exist = true;
        } else {
            $bb_page_exist = false;
        }  

        ob_start();
        ?>
        <div class="bb-main">
            <div class="bb-slider">
                <?php
                $limit_count = 1;
                // $bb_website_property_id_slugs_list = get_option('bb_website_property_id_slug', []);
                // $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids', []);
                // $bragbook_websiteproperty_id = get_option('bragbook_websiteproperty_id', []);
                $bb_scase_ids_list = [];
                $spro_title_bb = '';
                
                foreach($result['data'] as $procedure_data) {
                    $bb_scase_ids_list[] = $procedure_data['id'];
                   // $page_slug = isset($procedure_data['page_slug']) ? $procedure_data['page_slug'] : '';
                    $spro_title_bb = 'blepharoplasty'; //strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $procedure_data['procedure_title']));
                   // if (($parts[0] == $page_slug) || ($bb_page_exist == false && $cat_website_property_id == "0")) {
                       // if (!empty($procedure_data['photoSets']) && $limit_count <= $cat_limit && $procedure_data['procedure_case_count'] >= $cat_start) { 
                        if (!empty($procedure_data['photoSets']) && $limit_count <= $cat_limit) { 
                            ?>
                            <div class="bb-slick-slide">
                                <div class="bb-slide">
                                    <?php
                                    $bb_new_image_procedure_data = isset($procedure_data['photoSets'][0]['highResPostProcessedImageLocation']) && !is_null($procedure_data['photoSets'][0]['highResPostProcessedImageLocation'])
                                        ? $procedure_data['photoSets'][0]['highResPostProcessedImageLocation'] 
                                        : (isset($procedure_data['photoSets'][0]['postProcessedImageLocation']) && !is_null($procedure_data['photoSets'][0]['postProcessedImageLocation']) 
                                            ? $procedure_data['photoSets'][0]['postProcessedImageLocation'] 
                                            : $procedure_data['photoSets'][0]['originalBeforeLocation']);
                                    
                                    ?>
                                    <a href="<?php echo '/combine/' . $spro_title_bb . "/" . $procedure_data['id']; ?>">
                                        <img class="bb-slide-thumnail" src="<?php echo $bb_new_image_procedure_data; ?>" 
                                        alt="<?php echo isset($procedure_data['photoSets'][0]['seoAltText']) ? $procedure_data['photoSets'][0]['seoAltText'] : ''; ?>">
                                    </a>
                                    <?php if ($cat_title == 1 || $cat_details == 1) { ?>
                                        <div class="bb-content-box-inner">
                                            <div class="bb-content-box-inner-left">
                                                <?php if ($cat_title == 1) { ?>
                                                    <h5><?php echo isset($procedure_data['seoHeadline']) ? $procedure_data['seoHeadline'] : 'blepharoplasty' ?> : Patient</h5>
                                                    <p><?php echo self::bb_limitWords($procedure_data['details'], 50); ?></p>
                                                <?php } ?>
                                                <?php if ($cat_details == 1) { ?>
                                                    <button type="button"><a href="<?php echo '/combine/' . $spro_title_bb . "/" . $procedure_data['id']; ?>">View More</a></button>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php
                            $limit_count++;
                        }
                    }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function mvp_bragbook_set_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'caseid' => '',
                'website_property_id' => '0'
            ), 
            $atts
        );

        $caseid = 4141; //$atts['caseid'];
        
        $url = "https://nextjs-bragbook-app-dev.vercel.app/api/plugin/cases/?websitePropertyId=2&apiToken=0a910fcd-371e-454f-9615-749807690c92&caseId=4141&procedureId=138";
        $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

            $short_data_set = curl_exec($ch);
            curl_close($ch);
       // $short_data_set = get_transient($url);
       
        // $cat_website_property_id = $atts['website_property_id'];        

        // self::mvp_brag_shortcode($atts);
        // $data = get_option('bb_api_data_short');
         $result_set = json_decode($short_data_set, true);
        // $api_data = [];
        // $categories = [];
        // $bb_slug_count = 1;
        
        // foreach ($result as $key => $value) {
        //     $bb_api_data = $value['api_data'];
        //     if(!empty($bb_api_data) && is_array($bb_api_data)) {
        //         foreach ($bb_api_data as $index => $api_item) {
        //             $new_data = ["page_slug" => $key];
        //             $id_position = array_search('id', array_keys($api_item));
        //             $result[$key]['api_data'][$index] = array_merge(
        //                 array_slice($api_item, 0, $id_position + 1), 
        //                 $new_data,
        //                 array_slice($api_item, $id_position + 1)
        //             );
        //         }
        //     }
        // }

        // foreach ($result as $page_slug => $item) {
        //     if (isset($item['api_data'])) {
        //         $api_data = array_merge($api_data, $item['api_data']);
        //     }
        // }
        
         ob_start();
        ?>

        <div class="bb-main">
            <div class="bb-content-boxes">
                <?php
                
                if(!empty($result_set) && is_array($result_set)) {
                    
                    foreach ($result_set['data'] as $entry) {
                        if (isset($entry['photoSets']) && is_array($entry['photoSets'])) {
                            foreach ($entry['photoSets'] as $photoSet) {
                                
                                if ($caseid == $photoSet['caseId'] ) {
                                    ?>
                                    <div class="bb-content-box">
                                        <?php
                                        $bb_new_image_photoSet = isset($photoSet['highResPostProcessedImageLocation']) && !is_null($photoSet['highResPostProcessedImageLocation'])
                                            ? $photoSet['highResPostProcessedImageLocation'] 
                                                : (isset($photoSet['postProcessedImageLocation']) && !is_null($photoSet['postProcessedImageLocation']) 
                                                    ? $photoSet['postProcessedImageLocation'] 
                                                    : $photoSet['originalBeforeLocation']);
                                        
                                        ?>
                                        <img src="<?php echo $bb_new_image_photoSet ?>" alt="<?php echo isset($photoSet['seoAltText']) ? $photoSet['seoAltText'] : ''; ?>">
                                    </div>
                                <?php
                                break;
                                }
                            }
                        }
                    }
                }
                ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function bb_mvp_brag_shortcode($parts_page_name, $combine_gallery_page_slug) {
        ob_start();
        update_option("bbrag_api_data_short", "");
        update_option("bbrag_combine_api_data_short", "");
      
        $api_tokens = get_option('bragbook_api_token', []);
        $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
        $gallery_slugs = get_option('bb_gallery_page_slug', []);
        
        $all_results = [];
        $combine_results = [];
        foreach ($api_tokens as $index => $api_token) {
            $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
            $page_slug_bb = $gallery_slugs[$index] ?? '';
            if (empty($api_token) || empty($websiteproperty_id)) {
                continue;
            }

            $cat_url = "https://bragbookv2.com/api/plugin/categories?apiToken={$api_token}&websitepropertyId={$websiteproperty_id}";
            $category_list = self::bb_get_grabbook_category_feed($cat_url); 
            $cat_set = json_decode($category_list, true) ?? []; 

            $url = "https://bragbookv2.com/api/plugin/cases?apiToken={$api_token}&websitepropertyId={$websiteproperty_id}";
            $data = self::bb_get_grabbook_api($url);
            $api_data = json_decode($data, true) ?? [];

            $result = [
                'categories' => $cat_set,
                'api_data' => $api_data
            ];

            if($combine_gallery_page_slug == $parts_page_name[0]) {
                $combine_results[$api_token][$websiteproperty_id][$page_slug_bb] = $result; 
            } else {
                $all_results[$api_token][$websiteproperty_id][$page_slug_bb] = $result;
            }
        }

        $bragbook_api_information = json_encode($all_results);
        $bragbook_combine_api_information = json_encode($combine_results);

        update_option("bbrag_api_data_short", $bragbook_api_information);
        update_option("bbrag_combine_api_data_short", $bragbook_combine_api_information);
        ob_clean();
    }
    
    // add bragbook_home_menu shortcode
    public static function render_category_group_home_menu($all_properties, $plugin_dir_path, $parts, $cat_website_property_id) {
        if (!empty($all_properties) && is_array($all_properties)) {
            $merged_categories = [];
            foreach ($all_properties as $property_id => $categories) {
                if(is_array($categories)) {
                    foreach ($categories as $category_id => $category_data) {
                        $category_name = $category_data['category_name'];
                        $procedures_data = $category_data['procedures_data'];
                        if (!isset($merged_categories[$category_name])) {
                            $merged_categories[$category_name] = [
                                'category_name' => $category_name,
                                'procedures' => [],
                            ];
                        }

                        foreach ($procedures_data as $procedure) {
                            $procedure_name = $procedure['name'];
                            $case_count = $procedure['case_count'];
                            if (isset($merged_categories[$category_name]['procedures'][$procedure_name])) {
                                $merged_categories[$category_name]['procedures'][$procedure_name]['case_count'] += $case_count;
                            } else {
                                $merged_categories[$category_name]['procedures'][$procedure_name] = [
                                    'name' => $procedure_name,
                                    'case_count' => $case_count,
                                    'id' => $procedure['id']
                                ];
                            }
                        }
                    }
                }
            }

            foreach ($merged_categories as $category_name => $category_data) {
                $totalCaseCount = 0;
                foreach ($category_data['procedures'] as $procedure_name => $procedure_data) {
                    $totalCaseCount += $procedure_data['case_count'];
                }

                if ($totalCaseCount != 0) {
                    $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids', []);
                    $bragbook_websiteproperty_id = get_option('bragbook_websiteproperty_id', []);

                    if($cat_website_property_id !== '0') {
                        $search_key = array_search($cat_website_property_id, $bragbook_websiteproperty_id);
                        $bb_p_id = $bb_shortcode_page_id = $bb_page_list_gallery[$search_key];
                        $page_slug = get_post_field('post_name', $bb_shortcode_page_id);

                    } elseif ($cat_website_property_id == '0') {
                        $bb_p_id = $combine_gallery_page_id = get_option('combine_gallery_page_id');
                        $page_slug = get_post_field('post_name', $combine_gallery_page_id);
                    }
                    
                    if($page_slug == '' || get_post_status($bb_p_id) === 'trash') {
                        $firstValue = reset($bragbook_websiteproperty_id);
                        $search_key = array_search($firstValue, $bragbook_websiteproperty_id);
                        $bb_shortcode_page_id = $bb_page_list_gallery[$search_key];
                        $page_slug = get_post_field('post_name', $bb_shortcode_page_id);
                    }
                    
                    ?>
                    <span class="bb-accordion" cat_title="<?= htmlspecialchars($category_name); ?>">
                        <h3><?= $category_name; ?> <span>(<?= $totalCaseCount; ?>)</span></h3>
                        <img src="<?= $plugin_dir_path ?>assets/images/plus-icon.svg" alt="plus icon">
                    </span>
                    <div class="bb-panel">
                        <ul>
                            <?php
                            ksort($category_data['procedures']);
                            foreach ($category_data['procedures'] as $procedure_name => $procedure_data) {
                                if ($procedure_data['case_count'] != 0) {
                                    $converted_procedure_name = preg_replace('/[^a-zA-Z0-9]+/', '-', $procedure_data['name']);
                                    $lower_procedure_name = strtolower($converted_procedure_name);

                                    update_option($converted_procedure_name, $category_name);
                                    update_option($lower_procedure_name, $category_name);
                                    update_option($lower_procedure_name . '_id', $procedure_data['id']);
                                    update_option($procedure_data['id'] . '_title', $procedure_data['name']);
                                    ?>
                                    <li>
                                        <a id="<?= esc_attr($procedure['id']); ?>" href="<?= "/" . $page_slug . "/" . strtolower($converted_procedure_name) . "/"; ?>">
                                            <?= esc_html($procedure_data['name']); ?> <span>(<?php echo $procedure_data['case_count']; ?>)</span>
                                        </a>
                                    </li>
                                    <?php
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <?php
                }
            }
        }
        $bb_favorite_caseIds = get_option('favorite_caseIds_ajax');
        $favorite_caseIds_count = count($bb_favorite_caseIds);
        ?>
        <!-- <ul>
            <li>
                <a class="bb-sidebar_favorites" href="/ //$page_slug /favorites/">
                    <h3> My Favorites <span>(<?php // echo $favorite_caseIds_count ?>)</span></h3>
                </a> 
            </li> 
        </ul> -->
        <!-- <p>Befor and after gallery powered by <span style="color:red">BRAG book™</span></p> -->
 
        <?php
    }
   
    public static function mvp_bragbook_home_menu_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'website_property_id' => '0'
            ), 
            $atts
        );
        
        $cat_website_property_id = $atts['website_property_id'];

        ob_start();
        $combine_gallery_page_id = get_option('combine_gallery_page_id');
        $combine_gallery_page = get_post($combine_gallery_page_id);
        $combine_gallery_page_slug = '';

        if($combine_gallery_page !== null) {
            $combine_gallery_page_slug = $combine_gallery_page->post_name;
        }

        $bbrag_case_url = strtok($_SERVER["REQUEST_URI"], '?');
        $bbragbook_case_url = trim($bbrag_case_url, '/');
        $parts = explode('/', $bbragbook_case_url);
        $bb_sidebar_url = "https://nextjs-bragbook-app-dev.vercel.app/api/plugin?apiToken=0a910fcd-371e-454f-9615-749807690c92";
       // self::bb_mvp_brag_shortcode($parts, $combine_gallery_page_slug);
        ?>

        <div class="bb-container-main">
            <main class="bb-main">
                <?php
                // $data = get_option('bbrag_api_data_short');
                // if($combine_gallery_page_slug == $parts[0]) {
                //     $data = get_option("bbrag_combine_api_data_short");
                // } else {
                //     $data = get_option('bbrag_api_data_short');
                // }
                $data = get_transient($bb_sidebar_url);
               // $properties_data_all = json_decode($data, true);
               // $properties_data = $properties_data_all;
                ?>
                <div class="bb-sidebar">
                    <div class="bb-sidebar-wrapper">
                        <button type="button" class="bb-sidebar-toggle bb-sidebar-head-toggle">
                            <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/caret-right-sm.svg" alt="toggle sidebar">
                        </button>
                        <form class="search-container">
                            <input type="text" id="search-bar">
                            <img src="<?php echo BB_PLUGIN_DIR_PATH?>assets/images/search-svgrepo-com.svg" class="bb-search-icon" alt="search">
                            <ul id="search-suggestions" class="search-suggestions"></ul>
                        </form>

                        <div class="bb-nav-accordion"> 
                            <?php 
                            
                            $properties_data_all = json_decode($data, true);
                            $properties_data = $properties_data_all;

                            /* 
                            Show data for singal page
                            */
                            $categorized_procedures = [];
                            $all_properties = [];
                       
                            if (!empty($properties_data) && is_array($properties_data)) {
                               // foreach ($properties_data as $api_token_key => $token_bb) {
                                    //foreach ($token_bb as $websiteproperty_id_key => $website_id_bb) {
                                       // foreach ($website_id_bb as $websiteproperty_id => $property_data) {
                                           // if(($parts_page_name[0] == $websiteproperty_id) || ($combine_gallery_page_slug == $parts_page_name[0])) {
                                            
                                                foreach ($properties_data['data'] as $procedure_name => $procedure_data) {
                                                    ?>
                                                    <span class="bb-accordion" cat_title="<?= htmlspecialchars($procedure_data['name']); ?>">
                                                        <h3><?= $procedure_data['name']; ?> <span>(<?= $procedure_data['totalCase']; ?>)</span></h3>
                                                        <img src="<?= BB_PLUGIN_DIR_PATH ?>assets/images/plus-icon.svg" alt="plus icon">
                                                    </span>
                                                    <div class="bb-panel">
                                                        <ul>
                                                        <?php
                                                            foreach($procedure_data['procedures'] as $procedure ) {
                                                                ?>
                                                                <li>
                                                                <a id="<?= esc_attr($procedure['id']); ?>"
                                                                    href="<?= "/combine/" . $procedure['slugName'] . "/"; ?>"
                                                                    data-count="1"
                                                                    data-api-token="<?= esc_attr($api_token_key); ?>"
                                                                    data-website-property-id="<?= esc_attr($websiteproperty_id_key); ?>">
                                                                        <?= esc_html($procedure['name']); ?> 
                                                                        <span>(<?php echo $procedure['totalCase']; ?>)</span>
                                                                </a>
                                                                
                                                                </li>
                                                                <?php
                                                            }
                                                            ?>
                                                        </ul>
                                                    </div>
                                                <?php
                                                }
                                                
                                            //}
                                     //   }
                                  //  }
                                
                               // }
                            }
                        

                                    
                                            ?>
                                        
                                        
                            <ul>
                                <li>
                                    <a class="bb-sidebar_favorites" href="/combine/favorites/">
                                        <h3> My Favorites <span id="bb_favorite_caseIds_count">(<?php echo get_option('bb_favorite_caseIds_count'); ?>)</span></h3>
                                    </a> 
                                </li> 
                            </ul>  
                        </div>
                    </div> 
                    
                    <a href="/<?=$parts_page_name[0]?>/consultation/" class="bb-sidebar-btn">REQUEST A CONSULTATION</a>
                    <p class="request-promo">Ready for the next step?<br>Contact us to request your consultation.</p>
                    <!-- <p>Before and after gallery powered by <span style="color:red">BRAG book™</span></p> -->
                    
                </div>
                <!-- Sidebar end here -->

                <div class="bb-content-area">
                    <div class="bb-filter-attic bb-filter-attic-borderless">
                        <button type="button" class="bb-sidebar-toggle">
                            <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/caret-right-sm.svg" alt="toggle sidebar">
                        </button>
                        <div class="bb-search-container-outer">
                            <form class="search-container mobile-search-container">
                                <input type="text" id="mobile-search-bar">
                                <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/search-svgrepo-com.svg" class="bb-search-icon" alt="search">
                                <ul id="mobile-search-suggestions" class="search-suggestions"></ul>
                            </form>
                
                        </div>
                    </div>
                    <?php
                    
                    $brag_page_data =  get_option('bragbook_landing_page_text'); 
                    $explode_string = explode('[', $brag_page_data);
                    if (isset($explode_string[1])) {
                        $explode_string[1] = '[' . $explode_string[1]; 
                    }
                   
                    echo isset($explode_string['0']) ? $explode_string['0'] : '';
                    echo isset($explode_string['1']) ? do_shortcode($explode_string['1']) : '';
                    ?>
                    
                    <a href="<?=$bb_page_name?>consultation/" class="bb-sidebar-btn">REQUEST A CONSULTATION</a>
                    <div class="bb-bottom-bar">
                        <img src="<?php echo BB_PLUGIN_DIR_PATH?>assets/images/myfavs-logo.png" alt="logo">
                        <p>
                            <span>Use the MyFavorites tool</span> to help communicate your specific goals. If a result speaks to
                            you, tap the heart.
                        </p>
                    </div>
                </div>
            </main>
        </div>
        <script>
            jQuery(document).ready(function($) {
                if (document.getElementById('mobile-search-bar') && document.getElementById('mobile-search-suggestions')) {
                    $('#mobile-search-bar').on('input', function() {
                        var searchText = $(this).val().toLowerCase().trim();
                        var suggestionsList = $('#mobile-search-suggestions');

                        suggestionsList.empty();
                        $('.bb-nav-accordion').find('a').each(function() {
                            var procedureTitle = $(this).text().toLowerCase();
                            var href = $(this).attr('href');
                            if (procedureTitle.includes(searchText)) {
                                var listItem = $('<li><a href="' + href + '">' + $(this).text() + '</a></li>');
                                suggestionsList.append(listItem);
                            }
                        });
                        
                        if (suggestionsList.children().length > 0) {
                            suggestionsList.show();
                        } else {
                            suggestionsList.hide();
                        }
                    });

                    const searchInput = document.getElementById('mobile-search-bar');
                    const suggestionsList = document.getElementById('mobile-search-suggestions');

                    function toggleSuggestionsVisibility() {
                        if (searchInput.value.trim() === '') {
                            suggestionsList.style.display = 'none';
                        } else {
                            suggestionsList.style.display = 'block';
                        }
                    }

                    searchInput.addEventListener('input', toggleSuggestionsVisibility);
                    searchInput.addEventListener('keyup', toggleSuggestionsVisibility);
                    searchInput.addEventListener('keydown', toggleSuggestionsVisibility);
                }
                
                $('#search-bar').on('input', function() {
                    var searchText = $(this).val().toLowerCase().trim();
                    var searchsuggestions = $('#search-suggestions');
                    
                    searchsuggestions.empty();
                    $('.bb-nav-accordion').find('a').each(function() {
                        var procedureTitle = $(this).text().toLowerCase();
                        var href = $(this).attr('href');
                        if (procedureTitle.includes(searchText)) {
                            var listItem = $('<li><a href="' + href + '">' + $(this).text() + '</a></li>');
                            searchsuggestions.append(listItem);
                        }
                    });
                    
                    if (searchsuggestions.children().length > 0) {
                        searchsuggestions.show();
                    } else {
                        searchsuggestions.hide();
                    }
                });

                const searchBar = document.getElementById('search-bar');
                const searchsuggestions = document.getElementById('search-suggestions');
                function SuggestionsVisibility() {
                    if (searchBar.value.trim() === '') {
                        searchsuggestions.style.display = 'none';
                    } else {
                        searchsuggestions.style.display = 'block';
                    }
                }

                searchBar.addEventListener('input', SuggestionsVisibility);
                searchBar.addEventListener('keyup', SuggestionsVisibility);
                searchBar.addEventListener('keydown', SuggestionsVisibility);
            });

        </script>
        <?php
        return ob_get_clean();
    }
    
    public static function bb_mvp_category_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'category' => '',
                'procedure' => '',
                'limit' => 10,
                'title' => '0',
                'details' => '0',
                'start' => '0',
                'website_property_id' => '0'
            ), 
            $atts
        );
        
        $cat_name = empty($atts['category']) ? $atts['procedure'] : $atts['category'];
        $cat_limit = $atts['limit'];
        $cat_title = $atts['title'];
        $cat_details = $atts['details'];
        $cat_start = $atts['start'];
        $cat_website_property_id = $atts['website_property_id'];

       // self::mvp_brag_shortcode($atts);
       $url = "https://nextjs-bragbook-app-dev.vercel.app/api/plugin/cases/paginate?websitePropertyId=2&count=1&apiToken=0a910fcd-371e-454f-9615-749807690c92&procedureId=138";
       $short_data = get_transient($url);
        
        $result_pro = json_decode($short_data, true);
        $api_data = [];
        $categories = [];
        
        // foreach ($result as $key => $value) {
        //     $bb_api_data = $value['api_data']; 
        //     if(is_array($bb_api_data)) {
        //         foreach ($bb_api_data as $index => $api_item) {
        //             $new_data = ["page_slug" => $key];
        //             $id_position = array_search('id', array_keys($api_item));
        //             $result[$key]['api_data'][$index] = array_merge(
        //                 array_slice($api_item, 0, $id_position + 1),
        //                 $new_data,
        //                 array_slice($api_item, $id_position + 1)
        //             );
        //         }
        //     }
        // }

        // foreach ($result as $page_slug => $item) {
        //     if (isset($item['api_data'])) {
        //         $api_data = array_merge($api_data, $item['api_data']);
        //     }
        //     if (isset($item['categories'])) {
        //         $categories = array_merge($categories, $item['categories']);
        //     }
        // }
        // $categorized_procedures = [];
        // if(!empty($categories) && is_array($categories)) {
        //     foreach ($categories as $category_key => $category) {
        //         $case_counts = [];
        //         if(!empty($category) && is_array($category)) {
        //             foreach ($category['procedures'] as $procedure_key => $procedure) {
        //                 $p_case_count = 0; 
        //                 if(!empty($api_data) && is_array($api_data)) {
        //                     foreach ($api_data as $item) {
        //                         if (in_array($procedure['id'], $item['procedureIds'])) {
        //                             if (!empty($item['photoSets'])) {
        //                                 $p_case_count++;
        //                             }
        //                         }
        //                     }
        //                 }
        //                 $case_counts[$procedure_key] = $p_case_count;
        //             }
        //         }

        //         foreach ($category['procedures'] as $procedure_key => $procedure) {
        //             $categories[$category_key]['procedures'][$procedure_key]['case_count'] = $case_counts[$procedure_key];
        //         }
        //     }
        // }

        // if(!empty($categories) && is_array($categories)) {
        //     foreach ($categories as $category) {
        //         $procedures_cat_data = [];
        //         if(!empty($api_data) && is_array($api_data)) {
        //             foreach ($api_data as $item) {
        //                 foreach ($category['procedures'] as $procedure) {
        //                     if (in_array($procedure['id'], $item['procedureIds'])) {
        //                         if(!empty($item['photoSets'])) { 
        //                             $procedures_cat_data[] = $procedure['id']; 
        //                         }
        //                     }
        //                 }
        //             }
        //         }

        //         $categorized_procedures[$category['id']] = [
        //             'category_name' => $category['name'],
        //             'procedures_count' => count($procedures_cat_data),
        //             'procedures_data' => $category['procedures'],
        //         ];
        //     }
        // }
       
        // $matching_data = [];
        // if(!empty($api_data) && is_array($api_data)) {
        //     foreach ($api_data as $item) {
        //         foreach($categorized_procedures as $category_id => $category_data) {
        //             $procedures_data = $category_data['procedures_data'];
                
        //             if(is_array($procedures_data)) {
        //                 foreach($procedures_data as $complete_category) {
        //                     $b_converted_procedure_name = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($complete_category['name']));
        //                     if (in_array($complete_category['id'], $item['procedureIds']) && ($cat_name == $complete_category['name'] || $cat_name == $b_converted_procedure_name)) {
        //                         if(!empty($item['photoSets'])) { 
        //                             if (!isset($procedure_counts[$complete_category['id']])) {
        //                                 $procedure_counts[$complete_category['id']] = 0;
        //                             }

        //                             $procedure_counts[$complete_category['id']]++;
        //                             $item['procedure_title'] = $complete_category['name'];
        //                             $item['procedure_case_count']  = $procedure_counts[$complete_category['id']];
        //                             $item['procedure_id'] = $complete_category['id'];
        //                             $item['description'] = $complete_category['description'];

        //                             $matching_data[] = $item;
        //                         }
                                
        //                     }
        //                 }
        //             }
                    
        //         }
        //     }
        // }

        // $bb_all_gallery_slugs = get_option('bb_gallery_page_slug', []);
        // $bb_combine_gallery_slug = get_option('combine_gallery_slug');

        // $bbrag_case_url = strtok($_SERVER["REQUEST_URI"], '?');
        // $bbragbook_case_url = trim($bbrag_case_url, '/');
        // $parts = explode('/', $bbragbook_case_url);

        // $page_url_combine = get_page_by_path($bb_combine_gallery_slug);
        // $limit_count = 1;

        // if ($page_url_combine) {
        //     $bb_page_exist = true;
        // } else {
        //     $bb_page_exist = false;
        // } 

        ob_start();
        ?>
        <div class="bb-main bb-category-shortcode-main">
            <div class="bb-content-boxes">
                <?
                $bb_case_count = 0;
                $secondPart = 'combine';
                $thirdPart = 'blepharoplasty';
                

                // Start generating content
                $contentBox = ''; // This will hold the HTML content

                foreach ($result_pro['data'] as $caseItem) {
                    if (isset($caseItem['photoSets']) && count($caseItem['photoSets']) > 0) {
                        $photoSet = $caseItem['photoSets'][0]; // Get the first photo set
                        $imgSrc = $photoSet['highResPostProcessedImageLocation'] ?? $photoSet['postProcessedImageLocation'] ?? $photoSet['originalBeforeLocation'];
                        $imgAlt = $photoSet['seoAltText'] ?? 'Procedure Image';
                        $caseId = $caseItem['id'];
                        $caseDetails = $caseItem['details'] ?? '';
                        $patientCount = ++$bb_case_count;
                        $procedureUrl = "/$secondPart/$thirdPart/$caseId/";

                        $newContent = "
                            <div class='bb-content-box'>
                                <div class='bb-content-thumbnail'>
                                    <a href='$procedureUrl'>
                                        <img src='$imgSrc' alt='$imgAlt'>
                                    </a>
                                    
                                </div>
                                <div class='bb-content-box-inner'>
                                    <div class='bb-content-box-inner-left'>
                                        <h5>$thirdPart : Patient $patientCount</h5>
                                        <p>$caseDetails</p> 
                                    </div>
                                    <div class='bb-content-box-inner-right'>
                                        
                                    </div>
                                </div>
                                <div class='bb-content-box-cta'>
                                    <a class='view-more-btn' href='$procedureUrl'>
                                        View More
                                    </a>
                                </div>
                            </div>
                        ";

                        $contentBox .= $newContent; // Append content
                    }
                }

                // Output the generated content
                echo $contentBox;
        ?>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }
}
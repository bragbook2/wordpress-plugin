<?php
namespace mvpbrag;

class Shortcode {
    
    public static function register() {
        // Hook into 'init' to add custom rewrite rules
        add_action('init', [ __CLASS__, 'custom_rewrite_flush']);
        //add_shortcode('brag_book_gallery', [ __CLASS__, 'mvp_brag_shortcode' ]);
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
	// public static function bb_get_grabbook_category_feed($url) {
	// 	$cats_json = self::bb_get_grabbook_api($url);
	// 	return $cats_json;
	// }

    // public static function bb_get_grabbook_api($url) {
    //     $bb_set_transient_urls = get_option( 'bb_set_transient_url', [] );
    //     if ( ! is_array( $bb_set_transient_urls ) ) {
    //         $bb_set_transient_urls = [];
    //     }

	// 	if (get_transient($url) !== false) {
	// 		return get_transient($url);
	// 	}

	// 	$ch = curl_init();
	// 	curl_setopt($ch, CURLOPT_URL, $url);
	// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

	// 	$data = curl_exec($ch);
	// 	curl_close($ch);

    //     $bb_set_transient_urls[$url] = $data;
    //     update_option( 'bb_set_transient_url', $bb_set_transient_urls );
        
	// 	set_transient($url, $data, 1800);
	// 	return $data;
	// }

    // public static function mvp_brag_shortcode($atts) {
    //     $api_tokens = get_option('bragbook_api_token', []);
    //     $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
    //     $gallery_slugs = get_option('bb_gallery_page_slug', []);

    //     $all_results = [];
    //     $bb_website_property_id_slug = [];
    //     foreach ($api_tokens as $index => $api_token) {
    //         $websiteproperty_id = isset($websiteproperty_ids[$index]) ? $websiteproperty_ids[$index] : '';
    //         $page_slug_bb = isset($gallery_slugs[$index]) ? $gallery_slugs[$index] : '';
    //         if (empty($api_token) || empty($websiteproperty_id)) {
    //             continue;
    //         }
    
    //         $cat_url = BB_BASE_URL . "/api/plugin/categories?apiToken=" . $api_token . "&websitepropertyId=" . $websiteproperty_id;
    //         $category_list = self::bb_get_grabbook_category_feed($cat_url);
    
    //         $cat_set = json_decode($category_list, true);
    
    //         $url = BB_BASE_URL . "/api/plugin/cases?apiToken=" . $api_token . "&websitepropertyId=" . $websiteproperty_id;
    //         $data = self::bb_get_grabbook_api($url);
    //         $api_data = json_decode($data, true);
    
    //         $result = [
    //             'categories' => $cat_set,
    //             'api_data' => $api_data
    //         ];
    
    //         $all_results[$page_slug_bb] = $result;
    //         $bb_website_property_id_slug[$page_slug_bb] = $websiteproperty_id;
    //         update_option('bb_website_property_id_slug', $bb_website_property_id_slug);
    //     }
    
    //     $bragbook_api_information = json_encode($all_results);
    //     update_option("bb_api_data_short", $bragbook_api_information);

    //     return $bragbook_api_information;
	// }
    public static function searchData($data, $searchTerm) {
       
        foreach ($data as $key => $entry) {
            if ($entry === true) {
                return null; 
            }
            foreach ($entry as $category) {
                if (isset($category['procedures'])) {
                     foreach ($category['procedures'] as $procedure) {
                         if (strtolower($procedure["name"]) === strtolower($searchTerm) || strtolower($procedure["slugName"]) === strtolower($searchTerm)) {
                             return $procedure;
                         }
                     }
                }
            }
        }
        return null; 
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
        $cat_name = empty($atts['category']) ? $atts['procedure'] : $atts['category'];
        $cat_limit = $atts['limit'];
        $cat_title = $atts['title'];
        $cat_details = $atts['details'];
        $cat_start = $atts['start'];
        $cat_procedure_name = $atts['category'];

        $cat_website_property_id = $atts['website_property_id'];
        $api_tokens = get_option('bragbook_api_token', []); 
        $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
        $gallery_slugs = get_option('bb_gallery_page_slug', []); 
          
        $token = '';
        foreach ($api_tokens as $index => $api_token) {
            $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
            $page_slug_bb = $gallery_slugs[$index] ?? '';
            
            if(($websiteproperty_id == $cat_website_property_id)) {
                if (empty($api_token) || empty($websiteproperty_id)) {
                    continue;
                }
                $bb_sidebar_url = BB_BASE_URL . "/api/plugin/sidebar?apiToken={$api_token}";
                
                $token = $api_token;
                $bb_slug_link = $page_slug_bb;
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $bb_sidebar_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $data = curl_exec($ch);
                curl_close($ch);
             
              $sidebar_set = json_decode($data, true) ?? []; 
            
            }
        }
      
         $result = isset($sidebar_set) ? self::searchData($sidebar_set, $cat_name) : '';
         if(empty($result)) {
            return false; 
         }
        
         $id = $result['id'];
         $url_car = BB_BASE_URL . "/api/plugin/carousel?websitePropertyId={$cat_website_property_id}&start={$cat_start}&limit={$cat_limit}&apiToken={$token}&procedureId={$id}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_car);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data_car = curl_exec($ch);
        curl_close($ch); 

        ob_start();
        ?>
        <div class="bb-main">
            <div class="bb-slider">
                <?php
                $limit_count = 0;
                $bb_scase_ids_list = [];
                $spro_title_bb = $result['slugName'];
                $carousel_data_bb = json_decode($data_car);
                foreach($carousel_data_bb->data as $procedure_data) {
                    
                        if (!empty($procedure_data->photoSets)) { 
                            ?>
                            <div class="bb-slick-slide">
                                <div class="bb-slide">
                                    <?php
                                    $bb_new_image_procedure_data = isset($procedure_data->photoSets[0]->highResPostProcessedImageLocation) && !is_null($procedure_data->photoSets[0]->highResPostProcessedImageLocation)
                                        ? $procedure_data->photoSets[0]->highResPostProcessedImageLocation 
                                        : (isset($procedure_data->photoSets[0]->postProcessedImageLocation) && !is_null($procedure_data->photoSets[0]->postProcessedImageLocation) 
                                            ? $procedure_data->photoSets[0]->postProcessedImageLocation 
                                            : $procedure_data->photoSets[0]->originalBeforeLocation);
                                    ?>
                                    <?php 
                                        $caseSeoSuffixUrl = "";
                                        if($procedure_data->caseDetails[0] && $procedure_data->caseDetails[0]->seoSuffixUrl) {
                                            $caseSeoSuffixUrl = $procedure_data->caseDetails[0]->seoSuffixUrl;
                                        } else {
                                            $caseSeoSuffixUrl = 'bb-case-' . $procedure_data->id;
                                        }
                                    ?>
                                    <a href="<?php echo "/" . $bb_slug_link . "/" . $spro_title_bb . "/" . $caseSeoSuffixUrl; ?>">
                                        <img class="bb-slide-thumnail" src="<?php echo $bb_new_image_procedure_data; ?>" 
                                        alt="<?php echo isset($procedure_data->photoSets[0]->seoAltText) ? $procedure_data->photoSets[0]->seoAltText : ''; ?>">
                                    </a>
                                    <?php if ($cat_title == 1 || $cat_details == 1) { ?>
                                        <div class="bb-content-box-inner">
                                            <div class="bb-content-box-inner-left">
                                                <?php if ($cat_title == 1) { 
                                                    ?>
                                                    <h5><?php echo isset($procedure_data->caseDetails[0]->seoHeadline) ? $procedure_data->caseDetails[0]->seoHeadline : $cat_procedure_name; ?> : Patient: <?=++$limit_count?></h5>
                                                    <p><?php echo self::bb_limitWords($procedure_data->details, 50); ?></p>
                                                <?php } ?>
                                                <?php if ($cat_details == 1) { ?>
                                                    <button type="button"><a href="<?php echo "/" . $bb_slug_link . "/" . $spro_title_bb . "/" . $caseSeoSuffixUrl; ?>">View More</a></button>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php
                           
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
                'website_property_id' => '0',
                'procedure' => '',
            ), 
            $atts
        );
        $cat_name = $atts['procedure'];
        $caseid = $atts['caseid'];
        $cat_website_property_id = $atts['website_property_id'];
        
       $cat_website_property_id = $atts['website_property_id'];
        $api_tokens = get_option('bragbook_api_token', []); 
        $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
        $gallery_slugs = get_option('bb_gallery_page_slug', []); 
          
        $token = '';  
        foreach ($api_tokens as $index => $api_token) {
            $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
            $page_slug_bb = $gallery_slugs[$index] ?? '';
            
            if(($websiteproperty_id == $cat_website_property_id)) {
                if (empty($api_token) || empty($websiteproperty_id)) {
                    continue;
                }
                $bb_sidebar_url = BB_BASE_URL . "/api/plugin/sidebar?apiToken={$api_token}";
                
                $token = $api_token;
                $bb_slug_link = $page_slug_bb;
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $bb_sidebar_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $data = curl_exec($ch);
                curl_close($ch);
             
              $sidebar_set = json_decode($data, true) ?? []; 
            
            }
        }
      
         $result = isset($sidebar_set) ? self::searchData($sidebar_set, $cat_name) : '';
         if(empty($result)) {
            return false; 
         }
        
         $id = $result['id'];
        $url_case = BB_BASE_URL . "/api/plugin/cases/?websitePropertyId={$cat_website_property_id}&apiToken={$token}&caseId={$caseid}&procedureId={$id}";

        // $url_car = BB_BASE_URL . "/api/plugin/carousel?websitePropertyId={$cat_website_property_id}&start={$cat_start}&limit={$cat_limit}&apiToken={$token}&procedureId={$id}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_case);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data_case = curl_exec($ch);
        curl_close($ch);
      
         $result_set = json_decode($data_case, true);
       
        
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

   
   
    public static function mvp_bragbook_home_menu_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'website_property_id' => '0'
            ), 
            $atts
        );
        
        ob_start();
       
        $cat_website_property_id = $atts['website_property_id'];
        $api_tokens = get_option('bragbook_api_token', []); 
        $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
        $gallery_slugs = get_option('bb_gallery_page_slug', []); 
          
      
        $token = '';  
        $sidebar_set = '';
        $bb_slug_link = '';
        foreach ($api_tokens as $index => $api_token) {
            $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
            $page_slug_bb = $gallery_slugs[$index] ?? '';
            
            if(($websiteproperty_id == $cat_website_property_id)) {
                if (empty($api_token) || empty($websiteproperty_id)) {
                    continue;
                }
                $bb_sidebar_url = BB_BASE_URL . "/api/plugin/sidebar?apiToken={$api_token}";
                
                $token = $api_token;
                $bb_slug_link = $page_slug_bb;
                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $bb_sidebar_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                $data = curl_exec($ch);
                curl_close($ch);
             
              $sidebar_set = json_decode($data, true) ?? []; 
            
            }
        }
       // self::bb_mvp_brag_shortcode($parts, $combine_gallery_page_slug);
        ?>

        <div class="bb-container-main">
            <main class="bb-main">
                <?php
                
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
                            
                                $properties_data_all = $sidebar_set;
                                $properties_data = $properties_data_all;

                                /* 
                                Show data for singal page
                                */
                                $categorized_procedures = [];
                                $all_properties = [];
                              
                                if (!empty($properties_data) && is_array($properties_data)) {
                                
                                   
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
                                                        href="<?= "/" . $bb_slug_link . "/" . $procedure['slugName'] . "/"; ?>"
                                                        data-count="1"
                                                        data-api-token="<?= esc_attr($token); ?>"
                                                        data-website-property-id="<?= esc_attr($cat_website_property_id); ?>">
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
                                }
                            ?>
                                                   
                            <ul>
                                <li>
                                    <a class="bb-sidebar_favorites" href="<?="/" . $bb_slug_link . "/"?>favorites/">
                                        <h3> My Favorites <span id="bb_favorite_caseIds_count">(<?php echo get_option('bb_favorite_caseIds_count'); ?>)</span></h3>
                                    </a> 
                                </li> 
                            </ul>  
                        </div>
                    </div> 
                    
                    <a href="/<?=$bb_slug_link?>/consultation/" class="bb-sidebar-btn">REQUEST A CONSULTATION</a>
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

       $api_tokens = get_option('bragbook_api_token', []); 
       $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
       $gallery_slugs = get_option('bb_gallery_page_slug', []); 
         
       $token = '';  
       foreach ($api_tokens as $index => $api_token) {
           $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
           $page_slug_bb = $gallery_slugs[$index] ?? '';
           
           if(($websiteproperty_id == $cat_website_property_id)) {
               if (empty($api_token) || empty($websiteproperty_id)) {
                   continue;
               }
               $bb_sidebar_url = BB_BASE_URL . "/api/plugin/sidebar?apiToken={$api_token}";
               
               $token = $api_token;
               $bb_slug_link = $page_slug_bb;
               
               $ch = curl_init();
               curl_setopt($ch, CURLOPT_URL, $bb_sidebar_url);
               curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
               curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
               $data = curl_exec($ch);
               curl_close($ch);
            
             $sidebar_set = json_decode($data, true) ?? []; 
           
           }
       }
     
        $result = isset($sidebar_set) ? self::searchData($sidebar_set, $cat_name) : '';
        if(empty($result)) {
           return false; 
        }
       
        $id = $result['id'];
        $procedure_name_bb = $result['slugName'];
      // $url_pro = BB_BASE_URL . "/api/plugin/cases/paginate?websitePropertyId={$cat_website_property_id}&count=1&apiToken={$token}&procedureId={$id}";

        $url_pro = BB_BASE_URL . "/api/plugin/carousel?websitePropertyId={$cat_website_property_id}&start={$cat_start}&limit={$cat_limit}&apiToken={$token}&procedureId={$id}";

       $ch = curl_init();
       curl_setopt($ch, CURLOPT_URL, $url_pro);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
       $data_pro = curl_exec($ch);
       curl_close($ch);
        $result_pro = json_decode($data_pro, true);
        $api_data = [];
        $categories = [];
        
         

        ob_start();
        ?>
        <div class="bb-main bb-category-shortcode-main">
            <div class="bb-content-boxes">
                <?
                $bb_case_count = 0;
                $secondPart = $bb_slug_link;
                $thirdPart = $procedure_name_bb;
                

                // Start generating content
                $contentBox = ''; // This will hold the HTML content
                
                foreach ($result_pro['data'] as $caseItem) {
                    if (isset($caseItem['photoSets']) && count($caseItem['photoSets']) > 0) {
                        $photoSet = $caseItem['photoSets'][0]; // Get the first photo set
                        $imgSrc = $photoSet['highResPostProcessedImageLocation'] ?? $photoSet['postProcessedImageLocation'] ?? $photoSet['originalBeforeLocation'];
                        $imgAlt = $photoSet['seoAltText'] ?? 'Procedure Image';
                        $caseSeoSuffixUrl = "";
                        if($caseItem["caseDetails"][0] && $caseItem["caseDetails"][0]["seoSuffixUrl"]) {
                            $caseSeoSuffixUrl = $caseItem["caseDetails"][0]["seoSuffixUrl"];
                        } else {
                            $caseSeoSuffixUrl = 'bb-case-' . $caseItem['id'];
                        }
                        $caseDetails = $caseItem['details'] ?? '';
                        $patientCount = ++$bb_case_count;
                        $procedureUrl = "/$secondPart/$thirdPart/$caseSeoSuffixUrl/";

                        $newContent = "
                            <div class='bb-content-box'>
                                <div class='bb-content-thumbnail'>
                                    <a href='$procedureUrl'>
                                        <img src='$imgSrc' alt='$imgAlt'>
                                    </a>
                                </div>";

                        if ($cat_title == 1) {
                           
                            $newContent .= "
                                <div class='bb-content-box-inner'>
                                    <div class='bb-content-box-inner-left'>
                                        <h5>$thirdPart : Patient $patientCount</h5>
                                        <p>$caseDetails</p> 
                                    </div>
                                    <div class='bb-content-box-inner-right'>
                                        <!-- You can add content here if needed -->
                                    </div>
                                </div>";
                        }

                        if ($cat_details == 1) {
                           
                            $newContent .= "
                                <div class='bb-content-box-cta'>
                                    <a class='view-more-btn' href='$procedureUrl'>
                                        View More
                                    </a>
                                </div>";
                        }

                        $newContent .= "</div>"; 

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
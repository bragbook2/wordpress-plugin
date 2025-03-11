<?php
/*
Template Name: Brag Page Template
*/

get_header(); 
$bbrag_case_url = strtok($_SERVER["REQUEST_URI"], '?');
$bbragbook_case_url = trim($bbrag_case_url, '/');
$parts = explode('/', $bbragbook_case_url);
$page = get_page_by_path($parts[0]);
$favorite_caseIds = get_option('favorite_caseIds_ajax');
$page_id_via_slug = "";
if($page) {
    $page_id_via_slug = $page->ID;
}

if (strpos($bbrag_case_url, '/favorites/') !== false) {
    if (count($parts) >= 3) {
        $bbrag_procedure_title = $parts[2];
        $bbrag_procedure_id = get_option($bbrag_procedure_title . '_id');
    }
    if (count($parts) >= 4) {
        $bbrag_procedure_title = $parts[2];
        $bbrag_case_id = $parts[3];
    }
} else {
    if (count($parts) >= 2) {
        $bbrag_procedure_title = $parts[1]; 
        $bbrag_procedure_id = get_option($bbrag_procedure_title . '_id');
    }
    if (count($parts) >= 3) {
        $bbrag_procedure_title = $parts[1]; 
        $bbrag_case_id = $parts[2]; 
    }
}

function removeAccents_brag($string) {
    $accents = [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a', 'æ' => 'a',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e', 'ē' => 'e', 'ė' => 'e', 'ě' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i', 'į' => 'i', 'ì' => 'i',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ō' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u', 'ū' => 'u', 'ų' => 'u', 'ű' => 'u',
        'ý' => 'y', 'ÿ' => 'y',
        'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
        'ñ' => 'n', 'ń' => 'n', 'ņ' => 'n', 'ň' => 'n',
        'ś' => 's', 'š' => 's', 'ş' => 's',
        'ž' => 'z', 'ź' => 'z', 'ż' => 'z',
    ];

    foreach ($accents as $accented => $unaccented) {
        $string = str_replace($accented, $unaccented, $string);
    }
    return $string;
}

if(isset($bbrag_procedure_title )) {
    $bbrag_procedure_title = urldecode($bbrag_procedure_title);
    $bbrag_procedure_title = $bbrag_procedure_title;
    $bbrag_procedure_title = removeAccents_brag($bbrag_procedure_title);
}

if ($bbrag_case_url == "/".$parts[0]."/consultation/") {
    include plugin_dir_path(__FILE__) . 'bb-consultation.php';
} elseif (($bbrag_case_url == "/".$parts[0]."/favorites/") || 
(isset($bbrag_case_id) && $bbrag_case_url == "/".$parts[0]."/favorites/".$bbrag_procedure_title."/". $bbrag_case_id . "/")) {
    include plugin_dir_path(__FILE__) . 'bb-favorites.php';
} elseif (isset($bbrag_procedure_title) && $bbrag_case_url == "/".$parts[0]."/".$bbrag_procedure_title."/") {
   
    ?>
    <div class="bb-container-main">
        <main class="bb-main ">
            <?php include plugin_dir_path(__FILE__) . 'sidebar-template.php'; ?>
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
                        <h2><span><?php // echo empty($procedure_title) ? $category_title_ : $procedure_title; ?></span> Before & After Gallery</h2>
                    </div>
                    <script>
                        // document.addEventListener("DOMContentLoaded", function() {
                        //     var procedure_title = "<?php //echo $procedure_title; ?> Before & After Gallery";
                        //     document.querySelector('head title').textContent = procedure_title;
                        // });
                    </script>
                    
                    <div class="actions-box">
                        <div class="action-box actions-search">
                            <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/search-svgrepo-com.svg" alt="">
                            <input placeholder="Search" id="searchField" type="text">
                        </div>
                        <div class="action-box actions-filter ">
                            <div class="action-box-toggle toggle-on-click toggle-actions-filter-box">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="2 2 20 20"><path d="M3 4.6C3 4.03995 3 3.75992 3.10899 3.54601C3.20487 3.35785 3.35785 3.20487 3.54601 3.10899C3.75992 3 4.03995 3 4.6 3H19.4C19.9601 3 20.2401 3 20.454 3.10899C20.6422 3.20487 20.7951 3.35785 20.891 3.54601C21 3.75992 21 4.03995 21 4.6V6.33726C21 6.58185 21 6.70414 20.9724 6.81923C20.9479 6.92127 20.9075 7.01881 20.8526 7.10828C20.7908 7.2092 20.7043 7.29568 20.5314 7.46863L14.4686 13.5314C14.2957 13.7043 14.2092 13.7908 14.1474 13.8917C14.0925 13.9812 14.0521 14.0787 14.0276 14.1808C14 14.2959 14 14.4182 14 14.6627V17L10 21V14.6627C10 14.4182 10 14.2959 9.97237 14.1808C9.94787 14.0787 9.90747 13.9812 9.85264 13.8917C9.7908 13.7908 9.70432 13.7043 9.53137 13.5314L3.46863 7.46863C3.29568 7.29568 3.2092 7.2092 3.14736 7.10828C3.09253 7.01881 3.05213 6.92127 3.02763 6.81923C3 6.70414 3 6.58185 3 6.33726V4.6Z" stroke="#000000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                <span>Filter</span>
                            </div>
                            <div class="actions-filter-box bb-filter-content bb-filter-content-mobile">
                                <div>
                                    <div class="bb-filter-content-attic">
                                        <div class="attic-col-left">
                                            <button class="toggle-on-click toggle-actions-filter-box toggle-action-box-toggle"><img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/cross-icon-new.svg" alt="icon"></button>
                                        </div>
                                        <div class="attic-col-right">
                                            <span class="clear-attic" id="clearButton">Clear All</span>
                                        </div>
                                    </div>
                                    <div class="bb-filter-content-inner">
                                        
                                        
                                       
                                    </div>
                                </div>
                                <div class="actions-filter-box-inner actions-filter-box2">
                                    <div class="bb-filter-content-attic">
                                        <div class="attic-col-left">
                                            <button class="toggle-on-click-multiple-close toggle-actions-filter-box2"><img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/arrow-left.svg" alt="icon"></button>
                                            <span>Filter by:</span>
                                        </div>
                                        <div class="attic-col-right">
                                            <span class="clear-attic" id="advanceClearButton">Clear All</span>
                                        </div>
                                    </div>

                                    <form class="search-container">
                                        <input type="text" id="search-bar">
                                        <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/search-svgrepo-com.svg" class="bb-search-icon" alt="search">
                                        <ul id="search-suggestions" class="search-suggestions" style="display: none !important;"></ul>
                                    </form>

                                    <div class="bb-filter-checkbox-list" id="filter-options-container">
                                    <!-- filter list will be show here -->
                                    </div>
                                </div>
                            </div>
                            <script>

                                // document.addEventListener("DOMContentLoaded", function() {
                                //     var searchField = document.getElementById('searchField');

                                //     searchField.addEventListener('keyup', function() {
                                //         var query = this.value.toLowerCase();
                                //         var contentBoxes = document.querySelectorAll('.bb-content-box');

                                //         contentBoxes.forEach(function(box) {
                                //             var content = box.textContent.toLowerCase();
                                //             if (content.includes(query)) {
                                //                 box.style.display = '';
                                //             } else {
                                //                 box.style.display = 'none';
                                //             }
                                //         });
                                //     });
                                // });

                            </script>
                        </div>
                    </div>
                    <div class="bb-content-boxes">
                        
                    </div>
                    
                    <div class="ajax-load-more" id="load-container" style="display: none;">
                        <button class="bb_ajax-load-more-btn" data-offset="2">Load More</button>
                    </div>
                   

                    <script>
                        setTimeout(() => {
                            document.getElementById("load-container").style.display = "flex";
                        }, 3000); // 3000ms = 3 seconds
                    </script>
                </div>
            </div>
            <div class="bb-fav-modal">
                <div class="bb-fav-modal-inner"> 
                    <p class="bb-fav-modal-close-button" onClick="closeModal()">X</p>
                    <img class="bb-thumbnail" src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/myfavs-logo.svg" alt="logo">
                    <h3>You are Loving these!</h3>
                    <p>To improve the communication between us, keep hearting cases that speak to you. During our
                        consultation, we'll review this collection together so we can discuss your specific goals and
                        concerns.</p>
                    <form method="post">
                        <div class="bb-input-group">
                            <label for="name">Name</label>
                            <input placeholder="Name" type="text" name="name">
                        </div>
                        <div class="bb-input-group">
                            <label for="email">Email Address</label>
                            <input placeholder="Email" type="email" name="email">
                        </div>
                        <div class="bb-input-group">
                            <label for="number">Phone</label>
                            <input placeholder="Phone" type="number" name="number">
                        </div>
                        <input type="hidden" name="case-id">
                        <input type="hidden" name="api-token">
                        <input type="hidden" name="website-id">
                        <button type="submit">Submit</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script>
        // function handleDynamicCheckboxChange(selectedCheckbox) {
        //     const filter = selectedCheckbox.dataset.filter;
        //     const checkboxes = document.querySelectorAll('input[type="checkbox"][data-filter="' + filter + '"]');

        //     checkboxes.forEach(checkbox => {
        //         if (checkbox !== selectedCheckbox) {
        //             checkbox.checked = false;
        //         }
        //     });

        //     const contentBoxes = document.querySelectorAll('.bb-content-box');
        //     contentBoxes.forEach(box => {
        //         const classes = box.className.toLowerCase();
        //         let showBox = true;
        //         const checkedCheckboxes = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'));
        //         checkedCheckboxes.forEach(checkedCheckbox => {
        //             const currentFilter = checkedCheckbox.dataset.filter.toLowerCase();
        //             const minValue = checkedCheckbox.dataset.min_value ? parseInt(checkedCheckbox.dataset.min_value, 10) : null;
        //             const maxValue = checkedCheckbox.dataset.max_value ? parseInt(checkedCheckbox.dataset.max_value, 10) : null;
        //             const stringValue = checkedCheckbox.dataset.value ? checkedCheckbox.dataset.value.toLowerCase() : '';

        //             const attributeMatch = (function() {
        //                 const attributeClass = classes.match(new RegExp(`${currentFilter}-(\\d+)`));
        //                 if (attributeClass) {
        //                     const value = parseInt(attributeClass[1], 10);
        //                     if (minValue === null && maxValue === null) {
        //                         return true;
        //                     } else if (minValue === null) {
        //                         return value <= maxValue;
        //                     } else if (maxValue === null) {
        //                         return value >= minValue;
        //                     } else {
        //                         return value >= minValue && value <= maxValue;
        //                     }
        //                 } 
        //                 return false;
        //             })();
        //            const stringMatch = stringValue !== '' && (stringValue === 'all' || classes.includes(`${currentFilter}-${stringValue}`));
                   
        //             if (!attributeMatch && !stringMatch) {
        //                 showBox = false; 
        //             }
        //         });
               
        //         box.style.display = showBox ? 'block' : 'none';
        //     });
        // }

        // document.addEventListener('DOMContentLoaded', function() {
            
        // });
    </script>

<?php
} elseif (isset($bbrag_procedure_title) && isset($bbrag_case_id) && $bbrag_case_url == "/".$parts[0]."/".$bbrag_procedure_title."/". $bbrag_case_id . "/" && get_option($bbrag_case_id . "_bb_procedure_id_" . $page_id_via_slug) !== '') {
    
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
        if($page_slug_bb == $parts[0]) {
            $response = wp_remote_post('https://www.bragbookv2.com/api/plugin/views?apiToken='. $api_token, array(
                'method'    => 'POST',
                'body'      => json_encode(array(
                    'caseId'  => $bbrag_case_id,
                )),
                'headers'   => array(
                    'Content-Type' => 'application/json',
                ),
            ));
        }
    }

    // $bbrag_procedure_id = get_option($bbrag_case_id . "_bb_procedure_id_" . $page_id_via_slug);
    // $bbrag_case_id = get_option($bbrag_case_id);  
    // $bbrag_procedure_title = get_option($bbrag_procedure_id . '_title'); 
    // $bbrag_patient = 1; 

    // $category_to_match =  $bbrag_case_id;
    // $procedure_title = $bbrag_procedure_title;
    // $procedure_id = $bbrag_procedure_id;
    // $patient_id =  $bbrag_patient;
    ?>  
    <div class="bb-container-main">
        <main class="bb-main ">
            <?php 
            include plugin_dir_path(__FILE__) . 'sidebar-template.php';
            // $matching_data = []; 
            // $case_id_list = [];
            // $combine_data = [];
            // foreach ($properties_data as $token_key_bb => $token_bb) {
            //     foreach ($token_bb as $bb_website_id => $website_id_bb) { 
            //         foreach($website_id_bb as $bb_combine_data) {
            //             $bb_combine_data['api_data']['bb_api_token'] = $token_key_bb;
            //             $bb_combine_data['api_data']['bb_website_id'] = $bb_website_id;
            //             $combine_data[] = $bb_combine_data['api_data'];
            //         }
            //     }
            // }

            // $api_data_combine = $combine_data;
            // if(is_array($api_data_combine) && !empty($api_data_combine)) {
            //     foreach ($api_data_combine as $bb_item_combine) {
            //         foreach ($bb_item_combine as $item) {
            //             if($category_to_match == '') {
            //                 $category_to_match = $item['photoSets'][0]['caseId'];
            //             }
            //             if (isset($item['photoSets'][0]['caseId']) && $item['photoSets'][0]['caseId'] == $category_to_match) {
            //                 $item['bb_api_token'] = $bb_item_combine['bb_api_token'];
            //                 $item['bb_website_id'] = $bb_item_combine['bb_website_id'];
            //                 $matching_data[] = $item;
            //             }
            //             if (isset($item['procedureIds']) && in_array($procedure_id, $item['procedureIds'])) {
            //                 if(!empty($item['photoSets'])) {
            //                     $case_id_list[] = $item['photoSets'][0]['caseId'];
            //                 }
            //             } 
            //         }
            //     }
            // }

            // $case_page_title = isset($parts[1]) ? $parts[1] : '';
            // if(!empty($case_page_title)) {
            //     $case_page_title = str_replace('-', ' ', $case_page_title);
            //     $case_page_title = ucfirst($case_page_title);
            // }
            // $images = [];
            // $setup_wizard = '';
            // $procedure_description = '';
            // $default_and_seo_page_title = '';
            
            // foreach($matching_data as $procedure_data) {
            //     if(empty($procedure_description)) {
            //         $procedure_description = isset($procedure_data['caseDetails'][0]['seoPageDescription']) 
            //         && !empty($procedure_data['caseDetails'][0]['seoPageDescription']) ? $procedure_data['caseDetails'][0]['seoPageDescription'] : '';
            //     }
            //     if(empty($default_and_seo_page_title)) {
            //         $default_and_seo_page_title = isset($procedure_data['caseDetails'][0]['seoPageTitle']) 
            //         && !empty($procedure_data['caseDetails'][0]['seoPageTitle']) ? $procedure_data['caseDetails'][0]['seoPageTitle'] : '';
            //     }

            //     $bb_angle_count = 0;
            //     foreach ($procedure_data['photoSets'] as $key => $case) {
            //         $bb_angle_count++;
            //         $bb_new_image_case = isset($case['highResPostProcessedImageLocation']) && !is_null($case['highResPostProcessedImageLocation'])
            //             ? $case['highResPostProcessedImageLocation'] 
            //                 : (isset($case['postProcessedImageLocation']) && !is_null($case['postProcessedImageLocation']) 
            //                     ? $case['postProcessedImageLocation'] 
            //                     : $case['originalBeforeLocation']);
            //         $images[] = [
            //             '@type' => 'ImageObject',
            //             'url' => $bb_new_image_case,
            //             'caption' => empty($default_and_seo_page_title) ? "$case_page_title - angle $bb_angle_count" : "$default_and_seo_page_title - angle $bb_angle_count", 
            //             'representativeOfPage' => true,
            //             'thumbnailUrl' => $bb_new_image_case
            //         ];
            //     }
            // }

            // $bb_pro_cat_page = isset($parts[1]) ? home_url() . '/' . $parts[0] . '/' . $parts[1] . '/' : home_url();
            // $gallery_slug_page_id = $page_id_via_slug;
            // $bb_gallery_page_title = get_the_title($gallery_slug_page_id);
            // $default_and_seo_page_title = empty($default_and_seo_page_title) ? $bb_gallery_page_title : $default_and_seo_page_title;
            
            // if(isset($parts[2])) {
            //     $bb_input = $parts[2];
            //     if (isset($bb_input) && preg_match('/^\d+$/', $bb_input)) {
            //         $bb_case_url_title = $case_page_title;
            //     }else {
            //         $bb_case_url_title = $bb_input;
            //     }
            // }
            ?>
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
                <div class="bb-patient-box">
                    <div class="bb-patient-left">

                    </div>
                    <div class="bb-patient-right">
                        
                        
                       
                        <div class="bb-patient-slides">
                           
                        </div>    
                    </div>
                </div>
              
            </div>
        </main>
    </div>
    
<?php  
} else { 
    ?> 
    <div class="bb-container-main">
        <main class="bb-main">
            <?php include plugin_dir_path(__FILE__) . 'sidebar-template.php'; ?>
            <?php

                $brag_page_data =  get_option('bragbook_landing_page_text');  
                $explode_string = explode('[', $brag_page_data);
                $input_string = $explode_string['1'];
                $pattern = '/category="([^"]*)"/';
                if (preg_match($pattern, $input_string, $matches)) {
                    $carousel_category = $matches[1];
                }
                $bbrag_procedure_title = preg_replace('/[^a-zA-Z0-9]+/', '-', $carousel_category);
                $bbrag_procedure_title = strtolower($bbrag_procedure_title);
                $category_title_ = get_option($bbrag_procedure_title);
                
                // $bb_matching_case_data = category_procedure_case($bbrag_procedure_title, $api_data, $category_title_);
                // $matching_case_data = $bb_matching_case_data[1];
                // $mainEntity = [];
                // $hasPart = [];
                // foreach($matching_case_data as $procedure_data) {
                //     $bb_new_image_procedure_data = isset($procedure_data['photoSets'][0]['highResPostProcessedImageLocation']) && !is_null($procedure_data['photoSets'][0]['highResPostProcessedImageLocation'])
                //         ? $procedure_data['photoSets'][0]['highResPostProcessedImageLocation'] 
                //             : (isset($procedure_data['photoSets'][0]['postProcessedImageLocation']) && !is_null($procedure_data['photoSets'][0]['postProcessedImageLocation']) 
                //                 ? $procedure_data['photoSets'][0]['postProcessedImageLocation'] 
                //                 : $procedure_data['photoSets'][0]['originalBeforeLocation']);

                //     $images= [];
                //      $mainEntity[] = [
                //         "@type" => "ImageGallery",
                //         "name" => "Before and after " . $bbrag_procedure_title . " Gallery",
                //         "url" => home_url() . $bbrag_case_url . $bbrag_procedure_title,
                //         "image" => $bb_new_image_procedure_data
                //      ];
                //      foreach ($procedure_data['photoSets'] as $key => $case) {
                //         $bb_new_image_case = isset($case['highResPostProcessedImageLocation']) && !is_null($case['highResPostProcessedImageLocation'])
                //                         ? $case['highResPostProcessedImageLocation'] 
                //                             : (isset($case['postProcessedImageLocation']) && !is_null($case['postProcessedImageLocation']) 
                //                                 ? $case['postProcessedImageLocation'] 
                //                                 : $case['originalBeforeLocation']);
                //         $images[] = [
                //             "@type" => "ImageObject",
                //             "name" => $bbrag_procedure_title,
                //             "url" => home_url() . $bbrag_case_url . $bbrag_procedure_title . "/" . $procedure_data['photoSets'][0]['caseId'] . "/",
                //             "thumbnailUrl" => $bb_new_image_case
                //         ];
                //     }
                    
                //     $hasPart[] = [
                //         "@type" => "ImageGallery",
                //         "name" => "Featured " . $bbrag_procedure_title . " Cases",
                //         "description" => "Highlighted before and after " . $bbrag_procedure_title . " cases with high-quality images.",
                //         "url" => home_url() . $bbrag_case_url . $bbrag_procedure_title,
                //         "image" => $images
                //     ];
                // }
                
                // $bb_description = isset($explode_string['0']) ? $explode_string['0'] : '';
                // $bb_page_title = get_the_title($page_id_via_slug);
                // $schema = [
                //     "@context" => "https://schema.org",
                //     "@type" => "CollectionPage",
                //     "name" => $bb_page_title,
                //     "description" => $bb_description,
                //     "url" => home_url().$bbrag_case_url,
                //     "mainEntity" => $mainEntity,
                //     "hasPart" => $hasPart,
                //     "breadcrumb" => [
                //         "@type" => "BreadcrumbList",
                //         "itemListElement" => [
                //             ["@type" => "ListItem", "position" => 1, "name" => "Home", "item" => home_url()],
                //             ["@type" => "ListItem", "position" => 2, "name" => get_the_title(), "item" => home_url() . $bbrag_case_url]
                //         ]
                //     ]
                // ];
                // $schema_json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                ?>
                <script type="application/ld+json">
                    <?php //echo $schema_json; ?>
                </script>
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
                    echo isset($explode_string['0']) ? $explode_string['0'] : '';
                    foreach ($explode_string as $string) {
                        if (strpos($string, 'shortcode') !== false) {
                            echo do_shortcode('[' . $string);
                        }
                    }
                ?>
                <a href="/<?php echo $parts[0]; ?>/consultation/" class="bb-sidebar-btn mobile-footer">REQUEST A CONSULTATION</a>
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
    <?php
    }

    function category_procedure_case($bbrag_procedure_title, $api_data, $category_title_) {
        $bbrag_procedure_id = get_option($bbrag_procedure_title . '_id');
        $category_to_match = $bbrag_procedure_id;
        $procedure_title = get_option($bbrag_procedure_id . '_title'); 
        $cat_data = json_decode(get_option(get_option($bbrag_procedure_title))); 
        if(!isset($bbrag_procedure_id) && !isset($procedure_title) && !isset($bbrage_cat_title_)) {
            $cat_data = json_decode(get_option('Face'));
            $category_title_ = 'Face';
        }

        $matching_data = [];
        $procedure_counts = [];
        if(!empty($api_data) && is_array($api_data)) {
            foreach ($api_data as $item) {
                if (!empty($cat_data)) {
                    foreach($cat_data as $category_id => $category_data) {
                        $procedures_data = $category_data->procedures_data;
                        foreach($procedures_data as $complete_category) {
                            if (in_array($complete_category->id, $item['procedureIds']) && $category_title_ == $category_data->category_name) {
                                if(!empty($item['photoSets']) && $complete_category->name == $procedure_title ) { 
                                    if (!isset($procedure_counts[$complete_category->id])) {
                                        $procedure_counts[$complete_category->id] = 0;
                                    }
                                    $procedure_counts[$complete_category->id]++;
                                    $item['procedure_title'] = $complete_category->name;
                                    $item['procedure_case_count']  = $procedure_counts[$complete_category->id];
                                    $item['procedure_id'] = $complete_category->id;
                                    $item['procedure_details'] = $complete_category->procedureDetails;

                                    $matching_data[] = $item;
                                }elseif(!empty($item['photoSets']) && $procedure_title == '') {
                                    if (!isset($procedure_counts[$complete_category->id])) {
                                        $procedure_counts[$complete_category->id] = 0;
                                    }
                                    $procedure_counts[$complete_category->id]++;
                                    $item['procedure_title'] = $complete_category->name;
                                    $item['procedure_case_count']  = $procedure_counts[$complete_category->id];
                                    $item['procedure_id'] = $complete_category->id;
                                    $item['procedure_details'] = $complete_category->procedureDetails;

                                    $matching_data[] = $item;
                                }
                            }
                        }
                    }
                } else {
                    if ($category_to_match == '') {
                        $category_to_match = $item['procedureIds'][0];
                    }
                    if (in_array($category_to_match, $item['procedureIds'])) {
                        $item['procedure_id'] = $category_to_match;
                        $matching_data[] = $item;
                    }
                }
            }
        }
        $bb_match_info = [];
        $bb_match_info[] =  $category_title_;
        $bb_match_info[] =  $matching_data;
        return $bb_match_info;
    
    }
get_footer();
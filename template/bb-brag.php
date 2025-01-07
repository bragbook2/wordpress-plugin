<?php
/*
Template Name: Brag Page Template
*/

get_header(); 
$bbrag_case_url = strtok($_SERVER["REQUEST_URI"], '?');
$bbragbook_case_url = trim($bbrag_case_url, '/');
$parts = explode('/', $bbragbook_case_url);
$page = get_page_by_path($parts[0]);
$page_id_via_slug = $page->ID;

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
            <?php
                $bbrag_procedure_id = get_option($bbrag_procedure_title . '_id');
                
                $category_to_match = $bbrag_procedure_id;
                $procedure_title = get_option($bbrag_procedure_id . '_title');
                $cat_data_bb = json_decode(get_option("bb_api_data"));
                       
               $cat_data = json_decode(get_option(get_option($bbrag_procedure_title))); 
                $category_title_ = get_option($bbrag_procedure_title);                 
                
                if(!isset($bbrag_procedure_id) && !isset($procedure_title) && !isset($bbrage_cat_title_)) {
                    $cat_data = json_decode(get_option('Face'));
                    $category_title_ = 'Face';
                }
                $matching_data = [];
 
                $procedure_counts = [];
                foreach ($properties_data as $token_key_bb => $token_bb) {
                    foreach ($token_bb as $bb_website_id => $website_id_bb) {
                        foreach ($website_id_bb as $websiteproperty_id => $property_data) {
                            $api_data = $property_data['api_data'];
                            if(empty($api_data)){
                                continue;
                            }
                            foreach ($api_data as $item) {
                                
                                if (!empty($all_properties)) {
                                    foreach($all_properties[$websiteproperty_id] as $category_id => $category_data) {
                                        $procedures_data = $category_data['procedures_data'];
                                        
                                        foreach($procedures_data as $complete_category) {
                                            if (in_array($complete_category['id'], $item['procedureIds'])) {
                                                if (!empty($item['photoSets']) && $complete_category['name'] == $procedure_title ) { 
                                                    if (!isset($procedure_counts[$complete_category['id']])) {
                                                        $procedure_counts[$complete_category['id']] = 0;
                                                    }

                                                    $procedure_counts[$complete_category['id']]++;
                                                    $item['procedure_title'] = $complete_category['name'];
                                                    $item['procedure_case_count']  = $procedure_counts[$complete_category['id']];
                                                    $item['procedure_id'] = $complete_category['id'];
                                                    $item['procedure_details'] = $complete_category['procedureDetails'];
                                                    $item['bb_api_token'] = $token_key_bb;
                                                    $item['bb_website_id'] = $bb_website_id;
                                                    $matching_data[] = $item;

                                                } elseif (!empty($item['photoSets']) && $procedure_title == '') {
                                                    if (!isset($procedure_counts[$complete_category['id']])) {
                                                        $procedure_counts[$complete_category['id']] = 0;
                                                    }
                                                    $procedure_counts[$complete_category['id']]++;
                                                    $item['procedure_title'] = $complete_category['name'];
                                                    $item['procedure_case_count']  = $procedure_counts[$complete_category['id']];
                                                    $item['procedure_id'] = $complete_category['id'];
                                                    $item['procedure_details'] = $complete_category['procedureDetails'];
                                                    $item['bb_api_token'] = $token_key_bb;
                                                    $item['bb_website_id'] = $bb_website_id;
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
                                        $item['bb_api_token'] = $token_key_bb;
                                        $item['bb_website_id'] = $bb_website_id;
                                        $matching_data[] = $item;
                                    }
                                }
                            }
                        }
                    }
                }
                $matching_case_data = $matching_data;
                $number_of_cases = count($matching_case_data); 

                // Generate schema JSON-LD
                $images = [];
                foreach($matching_case_data as $procedure_data) {
                    foreach ($procedure_data['photoSets'] as $key => $case) {
                        $bb_new_image_case = isset($case['highResPostProcessedImageLocation']) && !is_null($case['highResPostProcessedImageLocation'])
                            ? $case['highResPostProcessedImageLocation'] 
                                : (isset($case['postProcessedImageLocation']) && !is_null($case['postProcessedImageLocation']) 
                                    ? $case['postProcessedImageLocation'] 
                                    : $case['originalBeforeLocation']);

                        $images[] = [
                            "@type" => "ImageObject",
                            "name" => $bbrag_procedure_title,
                            "description" => "Photo gallery of $bbrag_procedure_title results showing before and after photos from different angles.",
                            "url" => home_url() . $bbrag_case_url . $case['caseId'],
                            "thumbnailUrl" => $bb_new_image_case
                        ];
                    }
                }
               
                $schema = [
                    "@context" => "https://schema.org",
                    "@type" => "ImageGallery",
                    "name" => "$bbrag_procedure_title Before & After Gallery",
                    "description" => "Review $number_of_cases $bbrag_procedure_title before and after case. Each case includes photos from multiple angles, along with details about the procedure.",
                    "url" => home_url() . $bbrag_case_url,
                    "image" => $images,
                    "breadcrumb" => [
                        "@type" => "BreadcrumbList",
                        "itemListElement" => [
                            [
                                "@type" => "ListItem",
                                "position" => 1,
                                "name" => "Home",
                                "item" => home_url()
                            ],
                            [
                                "@type" => "ListItem",
                                "position" => 2,
                                "name" => $bbrag_case_url,
                                "item" => home_url() . $bbrag_case_url
                            ],
                            [
                                "@type" => "ListItem",
                                "position" => 3,
                                "name" => $bbrag_procedure_title,
                                "item" => home_url() . $bbrag_case_url
                            ]
                        ]
                    ]
                ];

                $schema_json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            ?>
            <script type="application/ld+json">
                <?php echo $schema_json; ?>
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
                        <h2><span><?php echo empty($procedure_title) ? $category_title_ : $procedure_title; ?></span> Before & After Gallery</h2>
                    </div>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            var procedure_title = "<?php echo $procedure_title; ?> Before & After Gallery";
                            document.querySelector('head title').textContent = procedure_title;
                        });
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
                                        <div class="bb-filter-content-inner-wrapper">
                                            <button class="accordion">Height <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/down-arrow.svg" alt="down"></button>
                                            <div class="panel">
                                                <div class="bb-filter-select-wrapper">
                                                    <div class="bb-input-box">
                                                        <label class="bb-checkbox-container" for="m-height-smaller-59"> Under 5'0"
                                                            <input type="checkbox" id="m-height-smaller-59" data-filter="height" data-min_value="infinity" data-max_value="59" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-height-60-to-63"> 5'0" to 5'3"
                                                            <input type="checkbox" id="m-height-60-to-63" data-filter="height" data-min_value="60" data-max_value="63" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-height-64-to-67"> 5'4" to 5'7"
                                                            <input type="checkbox" id="m-height-64-to-67" data-filter="height" data-min_value="64" data-max_value="67" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-height-68-to-71">5'8" to 5'11"
                                                            <input type="checkbox" id="m-height-68-to-71" data-filter="height" data-min_value="68" data-max_value="71" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-height-greater-72"> 6'0" and Over
                                                            <input type="checkbox" id="m-height-greater-72" data-filter="height" data-min_value="72" data-max_value="infinity" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bb-filter-content-inner-wrapper">
                                            <button class="accordion">Weight <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/down-arrow.svg" alt="down"></button>
                                            <div class="panel">
                                                <div class="bb-filter-select-wrapper">
                                                    <div class="bb-input-box">
                                                        
                                                        <label class="bb-checkbox-container" for="m-weight-smaller-99"> Under 100 lbs
                                                            <input type="checkbox" id="m-weight-smaller-99" data-filter="weight" data-min_value="infinity" data-max_value="99" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-weight-100-to-115">100 to 115 lbs
                                                            <input type="checkbox" id="m-weight-100-to-115" data-filter="weight" data-min_value="100" data-max_value="115" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-weight-116-to-125">116 to 125 lbs
                                                            <input type="checkbox" id="m-weight-116-to-125" data-filter="weight" data-min_value="116" data-max_value="125" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-weight-126-to-135">126 to 135 lbs
                                                            <input type="checkbox" id="m-weight-126-to-135" data-filter="weight" data-min_value="126" data-max_value="135" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-weight-136-to-145">136 to 145 lbs
                                                            <input type="checkbox" id="m-weight-136-to-145" data-filter="weight" data-min_value="136" data-max_value="145" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-weight-146-to-155">146 to 155 lbs
                                                            <input type="checkbox" id="m-weight-146-to-155" data-filter="weight" data-min_value="146" data-max_value="155" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-weight-156-to-165">156 to 165 lbs
                                                            <input type="checkbox" id="m-weight-156-to-165" data-filter="weight" data-min_value="156" data-max_value="165" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-weight-166-to-175">166 to 175 lbs
                                                            <input type="checkbox" id="m-weight-166-to-175" data-filter="weight" data-min_value="166" data-max_value="175" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-weight-176-to-185">176 to 185 lbs
                                                            <input type="checkbox" id="m-weight-176-to-185" data-filter="weight" data-min_value="176" data-max_value="185" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-weight-greater-186">186 lbs and Over
                                                            <input type="checkbox" id="m-weight-greater-186" data-filter="weight" data-min_value="186" data-max_value="infinity" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                    </div>
                                                </div> 
                                            </div>
                                        </div>
                                        <div class="bb-filter-content-inner-wrapper">
                                            <button class="accordion">Gender <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/down-arrow.svg" alt="down"></button>
                                            <div class="panel">
                                                <div class="bb-filter-select-wrapper">
                                                    <div class="bb-input-box">
                                                    <label class="bb-checkbox-container" for="m-gender-female">Female
                                                            <input type="checkbox" id="m-gender-female" data-filter="gender" data-value="female" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-gender-male">Male
                                                            <input type="checkbox" id="m-gender-male" data-filter="gender" data-value="male" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>     
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bb-filter-content-inner-wrapper">
                                            <button class="accordion">Race <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/down-arrow.svg" alt="down"></button>
                                            <div class="panel">
                                                <div class="bb-filter-select-wrapper">
                                                    <div class="bb-input-box">
                                                        <label class="bb-checkbox-container" for="m-race-American-Indian-or-Alaska-Native">American Indian or Alaska Native
                                                            <input type="checkbox" id="m-race-American-Indian-or-Alaska-Native" data-filter="race" data-value="American Indian or Alaska Native" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-race-Asian">Asian
                                                            <input type="checkbox" id="m-race-Asian" data-filter="race" data-value="Asian" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-race-Black-or-African-American">Black or African American
                                                            <input type="checkbox" id="m-race-Black-or-African-American" data-filter="race" data-value="Black or African American" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-race-hispanic-or-latino">Hispanic or Latino
                                                            <input type="checkbox" id="m-race-hispanic-or-latino" data-filter="race" data-value="Hispanic or Latino" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-race-middle-eastern">Middle Eastern
                                                            <input type="checkbox" id="m-race-middle-eastern" data-filter="race" data-value="Middle Eastern" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-race-native-american">Native American
                                                            <input type="checkbox" id="m-race-native-american" data-filter="race" data-value="Native American" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-race-Native-Hawaiian-or-Other-Pacific-Islander">Native Hawaiian or Other Pacific Islander
                                                            <input type="checkbox" id="m-race-Native-Hawaiian-or-Other-Pacific-Islander" data-filter="race" data-value="Native Hawaiian or Other Pacific Islander" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-race-white">White
                                                            <input type="checkbox" id="m-race-white" data-filter="race" data-value="white" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bb-filter-content-inner-wrapper">
                                            <button class="accordion">Age <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/down-arrow.svg" alt="down"></button>
                                            <div class="panel">
                                                <div class="bb-filter-select-wrapper">
                                                    <div class="bb-input-box">
                                                        <label class="bb-checkbox-container" for="m-age-smaller-21"> Under 22
                                                            <input type="checkbox" id="m-age-smaller-21" data-filter="age" data-min_value="infinity" data-max_value="21" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-age-between-22-and-35">22 to 35
                                                            <input type="checkbox" id="m-age-between-22-and-35" data-filter="age" data-min_value="22" data-max_value="35" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-age-Between-36-and-45">36 to 45
                                                            <input type="checkbox" id="m-age-Between-36-and-45" data-filter="age" data-min_value="36" data-max_value="45" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-age-Between-46-and-55">46 to 55
                                                            <input type="checkbox" id="m-age-Between-46-and-55" data-filter="age" data-min_value="46" data-max_value="55" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-age-Between-56-and-65">56 to 65
                                                            <input type="checkbox" id="m-age-Between-56-and-65" data-filter="age" data-min_value="56" data-max_value="65" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-age-Between-66-and-75">66 to 75
                                                            <input type="checkbox" id="m-age-Between-66-and-75" data-filter="age" data-min_value="66" data-max_value="75" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-age-Between-76-and-85">76 to 85
                                                            <input type="checkbox" id="m-age-Between-76-and-85" data-filter="age" data-min_value="76" data-max_value="85" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        <label class="bb-checkbox-container" for="m-age-greater-86"> 86 and older
                                                            <input type="checkbox" id="m-age-greater-86" data-filter="age" data-min_value="86" data-max_value="infinity" onchange="handleDynamicCheckboxChange(this)">
                                                            <span class="checkmark"></span>
                                                        </label>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php 
                                        $processed_keys = [];
                                        $bb_adv_count = 0;
                                        foreach ($matching_case_data as $procedure_data) {
                                            $bb_advance_details = $procedure_data['procedure_details'];
                                            if(!empty($bb_advance_details)) {
                                                foreach ($bb_advance_details as $bb_key => $bb_value) {
                                                    if (isset($bb_value['Options'])) {
                                                        if (!in_array($bb_key, $processed_keys)) {
                                                            $processed_keys[] = $bb_key;
                                                            
                                                            $bb_adv_count++;
                                                            if($bb_adv_count == 1) {
                                                                echo "<div class='advanced-filters'><span>Advanced Filters</span></div>";
                                                            }
                                                        ?>
                                                            
                                                        <div class="bb-filter-content-inner-wrapper">
                                                            <button class="accordion"><?php echo $bb_key; ?> <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/down-arrow.svg" alt="down"></button>
                                                            <div class="panel">
                                                                <div class="bb-filter-select-wrapper">
                                                                    <div class="bb-input-box">
                                                                        <?php 
                                                                            $bb_advance_key = strtolower(str_replace(' ', '_', $bb_key));
                                                                            foreach($bb_value['Options'] as $value) {
                                                                                $bb_advance_value = strtolower(str_replace(' ', '_', $value));
                                                                                ?>
                                                                                <label class="bb-checkbox-container" for="<?php echo $bb_advance_key.$bb_advance_value; ?>"><?php echo $value; ?> 
                                                                                    <input type="checkbox" id="<?php echo $bb_advance_key.$bb_advance_value; ?>" data-filter="<?php echo $bb_advance_key; ?>" data-value="<?php echo $bb_advance_key.$bb_advance_value; ?>" onchange="handleDynamicCheckboxChange(this)">
                                                                                    <span class="checkmark"></span>
                                                                                </label>
                                                                            <?php
                                                                            }
                                                                        ?>     
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php 
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                        ?>
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

                                document.addEventListener("DOMContentLoaded", function() {
                                    var searchField = document.getElementById('searchField');

                                    searchField.addEventListener('keyup', function() {
                                        var query = this.value.toLowerCase();
                                        var contentBoxes = document.querySelectorAll('.bb-content-box');

                                        contentBoxes.forEach(function(box) {
                                            var content = box.textContent.toLowerCase();
                                            if (content.includes(query)) {
                                                box.style.display = '';
                                            } else {
                                                box.style.display = 'none';
                                            }
                                        });
                                    });
                                });

                            </script>
                        </div>
                    </div>
                    <div class="bb-content-boxes">
                        <?php 
                        function bb_limitWords($text, $wordLimit) {
                            if (!is_string($text)) {
                                $text = '';
                            }
                            $words = explode(' ', $text);
                            $words = array_slice($words, 0, $wordLimit);
                            $limitedText = implode(' ', $words);
                            return $limitedText;
                        }

                        $patient_count = 1;
                        function convertKeys($array) {
                            $result = array();
                            if (!is_array($array)) {
                                $array = [];
                            }
                            
                            if(!empty($array)) {
                                foreach ($array as $key => $value) {
                                    if (is_array($value)) {
                                        $result[$key] = convertKeys($value);
                                    } else {
                                        $newKey = str_replace(' ', '_', $key);
                                        $result[$newKey] = $value;
                                    }
                                }
                            }
                            return $result;
                        }
                        function formatArrayToString($data) {
                            if (is_array($data) && !empty($data)) {
                                $key = array_key_first($data);
                                if (isset($data[$key])) {
                                    $subkey = array_key_first($data[$key]);
                                    if (isset($data[$key][$subkey]) && is_array($data[$key][$subkey]) && !empty($data[$key][$subkey])) {
                                        $values = $data[$key][$subkey];
                                        $dynamicString = str_replace(' ', '_', $subkey);

                                        foreach ($values as $value) {
                                            $formattedValue = str_replace(' ', '_', $value);
                                            $dynamicString .= $formattedValue;
                                        }
                                        return $dynamicString;
                                    }
                                }
                            }
                            return ''; 
                        }

                        $bb_case_ids_list = [];
                        $bb_isNude = "";
                        $items_per_page = 10;  
                        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;  
                        $offset = ($page - 1) * $items_per_page;  
                        update_option('bb_matching_case_data_for_ajax', $matching_case_data);
                        $matching_case_data_page = array_slice($matching_case_data, $offset, $items_per_page);
    
                        foreach($matching_case_data_page as $procedure_data) {
                            $convertedArray = convertKeys($procedure_data['procedureDetails']);
                            $formattedString = strtolower(formatArrayToString($convertedArray));
                            $bb_procedure_id = $procedure_data['procedureIds'][0];

                            if (!is_null($procedure_data['procedureDetails'])) {
                                if (isset($procedure_data['procedureDetails'][$bb_procedure_id])) {
                                    $bb_procedureDetails = $procedure_data['procedureDetails'][$bb_procedure_id];
                                    $advanced_filters_result_string = '';
                                    foreach ($bb_procedureDetails as $bb_procedureDetails_key => $bb_procedureDetails_value) {
                                        $bb_procedureDetails_key = strtolower($bb_procedureDetails_key);
                                        $bb_procedureDetails_key = str_replace(' ', '_', $bb_procedureDetails_key);
                                        if (is_array($bb_procedureDetails_value)) {
                                            $bb_procedureDetails_value = implode(',', $bb_procedureDetails_value); 
                                        }
                                        if (is_string($bb_procedureDetails_value)) {
                                            $bb_procedureDetails_value = strtolower($bb_procedureDetails_value);
                                            $bb_procedureDetails_value = str_replace(' ', '_', $bb_procedureDetails_value);
                                        } else {
                                            $bb_procedureDetails_value = '';
                                        }
                                        $advanced_filters_result_string .= $bb_procedureDetails_key . $bb_procedureDetails_value . ' ';
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
                            if(!empty($procedure_data['photoSets'])) { ?>
                                <div class="<?php echo $classString; ?> <?php echo $formattedString;?>">
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
                                            if(isset($procedure_data['photoSets'][0]) && $bb_isNude == "") {
                                                $bb_isNude = $procedure_data['photoSets'][0]['isNude'];
                                            }
                                        ?>
                                        <a href="<?php echo "/" . $parts[0] . "/" . removeAccents_brag(strtolower($converted_procedure_name)) . "/" . $formatted_heading . "/"; ?>">
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
                                                <h5><?php echo $pro_title; ?> : Patient <?php  echo $p_c_count ?></h5>
                                            <?php
                                            }
                                            ?>
                                            <p>
                                                <?php
                                                $bb_details_description = bb_limitWords($procedure_data['details'], 50);
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
                                            if(isset($favorite_caseIds) && !empty($favorite_caseIds) && in_array($procedure_data['photoSets'][0]['caseId'], $favorite_caseIds)) {
                                            ?>
                                                <img class="bb-open-fav-modal" data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>" 
                                                data-bb_api_token="<?php echo $procedure_data['bb_api_token']; ?>" 
                                                data-bb_website_id="<?php echo $procedure_data['bb_website_id']; ?>" 
                                                src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart-outline.svg" alt="heart">

                                            <?php
                                            }else {
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
                                    <div class="bb-content-box-cta">
                                        <a class="view-more-btn" href="<?php echo "/" . $parts[0] . "/" . removeAccents_brag(strtolower($converted_procedure_name)) . "/" . $formatted_heading . "/"; ?>">
                                            View More
                                        </a>
                                    </div>
                                </div>
                                <?php
                                $patient_count++;
                            }
                        }
                        $bb_encode_caseids_list = json_encode($bb_case_ids_list);
                        update_option('bb_caseids_list_' . $page_id_via_slug, $bb_encode_caseids_list);
                        update_option('bb_ajax_path', $parts[0]);
                        ?>
                    <div class="ajax-load-more">
                        <button class="ajax-load-more-btn" data-offset="10">Load More</button>
                    </div>

                    <script>
                        var bb_isNude = "<?php echo $bb_isNude; ?>";
                        if(bb_isNude !== '') {
                            const userResponse = confirm("Are you 18 years old?");
                            if (userResponse) {
                            } else {
                                window.location.href = '/'; 
                            }
                        }
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

            <div class="bb-bottom-bar">
                <img src="<?php echo BB_PLUGIN_DIR_PATH?>assets/images/myfavs-logo.svg" alt="logo">
                <p>
                    <span>Use the MyFavorites tool</span> to help communicate your specific goals. If a result speaks to
                    you, tap the heart.
                </p>
            </div>
        </main>
    </div>
    <script>
        function handleDynamicCheckboxChange(selectedCheckbox) {
            const filter = selectedCheckbox.dataset.filter;
            const checkboxes = document.querySelectorAll('input[type="checkbox"][data-filter="' + filter + '"]');

            checkboxes.forEach(checkbox => {
                if (checkbox !== selectedCheckbox) {
                    checkbox.checked = false;
                }
            });

            const contentBoxes = document.querySelectorAll('.bb-content-box');
            contentBoxes.forEach(box => {
                const classes = box.className.toLowerCase();
                let showBox = true;
                const checkedCheckboxes = Array.from(document.querySelectorAll('input[type="checkbox"]:checked'));
                checkedCheckboxes.forEach(checkedCheckbox => {
                    const currentFilter = checkedCheckbox.dataset.filter.toLowerCase();
                    const minValue = checkedCheckbox.dataset.min_value ? parseInt(checkedCheckbox.dataset.min_value, 10) : null;
                    const maxValue = checkedCheckbox.dataset.max_value ? parseInt(checkedCheckbox.dataset.max_value, 10) : null;
                    const stringValue = checkedCheckbox.dataset.value ? checkedCheckbox.dataset.value.toLowerCase() : '';

                    const attributeMatch = (function() {
                        const attributeClass = classes.match(new RegExp(`${currentFilter}-(\\d+)`));
                        if (attributeClass) {
                            const value = parseInt(attributeClass[1], 10);
                            if (minValue === null && maxValue === null) {
                                return true;
                            } else if (minValue === null) {
                                return value <= maxValue;
                            } else if (maxValue === null) {
                                return value >= minValue;
                            } else {
                                return value >= minValue && value <= maxValue;
                            }
                        } 
                        return false;
                    })();

                    const stringMatch = stringValue !== '' && (stringValue === 'all' || classes.includes(`${currentFilter}-${stringValue}`) || classes.includes(`${stringValue}`));
                    if (!attributeMatch && !stringMatch) {
                        showBox = false; 
                    }
                });
                box.style.display = showBox ? 'block' : 'none';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.bb-checkbox-container input[type="checkbox"]');
            const clearButton = document.getElementById('clearButton');
            clearButton.addEventListener('click', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                const contentBoxes = document.querySelectorAll('.bb-content-box');
                contentBoxes.forEach(box => {
                    box.style.display = 'block';
                });
            });
        });
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

    $bbrag_procedure_id = get_option($bbrag_case_id . "_bb_procedure_id_" . $page_id_via_slug);
    $bbrag_case_id = get_option($bbrag_case_id);  
    $bbrag_procedure_title = get_option($bbrag_procedure_id . '_title'); 
    $bbrag_patient = 1; 

    $category_to_match =  $bbrag_case_id;
    $procedure_title = $bbrag_procedure_title;
    $procedure_id = $bbrag_procedure_id;
    $patient_id =  $bbrag_patient;
    ?>  
    <div class="bb-container-main">
        <main class="bb-main ">
            <?php 
            include plugin_dir_path(__FILE__) . 'sidebar-template.php';
            $matching_data = []; 
            $case_id_list = [];
            $combine_data = [];
            foreach ($properties_data as $token_key_bb => $token_bb) {
                foreach ($token_bb as $bb_website_id => $website_id_bb) { 
                    foreach($website_id_bb as $bb_combine_data) {
                        $bb_combine_data['api_data']['bb_api_token'] = $token_key_bb;
                        $bb_combine_data['api_data']['bb_website_id'] = $bb_website_id;
                        $combine_data[] = $bb_combine_data['api_data'];
                    }
                }
            }

            $api_data_combine = $combine_data;
            if(is_array($api_data_combine) && !empty($api_data_combine)) {
                foreach ($api_data_combine as $bb_item_combine) {
                    foreach ($bb_item_combine as $item) {
                        if($category_to_match == '') {
                            $category_to_match = $item['photoSets'][0]['caseId'];
                        }
                        if (isset($item['photoSets'][0]['caseId']) && $item['photoSets'][0]['caseId'] == $category_to_match) {
                            $item['bb_api_token'] = $bb_item_combine['bb_api_token'];
                            $item['bb_website_id'] = $bb_item_combine['bb_website_id'];
                            $matching_data[] = $item;
                        }
                        if (isset($item['procedureIds']) && in_array($procedure_id, $item['procedureIds'])) {
                            if(!empty($item['photoSets'])) {
                                $case_id_list[] = $item['photoSets'][0]['caseId'];
                            }
                        } 
                    }
                }
            }

            $case_page_title = isset($parts[1]) ? $parts[1] : '';
            if(!empty($case_page_title)) {
                $case_page_title = str_replace('-', ' ', $case_page_title);
                $case_page_title = ucfirst($case_page_title);
            }
            $images = [];
            $setup_wizard = '';
            $procedure_description = '';
            $default_and_seo_page_title = '';
            
            foreach($matching_data as $procedure_data) {
                if(empty($procedure_description)) {
                    $procedure_description = isset($procedure_data['caseDetails'][0]['seoPageDescription']) 
                    && !empty($procedure_data['caseDetails'][0]['seoPageDescription']) ? $procedure_data['caseDetails'][0]['seoPageDescription'] : '';
                }
                if(empty($default_and_seo_page_title)) {
                    $default_and_seo_page_title = isset($procedure_data['caseDetails'][0]['seoPageTitle']) 
                    && !empty($procedure_data['caseDetails'][0]['seoPageTitle']) ? $procedure_data['caseDetails'][0]['seoPageTitle'] : '';
                }

                $bb_angle_count = 0;
                foreach ($procedure_data['photoSets'] as $key => $case) {
                    $bb_angle_count++;
                    $bb_new_image_case = isset($case['highResPostProcessedImageLocation']) && !is_null($case['highResPostProcessedImageLocation'])
                        ? $case['highResPostProcessedImageLocation'] 
                            : (isset($case['postProcessedImageLocation']) && !is_null($case['postProcessedImageLocation']) 
                                ? $case['postProcessedImageLocation'] 
                                : $case['originalBeforeLocation']);
                    $images[] = [
                        '@type' => 'ImageObject',
                        'url' => $bb_new_image_case,
                        'caption' => empty($default_and_seo_page_title) ? "$case_page_title - angle $bb_angle_count" : "$default_and_seo_page_title - angle $bb_angle_count", 
                        'representativeOfPage' => true,
                        'thumbnailUrl' => $bb_new_image_case
                    ];
                }
            }

            $bb_pro_cat_page = isset($parts[1]) ? home_url() . '/' . $parts[0] . '/' . $parts[1] . '/' : home_url();
            $gallery_slug_page_id = $page_id_via_slug;
            $bb_gallery_page_title = get_the_title($gallery_slug_page_id);
            $default_and_seo_page_title = empty($default_and_seo_page_title) ? $bb_gallery_page_title : $default_and_seo_page_title;
            
            if(isset($parts[2])) {
                $bb_input = $parts[2];
                if (isset($bb_input) && preg_match('/^\d+$/', $bb_input)) {
                    $bb_case_url_title = $case_page_title;
                }else {
                    $bb_case_url_title = $bb_input;
                }
            }
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
                        <div class="bb-patient-row">
                            <?php 
                            $bb_seo_detail = '';
                            foreach ($matching_data as $procedure_data) {
                                $bb_seo_detail = isset($procedure_data['caseDetails'][0]) ? $procedure_data['caseDetails'][0] : [];
                            } 
                            if (isset($bb_seo_detail['seoHeadline']) && !empty($bb_seo_detail['seoHeadline'])) {
                                echo "<h2>" . $bb_seo_detail['seoHeadline'] . "</h2>";  
                            } else {
                            ?>
                                <h2><?=$procedure_title;?> Patient <span><?=$patient_id?></span></h2>
                            <?php 
                            }

                            if (in_array($procedure_data['photoSets'][0]['caseId'], $favorite_caseIds)) {
                            ?>
                                <img class="bb-heart-icon bb-open-fav-modal" data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>" 
                                data-bb_api_token="<?php echo $procedure_data['bb_api_token']; ?>" 
                                data-bb_website_id="<?php echo $procedure_data['bb_website_id']; ?>" 
                                src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart-outline.svg" alt="heart">

                                    <?php
                            } else {
                            ?>
                                <img class="bb-heart-icon bb-open-fav-modal" data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>" 
                                data-bb_api_token="<?php echo $procedure_data['bb_api_token']; ?>" 
                                data-bb_website_id="<?php echo $procedure_data['bb_website_id']; ?>" 
                                src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart.svg" alt="heart">

                            <?php
                            }
                            ?>
                        </div>
                        <?php 
                            
                        $patient_detail = $height = $width = $race = $gender = $age = $bb_seo_detail = $timeframe2 = $timeframe = $revision_surgery = '';
                        foreach($matching_data as $procedure_data) {
                            $patient_detail = !empty($procedure_data['details']) ? $procedure_data['details'] : '';
                            $height = !empty($procedure_data['height']) ? '<li>HEIGHT: ' . strtolower($procedure_data['height']) . '</li>' : '';
                            $width = !empty($procedure_data['weight']) ? '<li>WEIGHT: ' . strtolower($procedure_data['weight']) . '</li>' : '';
                            $race = !empty($procedure_data['ethnicity']) ? '<li>RACE: ' . strtolower($procedure_data['ethnicity']) . '</li>' : '';
                            $gender = !empty($procedure_data['gender']) ? '<li>GENDER: ' . strtolower($procedure_data['gender']) . '</li>' : '';
                            $age = !empty($procedure_data['age']) ? '<li>AGE: ' . strtolower($procedure_data['age']) . '</li>' : '';
                            $timeframe = !empty($procedure_data['after1Timeframe']) && !empty($procedure_data['after1Unit']) ? '<li>POST-OP PERIOD: ' . strtolower($procedure_data['after1Timeframe']) . ' ' . strtolower($procedure_data['after1Unit']) . '</li>' : '';
                            $timeframe2 = !empty($procedure_data['after2Timeframe']) && !empty($procedure_data['after2Unit']) ? '<li>2nd AFTER: ' . strtolower($procedure_data['after2Timeframe']) . ' ' . strtolower($procedure_data['after2Unit']) . '</li>' : '';
                            $revision_surgery = !empty($procedure_data['revisionSurgery']) ? '<li>This case is a revision of a previous procedure.</li>' : '';
                            $bb_seo_detail = isset($procedure_data['caseDetails'][0]) ? $procedure_data['caseDetails'][0] : [];
                          
                            foreach ($procedure_data['photoSets'] as $key => $value) {
                                $bb_new_image_value = isset($value['highResPostProcessedImageLocation']) && !is_null($value['highResPostProcessedImageLocation'])
                                    ? $value['highResPostProcessedImageLocation'] 
                                        : (isset($value['postProcessedImageLocation']) && !is_null($value['postProcessedImageLocation']) 
                                            ? $value['postProcessedImageLocation'] 
                                            : $value['originalBeforeLocation']);
                                
                                ?>
                                    <img class="bbrag_gallery_image testing-image" src="<?php echo $bb_new_image_value; ?>" 
                                    alt="<?php echo isset($value['seoAltText']) ? $value['seoAltText'] : ''; ?>">
                                <?php
                            }
                        }    
                        ?>
                    </div>
                    <div class="bb-patient-right">
                        <div class="bb-patient-row">
                            <?php
                            if (isset($bb_seo_detail['seoHeadline']) && !empty($bb_seo_detail['seoHeadline'])) {
                              echo "<h2>" . $bb_seo_detail['seoHeadline'] . "</h2>";  
                            } else {
                            ?>
                                <h2><?=$procedure_title;?> Patient <span><?=$patient_id?></span></h2>
                            <?php 
                            }
                            $bb_seo_page_title = esc_js($procedure_title);

                            if (isset($bb_seo_detail['seoPageTitle']) && !empty($bb_seo_detail['seoPageTitle'])) {
                                $bb_seo_page_title = esc_js($bb_seo_detail['seoPageTitle']);
                            }
                            ?>
                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    var bb_seo_title = "<?php echo $bb_seo_page_title; ?>";
                                    document.querySelector('head title').textContent = bb_seo_title;
                                });
                            </script>
                            <?php 
                            if (!empty($procedure_data) && is_array($procedure_data)) {
                                if (in_array($procedure_data['photoSets'][0]['caseId'], $favorite_caseIds)) {
                                ?>
                                    <img class="bb-heart-icon bb-open-fav-modal" data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>" 
                                    data-bb_api_token="<?php echo $procedure_data['bb_api_token']; ?>" 
                                    data-bb_website_id="<?php echo $procedure_data['bb_website_id']; ?>" 
                                    src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart-outline.svg" alt="heart">

                                    <?php
                                } else {
                                ?>
                                    <img class="bb-heart-icon bb-open-fav-modal" data-case-id="<?php echo $procedure_data['photoSets'][0]['caseId'] ?>" 
                                    data-bb_api_token="<?php echo $procedure_data['bb_api_token']; ?>" 
                                    data-bb_website_id="<?php echo $procedure_data['bb_website_id']; ?>" 
                                    src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart.svg" alt="heart">

                                <?php
                                }
                            }
                            ?>
                        </div>
                        
                        <ul class="bb-patient-features">
                            <?php echo $height ?>
                            <?php echo $width ?>
                            <?php echo $race ?>
                            <?php echo $gender ?>
                            <?php echo $age ?>
                            <?php echo $timeframe ?>
                            <?php echo $timeframe2 ?>
                            <?php echo $revision_surgery ?>
                        </ul>

                        <?=$patient_detail?>

                        <a href="/<?php echo $parts[0]; ?>/consultation/" class="bb-sidebar-btn mobile-footer">REQUEST A CONSULTATION</a>
                        
                        <div class="bb-patient-slides">
                            <?php 
                            function update_url($new_case_id, $page_id_via_slug) {
                                $url = strtok($_SERVER["REQUEST_URI"], '?');
                                $path_parts = explode('/', $url);
                                $procedure_id_bb = get_option($new_case_id . '_bb_procedure_id_' . $page_id_via_slug);
                                $procedure_title = get_option($procedure_id_bb . '_title');
                                $converted_procedure_name = str_replace(' ', '-', strtolower($procedure_title));
                                $converted_procedure_name = removeAccents_brag($converted_procedure_name);
                                $path_parts[count($path_parts) - 3] = $converted_procedure_name; 
                                $path_parts[count($path_parts) - 2] = get_option($new_case_id); 
                            
                                return implode('/', $path_parts);
                            }

                            function generate_pagination($current_case_id, $case_id_list, $page_id_via_slug) {
                                if(!is_array($case_id_list) && empty($case_id_list)) {
                                    return false;
                                }
                                $currentIndex = is_array($case_id_list) ? array_search($current_case_id, $case_id_list) : 1;
                                $case_id_list = is_array($case_id_list) && !empty($case_id_list) ? $case_id_list : [1];
                                $prevIndex = max($currentIndex - 1, 0);
                                $nextIndex = min($currentIndex + 1, count($case_id_list) - 1);
                                $start = max($currentIndex - 1, 0);
                                $end = min($start + 3, count($case_id_list) - 1);
                                $end = min($end, $start + 3);

                                echo '<ul>';
                                if ($currentIndex == 0) {
                                    echo '<li style="display:none;"><a href="#">Previous</a></li>';
                                } else {
                                    echo '<li><a href="' . update_url($case_id_list[$prevIndex], $page_id_via_slug) . '"> &lt; <span>Previous</span></a></li>';
                                }
                                $page_count = $start + 1;
                                for ($i = $start; $i <= $end; $i++) {
                                    $case_id = $case_id_list[$i];
                                    $activeClass = ($case_id == $current_case_id) ? 'active' : ''; 
                                    if (!empty($activeClass)) {
                                        update_option('bb_current_case_page_count', $page_count);
                                    ?>
                                    <script> 
                                        var page_c_title = "<?php echo $page_count; ?>";
                                        var elements = document.querySelectorAll('.bb-patient-row h2 span');
                                        elements.forEach(function(element) {
                                            element.textContent = page_c_title;
                                        });
                                    </script>
                                    <?php
                                    }
                                    echo '<li class="' . $activeClass . ' bb-single-case"><a href="' . update_url($case_id, $page_id_via_slug) . '">' . $page_count++ . '</a></li>';
                                }
                                if($page_count<=2) {
                                ?>
                                <script>
                                    var elements = document.querySelectorAll('.bb-single-case');
                                    elements.forEach(function(element) {
                                        element.style.display = 'none';
                                    });
                                </script>
                                <?php
                                }
                                if ($currentIndex == count($case_id_list) - 1) {
                                    echo '<li style="display:none;"><a href="#">Next</a></li>';
                                } else {
                                    if ($nextIndex > -1) {
                                        echo '<li><a href="' . update_url($case_id_list[$nextIndex], $page_id_via_slug) . '"><span>Next</span> &gt;</a></li>';
                                    }
                                }
                                echo '</ul>';
                            }

                            $url = strtok($_SERVER["REQUEST_URI"], '?');
                            $path_parts = explode('/', $url);
                            $case_id_list = json_decode(get_option('bb_caseids_list_' . $page_id_via_slug));
                            
                            $current_case_id = $path_parts[3];
                            if (is_numeric($current_case_id)) {
                                $current_case_id = (int)$current_case_id; 
                            } else {
                                $current_case_id = get_option($current_case_id);
                            }  
                            generate_pagination($current_case_id, $case_id_list, $page_id_via_slug);
                            ?>
                        </div>
                    </div>
                </div>
            
                <?php
                
                $bb_current_case_page_count = get_option('bb_current_case_page_count');
                $schema = [
                    "@context" => "https://schema.org",
                    "@type" => "ImageGallery",
                    "name" => "Before and After Gallery $bb_case_url_title : Patient $bb_current_case_page_count",
                    "description" => "Photo gallery of $bb_case_url_title results showing before and after photos from different angles.",
                    'mainEntity' => [
                        '@type' => 'MedicalProcedure',
                        'name' => $default_and_seo_page_title,
                        'description' => $procedure_description,
                        'procedureType' => 'CosmeticProcedure',
                        'medicalSpecialty' => 'PlasticSurgery',
                    ],
                    'image' => $images,
                    'breadcrumb' => array(
                        '@type' => 'BreadcrumbList',
                        'itemListElement' => array(
                            array(
                                '@type' => 'ListItem',
                                'position' => 1,
                                'name' => 'Home',
                                'item' => home_url()
                            ),
                            array(
                                '@type' => 'ListItem',
                                'position' => 2,
                                'name' => $bb_gallery_page_title,
                                'item' => home_url() . '/' . $parts[0] . '/'
                            ),
                            array(
                                '@type' => 'ListItem',
                                'position' => 3,
                                'name' => 'Before and After ' . $case_page_title . ' Gallary',
                                'item' =>  $bb_pro_cat_page
                            ),
                            array(
                                '@type' => 'ListItem',
                                'position' => 4,
                                'name' => $default_and_seo_page_title,
                                'item' =>  home_url() . $bbrag_case_url
                            )
                        )
                    ),
                    'url' =>  home_url() . $bbrag_case_url
                ];
            
                $schema_json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        
                ?>
                <script type="application/ld+json">
                    <?php echo $schema_json; ?>
                </script>
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
                $bbrag_procedure_title = str_replace(' ', '-', $carousel_category);
                $bbrag_procedure_title = strtolower($bbrag_procedure_title);
                $category_title_ = get_option($bbrag_procedure_title);
                $bb_matching_case_data = category_procedure_case($bbrag_procedure_title, $api_data, $category_title_);
                $matching_case_data = $bb_matching_case_data[1];
                $mainEntity = [];
                $hasPart = [];
                foreach($matching_case_data as $procedure_data) {
                    $bb_new_image_procedure_data = isset($procedure_data['photoSets'][0]['highResPostProcessedImageLocation']) && !is_null($procedure_data['photoSets'][0]['highResPostProcessedImageLocation'])
                        ? $procedure_data['photoSets'][0]['highResPostProcessedImageLocation'] 
                            : (isset($procedure_data['photoSets'][0]['postProcessedImageLocation']) && !is_null($procedure_data['photoSets'][0]['postProcessedImageLocation']) 
                                ? $procedure_data['photoSets'][0]['postProcessedImageLocation'] 
                                : $procedure_data['photoSets'][0]['originalBeforeLocation']);

                    $images= [];
                     $mainEntity[] = [
                        "@type" => "ImageGallery",
                        "name" => "Before and after " . $bbrag_procedure_title . " Gallery",
                        "url" => home_url() . $bbrag_case_url . $bbrag_procedure_title,
                        "image" => $bb_new_image_procedure_data
                     ];
                     foreach ($procedure_data['photoSets'] as $key => $case) {
                        $bb_new_image_case = isset($case['highResPostProcessedImageLocation']) && !is_null($case['highResPostProcessedImageLocation'])
                                        ? $case['highResPostProcessedImageLocation'] 
                                            : (isset($case['postProcessedImageLocation']) && !is_null($case['postProcessedImageLocation']) 
                                                ? $case['postProcessedImageLocation'] 
                                                : $case['originalBeforeLocation']);
                        $images[] = [
                            "@type" => "ImageObject",
                            "name" => $bbrag_procedure_title,
                            "url" => home_url() . $bbrag_case_url . $bbrag_procedure_title . "/" . $procedure_data['photoSets'][0]['caseId'] . "/",
                            "thumbnailUrl" => $bb_new_image_case
                        ];
                    }
                    
                    $hasPart[] = [
                        "@type" => "ImageGallery",
                        "name" => "Featured " . $bbrag_procedure_title . " Cases",
                        "description" => "Highlighted before and after " . $bbrag_procedure_title . " cases with high-quality images.",
                        "url" => home_url() . $bbrag_case_url . $bbrag_procedure_title,
                        "image" => $images
                    ];
                }
                
                $bb_description = isset($explode_string['0']) ? $explode_string['0'] : '';
                $bb_page_title = get_the_title($page_id_via_slug);
                $schema = [
                    "@context" => "https://schema.org",
                    "@type" => "CollectionPage",
                    "name" => $bb_page_title,
                    "description" => $bb_description,
                    "url" => home_url().$bbrag_case_url,
                    "mainEntity" => $mainEntity,
                    "hasPart" => $hasPart,
                    "breadcrumb" => [
                        "@type" => "BreadcrumbList",
                        "itemListElement" => [
                            ["@type" => "ListItem", "position" => 1, "name" => "Home", "item" => home_url()],
                            ["@type" => "ListItem", "position" => 2, "name" => get_the_title(), "item" => home_url() . $bbrag_case_url]
                        ]
                    ]
                ];
                $schema_json = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
                ?>
                <script type="application/ld+json">
                    <?php echo $schema_json; ?>
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
                    <img src="<?php echo BB_PLUGIN_DIR_PATH?>assets/images/myfavs-logo.svg" alt="logo">
                    <p><span>Use the MyFavorites tool</span> to help communicate your specific goals. If a result speaks to
                        you, tap the heart.</p>
                    <a class="button-bar-my-favs-btn" href="/favorites/">View My Favorites</a>
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
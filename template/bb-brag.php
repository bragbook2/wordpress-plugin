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

$page_id_via_slug = $page ? $page->ID : null;

if (is_404() || !$page_id_via_slug) {
    get_template_part(404);
    exit;
}

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

$base_path = "/" . $parts[0] . "/";

$is_consultation = ($bbrag_case_url === $base_path . "consultation/");
$is_favorites_base = ($bbrag_case_url === $base_path . "favorites/");
$is_procedure_page = isset($bbrag_procedure_title) && $bbrag_case_url === $base_path . $bbrag_procedure_title . "/";

$is_procedure_detail_page = false;
if (isset($bbrag_procedure_title, $bbrag_case_id)) {
    $expected_procedure_detail_url = $base_path . $bbrag_procedure_title . "/" . $bbrag_case_id . "/";
    $option_key = $bbrag_case_id . "_bb_procedure_id_" . $page_id_via_slug;
    $has_valid_option = get_option($option_key) !== '';
    
    $is_procedure_detail_page = ($bbrag_case_url === $expected_procedure_detail_url && $has_valid_option);
}

if ($is_consultation) {
    include plugin_dir_path(__FILE__) . 'bb-consultation.php';
} elseif ($is_favorites_base) {
    include plugin_dir_path(__FILE__) . 'bb-favorites.php';
} elseif ($is_procedure_page) {
       
    ?>
    <div class="bb-container-main">
        <main class="bb-main ">
            <?php include plugin_dir_path(__FILE__) . 'sidebar-template.php'; ?>
            <div class="bb-content-area">
        
                <div class="bb-filter-attic bb-filter-attic-borderless">
                    <button type="button" class="bb-sidebar-toggle">
                        <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/menu-icon.svg"  style="padding:3px;"  alt="toggle sidebar">
                    </button>
                    <div class="bb-search-container-outer">
                        <form class="search-container mobile-search-container">
                            <input type="text" id="mobile-search-bar" placeholder="Search Procedures" >
                            <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/search-svgrepo-com.svg" class="bb-search-icon" alt="search">
                            <ul id="mobile-search-suggestions" class="search-suggestions"></ul>
                        </form>
                        <h1 id="procedure-title"><span></span></h1>
                    </div>
                    <div class="actions-box">
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
                        </div>
                    </div>
                    <div class="bb-content-boxes">
                        
                    </div>
                    
                    <div class="ajax-load-more" id="load-container" style="display: none;">
                        <button class="bb_ajax-load-more-btn" data-offset="2">Load More</button>
                    </div>
                </div>
            </div>
            <div class="bb-fav-modal">
                <div class="bb-fav-modal-inner"> 
                    <p class="bb-fav-modal-close-button">X</p>
                    <img class="bb-thumbnail" src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/myfavs-logo.svg" alt="logo">
                    <h3>You are Loving these!</h3>
                    <p>To improve the communication between us, keep hearting cases that speak to you. During our
                        consultation, we'll review this collection together so we can discuss your specific goals and
                        concerns.</p>
                    <form method="post">
                        <div class="bb-input-group">
                            <label for="name">Name</label>
                            <input class="bb-is-required" placeholder="Name" type="text" name="name">
                            <span class="bb-is-required-msg">Name is required</span>
                        </div>
                        <div class="bb-input-group">
                            <label for="email">Email Address</label>
                            <input class="bb-is-required" placeholder="Email" type="email" name="email">
                            <span class="bb-is-required-msg">Email is required</span>
                        </div>
                        <div class="bb-input-group">
                            <label for="number">Phone</label>
                            <input class="bb-is-required" placeholder="Phone" type="tel" name="phone">
                            <span class="bb-is-required-msg">Phone is required</span>
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
<?php
} elseif ($is_procedure_detail_page) {
    
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
            $response = wp_remote_post(BB_BASE_URL . '/api/plugin/views?apiToken='. $api_token, array(
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
    ?>  
    <div class="bb-container-main">
        <main class="bb-main ">
            <?php 
            include plugin_dir_path(__FILE__) . 'sidebar-template.php';
            ?>
            <div class="bb-content-area">
                <div class="bb-filter-attic bb-filter-attic-borderless">
                    <button type="button" class="bb-sidebar-toggle">
                        <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/menu-icon.svg"  style="padding:3px;"  alt="toggle sidebar">
                    </button>
                    <div class="bb-search-container-outer">
                        <form class="search-container mobile-search-container">
                            <input type="text" id="mobile-search-bar" placeholder="Search Procedures">
                            <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/search-svgrepo-com.svg" class="bb-search-icon" alt="search">
                            <ul id="mobile-search-suggestions" class="search-suggestions"></ul>
                        </form>
                    </div>
                </div>
                <div class="bb-patient-box">
                    <div class="bb-patient-left">
                    </div>
                    <div class="bb-patient-right">
                            
                    </div>
                </div>
            </div>
            <div class="bb-fav-modal">
                <div class="bb-fav-modal-inner"> 
                    <p class="bb-fav-modal-close-button">X</p>
                    <img class="bb-thumbnail" src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/myfavs-logo.svg" alt="logo">
                    <h3>You are Loving these!</h3>
                    <p>To improve the communication between us, keep hearting cases that speak to you. During our
                        consultation, we'll review this collection together so we can discuss your specific goals and
                        concerns.</p>
                    <form method="post">
                        <div class="bb-input-group">
                            <label for="name">Name</label>
                            <input class="bb-is-required" placeholder="Name" type="text" name="name">
                            <span class="bb-is-required-msg">Name is required</span>
                        </div>
                        <div class="bb-input-group">
                            <label for="email">Email Address</label>
                            <input class="bb-is-required" placeholder="Email" type="email" name="email">
                            <span class="bb-is-required-msg">Email is required</span>
                        </div>
                        <div class="bb-input-group">
                            <label for="number">Phone</label>
                            <input class="bb-is-required" placeholder="Phone" type="tel" name="phone">
                            <span class="bb-is-required-msg">Phone is required</span>
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
                ?>
            <div class="bb-content-area">
                <div class="bb-filter-attic bb-filter-attic-borderless">
                    <button type="button" class="bb-sidebar-toggle">
                        <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/menu-icon.svg" style="padding:3px;" alt="toggle sidebar">
                    </button>
                    <div class="bb-search-container-outer">
                        <form class="search-container mobile-search-container">
                            <input type="text" id="mobile-search-bar" placeholder="Search Procedures">
                            <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/search-svgrepo-com.svg" class="bb-search-icon" alt="search">
                            <ul id="mobile-search-suggestions" class="search-suggestions"></ul>
                        </form>
                    </div>
                </div>
                <?php
                    if (isset($explode_string[0])) {
                        $content = $explode_string[0];
                        $lines = preg_split("/\r\n|\n|\r/", $content);
                        $output = '';
                        foreach ($lines as $line) {
                            $trimmedLine = trim($line);
                            if ($trimmedLine === '') {
                                continue;
                            }
                            $output .= ($trimmedLine === strip_tags($trimmedLine)) ? "<p>{$trimmedLine}</p>" : $trimmedLine;
                        }
                        echo $output;
                    }
                    
                    $output = array_map(function($item) {
                        $item = str_replace("\r\n", "\n", $item); 
                        $pos = strpos($item, ']');
                    
                        if ($pos !== false) {
                            $shortcode = substr($item, 0, $pos + 1);
                            $after = substr($item, $pos + 1);
                            $afterLines = explode("\n", $after);
                            $afterLines = array_map(function ($line) {
                                $trimmed = trim($line);
                                if ($trimmed === '') return $line;
                                return ($trimmed === strip_tags($trimmed)) ? '<p>' . $trimmed . '</p>' : $line;
                            }, $afterLines);
                            return $shortcode . "\n" . implode("\n", $afterLines);
                        } else {
                            $lines = explode("\n", $item);
                            $lines = array_map(function ($line) {
                                $trimmed = trim($line);
                                if ($trimmed === '') return $line;
                                return ($trimmed === strip_tags($trimmed)) ? '<p>' . $trimmed . '</p>' : $line;
                            }, $lines);
                            return implode("\n", $lines);
                        }
                    }, $explode_string);
                    
                    foreach ($output as $string) {
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

get_footer();
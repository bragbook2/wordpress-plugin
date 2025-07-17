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

if ($page) {
    $page_id_via_slug = $page->ID;
}

$isfavorites = strpos($bbrag_case_url, '/favorites/');
if (isset($isfavorites)) {
    if (count($parts) >= 3) {
        $bbrag_procedure_title = $parts[2];
        $bbrag_procedure_id = get_option($bbrag_procedure_title . '_id');
    }
    if (count($parts) >= 4) {
        $bbrag_procedure_title = $parts[2];
        $bbrag_case_id = $parts[3];
    }
}

if (count($parts) >= 2) {
    $bbrag_procedure_title = $parts[1];
    $bbrag_procedure_id = get_option($bbrag_procedure_title . '_id');
}
if (count($parts) >= 3) {
    $bbrag_procedure_title = $parts[1];
    $bbrag_case_id = $parts[2];
}

if (isset($bbrag_procedure_title)) {
    $bbrag_procedure_title = urldecode($bbrag_procedure_title);
    $bbrag_procedure_title = $bbrag_procedure_title;
}


// Consultation page
if ($bbrag_case_url == "/" . $parts[0] . "/consultation/") {
    include plugin_dir_path(__FILE__) . 'bb-consultation.php';
    return;
}

// Favorites page
if (
    ($bbrag_case_url == "/" . $parts[0] . "/favorites/") ||
    (isset($bbrag_case_id) && $bbrag_case_url == "/" . $parts[0] . "/favorites/" . $bbrag_procedure_title . "/" . $bbrag_case_id . "/")
) {
    include plugin_dir_path(__FILE__) . 'bb-favorites.php';
    return;
}


if (isset($bbrag_procedure_title) && $bbrag_case_url == "/" . $parts[0] . "/" . $bbrag_procedure_title . "/") {

    if (get_option('bb_design_plugin_selector') == "v2") {
        include plugin_dir_path(__FILE__) . 'case-list-template-v2.php';
        return;
    }

    // Case listing page
    include plugin_dir_path(__FILE__) . 'case-list-template.php';
    return;
}

// Case detail page
if (isset($bbrag_procedure_title) && isset($bbrag_case_id) && $bbrag_case_url == "/" . $parts[0] . "/" . $bbrag_procedure_title . "/" . $bbrag_case_id . "/" && get_option($bbrag_case_id . "_bb_procedure_id_" . $page_id_via_slug) !== '') {
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
        if ($page_slug_bb == $parts[0]) {
            $response = wp_remote_post(BB_BASE_URL . '/api/plugin/views?apiToken=' . $api_token, array(
                'method' => 'POST',
                'body' => json_encode(array(
                    'caseId' => $bbrag_case_id,
                )),
                'headers' => array(
                    'Content-Type' => 'application/json',
                ),
            ));
        }
    }
    if (get_option('bb_design_plugin_selector') == "v2") {
        include plugin_dir_path(__FILE__) . 'bb-case-details-v2.php';
        return;
    }
    include plugin_dir_path(__FILE__) . 'case-details-template.php';
    return;
}

include plugin_dir_path(__FILE__) . 'carousel-page-template.php';

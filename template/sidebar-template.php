<?php
/*
Template Name: Sidebar Template
*/

$bbrag_case_url_bb = strtok($_SERVER["REQUEST_URI"], '?');
$bbragbook_case_url_bb = trim($bbrag_case_url_bb, '/');
$parts_page_name = explode('/', $bbragbook_case_url_bb);
$combine_gallery_page_id = get_option('combine_gallery_page_id');
$combine_gallery_page = get_post($combine_gallery_page_id);
$combine_gallery_page_slug = get_option('combine_gallery_slug');
$page_bb_data = get_page_by_path($parts_page_name[0]);
$page_id_via_slug = $page_bb_data->ID;

use mvpbrag\Bb_Api;
/*********************************************************************************************************** */
function bb_get_sidebar_data($parts_page_name, $combine_gallery_page_slug) {
    ob_start();
    update_option("bb_sidebar_data", []);
    
    $api_tokens = get_option('bragbook_api_token', []); 
    $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
    $gallery_slugs = get_option('bb_gallery_page_slug', []); 
    
    $single_results_sidebar = [];
    $combine_results_sidebar = [];
    $token_array = [];
    foreach ($api_tokens as $index => $api_token) {
        $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
        $page_slug_bb = $gallery_slugs[$index] ?? '';
        if(($page_slug_bb == $parts_page_name[0])) {
            if (empty($api_token) || empty($websiteproperty_id)) {
                continue;
            }
            
            $bb_set_transient_urls = get_option( 'bb_set_transient_url_sidebar', [] );
            if ( ! is_array( $bb_set_transient_urls ) ) {
                $bb_set_transient_urls = [];
            }
            $transient_key = 'bb_sidebar_' . md5($api_token);
            if (get_transient($transient_key) !== false) {
                return get_transient($transient_key);
            }
            
            $sidebar = new Bb_Api();
            $data = $sidebar->get_api_sidebar_bb($api_token);
            
            $bb_set_transient_urls[$transient_key] = $data;
            update_option( 'bb_set_transient_url_sidebar', $bb_set_transient_urls );
            set_transient($api_token, $data, 1800);

            $sidebar_set = json_decode($data, true) ?? []; 
            $result = [
                'sidebar_set' => $sidebar_set
            ];
            $single_results_sidebar[$api_token][$websiteproperty_id][$page_slug_bb] = $result;
          
        }elseif($combine_gallery_page_slug == $parts_page_name[0]) {
            $token_array[] = [
                'api_token' => $api_token,
                'websiteproperty_id' => $websiteproperty_id,
                'page_slug_bb' => $page_slug_bb
            ];
        }
    }

    if (!empty($token_array)) { 
        $sidebar = new Bb_Api();
    
        foreach ($token_array as $token_data) {
            $api_token = $token_data['api_token'];
            $websiteproperty_id = $token_data['websiteproperty_id'];
            $page_slug_bb = $token_data['page_slug_bb'];
    
            if (empty($api_token) || empty($websiteproperty_id) || empty($page_slug_bb)) {
                continue;
            }
    
            $body = $sidebar->get_api_sidebar_bb($api_token);
            $sidebar_set = json_decode($body, true) ?? [];
    
            $result = [
                'sidebar_set' => $sidebar_set
            ];

            $combine_results_sidebar[$api_token][$websiteproperty_id][$page_slug_bb] = $result;
        }
    }

    $bragbook_api_sidebar = json_encode($single_results_sidebar);
    $bragbook_combine_api_sidebar = json_encode($combine_results_sidebar);

    update_option("bb_single_sidebar_data", $bragbook_api_sidebar);
    update_option("bb_combine_sidebar_data", $bragbook_combine_api_sidebar);

    ob_clean(); 
}

bb_get_sidebar_data($parts_page_name, $combine_gallery_page_slug);

if($combine_gallery_page_slug == $parts_page_name[0]) {
    $data_sidebar = get_option("bb_combine_sidebar_data");
    $bb_f_ajax_page = 'combine';
} else {
    $data_sidebar = get_option('bb_single_sidebar_data'); 
    $bb_f_ajax_page = 'single';

}
?>
<div class="bb-sidebar">
    <div class="bb-sidebar-wrapper">
        <button type="button" class="bb-sidebar-toggle bb-sidebar-head-toggle">
            <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/caret-right-sm.svg" alt="toggle sidebar">
        </button>
        <form class="search-container">
            <input type="text" id="search-bar" placeholder="Search Procedures">
            <img src="<?php echo BB_PLUGIN_DIR_PATH?>assets/images/search-svgrepo-com.svg" class="bb-search-icon" alt="search">
            <ul id="search-suggestions" class="search-suggestions"></ul>
        </form>

        <div class="bb-nav-accordion">
        <?php 
            $properties_data_all = json_decode($data_sidebar, true);
            $properties_data = $properties_data_all;
            /* 
            Show data for singal page
            */
            $categorized_procedures = [];
            $all_properties = [];
            $api_tokens = get_option('bragbook_api_token', []);
            $values_string = implode(", ", $api_tokens);
            $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
            $values_string_webid = implode(", ", $websiteproperty_ids);

            if (!empty($properties_data) && is_array($properties_data)) {
                foreach ($properties_data as $api_token_key => $token_bb) {
                    foreach ($token_bb as $websiteproperty_id_key => $website_id_bb) {
                        foreach ($website_id_bb as $websiteproperty_id => $property_data) {
                           
                            if(isset($property_data['sidebar_set']['data']) && !empty($property_data['sidebar_set']['data']) && ($parts_page_name[0] == $websiteproperty_id) || ($combine_gallery_page_slug == $parts_page_name[0])) {
                                foreach ($property_data['sidebar_set']['data'] as $procedure_name => $procedure_data) {
                                    ?>
                                    <span class="bb-accordion" cat_title="<?= htmlspecialchars($procedure_data['name']); ?>">
                                        <span><?= $procedure_data['name']; ?> <p>(<?= $procedure_data['totalCase']; ?>)</p></span>
                                        <img src="<?= BB_PLUGIN_DIR_PATH ?>assets/images/plus-icon.svg" alt="plus icon">
                                    </span>
                                    <div class="bb-panel">
                                        <ul>
                                        <?php
                                            foreach($procedure_data['procedures'] as $procedure ) {
                                                if($parts_page_name[0] == $websiteproperty_id) {
                                                    ?>
                                                    <li>
                                                    <a id="<?= esc_attr($procedure['ids'][0]); ?>"
                                                        href="<?= "/" . $parts_page_name[0] . "/" . $procedure['slugName'] . "/"; ?>"
                                                        data-count="1"
                                                        data-api-token="<?= esc_attr($api_token_key); ?>"
                                                        data-website-property-id="<?= esc_attr($websiteproperty_id_key); ?>">
                                                            <?= esc_html($procedure['name']); ?> 
                                                            <span>(<?php echo $procedure['totalCase']; ?>)</span>
                                                    </a>
                                                    
                                                    </li>
                                                    <?php
                                                }elseif($combine_gallery_page_slug == $parts_page_name[0]) {
                                                    $ids_string = implode(", ", $procedure['ids']);
                                                    ?>
                                                    <li>
                                                    <a id="<?= esc_attr($ids_string); ?>"
                                                        href="<?= "/" . $parts_page_name[0] . "/" . $procedure['slugName'] . "/"; ?>"
                                                        data-count="1"
                                                        data-api-token="<?= esc_attr($values_string); ?>"
                                                        data-website-property-id="<?= esc_attr($values_string_webid); ?>">
                                                            <?= esc_html($procedure['name']); ?> 
                                                            <span>(<?php echo $procedure['totalCase']; ?>)</span>
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
                    }
                }
            }                  
        ?>
            <ul>
                <li>
                    <a class="bb-sidebar_favorites" href="/<?=$parts_page_name[0]?>/favorites/"> 
                        <h3> My Favorites <span id="bb_favorite_caseIds_count">(0)</span></h3>
                    </a> 
                </li> 
            </ul>  
        </div>
    </div> 
    
    <a href="/<?=$parts_page_name[0]?>/consultation/" class="bb-sidebar-btn">REQUEST A CONSULTATION</a>
    <p class="request-promo">Ready for the next step?<br>Contact us to request your consultation.</p>    
</div>
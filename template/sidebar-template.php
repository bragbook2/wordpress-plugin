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

/*********************************************************************************************************** */
function bb_get_sidebar_data($parts_page_name, $combine_gallery_page_slug) {
    ob_start();
    update_option("bb_sidebar_data", []);
  //  update_option("bb_combine_api_data", []);
    
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
            $bb_sidebar_url = "https://nextjs-bragbook-app-dev.vercel.app/api/plugin/sidebar?apiToken={$api_token}";
            $sidebar_list = get_api_sidebar_bb($bb_sidebar_url); 
            $sidebar_set = json_decode($sidebar_list, true) ?? []; 
            $result = [
                'sidebar_set' => $sidebar_set
            ];
            $single_results_sidebar[$api_token][$websiteproperty_id][$page_slug_bb] = $result;
          
        }elseif($combine_gallery_page_slug == $parts_page_name[0]) {
            $token_array[] = $api_token;
        }
    }
    if (!empty($token_array)) { 
        $bb_sidebar_url = "https://nextjs-bragbook-app-dev.vercel.app/api/plugin/combine/sidebar";

        $response = wp_remote_post($bb_sidebar_url, array(
            'method'    => 'POST',
            'body'      => json_encode(array(
                'apiTokens'    => $token_array
            )),
            'headers'   => array(
                'Content-Type' => 'application/json',
            ),
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        $body = wp_remote_retrieve_body($response);
        $sidebar_set = json_decode($body);
        $result = [
            'sidebar_set' => $sidebar_set
        ];
        $combine_results_sidebar[$api_token][$websiteproperty_id][$page_slug_bb] = $result; 
    }

    $bragbook_api_sidebar = json_encode($single_results_sidebar);
    $bragbook_combine_api_sidebar = json_encode($combine_results_sidebar);

    update_option("bb_single_sidebar_data", $bragbook_api_sidebar);
    update_option("bb_combine_sidebar_data", $bragbook_combine_api_sidebar);

    ob_clean(); 
}

bb_get_sidebar_data($parts_page_name, $combine_gallery_page_slug);

function get_api_sidebar_bb($url) {
   
    $bb_set_transient_urls = get_option( 'bb_set_transient_url_sidebar', [] );
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
    update_option( 'bb_set_transient_url_sidebar', $bb_set_transient_urls );
    
    set_transient($url, $data, 1800);
    return $data;
   
}
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
            <input type="text" id="search-bar">
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
                                        <h3><?= $procedure_data['name']; ?> <span>(<?= $procedure_data['totalCase']; ?>)</span></h3>
                                        <img src="<?= BB_PLUGIN_DIR_PATH ?>assets/images/plus-icon.svg" alt="plus icon">
                                    </span>
                                    <div class="bb-panel">
                                        <ul>
                                        <?php
                                            foreach($procedure_data['procedures'] as $procedure ) {
                                                
                                                if($parts_page_name[0] == $websiteproperty_id) {
                                                    ?>
                                                    <li>
                                                    <a id="<?= esc_attr($procedure['id']); ?>"
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
                                                ?>
                                                <?php
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
    <!-- <p>Before and after gallery powered by <span style="color:red">BRAG book™</span></p> -->
    
</div>
<?
/*********************************************************************************************************** */
function bb_get_grabbook_category_feed($url) {
    $cats_json = bb_get_grabbook_api($url);
    return $cats_json;
}

function bb_get_grabbook_api($url) {
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

function bb_mvp_brag_shortcode($parts_page_name, $combine_gallery_page_slug) {
    ob_start();
    update_option("bb_api_data", []);
    update_option("bb_combine_api_data", []);
    
    $api_tokens = get_option('bragbook_api_token', []); 
    $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
    $gallery_slugs = get_option('bb_gallery_page_slug', []); 
    
    $all_results = [];
    $combine_results = [];
    
    foreach ($api_tokens as $index => $api_token) {
        $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
        $page_slug_bb = $gallery_slugs[$index] ?? '';
        if(($page_slug_bb == $parts_page_name[0]) || ($combine_gallery_page_slug == $parts_page_name[0])) {
            if (empty($api_token) || empty($websiteproperty_id)) {
                continue;
            }
            $cat_url = "https://bragbookv2.com/api/plugin/categories?apiToken={$api_token}&websitepropertyId={$websiteproperty_id}";
            $category_list = bb_get_grabbook_category_feed($cat_url); 
            $cat_set = json_decode($category_list, true) ?? []; 

            $url = "https://bragbookv2.com/api/plugin/cases?apiToken={$api_token}&websitepropertyId={$websiteproperty_id}";
            $data = bb_get_grabbook_api($url);
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
    }

    $bragbook_api_information = json_encode($all_results);
    $bragbook_combine_api_information = json_encode($combine_results);

    update_option("bb_api_data", $bragbook_api_information);
    update_option("bb_combine_api_data", $bragbook_combine_api_information);

    ob_clean(); 
}
bb_mvp_brag_shortcode($parts_page_name, $combine_gallery_page_slug);

$data = get_option('bb_api_data');
$favorite_email_id = get_option('bragbook_favorite_email');
$favorite_caseIds_count = 0;
$favorite_caseIds = [];
if($combine_gallery_page_slug == $parts_page_name[0]) {
    $data = get_option("bb_combine_api_data");
    $bb_f_ajax_page = 'combine';
} else {
    $data = get_option('bb_api_data'); 
    $bb_f_ajax_page = 'single';

}

?> 
<script>
    jQuery(document).ready(function($) {
        function fetchFavoriteData() {
            var bb_a_page = "<?php echo $bb_f_ajax_page; ?>";
            var page_name = "<?php echo $parts_page_name[0]; ?>";
            var page_id = "<?php echo $page_id_via_slug; ?>";
            var bb_image_link = "<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/red-heart-outline.svg";
            $.ajax({
                type: 'POST',
                url: bb_plugin_data.ajaxurl,
                data: {
                    action: 'bb_fetch_favorite_data',
                    value: bb_a_page,
                    page_name: page_name,
                    page_id: page_id,
                },
                beforeSend: function() {
                    
                    $('#bb_favorite_caseIds_count').html('<img id="bb_f_gif_sidebar" src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/running-heart.gif" alt="Loading...">');
                    $('#bb-content-boxes-ajax').html('<img id="bb_f_gif_content" src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/running-heart.gif" alt="Loading...">');
                },
                success: function(response) {
                    if(response.success) {
                        $('#bb_favorite_caseIds_count').text('(' + response.data.favorite_case_count + ')');
                        $('#bb-content-boxes-ajax').html(response.data.html);
                        var favoriteCaseIds = Object.values(response.data.favorite_case_ids);
                        $('img[data-case-id]').each(function() {
                            var caseId = $(this).data('case-id');
                            
                            if (favoriteCaseIds.includes(caseId)) {
                                $(this).attr('src', bb_image_link);
                            }
                        });
                    } else { 
                        $('#bb_favorite_caseIds_count').text('(' + 0 + ')');
                        console.log('Error: ' + response.data.message);
                    }
                },
                complete: function() {
                    
                    $('#bb_f_gif_sidebar').remove();  
                    $('#bb_f_gif_content').remove();  
                },
                error: function(error) {
                    console.log('AJAX error234:', error);
                    
                    $('#bb_f_gif_sidebar').remove();  
                    $('#bb_f_gif_content').remove(); 
                }
            });
        }
     //   fetchFavoriteData();  
    });

</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        var mobileSearchBar = document.getElementById('mobile-search-bar');
        var mobileSearchSuggestions = document.getElementById('mobile-search-suggestions');
        var searchBar = document.getElementById('search-bar');
        var searchSuggestions = document.getElementById('search-suggestions');
        if (mobileSearchBar && mobileSearchSuggestions) {
            mobileSearchBar.addEventListener('input', function() {
                var searchText = this.value.toLowerCase().trim();
                mobileSearchSuggestions.innerHTML = '';

                var links = document.querySelectorAll('.bb-nav-accordion a');
                links.forEach(function(link) {
                    var procedureTitle = link.textContent.toLowerCase();
                    var href = link.getAttribute('href');
                    if (procedureTitle.includes(searchText)) {
                        var listItem = document.createElement('li');
                        listItem.innerHTML = '<a href="' + href + '">' + link.textContent + '</a>';
                        mobileSearchSuggestions.appendChild(listItem);
                    }
                });

                var itemsToRemove = mobileSearchSuggestions.querySelectorAll('li a[href="#"]');
                itemsToRemove.forEach(function(item) {
                    item.parentElement.remove();
                });
                mobileSearchSuggestions.style.display = mobileSearchSuggestions.children.length > 0 ? 'block' : 'none';
            });

            function toggleSuggestionsVisibility() {
                mobileSearchSuggestions.style.display = mobileSearchBar.value.trim() === '' ? 'none' : 'block';
            }

            mobileSearchBar.addEventListener('input', toggleSuggestionsVisibility);
            mobileSearchBar.addEventListener('keyup', toggleSuggestionsVisibility);
            mobileSearchBar.addEventListener('keydown', toggleSuggestionsVisibility);
        }

        if (searchBar && searchSuggestions) {
            searchBar.addEventListener('input', function() {
                var searchText = this.value.toLowerCase().trim();
                searchSuggestions.innerHTML = '';

                var links = document.querySelectorAll('.bb-nav-accordion a');
                links.forEach(function(link) {
                    var procedureTitle = link.textContent.toLowerCase();
                    var href = link.getAttribute('href');
                    if (procedureTitle.includes(searchText)) {
                        var listItem = document.createElement('li');
                        listItem.innerHTML = '<a href="' + href + '">' + link.textContent + '</a>';
                        searchSuggestions.appendChild(listItem);
                    }
                });

                var itemsToRemove = searchSuggestions.querySelectorAll('li a[href="#"]');
                itemsToRemove.forEach(function(item) {
                    item.parentElement.remove();
                });

                searchSuggestions.style.display = searchSuggestions.children.length > 0 ? 'block' : 'none';
            });

            function SuggestionsVisibility() {
                searchSuggestions.style.display = searchBar.value.trim() === '' ? 'none' : 'block';
            }
            searchBar.addEventListener('input', SuggestionsVisibility);
            searchBar.addEventListener('keyup', SuggestionsVisibility);
            searchBar.addEventListener('keydown', SuggestionsVisibility);
        }
    });
</script>
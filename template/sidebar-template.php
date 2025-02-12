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
        fetchFavoriteData();  
    });

</script>
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
                foreach ($properties_data as $token_bb) {
                    foreach ($token_bb as $website_id_bb) {
                        foreach ($website_id_bb as $websiteproperty_id => $property_data) {
                            if(($parts_page_name[0] == $websiteproperty_id) || ($combine_gallery_page_slug == $parts_page_name[0])) {
                                $categories = $property_data['categories'];
                                $api_data = $property_data['api_data'];
                                if (!empty($categories) && is_array($categories)) {
                                    foreach ($categories as $category_key => $category) {
                                        $case_counts = [];
                                        foreach ($category['procedures'] as $procedure_key => $procedure) {
                                            $p_case_count = 0; 
                                            foreach ($api_data as $item) {
                                                if (in_array($procedure['id'], $item['procedureIds'])) {
                                                    if (!empty($item['photoSets'])) {
                                                        $p_case_count++;
                                                    }
                                                }
                                            }
                                            $case_counts[$procedure_key] = $p_case_count;
                                        }
                                        foreach ($category['procedures'] as $procedure_key => $procedure) {
                                            $categories[$category_key]['procedures'][$procedure_key]['case_count'] = $case_counts[$procedure_key];
                                        }
                                    }
                                }
                                if (!empty($categories) && is_array($categories)) {
                                    foreach ($categories as $category) {
                                        $procedures_cat_data = [];
                                        foreach ($api_data as $item) {
                                            foreach ($category['procedures'] as $procedure) {
                                                if (in_array($procedure['id'], $item['procedureIds'])) {
                                                    if (!empty($item['photoSets'])) { 
                                                        $procedures_cat_data[] = $procedure['id']; 
                                                    }
                                                }
                                            }
                                        }
                                        $categorized_procedures[$websiteproperty_id][$category['id']] = [
                                            'category_name' => $category['name'],
                                            'procedures_count' => count($procedures_cat_data),
                                            'procedures_data' => $category['procedures'],
                                        ];
                                    }
                                    $all_properties[$websiteproperty_id] = $categorized_procedures[$websiteproperty_id];
                                }
                            }
                        }
                    }
                }
            }

            function removeAccents($string) {
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
        
            function render_category_group($all_properties, $plugin_dir_path, $parts_page_name) {
                if (!empty($all_properties) && is_array($all_properties)) {
                    $merged_categories = [];
                    foreach ($all_properties as $property_id => $categories) {
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

                    foreach ($merged_categories as $category_name => $category_data) {
                        $totalCaseCount = 0;
                        foreach ($category_data['procedures'] as $procedure_name => $procedure_data) {
                            $totalCaseCount += $procedure_data['case_count'];
                        }
                        if ($totalCaseCount != 0) {
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
                                            
                                            $lower_procedure_name = urldecode($lower_procedure_name);
                                            $lower_procedure_name = removeAccents($lower_procedure_name);
                                            
                                            $converted_procedure_name = urldecode($converted_procedure_name);
                                            $converted_procedure_name = removeAccents($converted_procedure_name);
                                            update_option($converted_procedure_name, $category_name);
                                            update_option($lower_procedure_name, $category_name);
                                            update_option($lower_procedure_name . '_id', $procedure_data['id']);
                                            
                                            update_option($procedure_data['id'] . '_title', $procedure_data['name']);
                                            ?>
                                            <li>
                                                <a id="<?= esc_attr($procedure['id']); ?>" href="<?= "/" . $parts_page_name[0] . "/" . strtolower($converted_procedure_name) . "/"; ?>">
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
            }

            render_category_group($all_properties, BB_PLUGIN_DIR_PATH, $parts_page_name);
            ?>
            <ul>
                <li>
                    <a class="bb-sidebar_favorites" href="/<?=$parts_page_name[0]?>/favorites/">
                        <h3> My Favorites <span id="bb_favorite_caseIds_count">(<?php echo get_option('bb_favorite_caseIds_count'); ?>)</span></h3>
                    </a> 
                </li> 
            </ul>  
        </div>
    </div> 
    
    <a href="/<?=$parts_page_name[0]?>/consultation/" class="bb-sidebar-btn">REQUEST A CONSULTATION</a>
    <p class="request-promo">Ready for the next step?<br>Contact us to request your consultation.</p>
    <p>Befor and after gallery powered by <span style="color:red">BRAG book™</span></p>
    
</div>

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
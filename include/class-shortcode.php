<?php
namespace mvpbrag;

class Shortcode
{

    public static function register()
    {
        // Hook into 'init' to add custom rewrite rules
        add_action('init', [__CLASS__, 'custom_rewrite_flush']);
        add_shortcode('bragbook_carousel_shortcode', [__CLASS__, 'mvp_carousel_shortcode']);
        add_shortcode('bb_bragbook_category', [__CLASS__, 'bb_mvp_category_shortcode']);
        add_shortcode('bb_bragbook_procedure', [__CLASS__, 'bb_mvp_category_shortcode']);
        add_shortcode('bb_bragbook_set', [__CLASS__, 'mvp_bragbook_set_shortcode']);
        add_shortcode('bb_bragbook_home_menu', [__CLASS__, 'mvp_bragbook_home_menu_shortcode']);

    }

    public static function bb_limitWords($text, $wordLimit)
    {
        if (!is_string($text)) {
            $text = '';
        }
        $words = explode(' ', $text);
        $words = array_slice($words, 0, $wordLimit);
        $limitedText = implode(' ', $words);

        return $limitedText;
    }

    // Define the custom rewrite rule function
    public static function custom_rewrite_rules()
    {

        $page_id = '';
        $stored_pages = get_option('bb_gallery_stored_pages', []);
        foreach ($stored_pages as $bb_page_key => $bb_page_value) {
            $page = get_page_by_path($bb_page_value, OBJECT, 'page');
            if ($page) {
                $page_id = $page->ID;
            }

            $bragbook_post = get_post($page_id);
            if (isset($bragbook_post->post_name)) {
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

        $combine_gallery_page_id = get_option('combine_gallery_page_id');
        $combine_gallery_page = get_post($combine_gallery_page_id);

        $combine_gallery_page_slug = "";
        if ($combine_gallery_page !== "" && is_a($combine_gallery_page, 'WP_Post')) {
            $combine_gallery_page_slug = $combine_gallery_page->post_name;

            if (isset($combine_gallery_page->post_name)) {
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

    public static function custom_rewrite_flush()
    {
        self::custom_rewrite_rules();
        flush_rewrite_rules();
    }

    public static function searchData($data, $searchTerm)
    {

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

    public static function get_optimize_bb_img($original_url, $api_token)
    {
        $bb_new_image_procedure_data = add_query_arg([
            'url' => urlencode($original_url),
            'quality' => 'small',
            'format' => 'webp',
            'x-api-token' => $api_token,
            'x-plugin-version' => BB_PLUGIN_VERSION,
        ], rest_url('bb/v1/optimize-image-proxy'));
        $response = wp_remote_get($bb_new_image_procedure_data);
        $image_data = wp_remote_retrieve_body($response);
        $content_type = wp_remote_retrieve_header($response, 'content-type') ?: 'image/webp';

        // Output image directly using base64
        $base64 = base64_encode($image_data);
        return 'data:' . esc_attr($content_type) . ';base64,' . esc_attr($base64);
    }
    public static function mvp_carousel_shortcode($atts)
    {
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
        $cat_title_formatted = ucwords(str_replace('-', ' ', $cat_name));

        $cat_website_property_id = $atts['website_property_id'];
        $api_tokens = get_option('bragbook_api_token', []);
        $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
        $gallery_slugs = get_option('bb_gallery_page_slug', []);
        $bb_token_page = array_search($cat_website_property_id, $websiteproperty_ids, true);
        $api_token = $api_tokens[$bb_token_page];
        $bb_slug_link = $gallery_slugs[$bb_token_page];
        $sidebar = new Bb_Api();
        $data = $sidebar->get_api_sidebar_bb($api_token);
        $sidebar_set = json_decode($data, true) ?? [];

        $result = isset($sidebar_set) ? self::searchData($sidebar_set, $cat_name) : '';
        if (empty($result)) {
            return false;
        }
        $id = $result['ids'][0];
        $url_car = BB_BASE_URL . "/api/plugin/carousel?websitePropertyId={$cat_website_property_id}&start={$cat_start}&limit={$cat_limit}&apiToken={$api_token}&procedureId={$id}";

        $response = wp_remote_get($url_car);
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
            $data_car = '';
        } else {
            $data_car = wp_remote_retrieve_body($response);
        }

        ob_start();
        ?>
                <div class="bb-main">
                    <div class="bb-slider">
                        <?php
                        $limit_count = 0;
                        $bb_scase_ids_list = [];
                        $spro_title_bb = $result['slugName'];
                        $carousel_data_bb = json_decode($data_car);
                        foreach ($carousel_data_bb->data as $procedure_data) {
                            if (!empty($procedure_data->photoSets)) {
                                ?>
                                        <div class="bb-slick-slide">
                                            <div class="bb-slide">
                                                <?php
                                                $bb_new_image_procedure_data_optimize = isset($procedure_data->photoSets[0]->highResPostProcessedImageLocation) && !is_null($procedure_data->photoSets[0]->highResPostProcessedImageLocation)
                                                    ? $procedure_data->photoSets[0]->highResPostProcessedImageLocation
                                                    : (isset($procedure_data->photoSets[0]->postProcessedImageLocation) && !is_null($procedure_data->photoSets[0]->postProcessedImageLocation)
                                                        ? $procedure_data->photoSets[0]->postProcessedImageLocation
                                                        : $procedure_data->photoSets[0]->originalBeforeLocation);
                                                $bb_new_image_procedure_data = self::get_optimize_bb_img($bb_new_image_procedure_data_optimize, $api_token);

                                                $caseSeoSuffixUrl = "";
                                                if ($procedure_data->caseDetails[0] && $procedure_data->caseDetails[0]->seoSuffixUrl) {
                                                    $caseSeoSuffixUrl = $procedure_data->caseDetails[0]->seoSuffixUrl;
                                                } else {
                                                    $caseSeoSuffixUrl = 'bb-case-' . $procedure_data->id;
                                                }
                                                ?>
                                                <a href="<?php echo "/" . $bb_slug_link . "/" . $spro_title_bb . "/" . $caseSeoSuffixUrl . "/"; ?>">
                                                    <img class="bb-slide-thumnail" src=<?php echo $bb_new_image_procedure_data; ?>
                                                        alt="<?php echo isset($procedure_data->photoSets[0]->seoAltText) ? $procedure_data->photoSets[0]->seoAltText : ''; ?>">
                                                </a>
                                                <?php if ($cat_title == 1 || $cat_details == 1) { ?>
                                                                <div class="bb-content-box-inner">
                                                                    <div class="bb-content-box-inner-left">
                                                                        <?php if ($cat_title == 1) { ?>
                                                                                        <p class="bb-carousel-tite">
                                                                                            <?php echo isset($procedure_data->caseDetails[0]->seoHeadline) ? $procedure_data->caseDetails[0]->seoHeadline : $cat_title_formatted . " Patient"; ?>
                                                                                        </p>
                                                                        <?php }
                                                                        if ($cat_details == 1) { ?>
                                                                                        <?php echo str_replace('<p>', '<p class="bb-carousel-description">', self::bb_limitWords($procedure_data->details, 50)); ?>
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

    public static function mvp_bragbook_set_shortcode($atts)
    {
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
        $bb_token_page = array_search($cat_website_property_id, $websiteproperty_ids, true);
        $api_token = $api_tokens[$bb_token_page];
        $sidebar = new Bb_Api();
        $data = $sidebar->get_api_sidebar_bb($api_token);
        $sidebar_set = json_decode($data, true) ?? [];
        $sidebar = new Bb_Api();
        $data = $sidebar->get_api_sidebar_bb($api_token);
        $sidebar_set = json_decode($data, true) ?? [];
        $result = isset($sidebar_set) ? self::searchData($sidebar_set, $cat_name) : '';
        if (empty($result)) {
            return false;
        }

        $id = $result['ids'][0];
        $url_case = BB_BASE_URL . "/api/plugin/cases/?websitePropertyId={$cat_website_property_id}&apiToken={$api_token}&caseId={$caseid}&procedureId={$id}";

        $response = wp_remote_get($url_case);
       
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
            $result_set = [];
        } else {
            $data_case = wp_remote_retrieve_body($response);
            $result_set = json_decode($data_case, true);
        }


        ob_start();
        ?>

        <div class="bb-main">
            <div class="bb-content-boxes">
                <?php

                if (!empty($result_set) && is_array($result_set)) {

                    foreach ($result_set['data'] as $entry) {
                        if (isset($entry['photoSets']) && is_array($entry['photoSets'])) {
                            foreach ($entry['photoSets'] as $photoSet) {

                                if ($caseid == $photoSet['caseId']) {
                                    ?>
                                        <div class="bb-content-box">
                                            <?php
                                            $bb_new_image_photoSet_optimize = isset($photoSet['highResPostProcessedImageLocation']) && !is_null($photoSet['highResPostProcessedImageLocation'])
                                                ? $photoSet['highResPostProcessedImageLocation']
                                                : (isset($photoSet['postProcessedImageLocation']) && !is_null($photoSet['postProcessedImageLocation'])
                                                    ? $photoSet['postProcessedImageLocation']
                                                    : $photoSet['originalBeforeLocation']);
                                            $bb_new_image_photoSet = self::get_optimize_bb_img($bb_new_image_photoSet_optimize, $api_token);
                                        
                                            ?>
                                            <img src="<?php echo $bb_new_image_photoSet ?>"
                                                alt="<?php echo isset($photoSet['seoAltText']) ? $photoSet['seoAltText'] : ''; ?>">
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

    public static function mvp_bragbook_home_menu_shortcode($atts)
    {
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

            if (($websiteproperty_id == $cat_website_property_id)) {
                if (empty($api_token) || empty($websiteproperty_id)) {
                    continue;
                }

                $token = $api_token;
                $bb_slug_link = $page_slug_bb;

                $sidebar = new Bb_Api();
                $data = $sidebar->get_api_sidebar_bb($api_token);
                $sidebar_set = json_decode($data, true) ?? [];

            }
        }
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
                            <img src="<?php echo BB_PLUGIN_DIR_PATH ?>assets/images/search-svgrepo-com.svg"
                                class="bb-search-icon" alt="search">
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
                                            foreach ($procedure_data['procedures'] as $procedure) {
                                                ?>
                                                            <li>
                                                                <a id="<?= esc_attr($procedure['ids']); ?>"
                                                                    href="<?= "/" . $bb_slug_link . "/" . $procedure['slugName'] . "/"; ?>"
                                                                    data-count="1" data-api-token="<?= esc_attr($token); ?>"
                                                                    data-website-property-id="<?= esc_attr($cat_website_property_id); ?>">
                                                                    <?= esc_html($procedure['name']); ?>
                                                                    <span>(<?php echo $procedure['totalCase'] . "/" ?>)</span>
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
                                    <a class="bb-sidebar_favorites" href="<?= "/" . $bb_slug_link . "/" ?>favorites/">
                                        <h3> My Favorites <span
                                                id="bb_favorite_caseIds_count">(<?php echo get_option('bb_favorite_caseIds_count'); ?>)</span>
                                        </h3>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <a href="/<?= $bb_slug_link ?>/consultation/" class="bb-sidebar-btn">REQUEST A CONSULTATION</a>
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
                                <img src="<?php echo BB_PLUGIN_DIR_PATH; ?>assets/images/search-svgrepo-com.svg"
                                    class="bb-search-icon" alt="search">
                                <ul id="mobile-search-suggestions" class="search-suggestions"></ul>
                            </form>

                        </div>
                    </div>
                    <?php

                    $brag_page_data = get_option('bragbook_landing_page_text');
                    $explode_string = explode('[', $brag_page_data);
                    if (isset($explode_string[1])) {
                        $explode_string[1] = '[' . $explode_string[1];
                    }

                    echo isset($explode_string['0']) ? $explode_string['0'] : '';
                    echo isset($explode_string['1']) ? do_shortcode($explode_string['1']) : '';
                    ?>

                    <a href="<?= $bb_slug_link; ?>consultation/" class="bb-sidebar-btn">REQUEST A CONSULTATION</a>
                    <div class="bb-bottom-bar">
                        <img src="<?php echo BB_PLUGIN_DIR_PATH ?>assets/images/myfavs-logo.png" alt="logo">
                        <p>
                            <span>Use the MyFavorites tool</span> to help communicate your specific goals. If a result speaks to
                            you, tap the heart.
                        </p>
                    </div>
                </div>
            </main>
        </div>
        <script>
            jQuery(document).ready(function ($) {
                if (document.getElementById('mobile-search-bar') && document.getElementById('mobile-search-suggestions')) {
                    $('#mobile-search-bar').on('input', function () {
                        var searchText = $(this).val().toLowerCase().trim();
                        var suggestionsList = $('#mobile-search-suggestions');

                        suggestionsList.empty();
                        $('.bb-nav-accordion').find('a').each(function () {
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

                $('#search-bar').on('input', function () {
                    var searchText = $(this).val().toLowerCase().trim();
                    var searchsuggestions = $('#search-suggestions');

                    searchsuggestions.empty();
                    $('.bb-nav-accordion').find('a').each(function () {
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

    public static function bb_mvp_category_shortcode($atts)
    {
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
        $bb_token_page = array_search($cat_website_property_id, $websiteproperty_ids, true);
        $api_token = $api_tokens[$bb_token_page];
        $bb_slug_link = $gallery_slugs[$bb_token_page];
        $sidebar = new Bb_Api();
        $data = $sidebar->get_api_sidebar_bb($api_token);
        $sidebar_set = json_decode($data, true) ?? [];

        $sidebar = new Bb_Api();
        $data = $sidebar->get_api_sidebar_bb($api_token);
        $sidebar_set = json_decode($data, true) ?? [];

        $result = isset($sidebar_set) ? self::searchData($sidebar_set, $cat_name) : '';
        if (empty($result)) {
            return false;
        }

        $id = $result['ids'][0];
        $procedure_name_bb = $result['slugName'];

        $url_pro = BB_BASE_URL . "/api/plugin/carousel?websitePropertyId={$cat_website_property_id}&start={$cat_start}&limit={$cat_limit}&apiToken={$api_token}&procedureId={$id}";

        $response = wp_remote_get($url_pro);
        if (is_wp_error($response)) {
            echo "Something went wrong: " . $response->get_error_message();
            $result_pro = [];
        } else {
            $data_pro = wp_remote_retrieve_body($response);
            $result_pro = json_decode($data_pro, true);
        }
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
                                                $imgSrcOptimize = $photoSet['highResPostProcessedImageLocation'] ?? $photoSet['postProcessedImageLocation'] ?? $photoSet['originalBeforeLocation'];
                                                $imgSrc = self::get_optimize_bb_img($imgSrcOptimize, $api_token);

                                                $imgAlt = $photoSet['seoAltText'] ?? 'Procedure Image';
                                                $caseSeoSuffixUrl = "";
                                                if ($caseItem["caseDetails"][0] && $caseItem["caseDetails"][0]["seoSuffixUrl"]) {
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

                                                if ($cat_title == 1 || $cat_details == 1) {

                                                    $newContent .= "
                                <div class='bb-content-box-inner'>
                                    <div class='bb-content-box-inner-left'>";
                                                    if ($cat_title == 1)
                                                        $newContent .= "<p class='bb-carousel-tite'>$thirdPart : Patient $patientCount</p>";
                                                    if ($cat_details == 1)
                                                        $newContent .= "<p>$caseDetails</p>";
                                                    $newContent .= "</div>
                                    <div class='bb-content-box-inner-right'>
                                        <!-- You can add content here if needed -->
                                    </div>
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
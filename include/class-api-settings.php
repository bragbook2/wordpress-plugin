<?php
namespace mvpbrag;

class Ajax_Handler
{
    public function __construct()
    {
        add_action('admin_menu', [$this, 'form_entry_menu_page']);
        add_action('wp_ajax_bragbook_my_favorite', [$this, 'bragbook_my_favorite_handler']);
        add_action('wp_ajax_nopriv_bragbook_my_favorite', [$this, 'bragbook_my_favorite_handler']);
        add_action('wp_ajax_bb_save_bragbook_settings', [$this, 'bb_save_bragbook_settings']);
        add_action('wp_ajax_nopriv_bb_save_bragbook_settings', [$this, 'bb_save_bragbook_settings']);
        add_action('wp_ajax_bb_setting_remove_row', [$this, 'bb_setting_remove_row']);
        add_action('wp_ajax_bb_update_api', [$this, 'bb_update_api']);
        add_action('wp_ajax_nopriv_bb_update_api', [$this, 'bb_update_api']);
        add_action('save_post', [$this, 'bb_update_slugs']);
        add_action('wp_trash_post', [$this, 'bb_delete_default_page']);
        add_action('wp_footer', [$this, 'bb_footer_gallery_modal']);
        add_action('delete_post', [$this, 'bb_permanent_delete_empty_trash']);
        add_action('wp_footer', [$this, 'bb_nudity_warning']);
        add_action('wp_ajax_bb_case_api', [$this, 'handle_bb_case_api']);
        add_action('wp_ajax_nopriv_bb_case_api', [$this, 'handle_bb_case_api']);
    }

    function handle_bb_case_api()
    {
        if (isset($_POST['count'], $_POST['pageSlug'], $_POST['procedureId'], $_POST['apiToken'], $_POST['websitePropertyId'])) {
            get_option('combine_gallery_page_id');
            $count = sanitize_text_field($_POST['count']);
            $apiToken = sanitize_text_field($_POST['apiToken']);
            $websitePropertyId = sanitize_text_field($_POST['websitePropertyId']);
            $procedureId = sanitize_text_field($_POST['procedureId']);
            $caseId = sanitize_text_field($_POST['caseId']);
            $seoSuffixUrl = sanitize_text_field($_POST['seoSuffixUrl']);
            $page_slug = sanitize_text_field($_POST['pageSlug']);
            $combine_gallery_page_slug = get_option('combine_gallery_slug');
            $design_version = get_option('bb_design_plugin_selector');

            $filter_get = '';
            if ($page_slug == $combine_gallery_page_slug) {
                $filter_data = new Bb_Api();
                $filter_get = $filter_data->bb_get_filter_data($apiToken, $procedureId, $websitePropertyId);
            } else {
                $transient_key = 'filters_' . md5($apiToken . $procedureId . $websitePropertyId);
                if (get_transient($transient_key) == false) {
                    $filter_data = new Bb_Api();
                    $filter_get = $filter_data->bb_get_filter_data($apiToken, $procedureId, $websitePropertyId);
                    set_transient($transient_key, $filter_get, 1800);
                } else {
                    $filter_get = get_transient($transient_key);
                }
            }

            if ($caseId !== "" || $seoSuffixUrl !== "") {
                $caseId = $caseId ? $caseId : '123';
                if ($page_slug == $combine_gallery_page_slug) {
                    $filter_data = new Bb_Api();
                    $data = $filter_data->bb_get_case_data($caseId, $seoSuffixUrl, $apiToken, $procedureId, $websitePropertyId);
                } else {
                    // $transient_key = 'cases_' . md5($apiToken . $procedureId . $websitePropertyId . $caseId . $seoSuffixUrl);
                    // if (get_transient($transient_key) !== false) {
                    //     $data = get_transient($transient_key);
                    // } else {
                    $filter_data = new Bb_Api();
                    $data = $filter_data->bb_get_case_data($caseId, $seoSuffixUrl, $apiToken, $procedureId, $websitePropertyId);
                    // }
                }
            } else {
                $dynamicFilterCombineAPIBody = [];
                $dynamicFilterCombineAPIBody['apiTokens'] = explode(", ", $apiToken);
                $dynamicFilterCombineAPIBody['count'] = (int) $count;
                $dynamicFilterCombineAPIBody['procedureIds'] = array_map('intval', explode(", ", $procedureId));
                $dynamicFilterCombineAPIBody['websitePropertyIds'] = array_map('intval', explode(", ", $websitePropertyId));
                $dynamicFilterCombineAPIBody['memberId'] = null;
                if (isset($_POST['gender']) && !empty($_POST['gender'])) {
                    $dynamicFilterCombineAPIBody['gender'] = preg_replace('/\\\"/', '', $_POST['gender']);
                }

                if (isset($_POST['height']) && !empty($_POST['height'])) {
                    $dynamicFilterCombineAPIBody['height'] = intval($_POST['height']);
                }

                if (isset($_POST['weight']) && !empty($_POST['weight'])) {
                    $dynamicFilterCombineAPIBody['weight'] = intval($_POST['weight']);
                }

                if (isset($_POST['ethnicity']) && !empty($_POST['ethnicity'])) {
                    $dynamicFilterCombineAPIBody['ethnicity'] = preg_replace('/\\\"/', '', $_POST['ethnicity']);
                }

                if (isset($_POST['age']) && !empty($_POST['age'])) {
                    $dynamicFilterCombineAPIBody['age'] = intval($_POST['age']);
                }

                if (isset($_POST['dynamicFilterCombine']) && !empty($_POST['dynamicFilterCombine'])) {
                    $dynamicFilterCombine = stripslashes($_POST['dynamicFilterCombine']);
                    $decodedFilters = json_decode($dynamicFilterCombine, true);
                    if ($decodedFilters === null) {
                        echo "Invalid filter JSON format: " . $_POST['dynamicFilterCombine'];
                        exit;
                    }
                    $dynamicFilterCombineAPIBody['filters'] = $decodedFilters;
                }

                $filter_data = new Bb_Api();
                $data = $filter_data->bb_get_pagination_data($dynamicFilterCombineAPIBody);
                $data_in = $dynamicFilterCombineAPIBody;
            }

            $case_fav = [];
            $sidebar_list = '';
            $cookieValue = $_COOKIE['wordpress_favorite_email'];
            $decodedValue = urldecode($cookieValue);
            $favorite_email_id = htmlspecialchars($decodedValue);

            update_option('bragbook_favorite_email', $favorite_email_id);
            $api_tokens = get_option('bragbook_api_token', []);
            $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
            $gallery_slugs = get_option('bb_gallery_page_slug', []);
            $seo_pages_title = get_option('bb_seo_page_title', []);
            $seo_pages_description = get_option('bb_seo_page_description', []);
            $tc = 0;
            $fav_token = [];
            $web_id = [];
            foreach ($api_tokens as $index => $apiToken) {
                $fav_token[] = $apiToken;
                $tc++;
                $websitePropertyId = $websiteproperty_ids[$index] ?? '';
                $web_id[] = (int) $websitePropertyId;
                $page_slug_bb = $gallery_slugs[$index] ?? '';

                if ($page_slug == $page_slug_bb) {
                    $seo_page_title = $seo_pages_title[$index] ?? '';
                    $seo_page_description = $seo_pages_description[$index] ?? '';
                    $pageSlugBB = $page_slug_bb;
                    if (!is_array($apiToken)) {
                        $apiToken = [$apiToken];
                    }

                    if (!is_array($websitePropertyId)) {
                        $websitePropertyId = [(int) $websitePropertyId];
                    }

                    $favorite_list = new Bb_Api();
                    $favorite_data_brag_json = $favorite_list->bb_get_favorite_list_data($apiToken, $websitePropertyId, $favorite_email_id);
                    $favorite_data_brag = json_decode($favorite_data_brag_json);

                    foreach ($favorite_data_brag->favorites as $favorite) {
                        foreach ($favorite->cases as $caseItem) {
                            $case_fav[] = $caseItem->id;
                        }
                    }

                    $cacheKey = "$procedureSlug-single";
                    $sidebar_list = get_transient($cacheKey);
                    if (!$sidebar_list) {
                        $sidebar = new Bb_Api();
                        $sidebar_list = $sidebar->get_api_sidebar_bb($apiToken);
                        set_transient($cacheKey, $sidebar_list, HOUR_IN_SECONDS);
                    }

                    if ($_POST['favorites'] == 'favorites') {
                        $data = $favorite_data_brag_json;
                    }
                } elseif ($page_slug == $combine_gallery_page_slug && $tc == count($api_tokens)) {
                    $favorite_list = new Bb_Api();
                    $favorite_data_brag_json = $favorite_list->bb_get_favorite_list_data($fav_token, $web_id, $favorite_email_id);

                    $sidebar = new Bb_Api();
                    $sidebar_list = $sidebar->get_api_sidebar_bb($fav_token);
                    if ($_POST['favorites'] == 'favorites') {
                        $data = $favorite_data_brag_json;
                    }

                    $favorite_data_brag = json_decode($favorite_data_brag_json);
                    foreach ($favorite_data_brag->favorites as $favorite) {
                        foreach ($favorite->cases as $caseItem) {
                            $case_fav[] = $caseItem->id;
                        }
                    }
                }
            }
            if (!empty($sidebar_list)) {
                $sidebar_list = json_encode($sidebar_list);
            } else {
                $sidebar_list = '';
            }
            if (!empty($case_fav)) {
                json_encode($case_fav);
            } else {
                $case_fav = '';
            }

            $info = $data_in;
            $response = [
                'status' => 'success',
                'message' => 'Data received successfully.',
                'data' => [
                    'url' => $url,
                    'case_set' => $data,
                    'filter_data' => $filter_get,
                    'bragbook_favorite' => $case_fav,
                    'sidebar_api' => $sidebar_list,
                    'info' => $info,
                    'page_slug' => $page_slug,
                    'page_slug_bb' => $pageSlugBB,
                    'combine_page_slug' => $combine_gallery_page_slug,
                    'seo_page_title' => $seo_page_title,
                    'seo_page_description' => $seo_page_description,
                ]
            ];
            wp_send_json($response);
        } else {
            wp_send_json_error(['message' => 'Missing required parameters.']);
        }
    }

    public function bb_update_api()
    {
        $bb_set_transient_urls = get_option('bb_set_transient_url', []);

        foreach ($bb_set_transient_urls as $bb_set_transient_url => $bb_set_transient_url_data) {
            set_transient($bb_set_transient_url, $bb_set_transient_url_data, 2);
        }

        $filepath = trailingslashit(ABSPATH) . 'brag-book-sitemap.xml';

        if (file_exists($filepath)) {
            $bb_sitemap = new Sitemap();
            $bb_sitemap->create_bragbook_sitemap();
        }
        wp_send_json_success('API Updated Successfully.');
        die();
    }

    public function bb_permanent_delete_empty_trash($post_id)
    {
        if (get_post_type($post_id) === 'page') {
            $page_slug = get_post_field('post_name', $post_id);

            $combine_gallery_slug = get_option('combine_gallery_slug');
            if ($page_slug == $combine_gallery_slug) {
                update_option('combine_gallery_slug', "");
            }

            $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids');
            $bb_remove_id = array_search($post_id, $bb_page_list_gallery);
            $bb_id_list = get_option('bb_gallery_stored_pages');
            $bb_id_list_gallery = get_option('bb_gallery_page_slug');
            $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids');
            $bragbook_websiteproperty_id = get_option('bragbook_websiteproperty_id');
            $bragbook_api_token = get_option('bragbook_api_token');

            update_option('bb_remove_pages_from_setting', $bb_id_list_gallery);
            update_option('bb_remove_combine_gallery_from_setting', $combine_gallery_slug);

            unset($bb_id_list_gallery[$bb_remove_id]);
            unset($bb_id_list[$bb_remove_id]);
            unset($bb_page_list_gallery[$bb_remove_id]);
            unset($bragbook_websiteproperty_id[$bb_remove_id]);
            unset($bragbook_api_token[$bb_remove_id]);

            update_option('bb_gallery_page_slug', $bb_id_list_gallery);
            update_option('bb_gallery_stored_pages_ids', $bb_page_list_gallery);
            update_option('bragbook_websiteproperty_id', $bragbook_websiteproperty_id);
            update_option('bragbook_api_token', $bragbook_api_token);
            update_option('bb_gallery_stored_pages', $bb_id_list);

        }
    }

    public function bb_footer_gallery_modal()
    {
        ?>
        <div id="bbrag_modal" class="bbrag_modal">
            <span class="bbrag_close">&times;</span>
            <img class="bbrag_modal_content" id="bbrag_modalImage">
            <a class="bbrag_prev">&#10094;</a>
            <a class="bbrag_next">&#10095;</a>
        </div>
        <?php
    }

    public function bb_setting_remove_row()
    {
        $bb_remove_id = $_POST['bb_remove_id'];
        $bb_id_list = get_option('bb_gallery_stored_pages');
        $bb_id_list_gallery = get_option('bb_gallery_page_slug');
        $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids');
        $bragbook_websiteproperty_id = get_option('bragbook_websiteproperty_id');
        $bragbook_api_token = get_option('bragbook_api_token');

        self::bb_delete_page_by_slug($bb_id_list[$bb_remove_id]);
        unset($bb_id_list_gallery[$bb_remove_id]);
        unset($bb_id_list[$bb_remove_id]);
        unset($bb_page_list_gallery[$bb_remove_id]);
        unset($bragbook_websiteproperty_id[$bb_remove_id]);
        unset($bragbook_api_token[$bb_remove_id]);

        update_option('bb_gallery_page_slug', $bb_id_list_gallery);
        update_option('bb_gallery_stored_pages_ids', $bb_page_list_gallery);
        update_option('bragbook_websiteproperty_id', $bragbook_websiteproperty_id);
        update_option('bragbook_api_token', $bragbook_api_token);

        $bb_result = update_option('bb_gallery_stored_pages', $bb_id_list);
        echo $bb_result;
        exit();
    }

    public static function bb_delete_page_by_slug($slug)
    {
        $page = get_page_by_path($slug, OBJECT, 'page');
        if ($page) {
            wp_delete_post($page->ID, true);
        }
    }

    public function bb_update_slugs($post_id)
    {
        if (get_post_type($post_id) !== 'page') {
            return;
        }

        $new_slug = get_post_field('post_name', $post_id);
        $stored_pages_associative = get_option('bb_gallery_stored_pages', []);
        $bb_pages_associative = get_option('bb_gallery_page_slug', []);

        $option_name = 'bb_gallery_stored_pages_ids';
        $current_option = get_option($option_name);

        if ($current_option && is_array($current_option)) {
            foreach ($current_option as $key => $bb_page_id) {
                if ($post_id === $bb_page_id) {
                    $stored_pages_associative[$key] = $new_slug;
                    $bb_pages_associative[$key] = $new_slug;
                    update_option('bb_gallery_stored_pages', $stored_pages_associative);
                    update_option('bb_gallery_page_slug', $bb_pages_associative);
                }
            }

            $new_title = ucwords(str_replace('-', ' ', $new_slug));
            $current_title = get_the_title($post_id);

            $current_slug_bb = "";
            $current_bb_slug = get_page_by_title($current_title, OBJECT, 'page');
            if ($current_bb_slug) {
                $current_slug_bb = $current_bb_slug->post_name;
            }

            if (empty($current_title) && empty($new_slug)) {
                wp_update_post([
                    'ID' => $post_id,
                    'post_title' => $new_title
                ]);

                $page_bb_combine = get_option('combine_gallery_page_id');

                if ($page_bb_combine == $post_id) {
                    if (get_post_status($post_id) === 'trash') {
                        update_option('combine_gallery_slug', "");
                    } else {
                        update_option('combine_gallery_slug', $new_slug);
                    }
                }
            }
        }
    }

    public function bb_save_bragbook_settings()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('You are not allowed to perform this action.');
            return;
        }

        $bb_token_based_page_keys = $_POST['bb_page_keys'];

        parse_str($_POST['form_data'], $form_data);
        update_option('bragbook_landing_page_text', wp_kses_post($form_data['bragbook_landing_page_text']));
        update_option('bb_seo_plugin_selector', sanitize_text_field($form_data['bb_seo_plugin_selector']));
        update_option('bb_design_plugin_selector', sanitize_text_field($form_data['bb_design_plugin_selector']));

        $current_user_id = get_current_user_id();
        if (isset($form_data['combine_gallery_slug']) && !empty($form_data['combine_gallery_slug'])) {
            $bb_old_combine_gallery = get_option('combine_gallery_slug');
            $slug_input = sanitize_text_field($form_data['combine_gallery_slug']);
            $bb_combine_seo_page_title = sanitize_text_field($form_data['bb_combine_seo_page_title']);
            $bb_combine_seo_page_description = sanitize_text_field($form_data['bb_combine_seo_page_description']);

            update_option('bb_combine_seo_page_title', $bb_combine_seo_page_title);
            update_option('bb_combine_seo_page_description', $bb_combine_seo_page_description);
            update_option('combine_gallery_slug', $slug_input);
            $bb_combine_slug = get_option('combine_gallery_slug');

            $bb_formatted_string = ucwords(str_replace('-', ' ', $bb_combine_slug));
            $existing_page = get_page_by_path($bb_combine_slug, OBJECT, 'page');

            if ($existing_page) {
                if ($existing_page->post_status == 'trash' || $existing_page->post_status == 'draft') {
                    $existing_page->post_status = 'publish';
                }
                wp_update_post([
                    'ID' => $existing_page->ID,
                    'post_name' => $bb_combine_slug,
                    'post_title' => $bb_formatted_string,
                    'post_status' => $existing_page->post_status
                ]);
            } else {
                $old_slug = $bb_old_combine_gallery;
                $old_page = get_page_by_path($old_slug, OBJECT, 'page');

                if ($old_page) {
                    wp_update_post([
                        'ID' => $old_page->ID,
                        'post_name' => $bb_combine_slug,
                        'post_title' => $bb_formatted_string,
                    ]);
                } else {
                    $new_page_id = wp_insert_post([
                        'post_title' => $bb_formatted_string,
                        'post_name' => $bb_combine_slug,
                        'post_status' => 'publish',
                        'post_type' => 'page',
                        'post_author' => $current_user_id,
                        'page_template' => 'bb-brag.php'
                    ]);

                    if (is_wp_error($new_page_id)) {
                        error_log('Error creating page: ' . $new_page_id->get_error_message());
                    } else {
                        update_post_meta($new_page_id, '_wp_page_template', 'template/bb-brag.php');
                        update_option('combine_gallery_page_id', $new_page_id);
                    }
                }
            }
        }

        if (isset($form_data['bragbook_api_token'])) {
            update_option('bragbook_api_token', array_map('sanitize_text_field', $form_data['bragbook_api_token']));
        }
        if (isset($form_data['bragbook_websiteproperty_id'])) {
            update_option('bragbook_websiteproperty_id', array_map('sanitize_text_field', $form_data['bragbook_websiteproperty_id']));
        }
        if (isset($form_data['bb_gallery_page_slug'])) {
            update_option('bb_gallery_page_slug', array_map('sanitize_text_field', $form_data['bb_gallery_page_slug']));
        }
        if (isset($form_data['bb_seo_page_title'])) {
            update_option('bb_seo_page_title', array_map('sanitize_text_field', $form_data['bb_seo_page_title']));
        }
        if (isset($form_data['bb_seo_page_description'])) {
            update_option('bb_seo_page_description', array_map('sanitize_text_field', $form_data['bb_seo_page_description']));
        }
        $bb_pages_slugs = get_option('bb_gallery_page_slug', []);
        self::bb_token_base_page_creation($bb_token_based_page_keys, $bb_pages_slugs, $current_user_id);

        $seoPluginOptions = get_option('bb_seo_plugin_selector');
        $api_tokens = get_option('bragbook_api_token', []);
        $domain = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
        $domain .= "://" . $_SERVER['HTTP_HOST'];

        $websitePropertyIds = get_option('bragbook_websiteproperty_id', []);
        $data_bb = [];
        foreach ($api_tokens as $pageKey => $token) {
            $data_bb[] = [
                'version' => BB_PLUGIN_VERSION,
                'domain' => $domain,
                'galleryPage' => $bb_pages_slugs[$pageKey] ?? '',
                'apiToken' => $token,
                'websitePropertyId' => isset($websitePropertyIds[$pageKey]) ? (int) $websitePropertyIds[$pageKey] : null,
            ];
        }

        $tracker_payload = json_encode(['data' => $data_bb]);

        $bb_tracker = new Bb_Api();
        $tracker_info = $bb_tracker->send_plugin_version_data($tracker_payload);

        $filepath = trailingslashit(ABSPATH) . 'brag-book-sitemap.xml';

        if (!empty($api_tokens) && !file_exists($filepath)) {
            $bb_sitemap = new Sitemap();
            $bb_sitemap->create_bragbook_sitemap();
        }

        wp_send_json_success('Settings saved successfully.');
        exit();
    }

    public static function get_page_id_by_slug($slug)
    {
        $page = get_page_by_path($slug);
        if ($page) {
            return $page->ID;
        }
        return null;
    }

    public static function bb_token_base_page_creation($bb_token_based_page_keys, $bb_pages_slugs, $current_user_id)
    {
        $stored_pages = get_option('bb_gallery_stored_pages', []);
        $bb_pages_ids = get_option('bb_gallery_stored_pages_ids', []);
        $updated_stored_pages = [];
        $stored_pages_ids = [];
        foreach ($bb_token_based_page_keys as $key => $slug) {
            if (!empty($stored_pages) && array_key_exists($slug['key'], $stored_pages)) {
                if ($stored_pages[$slug['key']] === $slug['value']) {
                    continue;
                }

                $bb_current_slug = $stored_pages[$slug['key']];
                $page_id = self::get_page_id_by_slug($bb_current_slug);
                wp_update_post([
                    'ID' => $page_id,
                    'post_name' => $slug['value'],
                ]);

                $updated_stored_pages[$slug['key']] = $slug['value'];
                $stored_pages_ids[$slug['key']] = $page_id;
            } else {
                $formatted_string = ucwords(str_replace('-', ' ', $slug['value']));
                $new_page_id = wp_insert_post([
                    'post_title' => $formatted_string,
                    'post_name' => $slug['value'],
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_author' => $current_user_id,
                    'page_template' => 'bb-brag.php'
                ]);

                if ($new_page_id) {
                    update_post_meta($new_page_id, '_wp_page_template', 'template/bb-brag.php');
                    $updated_stored_pages[$slug['key']] = $slug['value'];
                    $stored_pages_ids[$slug['key']] = $new_page_id;
                }
            }
        }

        $bb_merge_array = $updated_stored_pages + $stored_pages;
        $bb_merge_ids = $stored_pages_ids + $bb_pages_ids;
        update_option('bb_gallery_stored_pages', $bb_merge_array);
        update_option('bb_gallery_stored_pages_ids', $bb_merge_ids);
    }

    public function form_entry_menu_page()
    {
        add_menu_page(
            'BRAG book Settings',
            'BRAG book Settings',
            'manage_options',
            'bragbook-settings',
            array($this, 'display_api_settings'),
            'dashicons-admin-generic',
            100
        );

    }

    public function display_api_settings()
    {
        ?>
        <div class="wrap">
            <h1><b>BRAG book API Settings</b></h1>
            <div id="accordion">
                <h3>Api Credentials</h3>
                <div>
                    <form method="post" id="bragbook_setting_page">
                        <?php
                        $combine_gallery_slug = '';
                        $bb_combine_seo_page_title = '';
                        $bb_combine_seo_page_description = '';
                        if (!empty(get_option('combine_gallery_slug'))) {
                            $combine_gallery_slug = get_option('combine_gallery_slug');
                            $bb_combine_seo_page_title = get_option('bb_combine_seo_page_title') ? get_option('bb_combine_seo_page_title') : '';
                            $bb_combine_seo_page_description = get_option('bb_combine_seo_page_description') ? get_option('bb_combine_seo_page_description') : '';
                        }
                        ?>
                        <div class="dynamic-api-table">
                            <table id="dynamicTable">
                                <thead>
                                    <tr>
                                        <th>API Token</th>
                                        <th>Website Property ID</th>
                                        <th>Gallery Page</th>
                                        <th>Seo Page Title</th>
                                        <th>Seo Page Description</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stored_pages = get_option('bb_gallery_stored_pages', []);
                                    $gallery_slugs = get_option('bb_gallery_page_slug', []);
                                    $api_tokens = get_option('bragbook_api_token', []);
                                    $website_ids = get_option('bragbook_websiteproperty_id', []);
                                    $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids');
                                    $bb_seo_pages_title = get_option('bb_seo_page_title', []);
                                    $bb_seo_pages_description = get_option('bb_seo_page_description', []);

                                    $num_rows = max(count($api_tokens), count($website_ids));
                                    $used_slugs = [];
                                    $i = 1;

                                    foreach ($stored_pages as $key => $value):
                                        if (isset($api_tokens[$key])) {
                                            $api_token = isset($api_tokens[$key]) ? esc_attr($api_tokens[$key]) : '';
                                            $website_id = isset($website_ids[$key]) ? esc_attr($website_ids[$key]) : '';
                                            $bb_seo_page_title = isset($bb_seo_pages_title[$key]) ? esc_attr($bb_seo_pages_title[$key]) : '';
                                            $bb_seo_page_description = isset($bb_seo_pages_description[$key]) ? esc_attr($bb_seo_pages_description[$key]) : '';
                                            $gallery_slug = $value;

                                            if (in_array($gallery_slug, $used_slugs)) {
                                                continue;
                                            } else {
                                                $used_slugs[] = $gallery_slug;
                                            }

                                            $page_id = '';
                                            $bb_slug_page = get_page_by_path($gallery_slug);
                                            if ($bb_slug_page) {
                                                $page_id = $bb_slug_page->ID;
                                            }
                                            ?>
                                            <tr>
                                                <td>
                                                    <input type="text" data-key="<?php echo $key; ?>"
                                                        name="bragbook_api_token[<?php echo $key; ?>]" value="<?php echo $api_token; ?>"
                                                        required>
                                                </td>
                                                <td>
                                                    <input type="text" data-key="<?php echo $key; ?>"
                                                        name="bragbook_websiteproperty_id[<?php echo $key; ?>]"
                                                        value="<?php echo $website_id; ?>" required>
                                                </td>
                                                <td>
                                                    <input type="text" data-key="<?php echo $key; ?>"
                                                        name="bb_gallery_page_slug[<?php echo $key; ?>]" value="<?php echo $value; ?>"
                                                        required>
                                                    <input type="hidden" name="bb_hidden_page_slug_with_id[<?php echo $i; ?>]"
                                                        value="<?php echo $page_id . '_' . $gallery_slug; ?>">

                                                </td>
                                                <td>
                                                    <input type="text" data-key="<?php echo $key; ?>"
                                                        name="bb_seo_page_title[<?php echo $key; ?>]"
                                                        value="<?php echo $bb_seo_page_title; ?>" required>
                                                </td>
                                                <td>
                                                    <input type="text" data-key="<?php echo $key; ?>"
                                                        name="bb_seo_page_description[<?php echo $key; ?>]"
                                                        value="<?php echo $bb_seo_page_description; ?>" required>
                                                </td>
                                                <td>
                                                    <button type="button" class="removeRow">Remove Row</button>
                                                    <button type="button" class="addRow">Add Row</button>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        $i++;
                                    endforeach; ?>

                                    <?php if (empty($api_tokens)): ?>
                                        <tr>
                                            <td>
                                                <input type="text" data-key="page_1" name="bragbook_api_token[page_1]" value=""
                                                    required>
                                            </td>
                                            <td>
                                                <input type="text" data-key="page_1" name="bragbook_websiteproperty_id[page_1]"
                                                    value="" required>
                                            </td>
                                            <td>
                                                <input type="text" data-key="page_1" name="bb_gallery_page_slug[page_1]" value=""
                                                    required>
                                            </td>
                                            <td>
                                                <button type="button" class="removeRow">Remove Row</button>
                                                <button type="button" class="addRow">Add Row</button>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                            <script>
                                document.addEventListener("DOMContentLoaded", function () {
                                    const tableBody = document.querySelector("#dynamicTable tbody");

                                    function addRow() {
                                        let lastPageNumber = 0;
                                        const rows = tableBody.querySelectorAll('tr');

                                        rows.forEach(row => {
                                            const input = row.querySelector('input[data-key^="page_"]');
                                            if (input) {
                                                const currentPageNumber = parseInt(input.getAttribute('data-key').split('_')[1], 10);
                                                lastPageNumber = Math.max(lastPageNumber, currentPageNumber);
                                            }
                                        });

                                        const newRowNumber = lastPageNumber + 1;
                                        const newRow = document.createElement('tr');
                                        newRow.innerHTML = `
                                            <td>
                                                <input type="text" data-key="page_${newRowNumber}" name="bragbook_api_token[page_${newRowNumber}]" value="" required>
                                            </td>
                                            <td>
                                                <input type="text" data-key="page_${newRowNumber}" name="bragbook_websiteproperty_id[page_${newRowNumber}]" value="" required>
                                            </td>
                                            <td>
                                                <input type="text" data-key="page_${newRowNumber}" name="bb_gallery_page_slug[page_${newRowNumber}]" value="" required>
                                            </td>
                                            <td>
                                                <input type="text" data-key="page_${newRowNumber}" name="bb_seo_page_title[page_${newRowNumber}]" value="" required>
                                            </td>
                                            <td>
                                                <input type="text" data-key="page_${newRowNumber}" name="bb_seo_page_description[page_${newRowNumber}]" value="" required>
                                            </td>
                                            <td>
                                                <button type="button" class="removeRow">Remove Row</button>
                                                <button type="button" class="addRow">Add Row</button>
                                            </td>
                                        `;

                                        tableBody.appendChild(newRow);
                                        newRow.querySelector(".removeRow").addEventListener("click", function () {
                                            removeRow(newRow);
                                        });

                                        updateButtonVisibility();
                                    }

                                    function removeRow(row) {
                                        if (tableBody.rows.length > 1) {
                                            const parser = new DOMParser();
                                            const doc = parser.parseFromString(row.innerHTML, "text/html");
                                            const inputElement = doc.querySelector('[data-key^="page_"]');

                                            var bb_remove_id = '';
                                            if (inputElement) {
                                                var bb_remove_id = inputElement.dataset.key;

                                            } else {
                                                console.log("No element found with an id starting with 'page_'.");
                                            }

                                            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
                                            jQuery.ajax({
                                                type: 'POST',
                                                url: ajaxurl,
                                                data: {
                                                    action: 'bb_setting_remove_row',
                                                    bb_remove_id: bb_remove_id
                                                },
                                                success: function (response) {
                                                    var successMessage = response.data;
                                                },
                                                error: function (xhr, status, error) {
                                                }
                                            });
                                            tableBody.removeChild(row);

                                            updateRowNumbers();
                                            updateButtonVisibility();
                                        }
                                    }

                                    function updateRowNumbers() {
                                        const rows = tableBody.querySelectorAll("tr");
                                        rows.forEach((row) => {
                                            const dataKeyValue = row.getAttribute('data-key');
                                            const rowNumber = dataKeyValue ? dataKeyValue.split('_')[1] : '';

                                            if (rowNumber) {
                                                row.querySelector("input[name^='bragbook_api_token']").setAttribute('name', `bragbook_api_token[page_${rowNumber}]`);
                                                row.querySelector("input[name^='bragbook_websiteproperty_id']").setAttribute('name', `bragbook_websiteproperty_id[page_${rowNumber}]`);
                                                row.querySelector("input[name^='bb_gallery_page_slug']").setAttribute('name', `bb_gallery_page_slug[page_${rowNumber}]`);
                                            }
                                        });
                                    }

                                    function updateButtonVisibility() {
                                        const rows = tableBody.querySelectorAll("tr");
                                        rows.forEach((row, index) => {
                                            const addButton = row.querySelector(".addRow");
                                            const removeButton = row.querySelector(".removeRow");

                                            addButton.style.display = (index === rows.length - 1) ? "inline-block" : "none";
                                            removeButton.style.display = (rows.length > 1) ? "inline-block" : "none";
                                        });
                                    }

                                    tableBody.addEventListener("click", function (event) {
                                        if (event.target && event.target.classList.contains("addRow")) {
                                            addRow();
                                        }
                                    });

                                    tableBody.addEventListener("click", function (event) {
                                        if (event.target && event.target.classList.contains("removeRow")) {
                                            const row = event.target.closest("tr");
                                            removeRow(row);
                                        }
                                    });
                                    updateButtonVisibility();

                                    const createCombineGalleryBtn = document.getElementById("createCombineGallery");
                                    const slugFieldContainer = document.getElementById("slugFieldContainer");
                                    const combineGallerySlug = document.querySelector('.combineGallerySlug').value;
                                    if (combineGallerySlug === "") {
                                        createCombineGalleryBtn.style.display = 'block';
                                        slugFieldContainer.style.display = 'none';
                                        createCombineGalleryBtn.addEventListener("click", function () {
                                            if (slugFieldContainer.style.display === "none" || slugFieldContainer.style.display === "") {
                                                slugFieldContainer.style.display = "block";
                                            } else {
                                                slugFieldContainer.style.display = "none";
                                            }
                                        });
                                    } else {
                                        createCombineGalleryBtn.style.display = 'none';
                                        slugFieldContainer.style.display = 'block';
                                    }
                                }); 
                            </script>
                        </div>
                        <table class="form-table">
                            <tr valign="top">
                                <td>
                                    <button type="button" id="createCombineGallery" style="display: none">Create Combine Gallery
                                        Page</button>

                                    <div id="slugFieldContainer" style="display: none">
                                        <div class="fieldGroup">
                                            <label class="combine_page_bb">Combined Page Slug</label>
                                            <input type="text" id="combine_gallery_slug" class="combineGallerySlug"
                                                name="combine_gallery_slug"
                                                value="<?php echo esc_attr($combine_gallery_slug); ?>">
                                        </div>
                                        <div class="fieldGroup">
                                            <label class="combine_page_bb">SEO Page Title</label>
                                            <input type="text" id="bb_combine_seo_page_title" class="combineSeoTitle"
                                                name="bb_combine_seo_page_title"
                                                value="<?php echo esc_attr($bb_combine_seo_page_title); ?>">
                                        </div>
                                        <div class="fieldGroup">
                                            <label class="combine_page_bb">SEO Page Description</label>
                                            <input type="text" id="bb_combine_seo_page_description"
                                                class="combineSeoDescription" name="bb_combine_seo_page_description"
                                                value="<?php echo esc_attr($bb_combine_seo_page_description); ?>">
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">BRAG book Landing Page</th>
                                <td>
                                    <?php
                                    $content = get_option('bragbook_landing_page_text');
                                    if (empty($content)) {
                                        $content = '<h2>Go ahead, browse our before &amp; afters... visualize your possibilities.</h2>
                                        Our gallery is full of our real patients. Keep in mind results vary.
                                        [bragbook_carousel_shortcode category="Laser Skin Resurfacing" start="0" limit="10" title="0" details="0"]';
                                    }
                                    wp_editor($content, 'bragbook_landing_page_text', array('textarea_name' => 'bragbook_landing_page_text'));
                                    ?>
                                </td>
                            </tr>
                        </table>

                        <table class="form-table">
                            <tr valign="top">
                                <th scope="row">Select Installed SEO Plugin</th>
                                <td>
                                    <?php $selected_plugin = get_option('bb_seo_plugin_selector', ''); ?>
                                    <select name="bb_seo_plugin_selector" id="bb_seo_plugin_selector">
                                        <option value="">No SEO Plugins</option>
                                        <option value="1" <?php selected($selected_plugin, '1'); ?>>Yoast SEO</option>
                                        <option value="2" <?php selected($selected_plugin, '2'); ?>>All in One SEO</option>
                                        <option value="3" <?php selected($selected_plugin, '3'); ?>>Rank Math</option>
                                    </select>
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row">Select Design</th>
                                <td>
                                    <?php $selected_design = get_option('bb_design_plugin_selector', ''); ?>
                                    <select name="bb_design_plugin_selector" id="bb_design_plugin_selector">
                                        <option value="v1" <?php selected($selected_design, 'v1'); ?>>V1</option>
                                        <option value="v2" <?php selected($selected_design, 'v2'); ?>>V2</option>
                                    </select>
                                </td>
                            </tr>
                            
                            <tr valign="top">
                                <th scope="row">Update API</th>
                                <td>
                                    <button type="button" id="bb_update_api">Update API</button>
                                    <p class="update-api-status"></p>
                                    <span class="update-api"></span>
                                </td>
                            </tr>
                        </table>
                        <table class="form-table submit-button-table">
                            <tr valign="top">
                                <td>
                                    <?php submit_button(); ?>
                                    <p class="bb-save-api-settings-status"></p>
                                    <span class="bb-save-api-status"></span>
                                </td>
                            </tr>
                        </table>

                    </form>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $("#accordion").accordion();
            });
        </script>
        <?php
    }


    function bragbook_my_favorite_handler()
    {
        $email = sanitize_email($_POST['email']);
        $phone = sanitize_text_field($_POST['phone']);
        $name = sanitize_text_field($_POST['name']);
        $caseId = (int) $_POST['caseIds'][0];
        $caseIds = $_POST['caseIds'];
        $bbApiTokens = $_POST['bbApiTokens'];
        $bbWebsiteIds = $_POST['bbWebsiteIds'];

        $api_token = $bbApiTokens[0];
        $bbApiTokens = explode(", ", $bbApiTokens[0]); // Splitting the tokens by ", "
        $websiteproperty_id_array = array_map('intval', explode(", ", $bbWebsiteIds[0]));

        $favorite_data = new Bb_Api();
        $response = $favorite_data->bb_get_favorite_data($bbApiTokens, $websiteproperty_id_array, $email, $phone, $name, $caseId);

        $data = json_decode($response);

        if (isset($data->success) && $data->success) {
            $expireTime = time() + (365 * 24 * 60 * 60); // 1 year
            if (isset($_COOKIE['wordpress_favorite_email']) && $_COOKIE['wordpress_favorite_email'] === $email) {
                $newCaseIds = $caseIds;
                if (isset($_COOKIE['wordpress_favorite_case_id'])) {
                    $existingCaseIds = explode(',', $_COOKIE['wordpress_favorite_case_id']);
                    $allCaseIds = array_unique(array_merge($existingCaseIds, $newCaseIds));
                } else {
                    $allCaseIds = $newCaseIds;
                }
                $caseIdsString = implode(',', $allCaseIds);
            } else {
                $caseIdsString = implode(',', $caseIds);
            }
            $cookie_path = '/';

            setcookie('wordpress_favorite_email', $email, $expireTime, $cookie_path);
            setcookie('wordpress_favorite_case_id', $caseIdsString, $expireTime, $cookie_path);
            setcookie('wordpress_favorite_name', $name, $expireTime, $cookie_path);
            setcookie('wordpress_favorite_phone', $phone, $expireTime, $cookie_path);

            wp_send_json_success($data);
        } else {
            wp_send_json_error(array('message' => 'Failed to save favorite.'));
        }
        die();
    }

    public function bb_nudity_warning()
    {
        ?>
        <div class="popup-overlay" id="popup">
            <div class="popup">
                <h3><span>WARNING:</span> These galleries contain nudity.</h3>
                <p> if you are offended by such material or are under 18 years of age, please do not proceed</p>
                <div class="popup-btn">
                    <button onclick="closePopup()">proceed</button>
                    <button onclick="leavePopup()">do not proceed</button>
                </div>
            </div>
        </div>
        <?php
    }

    function bb_delete_default_page($post_id)
    {
        if (get_post_type($post_id) === 'page') {
            $page_slug = get_post_field('post_name', $post_id);
            $combine_gallery_slug = get_option('combine_gallery_slug');
            if ($page_slug == $combine_gallery_slug) {
                update_option('combine_gallery_slug', "");
            }

            $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids');
            $bb_remove_id = array_search($post_id, $bb_page_list_gallery);

            $bb_id_list = get_option('bb_gallery_stored_pages');
            $bb_id_list_gallery = get_option('bb_gallery_page_slug');
            $bb_page_list_gallery = get_option('bb_gallery_stored_pages_ids');
            $bragbook_websiteproperty_id = get_option('bragbook_websiteproperty_id');
            $bragbook_api_token = get_option('bragbook_api_token');

            update_option('bb_remove_pages_from_setting', $bb_id_list_gallery);
            update_option('bb_remove_combine_gallery_from_setting', $combine_gallery_slug);

            unset($bb_id_list_gallery[$bb_remove_id]);
            unset($bb_id_list[$bb_remove_id]);
            unset($bb_page_list_gallery[$bb_remove_id]);
            unset($bragbook_websiteproperty_id[$bb_remove_id]);
            unset($bragbook_api_token[$bb_remove_id]);

            update_option('bb_gallery_page_slug', $bb_id_list_gallery);
            update_option('bb_gallery_stored_pages_ids', $bb_page_list_gallery);
            update_option('bragbook_websiteproperty_id', $bragbook_websiteproperty_id);
            update_option('bragbook_api_token', $bragbook_api_token);
            update_option('bb_gallery_stored_pages', $bb_id_list);
        }
    }
}
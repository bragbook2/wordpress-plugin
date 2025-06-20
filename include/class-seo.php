<?php
namespace mvpbrag;

class Seo
{
    public $seoData;

    public function __construct()
    {
        $this->seoData = $this->get_custom_bragbook_title_and_description();
        add_action('wp', [$this, 'bb_seo']);
    }

    public function bb_get_current_url()
    {
        $current_link = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $current_link .= "s";
        }
        $current_link .= "://";
        $current_link .= "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        return $current_link;
    }

    public function get_custom_bragbook_title_and_description()
    {
        $site_title = get_bloginfo('name');
        $brag_page_data = get_option('bragbook_landing_page_text');
        $explode_string = explode('[', $brag_page_data);
        if (isset($explode_string[1])) {
            $explode_string[1] = '[' . $explode_string[1];
        }

        $bb_content = $explode_string['0'];
        if (preg_match('/^(<[^>]+>.*?<\/[^>]+>)(.*)$/s', $bb_content, $matches)) {
            $first_element = trim($matches[1]);
            $text_after_first_tag = trim($matches[2]);
        }
        $bbrag_case_url = strtok($_SERVER["REQUEST_URI"], '?');
        $bbragbook_case_url = trim($bbrag_case_url, '/');

        $parts = explode('/', $bbragbook_case_url);

        $bb_seo_title = null;
        $bb_seo_description = null;
        $combine_gallery_page_id = get_option('combine_gallery_page_id');
        $combine_gallery_page = get_post($combine_gallery_page_id);
        $combine_gallery_page_slug = get_option('combine_gallery_slug');
        $api_tokens = get_option('bragbook_api_token', []);
        $websiteproperty_ids = get_option('bragbook_websiteproperty_id', []);
        $gallery_slugs = get_option('bb_gallery_page_slug', []);
        $bb_seo_pages_title = get_option('bb_seo_page_title', []);
        $bb_seo_pages_description = get_option('bb_seo_page_description', []);
        $bb_combine_seo_page_title = get_option('bb_combine_seo_page_title', []);
        $bb_combine_seo_page_description = get_option('bb_combine_seo_page_description', []);

        //Get caseId from URL if exists
        $caseId = null;
        $seoSuffixUrl = null;
        $procedureName = null;
        $procedureTotalCase = null;

        if (isset($parts[0]) && empty($parts[1]) && empty($parts[2])) {
            if ($combine_gallery_page_slug == $parts[0]) {
                $bb_seo_title = $bb_combine_seo_page_title;
                $bb_seo_description = $bb_combine_seo_page_description;
            } else {
                foreach ($api_tokens as $index => $api_token) {
                    $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
                    $page_slug_bb = $gallery_slugs[$index] ?? '';
                    if (($page_slug_bb == $parts[0])) {
                        $bb_seo_title = $bb_seo_pages_title[$index] ?? '';
                        $bb_seo_description = $bb_seo_pages_description[$index] ?? '';
                        if (empty($api_token) || empty($websiteproperty_id)) {
                            continue;
                        }
                    }
                }
            }
        }
        if (isset($parts[1]) && empty($parts[2])) {
            if ($combine_gallery_page_slug == $parts[0]) {
                $procedureIdsName = $this->getProcedureIDFromSidebar(array_values($api_tokens), $parts[1], true);
                $procedureTotalCase = $procedureIdsName["procedureTotalCase"];
            } else {
                foreach ($api_tokens as $index => $api_token) {
                    $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
                    $page_slug_bb = $gallery_slugs[$index] ?? '';
                    if (($page_slug_bb == $parts[0]) || ($combine_gallery_page_slug == $parts[0])) {
                        if (empty($api_token) || empty($websiteproperty_id)) {
                            continue;
                        }
                        $procedureIdsName = $this->getProcedureIDFromSidebar($api_token, $parts[1], false);
                        $procedureTotalCase = $procedureIdsName["procedureTotalCase"];
                    }
                }
            }
        }
        if (isset($parts[2]) && !empty($parts[2])) {
            if (strpos($parts[2], 'bb-case') !== false) {
                preg_match('/\d+/', $parts[2], $matches);
                $caseId = isset($matches[0]) ? (int) $matches[0] : 'Default string';
            } else {
                $seoSuffixUrl = $parts[2];
            }
            $caseId = $caseId ? $caseId : '123';
            // Get case data for combine pages
            if ($combine_gallery_page_slug == $parts[0]) {
                // get procedureIds from sidebar API
                $procedureIdsName = $this->getProcedureIDFromSidebar(array_values($api_tokens), $parts[1], true);
                $procedureIds = $procedureIdsName["bb_procedure_id"];
                $procedureName = $procedureIdsName["bb_procedure_name"];
                $filter_data = new Bb_Api();
                $data = $filter_data->bb_get_case_data($caseId, $seoSuffixUrl, $api_tokens, $procedureIds, $websiteproperty_ids);

                $bb_api_data = json_decode($data, true);
            } else {
                // Get case data for single page
                foreach ($api_tokens as $index => $api_token) {
                    $websiteproperty_id = $websiteproperty_ids[$index] ?? '';
                    $page_slug_bb = $gallery_slugs[$index] ?? '';

                    if (($page_slug_bb == $parts[0]) || ($combine_gallery_page_slug == $parts[0])) {
                        if (empty($api_token) || empty($websiteproperty_id)) {
                            continue;
                        }
                        // get procedureIds from sidebar API
                        $procedureIdsName = $this->getProcedureIDFromSidebar($api_token, $parts[1], false);
                        $procedureId = $procedureIdsName["bb_procedure_id"];
                        $procedureName = $procedureIdsName["bb_procedure_name"];

                        $transient_key = 'cases_' . md5($api_token . $procedureId . $websiteproperty_id . $caseId . $seoSuffixUrl);
                        $data = get_transient($transient_key);
                        if (false === $data) {
                            $filter_data = new Bb_Api();
                            $data = $filter_data->bb_get_case_data($caseId, $seoSuffixUrl, $api_token, $procedureId, $websiteproperty_id);

                            set_transient($transient_key, $data, 3600);
                        }

                        $bb_api_data = json_decode($data, true);
                    }
                }
            }
            if (strpos($parts[2], 'bb-case') !== false) {
                // Use preg_match to extract the caseID after 'bb-case-'
                preg_match('/\d+/', $parts[2], $matches);
                $bbrag_case_id = isset($matches[0]) ? (int) $matches[0] : '';
            } else {
                $bbrag_case_id = $parts[2];
            }

            $bb_response = $bb_api_data['data'][0];
            $bb_case_ids = $bb_api_data['data'][0]['caseIds'];
            $bb_seo_case_title = "";
            $bb_seo_case_description = "";
            $bb_case_number = null;
            foreach ($bb_case_ids as $key => $bb_case_item) {
                if (
                    ($bb_case_item['id'] == $bbrag_case_id && strpos($parts[2], 'bb-case') !== false) ||
                    ($bb_case_item['seoSuffixUrl'] == $bbrag_case_id && strpos($parts[2], 'bb-case') === false)
                ) {
                    $bb_case_number = ++$key;
                    break;
                }
            }
            if (!empty($bb_api_data) && is_array($bb_api_data)) {
                if ($bbrag_case_id == $bb_response['id'] || $bbrag_case_id == $bb_response['caseDetails'][0]['seoSuffixUrl']) {
                    if (isset($bb_response['caseDetails'][0]) && !empty($bb_response['caseDetails'][0]['seoPageTitle']) && $bb_seo_case_title == "") {
                        $bb_seo_title = $bb_seo_case_title = isset($bb_response['caseDetails'][0]) ? $bb_response['caseDetails'][0]['seoPageTitle'] . " - " . $site_title : "";
                    } else {
                        $bb_seo_title = "Before and After " . $procedureName . ": Patient " . $bb_case_number . " - " . $site_title;
                    }
                    if (isset($bb_response['caseDetails'][0]) && !empty($bb_response['caseDetails'][0]['seoPageDescription']) && $bb_seo_case_description == "") {
                        $bb_seo_description = $bb_seo_case_description = isset($bb_response['caseDetails'][0]) ? $bb_response['caseDetails'][0]['seoPageDescription'] : "";
                    }
                }
            }
        }
        if (count($parts) == 2) {
            if ($parts[1] == "favorites" || $parts[1] == "consultation") {
                $procedureTotalCase = null;
            }
            $bbrag_procedure_title = $parts[1];
            $bb_pro_title_all_seo = ucwords(str_replace("-", " ", $bbrag_procedure_title));

            $bb_seo_title = "Before and After " . $bb_pro_title_all_seo . " Gallery, " . $procedureTotalCase . " Cases - " . $site_title;
        }
        $bb_title_description_array = ['bb_title' => $bb_seo_title, 'bb_description' => $bb_seo_description, 'bb_procedure_name' => $procedureName];
        return $bb_title_description_array;
    }

    public function getProcedureIDFromSidebar($api_tokens, $procedureSlug, $iscombine)
    {
        $procedureName = [];
        $bbprocedureTotalCase = [];
        if ($iscombine) {
            $cacheKey = "$procedureSlug-combine";
            // Get sidebar data from cache
            $sidebar_list = '';
            if (!$sidebar_list) {
                $sidebar = new Bb_Api();
                $sidebar_list = $sidebar->get_api_sidebar_bb($api_tokens);
                set_transient($cacheKey, $sidebar_list, HOUR_IN_SECONDS);
                $sidebar = json_decode($sidebar_list);
                $procedureIds = [];
                if (isset($sidebar) && isset($sidebar->data)) {
                    foreach ($sidebar->data as $category) {
                        foreach ($category->procedures as $procedure) {
                            if ($procedure->slugName == $procedureSlug) {
                                $procedureIds = $procedure->ids;
                                $procedureName = $procedure->name;
                                $bbprocedureTotalCase = $procedure->totalCase;
                            }
                        }
                    }
                }
            }
        } else {
            $cacheKey = "$procedureSlug-single";
            $sidebar_list = get_transient($cacheKey);
            if (!$sidebar_list) {
                $sidebar = new Bb_Api();
                $sidebar_list = $sidebar->get_api_sidebar_bb($api_tokens);
                set_transient($cacheKey, $sidebar_list, HOUR_IN_SECONDS);
            }
            $sidebar = json_decode($sidebar_list);
            $procedureIds = null;
            if (isset($sidebar) && isset($sidebar->data)) {
                foreach ($sidebar->data as $category) {
                    foreach ($category->procedures as $procedure) {
                        if ($procedure->slugName == $procedureSlug) {
                            $procedureIds = $procedure->ids[0];
                            $procedureName = $procedure->name;
                            $bbprocedureTotalCase = $procedure->totalCase;
                            break 2;
                        }
                    }
                }
            }
        }
        $bb_procedure_id_name_array = ['bb_procedure_id' => $procedureIds, 'bb_procedure_name' => $procedureName, 'procedureTotalCase' => $bbprocedureTotalCase];
        return $bb_procedure_id_name_array;
    }

    public function bb_get_custom_bragbook_title()
    {
        // Get SEO data from constructor
        $brag_book_title = $this->seoData;
        return $brag_book_title['bb_title'];
    }

    public function bb_get_custom_bragbook_description()
    {
        $brag_book_description = $this->seoData;
        return $brag_book_description['bb_description'];
    }

    public function bb_print_custom_bragbook_description()
    {
        echo '<meta name="description" content="' . $this->bb_get_custom_bragbook_description() . '">';
    }

    public function bb_print_canonical()
    {
        echo '<link rel="canonical" href="' . $this->bb_get_current_url() . '">';
    }

    public function bb_seo()
    {
        if (is_admin()) {
            return;
        }

        $current_page_id = get_queried_object_id();
        $stored_pages_ids = get_option('bb_gallery_stored_pages_ids');
        $combine_gallery_page_id = get_option('combine_gallery_page_id');
        $combine_gallery_page_slug = get_option('combine_gallery_slug');
        $get_page_slug = get_post($current_page_id);
        $current_get_page_slug = $get_page_slug->post_name;

        if ((is_array($stored_pages_ids) && in_array($current_page_id, $stored_pages_ids)) || $current_page_id == $combine_gallery_page_id || $combine_gallery_page_slug == $current_get_page_slug) {
            if (get_option('bb_seo_plugin_selector') == 1) {
                add_filter('wpseo_canonical', array($this, 'bb_get_current_url'));
                add_filter('wpseo_title', array($this, 'bb_get_custom_bragbook_title'));
                add_filter('wpseo_metadesc', array($this, 'bb_get_custom_bragbook_description'));
                add_filter('wpseo_canonical', array($this, 'bb_get_current_url'));
                add_filter('wpseo_opengraph_title', array($this, 'bb_get_custom_bragbook_title'));
                add_filter('wpseo_opengraph_desc', array($this, 'bb_get_custom_bragbook_description'));
                add_filter('wpseo_opengraph_url', array($this, 'bb_get_current_url'));
            } else {
                if (get_option('bb_seo_plugin_selector') == 2) {
                    add_filter('aioseo_canonical_url', array($this, 'bb_get_current_url'));
                    add_filter('aioseo_title', array($this, 'bb_get_custom_bragbook_title'));
                    add_filter('aioseo_description', array($this, 'bb_get_custom_bragbook_description'));
                } elseif (get_option('bb_seo_plugin_selector') == 3) {
                    add_filter('rank_math/frontend/canonical', array($this, 'bb_get_current_url'));
                    add_filter('rank_math/frontend/title', array($this, 'bb_get_custom_bragbook_title'));
                    add_filter('rank_math/frontend/description', array($this, 'bb_get_custom_bragbook_description'));
                } else {
                    add_filter('wp_title', array($this, 'bb_get_custom_bragbook_title'), 999, 0);
                    add_filter('pre_get_document_title', array($this, 'bb_get_custom_bragbook_title'), 999, 0);
                    add_action('wp_head', array($this, 'bb_print_custom_bragbook_description'));
                    remove_action('wp_head', 'rel_canonical');
                    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
                    add_action('wp_head', array($this, 'bb_print_canonical'));
                }
            }
        }
    }
}
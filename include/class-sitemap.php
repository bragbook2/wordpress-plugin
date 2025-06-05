<?php
namespace mvpbrag;

class Sitemap
{
    public function __construct()
    {
        add_filter('wpseo_sitemap_index', array($this, 'add_bragbook_sitemap_to_yoast'));
    }

    function bb_get_sitemap_data()
    {

        $api_tokens = get_option('bragbook_api_token', []);
        $websiteproperty_ids = array_values(get_option('bragbook_websiteproperty_id', []));

        $url = BB_BASE_URL . "/api/plugin/sitemap";
        $response = wp_remote_post($url, array(
            'method' => 'POST',
            'body' => json_encode(array(
                'apiTokens' => array_values($api_tokens),
                'websitePropertyIds' => array_map('intval', $websiteproperty_ids),
            )),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
        }

        $response_body = wp_remote_retrieve_body($response);
        $response = json_decode($response_body);

        return $response;
    }

    function create_bragbook_sitemap()
    {
        $pluginURL = plugin_dir_url(__DIR__) . 'assets/css/brag-book-sitemap-style.xsl';

        $revXmlSitemapOutput = '<?xml version="1.0" encoding="UTF-8"?>';
        $revXmlSitemapOutput .= '<?xml-stylesheet type="text/xsl" href="' . $pluginURL . '"?>';
        $revXmlSitemapOutput .= '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.google.com/schemas/sitemap-image/1.1 http://www.google.com/schemas/sitemap-image/1.1/sitemap-image.xsd" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $bb_sitemap_data = $this->bb_get_sitemap_data()->data;

        $gallery_slugs = array_values(get_option('bb_gallery_page_slug', []));
        foreach ($bb_sitemap_data as $index => $org_data) {

            foreach ($org_data as $sitemap_slug) {
                $sitemap_url = home_url() . "/" . $gallery_slugs[$index] . $sitemap_slug->url . '/';
                $sitemap_url_date = $sitemap_slug->updatedAt ?? "";
                $revXmlSitemapOutput .= '<url><loc>' . $sitemap_url . '</loc><lastmod>' . $sitemap_url_date . '</lastmod></url>';
            }
        }
        $revXmlSitemapOutput .= '</urlset>';

        $dom = new \DOMDocument;
        $dom->preserveWhiteSpace = false;
        $dom->loadXML($revXmlSitemapOutput);
        $dom->save($_SERVER['DOCUMENT_ROOT'] . '/brag-book-sitemap.xml');
    }

    function add_bragbook_sitemap_to_yoast()
    {

        $timezone = get_option('timezone_string');
        if (!$timezone) {
            $gmt_offset = get_option('gmt_offset');
            $timezone = timezone_name_from_abbr('', $gmt_offset * 3600, 0);

            if ($timezone === false) {
                $timezone = 'UTC';
            }
        }
        $date = new \DateTime('now', new \DateTimeZone($timezone));

        $appended_text = '';

        $static_post_link = home_url() . '/brag-book-sitemap.xml';
        $static_lastmod = $date->format('c');
        $appended_text .= '<sitemap>' .
            '<loc>' . esc_url($static_post_link) . '</loc>' .
            '<lastmod>' . $static_lastmod . '</lastmod>' .
            '</sitemap>';

        return $appended_text;
    }
}
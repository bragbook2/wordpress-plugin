<?php
namespace mvpbrag;

class Bb_Api
{
    public function bb_get_filter_data($apiTokens, $procedureIds, $websitePropertyIds)
    {
        $url = BB_BASE_URL . "/api/plugin/combine/filters";
        $response = wp_remote_post($url, array(
            'method' => 'POST',
            'body' => json_encode(array(
                'apiTokens' => explode(", ", $apiTokens),
                'procedureIds' => array_map('intval', explode(", ", $procedureIds)),
                'websitePropertyIds' => array_map('intval', explode(", ", $websitePropertyIds)),
            )),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        $filter_get = wp_remote_retrieve_body($response);

        return $filter_get;
    }

    public function send_plugin_version_data($json_payload)
    {
        $url = BB_BASE_URL . "/api/plugin/tracker";
        $response = wp_remote_post($url, array(
            'method' => 'POST',
            'body' => $json_payload,

            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));


        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        $response_body = wp_remote_retrieve_body($response);

        return $response_body;
    }

    public function bb_get_case_data($caseId, $seoSuffixUrl, $apiToken, $procedureId, $websitePropertyId)
    {

        $url = BB_BASE_URL . "/api/plugin/combine/cases/$caseId?seoSuffixUrl=$seoSuffixUrl";
        $response = wp_remote_post($url, array(
            'method' => 'POST',
            'body' => json_encode(array(
                'apiTokens' => explode(", ", is_array($apiToken) ? implode(", ", $apiToken) : $apiToken),
                'procedureIds' => array_map('intval', explode(", ", is_array($procedureId) ? implode(", ", $procedureId) : $procedureId)),
                'websitePropertyIds' => array_map('intval', explode(", ", is_array($websitePropertyId) ? implode(", ", $websitePropertyId) : $websitePropertyId)),
            )),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        $data = wp_remote_retrieve_body($response);

        return $data;

    }

    public function bb_get_favorite_data($bbApiTokens, $websiteproperty_id_array, $email, $phone, $name, $caseId)
    {

        $url = BB_BASE_URL . '/api/plugin/combine/favorites/add';
        $response = wp_remote_post($url, array(
            'method' => 'POST',
            'body' => json_encode(array(
                "apiTokens" => $bbApiTokens,
                "websitePropertyIds" => array_map('intval', $websiteproperty_id_array),
                'email' => $email,
                'phone' => $phone,
                'name' => $name,
                'caseId' => $caseId,
            )),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        $data = wp_remote_retrieve_body($response);

        return $data;
    }

    public function bb_get_favorite_list_data($fav_token, $web_id, $favorite_email_id)
    {

        $url_fav = BB_BASE_URL . "/api/plugin/combine/favorites/list";
        $response = wp_remote_post($url_fav, array(
            'method' => 'POST',
            'body' => json_encode(array(
                'apiTokens' => $fav_token,
                'websitePropertyIds' => $web_id,
                'email' => $favorite_email_id
            )),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }

        $data = wp_remote_retrieve_body($response);

        return $data;
    }

    public function bb_get_pagination_data($dynamicFilterCombineAPIBody)
    {

        $url = BB_BASE_URL . "/api/plugin/combine/cases";

        $response = wp_remote_post($url, array(
            'method' => 'POST',
            'body' => json_encode($dynamicFilterCombineAPIBody),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ));

        if (is_wp_error($response)) {
            wp_send_json_error(array('message' => $response->get_error_message()));
        }
        $data = wp_remote_retrieve_body($response);

        return $data;
    }

    public function get_api_sidebar_bb($api_token)
    {

        if (!is_array($api_token)) {
            $api_token = [$api_token];
        }

        $url = BB_BASE_URL . "/api/plugin/combine/sidebar";
        $response = wp_remote_post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'apiTokens' => $api_token,
            ]),
        ]);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            echo "Something went wrong: $error_message";
            return null;
        } else {
            $data = wp_remote_retrieve_body($response);
        }

        return $data;
    }

    public static function register_routes()
    {
        add_action('rest_api_init', [__CLASS__, 'register_proxy_endpoint']);
    }

    public static function register_proxy_endpoint()
    {
        register_rest_route('bb/v1', '/optimize-image-proxy', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'proxy_optimize_image'],
            'permission_callback' => '__return_true',
        ]);
    }

    public static function proxy_optimize_image(\WP_REST_Request $request)
    {
        $image_url = $request->get_param('url');
        $quality = $request->get_param('quality') ?? 'small';
        $format = $request->get_param('format') ?? 'png';

        $api_token = $request->get_header('x-api-token') ?: $request->get_param('x-api-token');
        $version   = $request->get_header('x-plugin-version') ?: $request->get_param('x-plugin-version');

        if (!$image_url || !$api_token) {
            return new \WP_REST_Response(['error' => 'Missing parameters'], 400);
        }

        $target_url = BB_BASE_URL . "/api/plugin/optimize-image?" . http_build_query([
            'url' => $image_url,
            'quality' => $quality,
            'format' => $format,
        ]);

        $response = wp_remote_get($target_url, [
            'headers' => [
                'x-api-token' => $api_token,
                'x-plugin-version' => $version,
            ]
        ]);

        if (is_wp_error($response)) {
            return new \WP_REST_Response(['error' => $response->get_error_message()], 500);
        }

        $body = wp_remote_retrieve_body($response);
        $content_type = wp_remote_retrieve_header($response, 'content-type') ?: 'image/' . $format;
        add_filter('rest_pre_echo_response', function () {
            return true;
        });

        ob_clean();
        header('Content-Type: ' . $content_type);
        header('Content-Length: ' . strlen($body));
        echo $body;
        exit;
    }
}
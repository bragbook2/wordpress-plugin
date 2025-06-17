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
}
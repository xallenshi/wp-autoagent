<?php
namespace WPAutoAgent\Core;

class API {
    private $api_token;

    public function __construct() {
        $this->api_token = get_option('wp_autoagent_access_key');
        add_action('wp_ajax_api_proxy_request', array($this, 'api_proxy_request'));
    }

    public function api_embedding_request($chunks) {
        check_ajax_referer('wp_autoagent_settings', 'nonce');

        $post_data = array(
            'name' => 'Hello!!!'
        );

        error_log('Making request to WP AutoAgent API');

        $api_embedding_url = wp_autoagent_API_EMBEDDING_URL;

        $response = wp_remote_post($api_embedding_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_token
            ),
            'body' => json_encode($post_data),
            'timeout' => 60,
        ));

        if (is_wp_error($response)) {
            error_log('Error calling WP AutoAgent API: ' . $response->get_error_message());
            return new \WP_Error('api_error', 'Error calling WP AutoAgent API: ' . $response->get_error_message());
        } else {
            $body = wp_remote_retrieve_body($response);
            error_log('Response from WP AutoAgent API: ' . $body);
            return json_decode($body, true);
        }
    }
}

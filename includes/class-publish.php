<?php
namespace WPAutoAgent\Core;

class Publish {
    private $table_agent;

    public function __construct() {
        $this->table_agent = Config::get_table_name('agent');
        add_action('wp_ajax_wpaa_get_agent_scope', array($this, 'wpaa_get_agent_scope'));

        add_action('wp_ajax_wpaa_get_theme_color', array($this, 'wpaa_get_theme_color'));
        add_action('wp_ajax_nopriv_wpaa_get_theme_color', array($this, 'wpaa_get_theme_color'));

        add_action('wp_ajax_wpaa_publish_agent', array($this, 'wpaa_publish_agent'));
    }

    /**
     * AJAX handler to get agent scope
     */
    public function wpaa_get_agent_scope() {
        if (!check_ajax_referer('wpaa_setting', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }

        if (!isset($_POST['agent_id'])) {
            wp_send_json_error('No agent ID provided.');
            return;
        }

        $agent_id = intval($_POST['agent_id']);
        $db_handler = new DBHandler();
        $agent = $db_handler->get_agent_by_id($agent_id);

        if (!$agent) {
            wp_send_json_error('Agent not found.');
            return;
        }

        // Get the scope
        $scope = json_decode($agent->scope, true);
        
        if (!$scope) {
            $scope = array();
        }

        wp_send_json_success($scope);
    }


    /**
     * AJAX handler to publish the agent
     */
    public function wpaa_publish_agent() {
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agent_id']) && (isset($_POST['selected_pages']) || isset($_POST['selected_admin_pages']))) {
            $agent_id = intval($_POST['agent_id']);
            $selected_pages = isset($_POST['selected_pages']) ? $_POST['selected_pages'] : array();
            $selected_admin_pages = isset($_POST['selected_admin_pages']) ? $_POST['selected_admin_pages'] : array();

            // Convert selected pages to JSON
            $scope_json = json_encode(array_merge($selected_pages, $selected_admin_pages));

            global $wpdb;
            $result = $wpdb->update(
                $this->table_agent,
                ['scope' => $scope_json],
                ['id' => $agent_id]
            );

            // Set the theme color when publishing the agent
            $this->set_theme_color();
            // Create a Qdrant collection
            //$this->create_qdrant_collection();

            if ($result !== false) {
                wp_send_json_success('Agent has been published.');
            } else {
                wp_send_json_error('Error publishing agent.');
            }

        }

    }

    /**
     * Set the theme color for chat panel background color
     */
    public function set_theme_color() {
        $db_handler = new DBHandler();
        $global_setting = $db_handler->get_global_setting();
        $saved_theme_name = $global_setting->theme_name;

        $current_theme = wp_get_theme();
        $current_theme_name = $current_theme->get('Name');

        //if new theme, run the agent to get the theme color
        if ($saved_theme_name !== $current_theme_name) {
            $run = new Run();
            $api_response = $run->wpaa_run_the_agent(1, $current_theme->get_screenshot(), null);
            if ($api_response) {
                $new_theme_color = $api_response;
                $global_setting->theme_color = $new_theme_color;
                $global_setting->theme_name = $current_theme_name;
                $db_handler->update_global_setting($global_setting);
            }
        }
    }

    function wpaa_get_theme_color() {
        $db_handler = new DBHandler();
        $global_setting = $db_handler->get_global_setting();
        $theme_color = $global_setting->theme_color;
        wp_send_json_success($theme_color);
    }


    /**
     * Create a Qdrant collection
     */
    function create_qdrant_collection() {

        $db_handler = new DBHandler();
        $global_setting = $db_handler->get_global_setting();
        $access_key = $global_setting->access_key;


        // make a rest api call to Lambda function to run the agent
        $api_url = 'https://zx2jc24fdhdyl47mv3u7z53msq0orywi.lambda-url.ap-southeast-2.on.aws/';
        $api_response = wp_remote_post($api_url, array(
            'method' => 'POST',
            'body' => json_encode(array('access_key' => $access_key)),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 60,
        ));

        //error_log('api_response: ' . print_r($api_response, true));
        if (is_wp_error($api_response)) {
            $api_error_msg = $api_response->get_error_message(); 
            error_log('api_error_msg: ' . $api_error_msg);
            return false;
        }
        if (wp_remote_retrieve_response_code($api_response) != 200) {
            error_log('Failed to create Qdrant collection.');
            return false;
        } else {
            $api_response_body = json_decode(wp_remote_retrieve_body($api_response), true);
            $api_msg = $api_response_body['message'];
            error_log('api_msg: ' . $api_msg);

            return true;
        }


    }
}
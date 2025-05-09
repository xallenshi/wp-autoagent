<?php
namespace WPAutoAgent\Core;

class Publish {
    private $table_agent;

    public function __construct() {
        $this->table_agent = Config::get_table_name('agent');
        add_action('wp_ajax_wpaa_get_agent_scope', array($this, 'wpaa_get_agent_scope'));

        add_action('wp_ajax_wpaa_get_theme_color', array($this, 'wpaa_get_theme_color'));
        add_action('wp_ajax_nopriv_wpaa_get_theme_color', array($this, 'wpaa_get_theme_color'));
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
}
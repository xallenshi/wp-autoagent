<?php
namespace WPAutoAgent\Core;

class Publish {
    private $table_agent;

    public function __construct() {
        $this->table_agent = Config::get_table_name('agent');
        add_action('wp_ajax_wpaa_get_agent_scope', array($this, 'wpaa_get_agent_scope'));
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

    public function get_theme_color() {
        
    }
}
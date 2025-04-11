<?php
namespace WPAutoAgent\Core;

class Chat {
    private $table_agent;

    public function __construct() {
        $this->table_agent = Config::get_table_name('agent');
        add_action('wp_ajax_wpaa_check_agent_scope', array($this, 'check_agent_scope'));
        add_action('wp_ajax_nopriv_wpaa_check_agent_scope', array($this, 'check_agent_scope'));
    }

    public function check_agent_scope() {
        if (!check_ajax_referer('wpaa_request', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }

        $current_url = sanitize_text_field($_POST['current_url']);
        $page_id = url_to_postid($current_url);
        
        $db_handler = new DBHandler();
        $agents = $db_handler->get_agents();
        foreach ($agents as $agent) {
            if (!empty($agent->scope)) {
                $scope = json_decode($agent->scope, true);
                if (is_array($scope) && in_array($page_id, $scope)) {
                    wp_send_json_success(array(
                        'agent_id' => $agent->id,
                        'message' => 'Page is in scope'
                    ));
                    return;
                }
            }
        }
        
        wp_send_json_error('Page is not in scope for any agent.');
    }
} 
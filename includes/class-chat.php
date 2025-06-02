<?php
namespace WPAgent\Core;

class Chat {
    private $table_agent;

    public function __construct() {
        $this->table_agent = Config::get_table_name('agent');
        add_action('wp_ajax_wpa_check_agent_scope', array($this, 'wpa_check_agent_scope'));
        add_action('wp_ajax_nopriv_wpa_check_agent_scope', array($this, 'wpa_check_agent_scope'));
    }

    public function wpa_check_agent_scope() {
        if (!check_ajax_referer('wpa_request', 'nonce', false)) {
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
                
                // Check if current page ID matches any frontend page in scope
                if (is_array($scope) && in_array($page_id, $scope)) {
                    wp_send_json_success(array(
                        'agent_id' => $agent->id,
                        'name' => $agent->name,
                        'greeting_message' => $agent->greeting_message,
                        'message' => 'Page is in scope'
                    ));
                    return;
                }
                
                // Check if current admin page slug matches any admin page in scope
                if (is_admin() && is_array($scope)) {
                    $current_page = isset($_POST['page_slug']) ? $_POST['page_slug'] : basename($_SERVER['PHP_SELF']);

                    // Normalize the current page slug for custom plugin pages
                    $current_page = str_replace('/wp-admin/admin.php?page=', '', $current_page);
                    // Normalize the current page slug for standard pages
                    $current_page = str_replace('/wp-admin/', '', $current_page);
    
                    if (isset($_GET['post_type'])) {
                        $current_page .= '?post_type=' . $_GET['post_type'];
                    }
                    
                    // Check if the current page matches any scope item
                    foreach ($scope as $scope_page) {
                        if ($scope_page === $current_page) {
                            wp_send_json_success(array(
                                'agent_id' => $agent->id,
                                'name' => $agent->name,
                                'greeting_message' => $agent->greeting_message,
                                'message' => 'Admin page is in scope'
                            ));
                            return;
                        }
                    }
                }
            }
        }
        
        wp_send_json_error('Page is not in scope for any agent.');
    }
}
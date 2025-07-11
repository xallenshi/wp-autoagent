<?php
namespace WPAgent\Core;

global $wpdb;
$db_handler = new DBHandler();
$articles = $db_handler->get_articles();
$functions = $db_handler->get_functions();

class Create {
    private $table_agent;

    public function __construct() {
        $this->table_agent = Config::get_table_name('agent');
        $this->table_conversation = Config::get_table_name('conversation');

        add_action('wp_ajax_wpa_create_agent', array($this, 'wpa_create_agent'));
        
        add_action('wp_ajax_wpa_get_agent', array($this, 'wpa_get_agent'));
        add_action('wp_ajax_nopriv_wpa_get_agent', array($this, 'wpa_get_agent'));

        add_action('wp_ajax_wpa_delete_agent', array($this, 'wpa_delete_agent'));
    }

    public function wpa_create_agent() {

        if (!check_ajax_referer('wpa_setting', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }

        if (!isset($_POST['name'])) {
            wp_send_json_error('No agent name provided.');
            return;
        }

        $agent_id = isset($_POST['agent_id']) ? sanitize_text_field($_POST['agent_id']) : null;
        $name = sanitize_text_field($_POST['name']);
        $instructions = wp_kses_post($_POST['instructions']);
        $greeting_message = wp_kses_post(wp_unslash($_POST['greeting_message']));
        $model = sanitize_text_field($_POST['model']);
        $selected_articles = isset($_POST['articles']) ? array_map('sanitize_text_field', $_POST['articles']) : [];
        $selected_functions = isset($_POST['functions']) ? array_map('sanitize_text_field', $_POST['functions']) : [];
        
        $tools_object = $this->get_tools_object($selected_articles, $selected_functions);
        $tools = $tools_object['tools'];

        if ($agent_id) {
            $this->update_agent($name, $instructions, $greeting_message, $model, $selected_articles, $selected_functions, $tools, $agent_id);
            wp_send_json_success('The agent has been updated.');
        } else {
            $agent_id = $this->save_agent($name, $instructions, $greeting_message, $model, $selected_articles, $selected_functions, $tools);
            wp_send_json_success('The agent has been created.');
        }

    }

    private function save_agent($name, $instructions, $greeting_message, $model, $article_ids, $function_ids, $tools) {
        global $wpdb;
        
        $result = $wpdb->insert($this->table_agent, array(
            'name' => $name,
            'instructions' => $instructions,
            'greeting_message' => $greeting_message,
            'model' => $model,
            'article_ids' => json_encode($article_ids),
            'function_ids' => json_encode($function_ids),
            'tools' => json_encode($tools),
            'created_time' => gmdate('Y-m-d H:i:s'),
            'updated_time' => gmdate('Y-m-d H:i:s'),
        ));

        if ($result === false) {
            error_log('Database insert error: ' . $wpdb->last_error);
            return false;
        }

        return $wpdb->insert_id;
    }

    private function update_agent($name, $instructions, $greeting_message, $model, $article_ids, $function_ids, $tools, $agent_id) {
        global $wpdb;
        
        $result = $wpdb->update($this->table_agent, array(
            'name' => $name,
            'instructions' => $instructions,
            'greeting_message' => $greeting_message,
            'model' => $model,
            'article_ids' => json_encode($article_ids),
            'function_ids' => json_encode($function_ids),
            'tools' => json_encode($tools),
            'updated_time' => gmdate('Y-m-d H:i:s'),
        ), array('id' => $agent_id));

        if ($result === false) {
            error_log('Database update error: ' . $wpdb->last_error);
            return false;
        }

        return true;
    }


    private function get_tools_object($article_ids, $function_ids) {
        global $wpdb;
        $tools = [];

        $db_handler = new DBHandler();

        // Add file search tool
        foreach ($article_ids as $article_id) {
            $vector_store_id = $db_handler->get_vector_store_id_by_article_id($article_id);
            if ($vector_store_id) {
                $vector_store_ids[] = $vector_store_id;
            }
        }

        if (!empty($vector_store_ids)) {
            $tools[] = array(
                'type' => 'file_search',
                'vector_store_ids' => $vector_store_ids,
                "max_num_results" => 3
            );
        }

        // Add function tools
        foreach ($function_ids as $function_id) {
            $function_data = $db_handler->get_function_by_id($function_id);
            if ($function_data) {
                $tools[] = json_decode($function_data->definition, true);
            }
        }

        return [
            'tools' => $tools
        ];
    }

    public function wpa_get_agent() {
        global $wpdb;
        $db_handler = new DBHandler();
        $agent_id = $_POST['agent_id'];
        $agent = $db_handler->get_agent_by_id($agent_id);
        
        $agent->instructions = stripslashes($agent->instructions);
        $agent->greeting_message = stripslashes($agent->greeting_message);
        wp_send_json_success($agent);
    }

    public function wpa_delete_agent() {
        global $wpdb;
        $agent_id = $_POST['agent_id'];
        if (!check_ajax_referer('wpa_setting', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }
        if (!isset($agent_id)) {
            wp_send_json_error('No agent ID provided.');
            return;
        }
        $wpdb->delete($this->table_agent, array('id' => $agent_id));
        $wpdb->delete($this->table_conversation, array('agent_id' => $agent_id));
        wp_send_json_success('The agent have been deleted.');
    }

}
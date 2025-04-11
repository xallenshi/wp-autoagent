<?php
namespace WPAutoAgent\Core;

use WPAutoAgent\Core\API;

global $wpdb;
$db_handler = new DBHandler();
$articles = $db_handler->get_articles();
$functions = $db_handler->get_functions();


class Create {
    private $table_agent;

    public function __construct() {
        $this->table_agent = Config::get_table_name('agent');
        add_action('wp_ajax_wpaa_create_agent', array($this, 'wpaa_create_agent'));
    }

    public function wpaa_create_agent() {

        if (!check_ajax_referer('wpaa_setting', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }

        if (!isset($_POST['name'])) {
            wp_send_json_error('No agent name provided.');
            return;
        }

        $name = $_POST['name'];
        $instructions = $_POST['instructions'];
        $model = $_POST['model'];
        $selected_article = $_POST['articles'] ?? [];
        $selected_functions = $_POST['functions'] ?? [];
        $vector_store_ids = ['vs_67d6248d4eec8191b0d64ef291a55a8d'];
        $tools_object = $this->get_tools_object($selected_article_ids, $selected_functions);
        $tools = $tools_object['tools'];
        $tool_resources = $tools_object['tool_resources'];

        // make a rest api call to Lambda function to process the article
        $api_url = 'https://pbe3crai7j4vy6eoo35pss3pzm0xcpxb.lambda-url.ap-southeast-2.on.aws/';
        $api_response = wp_remote_post($api_url, array(
            'method' => 'POST',
            'body' => json_encode(array(
                'name' => $name,
                'instructions' => $instructions,
                'model' => $model,
                'vector_store_ids' => $vector_store_ids,
                'tools' => $tools,
                'tool_resources' => $tool_resources
            )),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 20,
        ));

        //error_log('api_response: ' . print_r($api_response, true));

        if (is_wp_error($api_response)) {
            $api_error_msg = $api_response->get_error_message(); 
            wp_send_json_error('Failed to complete the api call with error: ' . $api_error_msg);
            return;
        }

        if (wp_remote_retrieve_response_code($api_response) != 200) {
            wp_send_json_error('Failed to create the agent.');
            return;
        } else {
            $api_response_body = json_decode(wp_remote_retrieve_body($api_response), true);
            $api_msg = $api_response_body['message'];
            $assistant_id = $api_response_body['assistant_id'];

            // Save file info including file_id and vector_id to table_article
            $agent_id = $this->save_agent($name, $instructions, $model, $vector_store_ids, $assistant_id);

            wp_send_json_success('The agent has been created with api call message: ' . $api_msg);
            return;
        }

    }
    

    private function save_agent($name, $instructions, $model, $vector_store_ids, $assistant_id) {
        global $wpdb;
        
        $result = $wpdb->insert($this->table_agent, array(
            'assistant_id' => $assistant_id,
            'name' => $name,
            'instructions' => $instructions,
            'model' => $model,
            'vector_store_ids' => json_encode($vector_store_ids),
            'created_time' => current_time('mysql'),
        ));

        if ($result === false) {
            error_log('Database insert error: ' . $wpdb->last_error);
            return false;
        }

        return $wpdb->insert_id;
    }


    private function get_tools_object($article_ids, $function_ids) {
        global $wpdb;
        $tools = [];
        $tool_resources = [];
        $vector_store_ids = [];

        $db_handler = new DBHandler();

        // Add file search tool
        foreach ($article_ids as $article_id) {
            $vector_store_id = $db_handler->get_vector_store_id_by_article_id($article_id);
            if ($vector_store_id) {
                $vector_store_ids[] = $vector_store_id;
            }
        }

        if (!empty($vector_store_ids)) {
            $tools[] = array('type' => 'file_search');
            $tool_resources = [
                'file_search' => [
                    'vector_store_ids' => $vector_store_ids
                ]
            ];
        }

        // Add function tools
        foreach ($function_ids as $function_id) {
            $function_data = $db_handler->get_function_by_id($function_id);

            if ($function_data) {
                $tools[] = array(
                    'type' => 'function',
                    'function' => json_decode($function_data->definition, true)
                );
            }
        }

        return [
            'tools' => $tools,
            'tool_resources' => $tool_resources
        ];
    }

}
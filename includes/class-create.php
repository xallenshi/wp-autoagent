<?php
namespace WPAutoAgent\Core;

use WPAutoAgent\Core\API;

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

        if (!isset($_POST['agent_name'])) {
            wp_send_json_error('No agent name provided.');
            return;
        }

        $agent_name = $_POST['agent_name'];
        $agent_instruction = $_POST['agent_instruction'];
        $model = $_POST['model'];
        $selected_files = $_POST['files'] ?? [];
        $selected_functions = $_POST['functions'] ?? [];
        $vector_id = 'vs_67d2bcd739f48191ae35186082ca779d';

        // make a rest api call to Lambda function to process the article
        $api_url = 'https://pbe3crai7j4vy6eoo35pss3pzm0xcpxb.lambda-url.ap-southeast-2.on.aws/';
        $api_response = wp_remote_post($api_url, array(
            'method' => 'POST',
            'body' => json_encode(array('agent_name' => $agent_name, 'agent_instruction' => $agent_instruction, 'model' => $model, 'vector_id' => $vector_id)),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 20,
        ));

        error_log('api_url: ' . $api_url);
        error_log('api_response: ' . print_r($api_response, true));

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
            $agent_id = $this->save_agent($agent_name, $agent_instruction, $model, $assistant_id);

            wp_send_json_success('The agent has been created with api call message: ' . $api_msg);
            return;
        }

    }
    

    private function save_agent($agent_name, $agent_instruction, $model, $assistant_id) {
        global $wpdb;

        $result = $wpdb->insert($this->table_agent, array(
            'agent_id' => $assistant_id,
            'agent_name' => $agent_name,
            'agent_instruction' => $agent_instruction,
            'model' => $model,
            'created_time' => current_time('mysql'),
        ));

        if ($result === false) {
            error_log('Database insert error: ' . $wpdb->last_error);
            return false;
        }

        return $wpdb->insert_id;
    }


}
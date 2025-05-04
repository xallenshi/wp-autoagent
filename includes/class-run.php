<?php
namespace WPAutoAgent\Core;

use WPAutoAgent\Core\API;

class Run {
    private $table_conversation;

    public function __construct() {
        $this->table_conversation = Config::get_table_name('conversation');
        add_action('wp_ajax_wpaa_run_agent', array($this, 'wpaa_run_agent'));
        add_action('wp_ajax_nopriv_wpaa_run_agent', array($this, 'wpaa_run_agent'));
    }

    public function wpaa_run_agent() {

        if (!check_ajax_referer('wpaa_request', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }

        if (!isset($_POST['agent_id'])) {
            wp_send_json_error('No agent id provided.');
            return;
        }

        $agent_id = $_POST['agent_id'];
        $content = isset($_POST['content']) ? sanitize_text_field($_POST['content']) : '';

        $db_handler = new DBHandler();
        $agent = $db_handler->get_agent($agent_id);
        $agent_id = $agent->id;
        $model = $agent->model;
        $instructions = $agent->instructions;
        $tools = json_decode($agent->tools, true);

        #keep conversation state
        $session_id = wp_get_session_token();
        $response_id = $db_handler->get_latest_response_id($agent_id, $session_id);
        #add system level instructions
        $input[] = array('role' => 'system', 'content' => $instructions);
        #add user question
        $input[] = array('role' => 'user', 'content' => $content);

        // make a rest api call to Lambda function to run the agent
        $api_url = 'https://jebcqgsrc7k5wffddjuof6feke0edirw.lambda-url.ap-southeast-2.on.aws/';
        $api_response = wp_remote_post($api_url, array(
            'method' => 'POST',
            'body' => json_encode(array('model' => $model, 'input' => $input, 'tools' => $tools, 'response_id' => $response_id)),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 60,
        ));

        //error_log('api_response: ' . print_r($api_response, true));
        if (is_wp_error($api_response)) {
            $api_error_msg = $api_response->get_error_message(); 
            wp_send_json_error('Failed to complete the api call with error: ' . $api_error_msg);
            return;
        }

        if (wp_remote_retrieve_response_code($api_response) != 200) {
            wp_send_json_error('Failed to run the agent.');
            return;
        } else {
            $api_response_body = json_decode(wp_remote_retrieve_body($api_response), true);
            $response_id = $api_response_body['response_id'];
            $api_msg = $api_response_body['message'];

            //error_log('api_response_body: ' . print_r($api_response_body, true));

            // Save file info including file_id and vector_id to table_article
            $conversation_id = $this->save_conversation($agent_id, $response_id, $content, $api_msg);

            wp_send_json_success($api_msg);
            return;
        }

    }

    private function save_conversation($agent_id, $response_id, $content, $api_msg) {
        global $wpdb;
        
        #get non-logged-in/logged-in user session id
        $session_id = wp_get_session_token();
        $user_id = is_user_logged_in() ? get_current_user_id() : null;

        $result = $wpdb->insert($this->table_conversation, array(
            'agent_id' => $agent_id,
            'session_id' => $session_id,
            'user_id' => $user_id,
            'response_id' => $response_id,
            'content' => $content,
            'response' => $api_msg,
            'created_time' => gmdate('Y-m-d H:i:s'),
        ));

        if ($result === false) {
            error_log('Database insert error: ' . $wpdb->last_error);
            return false;
        }

        return $wpdb->insert_id;
    }


}
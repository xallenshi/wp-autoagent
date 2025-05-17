<?php
namespace WPAutoAgent\Core;

class Run {
    private $table_conversation;

    public function __construct() {
        $this->table_conversation = Config::get_table_name('conversation');
        add_action('wp_ajax_wpaa_run_agent', array($this, 'wpaa_run_agent'));
        add_action('wp_ajax_nopriv_wpaa_run_agent', array($this, 'wpaa_run_agent'));

        add_action('wp_ajax_wpaa_get_chat_history', array($this, 'wpaa_get_chat_history'));
        add_action('wp_ajax_nopriv_wpaa_get_chat_history', array($this, 'wpaa_get_chat_history'));

        add_action('wp_ajax_wpaa_save_conversation', array($this, 'wpaa_save_conversation'));
        add_action('wp_ajax_nopriv_wpaa_save_conversation', array($this, 'wpaa_save_conversation'));

        add_action('wp_ajax_wpaa_run_the_agent', array($this, 'wpaa_run_the_agent'));
        add_action('wp_ajax_nopriv_wpaa_run_the_agent', array($this, 'wpaa_run_the_agent'));
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

        $db_handler = new DBHandler();
        $access_key = $db_handler->get_access_key();
        if(!$access_key) {
            wp_send_json_error('Invalid access key.');
            return;
        }

        $agent_id = $_POST['agent_id'];
        $content = isset($_POST['content']) ? sanitize_text_field($_POST['content']) : '';

        $db_handler = new DBHandler();
        $agent = $db_handler->get_agent_by_id($agent_id);
        $agent_id = $agent->id;
        $model = $agent->model;
        $instructions = $agent->instructions;
        error_log('agent->tools: ' . $agent->tools);

        $tools = json_decode($agent->tools, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('JSON DECODE ERROR: ' . json_last_error_msg());
        }
        error_log('tools: ' . print_r($tools, true));

        #keep conversation state
        $session_id = $this->wpaa_get_session_id();
        $response_id = $db_handler->get_latest_response_id($agent_id, $session_id);
        #add system level instructions
        #$input[] = array('role' => 'system', 'content' => $instructions);
        #add user question
        #$input[] = array('role' => 'user', 'content' => $content);

        // make a rest api call to Lambda function to run the agent
        $api_url = 'https://jebcqgsrc7k5wffddjuof6feke0edirw.lambda-url.ap-southeast-2.on.aws/';
        $api_response = wp_remote_post($api_url, array(
            'method' => 'POST',
            #'body' => json_encode(array('model' => $model, 'input' => $input, 'tools' => $tools, 'response_id' => $response_id)),
            'body' => json_encode(array('model' => $model, 'instructions' => $instructions, 'content' => $content, 'tools' => $tools, 'response_id' => $response_id)),
            'headers' => array(
                'Content-Type' => 'application/json',
                'x-access-key' => $access_key,
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
            wp_send_json_error('Failed to run agent.');
            return;
        } else {
            $api_response_body = json_decode(wp_remote_retrieve_body($api_response), true);
            $response_id = $api_response_body['response_id'] ?? null;
            $api_msg = $api_response_body['message'];
            $source = $api_response_body['source'];
            $score = $api_response_body['score'] ?? 0;

            //error_log('api_response_body: ' . print_r($api_response_body, true));

            // Save file info including file_id and vector_id to table_article
            $conversation_id = $this->save_conversation($agent_id, $response_id, $content, $api_msg, $source, $score);

            wp_send_json_success($api_msg . ' --- ' . $source . ' [' . $score . ']');
            return;
        }

    }



    public function wpaa_run_the_agent($request_id, $object1, $object2) {

        if (!$request_id) {
            return false;
        }

        if($request_id == 1) {
            #$object1 = $object1;
            $object1 = 'https://images.pexels.com/photos/355465/pexels-photo-355465.jpeg';

        } else if($request_id == 2) {
            $object1 = $object1;
            $object2 = $object2;
        }

        // make a rest api call to Lambda function to run the agent
        $api_url = 'https://h6ixjqz5kecqsrhm6hrakx3te40zknge.lambda-url.ap-southeast-2.on.aws/';
        $api_response = wp_remote_post($api_url, array(
            'method' => 'POST',
            'body' => json_encode(array('request_id' => $request_id, 'object1' => $object1, 'object2' => $object2)),
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
            error_log('Failed to run the agent.');
            return false;
        } else {
            $api_response_body = json_decode(wp_remote_retrieve_body($api_response), true);
            $api_msg = $api_response_body['message'];

            return $api_msg;
        }

    }


    public function wpaa_run_the_agent2() {

        if (!check_ajax_referer('wpaa_request', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }

        if (!isset($_POST['request_id'])) {
            wp_send_json_error('No request_id provided.');
            return;
        }

        $request_id = $_POST['request_id'];
        $object1 = $_POST['object1'] ?? null;
        $object2 = $_POST['object2'] ?? null;

        if($request_id == 1) {
            $current_theme = wp_get_theme();
            $object1 = $current_theme->get_screenshot();
            $object1 = 'https://ts.w.org/wp-content/themes/e-storefront/screenshot.png?ver=1.3?ver=1.3';

        } else if($request_id == 2) {
            $object1 = $object1;
            $object2 = $object2;
        }


        // make a rest api call to Lambda function to run the agent
        $api_url = 'https://h6ixjqz5kecqsrhm6hrakx3te40zknge.lambda-url.ap-southeast-2.on.aws/';
        $api_response = wp_remote_post($api_url, array(
            'method' => 'POST',
            'body' => json_encode(array('request_id' => $request_id, 'object1' => $object1, 'object2' => $object2)),
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
            $api_msg = $api_response_body['message'];

            wp_send_json_success($api_msg);
            return;
        }

    }


    private function save_conversation($agent_id, $response_id, $content, $api_msg, $source, $score) {
        global $wpdb;
        
        #get non-logged-in/logged-in user session id
        $session_id = $this->wpaa_get_session_id();
        if($session_id) {
            $user_id = is_user_logged_in() ? get_current_user_id() : null;
            $result = $wpdb->insert($this->table_conversation, array(
                'agent_id' => $agent_id,
                'session_id' => $session_id,
                'user_id' => $user_id,
                'response_id' => $response_id,
                'content' => $content,
                'response' => $api_msg,
                'source' => $source,
                'score' => $score,
                'created_time' => gmdate('Y-m-d H:i:s')
            ));
    
            if ($result === false) {
                error_log('Database insert error: ' . $wpdb->last_error);
                return false;
            }
    
            return $wpdb->insert_id;

        } else {
            return null;
        }
    }


    public function wpaa_save_conversation() {
        global $wpdb;

        $agent_id = $_POST['agent_id'];
        $response_id = $_POST['response_id'] ?? null;
        $content = $_POST['content'] ?? null;
        $api_msg = $_POST['api_msg'] ?? null;
        
        #get non-logged-in/logged-in user session id
        $session_id = $this->wpaa_get_session_id();
        if($agent_id && $session_id) {
            $user_id = is_user_logged_in() ? get_current_user_id() : null;
            $result = $wpdb->insert($this->table_conversation, array(
                'agent_id' => $agent_id,
                'session_id' => $session_id,
                'user_id' => $user_id,
                'response_id' => $response_id,
                'content' => $content,
                'response' => $api_msg,
                'source' => 'system',
                'score' => 0,
                'created_time' => gmdate('Y-m-d H:i:s'),
            ));
    
            if ($result === false) {
                error_log('Database insert error: ' . $wpdb->last_error);
                return false;
            }
    
            return $wpdb->insert_id;

        } else {
            return null;
        }
    }

    public function wpaa_get_chat_history() {
        if (!check_ajax_referer('wpaa_request', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }

        $agent_id = intval($_POST['agent_id']);
        $session_id = $this->wpaa_get_session_id();

        $db_handler = new DBHandler();
        $results = $db_handler->get_chat_history($agent_id, $session_id);

        wp_send_json_success($results);
    }


    public function wpaa_get_session_id() {
        static $session_id = null;
        # 1. Handle logged-in users
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $token = wp_get_session_token();
            $session_id = 'user_' . ($token ?: wp_generate_uuid4());
            return $session_id;
        }

        # 2. Guest users - cookie-based
        # Multisite-compatible
        $cookie_name = 'gid_' . COOKIEHASH; 
        
        if (isset($_COOKIE[$cookie_name])) {
            $session_id = 'guest_' . sanitize_key($_COOKIE[$cookie_name]);
            return $session_id;
        }

        # New guest - generate ID
        $guest_id = wp_generate_uuid4();
        
        # Set cookie (30 days, HTTP-only, Secure)
        $params = [
            'expires'  => time() + 30 * DAY_IN_SECONDS,
            'path'     => COOKIEPATH,
            'domain'   => COOKIE_DOMAIN,
            'secure'   => is_ssl(),
            'httponly' => true,
            'samesite' => 'Lax'
        ];
        
        if (PHP_VERSION_ID >= 70300) {
            setcookie($cookie_name, $guest_id, $params);
        } else {
            # PHP < 7.3 fallback
            setcookie(
                $cookie_name,
                $guest_id,
                $params['expires'],
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        $_COOKIE[$cookie_name] = $guest_id;
        $session_id = 'guest_' . $guest_id;
        
        return $session_id;
    }



}
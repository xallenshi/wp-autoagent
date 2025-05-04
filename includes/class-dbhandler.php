<?php
namespace WPAutoAgent\Core;

class DBHandler {

    private $wpdb;
    private $table_article;
    private $table_agent;
    private $table_function;
    private $table_conversation;
    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_article = Config::get_table_name('article');
        $this->table_agent = Config::get_table_name('agent');
        $this->table_function = Config::get_table_name('function');
        $this->table_conversation = Config::get_table_name('conversation');
    }

    public function get_articles() {
        $query = "SELECT id, file_name,created_time FROM {$this->table_article} ORDER BY created_time DESC";
        return $this->wpdb->get_results($query);
    }

    public function get_article_by_id($id) {
        $query = "SELECT * FROM {$this->table_article} WHERE id = %d";
        return $this->wpdb->get_row($this->wpdb->prepare($query, $id));
    }

    public function get_vector_store_id_by_article_id($id) {
        $query = "SELECT vector_store_id FROM {$this->table_article} WHERE id = %d";
        return $this->wpdb->get_var($this->wpdb->prepare($query, $id));
    }

    public function get_functions() {
        $query = "SELECT id, name, description FROM {$this->table_function}";
        return $this->wpdb->get_results($query);
    }

    public function get_function_by_id($id) {
        $query = "SELECT id, name, description, definition FROM {$this->table_function} WHERE id = %d";
        return $this->wpdb->get_row($this->wpdb->prepare($query, $id));
    }

    public function get_agents() {
        $query = "SELECT id, name, scope FROM {$this->table_agent} ORDER BY id DESC";
        return $this->wpdb->get_results($query);
    }

    public function get_agent($id) {    
        $query = "SELECT * FROM {$this->table_agent} WHERE id = %d";
        return $this->wpdb->get_row($this->wpdb->prepare($query, $id));
    }


    public function get_latest_response_id($agent_id, $session_id) {
        $query = "SELECT response_id, created_time FROM {$this->table_conversation} WHERE agent_id = %d AND session_id = %s ORDER BY created_time DESC LIMIT 1";
        $result = $this->wpdb->get_row($this->wpdb->prepare($query, $agent_id, $session_id));

        #reset conversation state if it's older than 5 minutes
        if ($result) {
            $conversation_pause = time() - strtotime($result->created_time);
            //error_log('conversation_pause: ' . $conversation_pause);

            if ($conversation_pause >= 300 || $result->response_id == null) {
                return null;
            } else {
                return $result->response_id;
            }
        }
        
        return null;
    }

    public function get_chat_history($agent_id, $session_id) {
        $query = "SELECT content, response, created_time FROM {$this->table_conversation} WHERE agent_id = %d AND session_id = %s ORDER BY created_time ASC";
        return $this->wpdb->get_results($this->wpdb->prepare($query, $agent_id, $session_id));
    }

}
<?php
namespace WPAutoAgent\Core;

class DBHandler {

    private $wpdb;
    private $table_article;
    private $table_agent;
    private $table_function;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_article = Config::get_table_name('article');
        $this->table_agent = Config::get_table_name('agent');
        $this->table_function = Config::get_table_name('function');
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
        $query = "SELECT id, name, scope FROM {$this->table_agent}";
        return $this->wpdb->get_results($query);
    }
    

}
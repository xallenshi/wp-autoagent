<?php
namespace WPAutoAgent\Core;

class DBHandler {
    private $wpdb;
    private $table_article;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_article = Config::get_table_name('article');
    }

    public function get_articles() {
        $query = "SELECT id, file_name,created_time FROM {$this->table_article} ORDER BY created_time DESC";
        return $this->wpdb->get_results($query);
    }

    public function get_article_by_id($id) {
        $query = "SELECT * FROM {$this->table_article} WHERE id = %d";
        return $this->wpdb->get_row($this->wpdb->prepare($query, $id));
    }
    

}
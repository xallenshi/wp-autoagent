<?php
namespace WPAutoAgent\Core;

class DBHandler {
    private $wpdb;
    private $table_article;
    private $table_article_chunk;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_article = Config::get_table_name('article');
        $this->table_article_chunk = Config::get_table_name('article_chunk');
    }

    public function get_articles() {
        $query = "SELECT article_id, file_name,created_time FROM {$this->table_article} ORDER BY created_time ASC";
        return $this->wpdb->get_results($query);
    }

    public function get_article_chunks($article_id) {
        $query = $this->wpdb->prepare("SELECT chunk_id, chunk_content, token_count FROM {$this->table_article_chunk} WHERE article_id = %d ORDER BY chunk_id", $article_id);
        return $this->wpdb->get_results($query);
    }
}
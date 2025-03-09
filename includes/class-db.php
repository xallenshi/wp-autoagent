<?php
namespace WPAutoAgent\Core;

class DB {
    private $table_article;
    private $table_article_chunk;

    public function __construct() {
        $this->table_article = Config::get_table_name('article');
        $this->table_article_chunk = Config::get_table_name('article_chunk');
        error_log("wp_autoagent_DB class instantiated.");
    }

    public function network_activate($network_wide) {
        if (is_multisite() && $network_wide) {
            error_log("Activating on multisite network");
            $sites = get_sites();

            foreach ($sites as $site) {
                switch_to_blog($site->blog_id);
                $this->create_tables();
                restore_current_blog();
            }
        } else {
            error_log("Activating on single site");
            $this->create_tables();
        }
    }

    public function create_tables() {
        error_log("create_tables method called.");

        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Check if the article table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_article}'") != $this->table_article) {
            $sql = "CREATE TABLE {$this->table_article} (
                article_id int UNSIGNED NOT NULL AUTO_INCREMENT,
                file_type varchar(255) NOT NULL,
                file_name varchar(255) NOT NULL,
                file_size int UNSIGNED NOT NULL,
                created_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (article_id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            // Log the result for debugging
            if (empty($wpdb->last_error)) {
                error_log("Table {$this->table_article} was created successfully.");
            } else {
                error_log("Error creating table {$this->table_article}: " . $wpdb->last_error);
            }
        } else {
            error_log("Table {$this->table_article} already exists.");
        }

        // Check if the article chunk table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_article_chunk}'") != $this->table_article_chunk) {
            $sql = "CREATE TABLE {$this->table_article_chunk} (
                chunk_id int UNSIGNED NOT NULL AUTO_INCREMENT,
                article_id int UNSIGNED NOT NULL,
                chunk_content text NOT NULL,
                token_count int UNSIGNED NOT NULL,
                PRIMARY KEY (chunk_id),
                FOREIGN KEY (article_id) REFERENCES {$this->table_article}(article_id) ON DELETE CASCADE
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            // Log the result for debugging
            if (empty($wpdb->last_error)) {
                error_log("Table {$this->table_article_chunk} was created successfully.");
            } else {
                error_log("Error creating table {$this->table_article_chunk}: " . $wpdb->last_error);
            }
        } else {
            error_log("Table {$this->table_article_chunk} already exists.");
        }
    }
}

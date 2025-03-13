<?php
namespace WPAutoAgent\Core;

class DB {
    private $table_article;
    private $table_article_chunk;

    public function __construct() {
        $this->table_article = Config::get_table_name('article');
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
                id int UNSIGNED NOT NULL AUTO_INCREMENT,
                file_type varchar(255) NOT NULL,
                file_name varchar(255) NOT NULL,
                file_size int UNSIGNED NOT NULL,
                file_id varchar(255) NOT NULL,
                vector_id varchar(255) NOT NULL,
                created_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (id)
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

    }
}

<?php
namespace WPAutoAgent\Core;

class DB {
    private $table_article;
    private $table_agent;
    private $table_conversation;
    private $table_function;

    public function __construct() {
        $this->table_article = Config::get_table_name('article');
        $this->table_agent = Config::get_table_name('agent');
        $this->table_conversation = Config::get_table_name('conversation');
        $this->table_function = Config::get_table_name('function');
        error_log("wp_autoagent_DB class instantiated.");
    }

    public function network_activate($network_wide) {
        if (is_multisite() && $network_wide) {
            $sites = get_sites();
            foreach ($sites as $site) {
                switch_to_blog($site->blog_id);
                $this->create_tables();
                restore_current_blog();
            }
            error_log("WP Auto Agent: Activated on multisite network");
        } else {
            $this->create_tables();
            error_log("WP Auto Agent: Activated on single site");
        }
    }

    public function create_tables() {
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
                vector_store_id varchar(255) NOT NULL,
                created_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY (id)
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

        // Check if the agent table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_agent}'") != $this->table_agent) {
            $sql = "CREATE TABLE {$this->table_agent} (
                id int UNSIGNED NOT NULL AUTO_INCREMENT,
                assistant_id varchar(255) NOT NULL,
                name varchar(255) NOT NULL,
                instructions varchar(255) NOT NULL,
                model varchar(255) NOT NULL,
                tools varchar(255) NOT NULL,
                vector_store_ids varchar(255) NOT NULL,
                scope text NOT NULL,
                created_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            // Log the result for debugging
            if (empty($wpdb->last_error)) {
                error_log("Table {$this->table_agent} was created successfully.");
            } else {
                error_log("Error creating table {$this->table_agent}: " . $wpdb->last_error);
            }
        } else {
            error_log("Table {$this->table_agent} already exists.");
        }

        // Check if the conversation table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_conversation}'") != $this->table_conversation) {
            $sql = "CREATE TABLE {$this->table_conversation} (
                id int UNSIGNED NOT NULL AUTO_INCREMENT,
                assistant_id varchar(255) NOT NULL,
                thread_id varchar(255) NOT NULL,
                content varchar(255) NOT NULL,
                response varchar(255) NOT NULL,
                created_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            // Log the result for debugging
            if (empty($wpdb->last_error)) {
                error_log("Table {$this->table_conversation} was created successfully.");
            } else {
                error_log("Error creating table {$this->table_conversation}: " . $wpdb->last_error);
            }
        } else {
            error_log("Table {$this->table_conversation} already exists.");
        }

        // Check if the functions table exists
        if ($wpdb->get_var("SHOW TABLES LIKE '{$this->table_function}'") != $this->table_function) {
            $sql = "CREATE TABLE {$this->table_function} (
                id int UNSIGNED NOT NULL AUTO_INCREMENT,
                name varchar(255) NOT NULL,
                description varchar(255) NOT NULL,
                definition text NOT NULL,
                created_time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY (id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            // Log the result for debugging
            if (empty($wpdb->last_error)) {
                error_log("Table {$this->table_function} was created successfully.");
            } else {
                error_log("Error creating table {$this->table_function}: " . $wpdb->last_error);
            }
        } else {
            error_log("Table {$this->table_function} already exists.");
        }


    }
}

<?php
namespace WPAutoAgent\Core;

class Config {
    private static $table_suffix = 'wpautoagent_';

    public static function get_table_name($table_alias) {
        global $wpdb;
        return $wpdb->prefix . self::$table_suffix . $table_alias;
    }

    public static function get($key) {
        $config = [
            'secret_key' => 'your_provided_secret_key_here', // Replace with the actual secret key
            // ... other config items ...
        ];

        return isset($config[$key]) ? $config[$key] : null;
    }

    // You could add more methods here for other configuration needs
}

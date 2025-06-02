<?php
namespace WPAgent\Core;

class Config {
    private static $table_suffix = 'wpagent_';

    public static function get_table_name($table_alias) {
        global $wpdb;
        return $wpdb->prefix . self::$table_suffix . $table_alias;
    }

    // You could add more methods here for other configuration needs
}

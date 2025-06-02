<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Include the Config class file
require_once plugin_dir_path(__FILE__) . 'includes/class-config.php';

// Use the Config class method to get the table names
$table_article = WPAgent\Core\Config::get_table_name('article');
$table_agent = WPAgent\Core\Config::get_table_name('agent');
$table_conversation = WPAgent\Core\Config::get_table_name('conversation');
$table_function = WPAgent\Core\Config::get_table_name('function');
$table_global = WPAgent\Core\Config::get_table_name('global');

// Remove custom database tables
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$table_article}");
$wpdb->query("DROP TABLE IF EXISTS {$table_agent}");
$wpdb->query("DROP TABLE IF EXISTS {$table_conversation}");
$wpdb->query("DROP TABLE IF EXISTS {$table_function}");
$wpdb->query("DROP TABLE IF EXISTS {$table_global}");



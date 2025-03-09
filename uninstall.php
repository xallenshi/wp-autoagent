<?php

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Include the Config class file
require_once plugin_dir_path(__FILE__) . 'includes/class-config.php';

// Use the Config class method to get the table name
$table_article_chunk = WPAutoAgent\Core\Config::get_table_name('article_chunk');
$table_article = WPAutoAgent\Core\Config::get_table_name('article');
// Remove custom database table
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS {$table_article_chunk}");
$wpdb->query("DROP TABLE IF EXISTS {$table_article}");

// Delete options if any
//delete_option('pinecone_api_key');

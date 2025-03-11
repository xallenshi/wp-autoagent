<?php
namespace WPAutoAgent\Core;

use PhpOffice\PhpWord\IOFactory as WordIOFactory;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetIOFactory;
use WPAutoAgent\Core\API;

class Upload {
    private $table_article;
    private $table_article_chunk;

    public function __construct() {
        $this->table_article = Config::get_table_name('article');
        $this->table_article_chunk = Config::get_table_name('article_chunk');
        add_action('wp_ajax_smart_chatbot_settings_upload', array($this, 'smart_chatbot_settings_upload'));
    }

    public function smart_chatbot_settings_upload() {
        if (!check_ajax_referer('smart_chatbot_settings', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }

        if (!isset($_FILES['article_file'])) {
            wp_send_json_error('No file uploaded.');
            return;
        }

        $file = $_FILES['article_file'];

        // Save header info to article table and get article_id
        $article_id = $this->save_article($file);

        if (!$article_id) {
            wp_send_json_error('Failed to save article header info.');
            return;
        }

        

        wp_send_json_success('Article uploaded and processed successfully.');
    }

    private function save_article($file) {
        global $wpdb;

        $result = $wpdb->insert($this->table_article, array(
            'file_type' => $file['type'],
            'file_name' => $file['name'],
            'file_size' => $file['size'],
            'created_time' => current_time('mysql'),
        ));

        if ($result === false) {
            error_log('Database insert error: ' . $wpdb->last_error);
            return false;
        }

        return $wpdb->insert_id;
    }


}
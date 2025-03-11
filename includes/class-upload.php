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
        add_action('wp_ajax_wpaa_article_upload', array($this, 'wpaa_article_upload'));
    }

    public function wpaa_article_upload() {
        if (!check_ajax_referer('wpaa_setting', 'nonce', false)) {
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
            wp_send_json_error('Failed to upload the article.');
            return;
        }

        wp_send_json_success('The article has been uploaded and processed.');
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
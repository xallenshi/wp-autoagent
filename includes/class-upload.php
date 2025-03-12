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


        // Read the file content
        $file_content = '';
        if ($file_handle = fopen($file['tmp_name'], 'rb')) {
            $file_content = fread($file_handle, filesize($file['tmp_name']));
            fclose($file_handle);
        }

        // Encode the file content in base64
        $file_content_base64 = base64_encode($file_content);

        // make a rest api call to Lambda function to process the article
        $api_url = 'https://keyhwsna4nmjf3giq2i4oyq4z40frwjs.lambda-url.ap-southeast-2.on.aws/';
        $api_response = wp_remote_post($api_url, array(
            'method' => 'POST',
            'body' => json_encode(array('file_content' => $file_content_base64)),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 20,
        ));

        error_log('api_response: ' . print_r($api_response, true));

        if (is_wp_error($api_response)) {
            wp_send_json_error('Failed to complete the api call.');
            return;
        }

        if (wp_remote_retrieve_response_code($api_response) != 200) {
            wp_send_json_error('Failed to embed the article.');
            return;
        } else {
            $api_response_body = json_decode(wp_remote_retrieve_body($api_response), true);
            $api_msg = $api_response_body['message'];
            wp_send_json_success('The article has been uploaded and processed with the following api message: ' . $api_msg);
        }

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
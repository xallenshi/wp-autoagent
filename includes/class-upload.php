<?php
namespace WPAutoAgent\Core;

use WPAutoAgent\Core\API;

class Upload {
    private $table_article;

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
        $file_name = $file['name'];

        // Read the file content
        $file_content = '';
        if ($file_handle = fopen($file['tmp_name'], 'rb')) {
            $file_content = fread($file_handle, filesize($file['tmp_name']));
            fclose($file_handle);
        }

        // Encode the file content in base64
        $file_content_base64 = base64_encode($file_content);

        // make a rest api call to Lambda function to process the article
        $api_url = 'https://tdbarn5h3ctyhzqjkgmmztkac40olzcm.lambda-url.ap-southeast-2.on.aws/';
        $api_response = wp_remote_post($api_url, array(
            'method' => 'POST',
            'body' => json_encode(array('file_content' => $file_content_base64, 'file_name' => $file_name)),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 20,
        ));

        //error_log('api_response: ' . print_r($api_response, true));

        if (is_wp_error($api_response)) {
            $api_error_msg = $api_response->get_error_message(); 
            wp_send_json_error('Failed to complete the api call with error: ' . $api_error_msg);
            return;
        }

        if (wp_remote_retrieve_response_code($api_response) != 200) {
            wp_send_json_error('Failed to embed the article.');
            return;
        } else {
            $api_response_body = json_decode(wp_remote_retrieve_body($api_response), true);
            $api_msg = $api_response_body['message'];
            $file_id = $api_response_body['file_id'];
            $vector_store_id = $api_response_body['vector_store_id'];

            // Save file info including file_id and vector_id to table_article
            $article_id = $this->save_article($file, $file_id, $vector_store_id);

            wp_send_json_success('The article has been uploaded and processed with api call message: ' . $api_msg);
            return;
        }

    }
    

    private function save_article($file, $file_id, $vector_store_id) {
        global $wpdb;

        $result = $wpdb->insert($this->table_article, array(
            'file_type' => $file['type'],
            'file_name' => $file['name'],
            'file_size' => $file['size'],
            'file_id' => $file_id,
            'vector_store_id' => $vector_store_id,
            'created_time' => current_time('mysql'),
        ));

        if ($result === false) {
            error_log('Database insert error: ' . $wpdb->last_error);
            return false;
        }

        return $wpdb->insert_id;
    }


}
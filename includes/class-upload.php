<?php
namespace WPAgent\Core;

class Upload {
    private $table_article;

    public function __construct() {
        $this->table_article = Config::get_table_name('article');
        add_action('wp_ajax_wpa_article_upload', array($this, 'wpa_article_upload'));
        add_action('wp_ajax_wpa_get_article_list', array($this, 'wpa_get_article_list'));
    }

    public function wpa_article_upload() {
        if (!check_ajax_referer('wpa_setting', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }

        if (!isset($_FILES['article_file'])) {
            wp_send_json_error('No file uploaded.');
            return;
        }

        $db_handler = new DBHandler();
        $access_key = $db_handler->get_access_key();
        if(!$access_key) {
            wp_send_json_error('Invalid access key.');
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
                'x-access-key' => $access_key,
            ),
            'timeout' => 60,
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
    
    public function wpa_get_article_list() {
        if (!check_ajax_referer('wpa_setting', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }
        //require_once __DIR__ . '/class-dbhandler.php';
        $db_handler = new DBHandler();
        $articles = $db_handler->get_articles();
        ob_start();
        if ($articles && count($articles) > 0) {
            foreach ($articles as $article) {
                echo "<div class='wpa_article_item'>{$article->file_name} at {$article->created_time}</div>";
            }
        } else {
            echo "<div>No articles found.</div>";
        }
        $html = ob_get_clean();
        wp_send_json_success($html);
    }

    private function save_article($file, $file_id, $vector_store_id) {
        global $wpdb;

        $result = $wpdb->insert($this->table_article, array(
            'file_type' => $file['type'],
            'file_name' => $file['name'],
            'file_size' => $file['size'],
            'file_id' => $file_id,
            'vector_store_id' => $vector_store_id,
            'created_time' => gmdate('Y-m-d H:i:s'),
        ));

        if ($result === false) {
            error_log('Database insert error: ' . $wpdb->last_error);
            return false;
        }

        return $wpdb->insert_id;
    }


}
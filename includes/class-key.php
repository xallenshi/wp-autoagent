<?php
namespace WPAutoAgent\Core;

class Key {
    private $table_global;

    public function __construct() {
        $this->table_global = Config::get_table_name('global');
        add_action('wp_ajax_wpaa_save_key', array($this, 'wpaa_save_key'));
        
    }
    
    public function wpaa_save_key() {
        if (!check_ajax_referer('wpaa_setting', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }

        if (!isset($_POST['access_key'])) {
            wp_send_json_error('No access key provided.');
            return;
        }

        $access_key = sanitize_text_field($_POST['access_key']);    

        $db_handler = new DBHandler();
        $db_handler->update_global_setting(array('access_key' => $access_key));
        wp_send_json_success('The access key has been saved.');
    }
}
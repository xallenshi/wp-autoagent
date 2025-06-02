<?php
namespace WPAgent\Core;

class Key {
    private $table_global;

    public function __construct() {
        $this->table_global = Config::get_table_name('global');
        add_action('wp_ajax_wpa_save_key', array($this, 'wpa_save_key'));
        
    }
    
    public function wpa_save_key() {
        if (!check_ajax_referer('wpa_setting', 'nonce', false)) {
            wp_send_json_error('Invalid nonce.');
            return;
        }

        if (!isset($_POST['access_key'])) {
            wp_send_json_error('No access key provided.');
            return;
        }

        $access_key = sanitize_text_field($_POST['access_key']);  

        $db_handler = new DBHandler();
        $global_setting = $db_handler->get_global_setting();
        if ($global_setting) {
            $global_setting->access_key = $access_key;
            $db_handler->update_global_setting($global_setting);
        } else {
            $db_handler->insert_global_setting(['access_key' => $access_key]);
        }
        wp_send_json_success('The access key has been saved.');
    }
}
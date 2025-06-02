<?php
namespace WPAgent\Core;

class Frontend {
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts() {
        wp_enqueue_style('wpa-css', WP_AGENT_PLUGIN_URL . 'assets/css/wpa.css', array(), WP_AGENT_VERSION);
        wp_enqueue_style('wpa-chat-css', WP_AGENT_PLUGIN_URL . 'assets/css/wpa-chat.css', array(), WP_AGENT_VERSION);
        
        wp_enqueue_script('wpa-js', WP_AGENT_PLUGIN_URL . 'assets/js/wpa.js', array('jquery'), WP_AGENT_VERSION, true);
        wp_enqueue_script('wpa-chat-js', WP_AGENT_PLUGIN_URL . 'assets/js/wpa-chat.js', array('jquery'), WP_AGENT_VERSION, true);
        wp_enqueue_script('wpa-function-js', WP_AGENT_PLUGIN_URL . 'assets/js/wpa-function.js', array('jquery'), WP_AGENT_VERSION, true);
        wp_enqueue_script('wpa-example-js', WP_AGENT_PLUGIN_URL . 'assets/js/wpa-example.js', array('jquery'), WP_AGENT_VERSION, true);
        
        wp_localize_script('wpa-js', 'wpa_setting_nonce', array(
            'nonce' => wp_create_nonce('wpa_setting')
        ));
        wp_localize_script('wpa-chat-js', 'wpa_request_nonce', array(
            'nonce' => wp_create_nonce('wpa_request'),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'session_id' => wp_get_session_token()
        ));
    }


}

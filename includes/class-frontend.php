<?php
namespace WPAutoAgent\Core;

class Frontend {
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts() {
        wp_enqueue_style('wpaa-css', WP_AUTOAGENT_PLUGIN_URL . 'assets/css/wpaa.css', array(), WP_AUTOAGENT_VERSION);
        wp_enqueue_style('wpaa-chat-css', WP_AUTOAGENT_PLUGIN_URL . 'assets/css/wpaa-chat.css', array(), WP_AUTOAGENT_VERSION);
        
        wp_enqueue_script('wpaa-js', WP_AUTOAGENT_PLUGIN_URL . 'assets/js/wpaa.js', array('jquery'), WP_AUTOAGENT_VERSION, true);
        wp_enqueue_script('wpaa-chat-js', WP_AUTOAGENT_PLUGIN_URL . 'assets/js/wpaa-chat.js', array('jquery'), WP_AUTOAGENT_VERSION, true);
        wp_enqueue_script('wpaa-function-js', WP_AUTOAGENT_PLUGIN_URL . 'assets/js/wpaa-function.js', array('jquery'), WP_AUTOAGENT_VERSION, true);
        
        wp_localize_script('wpaa-js', 'wpaa_setting_nonce', array(
            'nonce' => wp_create_nonce('wpaa_setting')
        ));
        wp_localize_script('wpaa-chat-js', 'wpaa_request_nonce', array(
            'nonce' => wp_create_nonce('wpaa_request'),
            'ajaxurl' => admin_url('admin-ajax.php'),
            'session_id' => wp_get_session_token()
        ));
    }


}

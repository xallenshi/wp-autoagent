<?php
namespace WPAutoAgent\Core;

class Frontend {
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function enqueue_scripts() {
        wp_enqueue_style('wp-autoagent-css', WP_AUTOAGENT_PLUGIN_URL . 'assets/css/wp-autoagent.css', array(), WP_AUTOAGENT_VERSION);
        wp_enqueue_script('wp-autoagent-js', WP_AUTOAGENT_PLUGIN_URL . 'assets/js/wp-autoagent.js', array('jquery'), WP_AUTOAGENT_VERSION, true);
        
        wp_localize_script('wp-autoagent-js', 'wpaa_nonce', array(
            'nonce' => wp_create_nonce('wpaa_setting')
        ));
    }



}

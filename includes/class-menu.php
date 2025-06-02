<?php
namespace WPAutoAgent\Core;

class Menu {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        // Main menu under WP AutoAgent
        add_menu_page(__('WP Agent', 'wp-autoagent'), __('WP AutoAgent', 'wp-autoagent'), 'manage_options', 'wp-autoagent', array($this, 'render_home_page'), 'dashicons-format-chat', 6);
        add_submenu_page('wp-autoagent', __('Home', 'wp-autoagent'), __('Home', 'wp-autoagent'), 'manage_options', 'wp-autoagent', array($this, 'render_home_page'));
        add_submenu_page('wp-autoagent', __('Review', 'wp-autoagent'), __('Review', 'wp-autoagent'), 'manage_options', 'wp-autoagent-review', array($this, 'render_review_page'));
        add_submenu_page('wp-autoagent', __('Settings', 'wp-autoagent'), __('Settings', 'wp-autoagent'), 'manage_options', 'wp-autoagent-settings', array($this, 'render_settings_page'));
    }

    public function render_home_page() {
        include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/home.php');
    }

    public function render_review_page() {
        include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/review.php');
    }

    public function render_settings_page() {
        include(WP_AUTOAGENT_PLUGIN_DIR . 'pages/settings.php');
    }

}


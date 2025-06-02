<?php
namespace WPAgent\Core;

class Menu {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }

    public function add_admin_menu() {
        // Main menu under WP Agent
        add_menu_page(__('WP Agent', 'wp-agent'), __('WP Agent', 'wp-agent'), 'manage_options', 'wp-agent', array($this, 'render_home_page'), 'dashicons-format-chat', 6);
        add_submenu_page('wp-agent', __('Home', 'wp-agent'), __('Home', 'wp-agent'), 'manage_options', 'wp-agent', array($this, 'render_home_page'));
        add_submenu_page('wp-agent', __('Review', 'wp-agent'), __('Review', 'wp-agent'), 'manage_options', 'wp-agent-review', array($this, 'render_review_page'));
        add_submenu_page('wp-agent', __('Settings', 'wp-agent'), __('Settings', 'wp-agent'), 'manage_options', 'wp-agent-settings', array($this, 'render_settings_page'));
    }

    public function render_home_page() {
        include(WP_AGENT_PLUGIN_DIR . 'pages/home.php');
    }

    public function render_review_page() {
        include(WP_AGENT_PLUGIN_DIR . 'pages/review.php');
    }

    public function render_settings_page() {
        include(WP_AGENT_PLUGIN_DIR . 'pages/settings.php');
    }

}


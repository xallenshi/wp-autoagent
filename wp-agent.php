<?php
/**
 * Plugin Name: WP Agent
 * Plugin URI: https://xsolution.com
 * Description: An AI-driven agent for WordPress that assists with customer inquiries and requests.
 * Version: 1.0.0
 * Author: XSolution
 * Author URI: https://xsolution.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-agent
 * Domain Path: /languages
 */

namespace WPAgent\Core;

if (!defined('ABSPATH')) {
    exit;
}

// Include the configuration file
require_once plugin_dir_path(__FILE__) . 'config.php';

// Define constants
define('WP_AGENT_VERSION', '1.0.0');
define('WP_AGENT_PLUGIN_FILE', __FILE__);
define('WP_AGENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_AGENT_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Class Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'WPAgent\\Core\\';
    $base_dir = WP_AGENT_PLUGIN_DIR . 'includes/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . 'class-' . strtolower($relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// Load text domain for internationalization
function load_textdomain() {
    load_plugin_textdomain('wp-agent', false, WP_AGENT_PLUGIN_DIR . 'languages/');
}
add_action('plugins_loaded', __NAMESPACE__ . '\\load_textdomain');


// Activate the plugin
function wp_agent_activate($network_wide) {
    $db = new DB();
    $db->network_activate($network_wide);
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\wp_agent_activate');


// Initialize the plugin
function wp_agent_init() {
    $wp_agent = new WPAgent();
    $wp_agent->init_plugin();
}
add_action('plugins_loaded', __NAMESPACE__ . '\\wp_agent_init');



// Function to create privacy policy page
function wp_agent_create_privacy_policy_page() {
    $page_title = 'WP Agent Privacy Policy';
    $page_slug = 'wpa-privacy-policy';
    $page_content = '[wpa_privacy_policy]';

    // Check if the page already exists
    $page = get_page_by_path($page_slug);
    if (!$page) {
        // Create post object
        $page_id = wp_insert_post([
            'post_title'   => $page_title,
            'post_name'    => $page_slug,
            'post_content' => $page_content,
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
        if ($page_id && !is_wp_error($page_id)) {
            update_option('wpa_privacy_policy_page_id', $page_id);
        }
    } else {
        update_option('wpa_privacy_policy_page_id', $page->ID);
    }
    error_log('Privacy Policy page created: ' . $page_id);
}

// Function to handle privacy policy shortcode
function wp_agent_privacy_policy_shortcode() {
    $file_path = WP_AGENT_PLUGIN_DIR . 'assets/html/wpa-privacy-policy.html';
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        return '<div class="wpa-privacy-policy">' . $content . '</div>';
    } else {
        error_log('Privacy Policy file not found: ' . $file_path);
        return '<div class="wpa-privacy-policy">Privacy Policy file not found.</div>';
    }
}

// Register activation hook and shortcode
register_activation_hook(__FILE__, __NAMESPACE__ . '\\wp_agent_create_privacy_policy_page');
add_shortcode('wpa_privacy_policy', __NAMESPACE__ . '\\wp_agent_privacy_policy_shortcode');

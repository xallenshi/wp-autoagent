<?php
/**
 * Plugin Name: WP AutoAgent
 * Plugin URI: https://xsolution.com
 * Description: An AI-driven agent for WordPress that assists with customer inquiries and requests.
 * Version: 1.0.0
 * Author: XSolution
 * Author URI: https://xsolution.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: wp-autoagent
 * Domain Path: /languages
 */

namespace WPAutoAgent\Core;

if (!defined('ABSPATH')) {
    exit;
}

// Include the configuration file
require_once plugin_dir_path(__FILE__) . 'config.php';

// Define constants
define('WP_AUTOAGENT_VERSION', '1.0.0');
define('WP_AUTOAGENT_PLUGIN_FILE', __FILE__);
define('WP_AUTOAGENT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_AUTOAGENT_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Class Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'WPAutoAgent\\Core\\';
    $base_dir = WP_AUTOAGENT_PLUGIN_DIR . 'includes/';
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
    load_plugin_textdomain('wp-autoagent', false, WP_AUTOAGENT_PLUGIN_DIR . 'languages/');
}
add_action('plugins_loaded', __NAMESPACE__ . '\\load_textdomain');


// Activate the plugin
function wp_autoagent_activate($network_wide) {
    $db = new DB();
    $db->network_activate($network_wide);
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\wp_autoagent_activate');


// Initialize the plugin
function wp_autoagent_init() {
    $wp_autoagent = new WPAutoAgent();
    $wp_autoagent->init_plugin();
}
add_action('plugins_loaded', __NAMESPACE__ . '\\wp_autoagent_init');





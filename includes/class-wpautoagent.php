<?php
namespace WPAutoAgent\Core;

class WPAutoAgent {
    private $menu;
    private $settings;
    private $upload;
    private $frontend;
    private $chat;
    private $publish;
    private $key;
    private $create;
    private $run;
    
    public function __construct() {
        // Constructor is now empty
    }

    public function init_plugin() {
        $this->init_components();
        $this->create_privacy_policy_page();
    }

    private function init_components() {
        // Check if required classes exist before instantiating
        if (!class_exists('WPAutoAgent\Core\Menu') ||
            !class_exists('WPAutoAgent\Core\Frontend') ||
            !class_exists('WPAutoAgent\Core\Upload') ||
            !class_exists('WPAutoAgent\Core\Create') ||
            !class_exists('WPAutoAgent\Core\Run') ||
            !class_exists('WPAutoAgent\Core\Chat') ||
            !class_exists('WPAutoAgent\Core\Publish') ||
            !class_exists('WPAutoAgent\Core\Key')) {
            throw new \Exception('Required classes not found');
        }

        $this->menu = new Menu();
        $this->frontend = new Frontend();
        $this->upload = new Upload();
        $this->create = new Create();
        $this->run = new Run();
        $this->chat = new Chat();
        $this->publish = new Publish();
        $this->key = new Key();
    }

    private function create_privacy_policy_page() {
        // 1. Register activation hook to create the page
        register_activation_hook(__FILE__, function() {
            $page_title = 'WP Agent Privacy Policy';
            $page_slug = 'wpaa-privacy-policy';
            $page_content = '[wpaa_privacy_policy]'; // Use a shortcode for dynamic content
    
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
                    update_option('wpaa_privacy_policy_page_id', $page_id);
                }
            } else {
                update_option('wpaa_privacy_policy_page_id', $page->ID);
            }
        });
    
        // 2. Add the shortcode to display file content
        add_shortcode('wpaa_privacy_policy', function() {
            $file_path = plugin_dir_path(__FILE__) . 'wpaa-privacy-policy.html'; // Adjust path as needed
            if (file_exists($file_path)) {
                $content = file_get_contents($file_path);
                return '<div class="wpaa-privacy-policy">' . $content . '</div>';
            } else {
                return '<div class="wpaa-privacy-policy">Privacy Policy file not found.</div>';
            }
        });
    }

    
}

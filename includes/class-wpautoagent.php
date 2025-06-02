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

    
}

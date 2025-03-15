<?php
namespace WPAutoAgent\Core;

class WPAutoAgent {
    private $menu;
    private $settings;
    private $upload;
    private $api;
    private $frontend;

    public function __construct() {
        // Constructor is now empty
    }

    public function init_plugin() {
        $this->init_components();
    }

    private function init_components() {
        $this->menu = new Menu();
        $this->frontend = new Frontend();
        $this->upload = new Upload();
        $this->create = new Create();
        $this->run = new Run();
        $this->api = new API();
    }

}

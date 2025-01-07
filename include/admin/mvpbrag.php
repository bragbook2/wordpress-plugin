<?php

namespace mvpbrag;

class mvpbrag {

    public function __construct() {
        
        add_action('admin_enqueue_scripts', [ $this, 'bragbook_enqueue_scripts' ]);
        add_action('wp_enqueue_scripts', [ $this, 'bragbook_enqueue_scripts' ]);

        register_activation_hook(__FILE__, ['mvpbrag\Activator', 'activate' ]);
        register_deactivation_hook(__FILE__, [ 'mvpbrag\Deactivator', 'deactivate' ]);

        add_action('init', [ $this, 'init' ]);
        
        add_filter('template_include', [ 'mvpbrag\Activator', 'include_template' ]);
        // Instantiate the Ajax_Handler class
        new \mvpbrag\Ajax_Handler();
    }

    // Hook to add CSS and JS
    public function bragbook_enqueue_scripts() {
        \mvpbrag\Activator::bb_enqueue_scripts();
    }

    public function init() {
        \mvpbrag\Shortcode::register();
    }
}
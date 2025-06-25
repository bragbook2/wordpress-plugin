<?php
/*
Plugin Name: BRAG Book Gallery
Plugin URI: https://github.com/bragbook2/wordpress-plugin/releases/latest
Description: Installs necessary components to display your BRAG book before and after gallery.
Version: 2.5.2
Author: Candace Crowe Design
Author URI: https://www.bragbookgallery.com/
License: A "Slug" license name e.g. GPL2
*/

namespace mvpbrag;

if (!defined('BB_PLUGIN_DIR_PATH')) {
    define('BB_PLUGIN_DIR_PATH', plugin_dir_url(__FILE__));
}
if (!defined('BB_PLUGIN_DIR_MAIN_PATH')) {
    define('BB_PLUGIN_DIR_MAIN_PATH', plugin_dir_path(__FILE__));
}
if (!defined('HOUR_IN_SECONDS')) {
    define('HOUR_IN_SECONDS', 3600);
}
if (!defined('BB_BASE_URL')) {
    define('BB_BASE_URL', 'https://app.bragbookgallery.com');
}

require_once BB_PLUGIN_DIR_MAIN_PATH . 'include/class-activator.php';
require_once BB_PLUGIN_DIR_MAIN_PATH . 'include/class-shortcode.php';
require_once BB_PLUGIN_DIR_MAIN_PATH . 'include/class-bb-api.php';
require_once BB_PLUGIN_DIR_MAIN_PATH . 'include/class-sitemap.php';
require_once BB_PLUGIN_DIR_MAIN_PATH . 'include/class-consultation.php';
require_once BB_PLUGIN_DIR_MAIN_PATH . 'include/class-seo.php';
require_once BB_PLUGIN_DIR_MAIN_PATH . 'include/class-api-settings.php';
$theme_directory = get_template();
$header_path = get_stylesheet_directory() . '/header.php';
class mvpbrag
{

    public function __construct()
    {
        add_action('init', [$this, 'bragbook_enqueue_jquery']);
        add_action('admin_enqueue_scripts', [$this, 'bragbook_enqueue_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'bragbook_enqueue_scripts']);

        add_action('init', [$this, 'init']);
        add_filter('template_include', ['mvpbrag\Activator', 'include_template']);
        new \mvpbrag\Ajax_Handler();
        new \mvpbrag\Sitemap();
        new \mvpbrag\Seo();
        new \mvpbrag\Consultation();
    }

    public function bragbook_enqueue_scripts()
    {
        \mvpbrag\Activator::bb_enqueue_scripts();
    }
    public function bragbook_enqueue_jquery()
    {
        \mvpbrag\Activator::bb_enqueue_jquery();
    }

    public function init()
    {
        \mvpbrag\Shortcode::register();
        \mvpbrag\Shortcode::custom_rewrite_rules();
        \mvpbrag\Shortcode::custom_rewrite_flush();

    }

    /**
     * Update rewrite rules based on the page slug when the page is saved.
     */
    public function update_rewrite_rules_on_save($post_id)
    {
        if (get_post_type($post_id) !== 'page' || wp_is_post_revision($post_id)) {
            return;
        }
        $post = get_post($post_id);
        $page_slug = $post->post_name;
        update_option('bragbook_page_slug', $page_slug);
    }

}

// Initialize the plugin
new \mvpbrag\mvpbrag();

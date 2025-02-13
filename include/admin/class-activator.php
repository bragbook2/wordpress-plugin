<?php
namespace mvpbrag;

class Activator {
    public static function bb_enqueue_jquery() {
		wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.7.1.min.js', [], '3.7.1', false);
	}
    public static function bb_enqueue_scripts() {
        // Enqueue CSS file
        // Get the current timestamp
        $version = time();
		if (!wp_script_is('jquery', 'enqueued')) {
            wp_enqueue_script('jquery');
        }

        wp_enqueue_style(
            'bragbook-style',
            BB_PLUGIN_DIR_PATH . 'assets/css/style.css',
            array(), 
            $version  
        );
        // Enqueue CDN link
        wp_enqueue_style('bragbook-cdn-style', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
        wp_enqueue_script('bragbook-cdn-script', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array(), null, true);
		// Enqueue JS file
        wp_enqueue_script('bragbook-script', BB_PLUGIN_DIR_PATH . 'assets/js/script.js', array('jquery'), $version, true);
        wp_enqueue_script('jquery-ui-accordion');
        wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');
    
        // Localize script to pass PHP data to JavaScript
        wp_localize_script('bragbook-script', 'bb_plugin_data', array(
            'leftArrow' => BB_PLUGIN_DIR_PATH . 'assets/images/red-angle-left.svg',
            'rightArrow' => BB_PLUGIN_DIR_PATH . 'assets/images/red-angle-right.svg',
            'leftArrowUrl' => BB_PLUGIN_DIR_PATH . 'assets/images/caret-left.svg',
            'rightArrowUrl' => BB_PLUGIN_DIR_PATH . 'assets/images/caret-right.svg',
            'heartBordered' => BB_PLUGIN_DIR_PATH . 'assets/images/red-heart-outline.svg',
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
		
    }
    public static function include_template($template) { 
        $page_slug = get_post_field('post_name', get_post());
        $gallery_slugs = get_option('bb_gallery_page_slug', []);
        $combine_gallery_slug = get_option('combine_gallery_slug');
        
        foreach($gallery_slugs as $slug_value) {
            if (($slug_value == $page_slug) || (isset($combine_gallery_slug) && $combine_gallery_slug == $page_slug)) {
                $new_template = BB_PLUGIN_DIR_MAIN_PATH . 'template/bb-brag.php';
                if (file_exists($new_template)) {
                    return $new_template;
                }
            }
        }
        return $template;
    }
}
?>
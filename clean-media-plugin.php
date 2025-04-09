<?php
/*
Plugin Name: Clean Unused Images
Description: Deletes unused images from WordPress media library
Version: 2.0
Author: Humphrey Ikhalea
*/

class CleanUnusedImages {
    // ========================
    // Core Class Configuration
    // ========================
    private $per_page = 20;
    private $current_page = 1;
    private $total_items = 0;
    private $search_term = '';

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_clean_images_delete', [$this, 'ajax_delete_images']);
    }

    // =====================
    // Admin Interface Setup
    // =====================
    public function add_admin_menu() {
        add_media_page(
            'Clean Unused Images',
            'Clean Unused Images',
            'manage_options',
            'clean-unused-images',
            [$this, 'admin_page']
        );
    }

    public function enqueue_scripts($hook) {
        if ($hook === 'media_page_clean-unused-images') {
            wp_enqueue_script('clean-images-js', plugins_url('clean-images.js', __FILE__), ['jquery'], '2.0', true);
            wp_localize_script('clean-images-js', 'cleanImagesVars', [
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('clean_images_ajax_nonce')
            ]);
            wp_enqueue_style('clean-images-css', plugins_url('clean-images.css', __FILE__), [], '2.0');
        }
    }

    // ======================
    // Core Functionality
    // ======================
    private function get_unused_images() {
        // [Previous query logic here]
    }

    private function is_image_used($image_url) {
        // [Previous content check logic here]
    }

    // ======================
    // Admin Page Rendering
    // ======================
    public function admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized access.');
        }

        echo '<div class="wrap">';
        echo '<h1>Clean Unused Images</h1>';
        $this->display_image_list();
        echo '</div>';
    }

    private function display_image_list() {
        // [Previous table rendering logic here]
    }

    private function pagination() {
        // [Previous pagination logic here]
    }

    // ======================
    // AJAX Handling
    // ======================
    public function ajax_delete_images() {
        check_ajax_referer('clean_images_ajax_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Unauthorized', 403);
        }

        // [Previous deletion logic here]
    }
}

new CleanUnusedImages();
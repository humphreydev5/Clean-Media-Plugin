<?php
/*
Plugin Name: Clean Unused Images
Description: Deletes unused images from the WordPress media library
Version: 1.0
Author: Humphrey Ikhalea
*/

class CleanUnusedImages {
    public function __construct() {
        if (function_exists('add_action')) {
            add_action('admin_menu', array($this, 'add_admin_menu'));
        }
    }

    public function add_admin_menu() {
        add_media_page(
            'Clean Unused Images',
            'Clean Unused Images',
            'manage_options',
            'clean-unused-images',
            array($this, 'admin_page')
        );
    }

    public function admin_page() {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have sufficient permissions to access this page.');
        }

        echo '<div class="wrap">';
        echo '<h1>Clean Unused Images</h1>';

        if (isset($_POST['delete_images']) && check_admin_referer('clean_images_nonce')) {
            $this->handle_image_deletion();
        }

        $this->display_image_list();
        echo '</div>';
    }

    private function get_unused_images() {
        $unused = array();
        
        // Get all unattached images
        $attachments = get_posts(array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'post_status' => 'inherit',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'post_parent' => 0
        ));

        foreach ($attachments as $attachment_id) {
            $image_url = wp_get_attachment_url($attachment_id);
            
            // Check if image is used in any post content
            if (!$this->is_image_used($image_url)) {
                $unused[] = array(
                    'id' => $attachment_id,
                    'url' => $image_url,
                    'title' => get_the_title($attachment_id)
                );
            }
        }

        return $unused;
    }

    private function is_image_used($image_url) {
        global $wpdb;
        
        // Search posts content for image URL
        $query = $wpdb->prepare(
            "SELECT ID FROM $wpdb->posts 
            WHERE post_content LIKE %s 
            AND post_type IN ('post', 'page') 
            AND post_status = 'publish'",
            '%' . $wpdb->esc_like($image_url) . '%'
        );
        
        $results = $wpdb->get_results($query);
        return !empty($results);
    }

    private function display_image_list() {
        $images = $this->get_unused_images();

        if (empty($images)) {
            echo '<div class="notice notice-success"><p>No unused images found!</p></div>';
            return;
        }

        echo '<form method="post">';
        wp_nonce_field('clean_images_nonce');
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>
                <th class="manage-column column-cb check-column"><input type="checkbox" id="select-all"></th>
                <th>Image</th>
                <th>Title</th>
                <th>URL</th>
            </tr></thead>';

        foreach ($images as $image) {
            echo '<tr>';
            echo '<td><input type="checkbox" name="image_ids[]" value="' . esc_attr($image['id']) . '"></td>';
            echo '<td>' . wp_get_attachment_image($image['id'], 'thumbnail') . '</td>';
            echo '<td>' . esc_html($image['title']) . '</td>';
            echo '<td><a href="' . esc_url($image['url']) . '" target="_blank">' . esc_url($image['url']) . '</a></td>';
            echo '</tr>';
        }

        echo '</table>';
        echo '<p class="submit"><input type="submit" name="delete_images" class="button button-primary" value="Delete Selected Images"></p>';
        echo '</form>';

        // Select all script
        echo '<script>
            jQuery(document).ready(function($) {
                $("#select-all").click(function() {
                    $("input[type=\'checkbox\']").prop("checked", this.checked);
                });
            });
        </script>';
    }

    private function handle_image_deletion() {
        if (empty($_POST['image_ids'])) {
            echo '<div class="notice notice-error"><p>No images selected for deletion.</p></div>';
            return;
        }

        $deleted = 0;
        foreach ($_POST['image_ids'] as $image_id) {
            if (wp_delete_attachment($image_id, true)) {
                $deleted++;
            }
        }

        echo '<div class="notice notice-success"><p>Deleted ' . $deleted . ' images successfully.</p></div>';
    }
}

new CleanUnusedImages();
class UnusedImagesTest extends WP_UnitTestCase {
    public function test_unused_image_detection() {
        // Create test attachment
        $attachment_id = self::factory()->attachment->create();
        
        // Verify detection
        $plugin = new CleanUnusedImages();
        $unused = $plugin->get_unused_images();
        
        $this->assertContains($attachment_id, wp_list_pluck($unused, 'id'));
    }
}
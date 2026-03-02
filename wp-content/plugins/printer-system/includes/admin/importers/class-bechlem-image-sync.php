<?php
/**
 * Bechlem Image Sync Module
 * Handles downloading product images from Bechlem API to WooCommerce.
 */

class Bechlem_Image_Sync {

    // Changeable Settings
    private $api_base_url = PS_PICTURE_BASE_URL;
    private $batch_limit  = PS_PICTURE_SYNC_BATCH_LIMIT; // Number of images to process per hourly run
    private $image_format = PS_PICTURE_IMAGE_FORMAT;

    /**
     * Main Controller Function
     * Finds products missing images and processes them in a batch.
     */
    public function run_batch_sync() {
        
        // 1. Query WooCommerce for products without a Featured Image (_thumbnail_id)
        $args = [
            'post_type'      => 'product',
            'posts_per_page' => $this->batch_limit,
            'meta_query'     => [
                [
                    'key'     => '_thumbnail_id',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => '_bechlem_id', // Ensure the product has a Bechlem ID
                    'compare' => 'EXISTS',
                ]
            ],
        ];

        $products = get_posts($args);

        if (empty($products)) {
            error_log("Bechlem Sync: No products found missing images.");
            return;
        }

        foreach ($products as $product) {
            $bechlem_id = get_post_meta($product->ID, '_bechlem_id', true);
            
            if ($bechlem_id) {
                // Construct the modular URL
                $url = "{$this->api_base_url}?iditem={$bechlem_id}&format={$this->image_format}";
                
                // Call the sub-function to handle the download
                $this->sideload_image($product->ID, $url, $product->post_title);
            }
        }
    }

    /**
     * Modular Sub-Function: Sideloading
     * Downloads image to Media Library and attaches it to Product.
     */
    private function sideload_image($product_id, $url, $title) {
        // Required WordPress files for media handling
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        // Download and create attachment
        $attachment_id = media_sideload_image($url, $product_id, $title, 'id');

        if (!is_wp_error($attachment_id)) {
            // Link the new Attachment ID to the WooCommerce Product
            set_post_thumbnail($product_id, $attachment_id);
            
            // Optional: Log success for debugging in Ubuntu terminal
            error_log("Bechlem Sync: Attached image to Product ID {$product_id}");
        } else {
            error_log("Bechlem Sync Error: " . $attachment_id->get_error_message() . " for URL: " . $url);
        }
    }
}
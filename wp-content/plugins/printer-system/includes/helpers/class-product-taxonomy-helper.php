<?php
/**
 * Product Taxonomy Helper Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class PS_Product_Taxonomy_Helper {
    
    /**
     * Assign printer taxonomy terms to a product
     *
     * @param int $product_id Product ID
     * @param array $printer_term_ids Array of printer term IDs
     * @param bool $append Whether to append or replace terms
     * @return bool|WP_Error
     */
    public static function assign_printers_to_product($product_id, $printer_term_ids, $append = true) {
        if (empty($printer_term_ids)) {
            return false;
        }
        
        // Ensure all values are integers
        $printer_term_ids = array_map('intval', $printer_term_ids);
        
        $result = wp_set_object_terms(
            $product_id,
            $printer_term_ids,
            PS_TAXONOMY_NAME,
            $append
        );
        
        return !is_wp_error($result);
    }
    
    /**
     * Get printer terms assigned to a product
     *
     * @param int $product_id Product ID
     * @return array Array of term objects
     */
    public static function get_product_printers($product_id) {
        $terms = wp_get_object_terms(
            $product_id,
            PS_TAXONOMY_NAME,
            ['fields' => 'all']
        );
        
        return is_wp_error($terms) ? [] : $terms;
    }
    
    /**
     * Remove printer taxonomy terms from a product
     *
     * @param int $product_id Product ID
     * @param array $printer_term_ids Array of printer term IDs (empty to remove all)
     * @return bool
     */
    public static function remove_printers_from_product($product_id, $printer_term_ids = []) {
        if (empty($printer_term_ids)) {
            // Remove all printer terms
            $result = wp_set_object_terms($product_id, [], PS_TAXONOMY_NAME, false);
        } else {
            // Get current terms
            $current_terms = wp_get_object_terms($product_id, PS_TAXONOMY_NAME, ['fields' => 'ids']);
            
            if (is_wp_error($current_terms)) {
                return false;
            }
            
            // Remove specific terms
            $new_terms = array_diff($current_terms, $printer_term_ids);
            $result = wp_set_object_terms($product_id, $new_terms, PS_TAXONOMY_NAME, false);
        }
        
        return !is_wp_error($result);
    }
    
    /**
     * Sync product taxonomy terms based on mapping table
     *
     * @param int $product_id Product ID
     * @return int Number of printer terms assigned
     */
    public static function sync_product_taxonomy_from_mapping($product_id) {
        global $wpdb;
        
        $table_name = PS_Database::get_table_name();
        
        // Get all printer term IDs that should be assigned to this product
        $printer_term_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT printer_id FROM {$table_name} WHERE supply_id = %d",
                $product_id
            )
        );
        
        if (empty($printer_term_ids)) {
            return 0;
        }
        
        // Assign all printer terms to the product
        self::assign_printers_to_product($product_id, $printer_term_ids, false);
        
        return count($printer_term_ids);
    }
    
    /**
     * Get products by printer term
     *
     * @param int $printer_term_id Printer term ID
     * @return array Array of product IDs
     */
    public static function get_products_by_printer($printer_term_id) {
        $args = [
            'post_type' => PS_POST_TYPE,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => [
                [
                    'taxonomy' => PS_TAXONOMY_NAME,
                    'field' => 'term_id',
                    'terms' => $printer_term_id,
                ]
            ]
        ];
        
        $query = new WP_Query($args);
        return $query->posts;
    }
}
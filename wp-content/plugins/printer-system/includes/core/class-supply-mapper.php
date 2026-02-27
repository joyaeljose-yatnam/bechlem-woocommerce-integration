<?php
/**
 * Supply Mapper Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class PS_Supply_Mapper {
    
    /**
     * Get supplies by printer term ID
     *
     * @param int $printer_term_id Printer term ID
     * @return WP_Query|array Query object or empty array
     */
    public static function get_supplies_by_printer($printer_term_id) {
        $supply_ids = PS_Database::get_supply_ids_by_printer($printer_term_id);
        
        if (empty($supply_ids)) {
            return [];
        }
        
        return new WP_Query([
            'post_type' => PS_POST_TYPE,
            'post__in' => $supply_ids,
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_status' => 'publish'
        ]);
    }
    
    /**
     * Add supply to printer
     *
     * @param int $printer_term_id Printer term ID
     * @param int $supply_product_id Supply product ID
     * @return bool
     */
    public static function add_supply_to_printer($printer_term_id, $supply_product_id) {
        // Check if mapping already exists
        if (PS_Database::mapping_exists($printer_term_id, $supply_product_id)) {
            return false;
        }
        
        return (bool) PS_Database::insert_mapping($printer_term_id, $supply_product_id);
    }
    
    /**
     * Remove supply from printer
     *
     * @param int $printer_term_id Printer term ID
     * @param int $supply_product_id Supply product ID
     * @return bool
     */
    public static function remove_supply_from_printer($printer_term_id, $supply_product_id) {
        return (bool) PS_Database::delete_mapping($printer_term_id, $supply_product_id);
    }
    
    /**
     * Get printer count for supply
     *
     * @param int $supply_product_id Supply product ID
     * @return int
     */
    public static function get_printer_count_for_supply($supply_product_id) {
        global $wpdb;
        
        $table_name = PS_Database::get_table_name();
        
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE supply_id = %d",
                $supply_product_id
            )
        );
        
        return (int) $count;
    }
}
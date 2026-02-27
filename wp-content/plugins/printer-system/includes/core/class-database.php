<?php
/**
 * Database Handler Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class PS_Database {
    
    /**
     * Get full table name with prefix
     *
     * @return string
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . PS_TABLE_NAME;
    }
    
    /**
     * Create printer-supply mapping table
     *
     * @return bool
     */
    public static function create_mapping_table() {
        global $wpdb;
        
        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            printer_id BIGINT UNSIGNED NOT NULL,
            supply_id BIGINT UNSIGNED NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY unique_map (printer_id, supply_id),
            KEY printer_id (printer_id),
            KEY supply_id (supply_id)
        ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        return true;
    }
    
    /**
     * Insert printer-supply mapping
     *
     * @param int $printer_term_id Printer term ID
     * @param int $supply_product_id Supply product ID
     * @return int|false Insert ID or false on failure
     */
    public static function insert_mapping($printer_term_id, $supply_product_id) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        $result = $wpdb->insert(
            $table_name,
            [
                'printer_id' => $printer_term_id,
                'supply_id' => $supply_product_id
            ],
            ['%d', '%d']
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Get supply IDs by printer term ID
     *
     * @param int $printer_term_id Printer term ID
     * @return array Array of supply product IDs
     */
    public static function get_supply_ids_by_printer($printer_term_id) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT supply_id FROM {$table_name} WHERE printer_id = %d",
                $printer_term_id
            )
        );
        
        return $results ? $results : [];
    }
    
    /**
     * Delete mapping
     *
     * @param int $printer_term_id Printer term ID
     * @param int $supply_product_id Supply product ID (optional)
     * @return int|false Number of rows deleted or false on failure
     */
    public static function delete_mapping($printer_term_id, $supply_product_id = null) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        if ($supply_product_id) {
            return $wpdb->delete(
                $table_name,
                [
                    'printer_id' => $printer_term_id,
                    'supply_id' => $supply_product_id
                ],
                ['%d', '%d']
            );
        } else {
            return $wpdb->delete(
                $table_name,
                ['printer_id' => $printer_term_id],
                ['%d']
            );
        }
    }
    
    /**
     * Check if mapping exists
     *
     * @param int $printer_term_id Printer term ID
     * @param int $supply_product_id Supply product ID
     * @return bool
     */
    public static function mapping_exists($printer_term_id, $supply_product_id) {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        $result = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} WHERE printer_id = %d AND supply_id = %d",
                $printer_term_id,
                $supply_product_id
            )
        );
        
        return (bool) $result;
    }
    
    /**
     * Drop mapping table
     *
     * @return bool
     */
    public static function drop_mapping_table() {
        global $wpdb;
        
        $table_name = self::get_table_name();
        
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        
        return true;
    }
}
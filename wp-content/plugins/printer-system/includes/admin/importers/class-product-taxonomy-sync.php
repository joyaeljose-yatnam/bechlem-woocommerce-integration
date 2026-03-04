<?php
/**
 * Product Taxonomy Sync Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class PS_Product_Taxonomy_Sync {
    
    /**
     * Statistics
     *
     * @var array
     */
    private $stats = [
        'processed' => 0,
        'synced' => 0,
        'skipped' => 0,
        'total_assignments' => 0,
        'messages' => []
    ];
    
    /**
     * Sync all products with their printer taxonomy terms
     *
     * @param int $batch_size Number of products to process per batch
     * @return array Statistics
     */
    public function sync_all_products($batch_size = 100) {
        global $wpdb;
        
        $table_name = PS_Database::get_table_name();
        
        // Get all unique supply IDs from mapping table
        $supply_ids = $wpdb->get_col(
            "SELECT DISTINCT supply_id FROM {$table_name}"
        );
        
        if (empty($supply_ids)) {
            return $this->stats;
        }
        
        $total = count($supply_ids);
        $this->stats['processed'] = 0;
        
        foreach ($supply_ids as $supply_id) {
            $this->stats['processed']++;
            
            // Check if product exists
            if (get_post_type($supply_id) !== PS_POST_TYPE) {
                array_push($this->stats['messages'], 'Product not found or incorrect type');
                $this->stats['skipped']++;
                continue;
            }
            
            // Sync this product
            $assigned_count = PS_Product_Taxonomy_Helper::sync_product_taxonomy_from_mapping($supply_id);
            
            if ($assigned_count > 0) {
                $this->stats['synced']++;
                $this->stats['total_assignments'] += $assigned_count;
            } else {
                array_push($this->stats['messages'], 'No taxonomy terms assigned');
                $this->stats['skipped']++;
            }
        }
        
        return $this->stats;
    }
    
    /**
     * Sync a single product
     *
     * @param int $product_id Product ID
     * @return int Number of printer terms assigned
     */
    public function sync_single_product($product_id) {
        return PS_Product_Taxonomy_Helper::sync_product_taxonomy_from_mapping($product_id);
    }
    
    /**
     * Get statistics
     *
     * @return array
     */
    public function get_stats() {
        return $this->stats;
    }
    
    /**
     * Display sync results
     */
    public function display_results() {
        echo "<h3>Product Taxonomy Sync Complete!</h3>";
        echo "<p><strong>Processed:</strong> {$this->stats['processed']} products</p>";
        echo "<p><strong>Synced:</strong> {$this->stats['synced']} products</p>";
        echo "<p><strong>Skipped:</strong> {$this->stats['skipped']} products</p>";
        echo "<p><strong>Total Assignments:</strong> {$this->stats['total_assignments']} taxonomy terms assigned</p>";
    }
}
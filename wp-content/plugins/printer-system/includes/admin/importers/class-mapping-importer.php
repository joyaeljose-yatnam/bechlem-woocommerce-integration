<?php
/**
 * Mapping Importer Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class PS_Mapping_Importer extends PS_Base_Importer {
    
    /**
     * Track products that need taxonomy sync
     *
     * @var array
     */
    private $products_to_sync = [];
    
    /**
     * Auto-sync taxonomy
     *
     * @var bool
     */
    private $auto_sync_taxonomy = true;
    
    /**
     * Constructor
     *
     * @param bool $auto_sync_taxonomy Whether to auto-sync taxonomy during import
     */
    public function __construct($auto_sync_taxonomy = true) {
        parent::__construct(PS_MAPPING_CSV);
        $this->auto_sync_taxonomy = $auto_sync_taxonomy;
    }
    
    /**
     * Process a single mapping row
     *
     * CSV Format: [0] => Printer Bechlem ID, [1] => Supply Bechlem ID
     *
     * @param array $row CSV row data
     * @return bool Success status
     */
    protected function process_row($row) {
        $printer_bechlem_id = intval($row[0]);
        $supply_bechlem_id = intval($row[1]);
        
        // Validate data
        if (empty($printer_bechlem_id) || empty($supply_bechlem_id)) {
            return false;
        }
        
        // Find printer term
        $printer_term = PS_Term_Helper::get_term_by_meta(
            PS_TAXONOMY_NAME,
            PS_META_PRINTER_ID,
            $printer_bechlem_id
        );
        
        if (!$printer_term) {
            return false;
        }
        
        // Find supply product by old slug
        $supply_post_id = $this->find_supply_product($supply_bechlem_id);
        
        if (!$supply_post_id) {
            return false;
        }
        
        // Check if mapping already exists
        if (PS_Database::mapping_exists($printer_term->term_id, $supply_post_id)) {
            return false;
        }
        
        // Insert mapping
        $inserted = (bool) PS_Database::insert_mapping($printer_term->term_id, $supply_post_id);
        
        // Track product for taxonomy sync
        if ($inserted && $this->auto_sync_taxonomy) {
            $this->products_to_sync[$supply_post_id] = true;
        }
        
        return $inserted;
    }
    
    /**
     * Find supply product by Bechlem ID
     *
     * @param int $supply_bechlem_id Supply Bechlem ID
     * @return int|false Product ID or false
     */
    private function find_supply_product($supply_bechlem_id) {
        $placeholder = PS_IMPORT_PLACEHOLDER_PREFIX . $supply_bechlem_id;
        
        $products = get_posts([
            'post_type' => PS_POST_TYPE,
            'meta_key' => PS_META_OLD_SLUG,
            'meta_value' => $placeholder,
            'posts_per_page' => 1,
            'fields' => 'ids',
            'post_status' => 'any'
        ]);
        
        return !empty($products) ? $products[0] : false;
    }
    
    /**
     * Run the import
     *
     * @return bool
     */
    public function run() {
        $result = parent::run();
        
        // Sync taxonomy for affected products
        if ($result && $this->auto_sync_taxonomy && !empty($this->products_to_sync)) {
            $this->sync_product_taxonomies();
        }
        
        return $result;
    }
    
    /**
     * Sync product taxonomies
     */
    private function sync_product_taxonomies() {
        $synced = 0;
        
        foreach (array_keys($this->products_to_sync) as $product_id) {
            $count = PS_Product_Taxonomy_Helper::sync_product_taxonomy_from_mapping($product_id);
            if ($count > 0) {
                $synced++;
            }
        }
        
        if ($synced > 0) {
            echo "<p><em>Auto-synced taxonomy for {$synced} products.</em></p>";
        }
    }
    
    /**
     * Display import results
     */
    public function display_results() {
        parent::display_results();
        
        if ($this->auto_sync_taxonomy && !empty($this->products_to_sync)) {
            echo "<p><strong>Products synced with taxonomy:</strong> " . count($this->products_to_sync) . "</p>";
        }
    }
}
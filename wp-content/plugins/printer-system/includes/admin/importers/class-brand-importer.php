<?php
/**
 * Brand Importer Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class PS_Brand_Importer extends PS_Base_Importer {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(PS_BRAND_CSV);
    }
    
    /**
     * Process a single brand row
     *
     * CSV Format: [0] => Brand ID, [1] => Brand Name
     *
     * @param array $row CSV row data
     * @return bool Success status
     */
    protected function process_row($row) {
        $brand_id = intval($row[0]);
        $brand_name = sanitize_text_field($row[1]);
        
        // Validate data
        if (empty($brand_id) || empty($brand_name)) {
            return false;
        }
        
        // Check if brand already exists
        if (PS_Term_Helper::term_exists_by_meta(PS_TAXONOMY_NAME, PS_META_BRAND_ID, $brand_id)) {
            return false;
        }
        
        // Create brand term
        $term = PS_Term_Helper::create_term_with_meta(
            $brand_name,
            PS_TAXONOMY_NAME,
            [],
            [PS_META_BRAND_ID => $brand_id]
        );
        
        return !is_wp_error($term);
    }
}
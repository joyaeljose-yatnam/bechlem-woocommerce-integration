<?php
/**
 * Series Importer Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class PS_Series_Importer extends PS_Base_Importer {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(PS_SERIES_CSV);
    }
    
    /**
     * Process a single series row
     *
     * CSV Format: [0] => Series ID, [1] => Brand ID, [3] => Series Name
     *
     * @param array $row CSV row data
     * @return bool Success status
     */
    protected function process_row($row) {
        $series_id = intval($row[0]);
        $brand_id = intval($row[1]);
        $series_name = sanitize_text_field($row[3]);
        
        // Validate data
        if (empty($series_id) || empty($brand_id) || empty($series_name)) {
            return false;
        }
        
        // Find parent brand
        $parent_brand = PS_Term_Helper::get_term_by_meta(
            PS_TAXONOMY_NAME,
            PS_META_BRAND_ID,
            $brand_id
        );
        
        if (!$parent_brand) {
            return false;
        }
        
        // Check if series already exists
        if (PS_Term_Helper::term_exists_by_meta(PS_TAXONOMY_NAME, PS_META_SERIES_ID, $series_id)) {
            return false;
        }
        
        // Create series term
        $term = PS_Term_Helper::create_term_with_meta(
            $series_name,
            PS_TAXONOMY_NAME,
            ['parent' => $parent_brand->term_id],
            [
                PS_META_SERIES_ID => $series_id,
                PS_META_SERIES_NAME => $series_name
            ]
        );
        
        return !is_wp_error($term);
    }
}
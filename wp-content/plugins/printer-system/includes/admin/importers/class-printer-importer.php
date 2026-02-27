<?php
/**
 * Printer Importer Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class PS_Printer_Importer extends PS_Base_Importer {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(PS_PRINTER_CSV);
    }
    
    /**
     * Process a single printer row
     *
     * CSV Format: [0] => Printer ID, [3] => Image URL, [4] => Printer Name, [7] => Series Name
     *
     * @param array $row CSV row data
     * @return bool Success status
     */
    protected function process_row($row) {
        $printer_id = intval($row[0]);
        $printer_image = esc_url_raw($row[3]);
        $printer_name = sanitize_text_field($row[4]);
        $series_name = sanitize_text_field($row[7]);
        
        // Validate data
        if (empty($printer_id) || empty($printer_name) || empty($series_name)) {
            return false;
        }
        
        // Find parent series by series name
        $parent_series = PS_Term_Helper::get_term_by_meta(
            PS_TAXONOMY_NAME,
            PS_META_SERIES_NAME,
            $series_name
        );
        
        if (!$parent_series) {
            return false;
        }
        
        // Check if printer already exists
        if (PS_Term_Helper::term_exists_by_meta(PS_TAXONOMY_NAME, PS_META_PRINTER_ID, $printer_id)) {
            return false;
        }
        
        // Prepare meta data
        $meta_data = [
            PS_META_PRINTER_ID => $printer_id
        ];
        
        if (!empty($printer_image)) {
            $meta_data[PS_META_PRINTER_IMAGE] = $printer_image;
        }
        
        // Create printer term
        $term = PS_Term_Helper::create_term_with_meta(
            $printer_name,
            PS_TAXONOMY_NAME,
            ['parent' => $parent_series->term_id],
            $meta_data
        );
        
        return !is_wp_error($term);
    }
}
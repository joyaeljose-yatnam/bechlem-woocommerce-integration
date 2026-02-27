<?php
/**
 * Import Controller Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class PS_Import_Controller {
    
    /**
     * Initialize import controller
     */
    public static function init() {
        add_action('init', [__CLASS__, 'handle_import_requests']);
    }
    
    /**
     * Handle import requests via URL parameters
     */
    public static function handle_import_requests() {
        // Test import trigger
        if (isset($_GET[PS_QUERY_TEST_IMPORT])) {
            self::test_import_trigger();
        }
        
        // Check permission for other operations
        if (!current_user_can(PS_REQUIRED_CAPABILITY)) {
            return;
        }
        
        // Handle specific imports
        if (isset($_GET[PS_QUERY_IMPORT_BRANDS])) {
            self::import_brands();
        }
        
        if (isset($_GET[PS_QUERY_IMPORT_SERIES])) {
            self::import_series();
        }
        
        if (isset($_GET[PS_QUERY_IMPORT_PRINTERS])) {
            self::import_printers();
        }
        
        if (isset($_GET[PS_QUERY_IMPORT_MAPPING])) {
            self::import_mapping();
        }
        
        if (isset($_GET[PS_QUERY_SYNC_TAXONOMY])) {
            self::sync_product_taxonomy();
        }
    }
    
    /**
     * Test import trigger
     */
    private static function test_import_trigger() {
        wp_die('Import trigger is working! ✓');
    }
    
    /**
     * Import brands
     */
    private static function import_brands() {
        $importer = new PS_Brand_Importer();
        $importer->run();
        $importer->display_results();
        exit;
    }
    
    /**
     * Import series
     */
    private static function import_series() {
        $importer = new PS_Series_Importer();
        $importer->run();
        $importer->display_results();
        exit;
    }
    
    /**
     * Import printers
     */
    private static function import_printers() {
        $importer = new PS_Printer_Importer();
        $importer->run();
        $importer->display_results();
        exit;
    }
    
    /**
     * Import mapping
     */
    private static function import_mapping() {
        // Auto-sync is enabled by default via constant
        $importer = new PS_Mapping_Importer(PS_AUTO_SYNC_TAXONOMY);
        $importer->run();
        $importer->display_results();
        exit;
    }
    
    /**
     * Sync product taxonomy
     */
    private static function sync_product_taxonomy() {
        $syncer = new PS_Product_Taxonomy_Sync();
        $syncer->sync_all_products();
        $syncer->display_results();
        exit;
    }
}
<?php
/**
 * Plugin Constants Configuration
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

// Taxonomy Configuration
define('PS_TAXONOMY_NAME', 'printer_hierarchy');
define('PS_TAXONOMY_LABEL', 'Printers');
define('PS_TAXONOMY_SLUG', 'printer');
define('PS_POST_TYPE', 'product');

// Database Configuration
define('PS_TABLE_NAME', 'printer_supply_map');

// CSV Import Configuration
define('PS_IMPORT_DIR', WP_CONTENT_DIR . '/uploads/import');
define('PS_BRAND_CSV', PS_IMPORT_DIR . '/BRAND.CSV');
define('PS_SERIES_CSV', PS_IMPORT_DIR . '/PRINTERSERIES.CSV');
define('PS_PRINTER_CSV', PS_IMPORT_DIR . '/PRINTER.CSV');
define('PS_MAPPING_CSV', PS_IMPORT_DIR . '/PRINTER2SUPPLY.CSV');

// CSV Configuration
define('PS_CSV_DELIMITER', ';');
define('PS_CSV_ENCLOSURE', '"');

// Meta Keys
define('PS_META_BRAND_ID', 'brand_id');
define('PS_META_SERIES_ID', 'series_id');
define('PS_META_SERIES_NAME', 'series_name');
define('PS_META_PRINTER_ID', 'printer_id');
define('PS_META_PRINTER_IMAGE', 'printer_image');
define('PS_META_OLD_SLUG', '_wp_old_slug');

// Import Placeholder Pattern
define('PS_IMPORT_PLACEHOLDER_PREFIX', 'import-placeholder-for-');

// Permissions
define('PS_REQUIRED_CAPABILITY', 'manage_options');

// Query Parameters
define('PS_QUERY_TEST_IMPORT', 'test_import');
define('PS_QUERY_IMPORT_BRANDS', 'import_brands');
define('PS_QUERY_IMPORT_SERIES', 'import_series');
define('PS_QUERY_IMPORT_PRINTERS', 'import_printers');
define('PS_QUERY_IMPORT_MAPPING', 'import_mapping');
define('PS_QUERY_SYNC_TAXONOMY', 'sync_product_taxonomy');
define('PS_QUERY_DEBUG_TEMPLATE', 'debug_template');

// Auto-sync settings
define('PS_AUTO_SYNC_TAXONOMY', true); // Auto-sync taxonomy during mapping import
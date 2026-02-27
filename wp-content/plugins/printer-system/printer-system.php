<?php
/**
 * Plugin Name: Printer System Architecture
 * Description: Brand → Series → Printer hierarchy with supply mapping
 * Version: 2.0
 * Author: Your Name
 * Text Domain: printer-system
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PS_PLUGIN_FILE', __FILE__);
define('PS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PS_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('PS_VERSION', '2.0');

// Load configuration
require_once PS_PLUGIN_DIR . 'includes/config/constants.php';

// Load core classes
require_once PS_PLUGIN_DIR . 'includes/helpers/class-term-helper.php';
require_once PS_PLUGIN_DIR . 'includes/helpers/class-product-taxonomy-helper.php';
require_once PS_PLUGIN_DIR . 'includes/core/class-database.php';
require_once PS_PLUGIN_DIR . 'includes/core/class-taxonomy.php';
require_once PS_PLUGIN_DIR . 'includes/core/class-template-loader.php';
require_once PS_PLUGIN_DIR . 'includes/core/class-supply-mapper.php';
require_once PS_PLUGIN_DIR . 'includes/core/class-activator.php';

// Load admin classes
if (is_admin() || (defined('DOING_CRON') && DOING_CRON) || isset($_GET['test_import']) || isset($_GET['import_brands']) || isset($_GET['import_series']) || isset($_GET['import_printers']) || isset($_GET['import_mapping']) || isset($_GET['sync_product_taxonomy'])) {
    require_once PS_PLUGIN_DIR . 'includes/admin/importers/class-base-importer.php';
    require_once PS_PLUGIN_DIR . 'includes/admin/importers/class-brand-importer.php';
    require_once PS_PLUGIN_DIR . 'includes/admin/importers/class-series-importer.php';
    require_once PS_PLUGIN_DIR . 'includes/admin/importers/class-printer-importer.php';
    require_once PS_PLUGIN_DIR . 'includes/admin/importers/class-mapping-importer.php';
    require_once PS_PLUGIN_DIR . 'includes/admin/importers/class-product-taxonomy-sync.php';
    require_once PS_PLUGIN_DIR . 'includes/admin/class-import-controller.php';
}

/**
 * Initialize the plugin
 */
function ps_init_plugin() {
    // Initialize taxonomy
    PS_Taxonomy::init();
    
    // Initialize template loader
    PS_Template_Loader::init();
    
    // Initialize import controller
    if (is_admin() || isset($_GET['test_import']) || isset($_GET['import_brands']) || isset($_GET['import_series']) || isset($_GET['import_printers']) || isset($_GET['import_mapping']) || isset($_GET['sync_product_taxonomy'])) {
        PS_Import_Controller::init();
    }
}
add_action('plugins_loaded', 'ps_init_plugin');

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, ['PS_Activator', 'activate']);

/**
 * Plugin deactivation hook
 */
register_deactivation_hook(__FILE__, ['PS_Activator', 'deactivate']);


/**
 * Backward compatibility - Legacy function for template
 */
function ps_get_supplies_by_printer($printer_term_id) {
    return PS_Supply_Mapper::get_supplies_by_printer($printer_term_id);
}
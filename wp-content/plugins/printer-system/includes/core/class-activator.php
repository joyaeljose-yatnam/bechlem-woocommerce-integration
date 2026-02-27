<?php
/**
 * Plugin Activator Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class PS_Activator {
    
    /**
     * Activation hook callback
     */
    public static function activate() {
        // Register taxonomy
        PS_Taxonomy::register_taxonomy();
        
        // Create database tables
        PS_Database::create_mapping_table();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set activation timestamp
        update_option('ps_activated_time', current_time('timestamp'));
        update_option('ps_version', PS_VERSION);
    }
    
    /**
     * Deactivation hook callback
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Note: We don't delete data on deactivation
        // Use uninstall.php for cleanup
    }
}
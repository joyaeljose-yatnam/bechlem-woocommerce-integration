<?php
/**
 * Template Loader Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class PS_Template_Loader {
    
    /**
     * Initialize template loader hooks
     */
    public static function init() {
        add_filter('template_include', [__CLASS__, 'load_taxonomy_template'], 99);
    }
    
    /**
     * Load custom template for printer taxonomy
     *
     * @param string $template Current template path
     * @return string Modified template path
     */
    public static function load_taxonomy_template($template) {
        // Debug mode
        if (isset($_GET[PS_QUERY_DEBUG_TEMPLATE])) {
            self::debug_template_info($template);
        }
        
        if (!is_tax(PS_TAXONOMY_NAME)) {
            return $template;
        }
        
        $plugin_template = self::locate_template();
        
        if ($plugin_template && file_exists($plugin_template)) {
            return $plugin_template;
        }
        
        return $template;
    }
    
    /**
     * Locate the template file
     *
     * @return string|false Template path or false
     */
    private static function locate_template() {
        // Check in theme first
        $theme_template = locate_template([
            'taxonomy-' . PS_TAXONOMY_NAME . '.php',
            'printer-system/taxonomy-' . PS_TAXONOMY_NAME . '.php',
        ]);
        
        if ($theme_template) {
            return $theme_template;
        }
        
        // Fallback to plugin template
        return PS_PLUGIN_DIR . 'templates/taxonomy-' . PS_TAXONOMY_NAME . '.php';
    }
    
    /**
     * Debug template information
     *
     * @param string $template Current template
     */
    private static function debug_template_info($template) {
        echo "<!-- Template Debug Info -->\n";
        echo "Current template: " . esc_html($template) . "<br>\n";
        echo "is_tax('" . PS_TAXONOMY_NAME . "'): " . (is_tax(PS_TAXONOMY_NAME) ? 'YES' : 'NO') . "<br>\n";
        echo "Plugin template: " . esc_html(self::locate_template()) . "<br>\n";
        echo "<!-- End Template Debug Info -->\n";
    }
}
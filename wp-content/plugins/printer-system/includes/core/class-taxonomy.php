<?php
/**
 * Taxonomy Registration Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class PS_Taxonomy {
    
    /**
     * Initialize taxonomy hooks
     */
    public static function init() {
        add_action('init', [__CLASS__, 'register_taxonomy']);
    }
    
    /**
     * Register hierarchical printer taxonomy
     */
    public static function register_taxonomy() {
        $labels = self::get_taxonomy_labels();
        $args = self::get_taxonomy_args($labels);
        
        register_taxonomy(
            PS_TAXONOMY_NAME,
            [PS_POST_TYPE],
            $args
        );
    }
    
    /**
     * Get taxonomy labels
     *
     * @return array
     */
    private static function get_taxonomy_labels() {
        return [
            'name' => __('Printers', 'printer-system'),
            'singular_name' => __('Printer', 'printer-system'),
            'search_items' => __('Search Printers', 'printer-system'),
            'all_items' => __('All Printers', 'printer-system'),
            'parent_item' => __('Parent Printer', 'printer-system'),
            'parent_item_colon' => __('Parent Printer:', 'printer-system'),
            'edit_item' => __('Edit Printer', 'printer-system'),
            'update_item' => __('Update Printer', 'printer-system'),
            'add_new_item' => __('Add New Printer', 'printer-system'),
            'new_item_name' => __('New Printer Name', 'printer-system'),
            'menu_name' => __('Printers', 'printer-system'),
        ];
    }
    
    /**
     * Get taxonomy arguments
     *
     * @param array $labels Labels array
     * @return array
     */
    private static function get_taxonomy_args($labels) {
        return [
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'hierarchical' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'show_admin_column' => true,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => PS_TAXONOMY_SLUG,
                'with_front' => false,
                'hierarchical' => true
            ],
            'query_var' => PS_TAXONOMY_NAME,
            'capabilities' => [
                'manage_terms' => 'manage_product_terms',
                'edit_terms' => 'edit_product_terms',
                'delete_terms' => 'delete_product_terms',
                'assign_terms' => 'assign_product_terms',
            ],
        ];
    }
}
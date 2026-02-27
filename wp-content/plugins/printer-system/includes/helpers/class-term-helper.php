<?php
/**
 * Term Helper Class
 *
 * @package PrinterSystem
 */

if (!defined('ABSPATH')) {
    exit;
}

class PS_Term_Helper {
    
    /**
     * Get term by meta key and value
     *
     * @param string $taxonomy Taxonomy name
     * @param string $meta_key Meta key
     * @param mixed $meta_value Meta value
     * @return WP_Term|false Term object or false
     */
    public static function get_term_by_meta($taxonomy, $meta_key, $meta_value) {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'meta_query' => [
                [
                    'key' => $meta_key,
                    'value' => $meta_value,
                    'compare' => '='
                ]
            ],
            'number' => 1
        ]);

        if (!empty($terms) && !is_wp_error($terms)) {
            return $terms[0];
        }

        return false;
    }
    
    /**
     * Check if term exists by meta
     *
     * @param string $taxonomy Taxonomy name
     * @param string $meta_key Meta key
     * @param mixed $meta_value Meta value
     * @return bool
     */
    public static function term_exists_by_meta($taxonomy, $meta_key, $meta_value) {
        return (bool) self::get_term_by_meta($taxonomy, $meta_key, $meta_value);
    }
    
    /**
     * Create term with meta
     *
     * @param string $term_name Term name
     * @param string $taxonomy Taxonomy name
     * @param array $args Additional arguments (parent, etc.)
     * @param array $meta_data Meta data to add [key => value]
     * @return WP_Term|WP_Error
     */
    public static function create_term_with_meta($term_name, $taxonomy, $args = [], $meta_data = []) {
        $term = wp_insert_term($term_name, $taxonomy, $args);
        
        if (is_wp_error($term)) {
            return $term;
        }
        
        // Add meta data
        foreach ($meta_data as $meta_key => $meta_value) {
            update_term_meta($term['term_id'], $meta_key, $meta_value);
        }
        
        return get_term($term['term_id'], $taxonomy);
    }
}
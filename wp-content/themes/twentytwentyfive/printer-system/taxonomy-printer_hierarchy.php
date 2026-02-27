<?php
/**
 * Template for Printer Hierarchy Taxonomy
 * Handles Brand → Series → Printer hierarchy
 */

get_header();

$term = get_queried_object();

// Determine term level
$brand_id = get_term_meta($term->term_id, 'brand_id', true);
$series_id = get_term_meta($term->term_id, 'series_id', true);
$printer_id = get_term_meta($term->term_id, 'printer_id', true);

// Determine what level we're at
if ($printer_id) {
    $level = 'printer';
} elseif ($series_id) {
    $level = 'series';
} elseif ($brand_id) {
    $level = 'brand';
} else {
    $level = 'unknown';
}

?>

<div class="printer-hierarchy-page">
    
    <header class="page-header">
        <h1 class="page-title"><?php echo esc_html($term->name); ?></h1>
        
        <?php if ($term->description): ?>
            <div class="taxonomy-description">
                <?php echo wpautop($term->description); ?>
            </div>
        <?php endif; ?>
        
        <!-- Breadcrumb -->
        <?php
        $breadcrumb = [];
        $current = $term;
        while ($current->parent) {
            $parent = get_term($current->parent, 'printer_hierarchy');
            if (!is_wp_error($parent)) {
                array_unshift($breadcrumb, $parent);
                $current = $parent;
            } else {
                break;
            }
        }
        
        if (!empty($breadcrumb)): ?>
            <nav class="printer-breadcrumb">
                <?php foreach ($breadcrumb as $crumb): ?>
                    <a href="<?php echo get_term_link($crumb); ?>"><?php echo esc_html($crumb->name); ?></a>
                    <span class="separator"> → </span>
                <?php endforeach; ?>
                <span class="current"><?php echo esc_html($term->name); ?></span>
            </nav>
        <?php endif; ?>
    </header>

    <div class="printer-hierarchy-content">
        
        <?php if ($level === 'brand'): ?>
            <!-- BRAND LEVEL: Show Series -->
            <?php
            $series_terms = get_terms([
                'taxonomy' => 'printer_hierarchy',
                'parent' => $term->term_id,
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC'
            ]);
            
            if (!empty($series_terms) && !is_wp_error($series_terms)): ?>
                <h2>Series in <?php echo esc_html($term->name); ?></h2>
                <ul class="printer-series-list">
                    <?php foreach ($series_terms as $series): ?>
                        <li>
                            <a href="<?php echo get_term_link($series); ?>">
                                <strong><?php echo esc_html($series->name); ?></strong>
                                <span class="count">(<?php echo $series->count; ?> printers)</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No series found for this brand.</p>
            <?php endif; ?>
            
        <?php elseif ($level === 'series'): ?>
            <!-- SERIES LEVEL: Show Printers -->
            <?php
            $printer_terms = get_terms([
                'taxonomy' => 'printer_hierarchy',
                'parent' => $term->term_id,
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC'
            ]);
            
            if (!empty($printer_terms) && !is_wp_error($printer_terms)): ?>
                <h2>Printers in <?php echo esc_html($term->name); ?> Series</h2>
                <ul class="printer-list">
                    <?php foreach ($printer_terms as $printer): 
                        $printer_image = get_term_meta($printer->term_id, 'printer_image', true);
                        ?>
                        <li class="printer-item">
                            <a href="<?php echo get_term_link($printer); ?>">
                                <?php if ($printer_image): ?>
                                    <img src="<?php echo esc_url($printer_image); ?>" 
                                         alt="<?php echo esc_attr($printer->name); ?>"
                                         style="max-width: 100px; height: auto;">
                                <?php endif; ?>
                                <strong><?php echo esc_html($printer->name); ?></strong>
                                <span class="count">(<?php echo $printer->count; ?> supplies)</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No printers found in this series.</p>
            <?php endif; ?>
            
        <?php elseif ($level === 'printer'): ?>
            <!-- PRINTER LEVEL: Show Supplies (Products) -->
            <?php
            $query = ps_get_supplies_by_printer($term->term_id);
            
            if ($query && $query->have_posts()): ?>
                <h2>Compatible Supplies for <?php echo esc_html($term->name); ?></h2>
                
                <?php
                // Get printer image
                $printer_image = get_term_meta($term->term_id, 'printer_image', true);
                if ($printer_image): ?>
                    <div class="printer-image">
                        <img src="<?php echo esc_url($printer_image); ?>" 
                             alt="<?php echo esc_attr($term->name); ?>"
                             style="max-width: 200px; height: auto;">
                    </div>
                <?php endif; ?>
                
                <ul class="products columns-4">
                    <?php while ($query->have_posts()): 
                        $query->the_post();
                        wc_get_template_part('content', 'product');
                    endwhile; ?>
                </ul>
                
                <?php wp_reset_postdata(); ?>
                
            <?php else: ?>
                <p>No supplies found for this printer.</p>
            <?php endif; ?>
            
        <?php else: ?>
            <p>Unable to determine hierarchy level.</p>
        <?php endif; ?>
        
    </div>
    
</div>

<style>
.printer-hierarchy-page {
    padding: 20px;
}

.printer-breadcrumb {
    margin: 15px 0;
    font-size: 14px;
    color: #666;
}

.printer-breadcrumb a {
    color: #0073aa;
    text-decoration: none;
}

.printer-breadcrumb a:hover {
    text-decoration: underline;
}

.printer-breadcrumb .separator {
    margin: 0 5px;
}

.printer-series-list,
.printer-list {
    list-style: none;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.printer-series-list li,
.printer-list li {
    background: #f9f9f9;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.printer-series-list li a,
.printer-list li a {
    text-decoration: none;
    color: #333;
    display: block;
}

.printer-series-list li:hover,
.printer-list li:hover {
    background: #fff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.printer-item img {
    display: block;
    margin-bottom: 10px;
}

.count {
    display: block;
    font-size: 12px;
    color: #666;
    margin-top: 5px;
}

.printer-image {
    margin: 20px 0;
}
</style>

<?php get_footer(); ?>

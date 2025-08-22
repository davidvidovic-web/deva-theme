<?php
/**
 * DEVA Categories Shortcode - Enhanced version
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DEVA Categories Grid Shortcode
 */
function deva_categories_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => 5, // Increased default to show more categories
        'hide_empty' => 'false',
        'class' => '',
        'show_count' => 'true',
        'columns' => 3
    ), $atts);

    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        return '<div class="deva-error">' . __('WooCommerce is not active.', 'hello-elementor-child') . '</div>';
    }

    ob_start();

    try {
        // Get product categories - get more than needed first, then filter
        $categories = null;
        $requested_limit = intval($atts['limit']);
        
        // Get extra categories to account for potential filtering
        $query_limit = $requested_limit + 2; // Get 2 extra in case some are filtered out
        
        // Method 1: Standard get_terms with array syntax
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => ($atts['hide_empty'] === 'true'),
            'number' => $query_limit,
            'orderby' => 'name',
            'order' => 'ASC'
        ));
        
        // Method 2: If failed, try legacy syntax
        if (empty($categories) || is_wp_error($categories)) {
            $categories = get_terms('product_cat', array(
                'hide_empty' => false,
                'number' => $query_limit,
                'orderby' => 'name',
                'order' => 'ASC'
            ));
        }
        
        // Method 3: Final fallback - get all categories
        if (empty($categories) || is_wp_error($categories)) {
            $categories = get_terms('product_cat', array(
                'orderby' => 'name',
                'order' => 'ASC'
            ));
        }
        
        // Filter out uncategorized FIRST, then apply limit
        if (!empty($categories) && !is_wp_error($categories)) {
            $uncategorized_id = get_option('default_product_cat', 0);
            if ($uncategorized_id > 0) {
                $categories = array_filter($categories, function($cat) use ($uncategorized_id) {
                    return $cat->term_id != $uncategorized_id;
                });
                // Re-index array after filtering
                $categories = array_values($categories);
            }
            
            // Apply limit AFTER filtering
            if ($requested_limit > 0) {
                $categories = array_slice($categories, 0, $requested_limit);
            }
        }
        
        // Check if we have valid categories
        if (empty($categories) || is_wp_error($categories)) {
            echo '<div class="deva-notice">' . __('No product categories found. Please create some categories in WooCommerce → Products → Categories.', 'hello-elementor-child') . '</div>';
        } else {
            // Output the categories
            $has_fifth_card = (count($categories) >= 5);
            ?>
            <section class="deva-categories-section <?php echo esc_attr($atts['class']); ?>">
                <div class="elementor-container elementor-column-gap-default">
                    <div class="category-grid <?php echo $has_fifth_card ? 'has-fifth-card' : ''; ?>">
                        <?php 
                        $category_counter = 1;
                        foreach ($categories as $category) :
                            // Get category image
                            $category_image_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                            $category_image_url = '';
                            if ($category_image_id) {
                                $category_image_url = wp_get_attachment_image_url($category_image_id, 'medium');
                            }
                            
                            // Get category link
                            $category_link = get_term_link($category);
                            if (is_wp_error($category_link)) {
                                $category_link = '#';
                            }
                            
                            // Get products in this category
                            $products_in_cat = array();
                            if (function_exists('wc_get_products')) {
                                $products_in_cat = wc_get_products(array(
                                    'category' => array($category->slug),
                                    'limit' => 5,
                                    'status' => 'publish'
                                ));
                            }
                            ?>
                            <a href="<?php echo esc_url($category_link); ?>" class="category-link">
                                <div class="category-display-card">
                                    <div class="category-content">
                                        <span class="category-label"><?php _e('Category', 'hello-elementor-child'); ?></span>
                                        <h3><?php echo esc_html($category->name); ?></h3>
                                        
                                        <?php if (!empty($products_in_cat)) : ?>
                                            <ul class="category-products">
                                                <?php foreach (array_slice($products_in_cat, 0, 5) as $product) : ?>
                                                    <li><?php echo esc_html($product->get_name()); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>

                                    <div class="category-footer">
                                        <span class="category-number"><?php echo sprintf('%02d', $category_counter); ?></span>
                                        <span class="arrow-icon">→</span>
                                    </div>
                                </div>
                            </a>
                            <?php 
                            $category_counter++;
                        endforeach; ?>
                    </div>
                </div>
            </section>
            <?php
        }
    } catch (Exception $e) {
        echo '<div class="deva-error">' . __('Error loading categories:', 'hello-elementor-child') . ' ' . esc_html($e->getMessage()) . '</div>';
    }

    return ob_get_clean();
}

add_shortcode('deva_categories', 'deva_categories_shortcode');

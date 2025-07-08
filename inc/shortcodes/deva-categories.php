<?php
/**
 * DEVA Categories Shortcode
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
        'limit' => 4,
        'hide_empty' => true,
        'class' => '',
        'show_count' => true,
        'columns' => 4
    ), $atts);

    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        return '<p>WooCommerce is not active.</p>';
    }

    ob_start();

    // Get product categories
    $categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'number' => intval($atts['limit']),
        'hide_empty' => $atts['hide_empty'],
        'exclude' => array(get_option('default_product_cat')) // Exclude uncategorized
    ));

    if (!empty($categories) && !is_wp_error($categories)) :
        $has_fifth_card = (count($categories) >= 5);
        ?>
        <section class="deva-categories-section <?php echo esc_attr($atts['class']); ?>">
            <div class="elementor-container elementor-column-gap-default">
                <div class="category-grid <?php echo $has_fifth_card ? 'has-fifth-card' : ''; ?>">
                    <?php 
                    $category_counter = 1;
                    foreach ($categories as $category) :
                        $category_image_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                        $category_image_url = '';
                        if ($category_image_id) {
                            $category_image_url = wp_get_attachment_image_url($category_image_id, 'medium');
                        }
                        $category_link = get_term_link($category);
                        $products_in_cat = wc_get_products(array(
                            'category' => array($category->slug),
                            'limit' => 5,
                            'status' => 'publish'
                        ));
                        ?>
                        <a href="<?php echo esc_url($category_link); ?>" class="category-link">
                            <div class="category-display-card">
                                <div class="category-content">
                                    <span class="category-label">Category</span>
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
    else :
        echo '<p>No categories found.</p>';
    endif;

    return ob_get_clean();
}
add_shortcode('deva_categories', 'deva_categories_shortcode');

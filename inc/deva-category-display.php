<?php

/**
 * DEVA Category Display Shortcode
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DEVA Category Display 50/50 Shortcode
 */
function deva_category_display_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'hide_empty' => true,
        'current_page' => 1,
        'class' => ''
    ), $atts);

    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        return '<p>WooCommerce is not active.</p>';
    }

    // Get current page from URL or attributes
    $current_page = isset($_GET['cat_page']) ? intval($_GET['cat_page']) : intval($atts['current_page']);
    $current_page = max(1, $current_page); // Ensure minimum page is 1

    ob_start();

    // Get WooCommerce product categories ordered consistently
    $categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => $atts['hide_empty'],
        'orderby' => 'name', // Changed from term_id to name for consistent ordering
        'order' => 'ASC',
        'exclude' => array(get_option('default_product_cat')) // Exclude uncategorized
    ));

    if (is_wp_error($categories)) {
        return '<p>Error loading categories: ' . $categories->get_error_message() . '</p>';
    }

    if (empty($categories)) {
        // Try without excluding uncategorized to see if that's the issue
        $categories = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => $atts['hide_empty'],
            'orderby' => 'name', // Changed from term_id to name for consistent ordering
            'order' => 'ASC'
        ));

        if (empty($categories)) {
            return '<div class="deva-category-display-section"><div class="elementor-container"><p style="text-align: center; padding: 40px; color: #666;">No product categories found. Please create some product categories in WooCommerce.</p></div></div>';
        }
    }

    // Re-index the array to ensure numerical indices starting from 0
    $categories = array_values($categories);
    $total_categories = count($categories);

    // Handle edge case where no categories exist
    if ($total_categories === 0) {
        return '<div class="deva-category-display-section"><div class="elementor-container"><p style="text-align: center; padding: 40px; color: #666;">No product categories found. Please create some product categories in WooCommerce.</p></div></div>';
    }

    // Ensure current page is within valid range and get the current category
    if ($current_page < 1) {
        $current_page = 1;
    }
    if ($current_page > $total_categories) {
        $current_page = $total_categories;
    }

    // Get the current category (0-based index)
    $category_index = $current_page - 1;
    $current_category = $categories[$category_index];

    // Get category image
    $category_image_id = get_term_meta($current_category->term_id, 'thumbnail_id', true);
    $category_image_url = '';
    if ($category_image_id) {
        $category_image_url = wp_get_attachment_image_url($category_image_id, 'large');
    }

    // Get products in this category
    $products_in_cat = wc_get_products(array(
        'category' => array($current_category->slug),
        'limit' => 5,
        'status' => 'publish'
    ));

    // Navigation URLs - use category path structure
    $base_url = home_url('/product-category/');
    $prev_url = '';
    $next_url = '';
    
    if ($current_page > 1) {
        $prev_category = $categories[$current_page - 2]; // Get previous category
        $prev_url = $base_url . $prev_category->slug . '/?cat_page=' . ($current_page - 1);
    }
    
    if ($current_page < $total_categories) {
        $next_category = $categories[$current_page]; // Get next category (0-based index)
        $next_url = $base_url . $next_category->slug . '/?cat_page=' . ($current_page + 1);
    }
    
    // Current category URL for JavaScript update
    $current_category_url = $base_url . $current_category->slug . '/?cat_page=' . $current_page;
?>
    <section class="deva-category-display-section <?php echo esc_attr($atts['class']); ?>">
        <div class="elementor-container">
            <div class="category-display-content">
                <!-- Category Card - Left Side -->
                <div class="category-display-card">
                    <div class="category-content">
                        <span class="category-label">Category</span>
                        <h3><?php echo esc_html($current_category->name); ?></h3>
                        <?php if (!empty($products_in_cat)) : ?>
                            <ul class="category-products">
                                <?php foreach (array_slice($products_in_cat, 0, 5) as $product) : ?>
                                    <li><?php echo esc_html($product->get_name()); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <div class="category-footer">
                            <span class="category-number"><?php echo sprintf('%02d', $current_page); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Category Image - Right Side -->
                <div class="category-display-image">
                    <?php if ($category_image_url) : ?>
                        <img src="<?php echo esc_url($category_image_url); ?>" alt="<?php echo esc_attr($current_category->name); ?>" />
                    <?php else : ?>
                        <div class="category-placeholder">
                            <span><?php echo esc_html($current_category->name); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="deva-pagination">
                <ul>
                    <?php if ($prev_url) : ?>
                        <li class="prev">
                            <a href="<?php echo esc_url($prev_url); ?>">←</a>
                        </li>
                    <?php else : ?>
                        <li class="prev">
                            <span class="disabled">←</span>
                        </li>
                    <?php endif; ?>

                    <li>
                        <span class="current"><?php echo $current_page; ?></span>
                    </li>

                    <li>
                        <span class="page-info"><?php echo $total_categories; ?></span>
                    </li>

                    <?php if ($next_url) : ?>
                        <li class="next">
                            <a href="<?php echo esc_url($next_url); ?>">→</a>
                        </li>
                    <?php else : ?>
                        <li class="next">
                            <span class="disabled">→</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </section>

    <!-- Update browser URL to reflect current category -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Update the browser URL to the clean category path structure
        if (window.history && window.history.replaceState) {
            const newUrl = '<?php echo esc_js($current_category_url); ?>';
            window.history.replaceState({}, '', newUrl);
        }
    });
    </script>
<?php
    return ob_get_clean();
}
add_shortcode('deva_category_display', 'deva_category_display_shortcode');

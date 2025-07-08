<?php
/**
 * DEVA Products Shortcode
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DEVA Products Grid Shortcode
 */
function deva_products_shortcode($atts) {
    $atts = shortcode_atts(array(
        'per_page' => 12,
        'columns' => 3,
        'class' => '',
        'pagination' => 'true',
        'ajax' => 'true'
    ), $atts);

    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        return '<p>WooCommerce is not active.</p>';
    }

    // Generate unique shortcode ID for AJAX
    $shortcode_id = 'deva_products_' . wp_rand(1000, 9999);
    
    // Get current page for pagination
    $paged = 1;
    if (isset($_GET['deva_page'])) {
        $paged = intval($_GET['deva_page']);
    }
    
    // Handle AJAX requests
    if (defined('DOING_AJAX') && DOING_AJAX) {
        $shortcode_atts = json_decode(stripslashes($_POST['shortcode_atts']), true);
        if ($shortcode_atts) {
            $atts = array_merge($atts, $shortcode_atts);
        }
        
        if (isset($_POST['page'])) {
            $paged = intval($_POST['page']);
        }
    }

    ob_start();
?>
    <section class="deva-shop-section <?php echo esc_attr($atts['class']); ?>" id="<?php echo $shortcode_id; ?>" data-ajax="<?php echo esc_attr($atts['ajax']); ?>">
        <div class="elementor-container elementor-column-gap-default">
            <div class="deva-products-container" data-shortcode-atts="<?php echo esc_attr(json_encode($atts)); ?>">
                <?php echo deva_get_products_html($atts, $paged); ?>
            </div>
        </div>
    </section>
<?php
    return ob_get_clean();
}

/**
 * Get products HTML for AJAX and initial load
 */
function deva_get_products_html($atts, $paged = 1) {
    ob_start();
    
    // Set up the WooCommerce query with pagination
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => intval($atts['per_page']),
        'paged' => $paged,
    );

    $products = new WP_Query($args);

    if ($products->have_posts()) :
        echo '<ul class="products columns-' . esc_attr($atts['columns']) . '">';

        while ($products->have_posts()) :
            $products->the_post();
            global $product;
            
            // Custom product card HTML with column layout
            ?>
            <li <?php wc_product_class('deva-product-card', $product); ?>>
                <a href="<?php echo esc_url($product->get_permalink()); ?>" class="product-link">
                    <div class="product-image-wrapper">
                        <?php echo woocommerce_get_product_thumbnail(); ?>
                        
                        <!-- Like/Favorite Heart Button - Top Left -->
                        <div class="favorite-heart" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </div>

                        <!-- Price Bubble - Top Right -->
                        <div class="price-overlay">
                            <?php echo $product->get_price_html(); ?>
                        </div>

                        <!-- Sale Badge - Bottom Left -->
                        <?php if ($product->is_on_sale()) : ?>
                            <span class="onsale"><?php esc_html_e('Sale!', 'woocommerce'); ?></span>
                        <?php endif; ?>
                    </div>
                </a>

                <div class="product-info-wrapper">
                    <div class="product-content">
                        <!-- Product Title -->
                        <h2 class="woocommerce-loop-product__title">
                            <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                <?php echo $product->get_name(); ?>
                            </a>
                        </h2>

                        <!-- Product Description -->
                        <?php 
                        $short_description = $product->get_short_description();
                        if ($short_description) : ?>
                            <div class="product-excerpt">
                                <?php echo wp_trim_words($short_description, 15, '...'); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Custom single star rating display -->
                        <?php
                        $rating = $product->get_average_rating();
                        $rating_count = $product->get_rating_count();
                        
                        if ($rating_count > 0) : ?>
                            <div class="deva-single-star-rating">
                                <span class="star-icon">★</span>
                                <span class="rating-score"><?php echo number_format($rating, 1); ?></span>
                                <span class="rating-count">(<?php echo $rating_count; ?>)</span>
                            </div>
                        <?php endif; ?>

                        <!-- Action Buttons -->
                        <div class="product-actions">
                            <?php if ($product->is_purchasable() && $product->is_in_stock()) : ?>
                                <?php
                                // Use WooCommerce's add to cart button but with our custom classes
                                woocommerce_template_loop_add_to_cart();
                                ?>
                            <?php else : ?>
                                <span class="out-of-stock">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </li>
            <?php
        endwhile;

        echo '</ul>';
        wp_reset_postdata();

        // Add pagination if enabled
        if ($atts['pagination'] === 'true' && $products->max_num_pages > 1) :
            echo '<nav class="woocommerce-pagination">';
            
            // Generate pagination with proper base URL
            $big = 999999999; // need an unlikely integer
            $current_url = home_url(add_query_arg(array()));
            
            echo paginate_links(array(
                'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format' => '?paged=%#%',
                'current' => max(1, $paged),
                'total' => $products->max_num_pages,
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'type' => 'list',
                'end_size' => 3,
                'mid_size' => 3,
                'add_args' => array('deva_page' => '%#%') // Custom parameter for our shortcode
            ));
            echo '</nav>';
        endif;
    else :
        do_action('woocommerce_no_products_found');
    endif;
    
    return ob_get_clean();
}

/**
 * AJAX handler for loading products
 */
function deva_ajax_load_products() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'deva_products_nonce')) {
        wp_die('Security check failed');
    }
    
    $paged = intval($_POST['paged']);
    $atts = $_POST['shortcode_atts'];
    
    $html = deva_get_products_html($atts, $paged);
    
    wp_send_json_success($html);
}
add_action('wp_ajax_deva_load_products', 'deva_ajax_load_products');
add_action('wp_ajax_nopriv_deva_load_products', 'deva_ajax_load_products');

// Register the shortcode
add_shortcode('deva_products', 'deva_products_shortcode');

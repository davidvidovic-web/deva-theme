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
function deva_products_shortcode($atts)
{
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
    if (isset($_GET['paged'])) {
        $paged = intval($_GET['paged']);
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
function deva_get_products_html($atts, $paged = 1)
{
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
        echo '<ul class="deva-products-grid columns-' . esc_attr($atts['columns']) . '">';

        while ($products->have_posts()) :
            $products->the_post();
            global $product;

            // Custom product card HTML with column layout
    ?>
            <li class="deva-product-card" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                <a href="<?php echo esc_url($product->get_permalink()); ?>" class="deva-product-link">
                    <div class="deva-product-image-wrapper">
                        <?php
                        if (has_post_thumbnail($product->get_id())) {
                            echo get_the_post_thumbnail($product->get_id(), 'woocommerce_thumbnail', array('class' => 'deva-product-image'));
                        } else {
                            echo '<div class="deva-product-placeholder">No Image</div>';
                        }
                        ?>

                        <!-- Like/Favorite Heart Button - Top Left -->
                        <div class="deva-favorite-heart" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="#fff" stroke="currentColor" stroke-width="2">
                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                            </svg>
                        </div>

                        <!-- Price Bubble - Top Right (Current Price Only) -->
                        <div class="deva-price-overlay">
                            <?php
                            // Display only current price, not both original and discounted
                            $current_price = $product->get_price();
                            $currency_symbol = get_woocommerce_currency_symbol();

                            // Validate price is numeric and not empty
                            if (is_numeric($current_price) && $current_price > 0) {
                                echo $currency_symbol . number_format((float)$current_price, 2);
                            } else {
                                // Fallback for products without valid price
                                echo $currency_symbol . '0.00';
                            }
                            ?>
                        </div>

                        <!-- Sale Badge - Bottom Left -->
                        <?php if ($product->is_on_sale()) : ?>
                            <span class="deva-sale-badge">Sale!</span>
                        <?php endif; ?>
                    </div>
                </a>

                <div class="deva-product-info-wrapper">
                    <div class="deva-product-content">
                        <div class="deva-product-header">
                            <!-- Product Title -->
                            <h2 class="deva-product-title">
                                <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                    <?php echo $product->get_name(); ?>
                                </a>
                            </h2>
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
                        </div>

                        <!-- Product Description -->
                        <?php
                        $product_excerpt = deva_get_product_excerpt($product, 15);
                        if ($product_excerpt && $product_excerpt !== 'No description available.') : ?>
                            <div class="deva-product-excerpt">
                                <?php echo esc_html($product_excerpt); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Custom single star rating display -->


                        <!-- Action Buttons -->
                        <div class="deva-product-actions">
                            <?php if ($product->is_purchasable() && $product->is_in_stock()) : ?>
                                <button class="deva-add-to-cart-btn" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                                    <span class="button-text">Buy Now</span>
                                </button>
                            <?php else : ?>
                                <span class="deva-out-of-stock">Out of Stock</span>
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
            echo '<nav class="deva-pagination">';

            // Generate pagination with current page URL as base (same as category shortcode)
            $base_url = remove_query_arg('paged');

            echo paginate_links(array(
                'base' => add_query_arg('paged', '%#%', $base_url),
                'format' => '',
                'current' => max(1, $paged),
                'total' => $products->max_num_pages,
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'type' => 'list',
                'end_size' => 3,
                'mid_size' => 3
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
function deva_ajax_load_products()
{
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

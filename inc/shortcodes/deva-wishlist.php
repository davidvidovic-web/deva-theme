<?php

/**
 * DEVA Wishlist Display Shortcode
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DEVA Wishlist Display Shortcode
 */
function deva_wishlist_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'class' => '',
        'columns' => 3,
        'show_remove_button' => 'true'
    ), $atts);

    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        return '<div class="deva-error">WooCommerce is not active.</div>';
    }

    ob_start();
?>
    <div class="deva-wishlist-display <?php echo esc_attr($atts['class']); ?>">
        <div class="deva-wishlist-header">
            <h2>My Wishlist</h2>
            <span class="wishlist-count-display">(<span class="deva-wishlist-count">0</span> items)</span>
        </div>
        
        <div class="deva-wishlist-products">
            <div class="deva-wishlist-empty" style="display: none;">
                <p>Your wishlist is empty.</p>
                <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="continue-shopping-btn">Continue Shopping</a>
            </div>
            
            <ul class="deva-products-grid columns-<?php echo esc_attr($atts['columns']); ?>" id="wishlist-products-grid">
                <!-- Products will be loaded here via JavaScript -->
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadWishlistProducts();
        });

        function loadWishlistProducts() {
            var favorites = JSON.parse(localStorage.getItem('deva_favorites') || '[]');
            var $grid = document.getElementById('wishlist-products-grid');
            var $empty = document.querySelector('.deva-wishlist-empty');
            var $countDisplay = document.querySelector('.deva-wishlist-count');
            
            // Update count
            if ($countDisplay) {
                $countDisplay.textContent = favorites.length;
            }
            
            if (favorites.length === 0) {
                $grid.style.display = 'none';
                if ($empty) $empty.style.display = 'block';
                return;
            }
            
            $grid.style.display = 'grid';
            if ($empty) $empty.style.display = 'none';
            
            // Load products via AJAX
            if (typeof shop_ajax !== 'undefined' && shop_ajax.ajax_url) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', shop_ajax.ajax_url, true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.success && response.data.html) {
                                $grid.innerHTML = response.data.html;
                            }
                        } catch (e) {
                            console.error('Error parsing wishlist response:', e);
                        }
                    }
                };
                
                var params = 'action=get_wishlist_products&product_ids=' + encodeURIComponent(JSON.stringify(favorites)) + '&nonce=' + shop_ajax.nonce + '&show_remove_button=<?php echo esc_js($atts['show_remove_button']); ?>';
                xhr.send(params);
            }
        }

        // Listen for wishlist changes
        document.addEventListener('click', function(e) {
            if (e.target.closest('.deva-favorite-heart')) {
                // Reload wishlist after heart click
                setTimeout(loadWishlistProducts, 500);
            }
        });
    </script>
<?php
    return ob_get_clean();
}

/**
 * AJAX handler to get wishlist products HTML
 */
function deva_get_wishlist_products() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'shop_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $product_ids = json_decode(stripslashes($_POST['product_ids']), true);
    $show_remove_button = isset($_POST['show_remove_button']) ? $_POST['show_remove_button'] === 'true' : true;
    
    if (!is_array($product_ids) || empty($product_ids)) {
        wp_send_json_success(array('html' => ''));
        return;
    }
    
    ob_start();
    
    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);
        if (!$product || !$product->is_visible()) {
            continue;
        }
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

                    <!-- Like/Favorite Heart Button - Already favorited -->
                    <div class="deva-favorite-heart active" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="#e74c3c" stroke="currentColor" stroke-width="2">
                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                        </svg>
                    </div>

                    <!-- Price Bubble -->
                    <div class="deva-price-overlay">
                        <?php
                        $current_price = $product->get_price();
                        $currency_symbol = get_woocommerce_currency_symbol();

                        if (is_numeric($current_price) && $current_price > 0) {
                            echo $currency_symbol . number_format((float)$current_price, 2);
                        } else {
                            echo $currency_symbol . '0.00';
                        }
                        ?>
                    </div>

                    <!-- Sale Badge -->
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
    }
    
    $html = ob_get_clean();
    wp_send_json_success(array('html' => $html));
}

add_action('wp_ajax_get_wishlist_products', 'deva_get_wishlist_products');
add_action('wp_ajax_nopriv_get_wishlist_products', 'deva_get_wishlist_products');

// Register the shortcode
add_shortcode('deva_wishlist', 'deva_wishlist_shortcode');

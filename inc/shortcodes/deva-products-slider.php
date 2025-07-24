<?php

/**
 * DEVA Products Slider Shortcode
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DEVA Products Slider Shortcode
 */
function deva_products_slider_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'limit' => 10,
        'class' => '',
        'related_to' => 0, // Product ID to find related products for
        'category' => '', // Specific category slug
        'title' => 'Related Products',
        'slides_per_view' => 4,
        'slides_per_view_mobile' => 2,
        'autoplay' => 'true',
        'loop' => 'true'
    ), $atts);

    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        return '<p>WooCommerce is not active.</p>';
    }

    // Generate unique slider ID
    $slider_id = 'deva_products_slider_' . wp_rand(1000, 9999);

    ob_start();
?>
    <section class="deva-products-slider-section <?php echo esc_attr($atts['class']); ?>">
        <?php if (!empty($atts['title'])) : ?>
            <h2 class="deva-products-slider-title"><?php echo esc_html($atts['title']); ?></h2>
        <?php endif; ?>
        
        <div class="deva-products-slider-wrapper">
            <div class="swiper <?php echo $slider_id; ?>">
                <div class="swiper-wrapper">
                    <?php echo deva_get_products_slider_html($atts); ?>
                </div>
                
                <!-- Navigation buttons -->
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                
                <!-- Pagination dots -->
                <div class="swiper-pagination"></div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to initialize the slider
            function initializeProductsSlider() {
                if (typeof Swiper !== 'undefined') {
                    // Initialize Swiper for this specific slider
                    const swiper_<?php echo str_replace('-', '_', $slider_id); ?> = new Swiper('.<?php echo $slider_id; ?>', {
                        slidesPerView: <?php echo intval($atts['slides_per_view_mobile']); ?>,
                        spaceBetween: 20,
                        loop: <?php echo $atts['loop'] === 'true' ? 'true' : 'false'; ?>,
                        <?php if ($atts['autoplay'] === 'true') : ?>
                        autoplay: {
                            delay: 3000,
                            disableOnInteraction: false,
                        },
                        <?php endif; ?>
                        navigation: {
                            nextEl: '.<?php echo $slider_id; ?> .swiper-button-next',
                            prevEl: '.<?php echo $slider_id; ?> .swiper-button-prev',
                        },
                        pagination: {
                            el: '.<?php echo $slider_id; ?> .swiper-pagination',
                            clickable: true,
                        },
                        breakpoints: {
                            640: {
                                slidesPerView: <?php echo max(2, intval($atts['slides_per_view_mobile'])); ?>,
                                spaceBetween: 20,
                            },
                            768: {
                                slidesPerView: <?php echo max(3, intval($atts['slides_per_view']) - 1); ?>,
                                spaceBetween: 25,
                            },
                            1024: {
                                slidesPerView: <?php echo intval($atts['slides_per_view']); ?>,
                                spaceBetween: 30,
                            }
                        }
                    });
                } else {
                    console.log('Swiper not loaded, attempting to load...');
                    // Load Swiper from CDN if not available
                    loadSwiperAndInitProducts();
                }
            }
            
            // Function to load Swiper if not available
            function loadSwiperAndInitProducts() {
                // Check if Swiper CSS is already loaded
                if (!document.querySelector('link[href*="swiper"]')) {
                    const swiperCSS = document.createElement('link');
                    swiperCSS.rel = 'stylesheet';
                    swiperCSS.href = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css';
                    document.head.appendChild(swiperCSS);
                }
                
                // Check if Swiper JS is already loaded
                if (!document.querySelector('script[src*="swiper"]')) {
                    const swiperJS = document.createElement('script');
                    swiperJS.src = 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js';
                    swiperJS.onload = function() {
                        initializeProductsSlider();
                    };
                    document.head.appendChild(swiperJS);
                } else {
                    // Swiper script exists, just wait a bit and try again
                    setTimeout(initializeProductsSlider, 100);
                }
            }
            
            // Start initialization
            initializeProductsSlider();
        });
    </script>
<?php
    return ob_get_clean();
}

/**
 * Get products HTML for slider
 */
function deva_get_products_slider_html($atts)
{
    ob_start();

    // Set up the WooCommerce query
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => intval($atts['limit']),
        'orderby' => 'rand', // Random order for variety
    );

    // Handle related products logic
    if (!empty($atts['related_to']) && intval($atts['related_to']) > 0) {
        $related_product = wc_get_product($atts['related_to']);
        if ($related_product) {
            // Get products from the same categories
            $product_categories = wp_get_post_terms($atts['related_to'], 'product_cat', array('fields' => 'ids'));
            if (!empty($product_categories)) {
                $args['tax_query'] = array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field'    => 'term_id',
                        'terms'    => $product_categories,
                        'operator' => 'IN',
                    ),
                );
                // Exclude the current product
                $args['post__not_in'] = array($atts['related_to']);
            }
        }
    } elseif (!empty($atts['category'])) {
        // Filter by specific category
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $atts['category'],
            ),
        );
    }

    $products = new WP_Query($args);
    
    // If no products found and we were looking for related products, try a fallback query
    if (!$products->have_posts() && (!empty($atts['related_to']) || !empty($atts['category']))) {
        // Fallback: get any products
        $fallback_args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => 'rand',
        );
        
        $products = new WP_Query($fallback_args);
    }

    if ($products->have_posts()) :
        while ($products->have_posts()) :
            $products->the_post();
            global $product;
    ?>
            <div class="swiper-slide">
                <div class="deva-product-card-slider" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
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
                                <h3 class="deva-product-title">
                                    <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                        <?php echo $product->get_name(); ?>
                                    </a>
                                </h3>
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
                            $product_excerpt = deva_get_product_excerpt($product, 10);
                            if ($product_excerpt && $product_excerpt !== 'No description available.') : ?>
                                <div class="deva-product-excerpt">
                                    <?php echo esc_html($product_excerpt); ?>
                                </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div class="deva-product-actions">
                                <?php if ($product->is_purchasable() && $product->is_in_stock()) : ?>
                                    <button class="deva-add-to-cart-btn" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                                        Buy Now
                                    </button>
                                <?php else : ?>
                                    <span class="deva-out-of-stock">Out of Stock</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php
        endwhile;
        wp_reset_postdata();
    else :
        // More informative fallback message
        echo '<div class="swiper-slide">';
        echo '<div class="no-products-message" style="text-align: center; padding: 40px 20px; color: #666;">';
        echo '<p><strong>No products found.</strong></p>';
        echo '<p style="font-size: 14px; margin-top: 10px;">Query details:</p>';
        echo '<ul style="font-size: 12px; text-align: left; display: inline-block;">';
        echo '<li>Post type: product</li>';
        echo '<li>Posts per page: ' . intval($atts['limit']) . '</li>';
        if (!empty($atts['related_to'])) {
            echo '<li>Related to product ID: ' . intval($atts['related_to']) . '</li>';
        }
        if (!empty($atts['category'])) {
            echo '<li>Category: ' . esc_html($atts['category']) . '</li>';
        }
        echo '<li>Total products in query: ' . $products->found_posts . '</li>';
        echo '</ul>';
        echo '</div>';
        echo '</div>';
    endif;

    return ob_get_clean();
}

// Register the shortcode
add_shortcode('deva_products_slider', 'deva_products_slider_shortcode');

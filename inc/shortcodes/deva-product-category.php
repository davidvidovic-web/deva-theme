<?php
/**
 * DEVA Product Category Shortcode
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DEVA Comprehensive Product Category and Filter Shortcode
 */
function deva_product_category_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category' => '',
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

    // Auto-detect current category context
    $current_category_context = '';
    
    // Check if we're on a product category page
    if (is_product_category()) {
        $current_category_context = get_queried_object()->slug;
    }
    // Check if category is passed via URL parameter
    elseif (isset($_GET['current_category'])) {
        $current_category_context = sanitize_text_field($_GET['current_category']);
    }
    // Use shortcode attribute as fallback
    elseif (!empty($atts['category'])) {
        $current_category_context = $atts['category'];
    }

    // Get current parameters
    $search_query = isset($_GET['product_search']) ? sanitize_text_field($_GET['product_search']) : '';
    $category_filter = isset($_GET['category_filter']) ? sanitize_text_field($_GET['category_filter']) : $current_category_context;
    $sort_by = isset($_GET['sort_by']) ? sanitize_text_field($_GET['sort_by']) : 'date';
    $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;

    // Get current category name for display
    $current_category_name = 'All Categories';
    if ($category_filter) {
        $category_term = get_term_by('slug', $category_filter, 'product_cat');
        if ($category_term) {
            $current_category_name = $category_term->name;
        }
    }

    // Set up query args - simplified approach
    $query_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => $atts['per_page'],
        'paged' => $current_page
    );

    // Add search query
    if ($search_query) {
        $query_args['s'] = $search_query;
    }

    // Add category filter
    if ($category_filter) {
        $query_args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category_filter,
            )
        );
    }

    // Add sorting
    switch ($sort_by) {
        case 'price_low':
            $query_args['meta_key'] = '_price';
            $query_args['orderby'] = 'meta_value_num';
            $query_args['order'] = 'ASC';
            break;
        case 'price_high':
            $query_args['meta_key'] = '_price';
            $query_args['orderby'] = 'meta_value_num';
            $query_args['order'] = 'DESC';
            break;
        case 'name':
            $query_args['orderby'] = 'title';
            $query_args['order'] = 'ASC';
            break;
        case 'popularity':
            $query_args['meta_key'] = 'total_sales';
            $query_args['orderby'] = 'meta_value_num';
            $query_args['order'] = 'DESC';
            break;
        case 'rating':
            $query_args['meta_key'] = '_wc_average_rating';
            $query_args['orderby'] = 'meta_value_num';
            $query_args['order'] = 'DESC';
            break;
        default: // date
            $query_args['orderby'] = 'date';
            $query_args['order'] = 'DESC';
            break;
    }

    $products = new WP_Query($query_args);
    $total_products = $products->found_posts;

    // Get all categories for filter dropdown
    $categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'exclude' => array(get_option('default_product_cat'))
    ));

    ob_start();
    ?>    <section class="deva-shop-section deva-product-category-section <?php echo esc_attr($atts['class']); ?>"
             data-ajax="<?php echo esc_attr($atts['ajax']); ?>"
             data-current-category="<?php echo esc_attr($current_category_context); ?>">
        <div class="elementor-container elementor-column-gap-default">
            
            <!-- Header Bar with Category, Count, and Sort -->
            <div class="search-header-bar">
                <div class="current-category">
                    <?php echo esc_html($current_category_name); ?>
                </div>
                <div class="product-count">
                    <?php 
                    $start = (($current_page - 1) * $atts['per_page']) + 1;
                    $end = min($current_page * $atts['per_page'], $total_products);
                    echo $start . ' of ' . $total_products . ' products';
                    ?>
                </div>
                <div class="sort-dropdown">
                    <select name="sort_by" id="sort_by">
                        <option value="date" <?php selected($sort_by, 'date'); ?>>Sort by Latest</option>
                        <option value="popularity" <?php selected($sort_by, 'popularity'); ?>>Sort by Popularity</option>
                        <option value="rating" <?php selected($sort_by, 'rating'); ?>>Sort by Rating</option>
                        <option value="price_low" <?php selected($sort_by, 'price_low'); ?>>Price: Low to High</option>
                        <option value="price_high" <?php selected($sort_by, 'price_high'); ?>>Price: High to Low</option>
                        <option value="name" <?php selected($sort_by, 'name'); ?>>Sort by Name</option>
                    </select>
                </div>
            </div>

            <!-- Search and Filter Bar -->
            <div class="search-filter-bar">
                <form method="get" class="product-category-form">
                    <div class="search-input-wrapper">
                        <input type="text" name="product_search" placeholder="Search by title, keywords..." value="<?php echo esc_attr($search_query); ?>" />
                    </div>
                    <div class="filter-dropdown-wrapper">
                        <select name="category_filter">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category) : ?>
                                <option value="<?php echo esc_attr($category->slug); ?>" <?php selected($category_filter, $category->slug); ?>>
                                    <?php echo esc_html($category->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="search-button">Search</button>
                    <!-- Hidden fields to preserve context -->
                    <input type="hidden" name="sort_by" value="<?php echo esc_attr($sort_by); ?>" />
                    <?php if ($current_category_context) : ?>
                        <input type="hidden" name="current_category" value="<?php echo esc_attr($current_category_context); ?>" />
                    <?php endif; ?>
                </form>
            </div>

            <!-- Products Container -->
            <div class="deva-products-container" data-shortcode-atts="<?php echo esc_attr(json_encode($atts)); ?>">
                <?php if ($products->have_posts()) : ?>
                    <ul class="deva-products-grid">
                        <?php while ($products->have_posts()) : $products->the_post(); ?>
                            <?php
                            global $product;
                            // Use custom product template for search shortcode
                            ?>
                            <li class="deva-product-card deva-search-product" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
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
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                            </svg>
                                        </div>

                                        <!-- Price Bubble - Top Right -->
                                        <div class="deva-price-overlay">
                                            <?php echo $product->get_price_html(); ?>
                                        </div>

                                        <!-- Sale Badge - Bottom Left -->
                                        <?php if ($product->is_on_sale()) : ?>
                                            <span class="deva-sale-badge">Sale!</span>
                                        <?php endif; ?>
                                    </div>
                                </a>

                                <div class="deva-product-info-wrapper">
                                    <div class="deva-product-content">
                                        <!-- Product Name -->
                                        <h2 class="deva-product-title">
                                            <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                                <?php echo $product->get_name(); ?>
                                            </a>
                                        </h2>
                                        
                                        <!-- Product Description -->
                                        <div class="product-excerpt">
                                            <?php 
                                            $excerpt = wp_trim_words($product->get_short_description() ?: $product->get_description(), 15, '...');
                                            echo $excerpt;
                                            ?>
                                        </div>

                                        <!-- Single Star Rating with Reviews -->
                                        <?php
                                        $rating = $product->get_average_rating();
                                        $rating_count = $product->get_rating_count();
                                        
                                        if ($rating_count > 0) : ?>
                                            <div class="deva-single-star-rating">
                                                <span class="star-icon">★</span>
                                                <span class="rating-score"><?php echo number_format($rating, 1); ?></span>
                                                <span class="rating-count">( <?php echo $rating_count; ?> reviews )</span>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Action Buttons -->
                                        <div class="deva-product-actions">
                                            <?php if ($product->is_purchasable() && $product->is_in_stock()) : ?>
                                                <div class="deva-button-row">
                                                    <button class="deva-add-to-cart-btn" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                                                        Add to Cart
                                                    </button>
                                                    <a href="<?php echo esc_url($product->get_permalink()); ?>" class="deva-buy-now-btn">
                                                        Buy Now
                                                    </a>
                                                </div>
                                            <?php else : ?>
                                                <span class="deva-out-of-stock">Out of Stock</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>

                    <!-- Pagination -->
                    <?php if ($atts['pagination'] === 'true' && $products->max_num_pages > 1) : ?>
                        <nav class="deva-pagination">
                            <?php
                            $base_url = remove_query_arg('paged');
                            echo paginate_links(array(
                                'base' => add_query_arg('paged', '%#%', $base_url),
                                'format' => '',
                                'prev_text' => 'Previous',
                                'next_text' => 'Next',
                                'total' => $products->max_num_pages,
                                'current' => $current_page,
                                'type' => 'list'
                            ));
                            ?>
                        </nav>
                    <?php endif; ?>

                <?php else : ?>
                    <div class="no-products-found">
                        <p>No products found matching your criteria.</p>
                        <?php if ($search_query || $category_filter) : ?>
                            <a href="<?php echo esc_url(remove_query_arg(array('product_search', 'category_filter'))); ?>" class="clear-filters">Clear Filters</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
    jQuery(document).ready(function($) {
        var currentCategory = $('.deva-product-search-section').data('current-category');
        
        // Handle sort dropdown change
        $('#sort_by').on('change', function() {
            var sortValue = $(this).val();
            var url = new URL(window.location);
            url.searchParams.set('sort_by', sortValue);
            url.searchParams.delete('paged'); // Reset to first page
            
            // Preserve current category context
            if (currentCategory) {
                url.searchParams.set('current_category', currentCategory);
            }
            
            window.location.href = url.toString();
        });

        // Enhance form submission to preserve category context
        $('.product-category-form').on('submit', function() {
            if (currentCategory && !$('input[name="current_category"]').val()) {
                $(this).append('<input type="hidden" name="current_category" value="' + currentCategory + '" />');
            }
        });

        // Handle add to cart button
        $('.add-to-cart-btn').on('click', function(e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            var $button = $(this);
            
            $button.addClass('loading').text('Adding...');
            
            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'woocommerce_add_to_cart',
                    product_id: productId,
                    quantity: 1
                },
                success: function(response) {
                    if (response.error) {
                        alert('Error: ' + response.error);
                    } else {
                        $button.removeClass('loading').text('Added!');
                        // Update cart fragments if available
                        $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                        
                        setTimeout(function() {
                            $button.text('Add to Cart');
                        }, 2000);
                    }
                },
                error: function() {
                    $button.removeClass('loading').text('Add to Cart');
                    alert('Error adding product to cart');
                }
            });
        });
    });
    </script>
    <?php

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('deva_product_category', 'deva_product_category_shortcode');

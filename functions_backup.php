<?php

/**
 * Hello Elementor Child Theme Functions
 * Clean WooCommerce theme without Elementor dependencies
 * 
 * @package HelloElementorChild
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme version
 */
define('HELLO_ELEMENTOR_CHILD_VERSION', '1.0.0');

/**
 * Enqueue parent theme styles
 */
function hello_elementor_child_enqueue_styles()
{
    wp_enqueue_style(
        'hello-elementor-child-style',
        get_stylesheet_uri(),
        array('hello-elementor-theme-style'),
        HELLO_ELEMENTOR_CHILD_VERSION
    );
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles');

/**
 * Enqueue assets
 */
function hello_elementor_child_enqueue_assets()
{
    // Enqueue WooCommerce CSS
    wp_enqueue_style(
        'hello-elementor-child-woocommerce',
        get_stylesheet_directory_uri() . '/assets/css/woocommerce.css',
        array('woocommerce-general'),
        HELLO_ELEMENTOR_CHILD_VERSION
    );

    // Enqueue shop JavaScript
    wp_enqueue_script(
        'hello-elementor-child-shop',
        get_stylesheet_directory_uri() . '/assets/js/shop.js',
        array('jquery'),
        HELLO_ELEMENTOR_CHILD_VERSION,
        true
    );

    // Localize script for AJAX
    wp_localize_script('hello-elementor-child-shop', 'deva_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('deva_products_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_assets');

/**
 * WooCommerce support
 */
function hello_elementor_child_woocommerce_support()
{
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'hello_elementor_child_woocommerce_support');

/**
 * DEVA Shortcodes
 */
require_once get_stylesheet_directory() . '/inc/shortcodes-loader.php';

// Remove sale flash from default position and reposition it
function deva_reposition_sale_flash() {
    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
}
add_action('init', 'deva_reposition_sale_flash');

// Remove default star rating from product loops
function deva_remove_loop_rating() {
    remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
}
add_action('init', 'deva_remove_loop_rating');

// Change "Add to basket" button text to "Buy now"
function deva_change_add_to_cart_button_text($text, $product) {
    if ($product->get_type() === 'simple' && $product->is_purchasable() && $product->is_in_stock()) {
        return __('Buy Now', 'woocommerce');
    }
    return $text;
}
add_filter('woocommerce_product_add_to_cart_text', 'deva_change_add_to_cart_button_text', 20, 2);

// Also change the button text on single product pages
function deva_change_single_add_to_cart_button_text() {
    return __('Buy Now', 'woocommerce');
}
add_filter('woocommerce_product_single_add_to_cart_button_text', 'deva_change_single_add_to_cart_button_text');

// Categories shortcode
function deva_categories_shortcode($atts)
{
    // Use DEVA protection system (simplified version without external dependencies)
    static $execution_count = 0;
    $execution_count++;

    if ($execution_count > 3) {
        return '<p>Categories loading...</p>';
    }

    $atts = shortcode_atts(array(
        'columns' => 4,
        'hide_empty' => true,
        'limit' => 5,
    ), $atts);

    ob_start();

    // Get WooCommerce product categories
    $categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => $atts['hide_empty'],
        'number' => $atts['limit'],
        'exclude' => array(get_option('default_product_cat')) // Exclude uncategorized
    ));

    if (empty($categories) || is_wp_error($categories)) {
        $execution_count--;
        return '<p>No categories found.</p>';
    }

    // Add class if there are exactly 5 categories
    $grid_class = (count($categories) === 5) ? 'has-fifth-card' : '';
?>
    <section class="deva-category-section">
        <div class="elementor-container elementor-column-gap-default">
            <div class="category-grid <?php echo $grid_class; ?>">
                <?php
                $category_count = 1;
                foreach ($categories as $category) :
                    $category_link = get_term_link($category);
                    $products_in_cat = wc_get_products(array(
                        'category' => array($category->slug),
                        'limit' => 5,
                        'status' => 'publish'
                    ));
                ?>
                    <div class="category-card">
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
                            <span class="category-number"><?php echo sprintf('%02d', $category_count); ?></span>
                            <a href="<?php echo esc_url($category_link); ?>" class="category-btn-link">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8.59 16.59L13.17 12 8.59 7.41 10 6l6 6-6 6-1.41-1.41z" />
                                </svg>
                            </a>
                        </div>
                    </div>
                <?php
                    $category_count++;
                endforeach; ?>
            </div>
        </div>
    </section>
<?php

    $output = ob_get_clean();
    $execution_count--;
    return $output;
}
add_shortcode('deva_categories', 'deva_categories_shortcode');


// Product Grid Shortcode
function deva_product_grid_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'columns' => 4,
        'per_page' => 12,
        'class' => '',
        'pagination' => 'true',
        'ajax' => 'true'
    ), $atts);

    // Generate unique ID for this shortcode instance
    $shortcode_id = 'deva-products-' . uniqid();

    ob_start();

    // Get current page for pagination
    $paged = (isset($_POST['paged']) && $_POST['paged']) ? intval($_POST['paged']) : 1;
    if (!$paged) {
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        if (is_front_page()) {
            $paged = (get_query_var('page')) ? get_query_var('page') : 1;
        }
    }
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

// Helper function to get products HTML
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
        woocommerce_product_loop_start();

        while ($products->have_posts()) :
            $products->the_post();
            wc_get_template_part('content', 'product');
        endwhile;

        woocommerce_product_loop_end();
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

// AJAX handler for loading products
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
add_shortcode('deva_products', 'deva_product_grid_shortcode');


// Search modal shortcode
function deva_search_modal_shortcode($atts)
{
    ob_start();
?>
    <div id="searchModal" class="search-modal" style="display: none;">
        <div class="search-modal-content">
            <span class="search-close">&times;</span>
            <div class="search-form">
                <?php echo get_product_search_form(); ?>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('deva_search_modal', 'deva_search_modal_shortcode');

// Category Display 50/50 Shortcode
function deva_category_display_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'hide_empty' => true,
        'current_page' => 1,
        'class' => ''
    ), $atts);

    // Get current page from URL parameter or shortcode attribute
    $current_page = isset($_GET['cat_page']) ? intval($_GET['cat_page']) : intval($atts['current_page']);
    $current_page = max(1, $current_page); // Ensure minimum page is 1

    ob_start();

    // Get WooCommerce product categories ordered by ID (creation order)
    $categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => $atts['hide_empty'],
        'orderby' => 'term_id',
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
            'orderby' => 'term_id',
            'order' => 'ASC'
        ));
        
        if (empty($categories)) {
            return '<div class="deva-category-display-section"><div class="elementor-container"><p style="text-align: center; padding: 40px; color: #666;">No product categories found. Please create some product categories in WooCommerce.</p></div></div>';
        }
    }

    // Re-index the array to ensure numerical indices starting from 0
    $categories = array_values($categories);
    $total_categories = count($categories);
    
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

    // Generate navigation URLs
    $current_url = remove_query_arg('cat_page');
    $prev_url = $current_page > 1 ? add_query_arg('cat_page', $current_page - 1, $current_url) : '';
    $next_url = $current_page < $total_categories ? add_query_arg('cat_page', $current_page + 1, $current_url) : '';
?>
    <section class="deva-category-display-section <?php echo esc_attr($atts['class']); ?>">
        <div class="elementor-container elementor-column-gap-default">
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
                    </div>

                    <div class="category-footer">
                        <span class="category-number"><?php echo sprintf('%02d', $current_page); ?></span>
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
            <nav class="woocommerce-pagination">
                <ul>
                    <?php if ($prev_url) : ?>
                        <li class="prev">
                            <a href="<?php echo esc_url($prev_url); ?>">Previous</a>
                        </li>
                    <?php else : ?>
                        <li class="prev">
                            <span class="disabled">Previous</span>
                        </li>
                    <?php endif; ?>

                    <li>
                        <span class="current"><?php echo $current_page; ?></span>
                    </li>

                    <li>
                        <span class="page-info">/ <?php echo $total_categories; ?></span>
                    </li>

                    <?php if ($next_url) : ?>
                        <li class="next">
                            <a href="<?php echo esc_url($next_url); ?>">Next</a>
                        </li>
                    <?php else : ?>
                        <li class="next">
                            <span class="disabled">Next</span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </section>
<?php
    return ob_get_clean();
}
add_shortcode('deva_category_display', 'deva_category_display_shortcode');

/**
 * DEVA Comprehensive Product Search and Filter Shortcode
 */
function deva_product_search_shortcode($atts) {
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

    // Debug information (remove this after testing)
    $debug_info = '';
    if (current_user_can('manage_options')) { // Only show to admins
        $debug_info = '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; font-size: 12px; border-radius: 5px;">';
        $debug_info .= '<strong>Debug Info:</strong><br>';
        $debug_info .= 'Category Context: ' . $current_category_context . '<br>';
        $debug_info .= 'Category Filter: ' . $category_filter . '<br>';
        $debug_info .= 'Search Query: ' . $search_query . '<br>';
        $debug_info .= 'Total Found: ' . $total_products . '<br>';
        $debug_info .= 'Query Args: ' . print_r($query_args, true);
        $debug_info .= '</div>';
    }

    // Get all categories for filter dropdown
    $categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'exclude' => array(get_option('default_product_cat'))
    ));

    ob_start();
    ?>
    <section class="deva-shop-section deva-product-search-section <?php echo esc_attr($atts['class']); ?>" 
             data-ajax="<?php echo esc_attr($atts['ajax']); ?>"
             data-current-category="<?php echo esc_attr($current_category_context); ?>">
        <div class="elementor-container elementor-column-gap-default">
            
            <!-- Debug Info (for admins only) -->
            <?php echo $debug_info; ?>
            
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
                <form method="get" class="product-search-form">
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
                    <?php woocommerce_product_loop_start(); ?>
                        <?php while ($products->have_posts()) : $products->the_post(); ?>
                            <?php
                            global $product;
                            // Use custom product template for search shortcode
                            ?>
                            <li <?php wc_product_class('', $product); ?>>
                                <?php do_action('woocommerce_before_shop_loop_item'); ?>

                                <div class="product-image-wrapper">
                                    <?php do_action('woocommerce_before_shop_loop_item_title'); ?>
                                    
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

                                <div class="product-info-wrapper search-product-info">
                                    <!-- Product Name -->
                                    <?php do_action('woocommerce_shop_loop_item_title'); ?>
                                    
                                    <!-- Product Description -->
                                    <div class="product-description">
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

                                    <!-- Action Buttons Row -->
                                    <div class="product-actions">
                                        <?php if ($product->is_purchasable() && $product->is_in_stock()) : ?>
                                            <button class="add-to-cart-btn" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                                                Add to Cart
                                            </button>
                                            <a href="<?php echo esc_url($product->get_permalink()); ?>" class="buy-now-btn">
                                                Buy Now
                                            </a>
                                        <?php else : ?>
                                            <span class="out-of-stock">Out of Stock</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Only call the link close hook, skip the add to cart hook -->
                                    <?php do_action('woocommerce_after_shop_loop_item_title'); ?>
                                    <?php do_action('woocommerce_before_shop_loop_item_title'); // This closes the link ?>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php woocommerce_product_loop_end(); ?>

                    <!-- Pagination -->
                    <?php if ($atts['pagination'] === 'true' && $products->max_num_pages > 1) : ?>
                        <nav class="woocommerce-pagination">
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
        $('.product-search-form').on('submit', function() {
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
add_shortcode('deva_product_search', 'deva_product_search_shortcode');



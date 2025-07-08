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
    // Enqueue component-based CSS files
    wp_enqueue_style(
        'deva-base',
        get_stylesheet_directory_uri() . '/assets/css/base.css',
        array('woocommerce-general'),
        HELLO_ELEMENTOR_CHILD_VERSION
    );

    wp_enqueue_style(
        'deva-hero',
        get_stylesheet_directory_uri() . '/assets/css/hero.css',
        array('deva-base'),
        HELLO_ELEMENTOR_CHILD_VERSION
    );

    wp_enqueue_style(
        'deva-categories',
        get_stylesheet_directory_uri() . '/assets/css/categories.css',
        array('deva-base'),
        HELLO_ELEMENTOR_CHILD_VERSION
    );

    wp_enqueue_style(
        'deva-products',
        get_stylesheet_directory_uri() . '/assets/css/products.css',
        array('deva-base'),
        HELLO_ELEMENTOR_CHILD_VERSION
    );

    wp_enqueue_style(
        'deva-reviews',
        get_stylesheet_directory_uri() . '/assets/css/reviews.css',
        array('deva-base'),
        HELLO_ELEMENTOR_CHILD_VERSION
    );

    wp_enqueue_style(
        'deva-single-product',
        get_stylesheet_directory_uri() . '/assets/css/single-product.css',
        array('deva-base'),
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

    // Enqueue reviews slider JavaScript
    wp_enqueue_script(
        'deva-reviews-slider',
        get_stylesheet_directory_uri() . '/assets/js/reviews-slider.js',
        array(),
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
 * WooCommerce Customizations
 */

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

/**
 * Enqueue Carbon Fields admin script and styles
 */
function deva_enqueue_carbon_fields_admin_script($hook) {
    // Only load on post edit pages
    if ($hook == 'post.php' || $hook == 'post-new.php') {
        global $post_type;
        // Only load for products
        if ($post_type == 'product') {
            wp_enqueue_script(
                'deva-carbon-fields-admin',
                get_stylesheet_directory_uri() . '/assets/js/carbon-fields-admin.js',
                array('jquery'),
                HELLO_ELEMENTOR_CHILD_VERSION,
                true
            );
            
            wp_enqueue_style(
                'deva-carbon-fields-admin',
                get_stylesheet_directory_uri() . '/assets/css/carbon-fields-admin.css',
                array(),
                HELLO_ELEMENTOR_CHILD_VERSION
            );
        }
    }
}
add_action('admin_enqueue_scripts', 'deva_enqueue_carbon_fields_admin_script');

/**
 * Carbon Fields for Custom Fields
 */
// Load Carbon Fields
require_once get_stylesheet_directory() . '/vendor/autoload.php';
require_once get_stylesheet_directory() . '/inc/carbon-fields.php';

/**
 * DEVA Shortcodes
 */
require_once get_stylesheet_directory() . '/inc/shortcodes-loader.php';

/**
 * DEVA Helper Functions
 */

/**
 * Get product excerpt - uses short description if available, otherwise gets first words from description
 *
 * @param WC_Product $product The product object
 * @param int $word_limit Number of words to extract (default: 15)
 * @return string Product excerpt
 */
function deva_get_product_excerpt($product, $word_limit = 15) {
    if (!$product || !is_object($product) || !method_exists($product, 'get_short_description')) {
        return '';
    }
    
    // Ensure we have a valid word limit
    $word_limit = max(1, intval($word_limit));
    
    // First try to get short description
    $short_description = $product->get_short_description();
    
    if (!empty($short_description)) {
        // If short description exists, trim it to word limit
        if (function_exists('wp_trim_words')) {
            return wp_trim_words($short_description, $word_limit, '...');
        } else {
            // Fallback manual word trimming
            $words = explode(' ', strip_tags($short_description));
            if (count($words) > $word_limit) {
                return implode(' ', array_slice($words, 0, $word_limit)) . '...';
            }
            return strip_tags($short_description);
        }
    }
    
    // If no short description, get from main description
    $description = $product->get_description();
    
    if (!empty($description)) {
        // Strip HTML tags and get plain text
        $plain_text = function_exists('wp_strip_all_tags') ? wp_strip_all_tags($description) : strip_tags($description);
        
        // Remove extra whitespace and newlines
        $plain_text = preg_replace('/\s+/', ' ', trim($plain_text));
        
        // Get first words
        if (function_exists('wp_trim_words')) {
            return wp_trim_words($plain_text, $word_limit, '...');
        } else {
            // Fallback manual word trimming
            $words = explode(' ', $plain_text);
            if (count($words) > $word_limit) {
                return implode(' ', array_slice($words, 0, $word_limit)) . '...';
            }
            return $plain_text;
        }
    }
    
    // Fallback: try to get excerpt from post content
    if (function_exists('get_the_excerpt')) {
        $post_excerpt = get_the_excerpt($product->get_id());
        if (!empty($post_excerpt)) {
            if (function_exists('wp_trim_words')) {
                return wp_trim_words($post_excerpt, $word_limit, '...');
            } else {
                // Fallback manual word trimming
                $words = explode(' ', strip_tags($post_excerpt));
                if (count($words) > $word_limit) {
                    return implode(' ', array_slice($words, 0, $word_limit)) . '...';
                }
                return strip_tags($post_excerpt);
            }
        }
    }
    
    // Final fallback
    return 'No description available.';
}

/**
 * Get clean product description for display
 *
 * @param WC_Product $product The product object
 * @param int $char_limit Character limit (default: 100)
 * @return string Clean product description
 */
function deva_get_clean_product_description($product, $char_limit = 100) {
    if (!$product) {
        return '';
    }
    
    $description = deva_get_product_excerpt($product, 50); // Get more words first
    
    if (strlen($description) > $char_limit) {
        $description = substr($description, 0, $char_limit);
        // Ensure we don't cut off in the middle of a word
        $description = substr($description, 0, strrpos($description, ' ')) . '...';
    }
    
    return $description;
}

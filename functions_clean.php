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
 * DEVA Shortcodes
 */
require_once get_stylesheet_directory() . '/inc/shortcodes-loader.php';

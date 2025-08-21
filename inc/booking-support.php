<?php
/**
 * Deva Theme - WooCommerce Bookings Support
 * Additional functions to ensure booking products work properly
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure WooCommerce Bookings templates load correctly
add_action('after_setup_theme', 'deva_add_booking_support');
function deva_add_booking_support()
{
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    // Ensure booking scripts are loaded
    if (class_exists('WC_Bookings')) {
        add_action('wp_enqueue_scripts', 'deva_enqueue_booking_scripts');
    }
}

function deva_enqueue_booking_scripts()
{
    if (is_product()) {
        global $product;
        if ($product && method_exists($product, 'get_type') && $product->get_type() === 'booking') {
            // Ensure booking scripts are properly loaded
            wp_enqueue_script('wc-bookings-booking-form');
            wp_enqueue_style('wc-bookings-styles');
        }
    }
}

// Fix booking add to cart URL
add_filter('woocommerce_product_add_to_cart_url', 'deva_booking_add_to_cart_url', 10, 2);
function deva_booking_add_to_cart_url($url, $product)
{
    if ($product && method_exists($product, 'get_type') && $product->get_type() === 'booking') {
        return get_permalink($product->get_id());
    }
    return $url;
}

// Ensure booking products show correct add to cart text
add_filter('woocommerce_product_add_to_cart_text', 'deva_booking_add_to_cart_text', 10, 2);
function deva_booking_add_to_cart_text($text, $product)
{
    if ($product && method_exists($product, 'get_type') && $product->get_type() === 'booking') {
        if ($product->is_purchasable()) {
            return __('Book Now', 'woocommerce');
        } else {
            return __('Read More', 'woocommerce');
        }
    }
    return $text;
}

// Debug function to check booking product status
function deva_debug_booking_product($product_id)
{
    $product = wc_get_product($product_id);
    if ($product) {
        error_log('Product ID: ' . $product_id);
        error_log('Product Type: ' . $product->get_type());
        error_log('Is Purchasable: ' . ($product->is_purchasable() ? 'Yes' : 'No'));
        error_log('Class: ' . get_class($product));

        if (method_exists($product, 'get_booking_form')) {
            error_log('Has booking form method: Yes');
        } else {
            error_log('Has booking form method: No');
        }
    }
}

// Add debug info to admin - only when specifically requested and only once per session
if (is_admin() && current_user_can('manage_options')) {
    add_action('admin_init', 'deva_booking_debug_handler');
}

function deva_booking_debug_handler()
{
    // Only show debug info when specifically requested and not already shown
    if (isset($_GET['debug_booking']) && $_GET['debug_booking'] == '1' && !get_transient('deva_booking_debug_shown')) {
        add_action('admin_notices', 'deva_booking_debug_notice');
        // Set transient to prevent showing again for 1 hour
        set_transient('deva_booking_debug_shown', true, HOUR_IN_SECONDS);
    }
}

function deva_booking_debug_notice()
{
    $bookings_active = class_exists('WC_Bookings') ? 'Active' : 'Not Active';
    echo '<div class="notice notice-info is-dismissible"><p><strong>Deva Booking Debug:</strong> WooCommerce Bookings is ' . $bookings_active . '</p></div>';

    if (class_exists('WC_Bookings')) {
        $version = defined('WC_BOOKINGS_VERSION') ? WC_BOOKINGS_VERSION : 'Unknown';
        echo '<div class="notice notice-success is-dismissible"><p><strong>Deva Booking Debug:</strong> WooCommerce Bookings Version: ' . $version . '</p></div>';
    }
}

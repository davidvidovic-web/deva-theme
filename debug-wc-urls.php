<?php
/**
 * Debug WooCommerce URL structure
 * Add this to your functions.php temporarily to debug the issue
 */

// Debug function to check WooCommerce URL structure
function debug_woocommerce_urls() {
    if (!function_exists('WC') || !is_admin()) {
        return;
    }
    
    error_log('=== WooCommerce URL Debug ===');
    error_log('Checkout URL: ' . wc_get_checkout_url());
    error_log('Checkout Page ID: ' . wc_get_page_id('checkout'));
    error_log('Checkout Page Permalink: ' . get_permalink(wc_get_page_id('checkout')));
    
    // Check if checkout page exists and has proper slug
    $checkout_page = get_post(wc_get_page_id('checkout'));
    if ($checkout_page) {
        error_log('Checkout Page Slug: ' . $checkout_page->post_name);
        error_log('Checkout Page Status: ' . $checkout_page->post_status);
    } else {
        error_log('ERROR: Checkout page not found!');
    }
    
    // Check WooCommerce endpoints
    $endpoints = WC()->query->get_query_vars();
    error_log('WC Endpoints: ' . print_r($endpoints, true));
    
    // Check if order-received endpoint is properly set
    if (isset($endpoints['order-received'])) {
        error_log('Order-received endpoint: ' . $endpoints['order-received']);
    }
    
    error_log('=== End WooCommerce URL Debug ===');
}
add_action('admin_init', 'debug_woocommerce_urls');

<?php
/**
 * DEVA Theme: Check for conflicting pages/posts
 * 
 * This script will help identify if there are any pages or posts
 * with slugs that conflict with WooCommerce endpoints
 */

// Check for conflicting pages
function deva_check_conflicting_pages() {
    global $wpdb;
    
    // Check for pages/posts with 'order-received' slug
    $conflicting_posts = $wpdb->get_results(
        "SELECT ID, post_title, post_name, post_type, post_status 
         FROM {$wpdb->posts} 
         WHERE post_name = 'order-received' 
         AND post_status IN ('publish', 'draft', 'private')"
    );
    
    if (!empty($conflicting_posts)) {
        echo "FOUND CONFLICTING POSTS/PAGES WITH 'order-received' SLUG:\n";
        foreach ($conflicting_posts as $post) {
            echo "- ID: {$post->ID}, Title: '{$post->post_title}', Type: {$post->post_type}, Status: {$post->post_status}\n";
            echo "  Edit URL: " . admin_url("post.php?post={$post->ID}&action=edit") . "\n";
        }
        echo "\nTO FIX: Delete or change the slug of these conflicting posts.\n\n";
    } else {
        echo "No conflicting posts found with 'order-received' slug.\n\n";
    }
    
    // Check WooCommerce pages setup
    echo "WOOCOMMERCE PAGE SETUP:\n";
    $wc_pages = array(
        'shop' => 'Shop Page',
        'cart' => 'Cart Page', 
        'checkout' => 'Checkout Page',
        'myaccount' => 'My Account Page'
    );
    
    foreach ($wc_pages as $key => $name) {
        $page_id = wc_get_page_id($key);
        if ($page_id > 0) {
            $page = get_post($page_id);
            echo "- {$name}: ID {$page_id}, Slug: '{$page->post_name}', Status: {$page->post_status}\n";
            echo "  URL: " . get_permalink($page_id) . "\n";
        } else {
            echo "- {$name}: NOT SET!\n";
        }
    }
}

// Only run this if accessed directly or in WordPress admin
if (defined('WP_CLI') || (isset($_GET['debug_wc']) && current_user_can('manage_options'))) {
    deva_check_conflicting_pages();
}
?>

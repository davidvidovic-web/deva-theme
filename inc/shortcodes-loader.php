<?php

/**
 * DEVA Shortcodes Loader
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load all shortcode files
require_once get_stylesheet_directory() . '/inc/shortcodes/deva-categories.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/deva-products.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/deva-products-slider.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/deva-category-display.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/deva-product-category.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/deva-reviews.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/deva-single-product.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/deva-wishlist.php';

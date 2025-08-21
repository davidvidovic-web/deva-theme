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
 * Load DEVA shortcodes
 */
require_once get_stylesheet_directory() . '/inc/shortcodes-loader.php';

/**
 * Enqueue parent theme styles
 */
function hello_elementor_child_enqueue_styles()
{
    // First, enqueue parent theme style
    wp_enqueue_style(
        'hello-elementor-theme-style',
        get_template_directory_uri() . '/style.css',
        array(),
        wp_get_theme()->parent()->get('Version')
    );
    
    // Then enqueue child theme style to override parent
    wp_enqueue_style(
        'hello-elementor-child-style',
        get_stylesheet_uri(),
        array('hello-elementor-theme-style'), // Depend on parent to ensure it loads after
        HELLO_ELEMENTOR_CHILD_VERSION
    );
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_enqueue_styles');

/**
 * Enqueue assets
 */
function hello_elementor_child_enqueue_assets()
{
    // Enqueue component-based CSS files - all depend on child theme style for priority
    wp_enqueue_style(
        'deva-base',
        get_stylesheet_directory_uri() . '/assets/css/base.css',
        array('hello-elementor-child-style', 'woocommerce-general'),
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

    wp_enqueue_style(
        'deva-products-slider',
        get_stylesheet_directory_uri() . '/assets/css/products-slider.css',
        array('deva-base'),
        HELLO_ELEMENTOR_CHILD_VERSION
    );

    wp_enqueue_style(
        'deva-notifications',
        get_stylesheet_directory_uri() . '/assets/css/notifications.css',
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

    // Localize script for AJAX (immediately after enqueuing)
    wp_localize_script('hello-elementor-child-shop', 'shop_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('shop_nonce'),
        'is_user_logged_in' => is_user_logged_in(),
        'debug' => WP_DEBUG,
        'version' => HELLO_ELEMENTOR_CHILD_VERSION
    ));

    // Enqueue reviews slider JavaScript
    wp_enqueue_script(
        'deva-reviews-slider',
        get_stylesheet_directory_uri() . '/assets/js/reviews-slider.js',
        array(),
        HELLO_ELEMENTOR_CHILD_VERSION,
        true
    );

    // Enqueue notifications JavaScript
    wp_enqueue_script(
        'deva-notifications',
        get_stylesheet_directory_uri() . '/assets/js/notifications.js',
        array(),
        HELLO_ELEMENTOR_CHILD_VERSION,
        true
    );
    
    // Add WooCommerce cart data for AJAX functionality
    if (class_exists('WooCommerce')) {
        wp_localize_script('hello-elementor-child-shop', 'deva_wc_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'wc_ajax_url' => WC_AJAX::get_endpoint('%%endpoint%%'),
            'cart_url' => wc_get_cart_url(),
            'checkout_url' => wc_get_checkout_url(),
            'shop_url' => get_permalink(wc_get_page_id('shop')),
            'currency_symbol' => get_woocommerce_currency_symbol(),
            'is_cart' => is_cart(),
            'is_checkout' => is_checkout(),
            'cart_hash' => WC()->cart ? WC()->cart->get_cart_hash() : '',
            'cart_redirect_after_add' => get_option('woocommerce_cart_redirect_after_add')
        ));
    }

    // Enqueue cart CSS and JS with high priority to override Elementor
    if (is_cart()) {
        wp_enqueue_style(
            'deva-cart',
            get_stylesheet_directory_uri() . '/assets/css/cart.css',
            array('deva-base', 'elementor-frontend'),
            HELLO_ELEMENTOR_CHILD_VERSION,
            'all'
        );
        
        wp_enqueue_script(
            'deva-cart',
            get_stylesheet_directory_uri() . '/assets/js/cart.js',
            array('jquery', 'hello-elementor-child-shop'),
            HELLO_ELEMENTOR_CHILD_VERSION,
            true
        );
        
        // Ensure cart.js has access to AJAX data
        wp_localize_script('deva-cart', 'deva_cart_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wc_cart_item_quantity_update'),
            'remove_nonce' => wp_create_nonce('wc_cart_item_remove'),
            'security' => wp_create_nonce('shop_nonce'),
            'shop_nonce' => wp_create_nonce('shop_nonce'),
            'is_user_logged_in' => is_user_logged_in(),
            'cart_url' => wc_get_cart_url(),
            'shop_url' => get_permalink(wc_get_page_id('shop'))
        ));
    }

    // Enqueue checkout CSS with high priority to override Elementor
    if (is_checkout()) {
        wp_enqueue_style(
            'deva-checkout',
            get_stylesheet_directory_uri() . '/assets/css/checkout.css',
            array('deva-base', 'elementor-frontend'),
            HELLO_ELEMENTOR_CHILD_VERSION,
            'all'
        );
    }
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
 * Customize WooCommerce checkout fields
 */
function deva_customize_checkout_fields($fields) {
    // Customize billing fields
    $fields['billing']['billing_first_name'] = array(
        'label' => __('First Name', 'woocommerce'),
        'placeholder' => __('Enter your first name', 'hello-elementor-child'),
        'required' => true,
        'class' => array('form-row-first', 'deva-form-group'),
        'priority' => 10,
    );

    $fields['billing']['billing_last_name'] = array(
        'label' => __('Last Name', 'woocommerce'),
        'placeholder' => __('Enter your last name', 'hello-elementor-child'),
        'required' => true,
        'class' => array('form-row-last', 'deva-form-group'),
        'priority' => 20,
    );

    $fields['billing']['billing_email'] = array(
        'label' => __('Email Address', 'woocommerce'),
        'placeholder' => __('Enter your email address', 'hello-elementor-child'),
        'required' => true,
        'type' => 'email',
        'class' => array('form-row-wide', 'deva-form-group'),
        'priority' => 30,
        'validate' => array('email'),
    );

    $fields['billing']['billing_phone'] = array(
        'label' => __('Phone Number', 'woocommerce'),
        'placeholder' => __('Enter your phone number', 'hello-elementor-child'),
        'required' => true,
        'type' => 'tel',
        'class' => array('form-row-wide', 'deva-form-group'),
        'priority' => 40,
        'validate' => array('phone'),
    );

    $fields['billing']['billing_country'] = array(
        'label' => __('Country / Region', 'woocommerce'),
        'required' => true,
        'type' => 'country',
        'class' => array('form-row-wide', 'deva-form-group', 'address-field', 'update_totals_on_change'),
        'priority' => 50,
    );

    $fields['billing']['billing_address_1'] = array(
        'label' => __('Address Line 1', 'woocommerce'),
        'placeholder' => __('House number and street name', 'hello-elementor-child'),
        'required' => true,
        'class' => array('form-row-wide', 'deva-form-group', 'address-field'),
        'priority' => 60,
    );

    $fields['billing']['billing_address_2'] = array(
        'label' => __('Address Line 2', 'woocommerce'),
        'placeholder' => __('Apartment, suite, unit, etc. (optional)', 'hello-elementor-child'),
        'required' => false,
        'class' => array('form-row-wide', 'deva-form-group', 'address-field'),
        'priority' => 70,
    );

    $fields['billing']['billing_city'] = array(
        'label' => __('City', 'woocommerce'),
        'placeholder' => __('Enter your city', 'hello-elementor-child'),
        'required' => true,
        'class' => array('form-row-first', 'deva-form-group', 'address-field'),
        'priority' => 80,
    );

    $fields['billing']['billing_state'] = array(
        'label' => __('State / County', 'woocommerce'),
        'required' => true,
        'type' => 'state',
        'class' => array('form-row-last', 'deva-form-group', 'address-field'),
        'priority' => 90,
        'validate' => array('state'),
    );

    $fields['billing']['billing_postcode'] = array(
        'label' => __('ZIP Code', 'woocommerce'),
        'placeholder' => __('Enter your ZIP code', 'hello-elementor-child'),
        'required' => true,
        'class' => array('form-row-wide', 'deva-form-group', 'address-field'),
        'priority' => 100,
        'validate' => array('postcode'),
    );

    // Remove company field if not needed
    unset($fields['billing']['billing_company']);

    // Customize shipping fields to match billing
    if (isset($fields['shipping'])) {
        $fields['shipping']['shipping_first_name'] = array(
            'label' => __('First Name', 'woocommerce'),
            'placeholder' => __('Enter first name', 'hello-elementor-child'),
            'required' => true,
            'class' => array('form-row-first', 'deva-form-group'),
            'priority' => 10,
        );

        $fields['shipping']['shipping_last_name'] = array(
            'label' => __('Last Name', 'woocommerce'),
            'placeholder' => __('Enter last name', 'hello-elementor-child'),
            'required' => true,
            'class' => array('form-row-last', 'deva-form-group'),
            'priority' => 20,
        );

        $fields['shipping']['shipping_country'] = array(
            'label' => __('Country / Region', 'woocommerce'),
            'required' => true,
            'type' => 'country',
            'class' => array('form-row-wide', 'deva-form-group', 'address-field', 'update_totals_on_change'),
            'priority' => 30,
        );

        $fields['shipping']['shipping_address_1'] = array(
            'label' => __('Address Line 1', 'woocommerce'),
            'placeholder' => __('House number and street name', 'hello-elementor-child'),
            'required' => true,
            'class' => array('form-row-wide', 'deva-form-group', 'address-field'),
            'priority' => 40,
        );

        $fields['shipping']['shipping_address_2'] = array(
            'label' => __('Address Line 2', 'woocommerce'),
            'placeholder' => __('Apartment, suite, unit, etc. (optional)', 'hello-elementor-child'),
            'required' => false,
            'class' => array('form-row-wide', 'deva-form-group', 'address-field'),
            'priority' => 50,
        );

        $fields['shipping']['shipping_city'] = array(
            'label' => __('City', 'woocommerce'),
            'placeholder' => __('Enter city', 'hello-elementor-child'),
            'required' => true,
            'class' => array('form-row-first', 'deva-form-group', 'address-field'),
            'priority' => 60,
        );

        $fields['shipping']['shipping_state'] = array(
            'label' => __('State / County', 'woocommerce'),
            'required' => true,
            'type' => 'state',
            'class' => array('form-row-last', 'deva-form-group', 'address-field'),
            'priority' => 70,
            'validate' => array('state'),
        );

        $fields['shipping']['shipping_postcode'] = array(
            'label' => __('ZIP Code', 'woocommerce'),
            'placeholder' => __('Enter ZIP code', 'hello-elementor-child'),
            'required' => true,
            'class' => array('form-row-wide', 'deva-form-group', 'address-field'),
            'priority' => 80,
            'validate' => array('postcode'),
        );

        // Remove company field from shipping if not needed
        unset($fields['shipping']['shipping_company']);
    }

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'deva_customize_checkout_fields');

/**
 * Add account creation checkbox with custom styling
 */
function deva_add_save_account_info_checkbox() {
    if (is_admin()) return;
    
    // Only show if user registration is enabled and user is not logged in
    if (!is_user_logged_in() && get_option('woocommerce_enable_checkout_login_reminder') === 'yes') {
        echo '<div class="deva-form-group deva-save-info-group">';
        echo '<label class="deva-checkbox-label">';
        echo '<input type="checkbox" id="createaccount" name="createaccount" value="1" class="deva-checkbox" />';
        echo '<span class="deva-checkbox-text">' . __('Save my information for faster checkout', 'hello-elementor-child') . '</span>';
        echo '</label>';
        echo '</div>';
    }
}
add_action('woocommerce_checkout_after_customer_details', 'deva_add_save_account_info_checkbox');

/**
 * Ensure proper field order in checkout
 */
function deva_reorder_checkout_fields($fields) {
    // Set proper priorities for billing fields
    if (isset($fields['billing'])) {
        $field_order = array(
            'billing_first_name' => 10,
            'billing_last_name' => 20,
            'billing_email' => 30,
            'billing_phone' => 40,
            'billing_country' => 50,
            'billing_address_1' => 60,
            'billing_address_2' => 70,
            'billing_city' => 80,
            'billing_state' => 90,
            'billing_postcode' => 100,
        );

        foreach ($field_order as $field_name => $priority) {
            if (isset($fields['billing'][$field_name])) {
                $fields['billing'][$field_name]['priority'] = $priority;
            }
        }
    }

    // Set proper priorities for shipping fields
    if (isset($fields['shipping'])) {
        $shipping_order = array(
            'shipping_first_name' => 10,
            'shipping_last_name' => 20,
            'shipping_country' => 30,
            'shipping_address_1' => 40,
            'shipping_address_2' => 50,
            'shipping_city' => 60,
            'shipping_state' => 70,
            'shipping_postcode' => 80,
        );

        foreach ($shipping_order as $field_name => $priority) {
            if (isset($fields['shipping'][$field_name])) {
                $fields['shipping'][$field_name]['priority'] = $priority;
            }
        }
    }

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'deva_reorder_checkout_fields', 20);

/**
 * Remove checkout fields that aren't needed
 */
function deva_remove_checkout_fields($fields) {
    // Remove company fields by default (can be re-enabled if needed)
    unset($fields['billing']['billing_company']);
    unset($fields['shipping']['shipping_company']);
    
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'deva_remove_checkout_fields', 30);

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

/**
 * Sort attribute values naturally (handles numbers properly)
 *
 * @param array $values Array of attribute values
 * @return array Sorted array of attribute values
 */
function deva_sort_attribute_values($values) {
    if (empty($values) || !is_array($values)) {
        return $values;
    }
    
    // Use natural sorting algorithm that handles numbers properly
    usort($values, function($a, $b) {
        // First, check if both values contain numbers
        $a_has_number = preg_match('/\d/', $a);
        $b_has_number = preg_match('/\d/', $b);
        
        // If both have numbers, use natural comparison
        if ($a_has_number && $b_has_number) {
            return strnatcasecmp($a, $b);
        }
        
        // If only one has numbers, prioritize numbers first
        if ($a_has_number && !$b_has_number) {
            return -1;
        }
        if (!$a_has_number && $b_has_number) {
            return 1;
        }
        
        // If neither has numbers, use regular string comparison
        return strcasecmp($a, $b);
    });
    
    return $values;
}

// Uncomment this line to test the sorting function
// add_action('init', 'deva_test_attribute_sorting');

/**
 * Handle AJAX add to cart for single product page
 */
function deva_ajax_add_to_cart() {
    // Security check
    if (!isset($_POST['product_id']) || !is_numeric($_POST['product_id'])) {
        wp_die('Invalid product ID');
    }
    
    $product_id = intval($_POST['product_id']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    // Get product attributes if any
    $attributes = array();
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'attribute_') === 0) {
            $attribute_name = str_replace('attribute_', '', $key);
            $attributes[$attribute_name] = sanitize_text_field($value);
        }
    }
    
    // Add to cart
    $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, 0, $attributes);
    
    if ($cart_item_key) {
        // Success response
        $response = array(
            'success' => true,
            'cart_hash' => WC()->cart->get_cart_hash(),
            'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array()),
            'cart_url' => wc_get_cart_url(),
            'checkout_url' => wc_get_checkout_url()
        );
        
        // Trigger cart update
        WC_AJAX::get_refreshed_fragments();
        
        wp_send_json($response);
    } else {
        // Error response
        wp_send_json(array(
            'error' => 'Failed to add product to cart. Please try again.'
        ));
    }
}

// Hook for both logged in and non-logged in users
add_action('wp_ajax_woocommerce_add_to_cart', 'deva_ajax_add_to_cart');
add_action('wp_ajax_nopriv_woocommerce_add_to_cart', 'deva_ajax_add_to_cart');

/**
 * DEVA Wishlist Functionality
 */

/**
 * Toggle product in user's wishlist
 */
function deva_toggle_favorite() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'shop_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $product_id = intval($_POST['product_id']);
    
    if (!$product_id) {
        wp_send_json_error('Invalid product ID');
        return;
    }
    
    $user_id = get_current_user_id();
    
    if ($user_id) {
        // For logged-in users, save to user meta
        $favorites = get_user_meta($user_id, 'deva_wishlist', true);
        if (!is_array($favorites)) {
            $favorites = array();
        }
        
        $key = array_search($product_id, $favorites);
        $is_favorited = false;
        
        if ($key !== false) {
            // Remove from favorites
            unset($favorites[$key]);
            $favorites = array_values($favorites); // Re-index array
        } else {
            // Add to favorites
            $favorites[] = $product_id;
            $is_favorited = true;
        }
        
        update_user_meta($user_id, 'deva_wishlist', $favorites);
        
        wp_send_json_success(array(
            'favorited' => $is_favorited,
            'count' => count($favorites),
            'message' => $is_favorited ? 'Added to wishlist' : 'Removed from wishlist'
        ));
    } else {
        // For non-logged-in users, just return success (handled client-side)
        wp_send_json_success(array(
            'favorited' => !empty($_POST['is_favorited']),
            'message' => 'Wishlist updated (stored locally)'
        ));
    }
}

// Hook for both logged in and non-logged in users
add_action('wp_ajax_toggle_favorite', 'deva_toggle_favorite');
add_action('wp_ajax_nopriv_toggle_favorite', 'deva_toggle_favorite');

/**
 * Get user's wishlist
 */
function deva_get_user_wishlist($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return array();
    }
    
    $favorites = get_user_meta($user_id, 'deva_wishlist', true);
    return is_array($favorites) ? $favorites : array();
}

/**
 * Check if product is in user's wishlist
 */
function deva_is_product_favorited($product_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $favorites = deva_get_user_wishlist($user_id);
    return in_array($product_id, $favorites);
}

/**
 * Get wishlist count for user
 */
function deva_get_wishlist_count($user_id = null) {
    $favorites = deva_get_user_wishlist($user_id);
    return count($favorites);
}

/**
 * Sync localStorage wishlist with user meta on login
 */
function deva_sync_wishlist_on_login($user_login, $user) {
    // This function can be called via AJAX to sync localStorage with user meta
    // We'll implement this in JavaScript
}

/**
 * AJAX handler to sync localStorage wishlist with user meta
 */
function deva_sync_wishlist() {
    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
        return;
    }
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'shop_nonce')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $local_favorites = json_decode(stripslashes($_POST['local_favorites']), true);
    if (!is_array($local_favorites)) {
        $local_favorites = array();
    }
    
    $user_id = get_current_user_id();
    $server_favorites = deva_get_user_wishlist($user_id);
    
    // Merge local and server favorites
    $merged_favorites = array_unique(array_merge($server_favorites, $local_favorites));
    
    // Save merged favorites
    update_user_meta($user_id, 'deva_wishlist', $merged_favorites);
    
    wp_send_json_success(array(
        'merged_favorites' => $merged_favorites,
        'count' => count($merged_favorites)
    ));
}

add_action('wp_ajax_sync_wishlist', 'deva_sync_wishlist');

/**
 * AJAX handler for updating cart item quantity
 */
function deva_update_cart_item_quantity() {
    check_ajax_referer('wc_cart_item_quantity_update', 'security');
    
    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $quantity = intval($_POST['quantity']);
    
    if (!$cart_item_key || $quantity < 0) {
        wp_send_json_error('Invalid data');
    }
    
    // Update cart
    $updated = WC()->cart->set_quantity($cart_item_key, $quantity);
    
    if ($updated) {
        // Calculate totals
        WC()->cart->calculate_totals();
        
        // Get updated cart data
        $cart_item = WC()->cart->get_cart_item($cart_item_key);
        $product = $cart_item['data'];
        
        wp_send_json_success(array(
            'cart_total' => WC()->cart->get_cart_total(),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'item_subtotal' => WC()->cart->get_product_subtotal($product, $cart_item['quantity']),
            'item_unit_price' => WC()->cart->get_product_price($product),
            'cart_hash' => WC()->cart->get_cart_hash()
        ));
    } else {
        wp_send_json_error('Failed to update quantity');
    }
}
add_action('wp_ajax_update_cart_item_quantity', 'deva_update_cart_item_quantity');
add_action('wp_ajax_nopriv_update_cart_item_quantity', 'deva_update_cart_item_quantity');

/**
 * AJAX handler for removing cart item
 */
function deva_remove_cart_item() {
    check_ajax_referer('wc_cart_item_remove', 'security');
    
    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    
    if (!$cart_item_key) {
        wp_send_json_error('Invalid cart item key');
    }
    
    // Remove item from cart
    $removed = WC()->cart->remove_cart_item($cart_item_key);
    
    if ($removed) {
        // Calculate totals
        WC()->cart->calculate_totals();
        
        wp_send_json_success(array(
            'cart_total' => WC()->cart->get_cart_total(),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_hash' => WC()->cart->get_cart_hash(),
            'cart_empty' => WC()->cart->is_empty()
        ));
    } else {
        wp_send_json_error('Failed to remove item');
    }
}
add_action('wp_ajax_remove_cart_item', 'deva_remove_cart_item');
add_action('wp_ajax_nopriv_remove_cart_item', 'deva_remove_cart_item');

/**
 * AJAX handler for getting wishlist content
 */
function deva_get_wishlist_content() {
    // Try multiple nonce validation methods to ensure compatibility
    $nonce_valid = false;
    
    // Method 1: Check shop_nonce (used by shop.js)
    if (isset($_POST['nonce']) && wp_verify_nonce($_POST['nonce'], 'shop_nonce')) {
        $nonce_valid = true;
    }
    
    // Method 2: Check security nonce (used by cart.js)
    if (!$nonce_valid && isset($_POST['security']) && wp_verify_nonce($_POST['security'], 'shop_nonce')) {
        $nonce_valid = true;
    }
    
    // Method 3: Check get_wishlist_content specific nonce
    if (!$nonce_valid && isset($_POST['security']) && wp_verify_nonce($_POST['security'], 'get_wishlist_content')) {
        $nonce_valid = true;
    }
    
    if (!$nonce_valid) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    // Get product IDs from the AJAX request
    $product_ids = array();
    if (isset($_POST['product_ids'])) {
        $product_ids = json_decode(stripslashes($_POST['product_ids']), true);
    }
    
    $show_remove_button = isset($_POST['show_remove_button']) ? $_POST['show_remove_button'] === 'true' : true;
    
    // Generate wishlist HTML directly
    ob_start();
    ?>
    <div class="deva-wishlist-display">
        <div class="deva-wishlist-header">
            <h2>My Wishlist</h2>
            <span class="wishlist-count-display">(<span class="deva-wishlist-count"><?php echo count($product_ids); ?></span> items)</span>
        </div>
        
        <div class="deva-wishlist-products">
            <?php if (empty($product_ids)): ?>
                <div class="deva-wishlist-empty">
                    <p>Your wishlist is empty.</p>
                    <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>" class="continue-shopping-btn">Continue Shopping</a>
                </div>
            <?php else: ?>
                <ul class="deva-products-grid columns-3">
                    <?php
                    foreach ($product_ids as $product_id) {
                        $product = wc_get_product($product_id);
                        if (!$product) continue;
                        
                        // Get product image
                        $image_id = $product->get_image_id();
                        $image_url = wp_get_attachment_image_src($image_id, 'woocommerce_thumbnail')[0];
                        if (!$image_url) {
                            $image_url = wc_placeholder_img_src('woocommerce_thumbnail');
                        }
                        
                        // Get product price
                        $price_html = $product->get_price_html();
                        
                        // Get product excerpt
                        $product_excerpt = $product->get_short_description();
                        if (empty($product_excerpt)) {
                            $product_excerpt = wp_trim_words($product->get_description(), 20);
                        }
                        ?>
                        <li class="deva-product-card" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                            <a href="<?php echo esc_url($product->get_permalink()); ?>" class="deva-product-link">
                                <div class="deva-product-image-container">
                                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" class="deva-product-image">
                                    
                                    <?php if ($show_remove_button): ?>
                                        <div class="deva-favorite-heart active" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M20.84 4.61012C20.3292 4.09912 19.7228 3.69376 19.0554 3.4172C18.3879 3.14064 17.6725 2.99829 16.95 2.99829C16.2275 2.99829 15.5121 3.14064 14.8446 3.4172C14.1772 3.69376 13.5708 4.09912 13.06 4.61012L12 5.67012L10.94 4.61012C9.9083 3.57842 8.50903 2.99883 7.05 2.99883C5.59096 2.99883 4.19169 3.57842 3.16 4.61012C2.1283 5.64181 1.54871 7.04108 1.54871 8.50012C1.54871 9.95915 2.1283 11.3584 3.16 12.3901L4.22 13.4501L12 21.2301L19.78 13.4501L20.84 12.3901C21.351 11.8794 21.7563 11.2729 22.0329 10.6055C22.3095 9.93801 22.4518 9.2226 22.4518 8.50012C22.4518 7.77763 22.3095 7.06222 22.0329 6.39476C21.7563 5.7273 21.351 5.12087 20.84 4.61012V4.61012Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="deva-product-content">
                                    <h3 class="deva-product-title">
                                        <?php echo esc_html($product->get_name()); ?>
                                    </h3>
                                    
                                    <div class="deva-product-price">
                                        <?php echo $price_html; ?>
                                    </div>
                                    
                                    <?php if (!empty($product_excerpt)): ?>
                                        <div class="deva-product-excerpt">
                                            <?php echo esc_html($product_excerpt); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="deva-product-actions">
                                        <button class="deva-add-to-cart-btn" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                                            <?php echo esc_html($product->single_add_to_cart_text()); ?>
                                        </button>
                                    </div>
                                </div>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    <?php
    $content = ob_get_clean();
    
    wp_send_json_success(array(
        'content' => $content,
        'html' => $content
    ));
}
add_action('wp_ajax_get_wishlist_content', 'deva_get_wishlist_content');
add_action('wp_ajax_nopriv_get_wishlist_content', 'deva_get_wishlist_content');

/**
 * High priority cart stylesheet enqueue to override Elementor
 */
function hello_elementor_child_cart_override_styles() {
    if (is_cart()) {
        wp_enqueue_style(
            'deva-cart-override',
            get_stylesheet_directory_uri() . '/assets/css/cart.css',
            array('elementor-frontend', 'elementor-common', 'elementor-pro'),
            HELLO_ELEMENTOR_CHILD_VERSION . '-override',
            'all'
        );
        
        // Add inline CSS for maximum priority
        $override_css = "
        body.woocommerce-cart .deva-button-outline,
        body.woocommerce-cart .deva-remove-item,
        body.woocommerce-cart .woocommerce-cart-form button[type='submit'],
        body.woocommerce-cart .woocommerce-cart-form input[type='submit'] {
            border: 2px solid #304624 !important;
            background: transparent !important;
            color: #304624 !important;
        }
        body.woocommerce-cart .deva-button-outline:hover,
        body.woocommerce-cart .deva-remove-item:hover,
        body.woocommerce-cart .woocommerce-cart-form button[type='submit']:hover,
        body.woocommerce-cart .woocommerce-cart-form input[type='submit']:hover {
            background: rgba(48, 70, 36, 0.05) !important;
            color: #304624 !important;
            border-color: #304624 !important;
        }";
        
        wp_add_inline_style('deva-cart-override', $override_css);
    }
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_cart_override_styles', 999);

/**
 * Account page functionality
 */

// Enqueue account page assets
function deva_enqueue_account_assets() {
    if (is_account_page()) {
        wp_enqueue_style(
            'deva-account',
            get_stylesheet_directory_uri() . '/assets/css/account.css',
            array('deva-base'),
            HELLO_ELEMENTOR_CHILD_VERSION
        );
        
        wp_enqueue_script(
            'deva-account',
            get_stylesheet_directory_uri() . '/assets/js/account.js',
            array('jquery'),
            HELLO_ELEMENTOR_CHILD_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('deva-account', 'devaAccountData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('deva_account_nonce'),
            'booking_url' => '#', // Add booking page URL if available
            'is_user_logged_in' => is_user_logged_in(),
        ));
    }
}
add_action('wp_enqueue_scripts', 'deva_enqueue_account_assets');

/**
 * Enqueue auth scripts and styles for login pages
 */
function deva_enqueue_auth_scripts() {
    global $post;
    
    // Check if we're on a page that shows the login form
    $show_auth_assets = false;
    
    if (!is_user_logged_in()) {
        // Check if it's the my account page with shortcode
        if (is_page() && $post && has_shortcode($post->post_content, 'woocommerce_my_account')) {
            $show_auth_assets = true;
        }
        
        // Check if it's the WooCommerce my account page
        if (function_exists('is_account_page') && is_account_page()) {
            $show_auth_assets = true;
        }
        
        // Check for specific login-related page templates
        if (is_page_template('page-login.php') || is_page_template('page-register.php')) {
            $show_auth_assets = true;
        }
    }
    
    if ($show_auth_assets) {
        // Enqueue Dashicons for frontend use
        wp_enqueue_style('dashicons');
        
        // Enqueue auth CSS with higher priority
        wp_enqueue_style(
            'deva-auth',
            get_stylesheet_directory_uri() . '/assets/css/auth.css',
            array('dashicons'),
            HELLO_ELEMENTOR_CHILD_VERSION
        );
        
        wp_enqueue_script(
            'deva-auth',
            get_stylesheet_directory_uri() . '/assets/js/auth.js',
            array('jquery'),
            HELLO_ELEMENTOR_CHILD_VERSION,
            true
        );
        
        wp_localize_script('deva-auth', 'deva_auth_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('deva_auth_action'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'deva_enqueue_auth_scripts', 20);

/**
 * Get user appointments/sessions
 */
function deva_get_user_appointments($user_id) {
    // This is a placeholder function. In a real implementation, you would:
    // 1. Query a custom appointments table
    // 2. Integrate with a booking plugin
    // 3. Or use post meta to store appointment data
    
    $appointments = get_user_meta($user_id, 'deva_appointments', true);
    
    if (empty($appointments)) {
        // Check if user has any completed orders (indicating they might have programs)
        $customer_orders = wc_get_orders(array(
            'customer' => $user_id,
            'status' => array('completed'),
            'limit' => 1,
        ));
        
        // If user has orders but no appointments, create sample appointments
        if (!empty($customer_orders)) {
            $sample_appointments = array(
                array(
                    'id' => 1,
                    'time' => '1:00–1:30 (GMT+0:00)',
                    'date' => '13th JUN',
                    'status' => 'upcoming',
                    'meeting_url' => '#',
                    'created' => current_time('mysql')
                ),
                array(
                    'id' => 2,
                    'time' => '1:00–1:30 (GMT+0:00)',
                    'date' => '20th JUN',
                    'status' => 'scheduled',
                    'meeting_url' => '#',
                    'created' => current_time('mysql')
                )
            );
            
            // Save sample appointments for the user
            update_user_meta($user_id, 'deva_appointments', $sample_appointments);
            return $sample_appointments;
        }
        
        // Return empty array for users with no orders
        return array();
    }
    
    return $appointments;
}

/**
 * AJAX handler for updating session data
 */
function deva_update_session_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'deva_account_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check user permissions
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to update sessions.');
        return;
    }
    
    $session_id = sanitize_text_field($_POST['session_id']);
    $field = sanitize_text_field($_POST['field']);
    $value = sanitize_text_field($_POST['value']);
    $user_id = get_current_user_id();
    
    // Validate field
    if (!in_array($field, array('time', 'date'))) {
        wp_send_json_error('Invalid field specified.');
        return;
    }
    
    // Get current appointments
    $appointments = deva_get_user_appointments($user_id);
    
    // Find and update the specific appointment
    $updated = false;
    foreach ($appointments as &$appointment) {
        if ($appointment['id'] == $session_id) {
            $appointment[$field] = $value;
            $updated = true;
            break;
        }
    }
    
    if ($updated) {
        // Save updated appointments
        update_user_meta($user_id, 'deva_appointments', $appointments);
        wp_send_json_success('Session updated successfully.');
    } else {
        wp_send_json_error('Session not found.');
    }
}
add_action('wp_ajax_deva_update_session', 'deva_update_session_ajax');

/**
 * AJAX handler for scheduling new sessions
 */
function deva_schedule_session_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'deva_account_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check user permissions
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to schedule sessions.');
        return;
    }
    
    $user_id = get_current_user_id();
    $preferred_date = sanitize_text_field($_POST['preferred_date']);
    $preferred_time = sanitize_text_field($_POST['preferred_time']);
    $notes = sanitize_textarea_field($_POST['notes']);
    
    // Validate required fields
    if (empty($preferred_date) || empty($preferred_time)) {
        wp_send_json_error('Please provide both date and time preferences.');
        return;
    }
    
    // Create scheduling request (in production, this would integrate with a booking system)
    $request_data = array(
        'user_id' => $user_id,
        'preferred_date' => $preferred_date,
        'preferred_time' => $preferred_time,
        'notes' => $notes,
        'status' => 'pending',
        'created' => current_time('mysql'),
    );
    
    // Save the request (you could use a custom post type or table for this)
    $saved = add_user_meta($user_id, 'deva_scheduling_request', $request_data);
    
    if ($saved) {
        // Send notification to admin (optional)
        $admin_email = get_option('admin_email');
        $subject = 'New Session Scheduling Request';
        $message = sprintf(
            "User %s has requested to schedule a session:\n\nDate: %s\nTime: %s\nNotes: %s",
            get_userdata($user_id)->display_name,
            $preferred_date,
            $preferred_time,
            $notes
        );
        wp_mail($admin_email, $subject, $message);
        
        wp_send_json_success('Your scheduling request has been submitted. We will contact you soon to confirm.');
    } else {
        wp_send_json_error('Failed to submit scheduling request. Please try again.');
    }
}
add_action('wp_ajax_deva_schedule_session', 'deva_schedule_session_ajax');

/**
 * Helper function to convert numbers to words (for session numbering)
 */
function number_to_words($number) {
    $words = array(
        1 => 'first',
        2 => 'second', 
        3 => 'third',
        4 => 'fourth',
        5 => 'fifth',
        6 => 'sixth',
        7 => 'seventh',
        8 => 'eighth',
        9 => 'ninth',
        10 => 'tenth'
    );
    
    return isset($words[$number]) ? $words[$number] : $number . 'th';
}

/**
 * Add wishlist endpoint to WooCommerce account menu
 */
function deva_add_wishlist_endpoint() {
    add_rewrite_endpoint('wishlist', EP_ROOT | EP_PAGES);
}
add_action('init', 'deva_add_wishlist_endpoint');

/**
 * Add wishlist tab to account menu
 */
function deva_add_wishlist_account_menu_item($items) {
    // Insert wishlist before logout
    $logout = $items['customer-logout'];
    unset($items['customer-logout']);
    
    $items['wishlist'] = __('Wishlist', 'hello-elementor-child');
    $items['customer-logout'] = $logout;
    
    return $items;
}
add_filter('woocommerce_account_menu_items', 'deva_add_wishlist_account_menu_item');

/**
 * Handle wishlist endpoint content
 */
function deva_wishlist_endpoint_content() {
    echo do_shortcode('[deva_wishlist]');
}
add_action('woocommerce_account_wishlist_endpoint', 'deva_wishlist_endpoint_content');

/**
 * Debug function to create sample appointment data for testing
 */
function deva_create_sample_appointments($user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    if (!$user_id) {
        return false;
    }
    
    $sample_appointments = array(
        array(
            'id' => 1,
            'time' => '1:00–1:30 (GMT+0:00)',
            'date' => '13th JUN',
            'status' => 'upcoming',
            'meeting_url' => 'https://zoom.us/j/sample-meeting-1',
            'created' => current_time('mysql')
        ),
        array(
            'id' => 2,
            'time' => '2:00–2:30 (GMT+0:00)',
            'date' => '20th JUN',
            'status' => 'scheduled',
            'meeting_url' => 'https://zoom.us/j/sample-meeting-2',
            'created' => current_time('mysql')
        )
    );
    
    update_user_meta($user_id, 'deva_appointments', $sample_appointments);
    return true;
}

/**
 * Add admin menu for DEVA account management (for testing)
 */
function deva_add_admin_menu() {
    add_submenu_page(
        'users.php',
        'DEVA Account Management',
        'DEVA Accounts',
        'manage_options',
        'deva-accounts',
        'deva_admin_accounts_page'
    );
}
add_action('admin_menu', 'deva_add_admin_menu');

/**
 * Admin page for managing DEVA accounts
 */
function deva_admin_accounts_page() {
    if (isset($_POST['create_sample_appointments'])) {
        $user_id = intval($_POST['user_id']);
        if ($user_id && deva_create_sample_appointments($user_id)) {
            echo '<div class="notice notice-success"><p>Sample appointments created for user ID: ' . $user_id . '</p></div>';
        }
    }
    
    ?>
    <div class="wrap">
        <h1>DEVA Account Management</h1>
        
        <div class="card">
            <h2>Create Sample Appointments</h2>
            <form method="post">
                <table class="form-table">
                    <tr>
                        <th scope="row">User ID</th>
                        <td>
                            <input type="number" name="user_id" required />
                            <p class="description">Enter the user ID to create sample appointments for.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Create Sample Appointments', 'primary', 'create_sample_appointments'); ?>
            </form>
        </div>
        
        <div class="card">
            <h2>Recent Users</h2>
            <?php
            $users = get_users(array('number' => 10, 'orderby' => 'registered', 'order' => 'DESC'));
            if (!empty($users)) {
                echo '<table class="wp-list-table widefat fixed striped">';
                echo '<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Registered</th><th>Actions</th></tr></thead>';
                echo '<tbody>';
                foreach ($users as $user) {
                    $appointments = get_user_meta($user->ID, 'deva_appointments', true);
                    $has_appointments = !empty($appointments);
                    echo '<tr>';
                    echo '<td>' . $user->ID . '</td>';
                    echo '<td>' . $user->display_name . '</td>';
                    echo '<td>' . $user->user_email . '</td>';
                    echo '<td>' . date('Y-m-d', strtotime($user->user_registered)) . '</td>';
                    echo '<td>';
                    if ($has_appointments) {
                        echo '<span style="color: green;">✓ Has appointments</span>';
                    } else {
                        echo '<form method="post" style="display: inline;">';
                        echo '<input type="hidden" name="user_id" value="' . $user->ID . '" />';
                        echo '<input type="submit" name="create_sample_appointments" value="Create Appointments" class="button button-small" />';
                        echo '</form>';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            }
            ?>
        </div>
    </div>
    <?php
}

/**
 * ===================================================================
 * DEVA Authentication System
 * ===================================================================
 */

/**
 * Override WooCommerce my account shortcode template
 */
function deva_override_my_account_shortcode($atts) {
    // Check if we're showing a password reset form or lost password form
    if (isset($_GET['show-reset-form']) || 
        (isset($_GET['action']) && ($_GET['action'] === 'rp' || $_GET['action'] === 'lost-password'))) {
        ob_start();
        include get_stylesheet_directory() . '/woocommerce/myaccount/form-lost-password.php';
        return ob_get_clean();
    }
    
    // If user is not logged in, show our custom login form
    if (!is_user_logged_in()) {
        ob_start();
        include get_stylesheet_directory() . '/woocommerce/myaccount/form-login.php';
        return ob_get_clean();
    }
    
    // If logged in, show the default account page
    return wc_get_template_html('myaccount/my-account.php');
}

// Replace the default WooCommerce my account shortcode
remove_shortcode('woocommerce_my_account');
add_shortcode('woocommerce_my_account', 'deva_override_my_account_shortcode');

/**
 * Custom login redirect
 */
function deva_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles)) {
        // Redirect customers to my account page
        if (in_array('customer', $user->roles)) {
            return wc_get_page_permalink('myaccount');
        }
        // Redirect administrators to admin dashboard
        if (in_array('administrator', $user->roles)) {
            return admin_url();
        }
    }
    return $redirect_to;
}
add_filter('login_redirect', 'deva_login_redirect', 10, 3);

/**
 * Prevent access to wp-admin for customers
 */
function deva_restrict_admin_access() {
    if (is_admin() && !current_user_can('administrator') && 
        !(defined('DOING_AJAX') && DOING_AJAX)) {
        wp_redirect(wc_get_page_permalink('myaccount'));
        exit;
    }
}
add_action('admin_init', 'deva_restrict_admin_access');

/**
 * Redirect default WordPress lost password to our custom form
 */
function deva_redirect_lost_password() {
    global $pagenow;
    
    if ($pagenow === 'wp-login.php' && isset($_GET['action']) && $_GET['action'] === 'lostpassword') {
        $redirect_url = add_query_arg('action', 'lost-password', wc_get_page_permalink('myaccount'));
        wp_redirect($redirect_url);
        exit;
    }
    
    if ($pagenow === 'wp-login.php' && isset($_GET['action']) && $_GET['action'] === 'rp') {
        $redirect_url = add_query_arg(array(
            'action' => 'rp',
            'key' => sanitize_text_field($_GET['key'] ?? ''),
            'login' => sanitize_text_field($_GET['login'] ?? ''),
            'show-reset-form' => 'true'
        ), wc_get_page_permalink('myaccount'));
        wp_redirect($redirect_url);
        exit;
    }
}
add_action('init', 'deva_redirect_lost_password');

/**
 * Customize lost password URL to point to our custom form
 */
function deva_custom_lostpassword_url($lostpassword_url, $redirect) {
    return add_query_arg('action', 'lost-password', wc_get_page_permalink('myaccount'));
}
add_filter('lostpassword_url', 'deva_custom_lostpassword_url', 10, 2);

/**
 * AJAX handler for profile updates
 */
add_action('wp_ajax_update_deva_profile', 'deva_update_profile_ajax');

function deva_update_profile_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['profile_nonce'], 'update_profile')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
        return;
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'You must be logged in to update your profile.'));
        return;
    }
    
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    
    // Sanitize and validate input data
    $first_name = sanitize_text_field($_POST['first_name'] ?? '');
    $last_name = sanitize_text_field($_POST['last_name'] ?? '');
    $display_name = sanitize_text_field($_POST['display_name'] ?? '');
    $email = sanitize_email($_POST['email'] ?? '');
    $user_url = esc_url_raw($_POST['user_url'] ?? '');
    $description = sanitize_textarea_field($_POST['description'] ?? '');
    $password = $_POST['user_pass'] ?? ''; // Don't sanitize passwords
    
    // DEVA-specific preferences
    $email_notifications = isset($_POST['deva_email_notifications']) ? '1' : '0';
    $marketing_emails = isset($_POST['deva_marketing_emails']) ? '1' : '0';
    $timezone = sanitize_text_field($_POST['deva_timezone'] ?? 'UTC');
    
    $update_data = array(
        'ID' => $user_id
    );
    
    // Validate email
    if (!empty($email) && !is_email($email)) {
        wp_send_json_error(array('message' => 'Please enter a valid email address.'));
        return;
    }
    
    // Check if email is already in use by another user
    if (!empty($email) && $email !== $current_user->user_email) {
        $email_exists = get_user_by('email', $email);
        if ($email_exists && $email_exists->ID !== $user_id) {
            wp_send_json_error(array('message' => 'This email address is already in use by another account.'));
            return;
        }
        $update_data['user_email'] = $email;
    }
    
    // Update basic user fields
    if (!empty($first_name)) {
        $update_data['first_name'] = $first_name;
    }
    
    if (!empty($last_name)) {
        $update_data['last_name'] = $last_name;
    }
    
    if (!empty($display_name)) {
        $update_data['display_name'] = $display_name;
    }
    
    if (!empty($user_url)) {
        $update_data['user_url'] = $user_url;
    }
    
    // Handle password update
    if (!empty($password)) {
        if (strlen($password) < 6) {
            wp_send_json_error(array('message' => 'Password must be at least 6 characters long.'));
            return;
        }
        $update_data['user_pass'] = $password;
    }
    
    // Update user data
    $result = wp_update_user($update_data);
    
    if (is_wp_error($result)) {
        wp_send_json_error(array('message' => 'Failed to update profile: ' . $result->get_error_message()));
        return;
    }
    
    // Update user meta fields
    if (!empty($description)) {
        update_user_meta($user_id, 'description', $description);
    }
    
    // Update DEVA preferences
    update_user_meta($user_id, 'deva_email_notifications', $email_notifications);
    update_user_meta($user_id, 'deva_marketing_emails', $marketing_emails);
    update_user_meta($user_id, 'deva_timezone', $timezone);
    
    // If password was changed, we might need to handle re-authentication
    if (!empty($password)) {
        // Re-authenticate the user with the new password
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id, true);
    }
    
    wp_send_json_success(array(
        'message' => 'Profile updated successfully!',
        'user_data' => array(
            'display_name' => get_userdata($user_id)->display_name,
            'email' => get_userdata($user_id)->user_email
        )
    ));
}

/**
 * DEVA Single Product Helper Functions
 */

/**
 * Convert WooCommerce notices to toasts
 * This ensures all WooCommerce notices are displayed as toast notifications
 */
function deva_enqueue_toast_scripts() {
    // Only enqueue on frontend
    if (!is_admin()) {
        wp_enqueue_script('deva-notifications', get_template_directory_uri() . '/assets/js/notifications.js', array('jquery'), '1.0.0', true);
        wp_enqueue_style('deva-notifications', get_template_directory_uri() . '/assets/css/notifications.css', array(), '1.0.0');
    }
}
add_action('wp_enqueue_scripts', 'deva_enqueue_toast_scripts');

/**
 * Add a script to the footer to ensure notices are converted to toasts
 */
function deva_convert_notices_to_toasts() {
    if (!is_admin()) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Convert notices after a short delay to ensure they're rendered
            setTimeout(function() {
                if (typeof convertWooCommerceNoticesToToasts === 'function') {
                    convertWooCommerceNoticesToToasts();
                }
            }, 100);
            
            // Also convert notices after AJAX updates
            jQuery(document.body).on('updated_wc_div', function() {
                setTimeout(function() {
                    if (typeof convertWooCommerceNoticesToToasts === 'function') {
                        convertWooCommerceNoticesToToasts();
                    }
                }, 100);
            });
            
            // Convert notices after cart updates
            jQuery(document.body).on('added_to_cart', function() {
                setTimeout(function() {
                    if (typeof convertWooCommerceNoticesToToasts === 'function') {
                        convertWooCommerceNoticesToToasts();
                    }
                }, 200);
            });
            
            // Convert notices after checkout updates
            jQuery(document.body).on('checkout_error', function() {
                setTimeout(function() {
                    if (typeof convertWooCommerceNoticesToToasts === 'function') {
                        convertWooCommerceNoticesToToasts();
                    }
                }, 100);
            });
        });
        </script>
        <?php
    }
}
add_action('wp_footer', 'deva_convert_notices_to_toasts');

/**
 * Hide WooCommerce notices wrapper via CSS for pages where toasts are preferred
 */
function deva_hide_notice_wrappers() {
    if (!is_admin()) {
        ?>
        <style>
        /* Hide notice wrappers on key pages - toasts will be used instead */
        body.woocommerce-account .woocommerce-notices-wrapper,
        body.single-product .woocommerce-notices-wrapper,
        body.woocommerce-cart .woocommerce-notices-wrapper,
        body.woocommerce-checkout .woocommerce-notices-wrapper {
            display: none !important;
        }
        </style>
        <?php
    }
}
add_action('wp_head', 'deva_hide_notice_wrappers');

/**
 * Test function to add WooCommerce notices for toast testing
 * Remove this in production
 */
function deva_test_woocommerce_notices() {
    if (isset($_GET['test_notices']) && is_user_logged_in()) {
        wc_add_notice('Test success message for toast conversion!', 'success');
        wc_add_notice('Test info message for toast conversion!', 'notice');
        wc_add_notice('Test error message for toast conversion!', 'error');
    }
}
add_action('init', 'deva_test_woocommerce_notices');



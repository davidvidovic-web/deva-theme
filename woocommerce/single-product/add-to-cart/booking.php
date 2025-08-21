<?php
/**
 * Booking add to cart
 * Override for WooCommerce Bookings
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/booking.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $product;

if ( ! $product->is_purchasable() ) {
    return;
}

echo wc_get_stock_html( $product ); // WPCS: XSS ok.

// Ensure Bookings form is rendered properly
if ( class_exists( 'WC_Bookings_Product' ) && $product instanceof WC_Bookings_Product ) {
    echo '<div id="deva-booking-form">';
    
    // Let WooCommerce Bookings render its form
    do_action( 'woocommerce_booking_add_to_cart' );
    
    echo '</div>';
} else {
    // Fallback for non-booking products
    woocommerce_template_single_add_to_cart();
}
?>

<style>
#deva-booking-form {
    margin: 20px 0;
}

#deva-booking-form .single_add_to_cart_button {
    background-color: #48733d !important;
    color: #fff !important;
    border: 2px solid #48733d !important;
    padding: 12px 30px !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 1px !important;
    transition: all 0.3s ease !important;
    border-radius: 4px !important;
}

#deva-booking-form .single_add_to_cart_button:hover {
    background-color: transparent !important;
    color: #48733d !important;
    border-color: #48733d !important;
}

/* Booking form styling */
.wc-bookings-booking-form {
    margin: 20px 0;
    padding: 20px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background-color: #f7fdf6;
}

.wc-bookings-booking-form .form-field {
    margin-bottom: 15px;
}

.wc-bookings-booking-form label {
    color: #48733d;
    font-weight: 600;
    margin-bottom: 5px;
    display: block;
}

.wc-bookings-booking-form select,
.wc-bookings-booking-form input[type="date"],
.wc-bookings-booking-form input[type="number"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #fff;
}

.wc-bookings-booking-form select:focus,
.wc-bookings-booking-form input:focus {
    border-color: #48733d;
    outline: none;
    box-shadow: 0 0 5px rgba(72, 115, 61, 0.3);
}
</style>

<script>
jQuery(document).ready(function($) {
    // Ensure booking form AJAX works with our theme
    $(document).on('submit', '.cart', function(e) {
        var $form = $(this);
        var $button = $form.find('.single_add_to_cart_button');
        
        // Only handle booking products
        if (!$button.hasClass('wc-booking-product-add-to-cart')) {
            return true; // Let default behavior handle non-booking products
        }
        
        // Add loading state
        $button.addClass('loading').prop('disabled', true);
        
        // Let WooCommerce Bookings handle the submission
        return true;
    });
});
</script>

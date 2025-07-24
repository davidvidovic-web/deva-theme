<?php
/**
 * DEVA Checkout form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined('ABSPATH') || exit;

/**
 * Hook: woocommerce_before_checkout_form.
 *
 * @hooked woocommerce_checkout_login_form - 10
 * @hooked woocommerce_checkout_coupon_form - 20
 */
do_action('woocommerce_before_checkout_form');

// If checkout registration is disabled and not logged in, the user cannot checkout.
if (! is_user_logged_in() && WC()->checkout()->is_registration_disabled()) {
    echo '<div class="deva-checkout-error">';
    echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
    echo '</div>';
    return;
}
?>

<div class="deva-checkout-container">
    <div class="deva-checkout-header">
        <h1 class="deva-checkout-title"><?php _e('Checkout', 'woocommerce'); ?></h1>
        <p class="deva-checkout-subtitle"><?php _e('Complete your order details below', 'hello-elementor-child'); ?></p>
    </div>

    <form name="checkout" method="post" class="checkout woocommerce-checkout deva-checkout-form" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

        <?php if (WC()->checkout()->get_checkout_fields()) : ?>

            <?php do_action('woocommerce_checkout_before_customer_details'); ?>

            <div class="deva-checkout-fields-wrapper">
                <div class="deva-checkout-customer-details" id="customer_details">
                    
                    <!-- Billing Information -->
                    <div class="deva-billing-section">
                        <h2 class="deva-section-title">
                            <svg class="deva-section-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M3 7V17C3 18.1046 3.89543 19 5 19H19C20.1046 19 21 18.1046 21 17V7M3 7L12 13L21 7M3 7L12 2L21 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <?php _e('Billing Details', 'woocommerce'); ?>
                        </h2>
                        <div class="deva-form-fields">
                            <?php do_action('woocommerce_checkout_billing'); ?>
                        </div>
                    </div>

                    <!-- Shipping Information -->
                    <div class="deva-shipping-section">
                        <h2 class="deva-section-title">
                            <svg class="deva-section-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 3H5C3.89543 3 3 3.89543 3 5V16C3 17.1046 3.89543 18 5 18H16M16 3L21 8V18C21 19.1046 20.1046 20 19 20H16M16 3V18M16 18H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <?php _e('Shipping Details', 'woocommerce'); ?>
                        </h2>
                        <div class="deva-form-fields">
                            <?php do_action('woocommerce_checkout_shipping'); ?>
                        </div>
                    </div>

                </div>

                <!-- Order Review Section -->
                <div class="deva-order-review-section">
                    
                    <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
                    
                    <h2 class="deva-section-title deva-order-title">
                        <svg class="deva-section-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 5H7C5.89543 5 5 5.89543 5 7V19C5 20.1046 5.89543 21 7 21H17C18.1046 21 19 20.1046 19 19V7C19 5.89543 18.1046 5 17 5H15M9 5C9 6.10457 9.89543 7 11 7H13C14.1046 7 15 6.10457 15 5M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5M12 12H15M12 16H15M9 12H9.01M9 16H9.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <?php esc_html_e('Your Order', 'woocommerce'); ?>
                    </h2>
                    
                    <?php do_action('woocommerce_checkout_before_order_review'); ?>

                    <div id="order_review" class="woocommerce-checkout-review-order deva-order-review">
                        <?php do_action('woocommerce_checkout_order_review'); ?>
                    </div>

                    <?php do_action('woocommerce_checkout_after_order_review'); ?>
                </div>

            </div>

            <?php do_action('woocommerce_checkout_after_customer_details'); ?>

        <?php endif; ?>

    </form>

    <?php do_action('woocommerce_after_checkout_form'); ?>
</div>

<?php
/**
 * Checkout Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined('ABSPATH') || exit;

get_header('shop');

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */
do_action('woocommerce_before_main_content');
?>

<div class="woocommerce">
    <?php
    /**
     * Hook: woocommerce_before_checkout_form.
     *
     * @hooked woocommerce_checkout_login_form - 10
     * @hooked woocommerce_checkout_coupon_form - 20
     */
    do_action('woocommerce_before_checkout_form');

    // If checkout registration is disabled and not logged in, the user cannot checkout.
    if (! is_user_logged_in() && WC()->checkout()->is_registration_disabled()) {
        echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
        return;
    }

    ?>

    <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

        <?php if (WC()->checkout()->get_checkout_fields()) : ?>

            <?php do_action('woocommerce_checkout_before_customer_details'); ?>

            <div class="col2-set" id="customer_details">
                <div class="col-1">
                    <?php do_action('woocommerce_checkout_billing'); ?>
                </div>

                <div class="col-2">
                    <?php do_action('woocommerce_checkout_shipping'); ?>
                </div>
            </div>

            <?php do_action('woocommerce_checkout_after_customer_details'); ?>

        <?php endif; ?>
        
        <?php do_action('woocommerce_checkout_before_order_review_heading'); ?>
        
        <h3 id="order_review_heading"><?php esc_html_e('Your order', 'woocommerce'); ?></h3>
        
        <?php do_action('woocommerce_checkout_before_order_review'); ?>

        <div id="order_review" class="woocommerce-checkout-review-order">
            <?php do_action('woocommerce_checkout_order_review'); ?>
        </div>

        <?php do_action('woocommerce_checkout_after_order_review'); ?>

    </form>

    <?php do_action('woocommerce_after_checkout_form'); ?>
</div>

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action('woocommerce_sidebar');

get_footer('shop');

<?php

/**
 * DEVA Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.7.0
 */

defined('ABSPATH') || exit;

// Debug: Add to browser console to confirm template is loading
wp_head();
?>

<div class="deva-thankyou-container">
    <?php if ($order) : ?>

        <?php do_action('woocommerce_before_thankyou', $order->get_id()); ?>

        <?php if ($order->has_status('failed')) : ?>

            <div class="deva-thankyou-header deva-order-failed">
                <div class="deva-thankyou-icon error">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="#e74c3c" stroke-width="2" />
                        <line x1="15" y1="9" x2="9" y2="15" stroke="#e74c3c" stroke-width="2" />
                        <line x1="9" y1="9" x2="15" y2="15" stroke="#e74c3c" stroke-width="2" />
                    </svg>
                </div>
                <h1 class="deva-thankyou-title"><?php esc_html_e('Order Failed', 'hello-elementor-child'); ?></h1>
                <p class="deva-thankyou-subtitle">
                    <?php esc_html_e('Unfortunately, your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'hello-elementor-child'); ?>
                </p>

                <div class="deva-order-details">
                    <div class="deva-order-summary">
                        <h3><?php esc_html_e('Order Details', 'hello-elementor-child'); ?></h3>
                        <div class="deva-order-info">
                            <div class="deva-order-item">
                                <span class="label"><?php esc_html_e('Order Number:', 'hello-elementor-child'); ?></span>
                                <span class="value"><?php echo $order->get_order_number(); ?></span>
                            </div>
                            <div class="deva-order-item">
                                <span class="label"><?php esc_html_e('Date:', 'hello-elementor-child'); ?></span>
                                <span class="value"><?php echo wc_format_datetime($order->get_date_created()); ?></span>
                            </div>
                            <div class="deva-order-item">
                                <span class="label"><?php esc_html_e('Status:', 'hello-elementor-child'); ?></span>
                                <span class="value status-failed"><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php else : ?>

            <div class="deva-thankyou-header deva-order-success">
                <div class="deva-thankyou-icon success">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="10" stroke="#27ae60" stroke-width="2" />
                        <path d="m9 12 2 2 4-4" stroke="#27ae60" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <h1 class="deva-thankyou-title"><?php esc_html_e('Thank You for Your Order!', 'hello-elementor-child'); ?></h1>
                <p class="deva-thankyou-subtitle">
                    <?php esc_html_e('Your order has been received and is being processed. We\'ll send you updates via email.', 'hello-elementor-child'); ?>
                </p>
            </div>

            <!-- Order Summary -->
            <div class="deva-order-summary">
                <h3><?php esc_html_e('Order Summary', 'hello-elementor-child'); ?></h3>
                <div class="deva-order-info">
                    <div class="deva-order-item">
                        <span class="label"><?php esc_html_e('Order Number:', 'hello-elementor-child'); ?></span>
                        <span class="value"><?php echo $order->get_order_number(); ?></span>
                    </div>
                    <div class="deva-order-item">
                        <span class="label"><?php esc_html_e('Date:', 'hello-elementor-child'); ?></span>
                        <span class="value"><?php echo wc_format_datetime($order->get_date_created()); ?></span>
                    </div>
                    <div class="deva-order-item">
                        <span class="label"><?php esc_html_e('Email:', 'hello-elementor-child'); ?></span>
                        <span class="value"><?php echo $order->get_billing_email(); ?></span>
                    </div>
                    <div class="deva-order-item">
                        <span class="label"><?php esc_html_e('Total:', 'hello-elementor-child'); ?></span>
                        <span class="value total"><?php echo $order->get_formatted_order_total(); ?></span>
                    </div>
                    <?php if ($order->get_payment_method_title()) : ?>
                        <div class="deva-order-item">
                            <span class="label"><?php esc_html_e('Payment Method:', 'hello-elementor-child'); ?></span>
                            <span class="value"><?php echo wp_kses_post($order->get_payment_method_title()); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Order Details -->
            <div class="deva-order-review">
                <h3><?php esc_html_e('Order Details', 'hello-elementor-child'); ?></h3>

                <?php do_action('woocommerce_order_details_before_order_table', $order); ?>

                <table class="deva-order-table woocommerce-table woocommerce-table--order-details shop_table order_details">
                    <thead>
                        <tr>
                            <th class="woocommerce-table__product-name product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
                            <th class="woocommerce-table__product-quantity product-quantity"><?php esc_html_e('Quantity', 'woocommerce'); ?></th>
                            <th class="woocommerce-table__product-total product-total"><?php esc_html_e('Total', 'woocommerce'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        do_action('woocommerce_order_details_before_order_table_items', $order);

                        foreach ($order->get_items() as $item_id => $item) {
                            $product = $item->get_product();

                            wc_get_template(
                                'order/order-details-item.php',
                                array(
                                    'order'              => $order,
                                    'item_id'            => $item_id,
                                    'item'               => $item,
                                    'show_purchase_note' => $order->is_paid() && ! $order->has_status('processing'),
                                    'purchase_note'      => $product ? $product->get_purchase_note() : '',
                                    'product'            => $product,
                                )
                            );
                        }

                        do_action('woocommerce_order_details_after_order_table_items', $order);
                        ?>
                    </tbody>
                    <tfoot>
                        <?php
                        foreach ($order->get_order_item_totals() as $key => $total) {
                        ?>
                            <tr>
                                <th scope="row"><?php echo esc_html($total['label']); ?></th>
                                <td><?php echo ('payment_method' === $key) ? esc_html($total['value']) : wp_kses_post($total['value']); ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                        <?php if ($order->get_customer_note()) : ?>
                            <tr>
                                <th><?php esc_html_e('Note:', 'woocommerce'); ?></th>
                                <td><?php echo wp_kses_post(nl2br(wptexturize($order->get_customer_note()))); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tfoot>
                </table>

                <?php do_action('woocommerce_order_details_after_order_table', $order); ?>
            </div>

            <!-- Customer Information -->
            <div class="deva-customer-details">
                <div class="deva-billing-info">
                    <h3><?php esc_html_e('Billing Address', 'hello-elementor-child'); ?></h3>
                    <div class="deva-address">
                        <?php echo wp_kses_post($order->get_formatted_billing_address(esc_html__('N/A', 'woocommerce'))); ?>

                        <?php if ($order->get_billing_phone()) : ?>
                            <div class="deva-phone">
                                <strong><?php esc_html_e('Phone:', 'hello-elementor-child'); ?></strong>
                                <?php echo esc_html($order->get_billing_phone()); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($order->get_billing_email()) : ?>
                            <div class="deva-email">
                                <strong><?php esc_html_e('Email:', 'hello-elementor-child'); ?></strong>
                                <?php echo esc_html($order->get_billing_email()); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $order->get_formatted_shipping_address()) : ?>
                    <div class="deva-shipping-info">
                        <h3><?php esc_html_e('Shipping Address', 'hello-elementor-child'); ?></h3>
                        <div class="deva-address">
                            <?php echo wp_kses_post($order->get_formatted_shipping_address(esc_html__('N/A', 'woocommerce'))); ?>

                            <?php if ($order->get_shipping_phone()) : ?>
                                <div class="deva-phone">
                                    <strong><?php esc_html_e('Phone:', 'hello-elementor-child'); ?></strong>
                                    <?php echo esc_html($order->get_shipping_phone()); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Next Steps -->
            <div class="deva-next-steps">
                <h3><?php esc_html_e('What\'s Next?', 'hello-elementor-child'); ?></h3>
                <div class="deva-steps-grid">
                    <div class="deva-step">
                        <div class="step-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="#48733d" stroke-width="2" fill="none" />
                                <polyline points="22,6 12,13 2,6" stroke="#48733d" stroke-width="2" />
                            </svg>
                        </div>
                        <h4><?php esc_html_e('Order Confirmation', 'hello-elementor-child'); ?></h4>
                        <p><?php esc_html_e('You\'ll receive an email confirmation with your order details shortly.', 'hello-elementor-child'); ?></p>
                    </div>

                    <div class="deva-step">
                        <div class="step-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="12" cy="12" r="3" stroke="#48733d" stroke-width="2" />
                                <path d="M12 6V2M12 22v-4M6 12H2M22 12h-4" stroke="#48733d" stroke-width="2" />
                            </svg>
                        </div>
                        <h4><?php esc_html_e('Processing', 'hello-elementor-child'); ?></h4>
                        <p><?php esc_html_e('We\'re preparing your order for shipment. This usually takes 1-2 business days.', 'hello-elementor-child'); ?></p>
                    </div>

                    <div class="deva-step">
                        <div class="step-icon">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 3h5v5M4 20L21 3M21 16v5h-5M4 4l2.5 2.5" stroke="#48733d" stroke-width="2" fill="none" />
                            </svg>
                        </div>
                        <h4><?php esc_html_e('Shipping', 'hello-elementor-child'); ?></h4>
                        <p><?php esc_html_e('Once shipped, you\'ll receive a tracking number to monitor your package.', 'hello-elementor-child'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Support Information -->
            <div class="deva-support-info">
                <h3><?php esc_html_e('Need Help?', 'hello-elementor-child'); ?></h3>
                <p><?php esc_html_e('If you have any questions about your order, please don\'t hesitate to contact us.', 'hello-elementor-child'); ?></p>
                <div class="deva-contact-options">
                    <?php 
                    // Get WooCommerce admin email or fallback to WordPress admin email
                    $admin_email = get_option('woocommerce_email_from_address');
                    if (empty($admin_email)) {
                        $admin_email = get_option('admin_email');
                    }
                    ?>
                    <a href="mailto:<?php echo esc_attr($admin_email); ?>" class="deva-contact-link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2" />
                            <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2" />
                        </svg>
                        <?php esc_html_e('Email Support', 'hello-elementor-child'); ?>
                    </a>
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="deva-btn deva-btn-primary">
                        <?php esc_html_e('Continue Shopping', 'hello-elementor-child'); ?>
                    </a>
                </div>
            </div>

        <?php endif; ?>

        <?php //do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); 
        ?>
        <?php //do_action('woocommerce_thankyou', $order->get_id()); 
        ?>

    <?php else : ?>

        <div class="deva-thankyou-header deva-order-notfound">
            <div class="deva-thankyou-icon error">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="#e74c3c" stroke-width="2" />
                    <line x1="12" y1="8" x2="12" y2="12" stroke="#e74c3c" stroke-width="2" />
                    <line x1="12" y1="16" x2="12.01" y2="16" stroke="#e74c3c" stroke-width="2" />
                </svg>
            </div>
            <h1 class="deva-thankyou-title"><?php esc_html_e('Order Not Found', 'hello-elementor-child'); ?></h1>
            <p class="deva-thankyou-subtitle">
                <?php esc_html_e('Sorry, we couldn\'t find your order. Please check the URL or contact support if you believe this is an error.', 'hello-elementor-child'); ?>
            </p>

            <div class="deva-thankyou-actions">
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="deva-btn deva-btn-primary">
                    <?php esc_html_e('Return to Shop', 'hello-elementor-child'); ?>
                </a>
            </div>
        </div>

    <?php endif; ?>

</div>

<?php
wp_footer();

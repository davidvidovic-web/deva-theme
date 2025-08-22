<?php

/**
 * DEVA View Order
 *
 * Shows the details of a particular order on the account page with custom DEVA styling.
 *
 * This template overrides WooCommerce's default view-order template.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 */

defined('ABSPATH') || exit;

$notes = $order->get_customer_order_notes();
$order_items = $order->get_items();
$status = $order->get_status();
$status_name = wc_get_order_status_name($status);
$status_class = sanitize_html_class($status);
?>

<div class="deva-order-details-page">
    <!-- Page Header -->
    <div class="deva-order-details-header">
        <div class="deva-order-title">
            <h2>
                <span class="dashicons dashicons-visibility"></span>
                <?php printf(__('Order #%s', 'woocommerce'), $order->get_order_number()); ?>
            </h2>
        </div>

        <!-- Back to Orders Link -->
        <div class="deva-order-back">
            <a href="<?php echo esc_url(wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount'))); ?>" class="deva-back-link">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <?php _e('Back to Orders', 'woocommerce'); ?>
            </a>
        </div>
    </div>

    <!-- Order Status Card -->
    <div class="deva-order-status-card deva-order-status-<?php echo esc_attr($status_class); ?>">
        <div class="deva-status-content">
            <div class="deva-status-icon">
                <?php if ($status === 'completed') : ?>
                    <span class="dashicons dashicons-yes-alt"></span>
                <?php elseif ($status === 'processing') : ?>
                    <span class="dashicons dashicons-clock"></span>
                <?php elseif ($status === 'pending') : ?>
                    <span class="dashicons dashicons-hourglass"></span>
                <?php elseif ($status === 'cancelled') : ?>
                    <span class="dashicons dashicons-dismiss"></span>
                <?php else : ?>
                    <span class="dashicons dashicons-info"></span>
                <?php endif; ?>
            </div>
            <div class="deva-status-info">
                <h3><?php echo esc_html($status_name); ?></h3>
                <p>
                    <?php
                    printf(
                        __('Order placed on %s and is currently %s.', 'hello-elementor-child'),
                        '<strong>' . wc_format_datetime($order->get_date_created(), 'F j, Y') . '</strong>',
                        '<strong>' . strtolower($status_name) . '</strong>'
                    );
                    ?>
                </p>
            </div>
            <div class="deva-status-total">
                <div class="deva-total-label"><?php _e('Total', 'hello-elementor-child'); ?></div>
                <div class="deva-total-amount"><?php echo wp_kses_post($order->get_formatted_order_total()); ?></div>
            </div>
        </div>
    </div>

    <!-- Order Details Grid -->
    <div class="deva-order-details-grid">
        <!-- Order Items -->
        <div class="deva-order-section">
            <h3>
                <span class="dashicons dashicons-cart"></span>
                <?php _e('Order Items', 'hello-elementor-child'); ?>
            </h3>

            <div class="deva-order-items-list">
                <?php foreach ($order_items as $item_id => $item) :
                    $product = $item->get_product();
                    if (!$product) continue;

                    $product_image = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
                    if (!$product_image) {
                        $product_image = wc_placeholder_img_src('thumbnail');
                    }
                ?>
                    <div class="deva-order-item">
                        <div class="deva-item-image">
                            <img src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" />
                        </div>

                        <div class="deva-item-details">
                            <h4><?php echo esc_html($product->get_name()); ?></h4>

                            <?php if ($product->get_sku()) : ?>
                                <div class="deva-item-sku">
                                    <strong><?php _e('SKU:', 'hello-elementor-child'); ?></strong>
                                    <?php echo esc_html($product->get_sku()); ?>
                                </div>
                            <?php endif; ?>

                            <div class="deva-item-meta">
                                <span class="deva-quantity">
                                    <strong><?php _e('Quantity:', 'hello-elementor-child'); ?></strong>
                                    <?php echo esc_html($item->get_quantity()); ?>
                                </span>
                            </div>
                            <div class="deva-item-meta">
                                <span class="deva-price">
                                    <strong><?php _e('Price:', 'hello-elementor-child'); ?></strong>
                                    <?php echo wp_kses_post(wc_price($item->get_total())); ?>
                                </span>
                            </div>
                        </div>

                        <div class="deva-item-actions">
                            <?php if ($product->is_purchasable() && $order->get_status() === 'completed') : ?>
                                <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" class="deva-btn deva-btn-secondary">
                                    <span class="dashicons dashicons-cart"></span>
                                    <?php _e('Buy Again', 'hello-elementor-child'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Order Information -->
        <div class="deva-order-section">
            <h3>
                <span class="dashicons dashicons-admin-generic"></span>
                <?php _e('Order Information', 'hello-elementor-child'); ?>
            </h3>

            <div class="deva-order-info-card">
                <div class="deva-info-row">
                    <span class="deva-info-label"><?php _e('Order Number:', 'hello-elementor-child'); ?></span>
                    <span class="deva-info-value">#<?php echo esc_html($order->get_order_number()); ?></span>
                </div>

                <div class="deva-info-row">
                    <span class="deva-info-label"><?php _e('Order Date:', 'hello-elementor-child'); ?></span>
                    <span class="deva-info-value"><?php echo esc_html(wc_format_datetime($order->get_date_created(), 'F j, Y \a\t g:i A')); ?></span>
                </div>

                <div class="deva-info-row">
                    <span class="deva-info-label"><?php _e('Status:', 'hello-elementor-child'); ?></span>
                    <span class="deva-info-value">
                        <span class="deva-status-badge deva-status-<?php echo esc_attr($status_class); ?>">
                            <?php echo esc_html($status_name); ?>
                        </span>
                    </span>
                </div>

                <div class="deva-info-row">
                    <span class="deva-info-label"><?php _e('Payment Method:', 'hello-elementor-child'); ?></span>
                    <span class="deva-info-value"><?php echo esc_html($order->get_payment_method_title()); ?></span>
                </div>

                <?php if ($order->get_billing_email()) : ?>
                    <div class="deva-info-row">
                        <span class="deva-info-label"><?php _e('Email:', 'hello-elementor-child'); ?></span>
                        <span class="deva-info-value"><?php echo esc_html($order->get_billing_email()); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Billing Address -->
        <?php if ($order->get_formatted_billing_address()) : ?>
            <div class="deva-order-section">
                <h3>
                    <span class="dashicons dashicons-location"></span>
                    <?php _e('Billing Address', 'hello-elementor-child'); ?>
                </h3>

                <div class="deva-address-card">
                    <?php echo wp_kses_post($order->get_formatted_billing_address()); ?>

                    <?php if ($order->get_billing_phone()) : ?>
                        <div class="deva-phone">
                            <strong><?php _e('Phone:', 'hello-elementor-child'); ?></strong>
                            <?php echo esc_html($order->get_billing_phone()); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Shipping Address -->
        <?php if ($order->get_formatted_shipping_address()) : ?>
            <div class="deva-order-section">
                <h3>
                    <span class="dashicons dashicons-admin-home"></span>
                    <?php _e('Shipping Address', 'hello-elementor-child'); ?>
                </h3>

                <div class="deva-address-card">
                    <?php echo wp_kses_post($order->get_formatted_shipping_address()); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Order Notes -->
    <?php if ($notes) : ?>
        <div class="deva-order-section deva-order-notes">
            <h3>
                <span class="dashicons dashicons-admin-comments"></span>
                <?php _e('Order Updates', 'hello-elementor-child'); ?>
            </h3>

            <div class="deva-notes-list">
                <?php foreach ($notes as $note) : ?>
                    <div class="deva-order-note">
                        <div class="deva-note-date">
                            <?php echo esc_html(date_i18n('F j, Y \a\t g:i A', strtotime($note->comment_date))); ?>
                        </div>
                        <div class="deva-note-content">
                            <?php echo wp_kses_post(wpautop(wptexturize($note->comment_content))); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Order Actions -->
    <?php
    $actions = wc_get_account_orders_actions($order);
    if (!empty($actions)) :
    ?>
        <!-- <div class="deva-order-actions-section">
            <h3><?php _e('Order Actions', 'hello-elementor-child'); ?></h3>
            <div class="deva-order-actions">
                <?php //foreach ($actions as $key => $action) :
                //$button_class = 'deva-btn deva-btn-secondary';
                //if ($key === 'pay') {
                //$button_class = 'deva-btn deva-btn-success';
                //} elseif ($key === 'cancel') {
                //$button_class = 'deva-btn deva-btn-danger';
                //}
                ?>
                    <a href="<?php //echo esc_url($action['url']); 
                                ?>" class="<?php //echo esc_attr($button_class); 
                                            ?>">
                        <?php //cho esc_html($action['name']); 
                        ?>
                    </a>
                <?php //endforeach; 
                ?>
            </div>
        </div> -->
    <?php endif; ?>
</div>

<?php //do_action('woocommerce_view_order', $order_id); 
?>
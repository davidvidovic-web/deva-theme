<?php
/**
 * DEVA Orders
 *
 * Shows orders on the account page with custom DEVA styling.
 *
 * This template overrides WooCommerce's default orders template.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.5.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_orders', $has_orders); ?>

<div class="deva-orders-page">
    <!-- Page Header -->
    <div class="deva-orders-header">
        <div class="deva-orders-title">
            <h2><span class="dashicons dashicons-text-page"></span> <?php _e('Order History', 'woocommerce'); ?></h2>
            <p><?php _e('View and track all your previous orders', 'woocommerce'); ?></p>
        </div>
        
        <!-- Back to Account Link -->
        <div class="deva-orders-back">
            <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="deva-back-link">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <?php _e('Back to Account', 'woocommerce'); ?>
            </a>
        </div>
    </div>

    <?php if ($has_orders) : ?>
        <!-- Orders Grid -->
        <div class="deva-orders-grid">
            <?php
            foreach ($customer_orders->orders as $customer_order) {
                $order = wc_get_order($customer_order);
                $item_count = $order->get_item_count() - $order->get_item_count_refunded();
                $order_items = $order->get_items();
                
                // Get order status details
                $status = $order->get_status();
                $status_name = wc_get_order_status_name($status);
                $status_class = sanitize_html_class($status);
                
                // Get first product image for preview
                $product_image = '';
                $first_item = reset($order_items);
                if ($first_item) {
                    $product = $first_item->get_product();
                    if ($product) {
                        $product_image = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
                        if (!$product_image) {
                            $product_image = wc_placeholder_img_src('thumbnail');
                        }
                    }
                }
                ?>
                
                <div class="deva-order-card deva-order-status-<?php echo esc_attr($status_class); ?>">
                    <!-- Order Header -->
                    <div class="deva-order-header">
                        <div class="deva-order-info">
                            <div class="deva-order-number">
                                <strong><?php echo esc_html('#' . $order->get_order_number()); ?></strong>
                                <span class="deva-order-date">
                                    <?php echo esc_html(wc_format_datetime($order->get_date_created(), 'M j, Y')); ?>
                                </span>
                            </div>
                            <div class="deva-order-status">
                                <span class="deva-status-badge deva-status-<?php echo esc_attr($status_class); ?>">
                                    <?php echo esc_html($status_name); ?>
                                </span>
                            </div>
                        </div>
                        <div class="deva-order-total">
                            <div class="deva-total-amount">
                                <?php echo wp_kses_post($order->get_formatted_order_total()); ?>
                            </div>
                            <div class="deva-item-count">
                                <?php printf(_n('%s item', '%s items', $item_count, 'woocommerce'), $item_count); ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Items Preview -->
                    <div class="deva-order-items">
                        <?php if ($product_image) : ?>
                            <div class="deva-order-image">
                                <img src="<?php echo esc_url($product_image); ?>" alt="<?php _e('Product image', 'hello-elementor-child'); ?>" />
                                <?php if ($item_count > 1) : ?>
                                    <span class="deva-more-items">+<?php echo ($item_count - 1); ?></span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="deva-order-products">
                            <?php
                            $displayed = 0;
                            foreach ($order_items as $item) {
                                if ($displayed >= 3) break; // Show max 3 items
                                $product = $item->get_product();
                                if ($product) {
                                    echo '<div class="deva-product-name">';
                                    echo esc_html($product->get_name());
                                    if ($item->get_quantity() > 1) {
                                        echo ' <span class="deva-quantity">×' . $item->get_quantity() . '</span>';
                                    }
                                    echo '</div>';
                                    $displayed++;
                                }
                            }
                            if (count($order_items) > 3) {
                                echo '<div class="deva-more-products">+' . (count($order_items) - 3) . ' ' . __('more items', 'hello-elementor-child') . '</div>';
                            }
                            ?>
                        </div>
                    </div>
                    
                    <!-- Order Actions -->
                    <div class="deva-order-actions">
                        <a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="deva-btn deva-btn-primary">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php _e('View Details', 'hello-elementor-child'); ?>
                        </a>
                        
                        <?php
                        $actions = wc_get_account_orders_actions($order);
                        if (!empty($actions)) {
                            foreach ($actions as $key => $action) {
                                if ($key === 'view') continue; // Skip view action as we already have it
                                
                                $button_class = 'deva-btn deva-btn-secondary';
                                if ($key === 'pay') {
                                    $button_class = 'deva-btn deva-btn-success';
                                } elseif ($key === 'cancel') {
                                    $button_class = 'deva-btn deva-btn-danger';
                                }
                                
                                echo '<a href="' . esc_url($action['url']) . '" class="' . esc_attr($button_class) . '">';
                                echo esc_html($action['name']);
                                echo '</a>';
                            }
                        }
                        ?>
                    </div>
                </div>
                
                <?php
            }
            ?>
        </div>
        
        <!-- Pagination -->
        <?php if (1 < $customer_orders->max_num_pages) : ?>
            <div class="deva-orders-pagination">
                <?php if (1 !== $current_page) : ?>
                    <a class="deva-pagination-btn deva-pagination-prev" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page - 1)); ?>">
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                        <?php esc_html_e('Previous', 'woocommerce'); ?>
                    </a>
                <?php endif; ?>
                
                <span class="deva-pagination-info">
                    <?php printf(__('Page %s of %s', 'hello-elementor-child'), $current_page, $customer_orders->max_num_pages); ?>
                </span>
                
                <?php if (intval($customer_orders->max_num_pages) !== $current_page) : ?>
                    <a class="deva-pagination-btn deva-pagination-next" href="<?php echo esc_url(wc_get_endpoint_url('orders', $current_page + 1)); ?>">
                        <?php esc_html_e('Next', 'woocommerce'); ?>
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
    <?php else : ?>
        <!-- Empty State -->
        <div class="deva-orders-empty">
            <div class="deva-empty-icon">
                <span class="dashicons dashicons-text-page"></span>
            </div>
            <h3><?php _e('No Orders Yet', 'hello-elementor-child'); ?></h3>
            <p><?php _e('You haven\'t placed any orders yet. Start shopping to see your order history here.', 'hello-elementor-child'); ?></p>
            <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="deva-btn deva-btn-primary deva-btn-large">
                <span class="dashicons dashicons-store"></span>
                <?php _e('Browse Products', 'hello-elementor-child'); ?>
            </a>
        </div>
    <?php endif; ?>
</div>

<?php do_action('woocommerce_after_account_orders', $has_orders); ?>

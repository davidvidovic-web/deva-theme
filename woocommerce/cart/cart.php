<?php

/**
 * DEVA Cart Template
 * 
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * @package HelloElementorChild
 * @version 10.0.0 
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart'); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
    <div class="deva-cart-container">
        <!-- Cart/Wishlist indicator -->
        <div class="deva-cart-header">
            <div class="deva-tab active" data-tab="cart">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="9" cy="21" r="1"></circle>
                    <circle cx="20" cy="21" r="1"></circle>
                    <path d="m1 1 4 4 4.5 11h9l4-8H6"></path>
                </svg>
                <?php esc_html_e('Cart', 'hello-elementor-child'); ?>
            </div>
            <div class="deva-tab" data-tab="wishlist">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
                <?php esc_html_e('Wishlist', 'hello-elementor-child'); ?>
            </div>
        </div>

        <!-- Cart Content -->
        <div class="deva-tab-content active" id="cart-content">
            <?php if (WC()->cart->is_empty()) : ?>
                <div class="deva-cart-empty">
                    <h2><?php esc_html_e('Your cart is currently empty.', 'woocommerce'); ?></h2>
                    <p><?php esc_html_e('Add some products to your cart to continue.', 'woocommerce'); ?></p>
                    <a href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>" class="deva-button deva-button-primary">
                        <?php esc_html_e('Return to Shop', 'woocommerce'); ?>
                    </a>
                </div>
            <?php else : ?>
                <?php do_action('woocommerce_before_cart_table'); ?>

                <!-- Cart Items Section -->
                <div class="deva-cart-items">
                    <?php
                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

                        if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                            $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                            $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);
                            $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                            $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
                            $product_price = apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key);
                            $product_subtotal = apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key);
                    ?>
                            <div class="deva-cart-item" data-key="<?php echo esc_attr($cart_item_key); ?>">
                                <div class="deva-item-image">
                                    <?php if (empty($product_permalink)) : ?>
                                        <?php echo $thumbnail; ?>
                                    <?php else : ?>
                                        <a href="<?php echo esc_url($product_permalink); ?>">
                                            <?php echo $thumbnail; ?>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div class="deva-item-details">
                                    <div class="deva-item-info">
                                        <?php if (empty($product_permalink)) : ?>
                                            <h3 class="deva-item-name"><?php echo wp_kses_post($product_name); ?></h3>
                                        <?php else : ?>
                                            <h3 class="deva-item-name">
                                                <a href="<?php echo esc_url($product_permalink); ?>">
                                                    <?php echo wp_kses_post($product_name); ?>
                                                </a>
                                            </h3>
                                        <?php endif; ?>

                                        <div class="deva-item-meta">
                                            <?php
                                            // Display product attributes and variations
                                            $item_data = wc_get_formatted_cart_item_data($cart_item);
                                            if ($item_data) {
                                                echo '<div class="deva-item-attributes">' . $item_data . '</div>';
                                            }

                                            do_action('woocommerce_after_cart_item_name', $cart_item, $cart_item_key);

                                            // Backorder notification
                                            if ($_product->backorders_require_notification() && $_product->is_on_backorder($cart_item['quantity'])) {
                                                echo wp_kses_post(apply_filters('woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__('Available on backorder', 'woocommerce') . '</p>', $product_id));
                                            }
                                            ?>

                                            <p class="deva-item-quantity">
                                                <?php esc_html_e('Quantity:', 'hello-elementor-child'); ?>
                                            <div class="quantity-controls">
                                                <button type="button" class="qty-btn qty-minus" data-key="<?php echo esc_attr($cart_item_key); ?>">-</button>
                                                <?php
                                                if ($_product->is_sold_individually()) {
                                                    $product_quantity = sprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);
                                                } else {
                                                    $product_quantity = woocommerce_quantity_input(
                                                        array(
                                                            'input_name'   => "cart[{$cart_item_key}][qty]",
                                                            'input_value'  => $cart_item['quantity'],
                                                            'max_value'    => $_product->get_max_purchase_quantity(),
                                                            'min_value'    => '0',
                                                            'product_name' => $product_name,
                                                            'classes'      => array('deva-quantity-input'),
                                                        ),
                                                        $_product,
                                                        false
                                                    );
                                                }
                                                echo apply_filters('woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item);
                                                ?>
                                                <button type="button" class="qty-btn qty-plus" data-key="<?php echo esc_attr($cart_item_key); ?>">+</button>
                                            </div>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="deva-item-actions">
                                        <div class="deva-item-buttons">
                                            <?php
                                            echo apply_filters(
                                                'woocommerce_cart_item_remove_link',
                                                sprintf(
                                                    '<button type="button" class="deva-button deva-button-outline deva-remove-item" data-key="%s" data-product_id="%s" data-product_sku="%s">
                                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                            <path d="M2 4H14M5.333 4V2.667C5.333 2.298 5.632 2 6 2H10C10.368 2 10.667 2.298 10.667 2.667V4M6.667 7.333V11.333M9.333 7.333V11.333M3.333 4H12.667L12 13.333C12 13.702 11.701 14 11.333 14H4.667C4.298 14 4 13.702 4 13.333L3.333 4Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        %s
                                                    </button>',
                                                    esc_attr($cart_item_key),
                                                    esc_attr($product_id),
                                                    esc_attr($_product->get_sku()),
                                                    esc_html__('Remove', 'hello-elementor-child')
                                                ),
                                                $cart_item_key
                                            );
                                            ?>
                                        </div>
                                        <div class="deva-item-price">
                                            <div class="total-price">
                                                <span class="price-amount subtotal-amount"><?php echo $product_subtotal; ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>

                <?php do_action('woocommerce_cart_contents'); ?>
                <?php do_action('woocommerce_after_cart_contents'); ?>

                <!-- Place your order button - only show on cart tab -->
                <div class="deva-cart-footer">
                    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="deva-button">
                        <?php esc_html_e('Place your order →', 'hello-elementor-child'); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Wishlist Content -->
        <div class="deva-tab-content" id="wishlist-content">
            <div class="deva-wishlist-container">
                <?php echo do_shortcode('[deva_wishlist]'); ?>
            </div>
        </div>
        <!-- navigation like in deva-categories -->
        <nav class="deva-pagination">
            <ul>
                <li class="prev">
                    <button type="button" class="deva-tab-nav" data-tab="cart">←</button>
                </li>

                <li class="cart-indicator">
                    <span class="current cart-tab-indicator active" data-tab="cart">1</span>
                </li>

                <li class="wishlist-indicator">
                    <span class="current wishlist-tab-indicator" data-tab="wishlist">2</span>
                </li>

                <li class="next">
                    <button type="button" class="deva-tab-nav" data-tab="wishlist">→</button>
                </li>
            </ul>
        </nav>

    </div>
</form>

<?php do_action('woocommerce_after_cart'); ?>
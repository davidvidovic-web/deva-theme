<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * @package HelloElementorChild
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $product;

// Ensure visibility.
if (empty($product) || !$product->is_visible()) {
    return;
}
?>
<li <?php wc_product_class('', $product); ?>>
    <?php
    /**
     * Hook: woocommerce_before_shop_loop_item.
     *
     * @hooked woocommerce_template_loop_product_link_open - 10
     */
    do_action('woocommerce_before_shop_loop_item');
    ?>

    <div class="product-image-wrapper">
        <?php
        /**
         * Hook: woocommerce_before_shop_loop_item_title.
         *
         * @hooked woocommerce_template_loop_product_thumbnail - 10
         * Sale flash has been removed via functions.php
         */
        do_action('woocommerce_before_shop_loop_item_title');
        ?>

        <!-- Like/Favorite Heart Button - Top Left -->
        <div class="favorite-heart" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
            </svg>
        </div>

        <!-- Price Bubble - Top Right -->
        <div class="price-overlay">
            <?php echo $product->get_price_html(); ?>
        </div>

        <!-- Sale Badge - Bottom Left (Custom positioned) -->
        <?php if ($product->is_on_sale()) : ?>
            <span class="onsale"><?php esc_html_e('Sale!', 'woocommerce'); ?></span>
        <?php endif; ?>
    </div>

    <div class="product-info-wrapper">
        <?php
        /**
         * Hook: woocommerce_shop_loop_item_title.
         *
         * @hooked woocommerce_template_loop_product_title - 10
         */
        do_action('woocommerce_shop_loop_item_title');

        // Custom single star rating display
        $rating = $product->get_average_rating();
        $rating_count = $product->get_rating_count();
        
        if ($rating_count > 0) : ?>
            <div class="deva-single-star-rating">
                <span class="star-icon">★</span>
                <span class="rating-score"><?php echo number_format($rating, 1); ?></span>
            </div>
        <?php endif;

        /**
         * Hook: woocommerce_after_shop_loop_item_title.
         *
         * @hooked woocommerce_template_loop_price - 10
         * Rating has been removed globally via functions.php
         */
        do_action('woocommerce_after_shop_loop_item_title');

        /**
         * Hook: woocommerce_after_shop_loop_item.
         *
         * @hooked woocommerce_template_loop_product_link_close - 5
         * @hooked woocommerce_template_loop_add_to_cart - 10
         */
        do_action('woocommerce_after_shop_loop_item');
        ?>
    </div>
</li>

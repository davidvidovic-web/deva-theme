<?php

/**
 * DEVA My Account Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/my-account.php.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined('ABSPATH') || exit;

/**
 * Hook: woocommerce_before_account_navigation.
 */
do_action('woocommerce_before_account_navigation');

// Get current user data
$current_user = wp_get_current_user();
$customer = new WC_Customer(get_current_user_id());

// Check if we're on the lost password page
$is_lost_password = isset($_GET['action']) && $_GET['action'] === 'lost-password';
$is_reset_password = isset($_GET['action']) && $_GET['action'] === 'rp';

// If this is a lost password request and user is not logged in, show the form
if (($is_lost_password || $is_reset_password) && !is_user_logged_in()) {
    wc_get_template('myaccount/form-lost-password.php');
    return;
}
?>

<div class="deva-account-container">
    <!-- Breadcrumb -->
    <div class="deva-account-breadcrumb">
        <?php
        if (function_exists('woocommerce_breadcrumb')) {
            woocommerce_breadcrumb(array(
                'delimiter' => ' > ',
                'wrap_before' => '<nav class="deva-breadcrumb">',
                'wrap_after' => '</nav>',
                'before' => '',
                'after' => '',
                'home' => _x('Home', 'breadcrumb', 'woocommerce'),
            ));
        }
        ?>
    </div>

    <?php
    // Check if we're on a specific account endpoint (not the main dashboard)
    global $wp;
    $current_endpoint = '';
    
    if (isset($wp->query_vars) && !empty($wp->query_vars)) {
        foreach (WC()->query->get_query_vars() as $key => $var) {
            if (isset($wp->query_vars[$key])) {
                $current_endpoint = $key;
                break;
            }
        }
    }
    
    // Only show dashboard content if we're on the main account page
    if (empty($current_endpoint)) :
    ?>

    <!-- Top Section -->
    <div class="deva-account-top-section">
        <!-- Profile Info -->
        <div class="deva-profile-section">
            <div class="deva-profile-card">
                <?php
                // Get user avatar
                $avatar_url = get_avatar_url($current_user->ID, array('size' => 100));
                ?>
                <img src="<?php echo esc_url($avatar_url); ?>" class="deva-avatar" alt="<?php echo esc_attr($current_user->display_name); ?>" />

                <h2><?php echo sprintf(__('Hi %s', 'hello-elementor-child'), esc_html($current_user->first_name ?: $current_user->display_name)); ?></h2>

                <p class="deva-email"><?php echo esc_html($current_user->user_email); ?></p>

                <p class="deva-description">
                    <?php _e('Your journey, Your story', 'hello-elementor-child'); ?><br>
                    <?php _e('This is your space! Add your health goals, track progress, and stay motivated. You can update this anytime in Settings.', 'hello-elementor-child'); ?>
                </p>
            </div>
        </div>

        <!-- My Program + Menu -->
        <div class="deva-program-menu-section">
            <div class="deva-program-section">
                <h3><?php _e('My program', 'hello-elementor-child'); ?></h3>

                <?php
                // Get user's active subscriptions or programs
                $has_active_program = false;
                $program_data = null;

                // Check if WooCommerce Subscriptions is active
                if (function_exists('wcs_get_users_subscriptions')) {
                    $active_subscriptions = wcs_get_users_subscriptions($current_user->ID);

                    if (!empty($active_subscriptions)) {
                        foreach ($active_subscriptions as $subscription) {
                            if ($subscription->has_status(array('active', 'pending-cancel'))) {
                                $has_active_program = true;
                                $subscription_items = $subscription->get_items();
                                $first_item = reset($subscription_items);

                                if ($first_item) {
                                    $product = $first_item->get_product();
                                    if ($product) {
                                        $program_data = array(
                                            'product' => $product,
                                            'price' => $subscription->get_formatted_order_total()
                                        );
                                        break;
                                    }
                                }
                            }
                        }
                    }
                } else {
                    // Fallback: Check user's recent orders for products
                    $customer_orders = wc_get_orders(array(
                        'customer' => $current_user->ID,
                        'status' => array('completed', 'processing'),
                        'limit' => 5,
                        'orderby' => 'date',
                        'order' => 'DESC',
                    ));

                    if (!empty($customer_orders)) {
                        foreach ($customer_orders as $order) {
                            $items = $order->get_items();
                            foreach ($items as $item) {
                                $product = $item->get_product();
                                if ($product && !$product->is_type('simple')) {
                                    // Assume non-simple products are programs/courses
                                    $has_active_program = true;
                                    $program_data = array(
                                        'product' => $product,
                                        'price' => wc_price($item->get_total())
                                    );
                                    break 2;
                                } elseif ($product) {
                                    // Check if it's a program-like product by name or category
                                    $product_name = strtolower($product->get_name());
                                    if (
                                        strpos($product_name, 'program') !== false ||
                                        strpos($product_name, 'course') !== false ||
                                        strpos($product_name, 'plan') !== false ||
                                        strpos($product_name, 'tpm') !== false
                                    ) {
                                        $has_active_program = true;
                                        $program_data = array(
                                            'product' => $product,
                                            'price' => wc_price($item->get_total())
                                        );
                                        break 2;
                                    }
                                }
                            }
                        }
                    }
                }

                if ($has_active_program && $program_data) {
                    $product = $program_data['product'];
                ?>
                    <div class="deva-program-box">
                        <?php
                        $product_image = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
                        if (!$product_image) {
                            $product_image = wc_placeholder_img_src('thumbnail');
                        }
                        ?>
                        <img src="<?php echo esc_url($product_image); ?>" class="deva-program-image" alt="<?php echo esc_attr($product->get_name()); ?>" />

                        <div class="deva-program-info">
                            <strong><?php echo esc_html($product->get_name()); ?></strong>
                            <p><?php echo esc_html($product->get_short_description() ?: 'Traditional Persian Medicine Program'); ?></p>
                            <small><?php _e('Ancient healing for balance.', 'hello-elementor-child'); ?></small>
                        </div>

                        <div class="deva-program-price">
                            <?php echo $program_data['price']; ?>
                        </div>
                    </div>
                <?php
                }

                if (!$has_active_program) {
                ?>
                    <div class="deva-program-box deva-no-program">
                        <div class="deva-program-placeholder">
                            <strong><?php _e('No Active Program', 'hello-elementor-child'); ?></strong>
                            <p><?php _e('Start your wellness journey today', 'hello-elementor-child'); ?></p>
                            <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="deva-browse-programs">
                                <?php _e('Browse Programs', 'hello-elementor-child'); ?>
                            </a>
                        </div>
                    </div>
                <?php
                }
                ?>

                <ul class="deva-menu-list">
                    <li>
                        <a href="<?php echo esc_url(wc_get_cart_url()); ?>#wishlist" class="deva-wishlist-link" title="<?php _e('View your saved items', 'hello-elementor-child'); ?>">
                            <span><?php _e('Wishlist', 'hello-elementor-child'); ?></span>
                            <span class="deva-icon dashicons dashicons-heart"></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url(wc_get_cart_url()); ?>" title="<?php _e('View items in your cart', 'hello-elementor-child'); ?>">
                            <span><?php _e('Shopping cart', 'hello-elementor-child'); ?></span>
                            <span class="deva-icon dashicons dashicons-cart"></span>
                        </a>
                    </li>
                    <li>
                        <a href="<?php echo esc_url(wc_get_endpoint_url('orders', '', wc_get_page_permalink('myaccount'))); ?>">
                            <span><?php _e('Order history', 'hello-elementor-child'); ?></span>
                            <span class="deva-icon dashicons dashicons-text-page"></span>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="deva-settings-toggle" onclick="toggleSettings(event)">
                            <span><?php _e('Settings', 'hello-elementor-child'); ?></span>
                            <span class="deva-icon dashicons dashicons-admin-generic"></span>
                            <span class="deva-icon dashicons dashicons-arrow-down deva-settings-arrow"></span>
                        </a>
                    </li>
                    <li>
                        <?php
                        // Get proper logout URL with nonce
                        $logout_url = function_exists('wc_logout_url') 
                            ? wc_logout_url(home_url())
                            : wp_logout_url(home_url());
                        ?>
                        <a href="<?php echo esc_url($logout_url); ?>" class="deva-logout-link">
                            <span><?php _e('Sign out', 'hello-elementor-child'); ?></span>
                            <span class="deva-icon dashicons dashicons-exit"></span>
                        </a>
                    </li>
                </ul>

                <!-- Expandable Settings Section -->
                <div id="deva-settings-section" class="deva-settings-section" style="display: none;">
                    <div class="deva-settings-header">
                        <h4><?php _e('Account Settings', 'hello-elementor-child'); ?></h4>
                        <p><?php _e('Manage your profile information and preferences', 'hello-elementor-child'); ?></p>
                    </div>

                    <form id="deva-profile-form" class="deva-profile-form">
                        <?php wp_nonce_field('update_profile', 'profile_nonce'); ?>

                        <!-- Personal Information -->
                        <div class="deva-settings-group">
                            <h5><?php _e('Personal Information', 'hello-elementor-child'); ?></h5>

                            <div class="deva-form-row">
                                <div class="deva-form-field">
                                    <label for="user_first_name">
                                        <span class="dashicons dashicons-admin-users"></span>
                                        <?php _e('First Name', 'hello-elementor-child'); ?>
                                    </label>
                                    <input type="text"
                                        id="user_first_name"
                                        name="first_name"
                                        value="<?php echo esc_attr($current_user->first_name); ?>"
                                        class="deva-input" />
                                </div>

                                <div class="deva-form-field">
                                    <label for="user_last_name">
                                        <span class="dashicons dashicons-admin-users"></span>
                                        <?php _e('Last Name', 'hello-elementor-child'); ?>
                                    </label>
                                    <input type="text"
                                        id="user_last_name"
                                        name="last_name"
                                        value="<?php echo esc_attr($current_user->last_name); ?>"
                                        class="deva-input" />
                                </div>
                            </div>

                            <div class="deva-form-field">
                                <label for="user_display_name">
                                    <span class="dashicons dashicons-businessman"></span>
                                    <?php _e('Display Name', 'hello-elementor-child'); ?>
                                </label>
                                <select id="user_display_name" name="display_name" class="deva-input">
                                    <?php
                                    $public_display = array();
                                    $public_display['display_username'] = $current_user->user_login;
                                    $public_display['display_nickname'] = $current_user->nickname;
                                    if (!empty($current_user->first_name)) {
                                        $public_display['display_firstname'] = $current_user->first_name;
                                    }
                                    if (!empty($current_user->last_name)) {
                                        $public_display['display_lastname'] = $current_user->last_name;
                                    }
                                    if (!empty($current_user->first_name) && !empty($current_user->last_name)) {
                                        $public_display['display_firstlast'] = $current_user->first_name . ' ' . $current_user->last_name;
                                        $public_display['display_lastfirst'] = $current_user->last_name . ' ' . $current_user->first_name;
                                    }
                                    foreach ($public_display as $id => $item) {
                                        echo '<option value="' . esc_attr($item) . '"' . selected($current_user->display_name, $item, false) . '>' . esc_html($item) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="deva-form-field">
                                <label for="user_email">
                                    <span class="dashicons dashicons-email-alt"></span>
                                    <?php _e('Email Address', 'hello-elementor-child'); ?>
                                </label>
                                <input type="email"
                                    id="user_email"
                                    name="email"
                                    value="<?php echo esc_attr($current_user->user_email); ?>"
                                    class="deva-input" />
                            </div>

                            <div class="deva-form-field">
                                <label for="user_website">
                                    <span class="dashicons dashicons-admin-site"></span>
                                    <?php _e('Website', 'hello-elementor-child'); ?>
                                </label>
                                <input type="url"
                                    id="user_website"
                                    name="user_url"
                                    value="<?php echo esc_attr($current_user->user_url); ?>"
                                    class="deva-input"
                                    placeholder="https://" />
                            </div>

                            <div class="deva-form-field">
                                <label for="user_description">
                                    <span class="dashicons dashicons-admin-comments"></span>
                                    <?php _e('Biographical Info', 'hello-elementor-child'); ?>
                                </label>
                                <textarea id="user_description"
                                    name="description"
                                    class="deva-input"
                                    rows="3"
                                    placeholder="<?php _e('Share a little biographical information to fill out your profile.', 'hello-elementor-child'); ?>"><?php echo esc_textarea(get_user_meta($current_user->ID, 'description', true)); ?></textarea>
                            </div>
                        </div>

                        <!-- Account Security -->
                        <div class="deva-settings-group">
                            <h5><?php _e('Account Security', 'hello-elementor-child'); ?></h5>

                            <div class="deva-form-field">
                                <label for="user_pass">
                                    <span class="dashicons dashicons-lock"></span>
                                    <?php _e('New Password', 'hello-elementor-child'); ?>
                                </label>
                                <div class="deva-password-wrapper">
                                    <input type="password"
                                        id="user_pass"
                                        name="user_pass"
                                        class="deva-input"
                                        autocomplete="new-password"
                                        placeholder="<?php _e('Leave blank to keep current password', 'hello-elementor-child'); ?>" />
                                    <button type="button" class="deva-password-toggle" onclick="togglePasswordVisibility('user_pass')">
                                        <span class="show-text">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </span>
                                        <span class="hide-text" style="display: none;">
                                            <span class="dashicons dashicons-hidden"></span>
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <div class="deva-form-field">
                                <label for="user_pass_confirm">
                                    <span class="dashicons dashicons-lock"></span>
                                    <?php _e('Confirm New Password', 'hello-elementor-child'); ?>
                                </label>
                                <div class="deva-password-wrapper">
                                    <input type="password"
                                        id="user_pass_confirm"
                                        name="user_pass_confirm"
                                        class="deva-input"
                                        autocomplete="new-password"
                                        placeholder="<?php _e('Confirm your new password', 'hello-elementor-child'); ?>" />
                                    <button type="button" class="deva-password-toggle" onclick="togglePasswordVisibility('user_pass_confirm')">
                                        <span class="show-text">
                                            <span class="dashicons dashicons-visibility"></span>
                                        </span>
                                        <span class="hide-text" style="display: none;">
                                            <span class="dashicons dashicons-hidden"></span>
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- DEVA Preferences -->
                        <div class="deva-settings-group">
                            <h5><?php _e('DEVA Preferences', 'hello-elementor-child'); ?></h5>

                            <div class="deva-form-field">
                                <label class="deva-checkbox-label">
                                    <input type="checkbox"
                                        name="deva_email_notifications"
                                        value="1"
                                        <?php checked(get_user_meta($current_user->ID, 'deva_email_notifications', true), '1'); ?> />
                                    <span class="dashicons dashicons-email"></span>
                                    <?php _e('Receive email notifications about appointments and wellness tips', 'hello-elementor-child'); ?>
                                </label>
                            </div>

                            <div class="deva-form-field">
                                <label class="deva-checkbox-label">
                                    <input type="checkbox"
                                        name="deva_marketing_emails"
                                        value="1"
                                        <?php checked(get_user_meta($current_user->ID, 'deva_marketing_emails', true), '1'); ?> />
                                    <span class="dashicons dashicons-megaphone"></span>
                                    <?php _e('Receive marketing emails about new products and offers', 'hello-elementor-child'); ?>
                                </label>
                            </div>

                            <div class="deva-form-field">
                                <label for="user_timezone">
                                    <span class="dashicons dashicons-clock"></span>
                                    <?php _e('Timezone', 'hello-elementor-child'); ?>
                                </label>
                                <select id="user_timezone" name="deva_timezone" class="deva-input">
                                    <?php
                                    $current_timezone = get_user_meta($current_user->ID, 'deva_timezone', true) ?: 'UTC';
                                    $timezones = array(
                                        'UTC' => 'UTC',
                                        'America/New_York' => 'Eastern Time (EST/EDT)',
                                        'America/Chicago' => 'Central Time (CST/CDT)',
                                        'America/Denver' => 'Mountain Time (MST/MDT)',
                                        'America/Los_Angeles' => 'Pacific Time (PST/PDT)',
                                        'Europe/London' => 'London (GMT/BST)',
                                        'Europe/Paris' => 'Paris (CET/CEST)',
                                        'Europe/Berlin' => 'Berlin (CET/CEST)',
                                        'Asia/Tokyo' => 'Tokyo (JST)',
                                        'Australia/Sydney' => 'Sydney (AEST/AEDT)'
                                    );

                                    foreach ($timezones as $value => $label) {
                                        echo '<option value="' . esc_attr($value) . '"' . selected($current_timezone, $value, false) . '>' . esc_html($label) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="deva-settings-actions">
                            <button type="submit" class="deva-btn deva-btn-primary">
                                <span class="dashicons dashicons-yes"></span>
                                <?php _e('Update Profile', 'hello-elementor-child'); ?>
                            </button>

                            <button type="button" class="deva-btn deva-btn-secondary" onclick="toggleSettings(event)">
                                <span class="dashicons dashicons-no-alt"></span>
                                <?php _e('Cancel', 'hello-elementor-child'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Section -->
    <div class="deva-schedule-section">
        <h3><?php _e('My schedule', 'hello-elementor-child'); ?></h3>

        <div class="deva-schedule-grid">
            <?php
            // Get user's appointments/bookings
            $appointments = deva_get_user_appointments($current_user->ID);

            if (!empty($appointments)) {
                foreach ($appointments as $index => $appointment) {
                    $session_number = $index + 1;
                    $is_completed = $appointment['status'] === 'completed';
                    $is_upcoming = $appointment['status'] === 'upcoming';
            ?>
                    <div class="deva-session-card">
                        <h4><?php echo sprintf(__('%s session', 'hello-elementor-child'), ucfirst(number_to_words($session_number))); ?></h4>

                        <p class="deva-session-time">
                            <span class="dashicons dashicons-clock"></span> <?php _e('Time:', 'hello-elementor-child'); ?> <?php echo esc_html($appointment['time']); ?>
                            <span class="deva-edit dashicons dashicons-edit" onclick="editSessionTime(<?php echo $appointment['id']; ?>)"></span>
                        </p>

                        <p class="deva-session-date">
                            <span class="dashicons dashicons-calendar-alt"></span> <?php _e('Date:', 'hello-elementor-child'); ?> <?php echo esc_html($appointment['date']); ?>
                            <span class="deva-edit dashicons dashicons-edit" onclick="editSessionDate(<?php echo $appointment['id']; ?>)"></span>
                        </p>

                        <p class="deva-session-calendar">
                            <span class="dashicons dashicons-calendar"></span> <?php _e('Save it to my calendar', 'hello-elementor-child'); ?>
                            <span class="deva-icon dashicons dashicons-bell" onclick="addToCalendar(<?php echo $appointment['id']; ?>)"></span>
                        </p>

                        <?php if ($is_upcoming): ?>
                            <button class="deva-call-button" onclick="joinCall('<?php echo esc_attr($appointment['meeting_url']); ?>')">
                                <span class="dashicons dashicons-video-alt3"></span> <?php _e('Call', 'hello-elementor-child'); ?>
                            </button>
                        <?php elseif ($is_completed): ?>
                            <button class="deva-call-button deva-completed" disabled>
                                <span class="dashicons dashicons-yes"></span> <?php _e('Completed', 'hello-elementor-child'); ?>
                            </button>
                        <?php else: ?>
                            <button class="deva-call-button deva-disabled" disabled>
                                <span class="dashicons dashicons-video-alt3"></span> <?php _e('Call', 'hello-elementor-child'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php
                }
            } else {
                // Default placeholder sessions
                ?>
                <div class="deva-session-card">
                    <h4><?php _e('First session', 'hello-elementor-child'); ?></h4>
                    <p class="deva-session-time">
                        <span class="dashicons dashicons-clock"></span> <?php _e('Time: Not scheduled', 'hello-elementor-child'); ?>
                    </p>
                    <p class="deva-session-date">
                        <span class="dashicons dashicons-calendar-alt"></span> <?php _e('Date: Not scheduled', 'hello-elementor-child'); ?>
                    </p>
                    <p class="deva-session-calendar">
                        <span class="dashicons dashicons-calendar"></span> <?php _e('Schedule your session', 'hello-elementor-child'); ?>
                    </p>
                    <button class="deva-call-button deva-disabled" disabled>
                        <?php _e('Schedule First', 'hello-elementor-child'); ?>
                    </button>
                </div>

                <div class="deva-session-card">
                    <h4><?php _e('Second session', 'hello-elementor-child'); ?></h4>
                    <p class="deva-session-time">
                        <span class="dashicons dashicons-clock"></span> <?php _e('Time: Not scheduled', 'hello-elementor-child'); ?>
                    </p>
                    <p class="deva-session-date">
                        <span class="dashicons dashicons-calendar-alt"></span> <?php _e('Date: Not scheduled', 'hello-elementor-child'); ?>
                    </p>
                    <p class="deva-session-calendar">
                        <span class="dashicons dashicons-calendar"></span> <?php _e('Schedule your session', 'hello-elementor-child'); ?>
                    </p>
                    <button class="deva-call-button deva-disabled" disabled>
                        <?php _e('Schedule First', 'hello-elementor-child'); ?>
                    </button>
                </div>
            <?php
            }
            ?>
        </div>
    </div>

    <?php endif; // End dashboard content conditional ?>

    <?php
    /**
     * WooCommerce Account Content Area
     * This section handles specific account pages like orders, downloads, etc.
     */
    
    // If we're on a specific endpoint (like orders), show WooCommerce content
    if (!empty($current_endpoint)) {
        echo '<div class="deva-woocommerce-content">';
        
        /**
         * Hook: woocommerce_account_content.
         */
        do_action('woocommerce_account_content');
        
        echo '</div>';
    }
    ?>

    <!-- Footer Button (only show on main dashboard) -->
    <?php if (empty($current_endpoint)) : ?>
    <div class="deva-account-footer">
        <button class="deva-schedule-next" onclick="scheduleNextSession()">
            <?php _e('Schedule Your Next Session', 'hello-elementor-child'); ?>
        </button>
    </div>
    <?php endif; ?>
</div>

<?php
/**
 * Hook: woocommerce_after_account_navigation.
 */
do_action('woocommerce_after_account_navigation');
?>

<script>
jQuery(document).ready(function($) {
    // Handle wishlist link clicks from account page
    $('.deva-wishlist-link').on('click', function(e) {
        // The link already has #wishlist in the href, so we don't need to do anything special
        // The cart page will handle the hash detection automatically
    });
    
    // Ensure logout link works properly
    $('.deva-logout-link').on('click', function(e) {
        // Don't prevent default - let the logout happen normally
        console.log('Logout link clicked, redirecting to:', this.href);
    });
});
</script>
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
<div class="deva-account-wrapper">
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
                        <!-- My Programs Section -->
                        <h3><?php _e('My Programs', 'hello-elementor-child'); ?></h3>

                        <?php
                        // Get current user
                        $current_user = wp_get_current_user();

                        // Get user's Amelia services and packages
                        $amelia_services_packages = deva_get_user_amelia_services_packages($current_user->ID);

                        // Filter to only show packages
                        $amelia_packages = array_filter($amelia_services_packages, function ($item) {
                            return $item['type'] === 'package';
                        });

                        if (!empty($amelia_packages)) {
                            foreach ($amelia_packages as $item) {
                                // Since we're only showing packages, this will always be true
                                $is_package = true;
                                $image_url = !empty($item['image']) ? $item['image'] : '';

                                // Use a default image if none provided
                                if (empty($image_url)) {
                                    $image_url = get_template_directory_uri() . '/assets/images/default-package.jpg';
                                }
                        ?>
                                <div class="deva-program-box deva-package">
                                    <img src="<?php echo esc_url($image_url); ?>"
                                        class="deva-program-image"
                                        alt="<?php echo esc_attr($item['name']); ?>"
                                        onerror="this.style.display='none'" />

                                    <div class="deva-program-info">
                                        <div class="deva-program-meta">
                                            <strong><?php echo esc_html($item['name']); ?></strong>
                                            <div class="deva-program-price">
                                                <?php if ($item['price']): ?>
                                                    <span class="deva-price">
                                                        <?php
                                                        // Format price properly - divide by 100 if needed for cents
                                                        $price = $item['price'];

                                                        if (function_exists('wc_price')) {
                                                            echo wc_price($price);
                                                        } else {
                                                            echo $price;
                                                        }
                                                        ?>
                                                    </span>
                                                <?php endif; ?>

                                                <!-- <div class="deva-program-status"> -->
                                                <!-- <span class="deva-status deva-status-<?php echo esc_attr($item['status']); ?>"> -->
                                                <?php //echo esc_html(ucfirst($item['status'])); 
                                                ?>
                                                <!-- </span> -->
                                            </div>
                                            <!-- <span class="deva-package-badge"><?php //_e('Package', 'hello-elementor-child'); 
                                                                                    ?></span> -->
                                        </div>

                                        <!-- <p><?php //echo esc_html($item['description'] ?: 'Comprehensive package program'); 
                                                ?></p> -->

                                        <?php if ($item['bookings_count']): ?>
                                            <!-- <div class="deva-program-duration"> -->
                                            <!-- <span class="dashicons dashicons-archive"></span> -->
                                            <?php //printf(__('%d sessions included', 'hello-elementor-child'), $item['bookings_count']); 
                                            ?>
                                            <!-- </div> -->
                                        <?php endif; ?>
                                    </div>


                                    <!-- </div> -->

                                    <?php if ($item['start_date']): ?>
                                        <!-- <div class="deva-package-dates"> -->
                                        <!-- <small> -->
                                        <!-- <span class="dashicons dashicons-calendar"></span> -->
                                        <?php if ($item['end_date']): ?>
                                            <?php
                                            $start = new DateTime($item['start_date']);
                                            $end = new DateTime($item['end_date']);
                                            // printf(
                                            //     __('Valid: %s - %s', 'hello-elementor-child'),
                                            //     $start->format('M j, Y'),
                                            //     $end->format('M j, Y')
                                            // );
                                            ?>
                                        <?php else: ?>
                                            <?php
                                            $start = new DateTime($item['start_date']);
                                            // printf(
                                            //     __('Started: %s', 'hello-elementor-child'),
                                            //     $start->format('M j, Y')
                                            // );
                                            ?>
                                        <?php endif; ?>
                                        <!-- </small> -->
                                        <!-- </div> -->
                                    <?php endif; ?>
                                </div>
                            <?php
                            }
                        } else {
                            // No Amelia packages found
                            ?>
                            <div class="deva-program-box deva-no-program">
                                <div class="deva-program-placeholder">
                                    <strong><?php _e('No Programs', 'hello-elementor-child'); ?></strong>
                                    <p><?php _e('You haven\'t purchased any program packages yet.', 'hello-elementor-child'); ?></p>
                                    <p><em><?php _e('Your purchased Amelia program packages will appear here.', 'hello-elementor-child'); ?></em></p>

                                    <!-- You can add a link to browse programs if needed -->
                                    <a href="<?php echo esc_url(home_url() . '/programs'); ?>" class="deva-browse-programs">
                                        <?php _e('Browse Programs', 'hello-elementor-child'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php } ?>

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
                                </a>
                                <!-- Expandable Settings Section -->
                                <div id="deva-settings-section" class="deva-settings-section">
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


                                        <!-- Form Actions -->
                                        <div class="deva-settings-actions">
                                            <button type="submit" class="deva-btn deva-btn-primary">
                                                <span class="dashicons dashicons-yes"></span>
                                                <?php _e('Update Profile', 'hello-elementor-child'); ?>
                                            </button>

                                            <button type="button" class="deva-btn deva-btn-secondary" onclick="cancelProfileEdit(event)">
                                                <span class="dashicons dashicons-no-alt"></span>
                                                <?php _e('Cancel', 'hello-elementor-child'); ?>
                                            </button>
                                        </div>
                                    </form>
                                </div>
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


                    </div>
                </div>
            </div>

            <!-- Schedule Section -->
            <div class="deva-schedule-section">
                <h3><?php _e('My schedule', 'hello-elementor-child'); ?></h3>

                <div class="deva-schedule-grid">
                    <?php
                    // Get user's appointments grouped by program
                    $grouped_appointments = deva_get_user_appointments_by_program($current_user->ID);

                    // Pagination settings
                    $page = isset($_GET['schedule_page']) ? max(1, intval($_GET['schedule_page'])) : 1;
                    $per_page = 10;

                    // Flatten appointments for pagination while maintaining program grouping
                    $all_appointments_for_pagination = [];
                    foreach ($grouped_appointments as $program) {
                        $all_appointments_for_pagination = array_merge($all_appointments_for_pagination, $program['appointments']);
                    }

                    $total_appointments = count($all_appointments_for_pagination);
                    $total_pages = ceil($total_appointments / $per_page);
                    $offset = ($page - 1) * $per_page;
                    $current_appointments = array_slice($all_appointments_for_pagination, $offset, $per_page);

                    // Re-group the current page appointments by program
                    $current_grouped_appointments = [];
                    foreach ($current_appointments as $appointment) {
                        $program_key = $appointment['packageId'] ? $appointment['packageId'] : 'individual_' . $appointment['service'];
                        $program_name = $appointment['packageName'] ?: $appointment['service'];

                        if (!isset($current_grouped_appointments[$program_key])) {
                            $current_grouped_appointments[$program_key] = [
                                'program_name' => $program_name,
                                'program_id' => $appointment['packageId'],
                                'appointments' => []
                            ];
                        }

                        $current_grouped_appointments[$program_key]['appointments'][] = $appointment;
                    }

                    if (!empty($current_grouped_appointments)) {
                    ?>
                        <?php foreach ($current_grouped_appointments as $program_key => $program_data): ?>
                            <div class="deva-schedule-program-group">
                                <h4 class="deva-schedule-program-title">
                                    <span class="dashicons dashicons-calendar-alt"></span>
                                    <?php echo esc_html($program_data['program_name']); ?>
                                </h4>

                                <div class="deva-schedule-cards">
                                    <?php foreach ($program_data['appointments'] as $appointment): ?>
                                        <div class="deva-schedule-card deva-schedule-status-<?php echo esc_attr($appointment['status']); ?>">
                                            <div class="deva-schedule-header">
                                                <div class="deva-schedule-appointment-number">
                                                    <span class="deva-appointment-badge">
                                                        <?php printf(__('%s Appointment', 'hello-elementor-child'), deva_number_to_ordinal($appointment['appointment_number'])); ?>
                                                    </span>
                                                </div>
                                                <div class="deva-schedule-status">
                                                    <span class="deva-status deva-status-<?php echo esc_attr($appointment['status']); ?>">
                                                        <?php echo esc_html(ucfirst($appointment['status'])); ?>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="deva-schedule-body">
                                                <div class="deva-schedule-date">
                                                    <span class="dashicons dashicons-calendar"></span>
                                                    <span class="deva-date"><?php echo esc_html($appointment['date']); ?></span>
                                                </div>

                                                <div class="deva-schedule-time">
                                                    <span class="dashicons dashicons-clock"></span>
                                                    <span class="deva-time"><?php echo esc_html($appointment['time']); ?></span>
                                                </div>

                                                <?php if ($appointment['status'] === 'upcoming' && !empty($appointment['meeting_url']) && $appointment['meeting_url'] !== '#'): ?>
                                                    <div class="deva-schedule-actions">
                                                        <a href="<?php echo esc_url($appointment['meeting_url']); ?>" target="_blank" class="deva-join-btn">
                                                            <span class="dashicons dashicons-video-alt2"></span>
                                                            <?php _e('Join Session', 'hello-elementor-child'); ?>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if ($total_pages > 1): ?>
                            <div class="deva-schedule-pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?schedule_page=<?php echo ($page - 1); ?>" class="deva-page-btn deva-prev">
                                        <span class="dashicons dashicons-arrow-left-alt2"></span> <?php _e('Previous', 'hello-elementor-child'); ?>
                                    </a>
                                <?php endif; ?>

                                <span class="deva-page-info">
                                    <?php printf(__('Page %d of %d', 'hello-elementor-child'), $page, $total_pages); ?>
                                </span>

                                <?php if ($page < $total_pages): ?>
                                    <a href="?schedule_page=<?php echo ($page + 1); ?>" class="deva-page-btn deva-next">
                                        <?php _e('Next', 'hello-elementor-child'); ?> <span class="dashicons dashicons-arrow-right-alt2"></span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    <?php } else { ?>
                        <div class="deva-no-schedule">
                            <p><?php _e('No appointments scheduled yet.', 'hello-elementor-child'); ?></p>
                            <p><em><?php _e('Your scheduled sessions will appear here once you book them.', 'hello-elementor-child'); ?></em></p>
                        </div>
                    <?php } ?>
                </div>
            </div>

        <?php endif; // End dashboard content conditional 
        ?>

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
                <a class="deva-schedule-next" href="<?php echo home_url() . '/customer'; ?>">
                    <?php _e('Manage your sessions', 'hello-elementor-child'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
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
        });
    });
</script>
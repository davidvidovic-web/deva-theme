<?php

/**
 * DEVA Custom Login/Registration Form
 * Template for [woocommerce_my_account] shortcode
 * @version 9.9.0
 */

defined('ABSPATH') || exit;

// If user is already logged in, redirect to account page
if (is_user_logged_in()) {
    wp_safe_redirect(wc_get_page_permalink('myaccount'));
    exit;
}
?>

<div class="deva-auth-container">
    <div class="deva-auth-form-wrapper">
        <div class="deva-auth-header">
            <h2><?php _e('Join DEVA', 'hello-elementor-child'); ?></h2>
            <p><?php _e('Create your account or sign in to continue your wellness journey', 'hello-elementor-child'); ?></p>
        </div>

        <form class="deva-auth-form" method="post" id="deva-auth-form">
            <?php wp_nonce_field('deva_auth_action', 'deva_auth_nonce'); ?>

            <!-- Form Mode Toggle -->
            <div class="deva-form-toggle">
                <button type="button" class="deva-toggle-btn active" data-mode="login">
                    <?php _e('Sign In', 'hello-elementor-child'); ?>
                </button>
                <button type="button" class="deva-toggle-btn" data-mode="register">
                    <?php _e('Create Account', 'hello-elementor-child'); ?>
                </button>
            </div>

            <!-- Error/Success Messages -->
            <div class="deva-auth-messages" id="deva-auth-messages"></div>

            <!-- Username Field (Always Visible) -->
            <div class="deva-form-group">
                <label for="deva_username"><?php _e('Username', 'hello-elementor-child'); ?> <span class="required">*</span></label>
                <input type="text"
                    name="username"
                    id="deva_username"
                    class="deva-input"
                    placeholder="<?php _e('Enter your username', 'hello-elementor-child'); ?>"
                    required />
            </div>

            <!-- Email Field (Visible for registration, hidden for login) -->
            <div class="deva-form-group deva-register-field" style="display: none;">
                <label for="deva_email"><?php _e('Email Address', 'hello-elementor-child'); ?> <span class="required">*</span></label>
                <input type="email"
                    name="email"
                    id="deva_email"
                    class="deva-input"
                    placeholder="<?php _e('Enter your email address', 'hello-elementor-child'); ?>" />
            </div>

            <!-- Password Field -->
            <div class="deva-form-group">
                <label for="deva_password"><?php _e('Password', 'hello-elementor-child'); ?> <span class="required">*</span></label>
                <div class="deva-password-wrapper">
                    <input type="password"
                        name="password"
                        id="deva_password"
                        class="deva-input"
                        placeholder="<?php _e('Enter your password', 'hello-elementor-child'); ?>"
                        required />
                    <button type="button" class="deva-password-toggle" onclick="togglePasswordVisibility('deva_password')">
                        <span class="show-text dashicons dashicons-visibility" title="<?php _e('Show password', 'hello-elementor-child'); ?>"></span>
                        <span class="hide-text dashicons dashicons-hidden" style="display: none;" title="<?php _e('Hide password', 'hello-elementor-child'); ?>"></span>
                    </button>
                </div>
            </div>

            <!-- Confirm Password Field (Only for registration) -->
            <div class="deva-form-group deva-register-field" style="display: none;">
                <label for="deva_confirm_password"><?php _e('Confirm Password', 'hello-elementor-child'); ?> <span class="required">*</span></label>
                <div class="deva-password-wrapper">
                    <input type="password"
                        name="confirm_password"
                        id="deva_confirm_password"
                        class="deva-input"
                        placeholder="<?php _e('Confirm your password', 'hello-elementor-child'); ?>" />
                    <button type="button" class="deva-password-toggle" onclick="togglePasswordVisibility('deva_confirm_password')">
                        <span class="show-text dashicons dashicons-visibility" title="<?php _e('Show password', 'hello-elementor-child'); ?>"></span>
                        <span class="hide-text dashicons dashicons-hidden" style="display: none;" title="<?php _e('Hide password', 'hello-elementor-child'); ?>"></span>
                    </button>
                </div>
            </div>

            <!-- Remember Me (Login only) -->
            <div class="deva-form-group deva-login-field">
                <label class="deva-checkbox-wrapper">
                    <input type="checkbox" name="rememberme" value="forever" />
                    <span class="deva-checkbox-custom"></span>
                    <span class="deva-checkbox-text"><?php _e('Remember me', 'hello-elementor-child'); ?></span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="deva-form-group">
                <button type="submit" class="deva-submit-btn" id="deva-submit-btn">
                    <span class="login-text"><?php _e('Sign In', 'hello-elementor-child'); ?></span>
                    <span class="register-text" style="display: none;"><?php _e('Create Account', 'hello-elementor-child'); ?></span>
                    <span class="loading-text" style="display: none;"><?php _e('Processing...', 'hello-elementor-child'); ?></span>
                </button>
            </div>

            <!-- Forgot Password Link (Login only) -->
            <div class="deva-form-footer deva-login-field">
                <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="deva-forgot-password">
                    <?php _e('Forgot your password?', 'hello-elementor-child'); ?>
                </a>
            </div>

            <!-- Hidden fields for form processing -->
            <input type="hidden" name="action" value="deva_auth_process" />
            <input type="hidden" name="auth_mode" id="auth_mode" value="login" />
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('deva-auth-form');
        const toggleBtns = document.querySelectorAll('.deva-toggle-btn');
        const registerFields = document.querySelectorAll('.deva-register-field');
        const loginFields = document.querySelectorAll('.deva-login-field');
        const authModeInput = document.getElementById('auth_mode');
        const submitBtn = document.getElementById('deva-submit-btn');
        const loginText = submitBtn.querySelector('.login-text');
        const registerText = submitBtn.querySelector('.register-text');

        // Form mode toggle
        toggleBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const mode = this.dataset.mode;

                // Update button states
                toggleBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                // Update form fields visibility
                if (mode === 'register') {
                    registerFields.forEach(field => field.style.display = 'block');
                    loginFields.forEach(field => field.style.display = 'none');
                    loginText.style.display = 'none';
                    registerText.style.display = 'inline';

                    // Make email and confirm password required
                    document.getElementById('deva_email').required = true;
                    document.getElementById('deva_confirm_password').required = true;
                } else {
                    registerFields.forEach(field => field.style.display = 'none');
                    loginFields.forEach(field => field.style.display = 'block');
                    loginText.style.display = 'inline';
                    registerText.style.display = 'none';

                    // Remove email and confirm password required
                    document.getElementById('deva_email').required = false;
                    document.getElementById('deva_confirm_password').required = false;
                }

                authModeInput.value = mode;
            });
        });

        // Form submission
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            const submitBtn = document.getElementById('deva-submit-btn');
            const loadingText = submitBtn.querySelector('.loading-text');
            const currentText = authModeInput.value === 'login' ? loginText : registerText;

            // Show loading state
            submitBtn.disabled = true;
            currentText.style.display = 'none';
            loadingText.style.display = 'inline';

            // Clear previous messages
            document.getElementById('deva-auth-messages').innerHTML = '';

            // Validate passwords match for registration
            if (authModeInput.value === 'register') {
                const password = document.getElementById('deva_password').value;
                const confirmPassword = document.getElementById('deva_confirm_password').value;

                if (password !== confirmPassword) {
                    showMessage('Passwords do not match.', 'error');
                    resetSubmitButton();
                    return;
                }
            }

            // Submit via AJAX
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showMessage(data.data.message, 'success');
                        if (data.data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.data.redirect;
                            }, 1500);
                        }
                    } else {
                        showMessage(data.data || 'An error occurred. Please try again.', 'error');
                    }
                })
                .catch(error => {
                    showMessage('Network error. Please try again.', 'error');
                })
                .finally(() => {
                    resetSubmitButton();
                });
        });

        function resetSubmitButton() {
            const submitBtn = document.getElementById('deva-submit-btn');
            const loadingText = submitBtn.querySelector('.loading-text');
            const currentText = authModeInput.value === 'login' ? loginText : registerText;

            submitBtn.disabled = false;
            loadingText.style.display = 'none';
            currentText.style.display = 'inline';
        }

        function showMessage(message, type) {
            const messagesDiv = document.getElementById('deva-auth-messages');
            messagesDiv.innerHTML = `<div class="deva-message deva-${type}">${message}</div>`;
        }
    });

    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const button = field.nextElementSibling;
        const showText = button.querySelector('.show-text');
        const hideText = button.querySelector('.hide-text');

        if (field.type === 'password') {
            field.type = 'text';
            showText.style.display = 'none';
            hideText.style.display = 'inline';
        } else {
            field.type = 'password';
            showText.style.display = 'inline';
            hideText.style.display = 'none';
        }
    }
</script>
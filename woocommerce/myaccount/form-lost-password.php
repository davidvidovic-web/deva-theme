<?php
/**
 * DEVA Lost Password Form
 * Template for password reset requests
 */

defined( 'ABSPATH' ) || exit;

// Show error/success messages
if ( ! empty( $_GET['reset-link-sent'] ) ) {
    $message_type = 'success';
    $message_text = __( 'Password reset email has been sent. Please check your email inbox and spam folder.', 'hello-elementor-child' );
} elseif ( ! empty( $_GET['show-reset-form'] ) ) {
    $message_type = 'info';
    $message_text = __( 'Enter your new password below.', 'hello-elementor-child' );
} elseif ( ! empty( $_GET['password-reset'] ) ) {
    $message_type = 'success';
    $message_text = __( 'Your password has been reset successfully. You can now sign in with your new password.', 'hello-elementor-child' );
} elseif ( ! empty( $_GET['invalid-key'] ) ) {
    $message_type = 'error';
    $message_text = __( 'Invalid or expired reset link. Please request a new password reset.', 'hello-elementor-child' );
}
?>

<div class="deva-auth-container">
    <div class="deva-auth-form-wrapper">
        <div class="deva-auth-header">
            <?php if ( ! empty( $_GET['show-reset-form'] ) ): ?>
                <h2><?php _e( 'Set New Password', 'hello-elementor-child' ); ?></h2>
                <p><?php _e( 'Create a strong password for your DEVA account', 'hello-elementor-child' ); ?></p>
            <?php else: ?>
                <h2><?php _e( 'Reset Your Password', 'hello-elementor-child' ); ?></h2>
                <p><?php _e( 'Enter your email address and we\'ll send you a link to reset your password', 'hello-elementor-child' ); ?></p>
            <?php endif; ?>
        </div>

        <!-- Messages -->
        <?php if ( isset( $message_text ) ): ?>
            <div class="deva-auth-messages">
                <div class="deva-message deva-<?php echo esc_attr( $message_type ); ?>">
                    <?php echo esc_html( $message_text ); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ( ! empty( $_GET['show-reset-form'] ) ): ?>
            <!-- Reset Password Form -->
            <form class="deva-auth-form deva-reset-form" method="post" id="deva-reset-form">
                <?php wp_nonce_field( 'reset_password_action', 'reset_password_nonce' ); ?>
                
                <!-- New Password Field -->
                <div class="deva-form-group">
                    <label for="deva_new_password"><?php _e( 'New Password', 'hello-elementor-child' ); ?> <span class="required">*</span></label>
                    <div class="deva-password-wrapper">
                        <input type="password" 
                               name="password_1" 
                               id="deva_new_password" 
                               class="deva-input" 
                               placeholder="<?php _e( 'Enter your new password', 'hello-elementor-child' ); ?>" 
                               required />
                        <button type="button" class="deva-password-toggle" onclick="togglePasswordVisibility('deva_new_password')">
                            <span class="show-text dashicons dashicons-visibility" title="<?php _e( 'Show password', 'hello-elementor-child' ); ?>"></span>
                            <span class="hide-text dashicons dashicons-hidden" style="display: none;" title="<?php _e( 'Hide password', 'hello-elementor-child' ); ?>"></span>
                        </button>
                    </div>
                    <div class="deva-password-strength" id="password-strength"></div>
                </div>

                <!-- Confirm New Password Field -->
                <div class="deva-form-group">
                    <label for="deva_confirm_password"><?php _e( 'Confirm New Password', 'hello-elementor-child' ); ?> <span class="required">*</span></label>
                    <div class="deva-password-wrapper">
                        <input type="password" 
                               name="password_2" 
                               id="deva_confirm_password" 
                               class="deva-input" 
                               placeholder="<?php _e( 'Confirm your new password', 'hello-elementor-child' ); ?>" 
                               required />
                        <button type="button" class="deva-password-toggle" onclick="togglePasswordVisibility('deva_confirm_password')">
                            <span class="show-text dashicons dashicons-visibility" title="<?php _e( 'Show password', 'hello-elementor-child' ); ?>"></span>
                            <span class="hide-text dashicons dashicons-hidden" style="display: none;" title="<?php _e( 'Hide password', 'hello-elementor-child' ); ?>"></span>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="deva-form-group">
                    <button type="submit" class="deva-submit-btn" id="deva-reset-submit">
                        <span class="submit-text"><?php _e( 'Update Password', 'hello-elementor-child' ); ?></span>
                        <span class="loading-text" style="display: none;"><?php _e( 'Updating...', 'hello-elementor-child' ); ?></span>
                    </button>
                </div>

                <!-- Hidden fields -->
                <input type="hidden" name="action" value="resetpass" />
                <input type="hidden" name="key" value="<?php echo esc_attr( $_GET['key'] ?? '' ); ?>" />
                <input type="hidden" name="login" value="<?php echo esc_attr( $_GET['login'] ?? '' ); ?>" />
            </form>

        <?php else: ?>
            <!-- Lost Password Form -->
            <form class="deva-auth-form deva-lost-password-form" method="post" id="deva-lost-password-form">
                <?php wp_nonce_field( 'lost_password_action', 'lost_password_nonce' ); ?>
                
                <div class="deva-auth-messages" id="deva-auth-messages"></div>

                <!-- Email Field -->
                <div class="deva-form-group">
                    <label for="deva_user_email"><?php _e( 'Email Address', 'hello-elementor-child' ); ?> <span class="required">*</span></label>
                    <input type="email" 
                           name="user_login" 
                           id="deva_user_email" 
                           class="deva-input" 
                           placeholder="<?php _e( 'Enter your email address', 'hello-elementor-child' ); ?>" 
                           required />
                    <small class="deva-field-help"><?php _e( 'Enter the email address associated with your account', 'hello-elementor-child' ); ?></small>
                </div>

                <!-- Submit Button -->
                <div class="deva-form-group">
                    <button type="submit" class="deva-submit-btn" id="deva-lost-password-submit">
                        <span class="submit-text"><?php _e( 'Send Reset Link', 'hello-elementor-child' ); ?></span>
                        <span class="loading-text" style="display: none;"><?php _e( 'Sending...', 'hello-elementor-child' ); ?></span>
                    </button>
                </div>

                <!-- Hidden field -->
                <input type="hidden" name="action" value="deva_lost_password" />
            </form>
        <?php endif; ?>

        <!-- Footer Links -->
        <div class="deva-form-footer">
            <?php if ( ! empty( $_GET['show-reset-form'] ) ): ?>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="deva-back-link">
                    ← <?php _e( 'Back to Sign In', 'hello-elementor-child' ); ?>
                </a>
            <?php else: ?>
                <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="deva-back-link">
                    ← <?php _e( 'Back to Sign In', 'hello-elementor-child' ); ?>
                </a>
                <div class="deva-help-text">
                    <p><?php _e( 'Remember your password?', 'hello-elementor-child' ); ?> 
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="deva-signin-link">
                        <?php _e( 'Sign in here', 'hello-elementor-child' ); ?>
                    </a></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lostPasswordForm = document.getElementById('deva-lost-password-form');
    const resetForm = document.getElementById('deva-reset-form');
    
    // Handle lost password form submission
    if (lostPasswordForm) {
        lostPasswordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const submitBtn = document.getElementById('deva-lost-password-submit');
            const submitText = submitBtn.querySelector('.submit-text');
            const loadingText = submitBtn.querySelector('.loading-text');
            
            // Show loading state
            submitBtn.disabled = true;
            submitText.style.display = 'none';
            loadingText.style.display = 'inline';
            
            // Clear previous messages
            document.getElementById('deva-auth-messages').innerHTML = '';
            
            // Submit via AJAX
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.data.message, 'success');
                    // Clear form
                    lostPasswordForm.reset();
                } else {
                    showMessage(data.data || 'An error occurred. Please try again.', 'error');
                }
            })
            .catch(error => {
                showMessage('Network error. Please try again.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitText.style.display = 'inline';
                loadingText.style.display = 'none';
            });
        });
    }
    
    // Handle reset password form
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            const password1 = document.getElementById('deva_new_password').value;
            const password2 = document.getElementById('deva_confirm_password').value;
            
            if (password1 !== password2) {
                e.preventDefault();
                showMessage('Passwords do not match.', 'error');
                return;
            }
            
            if (password1.length < 6) {
                e.preventDefault();
                showMessage('Password must be at least 6 characters long.', 'error');
                return;
            }
        });
        
        // Real-time password validation
        const newPasswordField = document.getElementById('deva_new_password');
        const confirmPasswordField = document.getElementById('deva_confirm_password');
        
        if (newPasswordField) {
            newPasswordField.addEventListener('input', function() {
                validatePasswordStrength(this.value);
            });
        }
        
        if (confirmPasswordField) {
            confirmPasswordField.addEventListener('input', function() {
                const password1 = newPasswordField.value;
                const password2 = this.value;
                
                if (password2 && password1 !== password2) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }
            });
        }
    }
    
    function validatePasswordStrength(password) {
        const strengthIndicator = document.getElementById('password-strength');
        if (!strengthIndicator) return;
        
        let strength = 0;
        let feedback = [];
        
        if (password.length >= 8) {
            strength++;
        } else {
            feedback.push('At least 8 characters');
        }
        
        if (password.match(/[a-z]/)) strength++;
        else feedback.push('lowercase letter');
        
        if (password.match(/[A-Z]/)) strength++;
        else feedback.push('uppercase letter');
        
        if (password.match(/[0-9]/)) strength++;
        else feedback.push('number');
        
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        else feedback.push('special character');
        
        const levels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['#dc2626', '#f59e0b', '#eab308', '#22c55e', '#16a34a'];
        
        if (password.length > 0) {
            strengthIndicator.innerHTML = `
                <span style="color: ${colors[strength - 1] || '#6b7280'}; font-weight: 500;">
                    ${levels[strength - 1] || 'Very Weak'}
                </span>
                ${feedback.length > 0 ? `<span style="color: #6b7280; font-size: 0.75rem;"> - Add: ${feedback.join(', ')}</span>` : ''}
            `;
        } else {
            strengthIndicator.innerHTML = '';
        }
    }
    
    function showMessage(message, type) {
        const messagesDiv = document.getElementById('deva-auth-messages');
        if (messagesDiv) {
            messagesDiv.innerHTML = `<div class="deva-message deva-${type}">${message}</div>`;
            messagesDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
});

// Password visibility toggle function
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

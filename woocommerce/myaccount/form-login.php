<?php
/**
 * Login Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-login.php.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="deva-auth-container">
    <div class="deva-auth-form-wrapper">
        <div class="deva-auth-header">
            <h2><?php _e('Join DEVA', 'hello-elementor-child'); ?></h2>
            <p><?php _e('Create your account or sign in to continue your wellness journey', 'hello-elementor-child'); ?></p>
        </div>

        <!-- Form Mode Toggle -->
        <div class="deva-form-toggle">
            <button type="button" class="deva-toggle-btn active" data-mode="login">
                <?php _e('Sign In', 'hello-elementor-child'); ?>
            </button>
            <button type="button" class="deva-toggle-btn" data-mode="register">
                <?php _e('Create Account', 'hello-elementor-child'); ?>
            </button>
        </div>

        <div class="u-columns col2-set" id="customer_login">

            <div class="u-column1 deva-login-form-wrapper">

                <!-- LOGIN FORM -->
                <form class="woocommerce-form woocommerce-form-login login deva-auth-form" method="post">

                    <?php do_action( 'woocommerce_login_form_start' ); ?>

                    <!-- Username Field -->
                    <div class="deva-form-group">
                        <label for="username"><?php _e('Username or email address', 'hello-elementor-child'); ?> <span class="required">*</span></label>
                        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text deva-input" 
                               name="username" id="username" autocomplete="username" 
                               value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" 
                               placeholder="<?php _e('Enter your username or email', 'hello-elementor-child'); ?>"
                               required />
                    </div>

                    <!-- Password Field -->
                    <div class="deva-form-group">
                        <label for="password"><?php _e('Password', 'hello-elementor-child'); ?> <span class="required">*</span></label>
                        <div class="deva-password-wrapper">
                            <input class="woocommerce-Input woocommerce-Input--text input-text deva-input" 
                                   type="password" name="password" id="password" autocomplete="current-password" 
                                   placeholder="<?php _e('Enter your password', 'hello-elementor-child'); ?>"
                                   required />
                        </div>
                    </div>

                    <?php do_action( 'woocommerce_login_form' ); ?>

                    <!-- Remember Me -->
                    <div class="deva-form-group deva-login-field">
                        <label class="deva-checkbox-wrapper woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
                            <input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
                            <span class="deva-checkbox-custom"></span>
                            <span class="deva-checkbox-text"><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="deva-form-group">
                        <?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
                        <button type="submit" class="woocommerce-button button woocommerce-form-login__submit deva-submit-btn" 
                                name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
                            <?php esc_html_e( 'Sign In', 'hello-elementor-child' ); ?>
                        </button>
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="deva-form-footer deva-login-field">
                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>" class="deva-forgot-password">
                            <?php esc_html_e( 'Forgot your password?', 'hello-elementor-child' ); ?>
                        </a>
                    </div>

                    <?php do_action( 'woocommerce_login_form_end' ); ?>

                </form>

            </div>

            <div class="u-column2 deva-register-form-wrapper" style="display: none;">

                <!-- REGISTRATION FORM -->
                <form method="post" class="woocommerce-form woocommerce-form-register register deva-auth-form deva-register-form" <?php do_action( 'woocommerce_register_form_tag' ); ?> >

                    <?php do_action( 'woocommerce_register_form_start' ); ?>

                    <?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
                        <!-- Username Field for Registration -->
                        <div class="deva-form-group">
                            <label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?> <span class="required">*</span></label>
                            <input type="text" class="woocommerce-Input woocommerce-Input--text input-text deva-input" 
                                   name="username" id="reg_username" autocomplete="username" 
                                   value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" 
                                   placeholder="<?php _e('Enter your username', 'hello-elementor-child'); ?>"
                                   required />
                        </div>
                    <?php endif; ?>

                    <!-- Email Field for Registration -->
                    <div class="deva-form-group">
                        <label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?> <span class="required">*</span></label>
                        <input type="email" class="woocommerce-Input woocommerce-Input--text input-text deva-input" 
                               name="email" id="reg_email" autocomplete="email" 
                               value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" 
                               placeholder="<?php _e('Enter your email address', 'hello-elementor-child'); ?>"
                               required />
                    </div>

                    <?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
                        <!-- Password Field for Registration -->
                        <div class="deva-form-group">
                            <label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?> <span class="required">*</span></label>
                            <div class="deva-password-wrapper">
                                <input type="password" class="woocommerce-Input woocommerce-Input--text input-text deva-input" 
                                       name="password" id="reg_password" autocomplete="new-password" 
                                       placeholder="<?php _e('Enter your password', 'hello-elementor-child'); ?>"
                                       required />
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="deva-form-group">
                            <p><?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?></p>
                        </div>
                    <?php endif; ?>

                    <?php do_action( 'woocommerce_register_form' ); ?>

                    <!-- Submit Button for Registration -->
                    <div class="deva-form-group">
                        <?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
                        <button type="submit" class="woocommerce-button button woocommerce-form-register__submit deva-submit-btn" 
                                name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
                            <?php esc_html_e( 'Create Account', 'hello-elementor-child' ); ?>
                        </button>
                    </div>

                    <?php do_action( 'woocommerce_register_form_end' ); ?>

                </form>

            </div>

<script>
// Toggle between login and register forms with WordPress standard forms
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtns = document.querySelectorAll('.deva-toggle-btn');
    const loginWrapper = document.querySelector('.deva-login-form-wrapper');
    const registerWrapper = document.querySelector('.deva-register-form-wrapper');
    
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const mode = this.dataset.mode;
            
            // Update active button
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Toggle form visibility
            if (mode === 'login') {
                if (loginWrapper) loginWrapper.style.display = 'block';
                if (registerWrapper) registerWrapper.style.display = 'none';
            } else {
                if (loginWrapper) loginWrapper.style.display = 'none';
                if (registerWrapper) registerWrapper.style.display = 'block';
            }
        });
    });
});
</script>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
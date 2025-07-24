/**
 * DEVA Authentication JavaScript
 * Handles login/registration form interactions
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('deva-auth-form');
    const toggleBtns = document.querySelectorAll('.deva-toggle-btn');
    const registerFields = document.querySelectorAll('.deva-register-field');
    const loginFields = document.querySelectorAll('.deva-login-field');
    const authModeInput = document.getElementById('auth_mode');
    const submitBtn = document.getElementById('deva-submit-btn');
    const messagesDiv = document.getElementById('deva-auth-messages');
    
    if (!form) return; // Exit if form not found
    
    const loginText = submitBtn.querySelector('.login-text');
    const registerText = submitBtn.querySelector('.register-text');
    const loadingText = submitBtn.querySelector('.loading-text');

    // Form mode toggle functionality
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const mode = this.dataset.mode;
            
            // Update button states
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Update form fields visibility with smooth transitions
            if (mode === 'register') {
                registerFields.forEach(field => {
                    field.style.display = 'block';
                    field.style.opacity = '0';
                    setTimeout(() => {
                        field.style.opacity = '1';
                    }, 10);
                });
                
                loginFields.forEach(field => {
                    field.style.opacity = '0';
                    setTimeout(() => {
                        field.style.display = 'none';
                    }, 300);
                });
                
                // Update button text
                if (loginText && registerText) {
                    loginText.style.display = 'none';
                    registerText.style.display = 'inline';
                }
                
                // Make email and confirm password required
                const emailField = document.getElementById('deva_email');
                const confirmPasswordField = document.getElementById('deva_confirm_password');
                if (emailField) emailField.required = true;
                if (confirmPasswordField) confirmPasswordField.required = true;
                
                // Update form title
                updateFormTitle('Create Account', 'Join DEVA and start your wellness journey');
                
            } else {
                registerFields.forEach(field => {
                    field.style.opacity = '0';
                    setTimeout(() => {
                        field.style.display = 'none';
                    }, 300);
                });
                
                loginFields.forEach(field => {
                    field.style.display = 'block';
                    field.style.opacity = '0';
                    setTimeout(() => {
                        field.style.opacity = '1';
                    }, 10);
                });
                
                // Update button text
                if (loginText && registerText) {
                    loginText.style.display = 'inline';
                    registerText.style.display = 'none';
                }
                
                // Remove email and confirm password required
                const emailField = document.getElementById('deva_email');
                const confirmPasswordField = document.getElementById('deva_confirm_password');
                if (emailField) emailField.required = false;
                if (confirmPasswordField) confirmPasswordField.required = false;
                
                // Update form title
                updateFormTitle('Sign in', 'By creating an account or signing you agree to our Terms and Conditions');
            }
            
            authModeInput.value = mode;
            clearMessages();
        });
    });

    // Form validation and submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Clear previous messages
        clearMessages();
        
        // Get form data
        const formData = new FormData(form);
        const authMode = authModeInput.value;
        
        // Client-side validation
        if (!validateForm(authMode)) {
            return;
        }
        
        // Show loading state
        setLoadingState(true);
        
        // Submit via AJAX
        fetch(deva_auth_ajax.ajax_url, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                showMessage(data.data.message, 'success');
                
                // Clear form
                form.reset();
                
                // Redirect after a short delay
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
            console.error('Auth error:', error);
            showMessage('Network error. Please check your connection and try again.', 'error');
        })
        .finally(() => {
            setLoadingState(false);
        });
    });
    
    // Real-time validation
    setupRealTimeValidation();
    
    // Helper functions
    function validateForm(authMode) {
        const username = document.getElementById('deva_username').value.trim();
        const password = document.getElementById('deva_password').value;
        
        if (!username) {
            showMessage('Username is required.', 'error');
            document.getElementById('deva_username').focus();
            return false;
        }
        
        if (!password) {
            showMessage('Password is required.', 'error');
            document.getElementById('deva_password').focus();
            return false;
        }
        
        if (authMode === 'register') {
            const email = document.getElementById('deva_email').value.trim();
            const confirmPassword = document.getElementById('deva_confirm_password').value;
            
            if (!email) {
                showMessage('Email address is required.', 'error');
                document.getElementById('deva_email').focus();
                return false;
            }
            
            if (!isValidEmail(email)) {
                showMessage('Please enter a valid email address.', 'error');
                document.getElementById('deva_email').focus();
                return false;
            }
            
            if (!confirmPassword) {
                showMessage('Please confirm your password.', 'error');
                document.getElementById('deva_confirm_password').focus();
                return false;
            }
            
            if (password !== confirmPassword) {
                showMessage('Passwords do not match.', 'error');
                document.getElementById('deva_confirm_password').focus();
                return false;
            }
            
            if (password.length < 6) {
                showMessage('Password must be at least 6 characters long.', 'error');
                document.getElementById('deva_password').focus();
                return false;
            }
        }
        
        return true;
    }
    
    function setupRealTimeValidation() {
        const passwordField = document.getElementById('deva_password');
        const confirmPasswordField = document.getElementById('deva_confirm_password');
        const emailField = document.getElementById('deva_email');
        
        // Password strength indicator
        if (passwordField) {
            passwordField.addEventListener('input', function() {
                validatePasswordStrength(this.value);
            });
        }
        
        // Password match validation
        if (confirmPasswordField) {
            confirmPasswordField.addEventListener('input', function() {
                validatePasswordMatch();
            });
        }
        
        // Email validation
        if (emailField) {
            emailField.addEventListener('blur', function() {
                if (this.value && !isValidEmail(this.value)) {
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
        if (password.length >= 6) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        const levels = ['Very Weak', 'Weak', 'Fair', 'Good', 'Strong'];
        const colors = ['#dc2626', '#f59e0b', '#eab308', '#22c55e', '#16a34a'];
        
        strengthIndicator.textContent = levels[strength - 1] || '';
        strengthIndicator.style.color = colors[strength - 1] || '#6b7280';
    }
    
    function validatePasswordMatch() {
        const password = document.getElementById('deva_password').value;
        const confirmPassword = document.getElementById('deva_confirm_password').value;
        const confirmField = document.getElementById('deva_confirm_password');
        
        if (confirmPassword && password !== confirmPassword) {
            confirmField.classList.add('error');
        } else {
            confirmField.classList.remove('error');
        }
    }
    
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }
    
    function setLoadingState(loading) {
        const currentText = authModeInput.value === 'login' ? loginText : registerText;
        
        submitBtn.disabled = loading;
        
        if (loading) {
            if (currentText) currentText.style.display = 'none';
            if (loadingText) loadingText.style.display = 'inline';
        } else {
            if (loadingText) loadingText.style.display = 'none';
            if (currentText) currentText.style.display = 'inline';
        }
    }
    
    function showMessage(message, type) {
        if (!messagesDiv) return;
        
        messagesDiv.innerHTML = `<div class="deva-message deva-${type}">${message}</div>`;
        
        // Scroll to message
        messagesDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        
        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                clearMessages();
            }, 5000);
        }
    }
    
    function clearMessages() {
        if (messagesDiv) {
            messagesDiv.innerHTML = '';
        }
        
        // Clear input error states
        document.querySelectorAll('.deva-input.error').forEach(input => {
            input.classList.remove('error');
        });
    }
    
    function updateFormTitle(title, subtitle) {
        const titleEl = document.querySelector('.deva-auth-header h2');
        const subtitleEl = document.querySelector('.deva-auth-header p');
        
        if (titleEl) titleEl.textContent = title;
        if (subtitleEl) subtitleEl.textContent = subtitle;
    }
});

// Password visibility toggle function (global scope for inline onclick)
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    if (!field) return;
    
    const button = field.nextElementSibling;
    if (!button) return;
    
    const showText = button.querySelector('.show-text');
    const hideText = button.querySelector('.hide-text');
    
    if (!showText || !hideText) return;
    
    if (field.type === 'password') {
        field.type = 'text';
        showText.style.display = 'none';
        hideText.style.display = 'flex';
    } else {
        field.type = 'password';
        showText.style.display = 'flex';
        hideText.style.display = 'none';
    }
}

// Form accessibility improvements
document.addEventListener('keydown', function(e) {
    // Enable form submission with Enter key
    if (e.key === 'Enter' && e.target.classList.contains('deva-input')) {
        const form = document.getElementById('deva-auth-form');
        if (form) {
            e.preventDefault();
            form.dispatchEvent(new Event('submit'));
        }
    }
});

// Auto-focus first field when form loads
window.addEventListener('load', function() {
    const firstInput = document.getElementById('deva_username');
    if (firstInput && !document.querySelector('input:focus')) {
        firstInput.focus();
    }
});

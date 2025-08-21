/**
 * DEVA Notifications Jav/**
 * Convert WooCommerce notices to toast notifications
 */
function convertWooCommerceNoticesToToasts() {
    const noticeWrappers = document.querySelectorAll('.woocommerce-notices-wrapper');
    
    noticeWrappers.forEach(wrapper => {
        const notices = wrapper.querySelectorAll('.woocommerce-message, .woocommerce-info, .woocommerce-error, .woocommerce-warning');
        
        notices.forEach(notice => {
            const message = getNoticeText(notice);
            const type = getNoticeType(notice);
            
            if (message && message.trim()) {
                // Create toast
                showWooCommerceToast(message, type);
                
                // Hide original notice
                notice.style.display = 'none';
            }
        });
        
        // Hide the wrapper if all notices are converted
        const visibleNotices = wrapper.querySelectorAll('.woocommerce-message:not([style*="display: none"]), .woocommerce-info:not([style*="display: none"]), .woocommerce-error:not([style*="display: none"]), .woocommerce-warning:not([style*="display: none"])');
        if (visibleNotices.length === 0) {
            wrapper.style.display = 'none';
        }
    });
}

/**
 * Extract text content from a notice element
 */
function getNoticeText(notice) {
    // Clone the notice to avoid modifying the original
    const clone = notice.cloneNode(true);
    
    // Remove dismiss buttons
    const dismissBtns = clone.querySelectorAll('.notice-dismiss, .dismiss-btn');
    dismissBtns.forEach(btn => btn.remove());
    
    // Get text content
    return clone.textContent.trim();
}

/**
 * Determine notice type from CSS classes
 */
function getNoticeType(notice) {
    if (notice.classList.contains('woocommerce-error')) return 'error';
    if (notice.classList.contains('woocommerce-warning')) return 'warning';
    if (notice.classList.contains('woocommerce-info')) return 'info';
    if (notice.classList.contains('woocommerce-message')) return 'success';
    return 'info';
}

/**
 * Show WooCommerce-specific toast with appropriate styling
 */
function showWooCommerceToast(message, type = 'info', duration = null) {
    // Set default duration based on type
    if (duration === null) {
        duration = type === 'error' ? 8000 : type === 'warning' ? 6000 : 5000;
    }
    
    const toast = showToast(message, type, duration);
    toast.classList.add('woocommerce-toast');
    
    return toast;
}

/**
 * Watch for new WooCommerce notices being added dynamically
 */
function observeWooCommerceNotices() {
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            mutation.addedNodes.forEach(function(node) {
                if (node.nodeType === 1) { // Element node
                    // Check if the added node is a notice wrapper or contains notices
                    const wrappers = node.classList && node.classList.contains('woocommerce-notices-wrapper') 
                        ? [node] 
                        : node.querySelectorAll ? node.querySelectorAll('.woocommerce-notices-wrapper') : [];
                    
                    const notices = node.classList && (node.classList.contains('woocommerce-message') || 
                                                     node.classList.contains('woocommerce-info') || 
                                                     node.classList.contains('woocommerce-error') ||
                                                     node.classList.contains('woocommerce-warning'))
                        ? [node]
                        : node.querySelectorAll ? node.querySelectorAll('.woocommerce-message, .woocommerce-info, .woocommerce-error, .woocommerce-warning') : [];
                    
                    // Convert new wrappers
                    wrappers.forEach(wrapper => {
                        setTimeout(() => convertWooCommerceNoticesToToasts(), 100);
                    });
                    
                    // Convert individual notices
                    notices.forEach(notice => {
                        const message = getNoticeText(notice);
                        const type = getNoticeType(notice);
                        
                        if (message && message.trim()) {
                            showWooCommerceToast(message, type);
                            notice.style.display = 'none';
                        }
                    });
                }
            });
        });
    });
    
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
}

/**
 * DEVA Notifications JavaScript
 * Handles enhanced notification behavior and animations
 */

document.addEventListener('DOMContentLoaded', function() {
    initializeNotifications();
    
    // Test toast on page load for debugging
    console.log('DEVA Notifications loaded');
    
    // Test function - remove in production
    window.testToast = function() {
        showWooCommerceToast('Test toast notification!', 'success');
    };
});

function initializeNotifications() {
    // Add dismiss functionality to WooCommerce messages
    addDismissButtons();
    
    // Convert WooCommerce notices to toasts
    convertWooCommerceNoticesToToasts();
    
    // Auto-dismiss success messages
    autoHideSuccessMessages();
    
    // Enhance existing messages with DEVA styling
    enhanceExistingMessages();
    
    // Initialize toast notifications
    createToastContainer();
    
    // Watch for new WooCommerce notices
    observeWooCommerceNotices();
}

/**
 * Add dismiss buttons to messages that don't have them
 */
function addDismissButtons() {
    const messages = document.querySelectorAll('.woocommerce-message, .woocommerce-info, .woocommerce-error, .woocommerce-warning, .deva-notification, .deva-message');
    
    messages.forEach(message => {
        // Skip if already has dismiss button
        if (message.querySelector('.dismiss-btn, .woocommerce-message-dismiss, .woocommerce-info-dismiss, .woocommerce-error-dismiss')) {
            return;
        }
        
        const dismissBtn = document.createElement('button');
        dismissBtn.className = 'dismiss-btn';
        dismissBtn.innerHTML = '×';
        dismissBtn.setAttribute('aria-label', 'Dismiss notification');
        dismissBtn.type = 'button';
        
        dismissBtn.addEventListener('click', function() {
            dismissMessage(message);
        });
        
        message.appendChild(dismissBtn);
        message.style.position = 'relative';
    });
}

/**
 * Auto-hide success messages after 5 seconds
 */
function autoHideSuccessMessages() {
    const successMessages = document.querySelectorAll('.woocommerce-message, .deva-notification-success, .deva-message.deva-success');
    
    successMessages.forEach(message => {
        // Add auto-dismiss attribute for visual indicator
        message.setAttribute('data-auto-dismiss', 'true');
        
        setTimeout(() => {
            if (message.parentNode) {
                dismissMessage(message);
            }
        }, 5000);
    });
}

/**
 * Enhance existing messages with better accessibility and functionality
 */
function enhanceExistingMessages() {
    const messages = document.querySelectorAll('.woocommerce-message, .woocommerce-info, .woocommerce-error, .woocommerce-warning');
    
    messages.forEach(message => {
        // Add ARIA roles for better accessibility
        if (message.classList.contains('woocommerce-error')) {
            message.setAttribute('role', 'alert');
            message.setAttribute('aria-live', 'assertive');
        } else {
            message.setAttribute('role', 'status');
            message.setAttribute('aria-live', 'polite');
        }
        
        // Add tabindex for keyboard navigation
        message.setAttribute('tabindex', '0');
        
        // Add keyboard support for dismiss
        message.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const dismissBtn = message.querySelector('.dismiss-btn, .woocommerce-message-dismiss, .woocommerce-info-dismiss, .woocommerce-error-dismiss');
                if (dismissBtn) {
                    dismissMessage(message);
                }
            }
        });
    });
}

/**
 * Dismiss a message with animation
 */
function dismissMessage(message) {
    // Use appropriate animation class based on element type
    if (message.classList.contains('deva-toast')) {
        message.classList.remove('show');
        message.classList.add('hide');
    } else {
        message.classList.add('fade-out');
    }
    
    setTimeout(() => {
        if (message.parentNode) {
            message.parentNode.removeChild(message);
        }
    }, 300);
}

/**
 * Create toast container for floating notifications
 */
function createToastContainer() {
    if (document.querySelector('.deva-toast-container')) {
        return; // Already exists
    }
    
    const container = document.createElement('div');
    container.className = 'deva-toast-container';
    document.body.appendChild(container);
}

/**
 * Show a toast notification
 */
function showToast(message, type = 'info', duration = 5000) {
    const container = document.querySelector('.deva-toast-container');
    if (!container) {
        createToastContainer();
        return showToast(message, type, duration);
    }
    
    const toast = document.createElement('div');
    toast.className = `deva-toast deva-notification deva-notification-${type}`;
    toast.innerHTML = message;
    
    // Add dismiss button
    const dismissBtn = document.createElement('button');
    dismissBtn.className = 'dismiss-btn';
    dismissBtn.innerHTML = '×';
    dismissBtn.type = 'button';
    
    dismissBtn.addEventListener('click', function() {
        dismissMessage(toast);
    });
    
    toast.appendChild(dismissBtn);
    container.appendChild(toast);
    
    // Show the toast with animation
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    // Auto-dismiss after duration
    if (duration > 0) {
        setTimeout(() => {
            if (toast.parentNode) {
                dismissMessage(toast);
            }
        }, duration);
    }
    
    return toast;
}

/**
 * Enhanced cart update notifications
 */
function showCartNotification(message, type = 'success') {
    // Remove existing cart notifications
    const existing = document.querySelectorAll('.deva-toast.cart-notification');
    existing.forEach(toast => dismissMessage(toast));
    
    const toast = showToast(message, type, type === 'success' ? 3000 : 5000);
    toast.classList.add('cart-notification');
    
    return toast;
}

/**
 * Buy now notification (shorter duration)
 */
function showBuyNowNotification(message) {
    const toast = showToast(message, 'success', 2000);
    toast.classList.add('buy-now-notification');
    
    return toast;
}

/**
 * Listen for WooCommerce events and show appropriate notifications
 */
function initializeWooCommerceNotifications() {
    // Listen for cart additions
    document.addEventListener('added_to_cart', function(event) {
        const productName = event.detail.productName || 'Product';
        showCartNotification(`${productName} added to cart!`, 'success');
    });
    
    // Listen for cart updates
    document.addEventListener('updated_cart_totals', function() {
        // Don't show notification for cart updates as WooCommerce already handles this
    });
    
    // Listen for wishlist additions
    document.addEventListener('added_to_wishlist', function(event) {
        const productName = event.detail.productName || 'Product';
        showToast(`${productName} added to wishlist!`, 'info', 3000);
    });
    
    // Listen for wishlist removals
    document.addEventListener('removed_from_wishlist', function(event) {
        const productName = event.detail.productName || 'Product';
        showToast(`${productName} removed from wishlist`, 'warning', 3000);
    });
}

/**
 * Handle AJAX form submissions and show appropriate notifications
 */
function handleAjaxNotifications() {
    // Override fetch to catch AJAX responses
    const originalFetch = window.fetch;
    window.fetch = function(...args) {
        return originalFetch.apply(this, args)
            .then(response => {
                // Clone response to read it
                const clonedResponse = response.clone();
                
                // Try to parse JSON and show notifications
                if (clonedResponse.headers.get('content-type')?.includes('application/json')) {
                    clonedResponse.json().then(data => {
                        if (data.success && data.data && data.data.message) {
                            showToast(data.data.message, 'success');
                        } else if (!data.success && data.data && data.data.message) {
                            showToast(data.data.message, 'error');
                        }
                    }).catch(() => {
                        // Ignore JSON parse errors
                    });
                }
                
                return response;
            });
    };
}

/**
 * Initialize enhanced notifications
 */
function initializeEnhancedNotifications() {
    initializeWooCommerceNotifications();
    handleAjaxNotifications();
    
    // Add smooth scrolling to notifications
    const notices = document.querySelectorAll('.woocommerce-notices-wrapper');
    notices.forEach(notice => {
        if (notice.children.length > 0) {
            notice.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
}

// Initialize enhanced notifications
setTimeout(initializeEnhancedNotifications, 500);

// Global functions for external use
window.devaNotifications = {
    showToast: showToast,
    showCartNotification: showCartNotification,
    showBuyNowNotification: showBuyNowNotification,
    dismissMessage: dismissMessage
};

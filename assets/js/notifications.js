/**
 * WooCommerce Toast Notification System
 * Converts WooCommerce notices into modern toast notifications
 * 
 * This file implements coverage for WooCommerce message types:
 * - success (woocommerce-message)
 * - error (woocommerce-error) 
 * - info (woocommerce-info)
 * - warning (custom notices)
 * 
 * EXCLUDES: Coupon notifications (left as default WooCommerce notifications)
 * 
 * Supports WooCommerce contexts: cart, checkout, account, shop, single product
 */

(function($) {
    'use strict';

    // Global toast notification system
    window.convertWooCommerceNoticesToToasts = function() {
        
        // Comprehensive selectors for all WooCommerce notice types
        const noticeSelectors = [
            // Standard WooCommerce notices
            '.woocommerce-notices-wrapper .woocommerce-message',
            '.woocommerce-notices-wrapper .woocommerce-error', 
            '.woocommerce-notices-wrapper .woocommerce-info',
            '.woocommerce-notices-wrapper .woocommerce-notice',
            
            // Direct notices (sometimes rendered without wrapper)
            '.woocommerce-message',
            '.woocommerce-error',
            '.woocommerce-info', 
            '.woocommerce-notice',
            
            // Checkout specific notices (excluding coupon areas)
            '.woocommerce-checkout .woocommerce-notices-wrapper > *',
            '.payment_method_paypal .woocommerce-notices-wrapper > *',
            '.woocommerce-billing-fields .woocommerce-notices-wrapper > *',
            '.woocommerce-shipping-fields .woocommerce-notices-wrapper > *',
            
            // Cart specific notices (excluding coupon areas)
            '.woocommerce-cart .woocommerce-notices-wrapper > *',
            '.cart_totals .woocommerce-notices-wrapper > *',
            '.cart-collaterals .woocommerce-notices-wrapper > *',
            
            // Account page notices
            '.woocommerce-account .woocommerce-notices-wrapper > *',
            '.woocommerce-MyAccount-content .woocommerce-notices-wrapper > *',
            '.woocommerce-account-fields .woocommerce-notices-wrapper > *',
            '.woocommerce-password-strength .woocommerce-notices-wrapper > *',
            
            // Shop/Product notices
            '.woocommerce-shop .woocommerce-notices-wrapper > *',
            '.single-product .woocommerce-notices-wrapper > *',
            '.woocommerce-product-gallery .woocommerce-notices-wrapper > *',
            
            // Payment and shipping notices
            '.payment-notice',
            '.shipping-notice',
            '.woocommerce-shipping-calculator .woocommerce-notices-wrapper > *',
            
            // Order and receipt notices
            '.order-info',
            '.woocommerce-order-received .woocommerce-notices-wrapper > *',
            '.woocommerce-order .woocommerce-notices-wrapper > *',
            
            // Generic fallback for any missed notices
            '[class*="woocommerce-"]:not(.woocommerce-toast-processed)'
        ];
        
        let processedNotices = 0;
        
        // Process each selector type
        noticeSelectors.forEach(selector => {
            $(selector).each(function() {
                const $notice = $(this);
                
                // Skip if already processed
                if ($notice.hasClass('woocommerce-toast-processed') || 
                    $notice.hasClass('deva-toast-notification')) {
                    return;
                }
                
                // Skip non-notice elements
                if (!isWooCommerceNotice($notice)) {
                    return;
                }
                
                // Skip coupon notifications - let them display as default WooCommerce notifications
                if (isCouponNotification($notice)) {
                    return;
                }
                
                // Extract notice content and type
                const noticeData = extractNoticeData($notice);
                if (!noticeData.message) {
                    return;
                }
                
                // Create and show toast
                createToastNotification(noticeData.message, noticeData.type);
                
                // Mark as processed and hide original
                $notice.addClass('woocommerce-toast-processed');
                $notice.hide();
                
                processedNotices++;
            });
        });
        
        // Debug log if notices were processed
        if (processedNotices > 0) {
            console.log(`Deva Toast: Converted ${processedNotices} WooCommerce notices to toasts`);
        }
    };

    /**
     * Check if element is a valid WooCommerce notice
     */
    function isWooCommerceNotice($element) {
        const classList = $element[0].className;
        
        // Must have WooCommerce notice class or be a notice-like element
        if (!/woocommerce-(message|error|info|notice)|discount-info|payment-notice|shipping-notice|order-info/.test(classList)) {
            return false;
        }
        
        // Skip containers, wrappers, and non-notice elements
        if (/woocommerce-notices-wrapper|woocommerce-breadcrumb|woocommerce-tabs|woocommerce-form|woocommerce-widget|woocommerce-sidebar/.test(classList)) {
            return false;
        }
        
        // Skip elements that are clearly not notices
        if (/button|input|select|textarea|img|link|nav|header|footer/.test($element[0].tagName.toLowerCase())) {
            return false;
        }
        
        // Must have visible text content
        const text = $element.text().trim();
        if (!text || text.length < 3) {
            return false;
        }
        
        // Skip if element contains form controls (it's likely a form, not a notice)
        if ($element.find('input, select, textarea, button').length > 0) {
            return false;
        }
        
        return true;
    }

    /**
     * Check if a notice is coupon-related and should be excluded from toast conversion
     */
    function isCouponNotification($element) {
        const classList = $element[0].className;
        const text = $element.text().toLowerCase();
        
        // Check for coupon-specific classes
        if (/coupon-info|applied-coupon|checkout_coupon|coupon/.test(classList)) {
            return true;
        }
        
        // Check for coupon-related content in the message
        if (/coupon|discount code|promo code|promotional code/.test(text)) {
            return true;
        }
        
        // Check if the element is within a coupon context
        if ($element.closest('.coupon, .checkout_coupon, .woocommerce-coupon').length > 0) {
            return true;
        }
        
        return false;
    }

    /**
     * Extract message content and determine notice type
     */
    function extractNoticeData($notice) {
        let message = '';
        let type = 'info'; // default type
        
        // Get clean text content
        const $clone = $notice.clone();
        $clone.find('.close, .dismiss, button, .woocommerce-Button').remove(); // Remove interactive elements
        
        // Check if there's an inner message element
        const $innerMessage = $clone.find('.woocommerce-message, .message, .notice-message').first();
        if ($innerMessage.length) {
            message = $innerMessage.text().trim();
        } else {
            message = $clone.text().trim();
        }
        
        // Clean up common WooCommerce message artifacts
        message = message.replace(/^\s*×\s*/, ''); // Remove close button text
        message = message.replace(/\s+/g, ' '); // Normalize whitespace
        message = message.trim();
        
        // Determine notice type from classes
        const classList = $notice[0].className;
        
        if (/woocommerce-message/.test(classList)) {
            type = 'success';
        } else if (/woocommerce-error/.test(classList)) {
            type = 'error';
        } else if (/woocommerce-info|shipping-notice|payment-notice/.test(classList)) {
            type = 'info';
        } else if (/woocommerce-notice/.test(classList)) {
            // Analyze message content for better type detection
            const lowerMessage = message.toLowerCase();
            if (/success|added|updated|saved|applied|complete|thank you|order received/.test(lowerMessage)) {
                type = 'success';
            } else if (/error|failed|invalid|required|missing|cannot|unable|denied/.test(lowerMessage)) {
                type = 'error';
            } else if (/warning|note|please|attention|important/.test(lowerMessage)) {
                type = 'warning';
            } else if (/shipping|payment|billing/.test(lowerMessage)) {
                type = 'info';
            } else {
                type = 'info';
            }
        }
        
        // Additional type detection for specific WooCommerce contexts
        if (type === 'info') {
            const lowerMessage = message.toLowerCase();
            if (/added to cart|item added|successfully added/.test(lowerMessage)) {
                type = 'success';
            } else if (/out of stock|insufficient|limit|maximum/.test(lowerMessage)) {
                type = 'warning';
            } else if (/checkout error|payment failed|validation/.test(lowerMessage)) {
                type = 'error';
            }
        }
        
        return { message, type };
    }

    /**
     * Create and display toast notification
     */
    function createToastNotification(message, type = 'info') {
        // Ensure we have a message
        if (!message || typeof message !== 'string') {
            return;
        }
        
        // Clean up message
        message = message.trim();
        if (message.length === 0) {
            return;
        }
        
        // Remove any existing identical toasts
        $(`.deva-toast-notification:contains("${message.substring(0, 50)}")`).remove();
        
        // Create toast element
        const $toast = createToastElement(message, type);
        
        // Add to DOM
        $('body').append($toast);
        
        // Animate in
        setTimeout(() => {
            $toast.addClass('show');
        }, 100);
        
        // Auto dismiss
        const dismissDelay = getDismissDelay(type, message);
        setTimeout(() => {
            dismissToast($toast);
        }, dismissDelay);
        
        // Manual dismiss on click
        $toast.on('click', () => {
            dismissToast($toast);
        });
    }

    /**
     * Create toast DOM element
     */
    function createToastElement(message, type) {
        const toastClass = `deva-toast-notification deva-toast-${type}`;
        const icon = getToastIcon(type);
        
        return $(`
            <div class="${toastClass}" role="alert" aria-live="polite">
                <div class="deva-toast-content">
                    <div class="deva-toast-icon">${icon}</div>
                    <div class="deva-toast-message">${message}</div>
                    <button class="deva-toast-close" aria-label="Close notification">×</button>
                </div>
            </div>
        `);
    }

    /**
     * Get appropriate icon for toast type
     */
    function getToastIcon(type) {
        const icons = {
            success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4"></path><circle cx="12" cy="12" r="10"></circle></svg>',
            error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
            warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.46 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
            info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
        };
        
        return icons[type] || icons.info;
    }

    /**
     * Determine auto-dismiss delay based on type and message length
     */
    function getDismissDelay(type, message) {
        const baseDelay = 4000; // 4 seconds
        const readingTime = Math.max(message.length * 50, 2000); // ~50ms per character, min 2s
        
        // Error messages stay longer
        if (type === 'error') {
            return Math.min(readingTime * 1.5, 8000);
        }
        
        // Success messages can be shorter
        if (type === 'success') {
            return Math.min(readingTime, 5000);
        }
        
        // Default timing
        return Math.min(readingTime, 6000);
    }

    /**
     * Dismiss toast with animation
     */
    function dismissToast($toast) {
        if (!$toast || !$toast.length) return;
        
        $toast.removeClass('show');
        setTimeout(() => {
            $toast.remove();
        }, 300);
    }

    /**
     * Initialize toast styles if not already loaded
     */
    function initializeToastStyles() {
        if ($('#deva-toast-styles').length > 0) return;
        
        const styles = `
            <style id="deva-toast-styles">
                .deva-toast-notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    min-width: 300px;
                    max-width: 500px;
                    z-index: 999999;
                    opacity: 0;
                    transform: translateX(100%);
                    transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                    margin-bottom: 10px;
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                }
                
                .deva-toast-notification.show {
                    opacity: 1;
                    transform: translateX(0);
                }
                
                .deva-toast-content {
                    display: flex;
                    align-items: flex-start;
                    gap: 12px;
                    padding: 16px;
                    border-radius: 8px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                    backdrop-filter: blur(10px);
                    cursor: pointer;
                }
                
                .deva-toast-success .deva-toast-content {
                    background: #10b981;
                    color: #ffffff;
                }
                
                .deva-toast-error .deva-toast-content {
                    background: #ef4444;
                    color: #ffffff;
                }
                
                .deva-toast-warning .deva-toast-content {
                    background: #f59e0b;
                    color: #ffffff;
                }
                
                .deva-toast-info .deva-toast-content {
                    background: #3b82f6;
                    color: #ffffff;
                }
                
                .deva-toast-icon {
                    flex-shrink: 0;
                    margin-top: 1px;
                }
                
                .deva-toast-message {
                    flex: 1;
                    font-size: 14px;
                    font-weight: 500;
                    line-height: 1.4;
                }
                
                .deva-toast-close {
                    background: none;
                    border: none;
                    color: inherit;
                    font-size: 18px;
                    cursor: pointer;
                    padding: 0;
                    width: 20px;
                    height: 20px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    opacity: 0.8;
                    flex-shrink: 0;
                }
                
                .deva-toast-close:hover {
                    opacity: 1;
                }
                
                /* Mobile responsive */
                @media (max-width: 480px) {
                    .deva-toast-notification {
                        top: 10px;
                        right: 10px;
                        left: 10px;
                        min-width: auto;
                        max-width: none;
                    }
                }
                
                /* Stacking toasts */
                .deva-toast-notification:nth-child(n+2) {
                    margin-top: -10px;
                    transform: translateX(100%) scale(0.95);
                }
                
                .deva-toast-notification.show:nth-child(n+2) {
                    transform: translateX(0) scale(0.95);
                }
            </style>
        `;
        
        $('head').append(styles);
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        initializeToastStyles();
        
        // Initial conversion
        setTimeout(convertWooCommerceNoticesToToasts, 100);
        
        // WooCommerce specific AJAX event listeners
        $(document.body).on('updated_wc_div', function() {
            setTimeout(convertWooCommerceNoticesToToasts, 100);
        });
        
        $(document.body).on('added_to_cart', function() {
            setTimeout(convertWooCommerceNoticesToToasts, 200);
        });
        
        $(document.body).on('checkout_error', function() {
            setTimeout(convertWooCommerceNoticesToToasts, 100);
        });
        
        // Additional WooCommerce events that may trigger notices
        $(document.body).on('wc_fragments_refreshed', function() {
            setTimeout(convertWooCommerceNoticesToToasts, 100);
        });
        
        $(document.body).on('wc_fragments_loaded', function() {
            setTimeout(convertWooCommerceNoticesToToasts, 100);
        });
        
        // jQuery AJAX complete event (catch any missed AJAX notices)
        $(document).ajaxComplete(function(event, xhr, settings) {
            // Only check for WooCommerce related AJAX calls
            if (settings.url && (
                settings.url.includes('wc-ajax') || 
                settings.url.includes('woocommerce') || 
                settings.url.includes('add-to-cart') ||
                settings.url.includes('cart') ||
                settings.url.includes('checkout')
            )) {
                setTimeout(convertWooCommerceNoticesToToasts, 150);
            }
        });
        
        // Monitor for new notices via MutationObserver
        if (window.MutationObserver) {
            const observer = new MutationObserver(function(mutations) {
                let shouldCheck = false;
                
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length > 0) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                const $node = $(node);
                                if ($node.hasClass('woocommerce-message') || 
                                    $node.hasClass('woocommerce-error') || 
                                    $node.hasClass('woocommerce-info') ||
                                    $node.hasClass('woocommerce-notice') ||
                                    $node.hasClass('woocommerce-notices-wrapper') ||
                                    $node.find('.woocommerce-message, .woocommerce-error, .woocommerce-info, .woocommerce-notice, .woocommerce-notices-wrapper').length > 0) {
                                    shouldCheck = true;
                                }
                            }
                        });
                    }
                });
                
                if (shouldCheck) {
                    setTimeout(convertWooCommerceNoticesToToasts, 100);
                }
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
        
        // Fallback: Periodic check for any missed notices (runs every 2 seconds for first 10 seconds)
        let checkCount = 0;
        const fallbackInterval = setInterval(function() {
            checkCount++;
            convertWooCommerceNoticesToToasts();
            
            // Stop after 5 checks (10 seconds)
            if (checkCount >= 5) {
                clearInterval(fallbackInterval);
            }
        }, 2000);
    });

})(jQuery);
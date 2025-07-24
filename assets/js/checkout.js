/**
 * DEVA Cart JavaScript
 * Handles cart page functionality including tab switching and cart updates
 */

jQuery(document).ready(function($) {
    
    // Tab switching functionality
    $('.deva-tab').on('click', function(e) {
        e.preventDefault();
        
        var tabId = $(this).data('tab');
        
        // Update active tab
        $('.deva-tab').removeClass('active');
        $(this).addClass('active');
        
        // Update active content
        $('.deva-tab-content').removeClass('active');
        $('#' + tabId + '-content').addClass('active');
        
        // If switching to wishlist, reload the content
        if (tabId === 'wishlist') {
            loadWishlistContent();
        }
    });
    
    // Quantity controls
    $(document).on('click', '.qty-btn', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var cartKey = $btn.data('key');
        var $input = $btn.siblings('.deva-quantity-input');
        var currentQty = parseInt($input.val());
        var newQty = currentQty;
        
        if ($btn.hasClass('qty-plus')) {
            newQty = currentQty + 1;
        } else if ($btn.hasClass('qty-minus') && currentQty > 1) {
            newQty = currentQty - 1;
        }
        
        if (newQty !== currentQty) {
            updateCartQuantity(cartKey, newQty, $input);
        }
    });
    
    // Remove item from cart
    $(document).on('click', '.deva-remove-item', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var cartKey = $btn.data('key');
        var $cartItem = $btn.closest('.deva-cart-item');
        
        // Show confirmation
        if (confirm('Are you sure you want to remove this item from your cart?')) {
            removeCartItem(cartKey, $cartItem);
        }
    });
    
    // Move item to wishlist
    $(document).on('click', '.deva-wishlist-item', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var productId = $btn.data('product-id');
        var cartKey = $btn.data('key');
        var $cartItem = $btn.closest('.deva-cart-item');
        
        moveToWishlist(productId, cartKey, $cartItem);
    });
    
    // Place order button handling
    $('.deva-place-order-btn').on('click', function(e) {
        var $btn = $(this);
        
        // Show loading state
        $btn.prop('disabled', true);
        $btn.find('.button-text').hide();
        $btn.find('.button-loader').show();
        
        // Let the form submit naturally
        // The loading state will be cleared on page reload/redirect
    });
    
    // Update cart quantity via AJAX
    function updateCartQuantity(cartKey, quantity, $input) {
        var originalQty = $input.val();
        
        // Update input optimistically
        $input.val(quantity);
        
        // Show loading on the item
        var $cartItem = $input.closest('.deva-cart-item');
        $cartItem.addClass('updating');
        
        $.ajax({
            url: shop_ajax ? shop_ajax.ajax_url : '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'update_cart_item_quantity',
                cart_item_key: cartKey,
                quantity: quantity,
                security: shop_ajax ? shop_ajax.nonce : ''
            },
            success: function(response) {
                if (response.success) {
                    // Update the cart totals
                    updateCartTotals(response.data);
                    
                    // Update item subtotal
                    if (response.data.item_subtotal) {
                        $cartItem.find('.price-amount').html(response.data.item_subtotal);
                    }
                    
                    showMessage('Cart updated successfully', 'success');
                } else {
                    // Revert quantity on error
                    $input.val(originalQty);
                    showMessage('Error updating cart', 'error');
                }
            },
            error: function() {
                // Revert quantity on error
                $input.val(originalQty);
                showMessage('Error updating cart', 'error');
            },
            complete: function() {
                $cartItem.removeClass('updating');
            }
        });
    }
    
    // Remove cart item via AJAX
    function removeCartItem(cartKey, $cartItem) {
        $cartItem.addClass('removing');
        
        $.ajax({
            url: shop_ajax ? shop_ajax.ajax_url : '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'remove_cart_item',
                cart_item_key: cartKey,
                security: shop_ajax ? shop_ajax.nonce : ''
            },
            success: function(response) {
                if (response.success) {
                    // Remove item with animation
                    $cartItem.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if cart is empty
                        if ($('.deva-cart-item').length === 0) {
                            location.reload(); // Reload to show empty cart message
                        }
                    });
                    
                    // Update cart totals
                    updateCartTotals(response.data);
                    showMessage('Item removed from cart', 'success');
                } else {
                    showMessage('Error removing item', 'error');
                }
            },
            error: function() {
                showMessage('Error removing item', 'error');
            },
            complete: function() {
                $cartItem.removeClass('removing');
            }
        });
    }
    
    // Move item to wishlist
    function moveToWishlist(productId, cartKey, $cartItem) {
        $cartItem.addClass('moving-to-wishlist');
        
        // First add to wishlist
        var favorites = JSON.parse(localStorage.getItem('deva_favorites') || '[]');
        if (favorites.indexOf(productId) === -1) {
            favorites.push(productId);
            localStorage.setItem('deva_favorites', JSON.stringify(favorites));
        }
        
        // Then remove from cart
        $.ajax({
            url: shop_ajax ? shop_ajax.ajax_url : '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'remove_cart_item',
                cart_item_key: cartKey,
                security: shop_ajax ? shop_ajax.nonce : ''
            },
            success: function(response) {
                if (response.success) {
                    // Remove item with animation
                    $cartItem.fadeOut(300, function() {
                        $(this).remove();
                        
                        // Check if cart is empty
                        if ($('.deva-cart-item').length === 0) {
                            location.reload();
                        }
                    });
                    
                    // Update cart totals
                    updateCartTotals(response.data);
                    showMessage('Item moved to wishlist', 'success');
                    
                    // Sync with server if user is logged in
                    if (typeof shop_ajax !== 'undefined' && shop_ajax.is_user_logged_in) {
                        syncWishlistWithServer();
                    }
                } else {
                    showMessage('Error moving item to wishlist', 'error');
                }
            },
            error: function() {
                showMessage('Error moving item to wishlist', 'error');
            },
            complete: function() {
                $cartItem.removeClass('moving-to-wishlist');
            }
        });
    }
    
    // Update cart totals in the UI
    function updateCartTotals(data) {
        if (data.cart_total) {
            $('.cart-total').html(data.cart_total);
        }
        
        if (data.cart_count) {
            $('.cart-count').text(data.cart_count + ' items');
        }
        
        // Update order summary if provided
        if (data.order_summary) {
            $('.deva-order-summary').html(data.order_summary);
        }
        
        // Trigger WooCommerce cart update event
        $(document.body).trigger('updated_wc_div');
    }
    
    // Load wishlist content
    function loadWishlistContent() {
        var $container = $('.deva-wishlist-container');
        
        // Show loading state
        $container.html('<div class="deva-loading">Loading wishlist...</div>');
        
        // The wishlist shortcode should handle the display
        // You could also make an AJAX call here to refresh the content
        $.ajax({
            url: shop_ajax ? shop_ajax.ajax_url : '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'get_wishlist_content',
                security: shop_ajax ? shop_ajax.nonce : ''
            },
            success: function(response) {
                if (response.success) {
                    $container.html(response.data.content);
                }
            },
            error: function() {
                // Fallback to existing content
                $container.html('<p>Unable to load wishlist content.</p>');
            }
        });
    }
    
    // Sync wishlist with server (reuse from shop.js)
    function syncWishlistWithServer() {
        if (typeof shop_ajax === 'undefined' || !shop_ajax.is_user_logged_in) {
            return;
        }
        
        var favorites = JSON.parse(localStorage.getItem('deva_favorites') || '[]');
        
        $.ajax({
            url: shop_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'sync_favorites',
                favorites: favorites,
                nonce: shop_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.favorites) {
                    localStorage.setItem('deva_favorites', JSON.stringify(response.data.favorites));
                }
            }
        });
    }
    
    // Show message function
    function showMessage(message, type) {
        // Remove existing messages
        $('.deva-checkout-message').remove();
        
        var messageEl = $('<div class="deva-checkout-message deva-checkout-' + type + '">' + message + '</div>');
        messageEl.css({
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: type === 'success' ? '#10b981' : '#ef4444',
            color: 'white',
            padding: '12px 20px',
            borderRadius: '8px',
            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
            zIndex: 9999,
            fontSize: '14px',
            fontWeight: '500',
            opacity: 0,
            transform: 'translateX(100%)',
            transition: 'all 0.3s ease'
        });
        
        $('body').append(messageEl);
        
        // Show message
        setTimeout(function() {
            messageEl.css({
                opacity: 1,
                transform: 'translateX(0)'
            });
        }, 100);
        
        // Hide message after 3 seconds
        setTimeout(function() {
            messageEl.css({
                opacity: 0,
                transform: 'translateX(100%)'
            });
            setTimeout(function() {
                messageEl.remove();
            }, 300);
        }, 3000);
    }
    
    // Add loading states CSS
    var loadingCSS = `
        .deva-cart-item.updating,
        .deva-cart-item.removing,
        .deva-cart-item.moving-to-wishlist {
            opacity: 0.6;
            pointer-events: none;
            position: relative;
        }
        
        .deva-cart-item.updating::after,
        .deva-cart-item.removing::after,
        .deva-cart-item.moving-to-wishlist::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #3b82f6;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .deva-loading {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
            font-style: italic;
        }
    `;
    
    // Inject loading CSS
    $('<style>').prop('type', 'text/css').html(loadingCSS).appendTo('head');
});

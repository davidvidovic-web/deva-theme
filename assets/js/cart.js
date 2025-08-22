/**
 * DEVA Cart JavaScript
 * Handles cart page functionality including tab switching and cart updates
 */

jQuery(document).ready(function($) {
    
    // Utility function to safely get favorites from localStorage as an array
    function getFavoritesFromStorage() {
        try {
            var storedData = localStorage.getItem('deva_favorites');
            if (!storedData) return [];
            
            var parsed = JSON.parse(storedData);
            
            // If it's already an array, return it
            if (Array.isArray(parsed)) {
                return parsed;
            }
            
            // If it's an object with numeric keys, convert to array
            if (typeof parsed === 'object' && parsed !== null) {
                var converted = [];
                Object.keys(parsed).forEach(function(key) {
                    converted.push(parsed[key]);
                });
                return converted;
            }
            
            return [];
        } catch (e) {
            console.warn('Error parsing favorites from localStorage:', e);
            return [];
        }
    }
    
    // URL Management Functions
    function updateURL(tab) {
        var newURL = window.location.pathname + window.location.search;
        if (tab === 'wishlist') {
            newURL += '#wishlist';
        }
        // Use pushState to update URL without page reload
        history.pushState({tab: tab}, '', newURL);
    }
    
    function getCurrentTabFromURL() {
        return window.location.hash === '#wishlist' ? 'wishlist' : 'cart';
    }
    
    function switchToTab(tabId) {
        // Update active tab in header
        $('.deva-cart-header .deva-tab').removeClass('active');
        $('.deva-cart-header .deva-tab[data-tab="' + tabId + '"]').addClass('active');
        
        // Update active content
        $('.deva-tab-content').removeClass('active');
        $('#' + tabId + '-content').addClass('active');
        
        // Update pagination indicators
        $('.cart-tab-indicator, .wishlist-tab-indicator').removeClass('active');
        $('.' + tabId + '-tab-indicator').addClass('active');
        
        // If switching to wishlist, reload the content
        if (tabId === 'wishlist') {
            loadWishlistContent();
        }
    }
    
    // Initialize correct tab based on URL on page load
    var initialTab = getCurrentTabFromURL();
    if (initialTab === 'wishlist') {
        switchToTab('wishlist');
    }
    
    // Listen for browser back/forward button
    $(window).on('popstate', function(event) {
        var currentTab = getCurrentTabFromURL();
        switchToTab(currentTab);
    });
    
    // Initialize wishlist content if we're on the wishlist tab
    if ($('#wishlist-content').hasClass('active') || $('.deva-wishlist-container').length > 0) {
        loadWishlistContent();
    }
    
    // Listen for localStorage changes (when wishlist is updated from other parts of the site)
    $(window).on('storage', function(e) {
        if (e.originalEvent.key === 'deva_favorites') {
            // Reload wishlist if we're on the wishlist tab
            if ($('#wishlist-content').hasClass('active') || $('.deva-wishlist-container').length > 0) {
                loadWishlistContent();
            }
        }
    });
    
    // Listen for custom wishlist update events
    $(document).on('deva_wishlist_updated', function() {
        if ($('#wishlist-content').hasClass('active') || $('.deva-wishlist-container').length > 0) {
            loadWishlistContent();
        }
    });
    
    // Listen for wishlist item removal within the wishlist display
    $(document).on('click', '.deva-wishlist-display .deva-favorite-heart', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var productId = $(this).data('product-id');
        if (!productId) return;
        
        // Remove from localStorage
        var favorites = getFavoritesFromStorage();
        var index = favorites.indexOf(productId.toString());
        if (index > -1) {
            favorites.splice(index, 1);
            localStorage.setItem('deva_favorites', JSON.stringify(favorites));
            
            // Trigger the wishlist update event
            $(document).trigger('deva_wishlist_updated');
            
            // Show feedback
            $(this).removeClass('active');
        }
    });
    
    // Tab switching functionality for header tabs
    $('.deva-cart-header .deva-tab').on('click', function(e) {
        e.preventDefault();
        
        var tabId = $(this).data('tab');
        
        // Switch to the tab and update URL
        switchToTab(tabId);
        updateURL(tabId);
    });
    
    // Bottom pagination navigation
    $(document).on('click', '.deva-tab-nav, .current', function(e) {
        e.preventDefault();
        
        var targetTab = $(this).data('tab');
        if (!targetTab) return;
        
        // Switch to the tab and update URL
        switchToTab(targetTab);
        updateURL(targetTab);
    });
    
    // Quantity controls
    $(document).on('click', '.qty-btn', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var cartKey = $btn.data('key');
        
        // Find the quantity input in multiple ways to ensure compatibility
        var $input = $btn.siblings('.deva-quantity-input');
        if ($input.length === 0) {
            $input = $btn.siblings('input[type="number"]');
        }
        if ($input.length === 0) {
            $input = $btn.parent().find('input[name^="cart[' + cartKey + ']"]');
        }
        
        if ($input.length === 0) {
            return;
        }
        
        var currentQty = parseInt($input.val()) || 1;
        var newQty = currentQty;
        var maxQty = parseInt($input.attr('max')) || 999;
        var minQty = parseInt($input.attr('min')) || 1;
        
        if ($btn.hasClass('qty-plus') && currentQty < maxQty) {
            newQty = currentQty + 1;
        } else if ($btn.hasClass('qty-minus') && currentQty > minQty) {
            newQty = currentQty - 1;
        }
        
        if (newQty !== currentQty) {
            updateCartQuantity(cartKey, newQty, $input);
        }
    });
    
    // Direct quantity input change
    $(document).on('change', '.deva-quantity-input, input[name^="cart["]', function(e) {
        var $input = $(this);
        var cartKey = '';
        
        // Extract cart key from input name attribute
        var inputName = $input.attr('name');
        if (inputName) {
            var matches = inputName.match(/cart\[([^\]]+)\]/);
            if (matches && matches[1]) {
                cartKey = matches[1];
            }
        }
        
        // Fallback: try to find cart key from nearby buttons
        if (!cartKey) {
            var $qtyBtn = $input.siblings('.qty-btn').first();
            if ($qtyBtn.length) {
                cartKey = $qtyBtn.data('key');
            }
        }
        
        if (!cartKey) {
            return;
        }
        
        var newQty = parseInt($input.val()) || 1;
        var minQty = parseInt($input.attr('min')) || 1;
        var maxQty = parseInt($input.attr('max')) || 999;
        
        // Validate quantity bounds
        if (newQty < minQty) {
            newQty = minQty;
            $input.val(newQty);
        } else if (newQty > maxQty) {
            newQty = maxQty;
            $input.val(newQty);
        }
        
        // Update cart if quantity is valid
        if (newQty >= minQty && newQty <= maxQty) {
            updateCartQuantity(cartKey, newQty, $input);
        }
    });

    // Quantity input validation on keyup (for real-time feedback)
    $(document).on('keyup', '.deva-quantity-input, input[name^="cart["]', function(e) {
        var $input = $(this);
        var newQty = parseInt($input.val()) || 0;
        var minQty = parseInt($input.attr('min')) || 1;
        var maxQty = parseInt($input.attr('max')) || 999;
        
        // Visual feedback for invalid quantities
        if (newQty < minQty || newQty > maxQty) {
            $input.css('border-color', '#ef4444');
        } else {
            $input.css('border-color', '');
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
    
    // Update cart button handling
    $('.deva-cart-actions .deva-button[name="update_cart"]').on('click', function(e) {
        var $btn = $(this);
        
        // Show loading state
        $btn.prop('disabled', true);
        var originalText = $btn.text();
        $btn.text('Updating...');
        
        // Let the form submit naturally
        // The loading state will be cleared on page reload
    });
    
    // Update cart quantity via AJAX
    function updateCartQuantity(cartKey, quantity, $input) {
        var originalQty = $input.val();
        
        // Update input optimistically
        $input.val(quantity);
        
        // Show loading on the item
        var $cartItem = $input.closest('.deva-cart-item');
        $cartItem.addClass('updating');
        
        // Get the best available AJAX configuration
        var ajaxConfig = getAjaxConfig();
        
        $.ajax({
            url: ajaxConfig.ajax_url,
            type: 'POST',
            data: {
                action: 'update_cart_item_quantity',
                cart_item_key: cartKey,
                quantity: quantity,
                security: ajaxConfig.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update the cart totals
                    updateCartTotals(response.data);
                    
                    // Update item prices
                    if (response.data.item_unit_price) {
                        $cartItem.find('.unit-price .price-amount').html(response.data.item_unit_price);
                    }
                    
                    if (response.data.item_subtotal) {
                        $cartItem.find('.subtotal-amount').html(response.data.item_subtotal);
                    }
                    
                    showMessage('Cart updated successfully', 'success');
                } else {
                    // Revert quantity on error
                    $input.val(originalQty);
                    showMessage(response.data || 'Error updating cart', 'error');
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
        
        var ajaxConfig = getAjaxConfig();
        
        $.ajax({
            url: ajaxConfig.ajax_url,
            type: 'POST',
            data: {
                action: 'remove_cart_item',
                cart_item_key: cartKey,
                security: ajaxConfig.remove_nonce || ajaxConfig.nonce
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
        var favorites = getFavoritesFromStorage();
        if (favorites.indexOf(productId) === -1) {
            favorites.push(productId);
            localStorage.setItem('deva_favorites', JSON.stringify(favorites));
        }
        
        // Then remove from cart
        var ajaxConfig = getAjaxConfig();
        
        $.ajax({
            url: ajaxConfig.ajax_url,
            type: 'POST',
            data: {
                action: 'remove_cart_item',
                cart_item_key: cartKey,
                security: ajaxConfig.remove_nonce || ajaxConfig.nonce
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
                    
                    // Sync with server if user is logged in (force sync after user action)
                    if (typeof shop_ajax !== 'undefined' && shop_ajax.is_user_logged_in) {
                        syncWishlistWithServer(true);
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
        
        // Get favorites from localStorage
        var favorites = getFavoritesFromStorage();
        
        // Show loading state
        $container.html('<div class="deva-loading">Loading wishlist...</div>');
        
        // Get AJAX config
        var ajaxConfig = getAjaxConfig();
        
        $.ajax({
            url: ajaxConfig.ajax_url,
            type: 'POST',
            data: {
                action: 'get_wishlist_content',
                product_ids: JSON.stringify(favorites),
                show_remove_button: true,
                nonce: ajaxConfig.nonce,
                security: ajaxConfig.security || ajaxConfig.nonce,
                shop_nonce: ajaxConfig.shop_nonce || ajaxConfig.nonce
            },
            success: function(response) {
                console.log('Wishlist AJAX response:', response);
                if (response.success) {
                    $container.html(response.data.content || response.data.html);
                    
                    // After loading wishlist content, sync with localStorage
                    syncWishlistUI();
                } else {
                    console.error('Wishlist AJAX error:', response);
                    $container.html('<div class="deva-wishlist-empty"><p>Unable to load wishlist content.</p></div>');
                }
            },
            error: function(xhr, status, error) {
                console.log('Wishlist AJAX Error:', {xhr: xhr, status: status, error: error});
                $container.html('<div class="deva-wishlist-empty"><p>Error loading wishlist. Please try again.</p></div>');
            }
        });
    }
    
    // Sync wishlist UI with localStorage
    function syncWishlistUI() {
        var favorites = getFavoritesFromStorage();
        
        // Update wishlist count
        $('.deva-wishlist-count').text(favorites.length);
        $('.wishlist-count-display').text('(' + favorites.length + ' items)');
        
        // Update heart icons to active state for favorited items
        $('.deva-favorite-heart').removeClass('active');
        favorites.forEach(function(productId) {
            $('.deva-favorite-heart[data-product-id="' + productId + '"]').addClass('active');
        });
        
        // If wishlist is empty, show empty state
        if (favorites.length === 0) {
            $('.deva-wishlist-products ul').hide();
            $('.deva-wishlist-empty').show();
        } else {
            $('.deva-wishlist-products ul').show();
            $('.deva-wishlist-empty').hide();
        }
    }
    
    // Sync wishlist with server (optimized - reuse from shop.js)
    function syncWishlistWithServer(forceSync) {
        var ajaxConfig = getAjaxConfig();
        
        // Validate AJAX config before proceeding
        if (!ajaxConfig || !ajaxConfig.ajax_url || !ajaxConfig.nonce) {
            return;
        }
        
        // Only sync if user is logged in
        if (!ajaxConfig.is_user_logged_in) {
            return;
        }
        
        var favorites = getFavoritesFromStorage();
        
        // Skip sync if no favorites and not forced
        if (!forceSync && favorites.length === 0) {
            return;
        }
        
        // Check sync cooldown (unless forced)
        if (!forceSync) {
            var lastSyncTime = sessionStorage.getItem('deva_last_sync');
            var currentTime = Date.now();
            var syncCooldown = 30000; // 30 seconds
            
            if (lastSyncTime && (currentTime - parseInt(lastSyncTime)) < syncCooldown) {
                return;
            }
        }
        
        $.ajax({
            url: ajaxConfig.ajax_url,
            type: 'POST',
            data: {
                action: 'sync_favorites',
                favorites: favorites,
                nonce: ajaxConfig.nonce
            },
            success: function(response) {
                if (response.success && Array.isArray(response.data.favorites)) {
                    localStorage.setItem('deva_favorites', JSON.stringify(response.data.favorites));
                    // Mark sync time
                    sessionStorage.setItem('deva_last_sync', Date.now().toString());
                } else {
                    // Server sync failed
                }
            },
            error: function(xhr, status, error) {
                // Server sync AJAX failed
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
    
    // Helper function to get the best available AJAX configuration
    function getAjaxConfig() {
        if (typeof deva_cart_ajax !== 'undefined' && deva_cart_ajax.ajax_url) {
            return deva_cart_ajax;
        } else if (typeof shop_ajax !== 'undefined' && shop_ajax.ajax_url) {
            return shop_ajax;
        } else {
            // Fallback configuration
            return {
                ajax_url: '/wp-admin/admin-ajax.php',
                nonce: '',
                is_user_logged_in: false
            };
        }
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

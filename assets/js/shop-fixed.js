jQuery(document).ready(function($) {
    // === UTILITY FUNCTIONS ===
    
    // Helper to get AJAX config for different endpoints
    function getAjaxConfig(action) {
        var config = {
            url: '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: action
            }
        };
        
        // Use appropriate nonce based on action
        if (typeof shop_ajax !== 'undefined' && shop_ajax) {
            config.url = shop_ajax.ajax_url;
            config.data.nonce = shop_ajax.nonce;
        } else if (typeof deva_cart_ajax !== 'undefined' && deva_cart_ajax) {
            config.url = deva_cart_ajax.ajax_url;
            config.data.nonce = deva_cart_ajax.nonce;
        }
        
        return config;
    }
    
    // Update wishlist count in header
    function updateWishlistCount(count) {
        var $wishlistCount = $('.wishlist-count, .deva-wishlist-count');
        if ($wishlistCount.length) {
            if (count > 0) {
                $wishlistCount.text(count).show();
            } else {
                $wishlistCount.hide();
            }
        }
    }
    
    // Update favorites UI
    function updateFavoritesUI(favorites) {
        $('.deva-wishlist-heart, .favorite-heart, .deva-favorite-heart').removeClass('active');
        favorites.forEach(function(productId) {
            $('.deva-wishlist-heart[data-product-id="' + productId + '"], .favorite-heart[data-product-id="' + productId + '"], .deva-favorite-heart[data-product-id="' + productId + '"]').addClass('active');
        });
    }
    
    // Sync wishlist with server
    function syncWishlistWithServer() {
        var favorites = JSON.parse(localStorage.getItem('deva_favorites') || '[]');
        
        // If user is logged in, sync with server
        if (typeof shop_ajax !== 'undefined' && shop_ajax && shop_ajax.is_user_logged_in) {
            var config = getAjaxConfig('sync_favorites');
            config.data.favorites = favorites;
            
            $.ajax(config).done(function(response) {
                if (response.success && response.data.favorites) {
                    // Update localStorage with server favorites
                    localStorage.setItem('deva_favorites', JSON.stringify(response.data.favorites));
                    
                    // Update UI
                    updateFavoritesUI(response.data.favorites);
                    updateWishlistCount(response.data.favorites.length);
                }
            }).fail(function() {
                console.log('Could not sync with server, using local favorites');
                // Update UI with local favorites
                updateFavoritesUI(favorites);
                updateWishlistCount(favorites.length);
            });
        } else {
            // Update UI with local favorites
            updateFavoritesUI(favorites);
            updateWishlistCount(favorites.length);
        }
    }
    
    // Show wishlist message
    function showWishlistMessage(message, type) {
        // Remove existing messages
        $('.deva-wishlist-message').remove();
        
        // Create new message
        var $message = $('<div class="deva-wishlist-message deva-wishlist-message-' + type + '">' + message + '</div>');
        $('body').append($message);
        
        // Auto-hide after 3 seconds
        setTimeout(function() {
            $message.fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    // === WISHLIST FUNCTIONALITY ===
    
    // Handle wishlist heart click
    function handleWishlistClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $heart = $(this);
        var productId = $heart.data('product-id');
        
        if (!productId) {
            console.error('No product ID found for wishlist');
            return;
        }
        
        // Prevent double clicks
        if ($heart.hasClass('loading')) {
            return;
        }
        
        $heart.addClass('loading');
        
        // Get current favorites
        var favorites = JSON.parse(localStorage.getItem('deva_favorites') || '[]');
        var isBecomingFavorited = !$heart.hasClass('active');
        
        // Optimistically update UI
        $heart.toggleClass('active');
        
        // Update localStorage
        if (isBecomingFavorited) {
            if (favorites.indexOf(productId) === -1) {
                favorites.push(productId);
            }
        } else {
            favorites = favorites.filter(function(id) { return id !== productId; });
        }
        
        localStorage.setItem('deva_favorites', JSON.stringify(favorites));
        updateWishlistCount(favorites.length);
        
        // Sync with server if logged in
        if (typeof shop_ajax !== 'undefined' && shop_ajax && shop_ajax.is_user_logged_in) {
            var config = getAjaxConfig('toggle_favorite');
            config.data.product_id = productId;
            config.data.is_favorite = isBecomingFavorited;
            
            $.ajax(config).done(function(response) {
                $heart.removeClass('loading');
                if (response.success) {
                    showWishlistMessage(isBecomingFavorited ? 'Added to wishlist' : 'Removed from wishlist', 'success');
                    
                    // Update wishlist count from server if available
                    if (response.data && typeof response.data.count !== 'undefined') {
                        updateWishlistCount(response.data.count);
                    }
                } else {
                    // Revert optimistic update on server error
                    $heart.toggleClass('active');
                    showWishlistMessage('Error updating wishlist', 'error');
                }
            }).fail(function() {
                $heart.removeClass('loading');
                // Keep optimistic update for localStorage fallback
                showWishlistMessage(isBecomingFavorited ? 'Added to wishlist (saved locally)' : 'Removed from wishlist', 'success');
            });
        } else {
            $heart.removeClass('loading');
            // Fallback to localStorage only
            showWishlistMessage(isBecomingFavorited ? 'Added to wishlist' : 'Removed from wishlist', 'success');
        }
    }
    
    // Initialize wishlist functionality
    function initializeWishlistFunctionality() {
        // Get current favorites and update heart icons
        var favorites = JSON.parse(localStorage.getItem('deva_favorites') || '[]');
        updateFavoritesUI(favorites);
        updateWishlistCount(favorites.length);
        
        // Bind wishlist heart click events (including backward compatibility)
        $(document).on('click', '.deva-wishlist-heart, .favorite-heart, .deva-favorite-heart', handleWishlistClick);
        
        // Sync with server if logged in
        syncWishlistWithServer();
    }
    
    // === SHOP FUNCTIONALITY ===
    
    // Initialize other shop functionality
    function initializeShopFunctionality() {
        // Hero Section Functions
        window.openSearchModal = function() {
            $('#searchModal').fadeIn();
        };
        
        window.scrollToCategories = function() {
            var $target = $('.deva-category-section');
            if ($target.length && $target.offset()) {
                $('html, body').animate({
                    scrollTop: $target.offset().top - 100
                }, 800);
            }
        };
        
        // Close search modal
        $(document).on('click', '#searchModal .close, #searchModal .overlay', function() {
            $('#searchModal').fadeOut();
        });
        
        // Prevent modal content clicks from closing modal
        $(document).on('click', '#searchModal .modal-content', function(e) {
            e.stopPropagation();
        });
    }
    
    // === AJAX ADD TO CART ===
    
    // DEVA Products Grid: AJAX Add to Cart (for older buy-now-btn class)
    $(document).on('click', '.deva-product-card .buy-now-btn, .woocommerce ul.products li.product .button', function(e) {
        // Skip if this is the new deva-add-to-cart-btn
        if ($(this).hasClass('deva-add-to-cart-btn')) {
            return;
        }
        
        e.preventDefault();
        
        var $button = $(this);
        var productId = $button.data('product_id') || $button.attr('data-product_id');
        var originalText = $button.text();
        
        // Skip if this is a variable product or requires options
        if ($button.hasClass('product_type_variable') || $button.hasClass('product_type_grouped')) {
            return true; // Allow default behavior
        }
        
        if (!productId) {
            console.error('No product ID found');
            return;
        }
        
        // Visual feedback
        $button.text('Adding...').prop('disabled', true);
        
        $.ajax({
            url: wc_add_to_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'woocommerce_add_to_cart',
                product_id: productId,
                quantity: 1
            },
            success: function(response) {
                if (!response || response.error) {
                    $button.text('Error').css('background', '#dc3545');
                    setTimeout(function() {
                        $button.text(originalText).css('background', '').prop('disabled', false);
                    }, 2000);
                    return;
                }
                
                // Show success state
                $button.text('Added!').css('background', '#28a745');
                
                // Update cart count if exists
                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                
                // Reset button after 2 seconds
                setTimeout(function() {
                    $button.text(originalText).css('background', '').prop('disabled', false);
                }, 2000);
            },
            error: function() {
                // Fallback: redirect to product page or cart
                window.location = $button.attr('href') || wc_add_to_cart_params.cart_url;
            }
        });
    });
    
    // DEVA Add to Cart functionality - Buy Now (Add to Cart + Redirect to Checkout)
    $(document).on('click', '.deva-add-to-cart-btn', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $button = $(this);
        var productId = $button.data('product-id');
        var $productActions = $button.closest('.deva-product-actions');
        
        if ($button.hasClass('loading')) {
            return;
        }
        
        if (!productId) {
            console.error('No product ID found for add to cart');
            return;
        }
        
        $button.addClass('loading');
        $button.find('.button-text').text('Processing...');
        
        $.ajax({
            url: wc_add_to_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'woocommerce_add_to_cart',
                product_id: productId,
                quantity: 1
            },
            success: function(response) {
                $button.removeClass('loading');
                
                if (response && !response.error) {
                    // Success: show brief confirmation then redirect to checkout
                    $button.find('.button-text').text('Redirecting...');
                    $button.addClass('added');
                    
                    // Update cart fragments if available
                    if (response.fragments && response.fragments['div.widget_shopping_cart_content']) {
                        $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                    }
                    
                    // Show quick notification
                    showBuyNowNotification(productId);
                    
                    // Redirect to checkout after brief delay
                    setTimeout(function() {
                        var checkoutUrl = '/checkout/'; // Default fallback
                        
                        // Try to get checkout URL from available WooCommerce params
                        if (typeof deva_wc_params !== 'undefined' && deva_wc_params.checkout_url) {
                            checkoutUrl = deva_wc_params.checkout_url;
                        } else if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.checkout_url) {
                            checkoutUrl = wc_add_to_cart_params.checkout_url;
                        } else if (typeof woocommerce_params !== 'undefined' && woocommerce_params.checkout_url) {
                            checkoutUrl = woocommerce_params.checkout_url;
                        }
                        
                        // Ensure absolute URL
                        if (checkoutUrl.indexOf('http') !== 0) {
                            checkoutUrl = window.location.origin + checkoutUrl;
                        }
                        
                        console.log('Redirecting to checkout:', checkoutUrl);
                        window.location.href = checkoutUrl;
                    }, 1000);
                    
                } else {
                    // Error state
                    $button.find('.button-text').text('Error');
                    setTimeout(function() {
                        $button.find('.button-text').text('Buy Now');
                    }, 2000);
                }
            },
            error: function() {
                $button.removeClass('loading');
                $button.find('.button-text').text('Error');
                setTimeout(function() {
                    $button.find('.button-text').text('Buy Now');
                }, 2000);
            }
        });
    });
    
    // Prevent clicks on View Cart buttons from triggering add to cart
    $(document).on('click', '.deva-view-cart-btn', function(e) {
        // Allow default behavior (navigate to cart)
        return true;
    });
    
    // === INITIALIZATION ===
    
    // Main initialization function
    function initializeShop() {
        // Debug: Check if shop_ajax is loaded
        if (typeof shop_ajax === 'undefined') {
            console.warn('DEVA Wishlist: shop_ajax not loaded, wishlist server features disabled');
            // Create a fallback object
            window.shop_ajax = {
                ajax_url: '/wp-admin/admin-ajax.php',
                nonce: '',
                is_user_logged_in: false
            };
        } else {
            console.log('DEVA Wishlist: shop_ajax loaded successfully', shop_ajax);
        }
        
        // Initialize all shop functionality
        initializeWishlistFunctionality();
        initializeShopFunctionality();
    }
    
    // Try to initialize with a slight delay to allow WordPress to localize scripts
    function tryInitialization() {
        if (typeof shop_ajax !== 'undefined') {
            // shop_ajax is available, initialize immediately
            initializeShop();
        } else {
            // shop_ajax not yet available, try again after a short delay
            var retryCount = 0;
            var maxRetries = 5;
            
            function retryInit() {
                retryCount++;
                if (typeof shop_ajax !== 'undefined') {
                    initializeShop();
                } else if (retryCount < maxRetries) {
                    setTimeout(retryInit, 100);
                } else {
                    console.warn('DEVA: shop_ajax not available after retries, initializing with fallback');
                    initializeShop();
                }
            }
            
            setTimeout(retryInit, 100);
        }
    }
    
    
    // Cart notification function
    function showCartNotification(productId, quantity) {
        // Get product name for notification
        var productName = $('.deva-product-card[data-product-id="' + productId + '"] .deva-product-title a').text();
        
        // Get current cart count from WooCommerce (if available)
        var cartCount = '';
        if (typeof wc_cart_fragments_params !== 'undefined') {
            var cartCountElement = $('.cart-contents-count, .cart-count');
            if (cartCountElement.length) {
                var currentCount = parseInt(cartCountElement.text()) || 0;
                cartCount = '<br><small>Cart: ' + (currentCount + quantity) + ' item' + (currentCount + quantity !== 1 ? 's' : '') + '</small>';
            }
        }
        
        // Create notification
        var notification = $('<div class="deva-cart-notification">' +
            '<div class="notification-content">' +
                '<div class="notification-icon">' +
                    '<svg class="cart-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                        '<circle cx="9" cy="21" r="1"></circle>' +
                        '<circle cx="20" cy="21" r="1"></circle>' +
                        '<path d="m1 1 4 4 16 1-1 10H6L4 1H1"></path>' +
                    '</svg>' +
                '</div>' +
                '<div class="notification-text">' +
                    '<strong>Added to cart!</strong>' +
                    (productName ? '<br><span class="product-name">' + productName + '</span>' : '') +
                    cartCount +
                '</div>' +
                '<a href="' + (wc_add_to_cart_params.cart_url || '/cart/') + '" class="view-cart-link">View Cart</a>' +
            '</div>' +
        '</div>');
        
        // Add to body
        $('body').append(notification);
        
        // Show notification with animation
        setTimeout(function() {
            notification.addClass('show');
        }, 100);
        
        // Auto-hide after 5 seconds (increased from 4)
        setTimeout(function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 5000);
        
        // Allow manual close on click
        notification.on('click', function(e) {
            // Don't close if clicking the view cart link
            if (!$(e.target).hasClass('view-cart-link')) {
                notification.removeClass('show');
                setTimeout(function() {
                    notification.remove();
                }, 300);
            }
        });
    }
    
    // Buy Now notification function (shorter duration)
    function showBuyNowNotification(productId) {
        // Get product name for notification
        var productName = $('.deva-product-card[data-product-id="' + productId + '"] .deva-product-title a').text();
        
        // Create notification
        var notification = $('<div class="deva-cart-notification buy-now-notification">' +
            '<div class="notification-content">' +
                '<div class="notification-icon">' +
                    '<svg class="cart-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">' +
                        '<path d="M9 12l2 2 4-4"></path>' +
                        '<circle cx="12" cy="12" r="10"></circle>' +
                    '</svg>' +
                '</div>' +
                '<div class="notification-text">' +
                    '<strong>Added to cart!</strong>' +
                    (productName ? '<br><span class="product-name">' + productName + '</span>' : '') +
                    '<br><small>Redirecting to checkout...</small>' +
                '</div>' +
            '</div>' +
        '</div>');
        
        // Add to body
        $('body').append(notification);
        
        // Show notification with animation
        setTimeout(function() {
            notification.addClass('show');
        }, 100);
        
        // Auto-hide after 1.5 seconds (shorter for buy now)
        setTimeout(function() {
            notification.removeClass('show');
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 1500);
    }
    
    // Initialize shop functionality with retry mechanism
    tryInitialization();
});

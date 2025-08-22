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
        // Ensure favorites is an array
        if (!Array.isArray(favorites)) {
            console.warn('DEVA: updateFavoritesUI received non-array:', favorites);
            
            // Convert object with numeric keys to array
            if (favorites && typeof favorites === 'object') {
                var convertedArray = [];
                for (var key in favorites) {
                    if (favorites.hasOwnProperty(key) && !isNaN(favorites[key])) {
                        convertedArray.push(parseInt(favorites[key], 10));
                    }
                }
                favorites = convertedArray;
            } else {
                favorites = [];
            }
        }
        
        $('.deva-wishlist-heart, .favorite-heart, .deva-favorite-heart').removeClass('active');
        favorites.forEach(function(productId) {
            $('.deva-wishlist-heart[data-product-id="' + productId + '"], .favorite-heart[data-product-id="' + productId + '"], .deva-favorite-heart[data-product-id="' + productId + '"]').addClass('active');
        });
    }
    
    // Sync wishlist with server (optimized)
    function syncWishlistWithServer(forceSync) {
        var favorites = getFavoritesFromStorage();
        
        // Check if we have proper AJAX configuration and user is logged in
        if (typeof shop_ajax === 'undefined' || !shop_ajax || !shop_ajax.ajax_url || !shop_ajax.nonce) {
            console.log('DEVA: shop_ajax not properly configured, skipping server sync');
            // Just update UI with local favorites
            updateFavoritesUI(favorites);
            updateWishlistCount(favorites.length);
            return;
        }
        
        // Check if we need to sync:
        // 1. Force sync is requested (user action)
        // 2. User is logged in AND has favorites to sync
        // 3. Skip if no favorites and no force sync
        if (!forceSync && favorites.length === 0) {
            console.log('DEVA: No favorites to sync, skipping server sync');
            updateFavoritesUI(favorites);
            updateWishlistCount(favorites.length);
            return;
        }
        
        // Check if we already synced in this session
        var lastSyncTime = sessionStorage.getItem('deva_last_sync');
        var currentTime = Date.now();
        var syncCooldown = 30000; // 30 seconds cooldown
        
        if (!forceSync && lastSyncTime && (currentTime - parseInt(lastSyncTime)) < syncCooldown) {
            console.log('DEVA: Sync cooldown active, skipping server sync');
            updateFavoritesUI(favorites);
            updateWishlistCount(favorites.length);
            return;
        }
        
        // Only sync with server if user is logged in
        if (shop_ajax.is_user_logged_in) {
            var config = getAjaxConfig('sync_favorites');
            
            // Validate config before making request
            if (!config.data.nonce) {
                console.log('DEVA: No nonce available, skipping server sync');
                updateFavoritesUI(favorites);
                updateWishlistCount(favorites.length);
                return;
            }
            
            config.data.favorites = favorites;
            
            console.log('DEVA: Syncing ' + favorites.length + ' favorites with server...');
            
            $.ajax(config).done(function(response) {
                if (response.success && Array.isArray(response.data.favorites)) {
                    // Update localStorage with server favorites
                    localStorage.setItem('deva_favorites', JSON.stringify(response.data.favorites));
                    
                    // Mark sync time
                    sessionStorage.setItem('deva_last_sync', currentTime.toString());
                    
                    // Update UI
                    updateFavoritesUI(response.data.favorites);
                    updateWishlistCount(response.data.favorites.length);
                    
                    console.log('DEVA: Sync successful, ' + response.data.favorites.length + ' favorites');
                } else {
                    console.log('DEVA: Server sync failed:', response);
                    // Update UI with local favorites
                    updateFavoritesUI(favorites);
                    updateWishlistCount(favorites.length);
                }
            }).fail(function(xhr, status, error) {
                console.log('DEVA: Server sync AJAX failed:', {xhr: xhr, status: status, error: error});
                // Update UI with local favorites
                updateFavoritesUI(favorites);
                updateWishlistCount(favorites.length);
            });
        } else {
            console.log('DEVA: User not logged in, using local favorites only');
            // Update UI with local favorites for non-logged-in users
            updateFavoritesUI(favorites);
            updateWishlistCount(favorites.length);
        }
    }
    
    // Show wishlist message - DISABLED
    function showWishlistMessage(message, type) {
        // Custom notifications disabled - using default WooCommerce notifications only
        return;
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
        var favorites = getFavoritesFromStorage();
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
        
        // Trigger custom event for other parts of the site to listen for
        $(document).trigger('deva_wishlist_updated');
        
        // Sync with server if logged in
        if (typeof shop_ajax !== 'undefined' && shop_ajax && shop_ajax.is_user_logged_in) {
            var config = getAjaxConfig('toggle_favorite');
            config.data.product_id = productId;
            config.data.is_favorite = isBecomingFavorited;
            
            $.ajax(config).done(function(response) {
                $heart.removeClass('loading');
                if (response.success) {
                    // Notification removed - using default WooCommerce notifications only
                    
                    // Update wishlist count from server if available
                    if (response.data && typeof response.data.count !== 'undefined') {
                        updateWishlistCount(response.data.count);
                    }
                    
                    // Force sync to ensure server and local are in sync after user action
                    syncWishlistWithServer(true);
                } else {
                    // Revert optimistic update on server error
                    $heart.toggleClass('active');
                    // Notification removed - using default WooCommerce notifications only
                }
            }).fail(function() {
                $heart.removeClass('loading');
                // Keep optimistic update for localStorage fallback
                // Notification removed - using default WooCommerce notifications only
            });
        } else {
            $heart.removeClass('loading');
            // Fallback to localStorage only
            // Notification removed - using default WooCommerce notifications only
        }
    }
    
    // Get favorites from localStorage and ensure it's a proper array
    function getFavoritesFromStorage() {
        try {
            var stored = localStorage.getItem('deva_favorites');
            if (!stored || stored === 'null' || stored === 'undefined') {
                return [];
            }
            
            var favorites = JSON.parse(stored);
            
            // Handle case where it's an object with numeric keys
            if (!Array.isArray(favorites) && favorites && typeof favorites === 'object') {
                console.warn('DEVA: Converting favorites object to array');
                var convertedArray = [];
                for (var key in favorites) {
                    if (favorites.hasOwnProperty(key) && !isNaN(favorites[key])) {
                        convertedArray.push(parseInt(favorites[key], 10));
                    }
                }
                favorites = convertedArray;
                // Save the corrected array back to localStorage
                localStorage.setItem('deva_favorites', JSON.stringify(favorites));
            }
            
            // Ensure it's an array
            if (!Array.isArray(favorites)) {
                console.warn('DEVA: favorites not an array, resetting');
                favorites = [];
            }
            
            // Filter out invalid values and ensure integers
            favorites = favorites.filter(function(id) { return !isNaN(id) && id > 0; });
            favorites = favorites.map(function(id) { return parseInt(id, 10); });
            
            return favorites;
        } catch (e) {
            console.error('DEVA: Error parsing favorites from localStorage:', e);
            localStorage.removeItem('deva_favorites');
            return [];
        }
    }
    
    // Initialize wishlist functionality
    function initializeWishlistFunctionality() {
        // Get current favorites and update heart icons
        var favorites = getFavoritesFromStorage();
        updateFavoritesUI(favorites);
        updateWishlistCount(favorites.length);
        
        // Bind wishlist heart click events (including backward compatibility)
        $(document).on('click', '.deva-wishlist-heart, .favorite-heart, .deva-favorite-heart', handleWishlistClick);
        
        // Only sync with server if user is logged in AND has favorites to sync
        if (typeof shop_ajax !== 'undefined' && shop_ajax && shop_ajax.is_user_logged_in && favorites.length > 0) {
            syncWishlistWithServer(false); // Don't force sync
        }
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
    
    // DEVA Products Grid: AJAX Add to Cart with Buy Now workflow
    $(document).on('click', '.deva-product-card .buy-now-btn, .deva-product-card .deva-add-to-cart-btn, .woocommerce ul.products li.product .button', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var productId = $button.data('product_id') || $button.attr('data-product_id');
        var originalText = $button.text();
        
            // Skip if this is a variable product, grouped product, or booking product that requires options
            if ($button.hasClass('product_type_variable') || 
                $button.hasClass('product_type_grouped') ||
                ($button.hasClass('product_type_booking') && !$button.hasClass('wc-bookings-simple-booking'))) {
                return true; // Allow default behavior for products requiring options
            }        if (!productId) {
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
    
    // DEVA Add to Cart functionality
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
        $button.find('.button-text').text('Adding...');
        
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
                    // Update cart fragments
                    $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                    
                    // Show redirecting state and redirect to checkout
                    $button.find('.button-text').text('Redirecting...');
                    
                    // Custom notification removed - using default WooCommerce notifications only
                    
                    // Get checkout URL with fallbacks
                    var checkoutUrl = '/checkout/';
                    if (typeof deva_wc_params !== 'undefined' && deva_wc_params.checkout_url) {
                        checkoutUrl = deva_wc_params.checkout_url;
                    } else if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.checkout_url) {
                        checkoutUrl = wc_add_to_cart_params.checkout_url;
                    } else if (typeof woocommerce_params !== 'undefined' && woocommerce_params.checkout_url) {
                        checkoutUrl = woocommerce_params.checkout_url;
                    }
                    
                    // Redirect to checkout after short delay
                    setTimeout(function() {
                        window.location.href = checkoutUrl;
                    }, 800);
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
    
    // Buy Now notification function - DISABLED
    function showBuyNowNotification(productId) {
        // Custom notifications disabled - using default WooCommerce notifications only
        return;
    }
    
    // === PAGINATION FUNCTIONALITY ===
    
    // Handle pagination clicks for products shortcode
    $(document).on('click', '.deva-pagination a[data-page], .deva-pagination a[href*="paged="]', function(e) {
        e.preventDefault();
        
        var $link = $(this);
        var $container = $link.closest('.deva-shop-section');
        var $productsContainer = $container.find('.deva-products-container');
        
        // Check if we found the required containers
        if ($container.length === 0 || $productsContainer.length === 0) {
            return;
        }
        
        // Check if AJAX is enabled for this container
        var ajaxEnabled = $container.data('ajax');
        if (ajaxEnabled !== 'true' && ajaxEnabled !== true) {
            // If AJAX is disabled, allow normal navigation
            window.location.href = $link.attr('href');
            return;
        }
        
        // Get page number from data attribute or href
        var page = $link.data('page');
        if (!page) {
            // Fallback: try to parse from href for backward compatibility
            var href = $link.attr('href');
            var pageMatch = href.match(/[?&]paged=(\d+)/);
            page = pageMatch ? parseInt(pageMatch[1]) : 1;
        }
        
        page = parseInt(page) || 1;
        
        // Get shortcode attributes
        var shortcodeAtts = $productsContainer.data('shortcode-atts');
        
        // Ensure shortcodeAtts is properly formatted
        if (typeof shortcodeAtts === 'string') {
            try {
                shortcodeAtts = JSON.parse(shortcodeAtts);
            } catch (e) {
                shortcodeAtts = {
                    per_page: 12,
                    columns: 3,
                    class: '',
                    pagination: 'true',
                    ajax: 'true'
                };
            }
        }
        
        // Show loading state
        $productsContainer.addClass('loading');
        $productsContainer.append('<div class="deva-loading-overlay"><div class="deva-spinner"></div></div>');
        
        // Make AJAX request
        var config = getAjaxConfig('deva_load_products');
        config.data.paged = page;
        config.data.shortcode_atts = JSON.stringify(shortcodeAtts);
        
        $.ajax(config)
            .done(function(response) {
                if (response.success) {
                    // Replace products container content
                    $productsContainer.html(response.data.html);
                    
                    // Scroll to top of products section
                    $('html, body').animate({
                        scrollTop: $container.offset().top - 50
                    }, 500);
                    
                    // Re-initialize wishlist functionality for new content (just update UI, don't sync)
                    var favorites = getFavoritesFromStorage();
                    updateFavoritesUI(favorites);
                    updateWishlistCount(favorites.length);
                } else {
                    console.error('AJAX request failed with response:', response);
                    // Fallback to normal navigation
                    window.location.href = $link.attr('href');
                }
            })
            .fail(function(xhr, status, error) {
                console.error('AJAX request failed:', error);
                // Fallback to normal navigation
                window.location.href = $link.attr('href');
            })
            .always(function() {
                $productsContainer.removeClass('loading');
                $('.deva-loading-overlay').remove();
            });
    });
    
    // Initialize shop functionality with retry mechanism
    tryInitialization();
});

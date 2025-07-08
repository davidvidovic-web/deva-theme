jQuery(document).ready(function($) {
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
    $('.search-close').on('click', function() {
        $('#searchModal').fadeOut();
    });
    
    // Close modal when clicking outside
    $('#searchModal').on('click', function(e) {
        if (e.target === this) {
            $(this).fadeOut();
        }
    });
    
    // Escape key to close modal
    $(document).on('keyup', function(e) {
        if (e.keyCode === 27) { // ESC key
            $('#searchModal').fadeOut();
        }
    });
    
    // Favorite heart functionality (works with both custom and WooCommerce templates)
    $(document).on('click', '.favorite-heart, .deva-favorite-heart', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $heart = $(this);
        var productId = $heart.data('product-id');
        
        // Toggle active class
        $heart.toggleClass('active');
        
        // Get current favorites from localStorage
        var favorites = JSON.parse(localStorage.getItem('deva_favorites') || '[]');
        
        if ($heart.hasClass('active')) {
            // Add to favorites
            if (favorites.indexOf(productId) === -1) {
                favorites.push(productId);
            }
        } else {
            // Remove from favorites
            var index = favorites.indexOf(productId);
            if (index > -1) {
                favorites.splice(index, 1);
            }
        }
        
        // Save to localStorage
        localStorage.setItem('deva_favorites', JSON.stringify(favorites));
        
        // Optional: Send AJAX request to save server-side if user is logged in
        if (shop_ajax && shop_ajax.ajax_url) {
            $.ajax({
                url: shop_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'toggle_favorite',
                    product_id: productId,
                    nonce: shop_ajax.nonce
                },
                success: function(response) {
                    // Handle success if needed
                }
            });
        }
    });
    
    // Load favorites on page load
    function loadFavorites() {
        var favorites = JSON.parse(localStorage.getItem('deva_favorites') || '[]');
        
        $('.favorite-heart').each(function() {
            var productId = $(this).data('product-id');
            if (favorites.indexOf(productId) !== -1) {
                $(this).addClass('active');
            }
        });
    }
    
    // Initialize favorites
    loadFavorites();
    
    // DEVA Products Grid: AJAX Add to Cart (works with both templates)
    $(document).on('click', '.deva-product-card .buy-now-btn, .woocommerce ul.products li.product .button', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var productId = $button.data('product_id') || $button.attr('data-product_id');
        var originalText = $button.text();
        
        // Skip if this is a variable product or requires options
        if ($button.hasClass('product_type_variable') || $button.hasClass('product_type_grouped')) {
            return true; // Allow default behavior
        }
        
        // Show loading state
        $button.text('Adding...').prop('disabled', true);
        
        // Add to cart via AJAX
        $.ajax({
            url: wc_add_to_cart_params ? wc_add_to_cart_params.ajax_url : '/wp-admin/admin-ajax.php',
            type: 'POST',
            data: {
                action: 'woocommerce_add_to_cart',
                product_id: productId,
                quantity: 1
            },
            success: function(response) {
                if (response.error && response.product_url) {
                    window.location = response.product_url;
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
            return false;
        }
        
        $button.addClass('loading').text('Adding...');
        
        // Use WooCommerce AJAX add to cart
        $.ajax({
            url: wc_add_to_cart_params ? wc_add_to_cart_params.ajax_url : ajaxurl,
            type: 'POST',
            data: {
                action: 'woocommerce_add_to_cart',
                product_id: productId,
                quantity: 1,
                security: wc_add_to_cart_params ? wc_add_to_cart_params.wc_ajax_nonce : ''
            },
            success: function(response) {
                if (response.error) {
                    alert('Error: ' + response.error);
                    $button.removeClass('loading').text('Buy Now');
                } else {
                    // Update cart fragments if available
                    if (response.fragments) {
                        $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                    }
                    
                    // Show success message
                    showSuccessMessage('Product added to cart!');
                    
                    // Create and show the View Cart link with exact same styling
                    $button.removeClass('loading').text('Added!');
                    
                    // Wait a moment, then replace with View Cart button
                    setTimeout(function() {
                        var cartUrl = wc_add_to_cart_params && wc_add_to_cart_params.cart_url ? wc_add_to_cart_params.cart_url : '/cart/';
                        
                        // Create a View Cart link that looks exactly like the Buy Now button
                        var $viewCartLink = $('<a>', {
                            href: cartUrl,
                            class: 'deva-add-to-cart-btn deva-view-cart-btn',
                            text: 'View Cart'
                        });
                        
                        // Replace the button with the link
                        $button.replaceWith($viewCartLink);
                        
                        // Mark the container as having item in cart
                        $productActions.addClass('cart-added');
                    }, 1500);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                $button.removeClass('loading').text('Buy Now');
                alert('Error adding product to cart. Please try again.');
            }
        });
    });
    
    // Success message function
    function showSuccessMessage(message) {
        // Remove any existing success messages
        $('.deva-success-message').remove();
        
        var $message = $('<div class="deva-success-message">' + message + '</div>');
        $message.css({
            position: 'fixed',
            top: '20px',
            right: '20px',
            background: '#48733d',
            color: 'white',
            padding: '15px 20px',
            borderRadius: '6px',
            zIndex: 9999,
            fontWeight: '600',
            boxShadow: '0 4px 15px rgba(0,0,0,0.2)'
        });
        
        $('body').append($message);
        
        // Fade in
        $message.fadeIn(300);
        
        // Auto remove after 3 seconds
        setTimeout(function() {
            $message.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }

    // Smooth scroll for pagination
    $(document).on('click', '.woocommerce-pagination a', function(e) {
        // Only handle this for regular shop pages, not AJAX pagination
        if ($(this).closest('.deva-products-container').length === 0) {
            setTimeout(function() {
                var $target = $('.woocommerce-products-header');
                if ($target.length && $target.offset()) {
                    $('html, body').animate({
                        scrollTop: $target.offset().top - 100
                    }, 500);
                }
            }, 100);
        }
    });
    
    // AJAX Pagination for DEVA Products Shortcode
    $(document).on('click', '.deva-shop-section[data-ajax="true"] .deva-pagination a', function(e) {
        e.preventDefault();
        
        var $container = $(this).closest('.deva-shop-section').find('.deva-products-container');
        var $section = $(this).closest('.deva-shop-section');
        var url = $(this).attr('href');
        var page = 1;
        
        // Store the scroll position before AJAX call with fallback
        var scrollTop = 0;
        try {
            if ($section.length && $section.offset()) {
                scrollTop = $section.offset().top - 100;
            }
        } catch (e) {
            scrollTop = 0;
        }
        
        // Extract page number from URL - try multiple patterns
        var match = url.match(/[?&]paged=(\d+)/) || 
                   url.match(/[?&]deva_page=(\d+)/) || 
                   url.match(/\/page\/(\d+)/);
        if (match) {
            page = parseInt(match[1]);
        } else {
            // If no page found in URL, get from link text for prev/next
            var linkText = $(this).text().trim();
            if (linkText === '»' || linkText === 'Next') {
                // Get current page from pagination and add 1
                var $current = $(this).closest('.deva-pagination').find('.current');
                if ($current.length) {
                    page = parseInt($current.text()) + 1;
                }
            } else if (linkText === '«' || linkText === 'Previous') {
                // Get current page from pagination and subtract 1
                var $current = $(this).closest('.deva-pagination').find('.current');
                if ($current.length) {
                    page = Math.max(1, parseInt($current.text()) - 1);
                }
            } else if (!isNaN(parseInt(linkText))) {
                // Direct page number
                page = parseInt(linkText);
            }
        }
        
        // Navigate to the specified page
        
        // Show loading state
        $container.addClass('loading');
        $container.append('<div class="deva-loading">Loading...</div>');
        
        // Get shortcode attributes
        var atts = $container.data('shortcode-atts');
        
        $.post(deva_ajax.ajax_url, {
            action: 'deva_load_products',
            paged: page,
            shortcode_atts: atts,
            nonce: deva_ajax.nonce
        }, function(response) {
            if (response.success) {
                $container.html(response.data);
                $container.removeClass('loading');
                
                // Scroll to top of products using stored position
                if (scrollTop > 0) {
                    $('html, body').animate({
                        scrollTop: scrollTop
                    }, 500);
                }
            }
        }).fail(function() {
            // Remove loading state on error
            $container.removeClass('loading');
            $container.find('.deva-loading').remove();
        });
    });
    
    // Handle WooCommerce's default added_to_cart links if they appear
    $(document).on('DOMNodeInserted', function(e) {
        var $target = $(e.target);
        
        // Check if a WooCommerce added_to_cart link was inserted
        if ($target.hasClass('added_to_cart') && $target.hasClass('wc-forward')) {
            var $productActions = $target.closest('.deva-product-actions');
            if ($productActions.length) {
                // Hide the default link and show our custom styling
                $target.hide();
                $productActions.addClass('cart-added');
                
                // Find the original button and replace it
                var $originalButton = $productActions.find('.deva-add-to-cart-btn');
                if ($originalButton.length) {
                    var cartUrl = $target.attr('href') || (wc_add_to_cart_params && wc_add_to_cart_params.cart_url ? wc_add_to_cart_params.cart_url : '/cart/');
                    
                    var $viewCartLink = $('<a>', {
                        href: cartUrl,
                        class: 'deva-add-to-cart-btn deva-view-cart-btn',
                        text: 'View Cart'
                    });
                    
                    $originalButton.replaceWith($viewCartLink);
                }
            }
        }
    });
    
    // Prevent clicks on View Cart buttons from triggering add to cart
    $(document).on('click', '.deva-view-cart-btn', function(e) {
        // Allow default behavior (navigate to cart)
        return true;
    });
});

<?php

/**
 * DEVA Single Product Page Shortcode
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DEVA Single Product Page Shortcode
 */
function deva_single_product_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'product_id' => 0,
        'class' => ''
    ), $atts);

    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        return '<div class="deva-error">' . __('WooCommerce is not active.', 'hello-elementor-child') . '</div>';
    }

    // Get product ID from current post if not provided
    if (!$atts['product_id']) {
        global $post;
        $atts['product_id'] = $post->ID;
    }

    $product = wc_get_product($atts['product_id']);

    if (!$product) {
        return '<div class="deva-error">' . __('Product not found.', 'hello-elementor-child') . '</div>';
    }

    ob_start();
?>
    <div class="deva-single-product <?php echo esc_attr($atts['class']); ?>">
        <!-- Breadcrumbs -->
        <div class="deva-breadcrumbs">
            <a href="<?php echo home_url(); ?>"><?php _e('Home', 'hello-elementor-child'); ?></a>
            <span class="separator">›</span>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>"><?php _e('Shop', 'hello-elementor-child'); ?></a>
            <span class="separator">›</span>
            <?php
            // Get product category for breadcrumbs
            $product_categories = wp_get_post_terms($product->get_id(), 'product_cat');
            if (!empty($product_categories) && !is_wp_error($product_categories)) {
                $category = $product_categories[0]; // Get first category
                echo '<a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a>';
                echo '<span class="separator">›</span>';
            }
            ?>
            <span><?php echo esc_html($product->get_name()); ?></span>
        </div>

        <!-- Product Main Section -->
        <div class="product-main">
            <!-- Product Image/Slider -->
            <div class="product-image-section">
                <div class="product-image-slider">
                    <?php
                    $attachment_ids = $product->get_gallery_image_ids();
                    $main_image = wp_get_attachment_image_url($product->get_image_id(), 'large');
                    if ($main_image) :
                    ?>
                        <img src="<?php echo esc_url($main_image); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" />
                    <?php else : ?>
                        <img src="<?php echo wc_placeholder_img_src(); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" />
                    <?php endif; ?>
                </div>

                <?php if (!empty($attachment_ids)) : ?>
                    <div class="product-thumbnails">
                        <div class="product-thumbnail active">
                            <img src="<?php echo esc_url($main_image); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" />
                        </div>
                        <?php foreach ($attachment_ids as $attachment_id) : ?>
                            <div class="product-thumbnail">
                                <img src="<?php echo esc_url(wp_get_attachment_image_url($attachment_id, 'thumbnail')); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" />
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Information -->
            <div class="product-info-section">
                <!-- Product Category -->
                <div class="product-brand">
                    <?php
                    $product_categories = wp_get_post_terms($product->get_id(), 'product_cat');
                    if (!empty($product_categories) && !is_wp_error($product_categories)) {
                        $category = $product_categories[0]; // Get first category
                        echo '<p><a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a></p>';
                    }
                    ?>
                </div>

                <!-- Product Name -->
                <h2 class="product-name"><?php echo esc_html($product->get_name()); ?></h2>

                <!-- Product Attributes -->
                <?php
                $attributes = $product->get_attributes();
                if (!empty($attributes)) :
                    foreach ($attributes as $attribute) :
                        $attribute_name = $attribute->get_name();
                        $attribute_label = wc_attribute_label($attribute_name);
                        $attribute_values = $product->get_attribute($attribute_name);

                        if (!empty($attribute_values)) :
                ?>
                            <div class="product-attribute-label">
                                <p><?php echo esc_html($attribute_label); ?></p>
                            </div>

                            <div class="attribute-selection">
                                <?php
                                $values = explode(', ', $attribute_values);
                                $values = array_map('trim', $values); // Remove any extra whitespace
                                $values = deva_sort_attribute_values($values); // Sort properly
                                
                                foreach ($values as $index => $value) :
                                ?>
                                    <div class="attribute-option <?php echo $index === 0 ? 'active' : ''; ?>" data-value="<?php echo esc_attr($value); ?>" data-attribute="<?php echo esc_attr($attribute_name); ?>">
                                        <?php echo esc_html($value); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                <?php
                        endif;
                    endforeach;
                endif; ?>

                <!-- Price Section -->
                <div class="price-section">
                    <span class="price-current"><?php echo $product->get_price_html(); ?></span>
                </div>

                <!-- Key Benefits List -->
                <div class="product-key-benefits">
                    <?php
                    $benefits = deva_get_key_benefits($product->get_id());

                    if (!empty($benefits)) :
                    ?>
                        <ul class="key-benefits-list">
                            <?php foreach ($benefits as $benefit) : ?>
                                <li class="key-benefit-item">
                                    <span class="benefit-checkmark">✓</span>
                                    <span class="benefit-text"><?php echo esc_html($benefit['benefit']); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="product-description">
                    <h6><?php _e('Description', 'hello-elementor-child'); ?></h6>
                    <?php
                    $description = $product->get_description();
                    if (!empty($description)) :
                    ?>
                        <div class="description-content" id="description-content">
                            <?php echo apply_filters('the_content', $description); ?>
                        </div>
                        <button class="read-more-btn" onclick="toggleDescription()" style="display: none;"><?php _e('Read More', 'hello-elementor-child'); ?></button>
                    <?php endif; ?>
                </div>

                <!-- Benefits Description -->
                <div class="product-benefits-description">

                    <?php
                    $benefits_description = deva_get_benefits_description($product->get_id());
                    if (!empty($benefits_description)) :
                    ?>
                        <h6><?php _e('Benefits', 'hello-elementor-child'); ?></h6>
                        <div class="benefits-description-content" id="benefits-description-content">
                            <?php echo wp_kses_post($benefits_description); ?>
                        </div>
                        <button class="read-more-btn" onclick="toggleBenefitsDescription()" style="display: none;"><?php _e('Read More', 'hello-elementor-child'); ?></button>
                    <?php endif; ?>
                </div>

                <!-- Quantity and Add to Cart -->
                <div class="product-actions" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                    <div class="quantity-selector">
                        <button class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                        <input type="number" class="quantity-input" value="1" min="1" id="quantity-input">
                        <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                    </div>
                    <button class="add-to-cart-btn" onclick="addToCart()"><?php _e('Add to Cart', 'hello-elementor-child'); ?></button>
                    <button class="buy-now-btn" onclick="buyNow()"><?php _e('Buy Now', 'hello-elementor-child'); ?></button>
                </div>
            </div>
        </div>

        <!-- Product Navigation -->
        <div class="product-navigation">
            <div class="nav-buttons">
                <?php
                $ingredients = deva_get_key_ingredients($product->get_id());
                $how_to_use = deva_get_how_to_use($product->get_id());
                $faqs = deva_get_product_faqs($product->get_id());
                
                // Determine first available section for active class
                $first_section = '';
                if (!empty($ingredients)) {
                    $first_section = 'ingredients-section';
                } elseif (!empty($how_to_use)) {
                    $first_section = 'how-to-use-section';
                } elseif (!empty($faqs)) {
                    $first_section = 'questions-section';
                }
                ?>
                <?php if (!empty($ingredients)) : ?>
                    <a href="#ingredients-section" class="nav-button <?php echo ($first_section === 'ingredients-section') ? 'active' : ''; ?>"><?php _e('Ingredients', 'hello-elementor-child'); ?></a>
                <?php endif; ?>
                <?php if (!empty($how_to_use)) : ?>
                    <a href="#how-to-use-section" class="nav-button <?php echo ($first_section === 'how-to-use-section') ? 'active' : ''; ?>"><?php _e('How to Use', 'hello-elementor-child'); ?></a>
                <?php endif; ?>
                <?php if (!empty($faqs)) : ?>
                    <a href="#questions-section" class="nav-button <?php echo ($first_section === 'questions-section') ? 'active' : ''; ?>"><?php _e('Questions', 'hello-elementor-child'); ?></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Product Sections -->
        <div class="product-sections">
            <!-- Ingredients Section -->
            <?php
            $ingredients = deva_get_key_ingredients($product->get_id());
            if (!empty($ingredients)) :
            ?>
            <div class="product-section" id="ingredients-section">
                <h2><?php _e('Key Ingredients', 'hello-elementor-child'); ?></h2>
                <p><?php _e('What\'s inside that really matters', 'hello-elementor-child'); ?></p>
                <div class="ingredients-grid">
                    <?php foreach ($ingredients as $ingredient) : ?>
                        <div class="ingredient-item">
                            <?php if (!empty($ingredient['ingredient_image'])) : ?>
                                <div class="ingredient-image">
                                    <img src="<?php echo esc_url($ingredient['ingredient_image']); ?>" alt="<?php echo esc_attr($ingredient['ingredient_title']); ?>">
                                </div>
                            <?php endif; ?>
                            <div class="ingredient-content">
                                <h4><?php echo esc_html($ingredient['ingredient_title']); ?></h4>
                                <p><?php echo wp_kses_post($ingredient['ingredient_description']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>            <!-- How to Use Section -->
            <?php
            $how_to_use = deva_get_how_to_use($product->get_id());
            if (!empty($how_to_use)) :
            ?>
            <div class="product-section" id="how-to-use-section">
                <h2><?php _e('How to Use', 'hello-elementor-child'); ?></h2>
                <div class="how-to-use-steps">
                    <?php foreach ($how_to_use as $index => $step) :
                        $step_number = $index + 1;
                        $is_odd = ($step_number % 2 === 1);
                    ?>
                        <div class="step-item <?php echo $is_odd ? 'step-odd' : 'step-even'; ?>">
                            <!-- <div class="step-number"><?php //echo $step_number; ?></div> -->
                            <div class="step-content">
                                <?php if ($is_odd) : ?>
                                    <!-- Odd steps: image, Step (x), text -->
                                    <?php if (!empty($step['step_image'])) : ?>
                                        <div class="step-image">
                                            <img src="<?php echo esc_url($step['step_image']); ?>" alt="<?php printf(__('Step %d', 'hello-elementor-child'), $step_number); ?>">
                                        </div>
                                    <?php endif; ?>
                                    <div class="step-text">
                                        <h4><?php printf(__('Step %d', 'hello-elementor-child'), $step_number); ?></h4>
                                        <p><?php echo wp_kses_post($step['step_description']); ?></p>
                                    </div>
                                <?php else : ?>
                                    <!-- Even steps: Step (x), text, image -->
                                    <div class="step-text">
                                        <h4><?php printf(__('Step %d', 'hello-elementor-child'), $step_number); ?></h4>
                                        <p><?php echo wp_kses_post($step['step_description']); ?></p>
                                    </div>
                                    <?php if (!empty($step['step_image'])) : ?>
                                        <div class="step-image">
                                            <img src="<?php echo esc_url($step['step_image']); ?>" alt="<?php printf(__('Step %d', 'hello-elementor-child'), $step_number); ?>">
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Questions Section -->
            <?php
            $faqs = deva_get_product_faqs($product->get_id());
            if (!empty($faqs)) :
            ?>
            <div class="product-section" id="questions-section">
                <h2><?php _e('Frequently Asked Questions', 'hello-elementor-child'); ?></h2>
                <?php foreach ($faqs as $faq) : ?>
                    <p><strong><?php printf(__('Q: %s', 'hello-elementor-child'), esc_html($faq['question'])); ?></strong></p>
                    <p><?php printf(__('A: %s', 'hello-elementor-child'), wp_kses_post($faq['answer'])); ?></p>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- Related Products -->
        <div class="related-products">
            <?php echo do_shortcode('[deva_products_slider related_to="' . $product->get_id() . '" limit="10" title="Related Products"]'); ?>
        </div>
    </div>

    <script>
        // Check if content needs read more functionality
        function initializeReadMore() {
            // Check description
            const descriptionContent = document.getElementById('description-content');
            const descriptionBtn = descriptionContent ? descriptionContent.nextElementSibling : null;

            if (descriptionContent && descriptionBtn) {
                if (descriptionContent.scrollHeight > 120) {
                    descriptionContent.classList.add('collapsed');
                    descriptionBtn.style.display = 'inline-flex';
                }
            }

            // Check benefits
            const benefitsContent = document.getElementById('benefits-content');
            const benefitsBtn = benefitsContent ? benefitsContent.nextElementSibling : null;

            if (benefitsContent && benefitsBtn) {
                if (benefitsContent.scrollHeight > 120) {
                    benefitsContent.classList.add('collapsed');
                    benefitsBtn.style.display = 'inline-flex';
                }
            }
        }

        function toggleDescription() {
            const content = document.getElementById('description-content');
            const button = content.nextElementSibling;

            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                button.textContent = 'Read Less';
                button.classList.add('expanded');
            } else {
                content.classList.add('collapsed');
                button.textContent = 'Read More';
                button.classList.remove('expanded');
            }
        }

        function toggleBenefits() {
            const content = document.getElementById('benefits-content');
            const button = content.nextElementSibling;

            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                button.textContent = 'Read Less';
                button.classList.add('expanded');
            } else {
                content.classList.add('collapsed');
                button.textContent = 'Read More';
                button.classList.remove('expanded');
            }
        }

        function toggleBenefitsDescription() {
            const content = document.getElementById('benefits-description-content');
            const button = content.nextElementSibling;

            if (content.classList.contains('collapsed')) {
                content.classList.remove('collapsed');
                button.textContent = 'Read Less';
                button.classList.add('expanded');
            } else {
                content.classList.add('collapsed');
                button.textContent = 'Read More';
                button.classList.remove('expanded');
            }
        }

        function changeQuantity(change) {
            const input = document.getElementById('quantity-input');
            const currentValue = parseInt(input.value);
            const newValue = currentValue + change;

            if (newValue >= 1) {
                input.value = newValue;
            }
        }

        function addToCart() {
            const quantity = document.getElementById('quantity-input').value;
            const productForm = document.querySelector('.product-actions');
            const productId = productForm.getAttribute('data-product-id');
            const addToCartBtn = document.querySelector('.add-to-cart-btn');
            
            // Collect selected attributes
            const selectedAttributes = {};
            document.querySelectorAll('.attribute-option.active').forEach(option => {
                const attributeName = option.getAttribute('data-attribute');
                const attributeValue = option.getAttribute('data-value');
                selectedAttributes[attributeName] = attributeValue;
            });
            
            // Show loading state
            const originalText = addToCartBtn.textContent;
            addToCartBtn.textContent = 'Adding...';
            addToCartBtn.disabled = true;
            addToCartBtn.classList.add('loading');
            
            // Prepare data for AJAX request
            const formData = {
                action: 'woocommerce_add_to_cart',
                product_id: productId,
                quantity: quantity
            };
            
            // Add attributes to form data
            Object.keys(selectedAttributes).forEach(attr => {
                formData[`attribute_${attr}`] = selectedAttributes[attr];
            });
            
            // Check if we have WooCommerce AJAX available
            const ajaxUrl = (typeof deva_wc_params !== 'undefined' && deva_wc_params.ajax_url) 
                ? deva_wc_params.ajax_url 
                : '/wp-admin/admin-ajax.php';
            
            // Use jQuery if available, otherwise fallback
            if (typeof jQuery !== 'undefined') {
                jQuery.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        handleAddToCartSuccess(response, addToCartBtn, originalText, quantity);
                    },
                    error: function(xhr, status, error) {
                        handleAddToCartError(addToCartBtn, originalText);
                    }
                });
            } else {
                // Fallback using fetch API
                const formBody = Object.keys(formData).map(key => 
                    encodeURIComponent(key) + '=' + encodeURIComponent(formData[key])
                ).join('&');
                
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formBody
                })
                .then(response => response.json())
                .then(data => {
                    handleAddToCartSuccess(data, addToCartBtn, originalText, quantity);
                })
                .catch(error => {
                    handleAddToCartError(addToCartBtn, originalText);
                });
            }
        }
        
        function handleAddToCartSuccess(response, button, originalText, quantity) {
            if (response.error) {
                // Error handled by default WooCommerce notifications
                console.error('Error:', response.error);
                resetButton(button, originalText);
            } else {
                // Update cart count if exists and jQuery is available
                if (typeof jQuery !== 'undefined' && response.fragments) {
                    jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, jQuery(button)]);
                }
                
                // Show success state
                button.textContent = 'Added!';
                button.style.background = '#28a745';
                
                // Custom notification removed - using default WooCommerce notifications only
                
                // Reset button after 2 seconds
                setTimeout(function() {
                    resetButton(button, originalText);
                }, 2000);
            }
        }
        
        function handleAddToCartError(button, originalText) {
            // Error handled by default WooCommerce notifications
            console.error('Error adding product to cart. Please try again.');
            resetButton(button, originalText);
        }
        
        function resetButton(button, originalText) {
            button.textContent = originalText;
            button.style.background = '';
            button.disabled = false;
            button.classList.remove('loading');
        }

        function buyNow() {
            const quantity = document.getElementById('quantity-input').value;
            const productForm = document.querySelector('.product-actions');
            const productId = productForm.getAttribute('data-product-id');
            const buyNowBtn = document.querySelector('.buy-now-btn');
            
            // Collect selected attributes
            const selectedAttributes = {};
            document.querySelectorAll('.attribute-option.active').forEach(option => {
                const attributeName = option.getAttribute('data-attribute');
                const attributeValue = option.getAttribute('data-value');
                selectedAttributes[attributeName] = attributeValue;
            });
            
            // Show loading state
            const originalText = buyNowBtn.textContent;
            buyNowBtn.textContent = 'Processing...';
            buyNowBtn.disabled = true;
            buyNowBtn.classList.add('loading');
            
            // Prepare data for AJAX request
            const formData = {
                action: 'woocommerce_add_to_cart',
                product_id: productId,
                quantity: quantity
            };
            
            // Add attributes to form data
            Object.keys(selectedAttributes).forEach(attr => {
                formData[`attribute_${attr}`] = selectedAttributes[attr];
            });
            
            // Check if we have WooCommerce AJAX available
            const ajaxUrl = (typeof deva_wc_params !== 'undefined' && deva_wc_params.ajax_url) 
                ? deva_wc_params.ajax_url 
                : '/wp-admin/admin-ajax.php';
            
            // Use jQuery if available, otherwise fallback
            if (typeof jQuery !== 'undefined') {
                jQuery.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        handleBuyNowSuccess(response, buyNowBtn, originalText);
                    },
                    error: function(xhr, status, error) {
                        handleBuyNowError(buyNowBtn, originalText);
                    }
                });
            } else {
                // Fallback using fetch API
                const formBody = Object.keys(formData).map(key => 
                    encodeURIComponent(key) + '=' + encodeURIComponent(formData[key])
                ).join('&');
                
                fetch(ajaxUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: formBody
                })
                .then(response => response.json())
                .then(data => {
                    handleBuyNowSuccess(data, buyNowBtn, originalText);
                })
                .catch(error => {
                    handleBuyNowError(buyNowBtn, originalText);
                });
            }
        }
        
        function handleBuyNowSuccess(response, button, originalText) {
            if (response.error) {
                // Error handled by default WooCommerce notifications
                console.error('Error:', response.error);
                resetButton(button, originalText);
            } else {
                // Update cart count if exists and jQuery is available
                if (typeof jQuery !== 'undefined' && response.fragments) {
                    jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, jQuery(button)]);
                }
                
                // Show "Redirecting..." message and redirect to checkout
                button.textContent = 'Redirecting...';
                
                // Get checkout URL with multiple fallbacks
                let checkoutUrl = '/checkout/';
                if (typeof deva_wc_params !== 'undefined' && deva_wc_params.checkout_url) {
                    checkoutUrl = deva_wc_params.checkout_url;
                } else if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.checkout_url) {
                    checkoutUrl = wc_add_to_cart_params.checkout_url;
                } else if (typeof woocommerce_params !== 'undefined' && woocommerce_params.checkout_url) {
                    checkoutUrl = woocommerce_params.checkout_url;
                }
                
                // Custom notification removed - using default WooCommerce notifications only
                
                // Redirect after short delay
                setTimeout(() => {
                    window.location.href = checkoutUrl;
                }, 800);
            }
        }
        
        function handleBuyNowError(button, originalText) {
            // Error handled by default WooCommerce notifications
            console.error('Error processing order. Please try again.');
            resetButton(button, originalText);
        }
        
        // Custom notification functions removed - using default WooCommerce notifications only

        // Navigation button active state and smooth scrolling
        document.querySelectorAll('.nav-button').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                // Remove active class from all buttons
                document.querySelectorAll('.nav-button').forEach(btn => btn.classList.remove('active'));

                // Add active class to clicked button
                this.classList.add('active');

                // Get target section and scroll to it
                const targetId = this.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetId);

                if (targetSection) {
                    targetSection.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Update active nav button on scroll
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('.product-section');
            const navButtons = document.querySelectorAll('.nav-button');

            let currentSection = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                if (window.scrollY >= sectionTop - 100) {
                    currentSection = section.getAttribute('id');
                }
            });

            navButtons.forEach(button => {
                button.classList.remove('active');
                if (button.getAttribute('href') === '#' + currentSection) {
                    button.classList.add('active');
                }
            });
        });

        // Size selection functionality
        document.querySelectorAll('.size-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.size-option').forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Attribute selection functionality
        document.querySelectorAll('.attribute-option').forEach(option => {
            option.addEventListener('click', function() {
                // Get the attribute name to only affect options within the same attribute group
                const attributeName = this.getAttribute('data-attribute');
                
                // Add visual feedback
                this.classList.add('selecting');
                
                // Remove active class from all options in the same attribute group
                document.querySelectorAll(`.attribute-option[data-attribute="${attributeName}"]`).forEach(opt => {
                    opt.classList.remove('active', 'selected-feedback');
                });
                
                // Add active class to clicked option with slight delay for visual effect
                setTimeout(() => {
                    this.classList.remove('selecting');
                    this.classList.add('active', 'selected-feedback');
                    
                    // Remove the feedback class after animation
                    setTimeout(() => {
                        this.classList.remove('selected-feedback');
                    }, 300);
                }, 100);
                
                // Update product selection
                updateProductSelection();
            });
            
            // Add keyboard support for accessibility
            option.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.click();
                }
            });
            
            // Make focusable for keyboard navigation
            option.setAttribute('tabindex', '0');
        });

        // Function to handle product selection updates
        function updateProductSelection() {
            const selectedAttributes = {};
            
            // Collect all selected attributes
            document.querySelectorAll('.attribute-option.active').forEach(option => {
                const attributeName = option.getAttribute('data-attribute');
                const attributeValue = option.getAttribute('data-value');
                selectedAttributes[attributeName] = attributeValue;
            });
            
            
            // Update the product form or data attributes for cart functionality
            const productForm = document.querySelector('.product-actions');
            if (productForm) {
                // Store selected attributes as data attributes for add to cart functionality
                Object.keys(selectedAttributes).forEach(attr => {
                    productForm.setAttribute(`data-${attr}`, selectedAttributes[attr]);
                });
            }
            
            // Update any display elements that show current selection
            updateSelectionDisplay(selectedAttributes);
            
            // Here you can implement additional logic to:
            // - Update product price based on variations
            // - Update product images based on color selection
            // - Update availability status
            // - Update SKU or product code display
        }
        
        // Function to update visual display of current selections
        function updateSelectionDisplay(selectedAttributes) {
            // Create or update a selection summary
            let summaryElement = document.querySelector('.selection-summary');
            if (!summaryElement) {
                summaryElement = document.createElement('div');
                summaryElement.className = 'selection-summary';
                summaryElement.style.cssText = 'margin: 10px 0; padding: 8px 12px; background: #f8f9fa; border-radius: 6px; font-size: 0.7rem; color: #666;';
                
                // Insert after the last attribute selection
                const lastAttributeSelection = document.querySelector('.attribute-selection:last-of-type');
                if (lastAttributeSelection) {
                    lastAttributeSelection.parentNode.insertBefore(summaryElement, lastAttributeSelection.nextSibling);
                }
            }
            
            // Update summary content
            const selections = Object.entries(selectedAttributes).map(([attr, value]) => {
                return `${attr.replace(/^pa_/, '').replace(/_/g, ' ')}: ${value}`;
            });
            
            if (selections.length > 0) {
                summaryElement.innerHTML = '<strong>Selected:</strong> ' + selections.join(', ');
                summaryElement.style.display = 'block';
            } else {
                summaryElement.style.display = 'none';
            }
        }

        // Thumbnail image switching
        document.querySelectorAll('.product-thumbnail').forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                const mainImage = document.querySelector('.product-image-slider img');
                const thumbnailImage = this.querySelector('img');

                mainImage.src = thumbnailImage.src.replace('thumbnail', 'large');

                document.querySelectorAll('.product-thumbnail').forEach(thumb => thumb.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Initialize read more functionality after page load
        window.addEventListener('load', initializeReadMore);
    </script>
<?php

    return ob_get_clean();
}

add_shortcode('deva_single_product', 'deva_single_product_shortcode');

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
        return '<div class="deva-error">WooCommerce is not active.</div>';
    }

    // Get product ID from current post if not provided
    if (!$atts['product_id']) {
        global $post;
        $atts['product_id'] = $post->ID;
    }

    $product = wc_get_product($atts['product_id']);

    if (!$product) {
        return '<div class="deva-error">Product not found.</div>';
    }

    ob_start();
?>
    <div class="deva-single-product <?php echo esc_attr($atts['class']); ?>">
        <!-- Breadcrumbs -->
        <div class="deva-breadcrumbs">
            <a href="<?php echo home_url(); ?>">Home</a>
            <span class="separator">›</span>
            <a href="<?php echo get_permalink(wc_get_page_id('shop')); ?>">Shop</a>
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
                                foreach ($values as $index => $value) :
                                ?>
                                    <div class="attribute-option <?php echo $index === 0 ? 'active' : ''; ?>" data-value="<?php echo esc_attr($value); ?>">
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

                <!-- Review Score -->
                <div class="review-score">
                    <div class="review-stars">
                        <span class="star filled">★</span>
                    </div>
                    <div class="review-count">
                        <a href="#reviews">4.2 | 156 reviews</a>
                    </div>
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
                    <?php else : ?>
                        <!-- Fallback benefits -->
                        <ul class="key-benefits-list">
                            <li class="key-benefit-item">
                                <span class="benefit-checkmark">✓</span>
                                <span class="benefit-text">100% Natural and Organic</span>
                            </li>
                            <li class="key-benefit-item">
                                <span class="benefit-checkmark">✓</span>
                                <span class="benefit-text">Clinically Tested</span>
                            </li>
                            <li class="key-benefit-item">
                                <span class="benefit-checkmark">✓</span>
                                <span class="benefit-text">Free from Harmful Chemicals</span>
                            </li>
                            <li class="key-benefit-item">
                                <span class="benefit-checkmark">✓</span>
                                <span class="benefit-text">Suitable for All Skin Types</span>
                            </li>
                        </ul>
                    <?php endif; ?>
                </div>

                <!-- Description -->
                <div class="product-description">
                    <h6>Description</h6>
                    <?php
                    $description = $product->get_description();
                    if (!empty($description)) :
                    ?>
                        <div class="description-content" id="description-content">
                            <?php echo apply_filters('the_content', $description); ?>
                        </div>
                        <button class="read-more-btn" onclick="toggleDescription()" style="display: none;">Read More</button>
                    <?php endif; ?>
                </div>

                <!-- Benefits Description -->
                <div class="product-benefits-description">

                    <?php
                    $benefits_description = deva_get_benefits_description($product->get_id());
                    if (!empty($benefits_description)) :
                    ?>
                        <h6>Benefits</h6>
                        <div class="benefits-description-content" id="benefits-description-content">
                            <?php echo wp_kses_post($benefits_description); ?>
                        </div>
                        <button class="read-more-btn" onclick="toggleBenefitsDescription()" style="display: none;">Read More</button>
                    <?php endif; ?>
                </div>

                <!-- Payment Options -->
                <div class="payment-options">
                    <div class="payment-options-title">Available Payment Options</div>
                    <div class="payment-methods">
                        <div class="payment-method">Credit Card</div>
                        <div class="payment-method">PayPal</div>
                        <div class="payment-method">Apple Pay</div>
                        <div class="payment-method">Google Pay</div>
                        <div class="payment-method">Bank Transfer</div>
                    </div>
                </div>

                <!-- Quantity and Add to Cart -->
                <div class="product-actions">
                    <div class="quantity-selector">
                        <button class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                        <input type="number" class="quantity-input" value="1" min="1" id="quantity-input">
                        <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                    </div>
                    <button class="add-to-cart-btn" onclick="addToCart()">Add to Cart</button>
                    <button class="buy-now-btn" onclick="buyNow()">Buy Now</button>
                </div>
            </div>
        </div>

        <!-- Product Navigation -->
        <div class="product-navigation">
            <div class="nav-buttons">
                <a href="#ingredients-section" class="nav-button active">Ingredients</a>
                <a href="#how-to-use-section" class="nav-button">How to Use</a>
                <a href="#reviews-section" class="nav-button">Reviews</a>
                <a href="#questions-section" class="nav-button">Questions</a>
            </div>
        </div>

        <!-- Product Sections -->
        <div class="product-sections">
            <!-- Ingredients Section -->

            <div class="product-section" id="ingredients-section">
                <h2>Key Ingredients</h2>
                <p>What’s inside that really matters</p>
                <?php
                $ingredients = deva_get_key_ingredients($product->get_id());
                if (!empty($ingredients)) :
                ?>
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
                <?php else : ?>
                    <!-- Fallback to mock data -->
                    <p>Our premium product is carefully crafted with the finest natural ingredients:</p>
                    <ul>
                        <li><strong>Organic Aloe Vera:</strong> Soothes and hydrates the skin</li>
                        <li><strong>Vitamin E:</strong> Provides antioxidant protection</li>
                        <li><strong>Hyaluronic Acid:</strong> Deeply moisturizes and plumps skin</li>
                        <li><strong>Green Tea Extract:</strong> Anti-inflammatory and anti-aging properties</li>
                        <li><strong>Jojoba Oil:</strong> Balances skin's natural oils</li>
                    </ul>
                <?php endif; ?>
            </div>

            <!-- How to Use Section -->
            <div class="product-section" id="how-to-use-section">
                <h2>How to Use</h2>
                <?php
                $how_to_use = deva_get_how_to_use($product->get_id());
                if (!empty($how_to_use)) :
                ?>
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
                                                <img src="<?php echo esc_url($step['step_image']); ?>" alt="Step <?php echo $step_number; ?>">
                                            </div>
                                        <?php endif; ?>
                                        <div class="step-text">
                                            <h4>Step <?php echo $step_number; ?></h4>
                                            <p><?php echo wp_kses_post($step['step_description']); ?></p>
                                        </div>
                                    <?php else : ?>
                                        <!-- Even steps: Step (x), text, image -->
                                        <div class="step-text">
                                            <h4>Step <?php echo $step_number; ?></h4>
                                            <p><?php echo wp_kses_post($step['step_description']); ?></p>
                                        </div>
                                        <?php if (!empty($step['step_image'])) : ?>
                                            <div class="step-image">
                                                <img src="<?php echo esc_url($step['step_image']); ?>" alt="Step <?php echo $step_number; ?>">
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <!-- Fallback to mock data -->
                    <p>Follow these simple steps for optimal results:</p>
                    <ol>
                        <li>Cleanse your face thoroughly with a gentle cleanser</li>
                        <li>Apply a small amount of the product to your fingertips</li>
                        <li>Gently massage into skin using upward circular motions</li>
                        <li>Allow the product to absorb for 2-3 minutes</li>
                        <li>Use twice daily, morning and evening, for best results</li>
                        <li>Always follow with SPF during daytime use</li>
                    </ol>
                <?php endif; ?>
            </div>

            <!-- Reviews Section -->
            <div class="product-section" id="reviews-section">
                <h2>Customer Reviews</h2>
                <?php echo do_shortcode('[deva_reviews]'); ?>
            </div>

            <!-- Questions Section -->
            <div class="product-section" id="questions-section">
                <h2>Frequently Asked Questions</h2>
                <p><strong>Q: Is this product suitable for sensitive skin?</strong></p>
                <p>A: Yes, our product is formulated with gentle, natural ingredients that are suitable for all skin types, including sensitive skin.</p>

                <p><strong>Q: How long does it take to see results?</strong></p>
                <p>A: Most customers notice improvements in skin texture and hydration within 7-14 days of regular use.</p>

                <p><strong>Q: Can I use this product with other skincare products?</strong></p>
                <p>A: Yes, this product works well with most skincare routines. Apply after cleansing and before moisturizer.</p>
            </div>
        </div>

        <!-- Related Products -->
        <div class="related-products">
            <h2 class="related-products-title">Related Products</h2>
            <?php echo do_shortcode('[deva_products limit="4" columns="4"]'); ?>
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
            // TODO: Implement add to cart functionality
            alert('Added ' + quantity + ' item(s) to cart!');
        }

        function buyNow() {
            const quantity = document.getElementById('quantity-input').value;
            // TODO: Implement buy now functionality
            alert('Proceeding to checkout with ' + quantity + ' item(s)!');
        }

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

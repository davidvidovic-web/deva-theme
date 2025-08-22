<?php
/**
 * DEVA Reviews Shortcode
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * DEVA Reviews Slider Shortcode
 */
function deva_reviews_shortcode($atts) {
    $atts = shortcode_atts(array(
        'class' => '',
        'show_count' => 6
    ), $atts);

    ob_start();

    // Mock review data
    $reviews = array(
        array(
            'stars' => 5,
            'review' => 'These essential oils have transformed my daily routine. The quality is exceptional and the scents are so pure and natural.',
            'person' => 'Sarah Johnson',
            'position' => 'Wellness Coach'
        ),
        array(
            'stars' => 5,
            'review' => 'I love the sustainable packaging and the products work amazing. My skin has never felt better!',
            'person' => 'Michael Chen',
            'position' => 'Environmental Scientist'
        ),
        array(
            'stars' => 4,
            'review' => 'Great natural products that actually work. The lavender oil helps me sleep so much better at night.',
            'person' => 'Emma Thompson',
            'position' => 'Teacher'
        ),
        array(
            'stars' => 5,
            'review' => 'Outstanding quality and customer service. These products are now a staple in our household.',
            'person' => 'David Rodriguez',
            'position' => 'Chef'
        ),
        array(
            'stars' => 5,
            'review' => 'The purity and effectiveness of these products is unmatched. Highly recommend to anyone seeking natural solutions.',
            'person' => 'Lisa Park',
            'position' => 'Naturopath'
        ),
        array(
            'stars' => 4,
            'review' => 'Beautiful packaging, amazing scents, and great results. These products are worth every penny.',
            'person' => 'James Wilson',
            'position' => 'Marketing Director'
        ),
        array(
            'stars' => 5,
            'review' => 'Finally found natural products that deliver on their promises. The tea tree oil is particularly impressive.',
            'person' => 'Rachel Green',
            'position' => 'Yoga Instructor'
        ),
        array(
            'stars' => 5,
            'review' => 'These products have helped me create a more natural lifestyle. The quality is consistently excellent.',
            'person' => 'Alex Turner',
            'position' => 'Fitness Trainer'
        )
    );

    // Limit reviews if specified
    $reviews = array_slice($reviews, 0, intval($atts['show_count']));

    ?>
    <section class="deva-reviews-section <?php echo esc_attr($atts['class']); ?>">
        <div class="elementor-container elementor-column-gap-default">
            <div class="reviews-content">
                <div class="reviews-header">
                    <div class="reviews-text">
                        <h2><?php _e('What Our Customers Love About Our Products', 'hello-elementor-child'); ?></h2>
                        <p><?php _e('Our customers appreciate the quality and purity of our natural products. Read their reviews and see how our carefully selected essentials make a difference.', 'hello-elementor-child'); ?></p>
                    </div>
                    <div class="reviews-buttons">
                        <a href="#" class="review-btn read-all-btn"><?php _e('Read all', 'hello-elementor-child'); ?></a>
                        <a href="#" class="review-btn write-review-btn"><?php _e('Write a Review', 'hello-elementor-child'); ?></a>
                    </div>
                </div>
                
                <div class="reviews-slider-container">
                    <div class="reviews-slider swiper">
                        <div class="swiper-wrapper">
                            <?php foreach ($reviews as $review) : ?>
                                <div class="review-card swiper-slide">
                                    <div class="review-stars">
                                        <?php for ($i = 1; $i <= 5; $i++) : ?>
                                            <span class="star <?php echo $i <= $review['stars'] ? 'filled' : 'empty'; ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                    <div class="review-text">
                                        "<?php echo esc_html($review['review']); ?>"
                                    </div>
                                    <div class="review-author">
                                        <div class="author-name"><?php echo esc_html($review['person']); ?></div>
                                        <div class="author-position"><?php echo esc_html($review['position']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php

    return ob_get_clean();
}

add_shortcode('deva_reviews', 'deva_reviews_shortcode');

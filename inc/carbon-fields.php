<?php
/**
 * Carbon Fields Configuration
 * 
 * @package HelloElementorChild
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

use Carbon_Fields\Container;
use Carbon_Fields\Field;

/**
 * Initialize Carbon Fields
 */
function deva_carbon_fields_init() {
    // Initialize Carbon Fields
    \Carbon_Fields\Carbon_Fields::boot();
}
add_action('after_setup_theme', 'deva_carbon_fields_init');

/**
 * Register custom fields for products
 */
function deva_register_product_fields() {
    // Register fields for both admin and frontend
    // Carbon Fields needs to be registered on frontend too for helper functions to work
    
    Container::make('post_meta', 'Product Details')
        ->where('post_type', '=', 'product')
        ->add_fields(array(
            
            // Key Benefits Repeater
            Field::make('complex', 'deva_key_benefits', 'Key Benefits')
                ->add_fields(array(
                    Field::make('text', 'benefit', 'Benefit')
                        ->set_width(100)
                        ->set_required(true)
                ))
                ->set_layout('tabbed-horizontal')
                ->set_help_text('Add key benefits for this product'),
            
            // Benefits Description
            Field::make('rich_text', 'deva_benefits_description', 'Benefits Description')
                ->set_help_text('General description about the benefits - supports rich text formatting'),
            
            // Key Ingredients Repeater
            Field::make('complex', 'deva_key_ingredients', 'Key Ingredients')
                ->add_fields(array(
                    Field::make('image', 'ingredient_image', 'Ingredient Image')
                        ->set_width(30)
                        ->set_value_type('url'),
                    Field::make('text', 'ingredient_title', 'Ingredient Title')
                        ->set_width(35)
                        ->set_required(true),
                    Field::make('textarea', 'ingredient_description', 'Ingredient Description')
                        ->set_width(35)
                        ->set_rows(3)
                ))
                ->set_layout('tabbed-horizontal')
                ->set_help_text('Add key ingredients for this product'),
            
            // How to Use Repeater
            Field::make('complex', 'deva_how_to_use', 'How to Use')
                ->add_fields(array(
                    Field::make('image', 'step_image', 'Step Image')
                        ->set_width(50)
                        ->set_value_type('url'),
                    Field::make('textarea', 'step_description', 'Step Description')
                        ->set_width(50)
                        ->set_rows(4)
                ))
                ->set_layout('tabbed-horizontal')
                ->set_help_text('Add instructions on how to use this product'),
        ))
        ->set_context('normal')
        ->set_priority('high');
}
add_action('carbon_fields_register_fields', 'deva_register_product_fields');

/**
 * Helper function to get key benefits
 */
function deva_get_key_benefits($product_id = null) {
    if (!$product_id) {
        global $post;
        $product_id = $post->ID;
    }
    
    // Check if Carbon Fields is available
    if (!function_exists('carbon_get_post_meta')) {
        return array();
    }
    
    $benefits = carbon_get_post_meta($product_id, 'deva_key_benefits');
    
    // Return empty array if no benefits or if it's not an array
    if (!is_array($benefits) || empty($benefits)) {
        return array();
    }
    
    return $benefits;
}

/**
 * Helper function to get benefits description
 */
function deva_get_benefits_description($product_id = null) {
    if (!$product_id) {
        global $post;
        $product_id = $post->ID;
    }
    
    // Check if Carbon Fields is available
    if (!function_exists('carbon_get_post_meta')) {
        return '';
    }
    
    $description = carbon_get_post_meta($product_id, 'deva_benefits_description');
    return $description ? $description : '';
}

/**
 * Helper function to get key ingredients
 */
function deva_get_key_ingredients($product_id = null) {
    if (!$product_id) {
        global $post;
        $product_id = $post->ID;
    }
    
    // Check if Carbon Fields is available
    if (!function_exists('carbon_get_post_meta')) {
        return array();
    }
    
    $ingredients = carbon_get_post_meta($product_id, 'deva_key_ingredients');
    
    // Return empty array if no ingredients or if it's not an array
    if (!is_array($ingredients) || empty($ingredients)) {
        return array();
    }
    
    return $ingredients;
}

/**
 * Helper function to get how to use steps
 */
function deva_get_how_to_use($product_id = null) {
    if (!$product_id) {
        global $post;
        $product_id = $post->ID;
    }
    
    // Check if Carbon Fields is available
    if (!function_exists('carbon_get_post_meta')) {
        return array();
    }
    
    $steps = carbon_get_post_meta($product_id, 'deva_how_to_use');
    
    // Return empty array if no steps or if it's not an array
    if (!is_array($steps) || empty($steps)) {
        return array();
    }
    
    return $steps;
}

/**
 * Helper function to get product FAQs
 */
function deva_get_product_faqs($product_id = null) {
    if (!$product_id) {
        global $post;
        $product_id = $post->ID;
    }
    
    // Check if Carbon Fields is available
    if (!function_exists('carbon_get_post_meta')) {
        return array();
    }
    
    $faqs = carbon_get_post_meta($product_id, 'deva_faqs');
    
    // Return empty array if no FAQs or if it's not an array
    if (!is_array($faqs) || empty($faqs)) {
        return array();
    }
    
    return $faqs;
}

<?php
/**
 * DEVA Search Modal Shortcode
 * 
 * @package HelloElementorChild
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Search Modal Shortcode
 */
function deva_search_modal_shortcode($atts)
{
    ob_start();
?>
    <div id="searchModal" class="search-modal" style="display: none;">
        <div class="search-modal-content">
            <span class="search-close">&times;</span>
            <div class="search-form">
                <?php echo get_product_search_form(); ?>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('deva_search_modal', 'deva_search_modal_shortcode');

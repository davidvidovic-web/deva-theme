/**
 * Carbon Fields Admin Script
 * Helps with dynamic header updates in repeater fields
 */

jQuery(document).ready(function($) {
    
    // Function to update repeater headers
    function updateRepeaterHeaders() {
        // Update Key Benefits headers
        $('.carbon-field-deva_key_benefits .carbon-subcontainer').each(function(index) {
            var $container = $(this);
            var $header = $container.find('.carbon-subcontainer-head .carbon-subcontainer-title');
            var $benefitField = $container.find('input[name*="[benefit]"]');
            
            if ($benefitField.length && $header.length) {
                var benefitValue = $benefitField.val();
                if (benefitValue && benefitValue.trim() !== '') {
                    $header.text(benefitValue.substring(0, 50) + (benefitValue.length > 50 ? '...' : ''));
                } else {
                    $header.text('Benefit ' + (index + 1));
                }
            }
        });
        
        // Update Key Ingredients headers
        $('.carbon-field-deva_key_ingredients .carbon-subcontainer').each(function(index) {
            var $container = $(this);
            var $header = $container.find('.carbon-subcontainer-head .carbon-subcontainer-title');
            var $titleField = $container.find('input[name*="[ingredient_title]"]');
            
            if ($titleField.length && $header.length) {
                var titleValue = $titleField.val();
                if (titleValue && titleValue.trim() !== '') {
                    $header.text(titleValue.substring(0, 50) + (titleValue.length > 50 ? '...' : ''));
                } else {
                    $header.text('Ingredient ' + (index + 1));
                }
            }
        });
        
        // Update How to Use headers
        $('.carbon-field-deva_how_to_use .carbon-subcontainer').each(function(index) {
            var $container = $(this);
            var $header = $container.find('.carbon-subcontainer-head .carbon-subcontainer-title');
            
            if ($header.length) {
                $header.text('Step ' + (index + 1));
            }
        });
    }
    
    // Update headers on page load
    setTimeout(updateRepeaterHeaders, 1000);
    
    // Update headers when typing
    $(document).on('input keyup', 'input[name*="[benefit]"], input[name*="[ingredient_title]"]', function() {
        setTimeout(updateRepeaterHeaders, 100);
    });
    
    // Update headers when adding/removing items
    $(document).on('click', '.carbon-btn-add, .carbon-btn-remove', function() {
        setTimeout(updateRepeaterHeaders, 500);
    });
    
    // Update headers when tabs are switched
    $(document).on('click', '.carbon-subcontainer-head', function() {
        setTimeout(updateRepeaterHeaders, 100);
    });
    
    // Also trigger on blur to catch paste operations
    $(document).on('blur', 'input[name*="[benefit]"], input[name*="[ingredient_title]"]', function() {
        setTimeout(updateRepeaterHeaders, 100);
    });
    
});

/**
 * Shore Booking Widget - Admin JavaScript
 */

jQuery(document).ready(function($) {
    // DOM elements
    var displayTypeSelect = $('#shbw_display_type');
    var standardSection = $('#standard_section');
    var floatingSection = $('#floating_section');
    var embeddedSection = $('#embedded_section');
    var standardPreview = $('#standard_preview');
    var floatingPreview = $('#floating_preview');
    var buttonTextInput = $('input[name="shbw_button_text"]');
    
    // Update standard button preview
    function updateStandardPreview() {
        var bgColor = $('input[name="shbw_standard_bg_color"]:checked').val();
        var textColor = $('input[name="shbw_standard_text_color"]:checked').val();
        
        if (bgColor && textColor) {
            standardPreview.css({
                'background-color': bgColor,
                'color': textColor
            });
        }
    }
    
    // Update floating button preview
    function updateFloatingPreview() {
        var bgColor = $('input[name="shbw_floating_bg_color"]:checked').val();
        var textColor = $('input[name="shbw_floating_text_color"]:checked').val();
        
        if (bgColor && textColor) {
            floatingPreview.css({
                'background-color': bgColor,
                'color': textColor
            });
        }
    }
    
    // Update button text in both previews
    function updateButtonText() {
        var text = buttonTextInput.val() || shbwSettings.defaultText;
        standardPreview.text(text);
        floatingPreview.text(text);
    }
    
    // Update floating button position
    function updateFloatingPosition() {
        var position = $('input[name="shbw_floating_position"]:checked').val();
        if (position) {
            floatingPreview.removeClass('position-left position-right').addClass('position-' + position);
        }
    }
    
    // Toggle sections based on display type
    function toggleSections() {
        var type = displayTypeSelect.val();
        
        // Hide all sections
        standardSection.hide();
        floatingSection.hide();
        embeddedSection.hide();
        
        // Show relevant section with animation
        if (type === 'standard_button') {
            standardSection.slideDown(300);
        } else if (type === 'floating_button') {
            floatingSection.slideDown(300);
        } else if (type === 'embedded') {
            embeddedSection.slideDown(300);
        }
    }
    
    // Initialize on page load
    toggleSections();
    updateStandardPreview();
    updateFloatingPreview();
    updateButtonText();
    updateFloatingPosition();
    
    // Event handlers
    displayTypeSelect.on('change', toggleSections);
    $('input[name="shbw_standard_bg_color"], input[name="shbw_standard_text_color"]').on('change', updateStandardPreview);
    $('input[name="shbw_floating_bg_color"], input[name="shbw_floating_text_color"]').on('change', updateFloatingPreview);
    $('input[name="shbw_floating_position"]').on('change', updateFloatingPosition);
    buttonTextInput.on('input', updateButtonText);
});
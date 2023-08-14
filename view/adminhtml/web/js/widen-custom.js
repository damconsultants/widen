require([
    'jquery',
    'select2'
], function ($) {
    jQuery(document).ready(function () {
        jQuery('#widen_property').select2();
        jQuery('#widen_property_image_role').select2();
        jQuery('#widen_property_alt_tax').select2();
        jQuery("#import_button").appendTo(".page-actions-buttons");
    });

    
});
/*!
 impleCode Shopping Cart
 Adds appropriate scripts to admin settings
 (c) 2020 https://implecode.com
 */

jQuery( document ).ready( function () {
    jQuery( '.chosen' ).chosen( { width: '200px' } );
    var selector = jQuery( '#payment_table .checkout_limit_selector_cart_' );
    selector.on('change', function () {
        jQuery( '.added_tax_exclude_row' ).remove();
        selector.find( ':selected' ).each( function () {
            var text = jQuery( this ).text();
            var label = admin_cart_object.tax_limit_label.replace( '[field_name]', text );
            selector.closest( 'tr' ).after( "<tr class='added_tax_exclude_row'><td><input type='hidden' name='product_currency_settings[tax_limit_labels][]' value='" + text + "'>" + label + "</td><td><input type='text' placeholder='" + admin_cart_object.tax_limit_placeholder + "' name='product_currency_settings[tax_limit_values][" + jQuery( this ).val() + "]'></td></tr>" );
        } );
    } );
} );
/*!
 impleCode Shopping Cart
 (c) 2020 Norbert Dreszer - http://implecode.com
 */

jQuery( document ).ready( function () {
    jQuery( '#country-selector' ).chosen( { width: "180px" } );
    jQuery( "#add-digital-product" ).click( function () {
        var count = parseInt( jQuery( ".manual_products_count" ).val(), 10 ) + 1;
        jQuery( ".manual_products_count" ).val( count );
        jQuery( "#order_product_list tr:last" ).after( '<tr><td>' + digital_order_edit_script_trans.digital_products_dropdown_action + '<input custom="' + count + '" style="display:none" type="text" id="manual_order_product_name_' + count + '" name="manual_order_product_name_' + count + '" value=""></td><td><input custom="' + count + '" type="number" min="1" step="1" class="product-quantity number_box" id="manual_order_product_quantity_' + count + '" name="manual_order_product_quantity_' + count + '" value="" /></td><td><input custom="' + count + '" type="text" id="manual_order_product_price_' + count + '" class="product-price number_box" name="manual_order_product_price_' + count + '" value="" /></td>' + digital_order_edit_script_trans.digital_order_manual_products_td_script_action + '<td><input readonly type="text" class="number_box" id="manual_order_product_summary_' + count + '" name="manual_order_product_summary_' + count + '" value="" /></td></tr>' );
        jQuery( "#new_manual_order_product_id" ).attr( "name", "manual_order_product_id_" + count );
        jQuery( "#new_manual_order_product_id" ).attr( "custom", count );
        jQuery( "#manual_order_product_quantity_" + count ).val( 1 );
        jQuery( "#new_manual_order_product_id option" ).each( function () {
            var product_id = jQuery( this ).val();
            jQuery( this ).attr( "name", "manual_order_product_id_" + count + "[" + product_id + "]" );
        } );
        jQuery( "#new_manual_order_product_id" ).attr( "id", "manual_order_product_id_" + count );
        disable_product_choose();
    } );
    jQuery( "#add-custom-digital-product" ).click( function () {
        var count = parseInt( jQuery( ".manual_products_count" ).val(), 10 ) + 1;
        jQuery( ".manual_products_count" ).val( count );
        jQuery( "#order_product_list tr:last" ).after( '<tr><td><input name="manual_order_product_name_' + count + '" value="" ></td><td><input custom="' + count + '" type="number" min="1" step="1" class="product-quantity number_box" id="manual_order_product_quantity_' + count + '" name="manual_order_product_quantity_' + count + '" value="" /></td><td><input custom="' + count + '" type="number" min="0.01" step="0.01" id="manual_order_product_price_' + count + '" class="product-price number_box" name="manual_order_product_price_' + count + '" value="" /></td>' + digital_order_edit_script_trans.digital_order_custom_manual_products_td_script_action + '<td><input  type="text" readonly class="number_box" id="manual_order_product_summary_' + count + '" name="manual_order_product_summary_' + count + '" value="" /></td></tr>' );
        jQuery( "#manual_order_product_id_" + count ).hide();
        jQuery( "#manual_order_product_name_" + count ).show();
    } );
    jQuery( "#order_product_list" ).on( 'change', '.digital_products_dropdown', function () {
        var product_id = jQuery( this ).val();
        var price = jQuery( "#product_price_" + product_id ).val();
        var field_number = jQuery( this ).attr( 'custom' );
        jQuery( "#manual_order_product_price_" + field_number ).val( price );
        var quantity = jQuery( "#manual_order_product_quantity_" + field_number ).val();
        var sum = Math.round( quantity * price * 100 ) / 100;
        sum = sum.toFixed( 2 );
        jQuery( "#manual_order_product_summary_" + field_number ).val( sum );
    } );
    jQuery( "#order_product_list" ).on( 'change', '.product-quantity', function () {
        var field_number = jQuery( this ).attr( 'custom' );
        var price = jQuery( "#manual_order_product_price_" + field_number ).val();
        var quantity = jQuery( "#manual_order_product_quantity_" + field_number ).val();
        var sum = Math.round( quantity * price * 100 ) / 100;
        sum = sum.toFixed( 2 );
        jQuery( "#manual_order_product_summary_" + field_number ).val( sum );
    } );
    jQuery( "#order_product_list" ).on( 'change', '.product-price', function () {
        var field_number = jQuery( this ).attr( 'custom' );
        var price = jQuery( "#manual_order_product_price_" + field_number ).val();
        var quantity = jQuery( "#manual_order_product_quantity_" + field_number ).val();
        var sum = Math.round( quantity * price * 100 ) / 100;
        sum = sum.toFixed( 2 );
        jQuery( "#manual_order_product_summary_" + field_number ).val( sum );
    } );
} );

function disable_product_choose() {
    jQuery( '.digital_products_dropdown option' ).removeAttr( "disabled", "disabled" );
    jQuery( ".digital_products_dropdown" ).each( function () {
        var name = 'id_' + jQuery( this ).val();
        jQuery( '.digital_products_dropdown .' + name ).attr( "disabled", "disabled" );
    } );
    jQuery( ":selected" ).removeAttr( "disabled", "disabled" );
}
/*!
 impleCode Shopping Cart
 (c) 2020 Norbert Dreszer - http://implecode.com
 */

jQuery( document ).ready( function () {
    jQuery( ".chosen" ).chosen( { width: "200px" } );

    jQuery( "#al_cart_variations" ).on( "click", ".add_variation:not(.disabled)", function () {
        var tr = jQuery( this ).closest( "tr" );
        var values = tr.find( "td.variation_values input:last-of-type" ).clone();
        var var_id = values.data( "var_num" );
        var new_var_lp = values.data( "var_lp" ) + 1;
        values = values.attr( "data-var_lp", new_var_lp );
        var prices = tr.find( "td.variation_prices input:last-of-type" ).clone();
        var shipping = tr.find( "td.variation_shipping input:last-of-type" ).clone();
        var edit = tr.find( "td.variation_details > div:last-child" ).clone();
        edit = edit.attr( "data-var_lp", new_var_lp );
        var media_file = edit.find( ".add_catalog_media" );
        media_file.attr( "id", 'button__' + var_id + '_product_image_' + new_var_lp );
        tr.find( "td.variation_values" ).append( values );
        tr.find( "td.variation_prices" ).append( prices );
        tr.find( "td.variation_shipping" ).append( shipping );
        tr.find( "td.variation_details" ).append( edit );
    } );

} );
/*!
 impleCode Admin scripts v1.0.0 - 2014-02-11
 Adds appropriate scripts to admin settings
 (c) 2014 Norbert Dreszer - https://implecode.com
 */


jQuery( document ).ready( function ( ) {
    jQuery( "input[name=\"container_width\"]" ).change( function ( ) {
        jQuery( "#container" ).css( "width", jQuery( this ).val( ) + "%" );
        jQuery( "#container" ).css( "margin", "0 auto" );
    } );
    jQuery( "input[name=\"container_padding\"]" ).change( function ( ) {
        jQuery( "#container #content" ).css( "padding", jQuery( this ).val( ) + "px" );
        jQuery( "#container" ).css( "box-sizing", "border-box" );
        jQuery( "#container #catalog_sidebar" ).css( "padding", jQuery( this ).val( ) + "px" );
    } );
    jQuery( "input[name=\"disable_breadcrumbs\"]" ).change( function ( ) {
        if ( jQuery( this ).is( ":checked" ) ) {
            jQuery( "p#breadcrumbs" ).hide( );
        } else {
            jQuery( "p#breadcrumbs" ).show( );
        }
    } );
    jQuery( "input[name=\"disable_name\"]" ).change( function ( ) {
        if ( jQuery( this ).is( ":checked" ) ) {
            jQuery( "h1.product-name" ).hide( );
        } else {
            jQuery( "h1.product-name" ).show( );
        }
    } );
    jQuery( "input[name=\"disable_image\"]" ).change( function ( ) {
        if ( jQuery( this ).is( ":checked" ) ) {
            jQuery( "div.product-image" ).hide( );
            jQuery( "#product_details" ).addClass( "no-image" );
        } else {
            jQuery( "div.product-image" ).show( );
            jQuery( "#product_details" ).removeClass( "no-image" );
        }
    } );
    jQuery( "input[name=\"disable_price\"]" ).change( function ( ) {
        if ( jQuery( this ).is( ":checked" ) ) {
            jQuery( "table.price-table, .price-container" ).hide( );
        } else {
            jQuery( "table.price-table, .price-container" ).show( );
        }
    } );
    jQuery( "input[name=\"disable_sku\"]" ).change( function ( ) {
        if ( jQuery( this ).is( ":checked" ) ) {
            jQuery( "table.sku-table" ).hide( );
        } else {
            jQuery( "table.sku-table" ).show( );
        }
    } );
    jQuery( "input[name=\"disable_shipping\"]" ).change( function ( ) {
        if ( jQuery( this ).is( ":checked" ) ) {
            jQuery( "table.shipping-table, .shipping-table-container" ).hide( );
        } else {
            jQuery( "table.shipping-table, .shipping-table-container" ).show( );
        }
    } );
    jQuery( "input[name=\"disable_attributes\"]" ).change( function ( ) {
        if ( jQuery( this ).is( ":checked" ) ) {
            jQuery( ".plain #product_features, .boxed h3[data-tab_id=\'product_features\']" ).hide( );
            jQuery( ".boxed h3[data-tab_id=\'product_description\']" ).click( );
        } else {
            jQuery( ".plain #product_features, .boxed h3[data-tab_id=\'product_features\']" ).show( );
        }
    } );
    jQuery( "input[name=\"default_sidebar\"]" ).change( function ( ) {
        if ( jQuery( this ).is( ":checked" ) ) {
            sidebar = jQuery( this ).val( );
            if ( sidebar == "left" ) {
                jQuery( "#catalog_sidebar" ).show( );
                jQuery( "#catalog_sidebar" ).css( "float", "left" );
                jQuery( ".product-catalog #content" ).css( "width", "70%" );
                jQuery( ".product-catalog #content" ).css( "float", "right" );
            } else if ( sidebar == "right" ) {
                jQuery( "#catalog_sidebar" ).show( );
                jQuery( "#catalog_sidebar" ).css( "float", "right" );
                jQuery( ".product-catalog #content" ).css( "width", "70%" );
                jQuery( ".product-catalog #content" ).css( "float", "left" );
            } else {
                jQuery( "#catalog_sidebar" ).hide( );
                jQuery( ".product-catalog #content" ).css( "width", "100%" );
                jQuery( ".product-catalog #content" ).css( "float", "none" );
            }
        }
    } );
    jQuery( ".start_section" ).click( function ( ) {
        jQuery( ".initial-description" ).hide();
        jQuery( this ).hide( );
        jQuery( "table.styling-adjustments tbody:first-child" ).show( );
        jQuery( "button.show_next_section" ).show( );
        jQuery( "#integration_wizard.fixed-box" ).addClass( 'opacity' );
    } );
    jQuery( ".switch_section" ).click( function ( ) {
        if ( jQuery( this ).hasClass( "show_third" ) ) {
            jQuery( ".integration-section" ).hide( );
            jQuery( ".section_3" ).show();
            jQuery( ".switch_section" ).show();
            return;
        }
        var current_section = jQuery( '.integration-section:visible' );
        var next = '';
        if ( jQuery( this ).hasClass( "show_next_section" ) ) {
            next = current_section.next( ".integration-section" );
        } else {
            next = current_section.prev( ".integration-section" );
        }
        if ( typeof next !== 'undefined' && next.length ) {
            current_section.hide( );
            next.show( );
        } else {
            jQuery( ".integration-section" ).hide( );
            jQuery( ".section_last" ).show( );
            jQuery( ".switch_section" ).hide( );
        }
        if ( jQuery( ".initial-description" ).is( ":visible" ) || jQuery( ".section_1" ).is( ":visible" ) || jQuery( ".section_last" ).is( ":visible" ) ) {
            jQuery( ".show_prev_section" ).hide();
        } else {
            jQuery( ".show_prev_section" ).show();
        }
        if ( jQuery( ".section_last" ).is( ":visible" ) ) {
            jQuery( ".show_third" ).show();
        }
    } );
    jQuery( "input[name='default_sidebar']" ).change( function () {
        if ( jQuery( this ).val() === 'left' || jQuery( this ).val() === 'right' ) {
            jQuery( ".integration-sidebar-info" ).show();
        } else {
            jQuery( ".integration-sidebar-info" ).hide();
        }
    } );
    jQuery( "a.integration-ok" ).click( function ( e ) {
        clicked = jQuery( this ).attr( "href" );
        e.preventDefault( );
        var breadcrumbs = 0;
        if ( jQuery( "input[name=\"disable_breadcrumbs\"]" ).is( ":checked" ) ) {
            breadcrumbs = 1;
        }
        var name = 0;
        if ( jQuery( "input[name=\"disable_name\"]" ).is( ":checked" ) ) {
            name = 1;
        }
        var image = 0;
        if ( jQuery( "input[name=\"disable_image\"]" ).is( ":checked" ) ) {
            image = 1;
        }
        var price = 0;
        if ( jQuery( "input[name=\"disable_price\"]" ).is( ":checked" ) ) {
            price = 1;
        }
        var sku = 0;
        if ( jQuery( "input[name=\"disable_sku\"]" ).is( ":checked" ) ) {
            sku = 1;
        }
        var shipping = 0;
        if ( jQuery( "input[name=\"disable_shipping\"]" ).is( ":checked" ) ) {
            shipping = 1;
        }
        var attributes = 0;
        if ( jQuery( "input[name=\"disable_attributes\"]" ).is( ":checked" ) ) {
            attributes = 1;
        }
        var default_sidebar = jQuery( "input[name=\"default_sidebar\"]:checked" ).val( );
        var data = {
            "action": "save_wizard",
            "container_width": jQuery( "input[name=\"container_width\"]" ).val( ),
            "container_padding": jQuery( "input[name=\"container_padding\"]" ).val( ),
            "container_bg": jQuery( "input[name=\"container_bg\"]" ).val( ),
            "container_text": jQuery( "input[name=\"container_text\"]" ).val( ),
            "disable_breadcrumbs": breadcrumbs,
            "disable_name": name,
            "disable_image": image,
            "disable_price": price,
            "disable_sku": sku,
            "disable_shipping": shipping,
            "disable_attributes": attributes,
            "default_sidebar": default_sidebar
        };
        jQuery( this ).attr( "disabled", true );
        jQuery( this ).css( "opacity", "0.5" );
        jQuery( ".ic_spinner" ).css( "display", "inline-block" );
        jQuery.post( product_object.ajaxurl, data, function ( response ) {
            window.location.href = clicked;
        } );
    } );
} );
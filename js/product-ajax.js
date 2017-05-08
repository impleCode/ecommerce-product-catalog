/*!
 impleCode Product Scripts v1.0.0 - 2014-02-11
 Manages product related scripts
 (c) 2014 Norbert Dreszer - https://implecode.com
 */
var ic_popstate = false;
jQuery( document ).ready( function ( ) {
    if ( jQuery( ".product-entry" ).length || jQuery( ".product-list" ).length !== 1 ) {
        return true;
    }
    var ic_submit_elements = jQuery.ic.applyFilters( "ic_ajax_submit_elements", 'form.ic_ajax, form.product_order, form#product_search_form, form.price-filter-form' );
    jQuery( "body" ).on( 'submit', ic_submit_elements, function ( e ) {
        if ( !ic_ajax_product_list_on_screen() ) {
            return true;
        }
        e.preventDefault();
        var form = jQuery( this );
        var form_clear = jQuery( '[name!=page]', this );
        var form_data = form.serialize();
        var url_replace = form_clear.serialize();
        if ( url_replace !== '' ) {
            var form_action = form.attr( 'action' );
            url_replace = form_action + "?" + url_replace;
        }
        ic_ajax_update_product_listing( form_data, url_replace );
    } );
    var ic_click_elements = "a.filter-url, .product-archive-nav a";
    jQuery( "body" ).on( 'click', ic_click_elements, function ( e ) {
        if ( !ic_ajax_product_list_on_screen() ) {
            return true;
        }
        e.preventDefault();
        var filter_url = jQuery( this ).attr( 'href' );
        if ( filter_url.indexOf( "?" ) !== -1 ) {
            var form_data = filter_url.substr( filter_url.indexOf( "?" ) + 1 );
        } else {
            var form_data = '';
        }
        var replace_url = false;
        var page = '';
        if ( jQuery( this ).closest( "li" ).data( 'page' ) !== undefined ) {
            page = jQuery( this ).closest( "li" ).data( 'page' );
        } else if ( jQuery( this ).data( 'page' ) !== undefined ) {
            page = jQuery( this ).data( 'page' );
        }
        if ( page !== '' ) {
            if ( form_data !== '' ) {
                form_data = form_data + "&page=" + page;
            } else {
                form_data = form_data + "page=" + page;
            }
            replace_url = jQuery( this ).attr( 'href' );
        }
        ic_ajax_update_product_listing( form_data, replace_url );
    } );
    if ( jQuery( ic_submit_elements ).length || jQuery( ic_click_elements ).length ) {
        ic_ajax_back_button_filters();
    }
} );

function ic_ajax_update_product_listing( form_data, url_replace ) {
    url_replace = ic_defaultFor( url_replace, false );
    if ( url_replace === false ) {
        url_replace = '?' + form_data;
    }
    if ( url_replace !== 'none' ) {
        window.history.pushState( { form_data: form_data }, document.title, url_replace );
        ic_popstate = true;
    }
    var query_vars = ic_ajax.query_vars;
    var shortcode = 0;
    if ( jQuery( ".product-list" ).data( "ic_ajax_query" ) ) {
        query_vars = JSON.stringify( jQuery( ".product-list" ).data( "ic_ajax_query" ) );
        shortcode = 1;
    }

    var data = {
        'action': 'ic_self_submit',
        'self_submit_data': form_data,
        'query_vars': query_vars,
        'request_url': '/' + ( location.pathname + location.search ).substr( 1 ),
        'ajax_elements': { },
        'shortcode': shortcode,
        'is_search': ic_ajax.is_search,
        'security': ic_ajax.nonce
    };
    if ( jQuery( ".product-sort-bar" ).length ) {
        data['ajax_elements']['product-sort-bar'] = 1;
    }
    if ( jQuery( ".ic_ajax" ).length ) {
        jQuery( ".ic_ajax" ).each( function ( ) {
            if ( jQuery( this ).data( "ic_ajax" ).length ) {
                var element_name = jQuery( this ).data( "ic_ajax" );
                if ( data['ajax_elements'][element_name] === undefined ) {
                    if ( data['ajax_elements']['product-sort-bar'] === undefined ) {
                        data['ajax_elements'][element_name] = 1;
                    } else if ( !jQuery( this ).closest( ".product-sort-bar" ).length ) {
                        data['ajax_elements'][element_name] = 1;
                    }
                }
            }
        } );
    }

    jQuery( ".product-list" ).css( 'opacity', '0.5' );
    jQuery.post( product_object.ajaxurl + '?' + form_data, data, function ( response ) {
        jQuery( ".reset-filters" ).remove( );
        response = jQuery.parseJSON( response );
        var listing = jQuery( response['product-listing'] ).not( "form, div.product-sort-bar, .reset-filters" );
        //jQuery( ".product-list" ).replaceWith( listing );
        jQuery( ".product-list" ).animate( { opacity: 0 }, 'fast', function () {
            listing = listing.hide();
            jQuery( ".product-list" ).replaceWith( listing );
            jQuery( ".product-list" ).fadeIn( "fast" );
            setTimeout( "modern_grid_font_size()", 0 );
        } );
        if ( jQuery( "#product_archive_nav" ).length ) {
            jQuery( "#product_archive_nav" ).replaceWith( response['product-pagination'] );
        } else if ( jQuery( "article#product_listing" ).length ) {
            jQuery( response['product-pagination'] ).insertAfter( "article#product_listing" );
        } else {
            jQuery( ".product-list" ).append( response['product-pagination'] );
        }
        jQuery.each( data['ajax_elements'], function ( element_name, element_enabled ) {
            if ( jQuery( "." + element_name ).length && ( response[element_name] !== undefined && response[element_name].length ) ) {
                var element_content = jQuery( response[element_name] );
                if ( !element_content.hasClass( element_name ) ) {
                    element_content = element_content.find( "." + element_name );
                }
                jQuery( "." + element_name ).replaceWith( element_content );
            }
        } );
        if ( response['remove_pagination'] ) {
            var main_url = ic_ajax.request_url;
            if ( main_url.indexOf( "?" ) !== -1 ) {
                main_url = main_url.substr( 0, main_url.indexOf( '?' ) );
            }
            var query = '';
            if ( url_replace.indexOf( "?" ) !== -1 ) {
                var query = url_replace.substr( url_replace.indexOf( "?" ) + 1 );
            }
            if ( query !== '' ) {
                query = '?' + query;
            }
            var replace_url = main_url + query;
            window.history.replaceState( { }, document.title, replace_url );
        }
    } );
}
function ic_ajax_back_button_filters() {
    jQuery( window ).unbind( 'popstate', ic_ajax_run_filters );
    jQuery( window ).on( 'popstate', ic_ajax_run_filters );
}

function ic_ajax_run_filters( e ) {
    var state = e.originalEvent.state;
    if ( state !== null ) {
        if ( state.form_data !== undefined ) {
            var form_data = state.form_data;
            if ( form_data.length ) {
                ic_ajax_update_product_listing( form_data, 'none' );
            } else {
                window.location.reload();
            }
        }
    } else if ( ic_popstate ) {
        location.reload();
    }
}

function ic_ajax_product_list_on_screen() {
    if ( jQuery( window ).scrollTop() + jQuery( window ).height() > jQuery( '.product-list' ).offset().top ) {
        return true;
    }
    return false;
}
/*!
 impleCode Product Scripts v1.0.0 - 2014-02-11
 Manages product related scripts
 (c) 2014 Norbert Dreszer - https://implecode.com
 */

jQuery( document ).ready( function ( $ ) {
    reponsive_product_catalog();
    initialize_ic_tabs();
    setTimeout( "modern_grid_font_size()", 0 );
    $( window ).resize( function () {
        if ( $( document.activeElement ).attr( 'type' ) === undefined ) {
            reponsive_product_catalog();
            redefine_ic_tabs();
            setTimeout( "modern_grid_font_size()", 0 );
        }
    } );

    if ( typeof colorbox == 'object' && $( ".a-product-image" ).length ) {
        $( ".a-product-image" ).colorbox( product_object.lightbox_settings );
    }

    jQuery( "body" ).on( 'change', ".ic_self_submit", function () {
        jQuery( this ).parent( "form" ).submit();
    } );

    jQuery( ".dismiss-empty-bar" ).click( function ( e ) {
        e.preventDefault();
        var data = {
            'action': 'hide_empty_bar_message'
        };
        jQuery.post( product_object.ajaxurl, data, function () {
            jQuery( "div.product-sort-bar" ).hide( 'slow' );
        } );
    } );

    jQuery.ic = {
        /**
         * Implement a WordPress-link Hook System for Javascript
         * TODO: Change 'tag' to 'args', allow number (priority), string (tag), object (priority+tag)
         */
        hooks: { action: { }, filter: { } },
        addAction: function ( action, callable, tag ) {
            jQuery.ic.addHook( 'action', action, callable, tag );
        },
        addFilter: function ( action, callable, tag ) {
            jQuery.ic.addHook( 'filter', action, callable, tag );
        },
        doAction: function ( action, args ) {
            jQuery.ic.doHook( 'action', action, null, args );
        },
        applyFilters: function ( action, value, args ) {
            return jQuery.ic.doHook( 'filter', action, value, args );
        },
        removeAction: function ( action, tag ) {
            jQuery.ic.removeHook( 'action', action, tag );
        },
        removeFilter: function ( action, tag ) {
            jQuery.ic.removeHook( 'filter', action, tag );
        },
        addHook: function ( hookType, action, callable, tag ) {
            if ( undefined == jQuery.ic.hooks[hookType][action] ) {
                jQuery.ic.hooks[hookType][action] = [ ];
            }
            var hooks = jQuery.ic.hooks[hookType][action];
            if ( undefined == tag ) {
                tag = action + '_' + hooks.length;
            }
            jQuery.ic.hooks[hookType][action].push( { tag: tag, callable: callable } );
        },
        doHook: function ( hookType, action, value, args ) {
            if ( undefined != jQuery.ic.hooks[hookType][action] ) {
                var hooks = jQuery.ic.hooks[hookType][action];
                for ( var i = 0; i < hooks.length; i++ ) {
                    if ( 'action' == hookType ) {
                        hooks[i].callable( args );
                    } else {
                        value = hooks[i].callable( value, args );
                    }
                }
            }
            if ( 'filter' == hookType ) {
                return value;
            }
        },
        removeHook: function ( hookType, action, tag ) {
            if ( undefined != jQuery.ic.hooks[hookType][action] ) {
                var hooks = jQuery.ic.hooks[hookType][action];
                for ( var i = hooks.length - 1; i >= 0; i-- ) {
                    if ( undefined == tag || tag == hooks[i].tag )
                        hooks.splice( i, 1 );
                }
            }
        }
    };
} );

function ic_switch_popstate_tabs( state ) {
    var hash = 'product_description';
    if ( window.location.hash !== "" ) {
        hash = window.location.hash;
        hash = hash.replace( "_tab", "" ).replace( "#", "" );
        var current_tab = jQuery( ".boxed .after-product-details h3[data-tab_id=" + hash + "]" );
        ic_enter_tab( hash, current_tab );
        //current_tab.trigger( "click" );
    } else {
        var current_tab = jQuery( ".boxed .after-product-details h3:first-of-type" );
        if ( !current_tab.hasClass( 'active' ) ) {
            set_default_ic_tab();
            history.replaceState( "", document.title, window.location.pathname + window.location.search );
        }
        //location.reload();
    }
}

function initialize_ic_tabs() {
    if ( jQuery( ".boxed" ).length ) {
        jQuery( window ).on( 'popstate', ic_switch_popstate_tabs );
        if ( jQuery( ".boxed" ).hasClass( "responsive" ) ) {
            ic_accordion();
        } else if ( jQuery( ".boxed" ).length ) {
            ic_tabs();
        }
        jQuery( document ).trigger( "ic_tabs_initialized" );
    }
}

function redefine_ic_tabs() {
    if ( jQuery( ".boxed .after-product-details" ).hasClass( "ic_accordion_container" ) ) {
        jQuery( ".boxed .after-product-details > div" ).each( function () {
            jQuery( this ).append( jQuery( this ).find( ".ic_accordion_content_container" ).html() );
            jQuery( this ).find( ".ic_accordion_content_container" ).remove();
        } );
        jQuery( ".after-product-details" ).removeClass( "ic_accordion_container" );
    } else if ( jQuery( ".boxed .after-product-details" ).hasClass( "ic_tabs_container" ) ) {
        jQuery( ".boxed .after-product-details .ic_tabs h3" ).each( function () {
            jQuery( this ).prepend( jQuery( this ).find( "a" ).text() );
            jQuery( this ).find( "a" ).remove();
            jQuery( this ).addClass( "catalog-header" );
            var tab_id = jQuery( this ).data( "tab_id" );
            jQuery( ".boxed .after-product-details #" + tab_id ).prepend( jQuery( this ) );
        } );
        jQuery( ".boxed .after-product-details > div" ).removeClass( "ic_tab_content" ).css( "display", "" );
        jQuery( ".boxed .after-product-details .ic_tabs" ).remove();
        jQuery( ".after-product-details" ).removeClass( "ic_tabs_container" );
    }
    initialize_ic_tabs();
}

function ic_accordion() {
    jQuery( ".boxed .after-product-details > div" ).each( function () {
        jQuery( this ).children().wrapAll( "<div class='ic_accordion_content_container' />" );
        jQuery( this ).find( ".catalog-header" ).prependTo( jQuery( this ) );
    } );
    ic_accordion_initial_hide();
    if ( window.location.hash !== "" ) {
        var hash = window.location.hash.replace( "_tab", "" ).replace( "#", "" );

        var current_tab = jQuery( ".boxed .after-product-details > #" + hash + " > .catalog-header" );
        if ( current_tab.length ) {
            current_tab.addClass( 'open' );
            jQuery( ".boxed .after-product-details > #" + hash + " .ic_accordion_content_container" ).show();
        } else {
            ic_open_default_accordion();
        }
    } else {
        ic_open_default_accordion();
    }
    jQuery( ".boxed.responsive .after-product-details .catalog-header" ).unbind( "click" );
    jQuery( ".boxed.responsive .after-product-details .catalog-header" ).click( function () {
        ic_accordion_initial_hide();
        if ( jQuery( this ).hasClass( "open" ) ) {
            history.pushState( { }, document.title, window.location.pathname );
            jQuery( this ).removeClass( "open" );
        } else {
            var clicked_tab_id = jQuery( this ).parent( "div" ).attr( "id" );
            window.location.hash = clicked_tab_id + "_tab";
            jQuery( ".boxed .after-product-details > div .catalog-header" ).removeClass( "open" );
            jQuery( this ).parent( "div" ).children().show();
            jQuery( this ).addClass( "open" );
        }
        //if ( false && !is_element_visible( jQuery( this ) ) ) {
        var page = jQuery( "html, body" );
        page.on( "scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove", function () {
            page.stop();
        } );
        page.animate( {
            scrollTop: jQuery( this ).offset().top
        }, 2000, function () {
            page.off( "scroll mousedown wheel DOMMouseScroll mousewheel keyup touchmove" );
        } );
        //     }
    } );
    jQuery( ".boxed .after-product-details" ).addClass( "ic_accordion_container" );
}

function ic_open_default_accordion() {
    jQuery( ".boxed .after-product-details > div:first-child .ic_accordion_content_container" ).show();
    jQuery( ".boxed .after-product-details > div:first-child .catalog-header" ).addClass( "open" );
}
function ic_accordion_initial_hide() {
    jQuery( ".boxed.responsive .after-product-details > div" ).each( function () {
        jQuery( this ).find( ".ic_accordion_content_container" ).hide();
        jQuery( this ).find( ".catalog-header" ).show();
    } );
}

function ic_tabs() {
    if ( !jQuery( ".boxed .after-product-details" ).hasClass( "ic_tabs_container" ) ) {
        var tabs = "<div class='ic_tabs'>";
        jQuery( ".boxed .after-product-details > div" ).each( function () {
            var ic_tab_content = jQuery( this );
            var ic_tab_id = ic_tab_content.attr( "id" );
            ic_tab_content.addClass( "ic_tab_content" );
            var h = ic_tab_content.find( "> h3.catalog-header" );
            if ( h.length ) {
                tabs = tabs + "<h3 data-tab_id='" + ic_tab_id + "'><a href='#" + ic_tab_id + "_tab'>" + h.html() + "</a></h3>";
                h.remove();
            }
        } );
        tabs = tabs + "</div>";
        jQuery( ".boxed .after-product-details" ).prepend( tabs );
        if ( window.location.hash !== "" ) {
            var hash = window.location.hash.replace( "_tab", "" ).replace( "#", "" );
            var current_tab = jQuery( ".boxed .after-product-details .ic_tabs > h3[data-tab_id='" + hash + "']" );
            if ( current_tab.length ) {
                ic_enter_tab( hash, current_tab );
            } else {
                set_default_ic_tab();
            }
        } else {
            set_default_ic_tab();
        }
        jQuery( ".boxed .after-product-details .ic_tabs > h3" ).unbind( "click" );
        jQuery( ".boxed .after-product-details .ic_tabs > h3" ).click( function ( e ) {
            e.preventDefault();
            var ic_tab_id = jQuery( this ).data( "tab_id" );
            ic_enter_tab( ic_tab_id, jQuery( this ) );
        } );
        jQuery( ".boxed .after-product-details" ).addClass( "ic_tabs_container" );
    }
}
function ic_enter_tab( ic_tab_id, object ) {
    if ( !jQuery( "#" + ic_tab_id ).hasClass( "active" ) ) {
        var hash = ic_tab_id + "_tab";
        if ( window.location.hash !== '#' + hash ) {
            window.location.hash = hash;
        }
        jQuery( ".boxed .after-product-details .ic_tab_content.active" ).hide();
        jQuery( "#" + ic_tab_id ).show();
        jQuery( ".boxed .after-product-details *" ).removeClass( "active" );
        jQuery( "#" + ic_tab_id ).addClass( "active" );
        object.addClass( "active" );
    }
}

function set_default_ic_tab() {
    jQuery( ".boxed .after-product-details .ic_tabs > h3" ).removeClass( "active" );
    jQuery( ".boxed .after-product-details > .ic_tab_content" ).removeClass( "active" ).hide();
    jQuery( ".boxed .after-product-details .ic_tabs > h3:first-child" ).addClass( "active" );
    jQuery( ".boxed .after-product-details > .ic_tab_content:first" ).addClass( "active" ).show();
}

function is_element_visible( element ) {
    var top_of_element = element.offset().top;
    var top_of_screen = jQuery( window ).scrollTop();
    if ( top_of_screen < top_of_element ) {
        return true;
    } else {
        return false;
    }
}

function reponsive_product_catalog() {
    // var list_width = jQuery( ".product-list" ).width();
    // var product_page_width = jQuery( "article.al_product" ).width();
    //if ( list_width < 600 ) {
    //    jQuery( ".product-list" ).addClass( "responsive" );
    // }
    // else {
    //     jQuery( ".product-list" ).removeClass( "responsive" );
    // }
    var body_width = jQuery( "body" ).width();

    if ( body_width < 1000 ) {
        jQuery( "article.al_product" ).addClass( "responsive" );
        jQuery( ".product-list" ).addClass( "responsive" );

    } else {
        jQuery( "article.al_product" ).removeClass( "responsive" );
        jQuery( ".product-list" ).removeClass( "responsive" );

    }
}

function modern_grid_font_size() {
    var fontSize = jQuery( ".modern-grid-element" ).width() * 0.08;
    if ( fontSize < 16 ) {
        jQuery( ".modern-grid-element h3" ).css( 'font-size', fontSize );
        jQuery( ".modern-grid-element .product-price" ).css( 'font-size', fontSize );
        fontSize = fontSize * 0.8;
        jQuery( ".modern-grid-element .product-attributes table" ).css( 'font-size', fontSize );
    }
}

function ic_defaultFor( arg, val ) {
    return typeof arg !== 'undefined' ? arg : val;
}
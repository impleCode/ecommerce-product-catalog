/*!
 impleCode Admin scripts v1.0.0 - 2014-02-11
 Adds appropriate scripts to admin settings
 (c) 2014 Norbert Dreszer - https://implecode.com
 */


jQuery( document ).ready( function () {
    var fixHelper = function ( e, ui ) {
        ui.children().each( function () {
            jQuery( this ).width( jQuery( this ).width() );
        } );
        return ui;
    };

    jQuery( '.sort-settings tbody' ).sortable( {
        items: "tr:not(.ic-not-sortable)",
        update: function () {
            jQuery( '.sort-settings tbody tr' ).each( function () {
                var r = jQuery( this ).index();
                jQuery( this ).find( 'td .attribute-label' ).attr( 'name', '_attribute-label' + r );
                jQuery( this ).find( 'td .attribute-value' ).attr( 'name', '_attribute' + r );
                jQuery( this ).find( 'td .attribute-unit' ).attr( 'name', '_attribute-unit' + r );
                r = r + 1;
                jQuery( this ).find( 'td .shipping-label' ).attr( 'name', '_shipping-label' + r );
                jQuery( this ).find( 'td .shipping-value' ).attr( 'name', '_shipping' + r );
            } );
        },
        helper: fixHelper,
        placeholder: 'sort-settings-placeholder'
    } );
//jQuery('.attributes .ui-sortable').height(jQuery('.attributes .ui-sortable').height());
//jQuery('.shipping .ui-sortable').height(jQuery('.shipping .ui-sortable').height());
    var fields = new Array( 'input[name="archive_multiple_settings\[category_archive_url\]"]', 'input[name="archive_multiple_settings\[seo_title\]"]', 'input[name="archive_multiple_settings\[seo_title_sep\]"]', 'input[name="archive_multiple_settings\[breadcrumbs_title\]"]', 'input[name="archive_multiple_settings\[enable_product_breadcrumbs\]"]', 'input[name="archive_multiple_settings\[product_listing_cats\]"]', 'input[name="archive_multiple_settings\[category_top_cats\]"]', 'input[name="archive_multiple_settings\[cat_template\]"]' );
    jQuery( 'input[name="archive_multiple_settings\[integration_type\]"]' ).change( function () {
        var disable = false;
        if ( jQuery( this ).is( ':checked' ) && jQuery( this ).val() == 'simple' ) {
            disable = true;
        }
        if ( jQuery( this ).is( ':checked' ) ) {
            if ( !disable ) {
                jQuery( ".al-box.warning" ).hide( 'slow' );
                jQuery( ".advanced_mode_settings" ).show( 'slow' );
            } else {
                jQuery( ".advanced_mode_settings" ).hide();
                jQuery( ".al-box.warning" ).show( 'slow' );
            }
            jQuery.each( fields, function ( index, element ) {
                jQuery( element ).prop( "disabled", disable );
            } );
        }
    } );
    jQuery( 'input[name="archive_multiple_settings\[integration_type\]"]' ).trigger( "change" );
    jQuery( ".overall-product-settings .submit .button-primary" ).click( function () {
        jQuery.each( fields, function ( index, element ) {
            jQuery( element ).prop( "disabled", false );
        } );
    } );

    jQuery( ".implecode-review .dashicons-no" ).click( function () {
        var data = {
            'action': 'hide_review_notice'
        };
        jQuery.post( ajaxurl, data, function ( response ) {
            jQuery( ".implecode-review" ).hide( "slow" );
        } );
    } );

    jQuery( ".implecode-translate .dashicons-no" ).click( function () {
        var data = {
            'action': 'hide_translate_notice'
        };
        jQuery.post( ajaxurl, data, function ( response ) {
            jQuery( ".implecode-translate" ).hide( "slow" );
        } );
    } );

    jQuery( ".implecode-review-thanks .dashicons-yes" ).click( function () {
        jQuery( ".implecode-review-thanks" ).hide( "slow" );
    } );

    jQuery( ".implecode-review a" ).click( function ( e ) {
        if ( jQuery( this ).hasClass( "button" ) ) {
            e.preventDefault();
            var data = {
                'action': 'hide_review_notice',
                'forever': 'yes'
            };
            jQuery.post( ajaxurl, data, function ( response ) {
                jQuery( ".implecode-review" ).hide( "slow" );
            } );
        } else {
            var data = {
                'action': 'hide_review_notice',
                'forever': 'yes'
            };
            jQuery.post( ajaxurl, data, function ( response ) {
                jQuery( ".implecode-review" ).hide( "slow" );
                jQuery( ".implecode-review-thanks" ).show( "slow" );
            } );
        }
    } );

    jQuery( '.implecode-review.is-dismissible' ).on( 'click', '.notice-dismiss', function ( event ) {
        var data = {
            'action': 'hide_review_notice',
            'forever': 'yes'
        };
        jQuery.post( ajaxurl, data );
    } );

    jQuery( function () {
        jQuery( ".setting-content input" ).tooltip( {
            position: {
                my: "left-48 top+37",
                at: "right+48 bottom-37",
                collision: "flip"
            },
            track: true,
            show: { delay: 200 }
        } );
    } );

    jQuery( ".add_catalog_media" ).click( function () {
        var clicked = jQuery( this );
        var upload_type = clicked.parent( "div" ).children( "#upload_type" ).val();
        wp.media.editor.send.attachment = function ( props, attachment ) {
            if ( upload_type == "url" ) {
                clicked.parent( "div" ).children( "#uploaded_image" ).val( attachment.url );
            } else {
                clicked.parent( "div" ).children( "#uploaded_image" ).val( attachment.id );
            }
            clicked.prev( "div" ).children( "img" ).attr( "src", attachment.url );
            clicked.prev( "div" ).children( "img" ).show();
            clicked.prev( "div" ).children( ".catalog-reset-image-button" ).show();
            clicked.prev( "div.implecode-admin-media-image.empty" ).removeClass( 'empty' );
            clicked.hide();
        }
        wp.media.editor.open( clicked );
        return false;
    } );

    jQuery( ".catalog-reset-image-button" ).click( function () {
        var clicked = jQuery( this );
        clicked.parent( "div" ).prev( "#uploaded_image" ).val( "" );
        src = jQuery( "#default" ).val();
        clicked.next( ".media-image" ).attr( "src", src );
        if ( src != '' ) {
            clicked.next( ".media-image" ).show();
        } else {
            clicked.next( ".media-image" ).hide();
        }
        clicked.parent( "div" ).next( ".add_catalog_media" ).show();
        clicked.parent( ".implecode-admin-media-image" ).addClass( 'empty' );
        clicked.hide();
    } );
    jQuery( "form#post" ).submit( function ( e ) {
        if ( jQuery( 'input[name="_price"]' ).length ) {
            if ( !jQuery( 'input[name="_price"]' ).valid() ) {
                e.preventDefault();
                jQuery( 'html, body' ).animate( {
                    scrollTop: jQuery( "#_price-error" ).offset().top - 200
                }, 100 );
            }
        }
    } );

    jQuery( ".ic_autocomplete" ).each( function () {
        var closing = false;
        var autocomplete = jQuery( this ).data( 'ic-autocomplete' );
        if ( autocomplete !== undefined ) {
            jQuery( this ).autocomplete( {
                source: autocomplete,
                minLength: 0,
                close: function ()
                {
                    // avoid double-pop-up issue
                    closing = true;
                    setTimeout( function () {
                        closing = false;
                    }, 300 );
                }
            } ).focus( function () {
                var value = jQuery( this ).val();
                if ( !closing && value == '' ) {
                    jQuery( this ).autocomplete( "search" );
                }
            } );

        }
    } );


} );

jQuery( document ).ready( function ( $ ) {
    $.ic = {
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
    }
} );

function reponsive_product_catalog() {
    var list_width = jQuery( ".product-list" ).width();
    var product_page_width = jQuery( "article.al_product" ).width();
    if ( list_width < 600 ) {
        jQuery( ".product-list" ).addClass( "responsive" );
    } else {
        jQuery( ".product-list" ).removeClass( "responsive" );
    }
    if ( product_page_width < 600 ) {
        jQuery( "article.al_product" ).addClass( "responsive" );
    } else {
        jQuery( "article.al_product" ).removeClass( "responsive" );
    }
}

function modern_grid_font_size() {
    var fontSize = jQuery( ".modern-grid-element" ).width() * 0.08; // 10% of container width
    if ( fontSize < 16 ) {
        jQuery( ".modern-grid-element h3" ).css( 'font-size', fontSize );
        jQuery( ".modern-grid-element .product-price" ).css( 'font-size', fontSize );
        fontSize = fontSize * 0.8;
        jQuery( ".modern-grid-element .product-attributes table" ).css( 'font-size', fontSize );
    }
}


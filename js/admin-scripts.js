/*!
 impleCode Admin
 Handles admin settings
 (c) 2021 impleCode - https://implecode.com
 */
if (typeof implecode === 'undefined') {
    var implecode = [];
}

jQuery(document).ready(function () {
    /* global ic_catalog */

    // Detect mobile/touch devices early
    var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
        ('ontouchstart' in window) ||
        (navigator.maxTouchPoints > 0);


    var fixHelper = function (e, ui) {
        ui.children().each(function () {
            jQuery(this).width(jQuery(this).width());
        });
        return ui;
    };

    var add_product_button = jQuery("body.post-type-al_product").find('.page-title-action:visible:first');
    if (ic_catalog.import_screen_url && add_product_button.length !== 0) {
        add_product_button.after('<a id="import-export-link-page" href="' + ic_catalog.import_screen_url + '" class="page-title-action">' + ic_catalog.import_export + '</a>');
    }


    jQuery(".ic_chosen").chosen({width: '200px', search_contains: true, allow_single_deselect: true});
    if (!isMobile) {
        jQuery('.sort-settings tbody').sortable({
            items: "tr:not(.ic-not-sortable)",
            update: function () {
                jQuery('.sort-settings tbody tr').each(function () {
                    var r = jQuery(this).index();
                    var label_input = jQuery(this).find('td .attribute-label');
                    var value_input = jQuery(this).find('td .attribute-value');
                    var unit_input = jQuery(this).find('td .attribute-unit');
                    var label_base_name = label_input.data('base_name');
                    var value_base_name = value_input.data('base_name');
                    var unit_base_name = unit_input.data('base_name');
                    label_input.attr('name', label_base_name + r);
                    value_input.attr('name', value_base_name + r);
                    unit_input.attr('name', unit_base_name + r);
                    r = r + 1;
                    jQuery(this).find('td .shipping-label').attr('name', '_shipping-label' + r);
                    jQuery(this).find('td .shipping-value').attr('name', '_shipping' + r);
                });
            },
            helper: fixHelper,
            placeholder: 'sort-settings-placeholder'
        });
    } else {
        jQuery('.sort-settings tbody .dragger').hide();
    }
//jQuery('.attributes .ui-sortable').height(jQuery('.attributes .ui-sortable').height());
//jQuery('.shipping .ui-sortable').height(jQuery('.shipping .ui-sortable').height());
    var fields_hide_simple = new Array('input[name="archive_multiple_settings\[category_archive_url\]"]', 'input[name="archive_multiple_settings\[seo_title\]"]', 'input[name="archive_multiple_settings\[seo_title_sep\]"]', 'input[name="archive_multiple_settings\[breadcrumbs_title\]"]', 'input[name="archive_multiple_settings\[enable_product_breadcrumbs\]"]', 'input[name="archive_multiple_settings\[product_listing_cats\]"]', 'input[name="archive_multiple_settings\[category_top_cats\]"]', 'input[name="archive_multiple_settings\[cat_template\]"]');
    var fields_hide_theme = new Array('input[name="archive_multiple_settings\[product_listing_cats\]"]', 'input[name="archive_multiple_settings\[category_top_cats\]"]', 'input[name="archive_multiple_settings\[cat_template\]"]', 'input[name="archive_multiple_settings\[cat_image_disabled\]"]');
    jQuery('.ic_radio_td').on('change', ' .integration-mode-selection', function () {
        var disable = false;
        var is_checked = jQuery(this).is(':checked');
        var val = jQuery(this).val();
        if (is_checked && val === 'simple') {
            disable = 'simple';
        } else if (is_checked && val === 'theme') {
            disable = 'theme';
        }
        if (is_checked) {
            if (!disable) {
                jQuery('.simple_mode_settings').hide('slow');
                jQuery('.theme_mode_settings').hide('slow');
                jQuery('.advanced_mode_settings').show();
                jQuery('.advanced_mode_settings_inline').show();
                jQuery.each(fields_hide_simple, function (index, element) {
                    //jQuery( element ).prop( "disabled", disable );
                    jQuery(element).closest('tr').show();
                });
            } else if (disable === 'simple') {
                jQuery('.advanced_mode_settings').hide();
                //jQuery( ".advanced_mode_settings_inline" ).hide();
                jQuery('.theme_mode_settings').hide('slow');
                jQuery('.simple_mode_settings').show('slow');
                jQuery.each(fields_hide_simple, function (index, element) {
                    //jQuery( element ).prop( "disabled", disable );
                    //jQuery( element ).closest( "tr" ).hide();
                });
            } else if (disable === 'theme') {
                jQuery('.advanced_mode_settings').hide();
                jQuery('.simple_mode_settings').hide('slow');
                jQuery('.theme_mode_settings').show('slow');
                jQuery('.advanced_mode_settings_inline').show();
                jQuery.each(fields_hide_simple, function (index, element) {
                    //jQuery( element ).prop( "disabled", disable );
                    jQuery(element).closest('tr').show();
                });
                jQuery.each(fields_hide_theme, function (index, element) {
                    //jQuery( element ).prop( "disabled", disable );
                    jQuery(element).closest('tr').hide();
                });
            }
        }
    });
    jQuery('.integration-mode-selection').trigger('change');
    /*
     jQuery( ".overall-product-settings .submit .button-primary" ).click( function () {
     jQuery.each( fields, function ( index, element ) {
     jQuery( element ).prop( "disabled", false );
     } );
     } );
     */
    /*
        jQuery('.implecode-review').on('click', '.dashicons-no', function () {
            var data = {
                'action': 'hide_review_notice',
                'nonce': ic_catalog.nonce
            };
            jQuery.post(ajaxurl, data, function () {
                jQuery('.implecode-review').hide('slow');
            });
        });

     */

    jQuery('.implecode-translate').on('click', ' .dashicons-no', function () {
        var data = {
            'action': 'hide_translate_notice',
            'nonce': ic_catalog.nonce
        };
        jQuery.post(ajaxurl, data, function () {
            jQuery('.implecode-translate').hide('slow');
        });
    });

    jQuery('.implecode-review-thanks').on('click', '.dashicons-yes', function () {
        jQuery(".implecode-review-thanks").hide("slow");
    });

    jQuery('.implecode-review').on('click', 'a.ic-user-dismiss', function (e) {
        jQuery('.implecode-review').hide('slow');
        jQuery('.implecode-review-thanks').show('slow');
    });
    /*
        jQuery('.implecode-review.is-dismissible').on('click', '.notice-dismiss', function (event) {
            var data = {
                'action': 'hide_review_notice',
                'nonce': ic_catalog.nonce
            };
            jQuery.post(ajaxurl, data);
        });
    */
    jQuery('.ic-notice.is-dismissible').on('click', '.notice-dismiss, .ic-user-dismiss', function (event) {
        var container = jQuery(this).closest(".is-dismissible");
        var element = container.data("ic_dismissible");
        if (element !== undefined) {
            var type = 'global';
            if (jQuery(this).hasClass('ic-user-dismiss')) {
                type = 'user';
                container.hide('slow');
            } else {
                type = container.data("ic_dismissible_type");
            }
            if (type === undefined) {
                type = 'global';
            }
            implecode.hide_notice(element, type);
        }
    });

    jQuery('.al-box').on('click', '.notice-dismiss', function () {
        var container = jQuery(this).closest(".al-box");
        container.css("opacity", "0.5");
        var hash = container.data('hash');
        if (hash !== undefined) {
            var data = {
                'action': 'ic_ajax_hide_message',
                'hash': hash,
                'nonce': ic_catalog.nonce
            };
            jQuery.post(ajaxurl, data, function () {
                container.hide("slow");
            });
        }
    });

    jQuery(function () {
        jQuery(".setting-content input, span.ic_tip").tooltip({
            position: {
                my: "left-48 top+37",
                at: "right+48 bottom-37",
                collision: "flip",
            },
            track: true,
            tooltipClass: "ui-ic-tooltip"
        });
    });

    jQuery("body").on("click", ".add_catalog_media", function () {
        var clicked = jQuery(this);
        var clicked_container = clicked.parent("div");
        var clicked_prev = clicked.prev("div");
        var upload_type = clicked_container.children("#upload_type").val();
        wp.media.editor.send.attachment = function (props, attachment) {
            if (upload_type == 'url') {
                clicked_container.children('.uploaded_image').val(attachment.url);
            } else {
                clicked_container.children('.uploaded_image').val(attachment.id);
            }
            clicked_container.children('.uploaded_image').trigger('change');
            clicked_prev.children("img").attr("src", attachment.url);
            clicked_prev.children("img").show();
            clicked_prev.children(".catalog-reset-image-button").show();
            clicked.prev("div.implecode-admin-media-image.empty").removeClass('empty');
            clicked.hide();
            clicked.closest(".custom-uploader").addClass("active-image");
        };
        wp.media.editor.open(clicked);
        return false;
    });

    jQuery('.implecode-admin-media-image, .blocks-widgets-container').on('click', '.catalog-reset-image-button', function () {
        var clicked = jQuery(this);
        var clicked_container = clicked.closest('.custom-uploader');
        clicked_container.find('.uploaded_image').val('');
        var src = clicked_container.find('.default').val();
        clicked_container.find('.media-image').attr('src', src);
        if (src !== '') {
            clicked_container.find('.media-image').show();
        } else {
            clicked_container.find('.media-image').hide();
        }
        clicked.parent('div').next('.add_catalog_media').show();
        clicked.parent('.implecode-admin-media-image').addClass('empty');
        clicked.hide();
        clicked.closest('.custom-uploader').removeClass('active-image');
    });
    jQuery('.wp-admin').on('submit', 'form#post', function (e) {
        validate_price(e);
    });
    jQuery('.wp-admin').on('click', '.editor-post-publish-button', function (e) {
        validate_price(e);
    });

    function validate_price(e) {
        if (jQuery('input[name="_price"]').length) {
            if (!jQuery('input[name="_price"]').valid()) {
                e.preventDefault();
                jQuery('html, body').animate({
                    scrollTop: jQuery("#_price-error").offset().top - 200
                }, 100);
            }
        }
    }

    ic_autocomplete_setup();
    jQuery('form').on('change', '.ic-input', function () {
        var input = jQuery(this);
        var value = input.val();
        var input_name = input.attr('name');
        var related_input = jQuery('form').find('[ic-show-when="' + input_name + '"]');
        if (related_input.length > 0) {
            related_input.each(function () {
                if (value) {
                    related_input.show();
                } else {
                    related_input.hide();
                }
            });
        }
    });
    jQuery('.ic-input').trigger('change');
});

jQuery(window).on('load', function () {
    setTimeout(function () {
        if (jQuery('#al_product_short_desc').length && jQuery('#al_product_short_desc .html-active').length === 0 && jQuery('#al_product_short_desc .inside .mce-tinymce').length === 0 && typeof tinymce !== 'undefined') {
            if (typeof tinyMCEPreInit.mceInit['excerpt'] !== 'undefined' && typeof tinyMCEPreInit.mceInit['excerpt'].wp_skip_init !== 'undefined' && tinyMCEPreInit.mceInit['excerpt'].wp_skip_init) {
                tinymce.init(tinyMCEPreInit.mceInit['excerpt']);
            }
        }
    }, 1000);
});

jQuery(document).ready(function ($) {
    $.ic = {
        /**
         * Implement a WordPress-link Hook System for Javascript
         * TODO: Change 'tag' to 'args', allow number (priority), string (tag), object (priority+tag)
         */
        hooks: {action: {}, filter: {}},
        addAction: function (action, callable, tag) {
            jQuery.ic.addHook('action', action, callable, tag);
        },
        addFilter: function (action, callable, tag) {
            jQuery.ic.addHook('filter', action, callable, tag);
        },
        doAction: function (action, args) {
            jQuery.ic.doHook('action', action, null, args);
        },
        applyFilters: function (action, value, args) {
            return jQuery.ic.doHook('filter', action, value, args);
        },
        removeAction: function (action, tag) {
            jQuery.ic.removeHook('action', action, tag);
        },
        removeFilter: function (action, tag) {
            jQuery.ic.removeHook('filter', action, tag);
        },
        addHook: function (hookType, action, callable, tag) {
            if (undefined == jQuery.ic.hooks[hookType][action]) {
                jQuery.ic.hooks[hookType][action] = [];
            }
            var hooks = jQuery.ic.hooks[hookType][action];
            if (undefined == tag) {
                tag = action + '_' + hooks.length;
            }
            jQuery.ic.hooks[hookType][action].push({tag: tag, callable: callable});
        },
        doHook: function (hookType, action, value, args) {
            if (undefined != jQuery.ic.hooks[hookType][action]) {
                var hooks = jQuery.ic.hooks[hookType][action];
                for (var i = 0; i < hooks.length; i++) {
                    if ('action' == hookType) {
                        hooks[i].callable(args);
                    } else {
                        value = hooks[i].callable(value, args);
                    }
                }
            }
            if ('filter' == hookType) {
                return value;
            }
        },
        removeHook: function (hookType, action, tag) {
            if (undefined != jQuery.ic.hooks[hookType][action]) {
                var hooks = jQuery.ic.hooks[hookType][action];
                for (var i = hooks.length - 1; i >= 0; i--) {
                    if (undefined == tag || tag == hooks[i].tag)
                        hooks.splice(i, 1);
                }
            }
        }
    }
});

function ic_autocomplete_setup() {
    jQuery(".ic_autocomplete").each(function () {
        var closing = false;
        var field = jQuery(this);
        var autocomplete = field.data('ic-autocomplete');
        if (autocomplete !== undefined && !field.hasClass('ui-autocomplete-input')) {
            field.autocomplete({
                source: autocomplete,
                minLength: 0,
                change: function (event, ui) {
                    field.trigger("change");
                },
                close: function () {
                    // avoid double-pop-up issue
                    closing = true;
                    setTimeout(function () {
                        closing = false;
                    }, 300);
                }
            }).on('focus', function () {
                var value = jQuery(this).val();
                if (!closing && value == '') {
                    jQuery(this).autocomplete("search");
                }
            });

        }
    });
}

if (typeof implecode.disable_body === 'undefined') {
    implecode.disable_body = function () {
        jQuery('body').addClass('ic-disabled-body');
    };
}
if (typeof implecode.enable_body === 'undefined') {
    implecode.enable_body = function () {
        jQuery('body').removeClass('ic-disabled-body');
    };
}

if (typeof implecode.disable_container === 'undefined') {
    implecode.disable_container = function (container) {
        container.addClass('ic-disabled-container');
    };
}

if (typeof implecode.enable_container === 'undefined') {
    implecode.enable_container = function (container) {
        container.removeClass('ic-disabled-container');
    };
}

if (typeof implecode.hide_notice === 'undefined') {
    implecode.hide_notice = function (element, type) {
        var data = {
            'action': 'hide_ic_notice',
            'element': element,
            'type': type,
            'nonce': ic_catalog.nonce
        };
        console.log(data);
        jQuery.post(ajaxurl, data);
    };
}

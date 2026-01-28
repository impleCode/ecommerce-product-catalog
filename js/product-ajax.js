/*!
 impleCode Product AJAX Scripts
 Manages product ajax related scripts
 (c) 2021 impleCode - https://implecode.com
 */
var ic_popstate = false;
var ic_product_list = '';
var ic_filters_bar = '';
jQuery(document).ready(function () {
    /* globals product_object, ic_ajax */
    ic_product_list = jQuery('.product-list').first();
    ic_filters_bar = jQuery('.product-sort-bar').first();
    if ( /*jQuery( ".product-entry" ).length || */jQuery('.product-list').length !== 1 && ic_filters_bar.length === 0) {
        return true;
    }
    var ic_submit_elements = jQuery.ic.applyFilters('ic_ajax_submit_elements', 'form.ic_ajax, form.product_order, .product-sort-bar form#product_search_form, form.price-filter-form');
    jQuery('body').on('change', '.product-search-box', function () {
        var search_key = jQuery(this).val();
        jQuery('.ic-search-keyword').text(search_key);
        jQuery('.product-search-box').val(search_key);

    });
    jQuery('body').on('submit', ic_submit_elements, function (e) {
        var form = jQuery(this);

        var form_sort_bar = form.closest('.product-sort-bar');
        if (form_sort_bar.length) {
            ic_filters_bar = form_sort_bar;
            ic_product_list = form_sort_bar.nextAll('.product-list').first();
        }
        if (!ic_ajax_product_list_on_screen()) {

            return true;
        }
        e.preventDefault();
        var form_clear = '';
        form_clear = jQuery('[name!=page]', this);
        var form_data = form.serialize();
        var url_replace = '';
        if (form.data('ic_ajax') === 'page') {
            url_replace = url_replace + 'page/' + form.find('[name=page]').val();
        }
        var serialized_form = form_clear.serialize();
        if (serialized_form) {
            url_replace = url_replace + '?' + serialized_form;
        }
        var form_action = form.attr('action');
        if (url_replace !== '') {
            if (form_action.slice(-1) !== '/' && form.data('ic_ajax') === 'page') {
                form_action = form_action + '/';
            }
            url_replace = form_action + url_replace;
        } else {
            url_replace = form_action;
        }
        var change_only = '';
        if (form.hasClass('product_order')) {
            change_only = 'product_order';
        }
        ic_ajax_update_product_listing(form_data, url_replace, change_only);
    });
    var ic_click_elements = 'a.filter-url, .product-archive-nav li:not(.active) a, a.price-filter-link, a.ic-remove-active-filter';
    jQuery('body').on('click', ic_click_elements, function (e) {
        var filter = jQuery(this);
        var form_sort_bar = filter.closest('.product-sort-bar');
        let pagination = filter.closest('.product-archive-nav');
        if (form_sort_bar.length > 0) {
            ic_filters_bar = form_sort_bar;
            ic_product_list = form_sort_bar.nextAll('.product-list').first();
        } else if (pagination.length > 0) {
            let pagination_product_list = pagination.prev('.product-list');
            if (pagination_product_list.length > 0) {
                ic_product_list = pagination_product_list.first();
            }
        }
        if (!ic_ajax_product_list_on_screen()) {
            return true;
        }
        e.preventDefault();
        var filter_url = filter.attr('href');
        var form_data = '';
        if (filter_url.indexOf('?') !== -1) {
            form_data = filter_url.substr(filter_url.indexOf("?") + 1);
        }
        var replace_url = false;
        var page = '';
        if (filter.closest('li').data('page') !== undefined) {
            page = filter.closest('li').data('page');
        } else if (filter.data('page') !== undefined) {
            page = filter.data('page');
        }
        if (page !== '') {
            if (form_data !== '') {
                form_data = form_data + '&page=' + page;
            } else {
                form_data = form_data + 'page=' + page;
            }
            replace_url = filter.attr('href');
        }
        var scroll = false;
        if (pagination.length > 0) {
            scroll = true;
        }
        ic_ajax_update_product_listing(form_data, replace_url, '', scroll);
    });
    if (jQuery(ic_submit_elements).length || jQuery(ic_click_elements).length) {
        ic_ajax_back_button_filters();
    }
});

function ic_ajax_update_product_listing(form_data, url_replace, change_only, scroll) {
    /* global ic_defaultFor */
    change_only = typeof change_only !== 'undefined' ? change_only : '';
    scroll = typeof scroll !== 'undefined' ? scroll : '';
    url_replace = ic_defaultFor(url_replace, false);
    if (url_replace === false) {
        url_replace = '?' + form_data;
    }
    if (url_replace !== 'none') {
        window.history.pushState({form_data: form_data}, document.title, url_replace);
        ic_popstate = true;
    }
    if (ic_product_list === '') {
        ic_product_list = jQuery('.product-list').first();
    }
    if (ic_filters_bar === '') {
        ic_filters_bar = jQuery('.product-sort-bar').first();
    }

    var query_vars = ic_ajax.query_vars;
    var shortcode = 0;
    if (ic_product_list.data('ic_ajax_query')) {
        query_vars = JSON.stringify(ic_product_list.data('ic_ajax_query'));
        shortcode = 1;
    }

    var data = {
        'action': 'ic_self_submit',
        'self_submit_data': form_data,
        'query_vars': query_vars,
        //'request_url': '/' + ( location.pathname + location.search ).substr( 1 ),
        'request_url': ic_ajax.request_url,
        'ajax_elements': {},
        'ic_shortcode': shortcode,
        'is_search': ic_ajax.is_search,
        'security': ic_ajax.nonce
    };

    jQuery.ic.doAction('ic_self_submit_before');
    var ic_ajax_elements = jQuery('.ic_ajax').not('.product-sort-bar .ic_ajax');
    if (ic_filters_bar.length && change_only === '') {
        data['ajax_elements']['product-sort-bar'] = 1;
        ic_ajax_elements.add(ic_filters_bar.find('.ic_ajax'));
    }
    if (ic_ajax_elements.length) {
        ic_ajax_elements.each(function () {
            if (jQuery(this).data('ic_ajax').length) {
                var element_name = jQuery(this).data('ic_ajax');
                if (change_only === '' || change_only === element_name) {
                    var element_data = jQuery(this).data('ic_ajax_data');
                    if (!element_data) {
                        element_data = 1;
                    }
                    if (data['ajax_elements'][element_name] === undefined) {
                        if (data['ajax_elements']['product-sort-bar'] === undefined) {
                            data['ajax_elements'][element_name] = element_data;
                        } else if (!jQuery(this).closest('.product-sort-bar').length) {
                            data['ajax_elements'][element_name] = element_data;
                        }
                    }
                }
            }
        });
    }
    ic_product_list.css('opacity', '0.5');
    jQuery.post(product_object.ajaxurl, data)
        .done(function (response) {
            /* globals modern_grid_font_size, is_element_visible */
            jQuery('.reset-filters').remove();
            try {
                response = JSON.parse(response);
            } catch (e) {
                location.reload();
                return false;
            }
            if (!response) {
                location.reload();
                return false;
            }
            if (response['redirect']) {
                var domain = (new URL(response['redirect']));
                if (domain.host === window.location.host) {
                    window.location.replace(response['redirect']);
                } else {
                    location.reload();
                }
                return false;
            }
            var listing = jQuery(response['product-listing']).not('form, div.product-sort-bar, .reset-filters');
            ic_product_list.animate({opacity: 0}, 'fast', function () {
                listing = listing.hide();
                jQuery('.product-list').removeClass('active-product-listing');
                listing.addClass('active-product-listing');
                ic_product_list.replaceWith(listing);
                ic_product_list = jQuery('.product-list.active-product-listing');
                ic_product_list.fadeIn('fast');
                setTimeout(modern_grid_font_size(), 0);

                if (scroll && !is_element_visible(ic_product_list.find('div:first-child'))) {
                    if (ic_product_list.find('div').length) {
                        jQuery('html, body').animate({
                            scrollTop: ic_product_list.find('div').offset().top - 100
                        }, 'slow');
                    }
                }

                if (jQuery('.product-archive-nav').length) {
                    jQuery('.product-archive-nav').replaceWith(response['product-pagination']);
                } else if (jQuery('div#product_sidebar').length) {
                    jQuery(response['product-pagination']).insertAfter('div#product_sidebar');
                } else if (jQuery('article#product_listing').length) {
                    jQuery(response['product-pagination']).insertAfter('article#product_listing');
                } else {
                    ic_product_list.after(response['product-pagination']);
                }
                jQuery.each(data['ajax_elements'], function (element_name, element_enabled) {
                    //var this_ajax_element = jQuery('.' + element_name).not('.product-sort-bar .' + element_name);
                    var this_ajax_element = jQuery();
                    if (element_name !== 'product-sort-bar') {
                        this_ajax_element = jQuery(':not(.product-sort-bar ) .' + element_name);
                    }
                    if (ic_filters_bar.length) {
                        if (element_name === 'product-sort-bar') {
                            this_ajax_element = ic_filters_bar;
                        } else {
                            this_ajax_element.add(ic_filters_bar.find('.' + element_name));
                        }
                    }

                    if (this_ajax_element.length && (response[element_name] !== undefined)) {
                        var element_content = jQuery(response[element_name]);
                        var hide_filter = false;
                        if (element_content.hasClass('ic-empty-filter')) {
                            hide_filter = true;
                        }

                        if (!element_content.hasClass(element_name)) {
                            element_content = element_content.find('.' + element_name);
                        }
                        if (element_content.length) {
                            this_ajax_element.replaceWith(element_content);
                        } else {
                            this_ajax_element.html('');
                            hide_filter = true;
                        }
                        if (hide_filter) {
                            this_ajax_element.closest('.widget').addClass('ic-empty-filter');
                            element_content.closest('.widget').addClass('ic-empty-filter');
                        } else {
                            this_ajax_element.closest('.widget').removeClass('ic-empty-filter');
                            element_content.closest('.widget').removeClass('ic-empty-filter');
                        }
                        this_ajax_element.trigger('reload');
                        if (element_name === 'product-sort-bar') {
                            ic_filters_bar = jQuery('.product-sort-bar').first();
                        }
                    }
                });
                if (response['remove_pagination']) {
                    var main_url = ic_ajax.request_url;
                    if (main_url.indexOf('?') !== -1) {
                        main_url = main_url.substr(0, main_url.indexOf('?'));
                    }
                    var query = '';
                    if (url_replace.indexOf('?') !== -1) {
                        query = url_replace.substr(url_replace.indexOf('?') + 1);
                    }
                    if (query !== '') {
                        query = '?' + query;
                    }
                    var replace_url = main_url + query;
                    window.history.replaceState({}, document.title, replace_url);
                }
                jQuery.ic.doAction('ic_self_submit');
            });
        })
        .fail(function () {
            location.reload();
        });
}

function ic_ajax_back_button_filters() {
    jQuery(window).off('popstate', ic_ajax_run_filters);
    jQuery(window).on('popstate', ic_ajax_run_filters);
}

function ic_ajax_run_filters(e) {
    var state = e.originalEvent.state;
    if (state !== null) {
        if (state.form_data !== undefined) {
            var form_data = state.form_data;
            if (form_data.length) {
                ic_ajax_update_product_listing(form_data, 'none');
            } else {
                window.location.reload();
            }
        }
    } else if (ic_popstate) {
        location.reload();
    }
}

function ic_ajax_product_list_on_screen() {
    if (ic_product_list === '') {
        ic_product_list = jQuery('.product-list').first();
    }
    if (ic_product_list.length === 0) {
        return false;
    }
    if (ic_product_list.offset().top === 0 && ic_product_list.offset().left === 0) {
        return false;
    }
    if (jQuery(window).scrollTop() + jQuery(window).height() > ic_product_list.offset().top) {
        return true;
    }
    return false;
}
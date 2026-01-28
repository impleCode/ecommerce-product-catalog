/*!
 impleCode FormBuilder v1.1.0
 Adds appropriate scripts to front-end form
 (c) 2021 Norbert Dreszer - https://implecode.com
 */

jQuery(document).ready(function () {
    /* globals product_object,implecode,ajaxurl,ic_catalog */
    var ic_ajaxurl = '';
    var nonce = '';
    if (typeof product_object !== 'undefined') {
        ic_ajaxurl = product_object.ajaxurl;
        nonce = product_object.nonce;
    } else if (typeof ajaxurl !== undefined) {
        ic_ajaxurl = ajaxurl;
    }
    if (nonce === '' && typeof ic_catalog !== 'undefined') {
        nonce = ic_catalog.nonce;
    }
    var state_container = jQuery('.ic-form .dropdown_state, .ic-order-checkout-data .dropdown_state');
    var state_select = state_container.find('select');

    if (state_select.length) {
        jQuery('.ic-form .dropdown_country select, .ic-order-checkout-data .dropdown_country select').on('change', function () {
            var country_selector = jQuery(this);
            var country_container = country_selector.closest('.dropdown_country');
            var this_state_container = state_container;
            var this_state_select = state_select;
            if (state_container.length > 1) {
                var new_state_container = country_container.nextAll('.dropdown_state').first();
                if (new_state_container.length) {
                    this_state_container = new_state_container;
                    this_state_select = new_state_container.find('select');
                }
            }
            var country_code = country_selector.val();
            if (country_code) {
                var data = {
                    'action': 'ic_state_dropdown',
                    'country_code': country_code,
                    'state_code': this_state_select.val(),
                    'nonce': nonce
                };
                implecode.disable_container(this_state_container);
                jQuery.post(ic_ajaxurl, data, function (response) {
                    if (response) {
                        var options = [];
                        try {
                            options = JSON.parse(response);
                        } catch (e) {
                            const json_regex = /\[.*?\]/g;
                            const parsed_response = response.match(json_regex);
                            options = JSON.parse(parsed_response);
                        }
                        this_state_select.find('option').remove();
                        this_state_select.append('<option value=""></option>');
                        jQuery(options).each(function (key, value) {
                            var selected = '';
                            if (value.checked) {
                                selected = ' selected';
                            }
                            this_state_select.append('<option' + selected + ' value="' + value.value + '">' + value.label + '</option>');
                        });
                        if (country_container.is(':visible')) {
                            this_state_container.show();
                        }
                        if (state_container.find('.chosen-container').length) {
                            state_select.trigger('chosen:updated');
                        } else if (typeof state_select.chosen === 'function') {
                            var chosen_width = '224px';
                            if (state_container.hasClass('size-medium')) {
                                chosen_width = '400px';
                            }
                            state_select.chosen({width: chosen_width});
                        }
                    } else if (this_state_container.is(':visible')) {
                        this_state_container.hide();
                    }
                    implecode.enable_container(this_state_container);
                });
            } else if (this_state_container.is(':visible')) {
                this_state_container.hide();
            }
        });
        jQuery('.ic-form .dropdown_country select,.ic-order-checkout-data .dropdown_country select').trigger('change');
    }
});
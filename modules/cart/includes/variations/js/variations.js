/*!
 impleCode Shopping Cart
 (c) 2021 impleCode - https://implecode.com
 */

jQuery(document).ready(function () {
    jQuery(".product-entry #product_details .variation_select, .product-entry .ic-variations-shortcode .variation_select").on('change', function () {
        var dropdown = jQuery(this);
        var variation = dropdown.attr("name");
        var value = dropdown.val();
        if (value !== '') {
            var product_id = jQuery('input[name="current_product"]').val();
            if (!product_id) {
                product_id = dropdown.data("product_id");
            }
            var variation_id = variation.substr(0, variation.indexOf('_'));
            jQuery("form.add-to-shopping-cart input[name=" + variation + "]").val(value);
            var values = {};
            var all_selected = 1;
            var var_num = dropdown.data("var_num");
            var var_lp = dropdown.find("option:selected").data("var_lp");
            dropdown.closest('.variations-container').find('.variation_select').each(function () {
                var this_dropdown = jQuery(this);
                if (var_num === 1) {
                    this_dropdown.find("option:disabled").each(function () {
                        jQuery(this).attr("disabled", false);
                    });
                }
                var this_value = this_dropdown.val();
                var this_name = this_dropdown.attr("name");
                if (this_value == '') {
                    this_value = 'not_selected';
                    all_selected = 0;
                }
                values[this_name] = this_value;
            });

            if (all_selected === 1) {
                var single_price_effect_container = dropdown.closest('.variable-single-price-effect');
                var variable_price_effect_container = dropdown.closest('.variable-price-effect');
                single_price_effect_container.find('.price-container').fadeTo("slow", 0);
                single_price_effect_container.find('.shipping-table-container').fadeTo("slow", 0);
                single_price_effect_container.find('.sku-table').fadeTo("slow", 0);
                dropdown.next(".ic_spinner").show().css("display", "inline-block");
                var fields = jQuery.ic.applyFilters("ic_variation_details_fields", ['shipping', 'in_cart', 'price', 'sku', 'size', 'weight', 'image']);
                jQuery.when(ic_get_variation_details(fields, values, product_id, variation_id, var_lp)).done(function (out) {
                    out = JSON.parse(out);
                    if (out === null) {
                        return false;
                    }
                    if (out[2] !== '' && out[2] !== undefined) {
                        variable_price_effect_container.find('td.price-value').html(out[2])
                        // jQuery('.variable-price-effect td.price-value').first().html(out[2]);
                    }
                    variable_price_effect_container.find('.price-container').fadeIn();
                    //jQuery(".variable-price-effect .price-container").fadeIn();

                    if (out[1] === '1') {
                        jQuery(".cart_info").hide();
                        jQuery("form.add-to-shopping-cart button").hide();
                        jQuery("form.add-to-shopping-cart input[name='current_quantity']").hide();
                        jQuery("form.add-to-shopping-cart .cart-added-info").show().css('display', 'block');
                    } else {
                        jQuery(".cart_info").show();
                        jQuery("form.add-to-shopping-cart button").fadeIn();
                        jQuery("form.add-to-shopping-cart input[name='current_quantity']").fadeIn();
                        jQuery("form.add-to-shopping-cart .cart-added-info").hide();
                    }

                    if (jQuery(".variable-price-effect .shipping-table-container").length) {
                        jQuery(".variable-price-effect .shipping-table-container").replaceWith(out[0]);
                    } else {
                        jQuery(".variable-price-effect #product_details").append(out[0]);
                    }
                    jQuery(".variable-price-effect .shipping-table-container").show();
                    jQuery(".variable-price-effect .price-container, .variable-price-effect .shipping-table-container, .variable-price-effect .sku-table").fadeTo("slow", 1);
                    jQuery.ic.doAction("ic_update_variation_details", [out, product_id]);
                    jQuery(".variations-container .ic_spinner").hide();
                });
            } else if (jQuery("article.al_product").hasClass("variable-single-price-effect")) {
                jQuery(".price-container").fadeTo("fast", 0);
                jQuery(".shipping-table-container").fadeTo("fast", 0);
                jQuery(".sku-table").fadeTo("fast", 0);
            }
        } else if (jQuery("article.al_product").hasClass("variable-single-price-effect")) {
            jQuery(".price-container").fadeTo("fast", 0);
            jQuery(".shipping-table-container").fadeTo("fast", 0);
            jQuery(".sku-table").fadeTo("fast", 0);
        }
        var data = {
            'action': 'modify_variations_price',
            'selected_variation': values,
            'product_id': product_id,
            'variation_id': variation_id
        };
        jQuery(".product-entry #product_details .variation_select").trigger("variation_select", data);
    });
    jQuery(".product-entry #product_details .variation_select").each(function () {
        jQuery(this).trigger('change');
    });

    jQuery(document).on('change', ".cart-products .variation_select", function () {
        var swicher = jQuery(this);
        swicher.closest('tr').find('.edit-product-quantity').trigger("change");
    });
});

function ic_get_variation_details(what, values, product_id, variation_id, var_lp) {
    var data = {
        'action': 'get_viariation_details',
        'selected_variation': values,
        'selected_variation_lp': var_lp,
        'product_id': product_id,
        'variation_id': variation_id,
        'variation_field': what
    };
    return jQuery.post(product_object.ajaxurl, data);
}

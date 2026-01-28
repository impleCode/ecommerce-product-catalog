/*!
 impleCode Shopping Cart
 (c) 2020 http://implecode.com
 */

if (window.opera && opera.toString() == "[object Opera]") { // only do for Opera
    history.navigationMode = "compatible";
    jQuery(".continue_shopping").click(function () {
        window.name = new Date().getTime();
    });
    if (window.name != "") { // will be '' if page not prev loaded
        window.name = ""; // reset to prevent infinite loop
        jQuery("html").hide();
        jQuery("body").css("background-color", "#fff");
        window.location.reload(true);
    }
}

jQuery(document).ready(function () {

    /* Shopping Cart */
    check_if_cart_empty('fast', true);
    var dec_sep = ic_cart_ajax_object.dec_sep;
    var th_sep = ic_cart_ajax_object.th_sep;
    var tax_rate = jQuery(".vat_rate").val();
    if (tax_rate !== undefined) {
        tax_rate = parseFloat(tax_rate);
        tax_rate = parseFloat(tax_rate / 100);
        var tax_rate_round = parseFloat(jQuery(".vat_rate_round").val());
    } else {
        tax_rate = parseFloat(0);
        var tax_rate_round = parseFloat(0);
    }
    var wto;
    jQuery(document).on('change', '#shopping-cart-container .edit-product-quantity', function (e) {
        var product_count = 0;
        var swicher = jQuery(this);
        var current_quantity = Number(swicher.val());
        var rowCount = jQuery('#shopping-cart-container .cart-products tr').length;
        if (current_quantity === 0) {
            var current_row = swicher.parent().parent();
            rowCount = rowCount - 1;
            current_row.hide('slow', function () {
                current_row.remove();
            });
            check_if_cart_empty('slow');
        }
        jQuery('input.edit-product-quantity').each(function () {
            product_count += parseFloat(jQuery(this).val());
        });
        if (jQuery('.product-shopping-cart button .cart_button_text').length > 0) {
            var selected_word = jQuery(".product-shopping-cart button .cart_button_text").text().split(" ");
            selected_word.splice(0, 1);
            selected_word = selected_word.join(" ");
            jQuery(".product-shopping-cart button .cart_button_text").text(product_count + " " + selected_word);
        }

        //jQuery('#shopping-cart-container .cart-products').css('opacity', '0.5');
        ic_hide_products_table();
        clearTimeout(wto);

        wto = setTimeout(function () {
            var new_current_quantity = Number(swicher.val());
            if (new_current_quantity === current_quantity) {
                var data = jQuery('#shopping-cart-container form').serialize();
                data = data + '&action=shopping_cart_products&raw=0&front=1';
                jQuery.post(product_object.ajaxurl, data, function (response) {
                    jQuery(".before-cart-products").remove();
                    jQuery("table.cart-products").remove();
                    jQuery("#shopping-cart-container form input[name='cart_content']").remove();
                    jQuery("#shopping-cart-container form  .form-buttons").last().before(response);
                    jQuery("#shopping-cart-container form  .form-buttons .to_cart_submit").show();
                    ic_show_products_table();
                });
            }
        }, 400);
    });
    jQuery(document).on('click', ".delete_product", function () {
        jQuery(this).parent().find('.edit-product-quantity').val(0).trigger("change");
    });
    jQuery(document).on('change', "#shopping-cart-container .variation_select", function () {
        var swicher = jQuery(this);
        var product_id = swicher.closest('tr').find('.product_id').val();
        var values = {};
        jQuery.ic.doAction("ic_cart_variation_before_update", swicher, product_id);
        swicher.closest('td.td-name').find('.variation_select').each(function () {
            this_value = jQuery(this).val();
            this_name = jQuery(this).attr("name");
            if (this_value == '') {
                this_value = 'not_selected';
            }
            values[this_name] = this_value;
        });
        var quantity = swicher.closest('tr').find('.edit-product-quantity').val();
        var data = {
            'action': 'get_shopping_cart_product_price',
            'selected_variation': values,
            'product_id': product_id,
            'quantity': quantity
        };
        // jQuery('#shopping-cart-container .cart-products').css('opacity', '0.5');
        ic_hide_products_table();
        jQuery.post(ic_cart_ajax_object.ajax_url, data, function (response) {
            response = number_format(response, 2, dec_sep, th_sep);
            swicher.closest('tr').find('.td-price').text(response);
            //jQuery('#shopping-cart-container .cart-products').css('opacity', '1');
            ic_show_products_table();
            swicher.closest('tr').find('.edit-product-quantity').trigger("change");
            jQuery.ic.doAction("ic_cart_variation_after_update", swicher, product_id);
        });
    });
    jQuery(document).on('keydown', "#shopping-cart-container .edit-product-quantity", function (event) {
        if (event.keyCode == 13) {
            event.preventDefault();
            jQuery(this).trigger("change");
            return false;
        }
    });
    /* END Shopping Cart */
    /* Order Form START */
    jQuery('#shopping_cart_form').on('submit', function () {
        var form = form2string(jQuery("#shopping_cart_form :input:visible"));
        setCookie("shopping_form_fields", form);
    });
    var cookie_fields = getCookie("shopping_form_fields");
    if (cookie_fields != null) {
        cookie2form(cookie_fields);
    }
    /* END Order Form */
    /* Shoppin Cart Button START */
    fix_cart_widget_width();
    /* END Shopping Cart Button */
    jQuery(document).on('click', '.restore-ic-cart', function (e) {
        e.preventDefault();
        var data = {
            'action': 'restore_shopping_cart',
            'front': 1
        };
        jQuery.post(ic_cart_ajax_object.ajax_url, data, function (response) {
            response = JSON.parse(response);
            var cart_container = jQuery(".restore-ic-cart").parent(".product-shopping-cart");
            cart_container.html(response[0]);
            if (jQuery("#shopping-cart-container").length) {
                jQuery("#shopping-cart-container").replaceWith(response[1]);
            }
        });
    });
});

function fix_cart_widget_width() {
    var cart_button_width = jQuery(".product-shopping-cart input").outerWidth();
    var temp_width = cart_button_width - 29;
    jQuery("#shopping_cart_widget_container .product_widget_search").outerWidth(cart_button_width);
    jQuery("#shopping_cart_widget_container .product-search-box").outerWidth(temp_width);
}

if (typeof number_format !== 'function') {
    function number_format(number, decimals, dec_point, thousands_sep) {
// Strip all characters but numerical ones.
        number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
        var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
        // Fix for IE parseFloat(0.55).toFixed(0) = 0;
        s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
        if (s[0].length > 3) {
            s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
        }
        if ((s[1] || '').length < prec) {
            s[1] = s[1] || '';
            s[1] += new Array(prec - s[1].length + 1).join('0');
        }
        return s.join(dec);
    }
}

function getCookie(name) {
    var re = new RegExp(name + "=([^;]+)");
    var value = re.exec(document.cookie);
    return (value != null) ? unescape(value[1]) : null;
}

function form2string($form) {
    return JSON.stringify($form.serializeArray());
}

function cookie2form(cookie) {
    var fields = JSON.parse(cookie);
    for (var i = 0; i < fields.length; i++) {
        var name = fields[i].name;
        var value = fields[i].value;
        if (name != "cart_content" && name != "captcha_code") {
            if (name == "cart_person") {
                jQuery(":radio[value=\"" + value + "\"]").attr("checked", true);
            } else {
                jQuery("[name=" + name + "]").val(value);
            }
        }
    }
}

var today = new Date();
var expiry = new Date(today.getTime() + 30 * 24 * 3600 * 1000); // plus 30 days
function setCookie(name, value) {
    document.cookie = name + "=" + escape(value) + "; path=/; expires=" + expiry.toGMTString();
}

function check_if_cart_empty(how, ajax) {
    if (jQuery(".cart-products").length === 0) {
        return false;
    }
    if (ajax !== undefined && jQuery('.ic_cache').length) {
        ic_hide_products_table();

        jQuery.when(update_checkout_products_table_html()).then(check_if_cart_empty(how));
        return false;
    }
    var allow = 0;
    jQuery("input.edit-product-quantity").each(function () {
        if (jQuery(this).val() != 0) {
            allow = 1;
        }
    });
    if (allow != 1) {
        jQuery(".to_cart_submit").hide(how);
        jQuery(".no-products").show(how);
    } else {
        jQuery(".no-products").hide(how);
        jQuery(".to_cart_submit").show(how);
    }
}

if (typeof ic_roundto !== 'function') {
    function ic_roundto(number, increments) {
        increments = increments * 100;
        if (increments != 0) {
            increments = 1 / increments;
            number = Math.round(number * increments * 100) / 100 / increments;
        }
        return number;
    }
}

function limitText(limitField, limitNum) {
    if (limitField.value.length > limitNum) {
        limitField.value = 999;
        jQuery(limitField).trigger("change");
    }
}

if (typeof decode_number_format !== 'function') {
    function decode_number_format(number, dec_sep, th_sep) {
        if (number === undefined) {
            return 0;
        }
        var out = number.replace(ic_cart_ajax_object.th_sep, "");
        if (ic_cart_ajax_object.dec_sep != '') {
            out = out.replace(ic_cart_ajax_object.dec_sep, ".");
        }
        return parseFloat(out);
    }
}

if (typeof price_format !== 'function') {
    function ic_price_format(price) {
        var data = {
            'action': 'ic_price_format',
            'price': price
        };
        return jQuery.post(product_object.ajaxurl, data);
    }
}

function update_checkout_products_table(data) {
    if (data['raw'] === 1) {

    } else {
        jQuery(".to_cart_submit").attr("disabled", true);
    }
    jQuery.post(product_object.ajaxurl, data, function (response) {
        jQuery.when(apply_updated_products_table(response, data)).then(after_updated_products_table(response));
    });
}

function apply_updated_products_table(response, data) {
    jQuery("table.cart-products").remove();
    if (data['raw'] === 1) {
        jQuery("#shopping-cart-submit-container .ic-form > form").prepend(response).append(function () {
            //jQuery( "input[name=ic_price_effect]" ).val( 0 );
        });
        return;
    } else {
        jQuery("#shopping-cart-container form .form-buttons:last-child").before(response);
        jQuery(".to_cart_submit").attr("disabled", false);
        jQuery(".to_cart_submit").show();
        return;
    }
}

function after_updated_products_table(response) {
    jQuery.when(jQuery.ic.doAction("ic_checkout_table_updated", response)).then();
}

function update_checkout_products_table_html(inputData) {
    if (!inputData) {
        var data = {
            'action': 'shopping_cart_products',
            'front': 1,
            'raw': 0
        };
        if (jQuery('.cart-products.raw').length) {
            data['raw'] = 1;
        }
    } else {
        var data = inputData + '&action=shopping_cart_products&raw=0&front=1';
    }
    jQuery.when(update_checkout_products_table(data)).then(ic_show_products_table());
}

function ic_hide_products_table() {
    ic_disable_container(jQuery('#shopping-cart-container'));
}

function ic_show_products_table() {
    ic_enable_container(jQuery('#shopping-cart-container'));
}
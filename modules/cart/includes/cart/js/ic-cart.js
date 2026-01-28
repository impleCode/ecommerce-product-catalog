/*!
 impleCode Shopping Cart v1.0.0 - 2014-08-27
 Adds appropriate scripts to admin settings
 (c) 2014 Norbert Dreszer - http://implecode.com
 */

jQuery(document).ready(function () {
    /* globals product_object */
    var cart_button = jQuery('.cart-widget-container.check-if-empty');
    if (cart_button.length) {
        var label = cart_button.find(".shopping-cart-widget").data("label");
        var data = {
            'action': 'ic_get_cart_button',
            'label': label
        };
        jQuery.post(product_object.ajaxurl, data, function (response) {
            if (response && !(response.indexOf("empty-cart") >= 0)) {
                cart_button.find("#shopping_cart_widget").replaceWith(response);
                ic_cart_show();
            }
        });
    }
    var cart_ajax_submit_on = true;
    jQuery("body").on('submit', '.add_to_cart_form_container form.reg_add', function (e) {
        if (jQuery(this).attr('action') !== '' || cart_ajax_submit_on === false) {
            return true;
        }
        e.preventDefault();
        var add_cart_form = jQuery(this);
        var form_data = add_cart_form.serialize();
        var cart_widget = 0;
        var cart_container = 0;
        if (jQuery("#shopping_cart_widget").length) {
            cart_widget = 1;
        }
        if (jQuery("table.cart-products").length) {
            cart_container = 1;
        }
        label = cart_button.find(".shopping-cart-widget").data("label");
        var data = {
            'action': 'ic_add_to_cart',
            'add_cart_data': form_data,
            'cart_widget': cart_widget,
            'cart_container': cart_container,
            'label': label
        };
        //jQuery( "#product_details" ).css( "opacity", "0.5" );
        add_cart_form.css("opacity", "0.5");
        var add_cart_button = add_cart_form.find(".button");
        add_cart_button.attr("disabled", true);
        add_cart_button.after('<div class="ic_spinner" style="display: inline-block"></div>');
        add_cart_form.find(".variation_select").attr("disabled", true);
        jQuery.post(product_object.ajaxurl, data, function (response) {
            try {
                response = JSON.parse(response);
            } catch (e) {
                cart_ajax_submit_on = false;
                add_cart_form.submit();
                return false;
            }
            if (!response) {
                cart_ajax_submit_on = false;
                add_cart_form.submit();
                return false;
            }
            if (add_cart_form.find(".cart-added-info").length) {
                add_cart_form.find(".cart-added-info").fadeIn().css("display", "inline-block");
            } else if (response["cart-added-info"]) {
                jQuery(response["cart-added-info"]).hide().appendTo(add_cart_form).fadeIn();
            }
            add_cart_button.hide();
            add_cart_form.find("input").hide();
            add_cart_form.next(".cart_info").hide();
            //jQuery( "#product_details" ).css( "opacity", "1" );
            add_cart_form.css("opacity", "1");
            add_cart_button.attr("disabled", false);
            add_cart_form.find(".variation_select").attr("disabled", false);
            add_cart_form.find(".ic_spinner").remove();
            if (response["cart-widget"]) {
                jQuery("#shopping_cart_widget").replaceWith(response["cart-widget"]);
                ic_cart_show();
            }
            if (response["cart-container"]) {
                jQuery("table.cart-products").replaceWith(response["cart-container"]);
            }
            jQuery(".to_cart_submit").show();
        });
    });
});

function ic_cart_show() {
    var cart_button = jQuery('.cart-widget-container.check-if-empty');
    cart_button.removeClass("ic_hidden");
    var content = jQuery(".cart-hide-container").html();
    jQuery(".cart-hide-container").after(content);
    jQuery(".cart-hide-container").remove();
}
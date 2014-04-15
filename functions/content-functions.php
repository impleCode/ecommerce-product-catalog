<?php 
/**
 * Manages product content functions
 *
 * Here all plugin content functions are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/* General */
function price_format($price_value) {
$set = get_option('product_currency_settings', unserialize(DEF_CURRENCY_SETTINGS));
$space = ' ';
if ($set['price_space'] == 'off') {
$space = '';
}
$formatted = $price_value.$space.product_currency();
if ($set['price_format'] == 'before') {
$formatted = product_currency().$space.$price_value;
}
return apply_filters('price_format', $formatted, $price_value);
}

/* Classic List */
function c_list_desc($post_id) {
$shortdesc = get_post_meta($post_id, "_shortdesc", true);
$desclenght = strlen($shortdesc);
$more = '';
if ($desclenght > 243) {
$more = ' [...]';
}
return substr($shortdesc, 0, 243) . $more;
}


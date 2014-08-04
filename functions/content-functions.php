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
$set['th_sep'] = isset($set['th_sep']) ? $set['th_sep'] : ',';
$set['dec_sep'] = isset($set['dec_sep']) ? $set['dec_sep'] : '.';
$price_value = number_format($price_value,2,$set['dec_sep'],$set['th_sep']);
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
$shortdesc = strip_tags(get_post_meta($post_id, "_shortdesc", true));
$desclenght = strlen($shortdesc);
$more = '';
if ($desclenght > 243) {
$more = ' [...]';
}
return apply_filters('c_list_desc_content', substr($shortdesc, 0, 243) . $more, $post_id);
}

/* Single Product */
function add_back_to_products_url($post, $single_names, $taxonomies) { ?>
<?php $enable_product_listing = get_option('enable_product_listing', 1);
		if ($enable_product_listing == 1) { ?>
			<a href="<?php echo product_listing_url(); ?>"><?php echo $single_names['return_to_archive']; ?></a>
<?php }
}

add_action('single_product_end','add_back_to_products_url', 99, 3); 


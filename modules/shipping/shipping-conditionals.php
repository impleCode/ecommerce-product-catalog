<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		impleCode
 */

/**
 * Checks if product shipping is enabled
 *
 * @return boolean
 */
function is_ic_shipping_enabled() {
	$shipping_count = get_shipping_options_number();
	if ( $shipping_count > 0 ) {
		return true;
	}
	return apply_filters( 'ic_is_shipping_enabled', false );
}

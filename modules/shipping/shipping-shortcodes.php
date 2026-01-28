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
add_shortcode( 'product_shipping', 'ic_product_shipping' );

/**
 * Shows product shipping table
 *
 * @param type $atts
 * @return string
 */
function ic_product_shipping( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	return get_shipping_options_table( $args[ 'product' ] );
}

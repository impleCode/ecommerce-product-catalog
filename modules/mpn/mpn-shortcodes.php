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
add_shortcode( 'product_mpn', 'ic_product_mpn' );

/**
 * Shows product mpn value
 *
 * @param type $atts
 * @return string
 */
function ic_product_mpn( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	return get_product_mpn( $args[ 'product' ] );
}

add_shortcode( 'product_mpn_table', 'ic_product_mpn_table' );

/**
 * Shows product mpn value
 *
 * @param type $atts
 * @return string
 */
function ic_product_mpn_table( $atts ) {
	$args			 = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	$single_names	 = get_single_names();
	return get_product_mpn_table( $args[ 'product' ], $single_names );
}

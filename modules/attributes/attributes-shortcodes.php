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
 * @author 		Norbert Dreszer
 */
add_shortcode( 'product_attributes', 'ic_product_attributes' );

/**
 * Shows product attributes table
 *
 * @param type $atts
 * @return string
 */
function ic_product_attributes( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	return get_product_attributes( $args[ 'product' ] );
}

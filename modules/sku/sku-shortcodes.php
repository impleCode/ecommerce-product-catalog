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
add_shortcode( 'product_sku', 'ic_product_sku' );

/**
 * Shows product SKU value
 *
 * @param type $atts
 * @return string
 */
function ic_product_sku( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	return get_product_sku( $args[ 'product' ] );
}

add_shortcode( 'product_sku_table', 'ic_product_sku_table' );

/**
 * Shows product SKU value
 *
 * @param type $atts
 * @return string
 */
function ic_product_sku_table( $atts ) {
	$args			 = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	$single_names	 = get_single_names();
	return get_product_sku_table( $args[ 'product' ], $single_names );
}

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
add_shortcode( 'product_attributes', 'ic_product_attributes' );

/**
 * Shows product attributes table
 *
 * @param type $atts
 *
 * @return string
 */
function ic_product_attributes( $atts ) {
	$args       = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	$product_id = intval( $args['product'] );
	if ( empty( $product_id ) ) {
		return '';
	}

	return get_product_attributes( $product_id );
}

add_shortcode( 'product_weight', 'ic_product_weight_shortcode' );

/**
 * Shows product weight
 *
 * @param type $atts
 *
 * @return string
 */
function ic_product_weight_shortcode( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );

	$product_id = intval( $args['product'] );
	if ( empty( $product_id ) ) {
		return '';
	}

	return ic_get_product_weight( $product_id );
}

add_shortcode( 'product_size', 'ic_product_size_shortcode' );

/**
 * Shows product size
 *
 * @param type $atts
 *
 * @return string
 */
function ic_product_size_shortcode( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );

	$product_id = intval( $args['product'] );
	if ( empty( $product_id ) ) {
		return '';
	}

	return ic_get_product_size( $product_id );
}

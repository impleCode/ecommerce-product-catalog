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
add_shortcode( 'product_price', 'ic_product_price' );

/**
 * Shows product price
 * @param type $atts
 * @return string
 */
function ic_product_price( $atts ) {
	$args	 = shortcode_atts( apply_filters( 'product_price_shortcode_args', array(
		'product'	 => get_the_ID(),
		'formatted'	 => 1,
		'container'	 => 1
	) ), $atts );
	$price	 = apply_filters( 'shortcode_product_price', product_price( $args[ 'product' ] ), $args );
	if ( !empty( $price ) && $args[ 'formatted' ] == 1 ) {
		$price = price_format( $price );
	}
	if ( !empty( $price ) && $args[ 'container' ] == 1 ) {
		$price = '<span class="product-price ' . design_schemes( null, 0 ) . '">' . $price . '</span>';
	}
	if ( !empty( $price ) && function_exists( 'ic_enqueue_main_catalog_js_css' ) ) {
		ic_enqueue_main_catalog_js_css();
	}
	return $price;
}

add_shortcode( 'product_price_table', 'ic_product_price_table' );

/**
 * Shows product price table
 *
 * @param type $atts
 * @return string
 */
function ic_product_price_table( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	return ic_price_display::get_product_price_table( $args[ 'product' ] );
}

add_shortcode( 'product_price_label', 'product_price_label_shortcode' );

/**
 * Defines product price label shortcode
 *
 * @param type $atts
 * @return type
 */
function product_price_label_shortcode( $atts ) {
	$args			 = shortcode_atts( apply_filters( 'product_price_label_shortcode_args', array() ), $atts );
	$single_names	 = get_single_names();
	$label			 = $single_names[ 'product_price' ];
	return apply_filters( 'shortcode_product_price_label', $label, $args );
}

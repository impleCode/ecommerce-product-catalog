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
function get_shipping_options_number() {
	return get_option( 'product_shipping_options_number', 1 );
}

/**
 * Returns product shipping values array
 *
 * @param type $product_id
 *
 * @return type
 */
function get_shipping_options( $product_id ) {
	$shipping_options = get_shipping_options_number();
	$shipping_values  = array();
	for ( $i = 1; $i <= $shipping_options; $i ++ ) {
		$sh_val = get_shipping_option( $i, $product_id );
		if ( ! empty( $sh_val ) || is_numeric( $sh_val ) ) {
			$test_val = ic_shipping_price_format( $sh_val );
		}
		if ( ! empty( $test_val ) ) {
			$any_shipping_value = $sh_val;
		}
		$shipping_values[ $i ] = $sh_val;
	}
	if ( ! isset( $any_shipping_value ) ) {
		$shipping_values = 'none';
	}

	return apply_filters( 'product_shipping_values', $shipping_values, $product_id );
}

/**
 * Returns product shipping labels array
 *
 * @param type $product_id
 *
 * @return type
 */
function get_shipping_labels( $product_id ) {
	$shipping_values = get_shipping_options( $product_id );
	$shipping_labels = array();
	if ( is_array( $shipping_values ) ) {
		foreach ( $shipping_values as $i => $shipping_value ) {
			$shipping_value = ic_shipping_price_format( $shipping_value );
			if ( ! empty( $shipping_value ) ) {
				$shipping_labels[ $i ] = get_shipping_label( $i, $product_id );
			}
		}
	}

	return apply_filters( 'product_shipping_labels', $shipping_labels );
}

/**
 * Returns specific shipping option
 *
 * @param type $i
 * @param type $product_id
 *
 * @return type
 */
function get_shipping_option( $i = 1, $product_id = null ) {
	if ( empty( $product_id ) ) {
		$product_id = function_exists( 'ic_get_product_id' ) ? ic_get_product_id() : get_the_ID();
	}
	$option = get_post_meta( $product_id, "_shipping" . $i, true );

	return apply_filters( 'product_shipping_option_price', $option, $product_id, $i );
}

function get_shipping_label( $i = 1, $product_id = null ) {
	if ( empty( $product_id ) ) {
		$product_id = function_exists( 'ic_get_product_id' ) ? ic_get_product_id() : get_the_ID();
	}
	$label = get_post_meta( $product_id, "_shipping-label" . $i, true );
	$label = empty( $label ) ? __( 'Shipping', 'ecommerce-product-catalog' ) : $label;

	return apply_filters( 'ic_product_shipping_label', $label, $product_id, $i );
}

add_action( 'product_details', 'show_shipping_options', 9, 0 );

/**
 * Shows shipping table
 *
 * @param object $post
 * @param array $single_names
 */
function show_shipping_options( $product_id = false ) {
	ic_show_template_file( 'product-page/product-shipping.php', AL_BASE_TEMPLATES_PATH, $product_id );
}

/**
 * Returns shipping options table
 *
 * @param int $product_id
 * @param array $v_single_names
 *
 * @return string
 */
function get_shipping_options_table( $product_id ) {
	ob_start();
	show_shipping_options( $product_id );

	return ob_get_clean();
}

/**
 * Generated a formatted shipping price
 *
 * @param $price
 * @param int $clear
 * @param int $format
 * @param int $raw
 * @param bool $free_label
 *
 * @return float|string
 */
function ic_shipping_price_format( $price, $clear = 0, $format = 1, $raw = 0, $free_label = true ) {
	if ( $price === null || $price === '' ) {
		return '';
	} else if ( empty( $price ) && $free_label ) {
		$single_names = get_single_names();

		return $single_names['free_shipping'];
	}

	return function_exists( 'price_format' ) ? price_format( $price, $clear, $format, $raw, $free_label ) : number_format( $price, 2 );
}

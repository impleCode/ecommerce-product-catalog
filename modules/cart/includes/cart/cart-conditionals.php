<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product variations
 *
 * Here product variations functions are defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-product-variations/includes
 * @author        Norbert Dreszer
 */
function is_ic_json_cart( $cart_content ) {
	if ( ! empty( $cart_content ) && is_string( $cart_content ) && isset( $cart_content[0] ) && $cart_content[0] === '{' ) {
		return true;
	}

	return false;
}

/**
 * Checks if provided cart id is already in cart
 *
 * @param array $cart_content
 * @param string $cart_id
 *
 * @return boolean
 */
function is_ic_product_in_cart( $cart_id, $cart_content = null, $strict = true ) {
	if ( empty( $cart_content ) ) {
		$cart_content = shopping_cart_products_array();
	}
	foreach ( $cart_content as $cart_content_cart_id => $quantity ) {
		if ( ic_string_contains( $cart_content_cart_id, '::' ) && ! ic_string_contains( $cart_id, '::' ) ) {
			$ex_cart_id           = explode( '::', $cart_content_cart_id );
			$cart_content_cart_id = $ex_cart_id[1];
		}
		if ( $cart_content_cart_id == $cart_id ) {
			return true;
		} else if ( ! $strict ) {
			$cart_content_product_id = cart_id_to_product_id( $cart_content_cart_id );
			if ( $cart_content_product_id == $cart_id ) {
				return true;
			}
		}
	}

	return apply_filters( 'is_ic_product_in_cart', false, $cart_id );
}

function is_ic_cart() {
	if ( function_exists( 'is_ic_quote_cart' ) ) {
		if ( is_ic_quote_cart() || is_ic_ajax( 'quote_cart_products' ) ) {
			return true;
		}
	} else if ( function_exists( 'is_ic_shopping_cart' ) ) {
		if ( is_ic_shopping_cart() || is_ic_ajax( 'shopping_cart_products' ) ) {
			return true;
		}
	}

	return false;
}

function is_ic_tax_per_product() {
	$tax_rate = get_cart_tax_rate();
	if ( ! empty( $tax_rate['tax_different_rates'] ) ) {
		return true;
	}

	return false;
}

function is_ic_tax_included() {
	$tax_rate = get_cart_tax_rate();
	if ( ! empty( $tax_rate['tax_included'] ) ) {
		return true;
	}

	return false;
}

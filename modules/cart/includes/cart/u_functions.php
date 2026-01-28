<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages shopping cart
 *
 * Here shopping cart functions are defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-quote-cart/includes
 * @author        Norbert Dreszer
 */

/**
 * Save cart individual product array
 *
 * @param int $_product_id
 * @param array|int $cart_product Product array or quantity
 * @param string $cart
 *
 * @return array
 */
function ic_cart_insert( $_product_id, $cart_product, $cart = 'cart_content' ) {
	$product_id = strval( $_product_id );
	if ( ! empty( $product_id ) ) {
		$cart_content         = ic_cart_get( $cart );
		$product_array['qty'] = 1;
		if ( is_array( $cart_product ) ) {
			$product_array = $cart_product;
		} else if ( is_int( $cart_product ) ) {
			$product_array['qty'] = $cart_product;
		}
		$product_id                  = apply_filters( 'ic_cart_insert_id', $product_id, $cart_content, $cart );
		$cart_content[ $product_id ] = apply_filters( 'ic_cart_insert', $product_array, $product_id, $cart );

		return ic_cart_save( $cart_content, $cart );
	}

	return false;
}

/**
 * Increase product quantity in cart
 *
 * @param int $product_id
 * @param string $cart
 * @param int $qty
 *
 * @return array
 */
function ic_cart_product_change_qty( $product_id, $cart = 'cart_content', $qty = 1, $cart_id = null ) {
	$cart_product = ic_cart_product_get( $product_id, $cart );
	if ( isset( $cart_product['qty'] ) ) {
		$cart_product['qty'] += intval( $qty );
	} else {
		$cart_product['qty'] = intval( $qty );
	}

	return ic_cart_insert( $product_id, $cart_product, $cart, $cart_id );
}

/**
 * Returns given cart content
 *
 * @param type $cart
 *
 * @return string
 */
function ic_cart_get( $cart = 'cart_content' ) {
	$session = get_product_catalog_session();
	if ( isset( $session['cart'][ $cart ] ) ) {
		return $session['cart'][ $cart ];
	}

	return '';
}

/**
 * Returns given cart content
 *
 * @param type $cart
 *
 * @return string
 */
function ic_cart_product_get( $product_id, $cart = 'cart_content', $_cart_id = null ) {
	$cart_content = ic_cart_get( $cart );
	if ( isset( $cart_content[ $product_id ] ) ) {
		$cart_product = $cart_content[ $product_id ];
		$cart_id      = intval( $_cart_id );
		if ( ! empty( $cart_id ) && isset( $cart_product[ $cart_id ] ) ) {
			$cart_product = $cart_product[ $cart_id ];
		}
	} else {
		$cart_product = array();
	}

	return apply_filters( 'ic_cart_product_get', $cart_product, $product_id );
}

/**
 * Saves new cart content
 *
 * @param type $new_cart
 * @param type $cart
 *
 * @return type
 */
function ic_cart_save( $new_cart, $cart = 'cart_content' ) {
	$session = get_product_catalog_session();
	if ( ! isset( $session['cart'] ) ) {
		$session['cart'] = array();
	}
	$session['cart'][ $cart ] = apply_filters( 'ic_cart_save', $new_cart );
	set_product_catalog_session( $session );

	return $new_cart;
}

/**
 * Add product(s) to cart
 *
 * @param array|string $insert
 * @param atring $cart
 */
function ic_cart_products_array_save( $insert, $cart = 'cart_content' ) {
	if ( is_array( $insert ) ) {
		$cart_content = ic_cart_get( $cart );
		foreach ( $insert as $product_id => $product_array ) {
			$cart_id                  = apply_filters( 'ic_cart_insert_product_id', $product_id, $product_array );
			$cart_content[ $cart_id ] = apply_filters( 'ic_cart_insert_product_array', $product_array, $product_id, $cart_id );
		}

		return ic_cart_save( $cart_content, $cart );
	} else if ( is_int( $insert ) ) {
		$cart_id = apply_filters( 'ic_cart_insert_product_id', $insert );

		return ic_cart_change_qty( $cart_id, $cart );
	}

	return;
}

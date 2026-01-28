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
 * Returns given cart content
 *
 * @param type $cart
 *
 * @return string
 */
function ic_cart_get( $cart = 'cart_content' ) {
	$session = get_product_catalog_session();
	if ( ! isset( $session['ic_cart'] ) ) {
		$session['ic_cart'] = array();
		set_product_catalog_session( $session );
	}
	if ( isset( $session['ic_cart'][ $cart ] ) ) {
		return $session['ic_cart'][ $cart ];
	}

	return '';
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
	if ( ! isset( $session['ic_cart'] ) ) {
		$session['ic_cart'] = array();
	}
	$session['ic_cart'][ $cart ] = apply_filters( 'ic_cart_save', $new_cart );
	set_product_catalog_session( $session );

	return $new_cart;
}

/**
 * Clears selected cart
 *
 * @param type $cart
 */
function ic_cart_clear( $cart = 'cart_content' ) {
	$session = apply_filters( 'ic_cart_clear', get_product_catalog_session() );
	if ( ! isset( $session['ic_cart'] ) ) {
		$session['ic_cart'] = array();
	}
	if ( isset( $session['ic_cart'][ $cart ] ) ) {
		unset( $session['ic_cart'][ $cart ] );
	}
	if ( isset( $session['recent_cart_added_variation'] ) ) {
		unset( $session['recent_cart_added_variation'] );
	}
	if ( isset( $session['recent_cart_added'] ) ) {
		unset( $session['recent_cart_added'] );
	}
	if ( isset( $session['trans_id'] ) ) {
		unset( $session['trans_id'] );
	}
	set_product_catalog_session( $session );
	do_action( 'ic_cart_updated', array() );
}

/**
 * Checks if cart is already initialized
 *
 * @return boolean
 */
function is_ic_cart_initialized( $cart = 'cart_content' ) {
	$session = get_product_catalog_session();
	if ( ! isset( $session['ic_cart'] ) ) {
		$session['ic_cart'] = array();
		set_product_catalog_session( $session );

		return false;
	}
	if ( isset( $session['ic_cart'][ $cart ] ) ) {
		return true;
	}

	return false;
}

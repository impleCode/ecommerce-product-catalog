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
function ic_get_post_product_variations( $_product_id ) {
	$product_id                  = intval( $_product_id );
	$product_variations_settings = get_product_variations_settings();
	$product_variations          = false;
	if ( isset( $_POST[ '1_variation_' . $product_id ] ) && ! empty( $product_id ) && $product_variations_settings['count'] > 0 ) {
		$product_variations = array();
		for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {
			if ( isset( $_POST[ $i . '_variation_' . $product_id ] ) && $_POST[ $i . '_variation_' . $product_id ] != '' ) {
				$product_variations[] = strval( $_POST[ $i . '_variation_' . $product_id ] );
			} else {
				$product_variations[] = '';
			}
		}
	}

	return $product_variations;
}

/**
 * Get product variations from post or cart
 *
 * @param type $_product_id
 * @param type $cart
 *
 * @return type
 */
function ic_get_cart_product_variations( $_product_id, $cart = 'cart_content' ) {
	$product_id         = intval( $_product_id );
	$product_variations = ic_get_post_product_variations( $_product_id );
	if ( ! $product_variations ) {
		$cart_product       = ic_cart_product_get( $product_id, $cart );
		$product_variations = array();
		if ( isset( $cart_product['variations'] ) ) {
			$product_variations = $cart_product['variations'];
		}
	}

	return $product_variations;
}

/**
 * Get product variations from cart
 *
 * @param type $_product_id
 * @param type $cart
 *
 * @return type
 */
function ic_get_cart_saved_product_variations( $_product_id, $cart = 'cart_content' ) {
	$product_id         = intval( $_product_id );
	$cart_product       = ic_cart_product_get( $product_id, $cart );
	$product_variations = array();
	if ( isset( $cart_product['variations'] ) ) {
		$product_variations = $cart_product['variations'];
	}

	return $product_variations;
}

function ic_variations_number() {
	$product_variations_settings = get_product_variations_settings();
	if ( ! empty( $product_variations_settings['count'] ) ) {
		return intval( $product_variations_settings['count'] );
	}

	return 0;
}

function ic_get_variation_label( $product_id, $var_num ) {
	$variation_labels = get_product_variations_labels( $product_id );
	$var_num --;
	$label = empty( $variation_labels[ $var_num ] ) ? '' : $variation_labels[ $var_num ];

	return $label;
}

function ic_get_variation_values( $product_id, $var_num ) {
	$variation_values = get_product_variations_values( $product_id );
	$var_num --;
	$values = empty( $variation_values[ $var_num ] ) ? array() : $variation_values[ $var_num ];

	return $values;
}

add_filter( 'is_ic_add_to_cart_shown', 'ic_show_add_to_cart_variations', 10, 2 );

function ic_show_add_to_cart_variations( $show, $product_id ) {
	if ( ic_has_product_variations( $product_id ) ) {
		$mod_type = get_product_variations_price_mod_type( $product_id );
		if ( ! is_array( $mod_type ) ) {
			return $show;
		}
		$prices = get_product_variations_prices( $product_id );
		if ( empty( $prices ) ) {
			return $show;
		}
		if ( ! in_array( "%", $mod_type ) ) {
			return true;
		}
		/*
		  foreach ( $mod_type as $type ) {
		  if ( $type === '=' ) {
		  return true;
		  }
		  }
		 *
		 */
	}

	return $show;
}

add_filter( 'unfiltered_product_price', 'ic_equal_variations_price_to_zero', 9, 2 );
add_filter( 'product_price', 'ic_equal_variations_price_to_zero', 9, 2 );

function ic_equal_variations_price_to_zero( $price, $product_id ) {
	if ( $price === '' && ! is_ic_admin() && ic_is_variations_price_effect_active() ) {
		remove_filter( 'unfiltered_product_price', 'ic_equal_variations_price_to_zero', 9, 2 );
		remove_filter( 'product_price', 'ic_equal_variations_price_to_zero', 9, 2 );
		if ( ic_show_add_to_cart_variations( false, $product_id ) ) {
			$price = 0;
		}
		add_filter( 'unfiltered_product_price', 'ic_equal_variations_price_to_zero', 9, 2 );
		add_filter( 'product_price', 'ic_equal_variations_price_to_zero', 9, 2 );
	}

	return $price;
}

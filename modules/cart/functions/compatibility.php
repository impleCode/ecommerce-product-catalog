<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages cart compatibility functions
 *
 *
 * @version		1.0.0
 * @package		implecode-quote-cart/functions
 * @author 		Norbert Dreszer
 */
if ( !function_exists( 'ic_get_permalink' ) ) {

	function ic_get_permalink( $id = null ) {
		if ( !empty( $id ) ) {
			$id = apply_filters( 'ic_permalink_id', $id );
		}
		return get_permalink( $id );
	}

}

if ( !function_exists( 'shopping_cart_button' ) ) {

	function shopping_cart_button( $echo = 1, $table = 1, $desc = 1, $product_id = null, $qty = true, $label = null,
								$redirect = false, $before_button = '', $cart_content = null, $additional_products = null ) {
		return ic_cart_add_button( $echo, $table, $desc, $product_id, $qty, $label, $redirect, $before_button, $cart_content, $additional_products );
	}

}

if ( !function_exists( 'shopping_cart_quantity_field' ) ) {

	function shopping_cart_quantity_field( $product_id ) {
		return ic_cart_quantity_field( $product_id );
	}

}
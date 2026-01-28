<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages shopping cart includes folder
 *
 * Here includes folder files defined and managed.
 *
 * @version		1.0.0
 * @package		implecode-shopping-cart/includes
 * @author 		Norbert Dreszer
 */
add_filter( 'filter_ic_cart', 'ic_remove_multiple_qty_in_cart' );

/**
 * Removes qties in cart
 *
 * @param type $cart_content
 * @return type
 */
function ic_remove_multiple_qty_in_cart( $cart_content ) {
	foreach ( $cart_content as $cart_id => $quantity ) {
		$cart_content[ $cart_id ] = 1;
	}
	return $cart_content;
}

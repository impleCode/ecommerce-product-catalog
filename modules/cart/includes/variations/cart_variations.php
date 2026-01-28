<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages shopping cart
 *
 * Here shopping cart functions are defined and managed.
 *
 * @version		1.0.0
 * @package		implecode-quote-cart/includes
 * @author 		Norbert Dreszer
 */
add_filter( 'ic_cart_insert', 'ic_cart_insert_variations', 10, 3 );

/**
 * Insert currently added product variations
 *
 * @param type $product_array
 * @param type $product_id
 * @param type $cart
 * @return type
 */
function ic_cart_insert_variations( $product_array, $product_id, $cart ) {
	$product_variations = ic_get_post_product_variations( $product_id );
	if ( $product_variations ) {
		$product_array[ 'variations' ] = $product_variations;
	}
	return $product_array;
}

add_filter( 'ic_cart_insert_id', 'ic_cart_insert_variations_id', 10, 3 );

/**
 * Change product id to variations ID
 *
 * @param type $product_id
 * @param type $product_array
 * @param type $cart_content
 * @return string
 */
function ic_cart_insert_variations_id( $product_id, $cart_content, $cart ) {
	$post_product_variations = ic_get_post_product_variations( $product_id );
	$product_variations		 = ic_get_cart_saved_product_variations( $product_id, $cart );
	if ( $product_variations && $post_product_variations != $product_variations ) {
		$i = 0;
		do {
			$i++;
			$next_id = $i . '::' . $product_id;
			if ( isset( $cart_content[ $next_id ] ) ) {
				if ( $cart_content[ $next_id ][ 'variations' ] == $post_product_variations ) {
					$product_id = $next_id;
				}
			} else {
				$product_id = $next_id;
			}
		} while ( !isset( $cart_content[ $next_id ] ) );
	}
	return $product_id;
}

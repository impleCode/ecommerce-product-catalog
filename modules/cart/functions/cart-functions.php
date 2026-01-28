<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages quote cart functions folder
 *
 * Here includes folder files defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-quote-cart/includes
 * @author        Norbert Dreszer
 */
add_action( 'wp', 'ic_shopping_cart_redirect_empty_cart' );

/**
 * Redirect checkout to cart if cart empty
 *
 */
function ic_shopping_cart_redirect_empty_cart() {
	if ( current_user_can( "manage_product_settings" ) ) {
		return;
	}
	if ( is_ic_shopping_order() && ! is_ic_shopping_cart() && ! is_ic_ajax() ) {
		$cart = shopping_cart_products_array();
		if ( empty( $cart ) ) {
			wp_redirect( ic_shopping_cart_page_url() );
			exit;
		}
	}
}

add_action( 'init', 'ic_add_recent_new_product_in_cart', 5 );

/**
 * Add recently added product to session
 *
 */
function ic_add_recent_new_product_in_cart() {
	if ( ! is_admin() ) {
		$shopping_cart_settings = get_shopping_cart_settings();
		if ( $shopping_cart_settings['cart_redirect'] != 1 && ! empty( $_POST['current_product'] ) ) {
			$product_id = intval( $_POST['current_product'] );
			if ( ! empty( $product_id ) ) {
				$session                      = get_product_catalog_session();
				$session['recent_cart_added'] = $product_id;
				$variation_values             = get_product_variations_values( $product_id, true );
				foreach ( $variation_values as $i => $variation ) {
					$i ++;
					if ( ! empty( $_POST[ $i . '_variation_' . $product_id ] ) ) {
						$session['recent_cart_added_variation'][ $i . '_variation_' . $product_id ] = sanitize_text_field( $_POST[ $i . '_variation_' . $product_id ] );
					}
				}
				set_product_catalog_session( $session );
			}
		}
	}
}

/**
 * Returns recently added product to cart
 *
 * @return type
 */
function ic_get_recently_added_product() {
	if ( ! is_admin() ) {
		$recently_added = ic_get_global( 'recent_cart_added' );
		if ( $recently_added ) {
			return $recently_added;
		}
		$session = get_product_catalog_session();
		if ( isset( $session['recent_cart_added'] ) ) {
			$recently_added = $session['recent_cart_added'];
			unset( $session['recent_cart_added'] );
			set_product_catalog_session( $session );
			ic_save_global( 'recent_cart_added', $recently_added );

			return $recently_added;
		}
	}

	return '';
}

if ( ! function_exists( 'ic_payment_number_format' ) ) {

	function ic_payment_number_format( $number ) {
		if ( is_numeric( $number ) ) {
			$number = number_format( floatval( $number ), 2, ".", "" );
		} else {
			$number = 0;
		}

		return $number;
	}

}

<?php

/**
 * Sets shopping cart conditionals
 *
 * Created by Norbert Dreszer.
 * Date: 03-Mar-15
 * Time: 18:07
 * Package: conditionals.php
 */
function is_ic_shopping_page() {
	if ( is_ic_shopping_cart() || is_ic_shopping_order() || is_ic_shopping_thank_you() ) {
		return true;
	}

	return false;
}

function is_ic_inside_shopping_page() {
	if ( is_ic_inside_shopping_cart() || is_ic_inside_checkout() || is_ic_inside_thank_you() ) {
		return true;
	}

	return false;
}

function is_ic_shopping_cart() {
	if ( is_ic_ajax( 'shopping_cart_products' ) ) {
		return true;
	}
	$shopping_cart_settings = get_shopping_cart_settings();
	if ( ! empty( $shopping_cart_settings['shopping_cart_page'] ) && $shopping_cart_settings['shopping_cart_page'] !== 'noid' && is_ic_page( $shopping_cart_settings['shopping_cart_page'] ) ) {
		return true;
	}

	return false;
}

function is_ic_shopping_order() {
	$shopping_cart_settings = get_shopping_cart_settings();
	if ( ! empty( $shopping_cart_settings['cart_submit_page'] ) && $shopping_cart_settings['cart_submit_page'] !== 'noid' && is_ic_page( $shopping_cart_settings['cart_submit_page'] ) ) {
		return apply_filters( 'is_ic_shopping_order', true );
	}

	return false;
}

function is_ic_shopping_thank_you() {
	$shopping_cart_settings = get_shopping_cart_settings();
	if ( ! empty( $shopping_cart_settings['thank_you_page'] ) && $shopping_cart_settings['thank_you_page'] != 'noid' && is_ic_page( $shopping_cart_settings['thank_you_page'] ) ) {
		return true;
	}

	return false;
}

function is_ic_min_qty_enabled() {
	$shopping_cart_settings = get_shopping_cart_settings();
	if ( $shopping_cart_settings['qty_box'] == 1 ) {
		return true;
	}

	return false;
}

/**
 * Checks whether the max quantity box is enabled
 *
 * @return boolean
 */
function is_ic_max_qty_enabled() {
	$shopping_cart_settings = get_shopping_cart_settings();
	if ( $shopping_cart_settings['max_qty_box'] == 1 ) {
		return true;
	}

	return apply_filters( 'ic_max_qty_enabled', false );
}

function is_ic_shopping_cart_empty() {
	$cart_content = ic_shopping_cart_content( true );
	$how_many     = ic_get_cart_items_count( $cart_content );
	if ( empty( $how_many ) ) {
		return true;
	}

	return false;
}

if ( ! function_exists( 'ic_product_exists' ) ) {

	/**
	 * Checks if product exists
	 *
	 * @param type $product_id
	 *
	 * @return boolean
	 */
	function ic_product_exists( $product_id ) {
		if ( false === get_post_status( $product_id ) ) {
			return false;
		}

		return true;
	}

}

function is_ic_any_payment_gateway_active() {
	$normal_payments = ic_get_order_payments();
	if ( ! empty( $normal_payments ) ) {
		return true;
	}

	return false;
}

function is_ic_order_taxed() {
	$tax_rate = get_cart_tax_rate();
	if ( ! empty( $tax_rate['tax_rate'] ) ) {
		return true;
	}

	return false;
}

function is_ic_cart_customer() {
	$return      = false;
	$customer_id = ic_cart_customer_id();
	if ( ! empty( $customer_id ) ) {
		$ic_roles = get_ic_roles( $customer_id );
		if ( current_user_can( 'customer' ) || in_array( 'customer', $ic_roles ) || in_array( 'cart_customer', $ic_roles ) || current_user_can( 'cart_customer' ) || in_array( 'digital_customer', $ic_roles ) || current_user_can( 'digital_customer' ) ) {
			$return = true;
		}
	}

	return $return;
}

function is_ic_inside_shopping_cart() {
	if ( is_ic_inside_cart_products() ) {
		return true;
	}

	return ic_get_global( 'inside_shopping_cart' );
}

function is_ic_inside_checkout() {
	if ( is_ic_inside_cart_products() ) {
		return true;
	}

	return ic_get_global( 'inside_checkout' );
}

function is_ic_inside_cart_products() {
	return ic_get_global( 'inside_cart_products' );
}

function is_ic_inside_thank_you() {
	return ic_get_global( 'inside_thank_you' );
}

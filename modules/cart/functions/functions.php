<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages shopping functions
 *
 * Here shopping functions are defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-shopping-cart/functions
 * @author        Norbert Dreszer
 */
function ic_shopping_cart_page_url() {
	$shopping_cart_settings = get_option( 'shopping_cart_settings' );
	if ( empty( $shopping_cart_settings['shopping_cart_page'] ) ) {
		return '';
	}
	$status = get_post_status( $shopping_cart_settings['shopping_cart_page'] );
	if ( $status && $status !== 'trash' ) {
		return ic_get_permalink( $shopping_cart_settings['shopping_cart_page'] );
	}

	return '';
}

function ic_shopping_submit_page_url() {
	$shopping_cart_settings = get_option( 'shopping_cart_settings' );
	if ( empty( $shopping_cart_settings['cart_submit_page'] ) ) {
		return '';
	}
	$status = get_post_status( $shopping_cart_settings['cart_submit_page'] );
	if ( $status && $status !== 'trash' ) {
		return ic_get_permalink( $shopping_cart_settings['cart_submit_page'] );
	}

	return '';
}

function ic_shopping_cart_content() {
	return ic_cart_content( true, 'cart_content' );
}

function ic_get_cart_items_count( $cart_content ) {
	$decoded_cart_content = ic_decode_json_cart( $cart_content, true );

	return $decoded_cart_content['count'];
}

if ( ! function_exists( 'ic_clear_cart_without_payment' ) ) {
	add_action( 'init', 'ic_clear_cart_without_payment' );

	function ic_clear_cart_without_payment() {
		if ( function_exists( 'ic_get_order_payments' ) ) {
			$normal_payments = ic_get_order_payments();
		}
		if ( empty( $normal_payments ) ) {
			add_action( 'ic_formbuilder_after_mail', 'ic_clear_shopping_cart', 99 );
			add_action( 'ic_before_payment_redirect', 'ic_clear_shopping_cart' );
		}
	}

}

if ( ! function_exists( 'ic_clear_shopping_cart' ) ) {
	add_action( 'gateway_payment_verification_successful', 'ic_clear_shopping_cart' );
	add_action( 'ic_payment_pre_verified', 'ic_clear_shopping_cart' );
	add_action( 'wp_logout', 'ic_clear_shopping_cart' );
	add_action( 'success_page', 'ic_clear_shopping_cart', 99 );

	/**
	 * Deletes Shopping cart contents
	 *
	 */
	function ic_clear_shopping_cart() {
		ic_cart_clear( 'cart_content' );
		$_POST['cart_content'] = '';
	}

}

add_action( 'wp', 'ic_shopping_form_output_buffer' );

/**
 * For payment gateway redirect page
 *
 */
function ic_shopping_form_output_buffer() {
	if ( is_ic_shopping_order() ) {
		ob_start();
	}
}

add_action( 'wp', 'ic_shopping_cart_add_redirect' );

/**
 * To avoid browser back button form resubmit message
 *
 */
function ic_shopping_cart_add_redirect() {
	$shopping_cart_settings = get_shopping_cart_settings();
	if ( $shopping_cart_settings['cart_redirect'] != 1 && isset( $_POST['current_product'] ) && ! is_ic_ajax() ) {
		ic_shopping_cart_content( true );
		if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
			$location = esc_url_raw( $_SERVER['HTTP_REFERER'] );
			wp_safe_redirect( $location );
			exit;
		}
	}
}

/**
 * Defines shopping cart continue shopping URL
 *
 * @return type
 */
function ic_get_shopping_back_url() {
	$shopping_cart_settings = get_shopping_cart_settings();
	if ( $shopping_cart_settings['contnue_shopping_target'] == 'prev' ) {
		$url = 'javascript:history.go(-1)';
	} else {
		$url = product_listing_url();
	}

	return apply_filters( 'continue_shopping_url', $url );
}

if ( ! function_exists( 'ic_country_selector' ) ) {

	function ic_country_selector( $name, $required = null, $selected = null, $echo = 1 ) {
		$return              = '<select ' . $required . ' data-placeholder="' . __( 'Choose your country...', 'ecommerce-product-catalog' ) . '" id="country-selector" name="' . $name . '" class="country-selector">';
		$return              .= '<option value=""></option>';
		$supported_countries = implecode_supported_countries();
		foreach ( $supported_countries as $code => $country ) {
			$return .= '<option value="' . $code . '"' . selected( $selected, $code, 0 ) . '>' . $country . '</option>';
		}
		$return .= '</select>';

		return echo_ic_setting( $return, $echo );
	}

}

if ( ! function_exists( 'ic_roundto' ) ) {

	function ic_roundto( $number, $increments ) {
		$increments = $increments * 100;
		if ( $increments == 0 ) {
			return round( $number, 2 );
		} else {
			$increments = 1 / $increments;

			return ( round( $number * $increments, 2 ) / $increments );
		}
	}

}

if ( ! function_exists( 'ic_get_price_tax' ) ) {

	function ic_get_price_tax( $price, $product_id = null ) {
		$tax_rate = get_cart_tax_rate();
		if ( ! empty( $product_id ) ) {
			$tax_rate_c = ic_get_tax_rate( $product_id ) / 100;
		} else {
			$tax_rate_c = $tax_rate['tax_rate'] / 100;
		}
		$tax = $price * $tax_rate_c;
		if ( ic_tax_round_per_item() ) {
			$tax = ic_round_tax( $tax );
		}

		return $tax;
	}

}

add_action( 'wp', 'ic_exclude_cart_pages_caching' );

/**
 * Exclude caching for checkout pages
 */
function ic_exclude_cart_pages_caching() {
	if ( is_ic_shopping_cart() || is_ic_shopping_order() ) {
		global $cache_stop;
		$cache_stop = true;
		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( 'DONOTCACHEPAGE', 1 );
		}
	}
}

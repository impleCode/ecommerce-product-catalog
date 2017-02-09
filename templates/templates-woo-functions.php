<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WP Product template functions
 *
 * Here all plugin template functions are defined.
 *
 * @version		1.1.3
 * @package		ecommerce-product-catalog/
 * @author 		Norbert Dreszer
 */
if ( !function_exists( 'is_product' ) ) {

	function is_product() {
		return is_ic_product_page();
	}

}

if ( !function_exists( 'is_shop' ) ) {

	function is_shop() {
		return is_ic_product_listing();
	}

}
if ( !function_exists( 'is_product_taxonomy' ) ) {

	function is_product_taxonomy() {
		return is_ic_taxonomy_page();
	}

}
if ( !function_exists( 'is_product_category' ) ) {

	function is_product_category() {
		return is_ic_taxonomy_page();
	}

}
if ( !function_exists( 'is_product_tag' ) ) {

	function is_product_tag() {
		return false;
	}

}
if ( !function_exists( 'is_cart' ) ) {

	function is_cart() {
		return false;
	}

}
if ( !function_exists( 'is_checkout' ) ) {

	function is_checkout() {
		return false;
	}

}
if ( !function_exists( 'is_checkout_pay_page' ) ) {

	function is_checkout_pay_page() {
		return false;
	}

}
if ( !function_exists( 'woocommerce_get_sidebar' ) ) {

	function woocommerce_get_sidebar() {
		get_sidebar();
	}

}
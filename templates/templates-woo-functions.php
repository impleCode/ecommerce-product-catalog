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
 * @author 		impleCode
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

if ( !function_exists( 'wc_get_page_id' ) ) {

	function wc_get_page_id() {
		return -1;
	}

}

if ( !function_exists( 'woocommerce_page_title' ) ) {

	function woocommerce_page_title( $echo = true ) {
		$title = get_the_title();
		if ( $echo ) {
			echo $title;
		} else {
			return $title;
		}
	}

}

if ( !function_exists( 'woocommerce_template_single_title' ) ) {

	function woocommerce_template_single_title() {
		the_title( '<h1 class="product_title entry-title">', '</h1>' );
	}

}

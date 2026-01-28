<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product functions
 *
 * Here all plugin functions are defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        impleCode
 */
if ( ! function_exists( 'get_product_image' ) ) {

	/**
	 * Returns product image HTML
	 *
	 * @return type
	 */
	function get_product_image( $product_id, $show_default = true ) {
		$product = ic_get_product_object( $product_id );

		return $product->image_html( $show_default );
	}

}

if ( ! function_exists( 'get_product_listing_image' ) ) {

	/**
	 * Returns product image HTML
	 *
	 * @return type
	 */
	function get_product_listing_image( $product_id ) {
		$product = ic_get_product_object( $product_id );

		return $product->listing_image_html();
	}

}

if ( ! function_exists( 'get_product_image_url' ) ) {

	function get_product_image_url( $product_id ) {
		$product = ic_get_product_object( $product_id );

		return $product->image_url();
	}

}

if ( ! function_exists( 'get_product_name' ) ) {

	/**
	 * Returns product name
	 *
	 * @param type $product_id
	 *
	 * @return type
	 */
	function get_product_name( $product_id = null ) {
		$product = ic_get_product_object( $product_id );

		return $product->name();
	}

}

if ( ! function_exists( 'get_product_url' ) ) {

	/**
	 * Returns product URL
	 *
	 * @param type $product_id
	 *
	 * @return type
	 */
	function get_product_url( $product_id = null ) {
		$product = ic_get_product_object( $product_id );

		return $product->url();
	}

}

if ( ! function_exists( 'get_product_description' ) ) {

	/**
	 * Returns product description
	 *
	 * @param type $product_id
	 *
	 * @return type
	 */
	function get_product_description( $product_id = null ) {
		$product = ic_get_product_object( $product_id );

		return $product->description();
	}

}

if ( ! function_exists( 'get_product_short_description' ) ) {

	/**
	 * Returns product short description
	 *
	 * @param type $product_id
	 *
	 * @return type
	 */
	function get_product_short_description( $product_id = null ) {
		$product = ic_get_product_object( $product_id );

		return $product->short_description();
	}

}
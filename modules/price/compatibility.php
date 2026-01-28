<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Defines compatibility functions with previous versions
 *
 * Created by impleCode.
 * Date: 10-Mar-15
 * Time: 12:49
 * Package: compatibility.php
 */
if ( !function_exists( 'price_format' ) ) {

	function price_format( $price_value, $clear = 0, $format = 1, $raw = 0, $free_label = true ) {
		return ic_price_display::price_format( $price_value, $clear, $format, $raw, $free_label );
	}

}

if ( !function_exists( 'show_price' ) ) {

	function show_price( $product_id = false ) {
		ic_price_display::show_price( $product_id );
	}

}

if ( !function_exists( 'raw_price_format' ) ) {

	function raw_price_format( $price_value ) {
		return ic_price_display::raw_price_format( $price_value );
	}

}

if ( !function_exists( 'product_price' ) ) {

	function product_price( $product_id, $unfiltered = null ) {
		return ic_price_display::product_price( $product_id, $unfiltered );
	}

}

if ( !function_exists( 'product_currency' ) ) {

	function product_currency() {
		return ic_price_display::product_currency();
	}

}
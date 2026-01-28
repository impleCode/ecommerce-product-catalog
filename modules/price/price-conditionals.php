<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		impleCode
 */

/**
 * Checks if price is enabled
 *
 * @return boolean
 */
function is_ic_price_enabled() {
	$product_currency = get_currency_settings();
	if ( $product_currency[ 'price_enable' ] == 'on' ) {
		return true;
	}
	return apply_filters( 'is_ic_price_enabled', false );
}

/**
 * Checks if product has price set
 * @param type $product_id
 * @return boolean
 */
function has_product_price( $product_id = null ) {
	$product_id	 = empty( $product_id ) ? get_the_ID() : $product_id;
	$price		 = product_price( $product_id, 1 );
	if ( !empty( $price ) ) {
		return true;
	}
	return false;
}

<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Integrates multilingual plugins with eCommerce Product Catalog
 *
 * @created: May 28, 2015
 * @package: ecommerce-product-catalog/ext-comp
 */
add_filter( 'product_listing_id', 'replace_product_listing_id' );

/**
 * Replaces product listing IDs for different language
 * @param int $listing_id
 * @return int
 */
function replace_product_listing_id( $listing_id ) {
	if ( function_exists( 'pll_get_post' ) ) {
		$listing_id = pll_get_post( $listing_id );
	}
	if ( function_exists( 'icl_object_id' ) ) {
		$listing_id = icl_object_id( $listing_id, 'al_product', true );
	}
	return $listing_id;
}

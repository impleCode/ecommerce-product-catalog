<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
add_filter( 'admin_product_details', 'ic_mpn_metabox', 10, 2 );

/**
 * Adds attributes meatbox
 *
 * @param array $names
 */
function ic_mpn_metabox( $product_details, $product_id, $field_name = '_mpn', $mpn = null ) {
	if ( is_ic_mpn_enabled() ) {
		if ( $mpn === null ) {
			$mpn = get_product_mpn( $product_id );
		}
		$single_names    = get_single_names();
		$product_details .= apply_filters( 'admin_mpn_table', '<table><tr><td class="label-column">' . str_replace( ':', '', $single_names['product_mpn'] ) . ':</td><td class="mpn-column"><input type="text" name="' . $field_name . '" value="' . $mpn . '" class="widefat" /></td></tr></table>', $product_id );
	}

	return $product_details;
}

add_filter( 'product_meta_save', 'ic_save_product_mpn', 1 );

/**
 * Saves product attributes
 *
 * @param type $product_meta
 *
 * @return type
 */
function ic_save_product_mpn( $product_meta ) {
	$product_meta['_mpn'] = isset( $_POST['_mpn'] ) ? sanitize_text_field( $_POST['_mpn'] ) : '';

	return $product_meta;
}



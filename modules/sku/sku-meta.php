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
add_filter( 'admin_product_details', 'ic_sku_metabox', 10, 2 );

/**
 * Adds attributes meatbox
 *
 * @param array $names
 */
function ic_sku_metabox( $product_details, $product_id, $field_name = '_sku', $sku = null ) {
	if ( is_ic_sku_enabled() ) {
		if ( $sku === null ) {
			$sku = get_product_sku( $product_id );
		}
		$single_names    = get_single_names();
		$product_details .= apply_filters( 'admin_sku_table', '<table><tr><td class="label-column">' . str_replace( ':', '', $single_names['product_sku'] ) . ':</td><td class="sku-column"><input type="text" name="' . $field_name . '" value="' . $sku . '" class="widefat" /></td></tr></table>', $product_id );
	}

	return $product_details;
}

add_filter( 'product_meta_save', 'ic_save_product_sku', 1 );

/**
 * Saves product attributes
 *
 * @param type $product_meta
 *
 * @return type
 */
function ic_save_product_sku( $product_meta ) {
	$product_meta['_sku'] = isset( $_POST['_sku'] ) ? sanitize_text_field( $_POST['_sku'] ) : '';

	return $product_meta;
}

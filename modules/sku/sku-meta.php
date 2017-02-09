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
 * @author 		Norbert Dreszer
 */
add_action( 'admin_product_details', 'ic_sku_metabox', 10, 2 );

/**
 * Adds attributes meatbox
 *
 * @param array $names
 */
function ic_sku_metabox( $product_details, $product_id ) {
	if ( is_ic_sku_enabled() ) {
		$sku = get_post_meta( $product_id, '_sku', true );
		$product_details .= apply_filters( 'admin_sku_table', '<table><tr><td class="label-column">' . __( 'SKU', 'ecommerce-product-catalog' ) . ':</td><td class="sku-column"><input type="text" name="_sku" value="' . $sku . '" class="widefat" /></td></tr></table>', $product_id );
	}
	return $product_details;
}

add_filter( 'product_meta_save', 'ic_save_product_sku', 1 );

/**
 * Saves product attributes
 *
 * @param type $product_meta
 * @return type
 */
function ic_save_product_sku( $product_meta ) {
	$product_meta[ '_sku' ] = isset( $_POST[ '_sku' ] ) ? sanitize_text_field( $_POST[ '_sku' ] ) : '';
	return $product_meta;
}

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

/**
 * Checks if product attributes are enabled
 *
 * @return boolean
 */
function is_ic_attributes_enabled() {
	$attributes_count = product_attributes_number();
	if ( $attributes_count > 0 ) {
		return true;
	}
	return false;
}

/**
 * Checks if product has any attributes selected
 *
 * @param type $product_id
 * @return boolean
 */
function has_product_any_attributes( $product_id ) {
	$attributes_number = product_attributes_number();
	if ( $attributes_number > 0 ) {
		$attributes_number = product_attributes_number();
		for ( $i = 1; $i <= $attributes_number; $i++ ) {
			$at_val = get_post_meta( $product_id, "_attribute" . $i, true );
			if ( !empty( $at_val ) ) {
				return apply_filters( 'ic_has_product_any_attributes', true );
			}
		}
	}
	return apply_filters( 'ic_has_product_any_attributes', false );
}

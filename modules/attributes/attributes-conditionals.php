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
 *
 * @return boolean
 */
function has_product_any_attributes( $product_id ) {
	$has_attr = false;
	if ( is_ic_attributes_enabled() ) {
		$any_attr = ic_wp_get_object_terms( $product_id, 'al_product-attributes', array( 'number' => 1 ) );
		if ( ! empty( $any_attr ) ) {
			$has_attr = true;
		}
	}

	return apply_filters( 'ic_has_product_any_attributes', $has_attr, $product_id );
}

function is_ic_attribute_table_visible( $product_id ) {
	if ( has_product_any_attributes( $product_id ) ) {
		$visible = true;
	} else {
		$visible = false;
	}

	return apply_filters( 'ic_is_attribute_table_visible', $visible, $product_id );
}

function is_ic_attributes_size_enabled() {
	$settings = ic_attributes_standard_settings();
	if ( ! empty( $settings['size_unit'] ) && $settings['size_unit'] == 'disable' ) {
		return false;
	}

	return true;
}

function is_ic_attributes_weight_enabled() {
	$settings = ic_attributes_standard_settings();
	if ( ! empty( $settings['weight_unit'] ) && $settings['weight_unit'] == 'disable' ) {
		return false;
	}

	return true;
}

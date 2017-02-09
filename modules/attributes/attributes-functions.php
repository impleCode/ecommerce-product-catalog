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
add_action( 'after_product_details', 'show_product_attributes', 10, 0 );

/**
 * Shows product attributes table on product page
 *
 * @param object $post
 * @param array $single_names
 */
function show_product_attributes() {
	ic_show_template_file( 'product-page/product-attributes.php' );
	//echo get_product_attributes( $post->ID, $single_names );
}

add_action( 'single_product_begin', 'boxed_template_desc_first', 2, 0 );

/**
 * Sets products description as first tab
 *
 */
function boxed_template_desc_first() {
	$single_options = get_product_page_settings();
	if ( $single_options[ 'template' ] == 'boxed' ) {
		remove_action( 'after_product_details', 'show_product_attributes', 10, 0 );
		add_action( 'after_product_details', 'show_product_attributes', 11, 0 );
	}
}

/**
 * Returns product attributes HTML table
 *
 * @param int $product_id
 * @param array $v_single_names
 * @return string
 */
function get_product_attributes( $product_id, $v_single_names = null ) {
	ic_save_global( 'product_id', $product_id );
	ob_start();
	show_product_attributes();
	ic_delete_global( 'product_id' );
	return ob_get_clean();
}

/**
 * Returns selected attribute label
 *
 * @param type $i
 * @param type $product_id
 * @return type
 */
function get_attribute_label( $i = 1, $product_id ) {
	$label = ic_get_global( $product_id . "_attribute-label" . $i );
	if ( !$label ) {
		$label = get_post_meta( $product_id, "_attribute-label" . $i, true );
		ic_save_global( $product_id . "_attribute-label" . $i, $label );
	}
	return $label;
}

/**
 * Returns selected attribute value
 *
 * @param type $i
 * @param type $product_id
 * @return type
 */
function get_attribute_value( $i = 1, $product_id ) {
	$value = ic_get_global( $product_id . "_attribute" . $i );
	if ( !$value ) {
		$value = get_post_meta( $product_id, "_attribute" . $i, true );
	}
	if ( function_exists( 'is_ic_product_page' ) && is_ic_product_page() && !is_array( $value ) ) {
		$value = str_replace( 'rel="nofollow"', '', make_clickable( $value ) );
	}
	ic_save_global( $product_id . "_attribute" . $i, $value );
	return apply_filters( 'ic_attribute_value', $value, $product_id, $i );
}

/**
 * Returns selected attribute unit
 *
 * @param type $i
 * @param type $product_id
 * @return type
 */
function get_attribute_unit( $i = 1, $product_id ) {
	$label = ic_get_global( $product_id . "_attribute-unit" . $i );
	if ( !$label ) {
		$label = get_post_meta( $product_id, "_attribute-unit" . $i, true );
		ic_save_global( $product_id . "_attribute-unit" . $i, $label );
	}
	return $label;
}

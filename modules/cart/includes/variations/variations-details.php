<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product variations
 *
 * Here product variations functions are defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-product-variations/includes
 * @author        Norbert Dreszer
 */
add_action( 'wp_ajax_get_viariation_details', 'ic_ajax_get_viariation_details' );
add_action( 'wp_ajax_nopriv_get_viariation_details', 'ic_ajax_get_viariation_details' );

/**
 * Handles ajax variations fields
 *
 */
function ic_ajax_get_viariation_details() {
	$what               = is_array( $_POST['variation_field'] ) ? array_map( 'sanitize_text_field', $_POST['variation_field'] ) : sanitize_text_field( $_POST['variation_field'] );
	$selected_variation = is_array( $_POST['selected_variation'] ) ? array_map( 'stripcslashes', $_POST['selected_variation'] ) : stripcslashes( $_POST['selected_variation'] );

	$variation_id = intval( $_POST['variation_id'] );
	$product_id   = intval( $_POST['product_id'] );
	if ( ! empty( $product_id ) ) {
		ic_set_product_id( $product_id );
		do_action( 'ic_ajax_get_variation_details_start', $product_id );
	}
	$var_lp    = isset( $_POST['selected_variation_lp'] ) ? $_POST['selected_variation_lp'] : '';
	$out       = array();
	$var_qties = isset( $_POST['var_qties'] ) ? $_POST['var_qties'] : array();
	if ( $selected_variation != '' && ! empty( $variation_id ) ) {
		$variation_id = 1; // Details are available only for first variation
		foreach ( $what as $element ) {
			ob_start();
			if ( $element == 'in_cart' ) {
				$product_variations_settings = get_product_variations_settings();
				foreach ( $_POST['selected_variation'] as $var_id => $var_value ) {
					$_POST[ $var_id ] = $var_value;
				}

				$current_product_variations = get_current_product_variations_string( $product_id, $product_variations_settings );
				if ( $current_product_variations ) {
					$cart_id = $product_id . $current_product_variations;
				}
				$cart_content = shopping_cart_products_array();
				if ( is_ic_product_in_cart( $cart_id, $cart_content ) ) {
					echo 1;
				}
			} else if ( $element == 'price' ) {
				$price_modifier = 0;
				if ( ! empty( $selected_variation ) && ! empty( $_POST['variation_id'] ) && ! empty( $_POST['product_id'] ) ) {
					$_POST['format'] = isset( $_POST['format'] ) ? false : true;
					$_POST['price']  = isset( $_POST['price'] ) ? $_POST['price'] : null;
					$price_modifier  = get_variations_modificators( $_POST['product_id'], $selected_variation, $_POST['variation_id'], $_POST['format'], $_POST['price'], $var_qties );
				}
				echo html_entity_decode( $price_modifier );
			} else if ( ! empty( $selected_variation ) && ! empty( $product_id ) ) {
				do_action( 'ic_ajax_get_variation_details', $element, $selected_variation, $variation_id, $product_id, $var_lp );
			}
			$out[] = ob_get_clean();
		}
	}
	echo json_encode( $out );
	wp_die();
}

function has_ic_variation_details_meta() {
	return false;
}

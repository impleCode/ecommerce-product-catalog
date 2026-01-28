<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages framework folder
 *
 * Here framework folder files defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog-pro/framework
 * @author 		Norbert Dreszer
 */
class ic_cart_variations_ajax {

	function __construct() {
		add_action( 'wp_ajax_nopriv_modify_variations_price', array( $this, 'variations_price' ) );
		add_action( 'wp_ajax_modify_variations_price', array( $this, 'variations_price' ) );

		add_action( 'wp_ajax_nopriv_modify_variations_shipping', array( $this, 'variations_shipping' ) );
		add_action( 'wp_ajax_modify_variations_shipping', array( $this, 'variations_shipping' ) );
	}

	/**
	 * Handles ajax variation product price modification
	 */
	function variations_price() {
		$price_modifier = 0;
		if ( !empty( $_POST[ 'selected_variation' ] ) && !empty( $_POST[ 'variation_id' ] ) && !empty( $_POST[ 'product_id' ] ) ) {
			$format		 = isset( $_POST[ 'format' ] ) ? false : true;
			$price		 = isset( $_POST[ 'price' ] ) ? floatval( $_POST[ 'price' ] ) : null;
			$product_id	 = intval( $_POST[ 'product_id' ] );
			if ( is_array( $_POST[ 'selected_variation' ] ) ) {
				$selected_variation = array_map( 'sanitize_text_field', $_POST[ 'selected_variation' ] );
			} else {
				$selected_variation = sanitize_text_field( $_POST[ 'selected_variation' ] );
			}
			$var_id			 = esc_attr( $_POST[ 'variation_id' ] );
			$price_modifier	 = get_variations_modificators( $product_id, $selected_variation, $var_id, $format, $price );
		}
		echo $price_modifier;
		wp_die();
	}

	/**
	 * Handles ajax variation product price modification
	 */
	function variations_shipping() {
		$price_modifier = 0;
		if ( !empty( $_POST[ 'selected_variation' ] ) && !empty( $_POST[ 'variation_id' ] ) && !empty( $_POST[ 'product_id' ] ) ) {
			$format				 = isset( $_POST[ 'format' ] ) ? false : true;
			$product_id			 = intval( $_POST[ 'product_id' ] );
			$selected_variation	 = esc_attr( $_POST[ 'selected_variation' ] );
			$var_id				 = esc_attr( $_POST[ 'variation_id' ] );
			$price_modifier		 = get_variations_shipping_modificators( $product_id, $selected_variation, $var_id, $format );
		}
		echo json_encode( $price_modifier );
		wp_die();
	}

}

$ic_cart_variations_ajax = new ic_cart_variations_ajax;

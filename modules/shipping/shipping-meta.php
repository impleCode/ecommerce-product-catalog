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
add_action( 'add_product_metaboxes', 'ic_shipping_metabox' );

/**
 * Adds attributes meatbox
 *
 * @param array $names
 */
function ic_shipping_metabox( $names ) {
	$names['singular'] = ic_ucfirst( $names['singular'] );
	if ( is_plural_form_active() ) {
		$labels['shipping'] = sprintf( __( '%s Shipping', 'ecommerce-product-catalog' ), $names['singular'] );
	} else {
		$labels['shipping'] = __( 'Shipping', 'ecommerce-product-catalog' );
	}
	$sh_num = get_shipping_options_number();
	if ( $sh_num > 0 ) {
		add_meta_box( 'al_product_shipping', $labels['shipping'], 'al_product_shipping', 'al_product', apply_filters( 'product_shipping_box_column', 'side' ), apply_filters( 'product_shipping_box_priority', 'default' ) );
	}
}

/**
 * Shows shipping meta box content
 *
 * @global type $post
 */
function al_product_shipping() {
	global $post;
	echo '<input type="hidden" name="shippingmeta_noncename" id="shippingmeta_noncename" value="' .
	     wp_create_nonce( AL_BASE_PATH . 'shipping_meta' ) . '" />';
	$currency = '';
	if ( function_exists( 'product_currency' ) ) {
		$currency = product_currency();
	}
	echo '<table class="sort-settings shipping"><tbody>';
	$shipping_option       = get_default_shipping_costs();
	$shipping_label_option = get_default_shipping_labels();
	for ( $i = 1; $i <= get_shipping_options_number(); $i ++ ) {
		$shipping_option_field = get_post_meta( $post->ID, '_shipping' . $i, true );
		$shipping_label_field  = get_post_meta( $post->ID, '_shipping-label' . $i, true );
		$shipping              = '';
		if ( $shipping_option_field !== null && $shipping_option_field !== '' ) {
			$shipping = floatval( $shipping_option_field );
		} else if ( is_ic_new_product_screen() ) {
			$shipping = isset( $shipping_option[ $i ] ) ? floatval( $shipping_option[ $i ] ) : '';
		}
		if ( ! empty( $shipping_label_field ) ) {
			$shipping_label = $shipping_label_field;
		} else {
			$shipping_label = isset( $shipping_label_option[ $i ] ) ? $shipping_label_option[ $i ] : '';
		}
		echo '<tr><td class="dragger"></td><td class="shipping-label-column"><input class="shipping-label" type="text" name="_shipping-label' . $i . '" value="' . esc_html( $shipping_label ) . '" /></td><td><input class="shipping-value" type="number" min="0" step="0.01" name="_shipping' . $i . '" value="' . $shipping . '" />' . $currency . '</td></tr>';
	}
	echo '</tbody></table>';
	do_action( 'product_shipping_metabox', $post->ID );
}

add_filter( 'product_meta_save', 'ic_save_product_shipping', 1, 2 );

/**
 * Saves product attributes
 *
 * @param type $product_meta
 *
 * @return type
 */
function ic_save_product_shipping( $product_meta, $post ) {
	$max_shipping = get_shipping_options_number();
	for ( $i = 1; $i <= $max_shipping; $i ++ ) {
		$product_meta[ '_shipping' . $i ]       = isset( $_POST[ '_shipping' . $i ] ) ? $_POST[ '_shipping' . $i ] : '';
		$product_meta[ '_shipping-label' . $i ] = ! empty( $_POST[ '_shipping-label' . $i ] ) ? $_POST[ '_shipping-label' . $i ] : '';
	}

	return $product_meta;
}

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages shopping customer
 *
 * Here shopping customer is defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-shopping-cart/includes
 * @author        Norbert Dreszer
 */
if ( ! function_exists( 'get_cart_tax_rate' ) ) {

	/**
	 * Returns tax rate
	 *
	 * @param type $what
	 *
	 * @return type
	 */
	function get_cart_tax_rate( $what = null ) {
		$product_currency_settings = get_currency_settings();
		if ( empty( $product_currency_settings['tax_rate_round'] ) || ! in_array( $product_currency_settings['tax_rate_round'], ic_round_options() ) ) {
			$product_currency_settings['tax_rate_round'] = 0.01;
		}
		if ( $what == 'rate' ) {
			return floatval( $product_currency_settings['tax_rate'] );
		} else {
			return $product_currency_settings;
		}
	}

}
if ( ! function_exists( 'ic_get_checkout_field_selector' ) ) {

	function ic_get_checkout_field_selector(
		$fields, $pre_name, $name, $selected, $attr = 'multiple',
		$include_empty = false, $placeholder = null
	) {
		//$product_currency_settings	 = get_currency_settings();
		if ( $placeholder == null ) {
			$placeholder = __( 'Select Checkout Fields', 'ecommerce-product-catalog' );
		}
		$selector = '<select class="chosen checkout_limit_selector_' . $pre_name . '" ' . $attr . ' data-placeholder="' . $placeholder . '" name="' . $name . '">';
		//$pre_name					 = 'cart_';
		if ( $include_empty ) {
			$selector .= '<option value=""></option>';
		}
		foreach ( $fields->fields as $field ) {
			if ( $field->field_type == 'dropdown' ) {
				$option_selected = '';
				$field_id        = apply_filters( 'ic_formbuilder_cid', $pre_name . $field->cid, $field, $pre_name );
				if ( ! empty( $selected ) && ( ! is_array( $selected ) && $field_id == $selected ) || ( is_array( $selected ) && array_search( $field_id, $selected ) !== false ) ) {
					$option_selected = 'selected';
				}
				if ( is_array( $field->field_options->options ) ) {
					$options = json_encode( $field->field_options->options );
				}
				$selector .= '<option ' . $option_selected . ' data-options="' . $options . '" value="' . $field_id . '">' . str_replace( ':', '', $field->label ) . '</option>';
			}
		}
		$selector .= '</select>';

		return $selector;
	}

}

add_action( 'payment_settings_table_start', 'ic_add_tax_settings' );

/**
 * Shows tax settings
 */
function ic_add_tax_settings() {
	$tax_rate = get_cart_tax_rate();
	implecode_settings_checkbox( __( 'Tax is already included in price', 'ecommerce-product-catalog' ), 'product_currency_settings[tax_included]', $tax_rate['tax_included'] );

	implecode_settings_number( __( 'Tax rate', 'ecommerce-product-catalog' ), 'product_currency_settings[tax_rate]', $tax_rate['tax_rate'], '%', 1, 0.1 );

	implecode_settings_dropdown( __( 'Tax round', 'ecommerce-product-catalog' ), 'product_currency_settings[tax_rate_round]', $tax_rate['tax_rate_round'], ic_round_options(), 1, null );
	//implecode_settings_number( __( 'Tax round', 'ecommerce-product-catalog' ), 'product_currency_settings[tax_rate_round]', $tax_rate[ 'tax_rate_round' ], product_currency(), 1, 0.01 );
	implecode_settings_text( __( 'Tax label', 'ecommerce-product-catalog' ), 'product_currency_settings[tax_label]', $tax_rate['tax_label'] );
}

function ic_round_options() {
	return array(
		'1'      => '1',
		'0.1'    => '0.1',
		'0.01'   => '0.01',
		'0.001'  => '0.001',
		'0.0001' => '0.0001'
	);
}

add_filter( 'product_currency_settings', 'ic_set_default_tax_settings' );

/**
 * Defines default tax settings
 *
 * @param type $product_currency_settings
 *
 * @return type
 */
function ic_set_default_tax_settings( $product_currency_settings ) {
	$product_currency_settings['tax_included']        = isset( $product_currency_settings['tax_included'] ) ? $product_currency_settings['tax_included'] : '';
	$product_currency_settings['tax_different_rates'] = isset( $product_currency_settings['tax_different_rates'] ) ? $product_currency_settings['tax_different_rates'] : '';
	$product_currency_settings['tax_rate']            = isset( $product_currency_settings['tax_rate'] ) ? $product_currency_settings['tax_rate'] : '';
	$product_currency_settings['tax_rate_round']      = isset( $product_currency_settings['tax_rate_round'] ) ? $product_currency_settings['tax_rate_round'] : 0.01;
	$product_currency_settings['tax_label']           = isset( $product_currency_settings['tax_label'] ) ? $product_currency_settings['tax_label'] : __( 'VAT', 'ecommerce-product-catalog' );

	return $product_currency_settings;
}

function ic_get_cart_net_price( $price ) {
	$sep_settings = get_currency_settings();
	if ( ! empty( $sep_settings['tax_included'] ) ) {
		$tax_rate   = get_cart_tax_rate();
		$tax_rate_c = floatval( $tax_rate['tax_rate'] ) / 100;
		$price      = ic_roundto( $price / ( 1 + $tax_rate_c ), $tax_rate['tax_rate_round'] );
	}

	return $price;
}

function ic_cart_update_tax( $product_id, $p_total, $p_quantity, $cart_id ) {
	$current_cart_tax = ic_get_global( 'current_cart_tax' );
	if ( empty( $current_cart_tax ) ) {
		$current_cart_tax = array();
	}
	$current_cart_tax_counted = ic_get_global( 'current_cart_tax_counted' );
	if ( empty( $current_cart_tax_counted ) ) {
		$current_cart_tax_counted = array();
	}
	if ( in_array( $cart_id, $current_cart_tax_counted ) ) {
		return;
	}
	$tax_rate = ic_get_tax_rate( $product_id );
	if ( empty( $tax_rate ) ) {
		return;
	}
	$tax_rate_c      = $tax_rate / 100;
	$tax_rate_string = sprintf( "%.2f", $tax_rate );
	if ( ! isset( $current_cart_tax[ $tax_rate_string ] ) ) {
		$current_cart_tax[ $tax_rate_string ] = 0;
	}
	$round_per_item = ic_tax_round_per_item();
	if ( $round_per_item ) {
		$p_total = $p_total / $p_quantity;
	}
	if ( is_ic_tax_included() ) {
		$p_tax_total = $p_total - ( $p_total / ( 1 + $tax_rate_c ) );
	} else {
		$p_tax_total = $p_total * $tax_rate_c;
	}
	if ( $round_per_item ) {
		$p_tax_total = ic_round_tax( $p_tax_total ) * $p_quantity;
	}
	$current_cart_tax[ $tax_rate_string ] = $current_cart_tax[ $tax_rate_string ] + $p_tax_total;
	ic_save_global( 'current_cart_tax', $current_cart_tax );
	$current_cart_tax_counted[] = $cart_id;
	ic_save_global( 'current_cart_tax_counted', $current_cart_tax_counted );
}

function ic_tax_round_per_item() {
	return false;
}

function ic_cart_get_tax( $return_array = false ) {
	$current_cart_tax = ic_get_global( 'current_cart_tax' );
	$tax              = 0;
	$array            = array();
	if ( ! empty( $current_cart_tax ) ) {
		foreach ( $current_cart_tax as $key => $tax_sum ) {
			if ( function_exists( 'ic_round_tax' ) ) {
				$tax_sum = ic_round_tax( $tax_sum );
			}
			$array[ $key ] = $tax_sum;
			$tax           += $tax_sum;
		}
	}
	if ( $return_array ) {
		return $array;
	} else {
		return $tax;
	}
}

function ic_get_tax_rate() {
	$default_rate = get_cart_tax_rate( 'rate' );

	return $default_rate;
}

function ic_round_tax( $tax ) {
	$tax_rate    = get_cart_tax_rate();
	$rounded_tax = ic_roundto( $tax, $tax_rate['tax_rate_round'] );

	return $rounded_tax;
}

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
function ic_is_variations_price_effect_active() {
	$return = false;
	if ( function_exists( 'start_shopping_cart' ) && function_exists( 'is_ic_price_enabled' ) && is_ic_price_enabled() ) {
		$return = true;
	}

	return apply_filters( 'ic_is_variations_price_effect_active', $return );
}

/**
 * Checks if variations shipping effect should be enabled
 *
 * @return boolean
 */
function ic_is_variations_shipping_effect_active() {
	if ( function_exists( 'is_ic_shipping_enabled' ) && is_ic_shipping_enabled() && ic_is_variations_price_effect_active() ) {
		return true;
	}

	return false;
}

function is_ic_variations_checkout() {
	if ( function_exists( 'is_ic_shopping_cart' ) ) {
		if ( is_ic_shopping_cart() || is_ic_shopping_order() ) {
			return true;
		}
	} else if ( function_exists( 'is_ic_quote_cart' ) ) {
		if ( is_ic_quote_cart() || is_ic_quote_order() ) {
			return true;
		}
	}

	return false;
}

/**
 * Checks if multiple price effects are set for product variations
 *
 * @param type $product_id
 *
 * @return boolean
 */
function is_ic_multi_variation_price_effect( $product_id ) {
	if ( ic_is_variations_price_effect_active() ) {
		$count = ic_get_global( 'multi_variation_price_effect_' . $product_id );
		if ( ! $count ) {
			$variation_prices = get_product_variations_prices( $product_id );
			$count            = 0;
			$variation_prices = array_filter( array_map( 'array_filter', $variation_prices ) );
			foreach ( $variation_prices as $prices ) {
				if ( ! empty( $prices ) ) {
					$count += 1;
				}
			}
			ic_save_global( 'multi_variation_price_effect_' . $product_id, $count );
		}
		if ( $count > 1 ) {
			return true;
		}
	}

	return false;
}

/**
 * Checks if any price effect is set for product variations
 *
 * @param type $product_id
 *
 * @return boolean
 */
function is_ic_any_variation_price_effect( $product_id ) {
	if ( ic_is_variations_price_effect_active() ) {
		$count = ic_get_global( 'any_variation_price_effect_' . $product_id );
		if ( ! $count ) {
			$variation_prices = get_product_variations_prices( $product_id );
			$count            = 0;
			$variation_prices = array_filter( array_map( 'array_filter', $variation_prices ) );
			foreach ( $variation_prices as $prices ) {
				if ( ! empty( $prices ) ) {
					$count += 1;
				}
			}
			ic_save_global( 'any_variation_price_effect_' . $product_id, $count );
		}
		if ( $count > 0 ) {
			return true;
		}
	}

	return false;
}

/**
 * Checks if product has variations
 *
 * @param type $product_id
 *
 * @return boolean
 */
function ic_has_product_variations( $product_id ) {
	$variations = get_product_variations_values( $product_id );
	if ( ! empty( $variations ) ) {
		$variations = array_filter( $variations );
		if ( isset( $variations[0] ) && is_array( $variations[0] ) ) {
			$variations = array_filter( array_map( 'array_filter', $variations ) );
		}
		if ( ! empty( $variations ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Checks if product has multiple variations
 *
 * @param type $product_id
 *
 * @return boolean
 */
function ic_has_multiple_product_variations( $product_id ) {
	$variations = get_product_variations_values( $product_id );
	if ( ! empty( $variations ) ) {
		$variations = array_filter( $variations );
		if ( isset( $variations[0] ) && is_array( $variations[0] ) ) {
			$variations = array_filter( array_map( 'array_filter', $variations ) );
		}
		if ( ! empty( $variations ) && count( $variations ) > 1 ) {
			return true;
		}
	}

	return false;
}

function is_ic_inside_variation_selectors() {
	if ( ic_get_global( 'inside_variation_selectors' ) ) {
		return true;
	}

	return false;
}

function ic_cart_all_variations_selected() {
	$cart_items          = shopping_cart_products_array();
	$all_have_variations = true;
	foreach ( $cart_items as $cart_id => $qty ) {
		if ( ! ic_has_product_variations( $cart_id ) ) {
			continue;
		}
		$available           = get_product_variations_values( $cart_id, false );
		$selected_variations = array_filter( get_variation_value_from_cart_id( $cart_id ), function ( $value ) {
			return ! empty( $value ) || is_numeric( $value );
		} );
		if ( count( $available ) > count( $selected_variations ) ) {
			$all_have_variations = false;
			break;
		}
	}

	return $all_have_variations;
}
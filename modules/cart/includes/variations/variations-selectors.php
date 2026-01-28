<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages framework folder
 *
 * Here framework folder files defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog-pro/framework
 * @author        Norbert Dreszer
 */
class ic_cart_variations_selectors {

	public $product_id;
	public $cart_id;

	function __construct( $product_id, $cart_id = null ) {
		$this->product_id = $product_id;
		$this->cart_id    = $cart_id;
	}

	function selectors() {
		$product_id = $this->product_id;
		$cart_id    = $this->cart_id;
		if ( empty( $cart_id ) ) {
			$cart_id = $product_id;
		}
		$variation_values = array_filter( get_product_variations_values( $product_id, true ) );
		$return           = '';
		if ( ! empty( $variation_values ) ) {
			$selectors = '';
			ic_save_global( 'inside_variation_selectors', 1 );
			foreach ( $variation_values as $i => $values ) {
				$var_id    = $i + 1;
				$selectors .= $this->selector( $product_id, $var_id, $values, $cart_id );
			}
			ic_delete_global( 'inside_variation_selectors' );
			if ( ! empty( $selectors ) ) {
				$return = '<div class="variations-container" data-product_id="' . $product_id . '">';
				$return .= $selectors;
				$return .= '</div>';
			}
		}

		return $return;
	}

	function selector( $product_id, $var_id, $var_values, $cart_id = null, $before = null, $after = null ) {
		$type = ic_cat_get_variation_type( $product_id, $var_id );
		if ( $type !== 'custom' ) {
			$var_values = array_filter( $var_values );
		}
		$return = '';
		if ( $type === 'table' && is_ic_cart() ) {
			$type = 'dropdown';
		}
		if ( $type === 'dropdown' && ! empty( $var_values ) ) {
			$return .= $before . $this->dropdown( $cart_id, $var_id ) . $after;
		}

		return $return;
	}

	function dropdown( $product_id = null, $variation = 1 ) {
		$product_id         = empty( $product_id ) ? ic_get_product_id() : $product_id;
		$product_price      = product_price( cart_id_to_product_id( $product_id ) );
		$variation_values   = get_product_variations_values( $product_id, true );
		$variation_prices   = get_product_variations_prices( $product_id );
		$variation_shipping = get_product_variations_shipping( $product_id );
		$variation_mod_type = get_product_variations_price_mod_type( $product_id );
		$variation_labels   = get_product_variations_labels( $product_id );
		$return             = '';
		$i                  = $variation;
		$var_id             = $i - 1;
		if ( ! empty( $variation_labels[ $var_id ] ) ) {
			if ( ! empty( $variation_values[ $var_id ] ) ) {
				if ( ! is_array( $variation_values[ $var_id ] ) ) {
					$values = explode( "\r\n", $variation_values[ $var_id ] );
				} else {
					$values = $variation_values[ $var_id ];
				}
				if ( function_exists( 'ic_get_recently_added_product_variation' ) ) {
					$recently_added = ic_get_recently_added_product_variation();
				}
				$selected = isset( $recently_added[ $i . '_variation_' . $product_id ] ) ? $recently_added[ $i . '_variation_' . $product_id ] : '';
				if ( /* (is_ic_variations_checkout() || defined( 'DOING_AJAX' ) && DOING_AJAX) && */ $selected == '' ) {
//$selected	 = isset( $_POST[ $i . '_variation_' . $product_id ] ) ? $_POST[ $i . '_variation_' . $product_id ] : '';
					$selected = get_variation_value_from_cart_id( $product_id, $i );
				}
				$attributes   = $this->selector_attributes( $var_id, $i, $variation_mod_type, $variation_prices, $variation_shipping );
				$return       .= '<select class="variation_select" ' . $attributes . ' data-product_id="' . $product_id . '" name="' . $i . '_variation_' . get_cart_id_without_variations( $product_id ) . '">
			<option value = "">' . __( 'Select', 'ecommerce-product-catalog' ) . ' ' . $variation_labels[ $var_id ] . '</option>';
				$mod          = '';
				$var_prices_a = $variation_prices[ $var_id ];
				foreach ( $values as $key => $value ) {
					if ( empty( $value ) && ! is_numeric( $value ) ) {
						continue;
					}
					/* if ( !empty( $var_prices_a[ $key ] ) ) {
					  $mod = $variation_mod_type[ $i - 1 ] == '+' ? ' (' . price_format( $product_price + $var_prices_a[ $key ] ) . ')' : ' (' . price_format( $product_price * (1 + $var_prices_a[ $key ] / 100) ) . ')';
					  } else {
					  $mod = ' (' . price_format( $product_price ) . ')';
					  }
					  $mod = str_replace( '()', '', $mod );
					 *
					 */
					if ( isset( $variation_mod_type[ $var_id ] ) && isset( $var_prices_a[ $key ] ) ) {
						$mod = $this->get_price_effect_dropdown_info( $variation_mod_type[ $var_id ], $var_prices_a[ $key ], $product_price, $product_id );
					}
//$process_value	 = ic_filter_variation_value( $value );
					$attributes = apply_filters( 'ic_variation_dropdown_option_attributes', '', $product_id, $i, $key );
					$return     .= '<option data-var_lp="' . $key . '" value="' . $value . '" ' . selected( $value, $selected, 0 ) . ' ' . $attributes . '>' . $value . $mod . '</option>';
				}
				$return .= '</select><div class="ic_spinner"></div><br>';
			}
		}

		return $return;
	}

	function selector_attributes( $var_id, $var_num, $variation_mod_type, $variation_prices, $variation_shipping ) {
		$attributes = 'data-var_num="' . $var_num . '"';
		if ( ! empty( $variation_mod_type[ $var_id ] ) ) {
			$attributes .= ' data-mod-type="' . $variation_mod_type[ $var_id ] . '"';
		}
		if ( ! empty( $variation_prices[ $var_id ] ) && is_array( $variation_prices[ $var_id ] ) ) {
			$filtered_prices = array_filter( $variation_prices[ $var_id ] );
			if ( ! empty( $filtered_prices ) ) {
				$attributes .= ' data-mod-prices="' . encode_variation_mod( $variation_prices[ $var_id ] ) . '"';
			}
		}
		if ( ! empty( $variation_shipping[ $var_id ] ) && is_array( $variation_shipping[ $var_id ] ) ) {
			$filtered_shipping = array_filter( $variation_shipping[ $var_id ] );
			if ( ! empty( $filtered_shipping ) ) {
				$attributes .= ' data-mod-shipping="' . encode_variation_mod( $variation_shipping[ $var_id ] ) . '"';
			}
		}

		return $attributes;
	}

	/**
	 * Defines price effect dropdown info
	 *
	 * @param type $mod_type
	 * @param type $price_effect
	 * @param type $product_price
	 * @param type $product_id
	 *
	 * @return string
	 */
	function get_price_effect_dropdown_info( $mod_type, $price_effect, $product_price, $product_id ) {
		$settings = get_product_variations_settings();
		if ( empty( $product_price ) ) {
			$product_price = 0;
		}
		if ( ! empty( $price_effect ) && $settings['info'] != 'no-info' && ic_is_variations_price_effect_active() ) {
			if ( ic_has_multiple_product_variations( $product_id ) || $settings['info'] == 'price-effect' ) {
				$sign = $price_effect < 0 ? '-' : '+';
				$mod  = $mod_type == '+' ? ' (' . $sign . ' ' . price_format( abs( $price_effect ) ) . ')' : ' (' . $sign . ' ' . abs( $price_effect ) . '%)';
			} else {
				$mod = $mod_type == '+' ? ' (' . price_format( $product_price + $price_effect ) . ')' : ' (' . price_format( $product_price * ( 1 + $price_effect / 100 ) ) . ')';
			}
		} else if ( is_ic_any_variation_price_effect( $product_id ) && ! ic_has_multiple_product_variations( $product_id ) && $settings['info'] != 'price-effect' && $settings['info'] != 'no-info' && ic_is_variations_price_effect_active() ) {
			$mod = ' (' . price_format( $product_price ) . ')';
			$mod = str_replace( '()', '', $mod );
		} else {
			$mod = '';
		}

		return $mod;
	}

}

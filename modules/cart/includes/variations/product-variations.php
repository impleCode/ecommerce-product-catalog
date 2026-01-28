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
function get_variations_modificators(
	$product_id = null, $selected = null, $variation_id = null, $format = true,
	$price_value = null
) {
	$product_id       = empty( $product_id ) ? get_the_ID() : cart_id_to_product_id( $product_id );
	$variation_prices = get_product_variations_prices( $product_id );
	$variation_values = get_product_variations_values( $product_id );
	$variation_mod    = get_product_variations_price_mod_type( $product_id );
	$price_value      = isset( $price_value ) ? $price_value : product_price( $product_id );
	if ( empty( $price_value ) ) {
		$price_value = 0;
	}
	if ( empty( $selected ) ) {
		$modificators = array();
		foreach ( $variation_prices as $i => $prices ) {
			$i = $i + 1;
			if ( ! empty( $prices ) ) {
				if ( ! is_array( $prices ) ) {
					$prices = explode( "\r\n", $prices );
				}
				$selected = isset( $_POST[ $i . '_variation_' . $product_id ] ) ? $_POST[ $i . '_variation_' . $product_id ] : '';
				if ( $selected == '' ) {
					$selected = get_variation_value_from_cart_id( $product_id, $i );
				}
				if ( $selected & ! empty( $selected ) ) {
					foreach ( $variation_values[ $i ] as $z => $value ) {
						if ( $value == $selected ) {
							break;
						}
					}
					$modificators[] = $prices[ $z ];
				}
			}
		}

		return $modificators;
	} else if ( is_array( $selected ) ) {
		$price_mod = 0;
		foreach ( $selected as $var_id => $var_selected ) {
			$var_id = current( explode( "_", $var_id ) ) - 1;
			$found  = false;
			if ( ! is_array( $variation_values[ $var_id ] ) ) {
				$values = explode( "\r\n", $variation_values[ $var_id ] );
			} else {
				$values = $variation_values[ $var_id ];
			}
			if ( ! is_array( $variation_prices[ $var_id ] ) ) {
				$prices = explode( "\r\n", $variation_prices[ $var_id ] );
			} else {
				$prices = $variation_prices[ $var_id ];
			}
			foreach ( $values as $z => $value ) {
				if ( $value == $var_selected ) {
					$found = true;
					break;
				}
			}
			if ( $found ) {
				$prices[ $z ] = empty( $prices[ $z ] ) ? 0 : $prices[ $z ];
				if ( ! empty( $prices[ $z ] ) ) {
					if ( $variation_mod[ $var_id ] == '%' ) {
						$price_mod = $price_mod + ( $price_value * ( 1 + ( $prices[ $z ] / 100 ) ) - $price_value );
					} else {
						$price_mod = $price_mod + $prices[ $z ];
					}
				}
			}
		}
		if ( ! empty( $price_mod ) ) {
			$price_mod = apply_filters( 'ic_variation_mod', $price_mod, $product_id );
		}
		$modified_price = apply_filters( 'ic_variation_price', $price_value + $price_mod, $product_id );
		if ( $format ) {
			return price_format( $modified_price );
		} else {
			return $modified_price;
		}
	} else if ( $selected != 'not_selected' && ! is_array( $selected ) ) {
		$variation_id = $variation_id - 1;
		$found        = false;
		if ( ! is_array( $variation_values[ $variation_id ] ) ) {
			$values = explode( "\r\n", $variation_values[ $variation_id ] );
		} else {
			$values = $variation_values[ $variation_id ];
		}
		if ( ! is_array( $variation_prices[ $variation_id ] ) ) {
			$prices = explode( "\r\n", $variation_prices[ $variation_id ] );
		} else {
			$prices = $variation_prices[ $variation_id ];
		}
		foreach ( $values as $z => $value ) {
			if ( $value == $selected ) {
				$found = true;
				break;
			}
		}
		if ( $found ) {
			$prices[ $z ] = empty( $prices[ $z ] ) ? 0 : $prices[ $z ];
			if ( ! empty( $prices[ $z ] ) ) {
				if ( $variation_mod[ $variation_id ] == '%' ) {
					$price_mod = $price_value * ( $prices[ $z ] / 100 );
				} else {
					$price_mod = $prices[ $z ];
				}
				$price_mod   = apply_filters( 'ic_variation_mod', $price_mod, $product_id );
				$price_value = $price_value + $price_mod;
			}
			$modified_price = apply_filters( 'ic_variation_price', $price_value, $product_id );
			if ( $format ) {
				return price_format( $modified_price );
			} else {
				return $modified_price;
			}
		}
	} else {
		$modified_price = apply_filters( 'ic_variation_price', $price_value, $product_id );
		if ( $format ) {
			return price_format( $modified_price );
		} else {
			return $modified_price;
		}
	}
}

function ic_get_variation_lp( $product_id, $variation_value, $var_id ) {
	$variation_values = get_product_variations_values( $product_id );
	$var_key          = $var_id - 1;
	$lp               = array_search( $variation_value, $variation_values[ $var_key ] );

	return $lp;
}

/**
 * Returns variation modified shipping price array
 *
 * @param int $product_id Product ID (Cart ID will be transformed to Product ID)
 * @param string|array $selected Selected product variations
 * @param int $variation_id Required if $selected is set and is not array
 * @param boolean $format If output formatted price for front-end
 * @param array $shipping_price_a Shipping prices array (it will get it if not provided)
 *
 * @return type
 */
function get_variations_shipping_modificators(
	$product_id = null, $selected = null, $variation_id = null,
	$format = true, $shipping_price_a = null
) {
	$product_id         = empty( $product_id ) ? get_the_ID() : cart_id_to_product_id( $product_id );
	$variation_shipping = get_product_variations_shipping( $product_id );
	$variation_values   = get_product_variations_values( $product_id );
	$variation_mod      = get_product_variations_price_mod_type( $product_id );
	$shipping_price_a   = isset( $shipping_price_a ) ? $shipping_price_a : get_shipping_options( $product_id );
	if ( ! is_array( $shipping_price_a ) ) {
		return 0;
	}
	if ( empty( $selected ) ) {
		$modificators = array();
		foreach ( $variation_shipping as $i => $prices ) {
			$i = $i + 1;
			if ( ! empty( $prices ) ) {
				if ( ! is_array( $prices ) ) {
					$prices = explode( "\r\n", $prices );
				}
				$selected = isset( $_POST[ $i . '_variation_' . $product_id ] ) ? $_POST[ $i . '_variation_' . $product_id ] : '';
				if ( $selected == '' ) {
					$selected = get_variation_value_from_cart_id( $product_id, $i );
				}
				if ( $selected && ! empty( $selected ) ) {
					foreach ( $variation_values[ $i ] as $z => $value ) {
						if ( $value == $selected ) {
							break;
						}
					}
					$modificators[] = $prices[ $z ];

					return $modificators;
				}
			}
		}
	} else if ( is_array( $selected ) ) {
		$price_mod = 0;
		foreach ( $selected as $var_id => $var_selected ) {
			$var_id = current( explode( "_", $var_id ) ) - 1;
			$found  = false;
			if ( ! is_array( $variation_values[ $var_id ] ) ) {
				$values = explode( "\r\n", $variation_values[ $var_id ] );
			} else {
				$values = $variation_values[ $var_id ];
			}
			if ( ! is_array( $variation_shipping[ $var_id ] ) ) {
				$prices = explode( "\r\n", $variation_shipping[ $var_id ] );
			} else {
				$prices = $variation_shipping[ $var_id ];
			}
			if ( ! empty( $var_selected ) ) {
				foreach ( $values as $z => $value ) {
					if ( $value == $var_selected ) {
						$found = true;
						break;
					}
				}
				if ( $found ) {
					$prices[ $z ] = empty( $prices[ $z ] ) ? 0 : $prices[ $z ];
					$price_mod    = get_shipping_price_mod( $shipping_price_a, $prices[ $z ], $variation_mod[ $var_id ], $price_mod );
				}
			}
		}
		$modified_shipping = variation_modify_shipping( $shipping_price_a, $price_mod, $format );

		return $modified_shipping;
	} else if ( $selected != 'not_selected' && ! is_array( $selected ) ) {
		$variation_id = $variation_id - 1;
		$found        = false;
		if ( ! is_array( $variation_values[ $variation_id ] ) ) {
			$values = explode( "\r\n", $variation_values[ $variation_id ] );
		} else {
			$values = $variation_values[ $variation_id ];
		}
		if ( ! is_array( $variation_shipping[ $variation_id ] ) ) {
			$prices = explode( "\r\n", $variation_shipping[ $variation_id ] );
		} else {
			$prices = $variation_shipping[ $variation_id ];
		}
		foreach ( $values as $z => $value ) {
			if ( $value == $selected ) {
				$found = true;
				break;
			}
		}
		if ( $found ) {
			$prices[ $z ]      = empty( $prices[ $z ] ) ? 0 : $prices[ $z ];
			$price_mod         = get_shipping_price_mod( $shipping_price_a, $prices[ $z ], $variation_mod[ $variation_id ], $price_mod );
			$modified_shipping = variation_modify_shipping( $shipping_price_a, $price_mod, $format );

			return $modified_shipping;
		}
	} else {
		$modified_shipping = variation_modify_shipping( $shipping_price_a, 0, $format );

		return $modified_shipping;
	}

	return $shipping_price_a;
}

/**
 * Returns variation shipping price modificator
 *
 * @param array $shipping_price_a Product shipping price array
 * @param float $variation_shipping Selected variation shipping effect value
 * @param string $variation_mod Variation modificator type
 *
 * @return int
 */
function get_shipping_price_mod( $shipping_price_a, $variation_shipping, $variation_mod, $price_mod = 0 ) {
	if ( empty( $variation_shipping ) ) {
		return 0;
	}
	if ( $variation_mod == '%' ) {
		$price_mod = ! is_array( $price_mod ) ? array() : $price_mod;
		foreach ( $shipping_price_a as $i => $shipping_price ) {
			$price_mod[ $i ] = isset( $price_mod[ $i ] ) ? $price_mod[ $i ] : 0;
			$price_mod[ $i ] = $price_mod[ $i ] + ( $shipping_price * ( 1 + ( $variation_shipping / 100 ) ) - $shipping_price );
		}
	} else {
		$price_mod = $price_mod + $variation_shipping;
	}

	return $price_mod;
}

/**
 * Returns variation modified shipping array
 *
 * @param array $shipping_a
 * @param array|float $price_mod Modification array or value
 * @param boolean $format
 *
 * @return array
 */
function variation_modify_shipping( $shipping_a, $price_mod, $format ) {
	if ( is_array( $price_mod ) ) {
		foreach ( $shipping_a as $i => $shipping_price ) {
			$modified_shipping[ $i ] = $shipping_price + $price_mod[ $i ];
			if ( $format ) {
				$modified_shipping[ $i ] = price_format( $modified_shipping[ $i ] );
			}
		}
	} else {
		foreach ( $shipping_a as $i => $shipping_price ) {
			$modified_shipping[ $i ] = floatval( $shipping_price ) + $price_mod;
			if ( $format ) {
				$modified_shipping[ $i ] = price_format( $modified_shipping[ $i ] );
			}
		}
	}

	return $modified_shipping;
}

/**
 * Encodes array to json
 *
 * @param type $var_mod
 *
 * @return type
 */
function encode_variation_mod( $var_mod ) {
	return htmlspecialchars( json_encode( $var_mod ) );
}

/**
 * Returns product variations values array
 *
 * @param int $product_id
 * @param boolean $show_empty
 *
 * @return array
 */
function get_product_variations_values( $product_id, $show_empty = true ) {
	$product_id       = cart_id_to_product_id( $product_id );
	$variation_values = ic_get_global( $product_id . '_product_variations_values' );
	if ( ! $variation_values ) {
		$product_variations_settings = get_product_variations_settings();
		$variation_values            = array();
		for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {
			$values = get_post_meta( $product_id, $i . '_variation_values', true );
			if ( ! is_array( $values ) ) {
				$values = explode( "\r\n", $values );
			}
			if ( $show_empty || ! empty( $values ) ) {
				$variation_values[] = $values;
			}
			unset( $values );
		}
		ic_save_global( $product_id . '_product_variations_values', $variation_values );
	}

	return $variation_values;
}

/**
 * Returns product variations price modificators array
 *
 * @param int $product_id
 * @param boolean $array Will make array from each textarea value if true
 *
 * @return array
 */
function get_product_variations_prices( $product_id ) {
	$product_id       = cart_id_to_product_id( $product_id );
	$variation_prices = ic_get_global( $product_id . '_product_variations_prices' );
	if ( ! $variation_prices ) {
		$product_variations_settings = get_product_variations_settings();
		$variation_prices            = array();
		for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {
			$prices_meta = get_post_meta( $product_id, $i . '_variation_prices', true );
			if ( ! is_array( $prices_meta ) ) {
				$prices_meta = explode( "\r\n", $prices_meta );
			}
			$variation_prices[] = $prices_meta;
		}
		ic_save_global( $product_id . '_product_variations_prices', $variation_prices );
	}

	return apply_filters( 'ic_variations_prices', $variation_prices, $product_id );
}

/**
 * Returns product variations shipping price modificators array
 *
 * @param int $product_id
 * @param boolean $array Will make array from each textarea value if true
 *
 * @return array
 */
function get_product_variations_shipping( $product_id ) {
	$product_id         = cart_id_to_product_id( $product_id );
	$variation_shipping = ic_get_global( $product_id . '_product_variations_shipping' );
	if ( ! $variation_shipping ) {
		$product_variations_settings = get_product_variations_settings();
		$variation_shipping          = array();
		for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {
			$variation_shipping[] = get_post_meta( $product_id, $i . '_variation_shipping', true );
		}
		ic_save_global( $product_id . '_product_variations_shipping', $variation_shipping );
	}

	return $variation_shipping;
}

/**
 * Returns product variations labels array
 *
 * @param int $product_id
 *
 * @return array
 */
function get_product_variations_labels( $product_id ) {
	$product_id = cart_id_to_product_id( $product_id );
	$labels     = ic_get_global( $product_id . '_product_variations_labels' );
	if ( ! $labels ) {
		$labels                      = array();
		$product_variations_settings = get_product_variations_settings();
		for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {
			$labels [] = get_product_variation_label( $product_id, $i );
		}
		ic_save_global( $product_id . '_product_variations_labels', $labels );
	}

	return $labels;
}

function get_product_variation_label( $product_id, $i ) {
	$label = get_post_meta( $product_id, $i . '_variation_label', true );

	return apply_filters( 'get_product_variation_label', $label, $i, $product_id );
}

/**
 * Returns variations price modificators array
 *
 * @param int $product_id
 *
 * @return array
 */
function get_product_variations_price_mod_type( $product_id ) {
	$product_id                  = cart_id_to_product_id( $product_id );
	$product_variations_settings = get_product_variations_settings();
	$mod_types                   = array();
	for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {
		$mod_types[] = get_post_meta( $product_id, $i . '_variation_mod', true );
	}

	return $mod_types;
}

function get_cart_id_without_variations( $cart_id ) {
	if ( strpos( $cart_id, '_' ) == ! false ) {
		$cart_id = explode( '_', $cart_id );

		return $cart_id[0];
	} else {
		return $cart_id;
	}
}

/**
 * Returns exact variation value or array of selected variations if $i not provided
 *
 * @param type $cart_id
 * @param type $i
 *
 * @return boolean
 */
function get_variation_value_from_cart_id( $cart_id, $i = null ) {
	if ( strpos( $cart_id, '_' ) == ! false ) {
		$var_values = explode( '_', $cart_id );
		if ( $i == null ) {
			unset( $var_values[0] );
			$product_id = cart_id_to_product_id( $cart_id );
			foreach ( $var_values as $z => $value ) {
				$var_array[ $z . '_variation_' . $product_id ] = $value;
			}

			return $var_array;
		} else {
			return isset( $var_values[ $i ] ) ? $var_values[ $i ] : '';
		}
	} else {
		return false;
	}
}

function unescape_variation_cart_value( $encoded ) {
//    $unescaped = preg_replace_callback('/(?<!\\\\)\\\\u(\w{4})/', function ($matches) {
//        return html_entity_decode(' & #x' . $matches[1] . ';', ENT_COMPAT, 'UTF-8');
//    }, $encoded);
	$unescaped = mb_decode_numericentity( $encoded, array( 0x80, 0xffff, 0, 0xffff ), 'UTF-8' );

	return $unescaped;
}

add_filter( 'payment_order_details', 'add_variations_to_payment_summary' );

function add_variations_to_payment_summary( $order ) {
	if ( true || isset( $_POST['cart_submit'] ) ) {
		$product_variations_settings = get_product_variations_settings();
		$cart_content                = ic_shopping_cart_content( true );
		$products_array              = shopping_cart_products_array( $cart_content );
		$a                           = 0;
		foreach ( $products_array as $cart_id => $p_quantity ) {
			$product_id = cart_id_to_product_id( $cart_id );
			for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {
				$variation_label = get_product_variation_label( $product_id, $i );
				$variation_value = get_variation_value_from_cart_id( $cart_id, $i );
				if ( $variation_value ) {
					$order['variations'][ $a ][ $variation_label ] = $variation_value;
				}
			}
			$a += 1;
		}
	}

	return $order;
}

function get_current_product_variations_string( $current_product_id, $product_variations_settings ) {
	$current_product_variations = false;
	if ( $current_product_id != '' && $product_variations_settings['count'] > 0 ) {
		for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {
			if ( isset( $_POST[ $i . '_variation_' . $current_product_id ] ) && $_POST[ $i . '_variation_' . $current_product_id ] != '' ) {
				$current_product_variations = isset( $current_product_variations ) ? $current_product_variations : '';
				$current_product_variations .= '_' . $_POST[ $i . '_variation_' . $current_product_id ];
			} else if ( isset( $_POST[ $i . '_variation_' . $current_product_id ] ) ) {
				$current_product_variations = isset( $current_product_variations ) ? $current_product_variations : '';
				$current_product_variations .= '_';
			} else {
				$current_product_variations = isset( $current_product_variations ) ? $current_product_variations : '';
				$current_product_variations .= '_';
			}
		}
	}

	return $current_product_variations;
}

function create_variation_id( $cart_content, $product_id, $variations_string ) {
	$variation_id        = $product_id;
	$product_id          = cart_id_to_product_id( $product_id );
	$string_cart_content = implode( ',', array_keys( $cart_content ) );
	if ( ! empty( $cart_content ) && $product_id != '' && $variations_string && ! ic_string_contains( $string_cart_content, $product_id . $variations_string ) && ( ic_string_contains( $string_cart_content, $product_id . '_' ) || ic_string_contains( $string_cart_content, '::' . $product_id ) ) ) {
		$count         = 1;
		$lastPos       = 0;
		$same_products = array();
		//$unique_products = implode( ',', array_unique( $cart_content ) );
		while ( ( $lastPos = strpos( $string_cart_content, '::' . $product_id, $lastPos ) ) !== false ) {
			$same_products[] = $lastPos;
			$lastPos         = $lastPos + strlen( '::' . $product_id );
		}
		$count        = count( $same_products ) + 1;
		$variation_id = $count . '::' . $product_id;
	}

	/*
	  else if ($cart_content != '' && $product_id != '' && $variations_string && strpos($cart_content,$product_id.$variations_string) !== false) {
	  $count = get_variation_id($product_id.$variations_string, $cart_content);
	  if (!empty($count)) {
	  $variation_id = $count.'::'.$product_id;
	  }
	  } */

	return $variation_id;
}

add_action( 'wp', 'ic_validate_variations_submit' );

function ic_validate_variations_submit() {
	if ( is_ic_variations_checkout() && ! is_ic_product_page() && ! isset( $_POST['current_product'] ) ) {
		foreach ( $_POST as $key => $param ) {
			$key_temp = explode( '_', $key );
			if ( isset( $key_temp[1] ) && $key_temp[1] == 'variation' && $param == '' ) {
				$checkout_url = esc_url_raw( $_SERVER['HTTP_REFERER'] );
				wp_redirect( add_query_arg( 'no_variations', $key, $checkout_url ) );
				exit();
			}
		}
		if ( empty( $_POST ) && is_ic_shopping_order() && ! ic_cart_all_variations_selected() ) {
			$checkout_url = esc_url_raw( ic_shopping_cart_page_url() );
			if ( empty( $checkout_url ) ) {
				return;
			}
			wp_redirect( add_query_arg( 'no_variations', '1', $checkout_url ) );
			exit();
		}
	}
}

/**
 * Delete variations id from cart id
 *
 * @param type $cart_id
 *
 * @return type
 */
function get_cart_id_without_variation_id( $cart_id ) {
	if ( strpos( $cart_id, '::' ) !== false ) {
		$cart_id = explode( '::', $cart_id );
		$cart_id = $cart_id[1];
	}

	return $cart_id;
}

add_filter( 'post_class', 'add_variations_product_class', 10, 1 );

/**
 * Adds variable product class
 *
 * @param string $product_class
 * @param type $class
 * @param type $product_id
 *
 * @return string
 */
function add_variations_product_class( $product_class ) {
	if ( ! is_ic_product_page() ) {
		return $product_class;
	}
	$product_id = ic_get_product_id();
	if ( function_exists( 'is_ic_product' ) && is_ic_product( $product_id ) && ic_has_product_variations( $product_id ) ) {
		$product_class[] = 'variable-product';
		if ( is_ic_any_variation_price_effect( $product_id ) ) {
			$product_class[] = 'variable-price-effect';
			$settings        = get_product_variations_settings();
			if ( is_ic_multi_variation_price_effect( $product_id ) ) {
				$product_class[] = 'variable-multi-price-effect';
			} else if ( ! ic_has_multiple_product_variations( $product_id ) && $settings['info'] === 'full-price' ) {
				$product_class[] = 'variable-single-price-effect';
			}
		}
	}

	return $product_class;
}

/*
  function get_variation_id( $cart_id, $cart_content ) {
  $cart_content = explode( ',', $cart_content );
  foreach ( $cart_content as $item ) {
  if ( strpos( $item, $cart_id ) !== FALSE ) {
  if ( strpos( $item, '::' ) !== FALSE ) {
  $item = explode( '::', $item );
  return $item[ 0 ];
  break;
  } else {
  return '';
  break;
  }
  }
  }
  }
 */


add_filter( 'ic_cat_variation_tr_class', 'ic_cat_get_variation_type_class', 10, 3 );

function ic_cat_get_variation_type_class( $class, $product_id, $var_id ) {
	$class .= ' type-' . ic_cat_get_variation_type( $product_id, $var_id );

	return $class;
}

function ic_cat_get_variation_type( $product_id, $var_id ) {
	$type = get_post_meta( $product_id, $var_id . '_variation_type', true );
	if ( empty( $type ) ) {
		$type = 'dropdown';
	}

	return apply_filters( 'ic_variation_type', $type );
}

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages shopping cart
 *
 * Here shopping cart functions are defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-quote-cart/includes
 * @author        Norbert Dreszer
 */
class ic_cart_shipping {

	function __construct() {
		if ( is_ic_shipping_enabled() ) {
			add_action( 'init', array( $this, 'hooks' ) );
		}
	}

	function hooks() {
		add_action( 'product-shipping-settings', array( $this, 'settings_html' ) );

		add_filter( 'ic_checkout_products_table_end', array( $this, 'shipping_total_table_row' ), 10, 3 );
		add_filter( 'ic_formbuilder_before_button', array( $this, 'checkout_options_html' ), 7, 2 );

		add_filter( 'checkout_shipping_options', array( $this, 'add_current' ), 10, 2 );
		add_filter( 'shopping_cart_order_handling', array( $this, 'selected_cost' ), 10, 2 );
	}

	/**
	 * Shows cart shipping settings
	 *
	 */
	function settings_html() {
		$cart_shipping = $this->settings();
		echo '<h3>' . __( 'Checkout Shipping Cost', 'ecommerce-product-catalog' ) . '</h3>';
		echo '<table>';
		implecode_settings_radio( __( 'Shipping Cost Mode', 'ecommerce-product-catalog' ), 'general_shipping_settings[cart_shipping][mode]', $cart_shipping['mode'], array(
			'highest'    => __( 'Highest cost of all products in cart', 'ecommerce-product-catalog' ),
			'individual' => __( 'Sum of each product shipping cost', 'ecommerce-product-catalog' ),
			'none'       => __( 'Shipping cost calculation disabled', 'ecommerce-product-catalog' )
		) );
		echo '</table>';
	}

	/**
	 * Returns cart shipping settings
	 *
	 * @return type
	 */
	function settings() {
		$shipping_settings                          = get_general_shipping_settings();
		$shipping_settings['cart_shipping']['mode'] = isset( $shipping_settings['cart_shipping']['mode'] ) ? $shipping_settings['cart_shipping']['mode'] : 'highest';

		return $shipping_settings['cart_shipping'];
	}

	/**
	 * Checks if indivicual cart shipping cost is enabled
	 *
	 * @return boolean
	 */
	function individual() {
		$settings = $this->settings();
		if ( $settings['mode'] == 'individual' ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns current checkout shipping options array
	 *
	 * @param string $pre_name
	 *
	 * @return array
	 */
	function selected_options( $pre_name ) {
		if ( $checkout_shipping = ic_get_global( 'current_checkout_shipping_options' ) ) {
			return $checkout_shipping;
		}
		$products_array  = ic_cart_products_array( null, $pre_name );
		$product_count   = count( $products_array );
		$shipping_labels = $this->remove_empty_labels( $this->cart_products_shipping_labels( $products_array ) );
		$not_unique      = $this->cart_get_most_appearing_shipping( $shipping_labels );
		if ( empty( $not_unique ) ) {
			$checkout_shipping = $this->front_labels( $shipping_labels );
		} else {
			$max             = max( $not_unique );
			$current_options = $this->array_get_max_value_keys( $not_unique );
			if ( ! empty( $current_options ) && $max >= $product_count ) {
				$checkout_shipping = $this->labels_to_options( $shipping_labels, $current_options );
			} else {
				$checkout_shipping = $this->labels_to_options( $shipping_labels, $current_options );
			}
		}
		$checkout_shipping = apply_filters( 'current_checkout_shipping_options', $checkout_shipping, $shipping_labels, $products_array );
		ic_save_global( 'current_checkout_shipping_options', $checkout_shipping );

		return $checkout_shipping;
	}

	function remove_empty_labels( $shipping_labels ) {
		foreach ( $shipping_labels as $cart_id => $labels ) {
			foreach ( $labels['labels'] as $key => $in_labels ) {
				if ( $labels['prices'][ $key ] === '' ) {
					unset( $shipping_labels[ $cart_id ]['labels'][ $key ] );
					unset( $shipping_labels[ $cart_id ]['prices'][ $key ] );
				}
			}
		}

		return $shipping_labels;
	}

	/**
	 * Transforms shipping labels directly to checkout shipping array
	 *
	 * @param array $shipping_labels
	 *
	 * @return array
	 */
	function front_labels( $shipping_labels ) {
		$shipping = $shipping_labels;
		if ( ! isset( $shipping_labels[1] ) ) {
			$shipping = array();
			$i        = 0;
			foreach ( $shipping_labels as $cart_id => $ship ) {
				$product_id   = cart_id_to_product_id( $cart_id );
				$product_name = apply_filters( 'cart_email_product_name', get_product_name( $product_id ), $product_id, $cart_id );
				foreach ( $ship['labels'] as $i => $name ) {
					if ( ! empty( $name ) ) {
						$shipping[ $cart_id ]['product_ids']      = $product_id;
						$shipping[ $cart_id ]['product_names']    = $product_name;
						$shipping[ $cart_id ]['options'][ $name ] = array(
							'name'  => $name,
							'price' => $ship['prices'][ $i ]
						);
					}
				}
			}
		}

		return $shipping;
	}

	/**
	 * Transforms cart products array to product shipping labels array
	 *
	 * @param type $products_array
	 *
	 * @return type
	 */
	function cart_products_shipping_labels( $products_array ) {
		$product_shipping = array();
		foreach ( $products_array as $cart_id => $qty ) {
			$product_id = cart_id_to_product_id( $cart_id );
			$selected   = get_variation_value_from_cart_id( $cart_id );
			$labels     = apply_filters( 'checkout_shipping_labels', get_shipping_labels( $product_id ), $product_id, $cart_id );
			if ( ! empty( $labels ) ) {
				$prices = apply_filters( 'checkout_shipping_prices', get_variations_shipping_modificators( $product_id, $selected, null, false ), $labels, $product_id, $cart_id );
				if ( ! empty( $prices ) ) {
					$product_shipping[ $cart_id ]['labels'] = $labels;
					$product_shipping[ $cart_id ]['prices'] = $prices;
				}
			}
		}

		return $product_shipping;
	}

	/**
	 * Transforms shipping labels to current shipping options with multiple groups
	 *
	 * @param array $shipping_labels
	 * @param array $current_options
	 *
	 * @return array
	 */
	function labels_to_options( $shipping_labels, $current_options ) {
		$option_added  = array();
		$product_added = array();
		if ( ! is_array( $current_options[0] ) ) {
			$current_options = array( 0 => $current_options );
		}
		$price = array();
		foreach ( $current_options as $x => $options ) {
			foreach ( $shipping_labels as $cart_id => $option ) {
				$product_id   = cart_id_to_product_id( $cart_id );
				$product_name = apply_filters( 'cart_email_product_name', get_product_name( $product_id ), $product_id, $cart_id );
				foreach ( $option['labels'] as $i => $label ) {
					if ( array_search( $label, $options ) !== false ) {
						unset( $shipping_labels[ $cart_id ] );
						if ( array_search( $product_name, $product_added ) === false ) {
							$product_added[] = $product_name;
							if ( ! empty( $shipping_options[ $x ]['product_names'] ) ) {
								$shipping_options[ $x ]['product_names'] .= ', ' . $product_name;
							} else {
								$shipping_options[ $x ]['product_names'] = $product_name;
							}
						}
						if ( array_search( $label, $option_added ) === false ) {
							$option_added[]                                      = $label;
							$shipping_options[ $x ]['options'][ $label ]['name'] = $label;
						}
						$price[ $label ] = isset( $price[ $label ] ) ? $price[ $label ] : 0;
						if ( $this->individual() ) {
							$price[ $label ] += apply_filters( 'individual_cart_shipping_addition', $option['prices'][ $i ], $label );
						} else {
							$price[ $label ] = $option['prices'][ $i ] > $price[ $label ] ? $option['prices'][ $i ] : $price[ $label ];
						}
						$shipping_options[ $x ]['options'][ $label ]['price'] = $price[ $label ];
					}
				}
			}
		}
		if ( ! empty( $shipping_labels ) ) {
			$not_unique = $this->cart_get_most_appearing_shipping( $shipping_labels );
			$next_key   = max( array_keys( $shipping_options ) ) + 1;
			if ( ! empty( $not_unique ) ) {
				$current_options               = $this->array_get_max_value_keys( $not_unique );
				$shipping_options[ $next_key ] = $this->labels_to_options( $shipping_labels, $current_options );
			} else {
				$shippings = $this->front_labels( $shipping_labels );
				foreach ( $shippings as $shipping ) {
					$shipping_options[ $next_key ] = $shipping;
					$next_key                      += 1;
				}
			}
		}

		return $shipping_options;
	}

	/**
	 * Returns most appearing shipping label from given products
	 *
	 * @param type $products_array
	 *
	 * @return type
	 */
	function cart_get_most_appearing_shipping( $products_array ) {
		$shipping_labels_a = array();
		foreach ( $products_array as $cart_id => $shipping_labels ) {
			$shipping_labels_a = array_merge( $shipping_labels_a, $shipping_labels['labels'] );
		}
		$not_unique = array_count_values( $this->array_not_unique( array_filter( $shipping_labels_a ) ) );

		return $not_unique;
	}

	/**
	 * Returns most appearing same values keys
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	function array_get_max_value_keys( $array ) {
		$same_options = array();
		if ( ! empty( $array ) ) {
			$max = max( $array );
			foreach ( $array as $key => $value ) {
				if ( $value >= $max ) {
					$same_options[] = $key;
				}
			}
		}

		return $same_options;
	}

	/**
	 * Returns duplicate values from array
	 *
	 * @param type $raw_array
	 *
	 * @return type
	 */
	function array_not_unique( $raw_array ) {
		$dupes = array();
		natcasesort( $raw_array );
		reset( $raw_array );

		$old_key   = null;
		$old_value = null;
		foreach ( $raw_array as $key => $value ) {
			if ( $value === null ) {
				continue;
			}
			if ( $old_value !== null && strcasecmp( $old_value, $value ) === 0 ) {
				$dupes[ $old_key ] = $old_value;
				$dupes[ $key ]     = $value;
			}
			$old_value = $value;
			$old_key   = $key;
		}

		return $dupes;
	}

	/**
	 * Adds shipping options to checkout form
	 *
	 * @param string $content
	 * @param string $pre_name
	 *
	 * @return string
	 */
	function checkout_options_html( $content, $pre_name ) {
		if ( $pre_name == 'order_form_' || $pre_name == 'cart_' ) {
			$shipping_options = apply_filters( "checkout_shipping_options", '', $pre_name );
			if ( ! empty( $shipping_options ) ) {
				$content .= '<div class="form_section shipping-options-section">';
				$content .= '<div class="order_form_row row section_break"><h5 class="section-break"><strong>' . __( 'SHIPPING', 'ecommerce-product-catalog' ) . '</strong></h5></div>';
				$content .= $shipping_options;
				$content .= '</div>';
			}
		}

		return $content;
	}

	/**
	 * Adds custom payment options to checkout form
	 *
	 * @param type $content
	 * @param type $pre_name
	 *
	 * @return string
	 */
	function add_current( $content = '', $pre_name = 'cart_' ) {
		$settings = $this->settings();
		if ( $settings['mode'] == 'none' ) {
			return $content;
		}
		$shipping               = '';
		$shipping_options       = $this->selected_options( $pre_name );
		$first_shipping_options = reset( $shipping_options );
		if ( is_array( $shipping_options ) && ( count( $shipping_options ) > 1 || ( ( is_array( $first_shipping_options ) && is_array( $first_shipping_options['options'] ) ) && count( $first_shipping_options['options'] ) > 1 ) ) ) {
			$a            = 0;
			$shipping_num = count( $shipping_options );
			$name         = 'shipping';
			foreach ( $shipping_options as $key => $shipping_option ) {
				unset( $selected );
				if ( is_array( $shipping_option ) && isset( $shipping_option['options'] ) ) {

					$shipping .= '<div class="order_form_row row shipping-options">';
					$shipping .= '<div class="label">';
					if ( isset( $shipping_option['product_names'] ) && $shipping_num > 1 ) {
						$shipping .= $shipping_option['product_names'];
					}
					$shipping .= '</div>';
					$shipping .= '<div class="field">';
					if ( $shipping_num > 1 ) {
						$name = 'shipping_' . $a;
					}
					foreach ( $shipping_option['options'] as $option ) {
						if ( $option['price'] === '' ) {
							continue;
						}
						$esc_name = sanitize_title( $option['name'] );
						if ( ! isset( $selected ) ) {
							$selected = $esc_name;
						}
						$shipping .= '<div><input type="radio" value="' . $esc_name . '" data-price_effect="' . $option['price'] . '" name="' . $name . '" ' . checked( $esc_name, $selected, 0 ) . ' /> ';
						$shipping .= '<label><span>' . $option['name'] . '</span> <span>(' . price_format( $option['price'] ) . ')</span></label></div>';
					}
					$shipping .= '</div>';
					$shipping .= '</div>';
					$a        += 1;
				}
			}
		}

		return $content . $shipping;
	}

	/**
	 * Adds selected shipping cost to order handling
	 *
	 * @param type $handling
	 *
	 * @return type
	 */
	function selected_cost( $handling, $pre_name ) {
		if ( true || isset( $_POST['shipping'] ) || isset( $_POST['shipping_0'] ) ) {
			$shipping_options = $this->selected_options( $pre_name );
			$shipping_num     = count( $shipping_options );
			$a                = 0;
			foreach ( $shipping_options as $shipping_option ) {
				if ( is_array( $shipping_option ) && isset( $shipping_option['options'] ) ) {
					if ( $shipping_num > 1 ) {
						$selected = sanitize_text_field( $_POST[ 'shipping_' . $a ] );
					} else {
						$selected = sanitize_text_field( $_POST['shipping'] );
					}
					foreach ( $shipping_option['options'] as $option ) {
						$esc_name = sanitize_title( $option['name'] );
						if ( $selected == $esc_name ) {
							$handling += $option['price'];
							break;
						}
					}
					$a += 1;
				}
			}
		}

		return $handling;
	}

	/**
	 * Returns selected order shipping labels
	 *
	 * @param type $pre_name
	 *
	 * @return type
	 */
	function order_labels( $pre_name ) {
		$labels = '';
		if ( isset( $_POST['shipping'] ) || isset( $_POST['shipping_0'] ) ) {
			$shipping_options = $this->selected_options( $pre_name );
			$shipping_num     = count( $shipping_options );
			$a                = 0;
			foreach ( $shipping_options as $shipping_option ) {
				if ( is_array( $shipping_option ) && isset( $shipping_option['options'] ) ) {
					if ( $shipping_num > 1 ) {
						if ( ! empty( $labels ) ) {
							$labels .= ', ';
						}
						$labels .= sanitize_text_field( $_POST[ 'shipping_' . $a ] );
					} else {
						$labels = sanitize_text_field( $_POST['shipping'] );
					}
					$a += 1;
				}
			}
		}

		return $labels;
	}

	function shipping_total_table_row( $products_table, $price, $cart ) {
		$settings = $this->settings();
		if ( $settings['mode'] == 'none' ) {
			return $products_table;
		}
		if ( $price ) {
			$shipping_options = $this->selected_options( 'cart_' );
			$first_option     = reset( $shipping_options );
			if ( is_array( $shipping_options ) && ( is_array( $first_option ) && is_array( $first_option['options'] ) ) && ( count( $shipping_options ) <= 1 && count( $first_option['options'] ) <= 1 ) ) {
				$products_table         .= '<tr class="order-checkout-shipping">';
				$def_colspan            = 3;
				$shopping_cart_settings = get_shopping_cart_settings();
				if ( $shopping_cart_settings['cart_page_template'] == 'no_qty' ) {
					$def_colspan = 1;
				}
				if ( function_exists( 'is_ic_sku_enabled' ) && is_ic_sku_enabled() ) {
					$def_colspan ++;
				}
				$products_table .= '<td style="text-align:right" colspan="' . apply_filters( 'ic_cart_checkout_table_colspan', $def_colspan ) . '">';
				$products_table .= __( 'Shipping', 'ecommerce-product-catalog' );
				$products_table .= '</td>';
				$first_option   = reset( $first_option['options'] );
				$products_table .= '<td style="text-align:right">';
				if ( ! empty( $first_option['price'] ) ) {
					$products_table .= '+';
				}
				$products_table .= price_format( $first_option['price'], 1, 0, 0 );
				$products_table .= '<input type="radio" style="display: none;" value="' . sanitize_title( $first_option['name'] ) . '" data-price_effect="' . $first_option['price'] . '" name="shipping" checked />';
				$products_table .= '</td>';
				$products_table .= '</tr>';
			}
		}

		return $products_table;
	}

}

global $ic_cart_shipping;
$ic_cart_shipping = new ic_cart_shipping;

/**
 * Adds selected shipping cost to order handling
 *
 * @param type $handling
 *
 * @return type
 */
function ic_count_shipping_cost_payment( $handling, $pre_name ) {
	global $ic_cart_shipping;

	return $ic_cart_shipping->selected_cost( $handling, $pre_name );
}

if ( ! function_exists( 'get_current_checkout_shipping_options' ) ) {

	/**
	 * Returns current checkout shipping options array
	 *
	 * @param string $pre_name
	 *
	 * @return array
	 */
	function get_current_checkout_shipping_options( $pre_name ) {
		global $ic_cart_shipping;

		return $ic_cart_shipping->selected_options( $pre_name );
	}

}

/**
 * Returns selected order shipping labels
 *
 * @param type $pre_name
 *
 * @return type
 */
function ic_get_order_shipping_labels( $pre_name ) {
	global $ic_cart_shipping;

	return $ic_cart_shipping->order_labels( $pre_name );
}

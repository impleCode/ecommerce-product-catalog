<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages shopping form
 *
 * Here shopping form are defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-shopping-cart/includes
 * @author        Norbert Dreszer
 */
class ic_cart_checkout_form {

	function __construct() {
		add_filter( 'ic_formbuilder_form_beginning', array( __CLASS__, 'add_checkout_products' ), 5, 2 );
		add_filter( 'ic_formbuilder_form_beginning', array( __CLASS__, 'add_checkout_hidden_fields' ), 10, 2 );

		add_filter( 'payment_order_details', array( __CLASS__, 'payment_details' ) );
	}

	/**
	 * Adds ordered products to checkout form
	 *
	 * @param string $content
	 * @param string $pre_name
	 *
	 * @return string
	 */
	static function add_checkout_products( $content, $pre_name ) {
		if ( $pre_name == 'cart_' ) {
			$content .= shopping_cart_products();
		}

		return $content;
	}

	/**
	 * Adds shopping cart checkout hidden fields
	 *
	 * @param string $content
	 * @param string $pre_name
	 *
	 * @return string
	 */
	static function add_checkout_hidden_fields( $content, $pre_name ) {
		if ( $pre_name == 'cart_' ) {
			$cart_content = '<input hidden type="hidden" name="cart_content" value=\'' . ic_shopping_cart_content() . '\'>';
			$cart_type    = '<input hidden type="hidden" name="cart_type" value="order">';
			$content      .= apply_filters( 'cart_mail_hidden_fields', $cart_content . $cart_type );
		}

		return $content;
	}

	static function payment_details( $order ) {
		if ( true || isset( $_POST['cart_submit'] ) ) {
			$shopping_cart_settings = get_shopping_cart_settings();
			$cart_content           = ic_shopping_cart_content( true );
			$products_array         = shopping_cart_products_array( $cart_content );
			$total_net              = 0;
			$total_gross            = 0;
			$total_tax              = 0;
			$taxed                  = function_exists( 'is_ic_order_taxed' ) ? is_ic_order_taxed() : false;
			//$currency_settings		 = get_currency_settings();
			$tax_rate = function_exists( 'get_cart_tax_rate' ) ? get_cart_tax_rate() : 0;
			foreach ( $products_array as $cart_id => $p_quantity ) {
				$product_id            = cart_id_to_product_id( $cart_id );
				$order['product'][]    = apply_filters( 'cart_payment_product_name', get_product_name( $product_id ), $cart_id, $product_id );
				$order['product_id'][] = $product_id;
				$order['quantity'][]   = $p_quantity;
				$order['cart_id'][]    = $cart_id;
				//$product_price			 = apply_filters( 'shopping_cart_product_price', product_price( $product_id, 1 ), $cart_id, $p_quantity );
				$product_net_price = get_shopping_cart_product_price( $product_id, $cart_id, $p_quantity );
				$product_net_price = floatval( $product_net_price );

				if ( $taxed ) {
					if ( is_ic_tax_included() ) {
						$tax_rate_c    = $tax_rate['tax_rate'] / 100;
						$taxed_product = $product_net_price;
						//$product_net_price	 = ic_roundto( $product_net_price / (1 + $tax_rate_c), $tax_rate[ 'tax_rate_round' ] );
						$product_net_price = $product_net_price / ( 1 + $tax_rate_c );
						$product_tax       = $taxed_product - $product_net_price;
						if ( function_exists( 'ic_tax_round_per_item' ) && ic_tax_round_per_item() ) {
							$product_tax = ic_round_tax( $product_tax );
						}
					} else {
						$product_tax = ic_get_price_tax( $product_net_price, $product_id );
					}
					$order['tax'][] = $product_tax;
				}
				$order['sum'][]    = ic_payment_number_format( $product_net_price );
				$product_total_net = $product_net_price * $p_quantity;
				$taxed_total       = $product_total_net;
				if ( $taxed ) {
					if ( ! is_ic_tax_included() ) {
						$taxed_product = $product_net_price + $product_tax;
						$taxed_total   = $taxed_product * $p_quantity;
						//$taxed_product	 = ic_roundto( $product_net_price + $product_tax, $tax_rate[ 'tax_rate_round' ] );
					}
					$order['product_gross'][]       = ic_payment_number_format( $taxed_product );
					$order['product_total_net'][]   = ic_payment_number_format( $product_total_net );
					$order['product_total_gross'][] = ic_payment_number_format( $taxed_total );
					$order['product_total'][]       = ic_payment_number_format( $taxed_total );
					$order['product_tax_rate'][]    = ic_get_tax_rate( $product_id );
				} else {
					$order['product_total_net'][] = ic_payment_number_format( $product_total_net );
					$order['product_total'][]     = ic_payment_number_format( $product_total_net );
				}
				$total_net   += $product_total_net;
				$total_gross += $taxed_total;
				if ( $taxed ) {
					$total_tax += $product_tax * $p_quantity;
				}
			}
			if ( function_exists( 'ic_tax_round_per_item' ) && ! ic_tax_round_per_item() ) {
				$total_tax = ic_round_tax( $total_tax );
			}
			if ( is_ic_tax_included() && ! empty( $total_gross ) ) {
				$order_total = $total_gross;
			} else {
				$order_total = $total_net + $total_tax;
			}
			$order['total_net']    = ic_payment_number_format( $order_total - $total_tax );
			$order['taxed']        = ic_payment_number_format( $order_total );
			$order['tax']          = ic_payment_number_format( $total_tax );
			$order['currency']     = get_product_currency_code();
			$order['success_page'] = ic_get_permalink( $shopping_cart_settings['thank_you_page'] );
			$handling              = apply_filters( 'shopping_cart_order_handling', 0, 'cart_' );
			$order['handling']     = 0;
			if ( ! empty( $handling ) ) {
				$order['handling'] = ic_payment_number_format( $handling );
			}
			$order['total_taxed'] = ic_payment_number_format( $order['taxed'] + $order['handling'] );
		}

		return $order;
	}

}

$ic_cart_checkout_form = new ic_cart_checkout_form;

class ic_cart_checkout_form_email {

	function __construct() {
		add_filter( 'ic_formbuilder_admin_email', array( $this, 'modify_admin_email' ), 5, 2 );
		add_filter( 'ic_formbuilder_admin_email', 'strip_shortcodes', 99 );

		add_filter( 'ic_formbuilder_user_email', array( $this, 'modify_user_email' ), 5, 2 );
		add_filter( 'ic_formbuilder_user_email', 'strip_shortcodes', 99 );
	}

	/**
	 * Replaces customer_details shortcode in admin email template with order data
	 *
	 * @param string $message
	 * @param string $pre_name
	 *
	 * @return string
	 */
	function modify_admin_email( $message, $pre_name ) {
		if ( $pre_name == 'cart_' ) {
			$email_settings = get_shopping_cart_settings();

			$p           = ic_email_paragraph();
			$ep          = ic_email_paragraph_end();
			$new_message = wpautop( $email_settings['admin_email'] );
			$new_message = str_replace( '<p>', $p, $new_message );
			$order_data  = $this->products_summary( 'admin' );
			//$order_data	 .= $p . trim( $message, "<br>" ) . $ep;
			$order_data  .= $p . $message . $ep;
			$new_message = str_replace( '[customer_details]', $order_data, $new_message );

			return $new_message;
		}

		return $message;
	}

	/**
	 * Replaces customer_details shortcode in customer email template with order data
	 *
	 * @param string $message
	 * @param string $pre_name
	 *
	 * @return string
	 */
	function modify_user_email( $message, $pre_name ) {
		if ( $pre_name == 'cart_' ) {
			$email_settings = get_shopping_cart_settings();

			$p           = ic_email_paragraph();
			$ep          = ic_email_paragraph_end();
			$new_message = wpautop( $email_settings['user_email'] );
			$new_message = str_replace( '<p>', $p, $new_message );
			$order_data  = $this->products_summary( 'user' );
			//$order_data	 .= $p . trim( $message, "<br>" ) . $ep;
			$order_data  .= $p . $message . $ep;
			$new_message = str_replace( '[customer_details]', $order_data, $new_message );

			return $new_message;
		}

		return $message;
	}

	/**
	 * Returns order products summary for email
	 *
	 * @param string $message
	 * @param string $pre_name
	 *
	 * @return string
	 */
	function products_summary( $who = '' ) {
		$cart_content   = ic_shopping_cart_content( true );
		$products_array = shopping_cart_products_array( $cart_content );
		$pre_message    = '';
		$line           = '<br>';
		$total_net      = 0;
		$td             = ic_email_table_td();
		$etd            = ic_email_table_td_end();
		$pre_message    .= ic_email_table();
		$pre_message    .= ic_email_table_th();

		$pre_message .= apply_filters( 'ic_cart_checkout_email_name_header', ic_email_table_td_first() . __( 'Product name', 'ecommerce-product-catalog' ) . ic_email_table_td_end(), $products_array );
		if ( function_exists( 'is_ic_sku_enabled' ) && is_ic_sku_enabled() ) {
			$single_names = get_single_names();
			$pre_message  .= $td . str_replace( ':', '', $single_names['product_sku'] ) . $etd;
		}
		$pre_message .= $td . __( 'Quantity', 'ecommerce-product-catalog' ) . $etd;
		$pre_message .= $td . __( 'Price', 'ecommerce-product-catalog' ) . $etd;
		$pre_message .= $td . __( 'Subtotal', 'ecommerce-product-catalog' ) . $etd;
		$pre_message .= ic_email_table_th_end();
		global $ic_shopping_cart_totals;
		$ic_shopping_cart_totals['total'] = 0;
		foreach ( $products_array as $cart_id => $p_quantity ) {
			$product_id  = cart_id_to_product_id( $cart_id );
			$pre_message .= ic_email_table_tr();
			$pre_message .= apply_filters( 'ic_cart_checkout_email_name_td', ic_email_table_td_first() . apply_filters( 'cart_email_product_name', html_entity_decode( get_the_title( $product_id ), ENT_QUOTES, get_bloginfo( 'charset' ) ), $product_id, $cart_id ) . ic_email_table_td_end(), $product_id );
			if ( function_exists( 'is_ic_sku_enabled' ) && is_ic_sku_enabled() ) {
				$sku         = get_product_sku( $product_id );
				$pre_message .= $td . $sku . $etd;
			}
			$pre_message .= $td . $p_quantity . $etd;
			//$product_price	 = apply_filters( 'shopping_cart_product_price', product_price( $product_id, 1 ), $cart_id, $p_quantity );
			$product_price = get_shopping_cart_product_price( $product_id, $cart_id, $p_quantity );
			$pre_message   .= $td . price_format( $product_price, 1, 0 ) . $etd;
			$product_total = $product_price * $p_quantity;
			ic_cart_update_tax( $product_id, $product_total, $p_quantity, $cart_id );
			$pre_message                      .= $td . price_format( $product_total, 1, 0 ) . $etd;
			$pre_message                      .= ic_email_table_tr_end();
			$total_net                        += $product_total;
			$ic_shopping_cart_totals['total'] += $product_total;
		}
		$pre_message .= ic_email_table_end();
		$p           = ic_email_paragraph();
		$ep          = ic_email_paragraph_end();
		global $order_price_effect_data;
		if ( isset( $order_price_effect_data ) && ! empty( $order_price_effect_data['message'] ) ) {
			$pre_message .= $p . $order_price_effect_data['message'] . $ep;
			if ( ! empty( $order_price_effect_data['total'] ) ) {
				$total_net += $order_price_effect_data['total'];
			}
		}
		if ( function_exists( 'ic_count_shipping_cost_payment' ) ) {
			$shipping = ic_count_shipping_cost_payment( 0, 'cart_' );
			if ( ! empty( $shipping ) ) {
				$labels = ic_get_order_shipping_labels( 'cart_' );
				if ( ! empty( $labels ) ) {
					$labels = ' (' . $labels . ')';
				}
				$shipping_label = __( 'Shipping', 'ecommerce-product-catalog' ) . $labels;
			}
		}
		if ( is_ic_order_taxed() ) {
			//$currency_settings	 = get_currency_settings();
			$total_tax = ic_cart_get_tax();
			/*
			  if ( !empty( $currency_settings[ 'tax_included' ] ) ) {
			  $tax_rate	 = get_cart_tax_rate();
			  $tax_rate_c	 = $tax_rate[ 'tax_rate' ] / 100;
			  $total_net	 = ic_roundto( $total_net / (1 + $tax_rate_c), $tax_rate[ 'tax_rate_round' ] );
			  }
			 *
			 */
			if ( is_ic_tax_included() ) {
				$total_net = $total_net - $total_tax;
			}
			/*
			  if ( !empty( $order_price_effect_data[ 'total' ] ) ) {
			  $taxation_subect = $total_net - $order_price_effect_data[ 'total' ];
			  } else {
			  $taxation_subect = $total_net;
			  }
			 *
			 */
			//$total_tax	 = ic_get_price_tax( $taxation_subect );
			$order_total = $total_net + $total_tax + $shipping;
			$pre_message .= $p . __( 'Total Net:', 'ecommerce-product-catalog' ) . ' ' . price_format( $total_net, 1, 0 ) . $ep;
			$pre_message .= $p . __( 'Tax:', 'ecommerce-product-catalog' ) . ' ' . price_format( $total_tax, 1, 0 ) . $ep;
			if ( ! empty( $shipping_label ) ) {
				$pre_message .= $p . $shipping_label . ': ' . price_format( $shipping ) . $ep;
			}
			$pre_message .= $p . '<strong>' . __( 'Total Gross:', 'ecommerce-product-catalog' ) . ' ' . price_format( $order_total, 1 ) . '</strong>' . $ep;
		} else {
			$total_tax   = 0;
			$order_total = $total_net + $shipping;
			if ( ! empty( $shipping_label ) ) {
				$pre_message .= $p . $shipping_label . ': ' . price_format( $shipping ) . $ep;
			}
			$pre_message .= $p . '<strong>' . __( 'Order Total:', 'ecommerce-product-catalog' ) . ' ' . price_format( $order_total, 1 ) . '</strong>' . $ep;
		}
		$pre_message = apply_filters( 'cart_checkout_' . $who . '_product_data', $pre_message, $order_total, $total_tax, $p, $ep, $line );

		return $pre_message;
	}

}

global $ic_cart_checkout_form_email;
$ic_cart_checkout_form_email = new ic_cart_checkout_form_email;

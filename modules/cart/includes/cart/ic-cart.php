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
class ic_cart {

	function __construct() {
		if ( class_exists( 'ic_cached_cart' ) ) {
			new ic_cached_cart();
		}
		add_action( 'ic_cart_products_start', array( $this, 'clear_globals' ) );

		add_filter( 'ic_cart_checkout_default_columns', array( $this, 'delete_button' ), 2, 5 );
		add_filter( 'ic_cart_checkout_default_columns', array( $this, 'image_td' ), 5, 4 );
		add_filter( 'ic_cart_checkout_default_columns', array( $this, 'name_td' ), 6, 5 );
		add_filter( 'ic_cart_checkout_default_columns', array( $this, 'sku_td' ), 7, 2 );
		add_filter( 'ic_cart_checkout_default_columns', array( $this, 'qty_td' ), 7, 6 );
		add_filter( 'ic_cart_checkout_default_columns', array( $this, 'price_td' ), 8, 6 );
		add_filter( 'ic_checkout_products_table_end', array( $this, 'price_summary' ), 8, 4 );

		add_action( 'wp_ajax_nopriv_get_shopping_cart_product_price', array( $this, 'ajax_price' ) );
		add_action( 'wp_ajax_get_shopping_cart_product_price', array( $this, 'ajax_price' ) );
	}

	function clear_globals() {
		ic_delete_global( 'current_cart_total' );
		ic_delete_global( 'current_cart_tax' );
	}

	function product_table( $raw = 1, $price = true, $cart = 'cart_content', $settings = null ) {
		ic_save_global( 'inside_cart_products', 1 );
		if ( empty( $settings ) ) {
			$settings = get_shopping_cart_settings();
		}
		$cart_content   = $this->content( true, $cart );
		$products_array = ic_cart_products_array( $cart_content, $cart );
		$products_table = '<div class="before-cart-products">';
		$products_table .= apply_filters( 'ic_cart_products_before', '<input hidden type="hidden" name="' . $cart . '" value=\'' . $cart_content . '\'>', $products_array, $raw, $cart, $price );
		$products_table .= '</div>';
		$table_class    = 'cart-products';
		if ( $raw == 1 ) {
			$table_class .= ' raw';
		}
		$products_table .= '<table class="' . $table_class . '">';
		do_action( 'ic_cart_products_start', $products_array, $cart, $price );
		if ( $settings['cart_page_template'] == 'qty' ) {
			$products_table .= '<thead><tr>';
			$products_table .= apply_filters( 'ic_checkout_products_table_start', '', $products_array, $cart, $price );
			if ( ! empty( $settings['cart_page_image'] ) ) {
				$products_table .= '<th class="th_image"></th>';
			}

			$products_table .= apply_filters( 'cart_checkout_table_header_product_name', '<th class="th_name">' . __( 'Product name', 'ecommerce-product-catalog' ) . '</th>' );
			if ( function_exists( 'is_ic_sku_enabled' ) && is_ic_sku_enabled() ) {
				$single_names   = get_single_names();
				$products_table .= '<th class="th_sku">' . str_replace( ':', '', $single_names['product_sku'] ) . '</th>';
			}
			$products_table .= '<th class="th_qty">' . __( 'Quantity', 'ecommerce-product-catalog' ) . '</th>';
			if ( $price !== false ) {
				$products_table .= '<th class="th_price">' . __( 'Unit Price', 'ecommerce-product-catalog' ) . '</th>';
				$products_table .= apply_filters( 'ic_cart_checkout_additional_headers', '', $raw, $price, $cart, $settings );
				$products_table .= '<th class="th_total">' . __( 'Total', 'ecommerce-product-catalog' ) . '</th>';
			} else {
				$products_table .= apply_filters( 'ic_cart_checkout_additional_headers', '', $raw, $price, $cart, $settings );
			}
			$products_table .= '</tr></thead>';
		}
		$products_table .= '<tbody>';
		$total          = 0;
		if ( $raw == 1 ) {
			foreach ( $products_array as $cart_id => $quantity ) {
				if ( $cart_id != '' ) {
					$product_id = cart_id_to_product_id( $cart_id );
					if ( ! is_ic_product( $product_id ) ) {
						continue;
					}
					ic_set_product_id( $product_id );
					$quantity = apply_filters( 'ic_cart_qty', $quantity, $product_id );
					if ( $price !== false ) {
						$product_price = get_shopping_cart_product_price( $product_id, $cart_id, $quantity );
						$price         = $product_price;
					}
					$products_table .= '<tr>';
					$products_table .= apply_filters( 'ic_cart_checkout_default_columns', '', $cart_id, $quantity, $settings, $raw, $price );
					$products_table .= '</tr>';
					ic_reset_product_id();
				}
			}
		} else {
			$style = '';
			foreach ( $products_array as $cart_id => $quantity ) {
				if ( $cart_id != '' ) {
					$style      = 'style="display: none"';
					$product_id = cart_id_to_product_id( $cart_id );
					if ( ! is_ic_product( $product_id ) ) {
						continue;
					}
					ic_set_product_id( $product_id );
					$quantity = apply_filters( 'ic_cart_qty', $quantity, $product_id );
					if ( $price !== false ) {
						$product_price = get_shopping_cart_product_price( $product_id, $cart_id, $quantity );
						$price         = $product_price;
					}
					$products_table .= '<tr>';
					$products_table .= apply_filters( 'ic_cart_checkout_default_columns', '', $cart_id, $quantity, $settings, $raw, $price );
					$products_table .= '</tr>';
					ic_reset_product_id();
				}
			}
			$products_table = apply_filters( 'ic_after_shopping_cart_products', $products_table, $products_array );
			if ( is_ic_product_listing_enabled() ) {
				$no_products_text = sprintf( __( 'No products. <a href="%s">Go back</a> and choose some.', 'ecommerce-product-catalog' ), product_listing_url() );
			} else {
				$no_products_text = __( 'No products.', 'ecommerce-product-catalog' );
			}
			if ( isset( $settings['empty_cart'] ) && $settings['empty_cart'] != '' ) {
				$no_products_text = $settings['empty_cart'];
			}
			$products_table .= '<tr class="no-products" ' . $style . '><td colspan="4">' . apply_filters( 'ic_no_products_text', $no_products_text ) . '</td></tr>';
		}

		$products_table .= apply_filters( 'ic_checkout_products_table_end', '', $price, $cart, $settings );
		$products_table .= '</tbody></table>';
		do_action( 'ic_cart_products_end', $products_array, $cart, $price );
		ic_delete_global( 'inside_cart_products' );

		return $products_table;
	}

	function delete_button( $products_table, $cart_id, $quantity, $settings, $raw ) {
		if ( ! $raw && isset( $settings['cart_page_template'] ) && $settings['cart_page_template'] == 'no_qty' ) {
			$product_id     = cart_id_to_product_id( $cart_id );
			$products_table .= '<td><input hidden type="hidden" name="p_id[]" class="product_id" value="' . $cart_id . '">';
			$p_price        = product_price( $product_id, 1 );
			if ( ! empty( $p_price ) ) {
				$p_price = price_format( $p_price, 1, 0 );
			}
			$products_table .= '<input class="edit-product-quantity" data-p_id="' . $product_id . '" data-price="' . $p_price . '" type="hidden" name="p_quantity[]" value="1">';
			$products_table .= '<span class="delete_product" p_id="' . $product_id . '"></span></td>';
		}

		return $products_table;
	}

	function image_td( $products_table, $cart_id, $quantity, $settings ) {
		$product_id = cart_id_to_product_id( $cart_id );
		if ( isset( $settings['cart_page_template'] ) && $settings['cart_page_template'] == 'no_qty' ) {
			$products_table .= '<td class="td-image">' . get_product_image( $product_id ) . '</td>';
		} else if ( ! empty( $settings['cart_page_image'] ) ) {
			$products_table .= '<td class="td-image">' . $this->product_image( $cart_id ) . '</td>';
		}

		return $products_table;
	}

	function sku_td( $products_table, $cart_id ) {
		if ( function_exists( 'is_ic_sku_enabled' ) && is_ic_sku_enabled() ) {
			$product_id     = cart_id_to_product_id( $cart_id );
			$sku            = get_product_sku( $product_id );
			$products_table .= '<td class="td-sku">' . $sku . '</td>';
		}

		return $products_table;
	}

	function name_td( $products_table, $cart_id, $quantity, $settings, $raw ) {
		$product_id = cart_id_to_product_id( $cart_id );
		$permalink  = get_product_url( $product_id );
		if ( ! empty( $permalink ) ) {
			$product_name_url = '<a href="' . $permalink . '">' . get_product_name( $product_id ) . '</a>';
		} else {
			$product_name_url = get_product_name( $product_id );
		}
		if ( $raw ) {
			$filter_name = 'cart_summary_product_name';
		} else {
			$filter_name = 'cart_product_name';
		}
		$products_table .= apply_filters( 'cart_summary_product_name_td', '<td class="td-name">' . apply_filters( $filter_name, $product_name_url, $cart_id, $product_id ) . '</td>', $product_id );

		return $products_table;
	}

	function qty_td( $products_table, $cart_id, $quantity, $settings, $raw, $product_price ) {
		if ( isset( $settings['cart_page_template'] ) && $settings['cart_page_template'] == 'qty' ) {
			if ( $raw ) {
				$qty_field = $quantity;
			} else {
				$product_id = cart_id_to_product_id( $cart_id );
				$qty_field  = '<input hidden type="hidden" name="p_id[]" class="product_id" value="' . $cart_id . '"><span class="delete_product" p_id="' . $product_id . '"></span> ';
				$min        = apply_filters( 'ic_cart_min_qty', 0, $product_id, $cart_id );
				$max        = apply_filters( 'ic_cart_max_qty', '', $product_id, $cart_id );
				$p_price    = '';
				if ( ! empty( $product_price ) ) {
					$p_price = price_format( $product_price, 1, 0 );
				}
				$qty_box   = '<input class="edit-product-quantity" data-p_id="' . $product_id . '" data-price="' . $p_price . '" type="number" min="' . $min . '" max="' . $max . '" step="1" name="p_quantity[]" value="' . $quantity . '">';
				$qty_field .= apply_filters( 'shopping_cart_quantity_box', $qty_box, $cart_id );
				$quantity  = $qty_field;
			}
			$products_table .= '<td class="td-qty">' . $quantity . '</td>';
		}

		return $products_table;
	}

	function price_td( $products_table, $cart_id, $quantity, $settings, $raw, $product_price ) {
		$product_id = cart_id_to_product_id( $cart_id );
		if ( $product_price !== '' && $product_price !== false ) {
			$price   = apply_filters( 'shopping_cart_product_price_second_mod', $product_price, $cart_id, $quantity );
			$p_total = $price * $quantity;
			ic_save_global( 'current_cart_total', ic_get_global( 'current_cart_total' ) + $p_total );
			if ( is_ic_order_taxed() ) {
				ic_cart_update_tax( $product_id, $p_total, $quantity, $cart_id );
			}

			if ( isset( $settings['cart_page_template'] ) && $settings['cart_page_template'] == 'qty' ) {
				$products_table .= '<td class="td-price">';
				$products_table .= apply_filters( 'ic_cart_td_price', price_format( $price, 1, 0 ), $cart_id );
				$products_table .= '</td>';
			}
			$products_table .= apply_filters( 'ic_cart_checkout_additional_columns', '', $product_id, $cart_id, $quantity, $raw );
			if ( $raw ) {
				$products_table .= '<td class="td-total"><input type="hidden" hidden name="product_price_' . sanitize_title( $cart_id ) . '" value="' . price_format( $price, 1, 0 ) . '">' . apply_filters( 'ic_cart_td_total', price_format( $p_total, 1, 0 ), $cart_id ) . '</td>';
			} else {
				$products_table .= '<td class="' . $product_id . '_total product_total td-total">' . apply_filters( 'ic_cart_td_total', price_format( $p_total, 1, 0 ), $cart_id );
				$sep_settings   = get_currency_settings();
				$products_table .= '<input type="hidden" hidden class="dec_sep" value="' . $sep_settings['dec_sep'] . '" /><input type="hidden" hidden class="th_sep" value="' . $sep_settings['th_sep'] . '" />';
				$products_table .= '</td>';
			}
		} else {
			$products_table .= apply_filters( 'ic_cart_checkout_additional_columns', '', $product_id, $cart_id, $quantity, $raw );
		}

		return $products_table;
	}

	function price_summary( $products_table, $price, $cart, $settings ) {
		if ( ! empty( $price ) && function_exists( 'get_cart_tax_rate' ) ) {
			global $ic_shopping_cart_totals;
			$sep_settings = get_currency_settings();
			$tax_rate     = get_cart_tax_rate();
			$tax_rate_c   = floatval( $tax_rate['tax_rate'] ) / 100;
			$total        = apply_filters( 'ic_cart_order_total', ic_get_global( 'current_cart_total' ) );
			/*
			  if ( !empty( $sep_settings[ 'tax_included' ] ) ) {
			  $total = ic_roundto( $total / (1 + $tax_rate_c), $tax_rate[ 'tax_rate_round' ] );
			  }
			 *
			 */
			$tax = ic_cart_get_tax();
			if ( is_ic_tax_included() ) {
				$total = $total - $tax;
			}
			//$tax		 = ic_roundto( $total * $tax_rate_c, $tax_rate[ 'tax_rate_round' ] );
			$currency = product_currency();
			$label    = __( 'Total', 'ecommerce-product-catalog' );
			if ( ! empty( $tax_rate['tax_rate'] ) ) {
				$label = __( 'Total NET', 'ecommerce-product-catalog' );
			}
			$products_table .= '<tr class="section_sep">';
			$def_colspan    = 3;
			if ( $settings['cart_page_template'] == 'no_qty' ) {
				$def_colspan = 1;
			}
			if ( function_exists( 'is_ic_sku_enabled' ) && is_ic_sku_enabled() ) {
				$def_colspan ++;
			}
			$colspan                          = apply_filters( 'ic_cart_checkout_table_colspan', $def_colspan );
			$products_table                   .= '<td colspan="' . $colspan . '"></td><td></td></tr>';
			$ic_shopping_cart_totals['total'] = $total;
			$ic_shopping_cart_totals['tax']   = $tax;
			$total_net                        = price_format( $total, 1, 0 );
			$products_table                   .= apply_filters( 'ic_before_cart_total', '', $total, $tax );
			if ( ! empty( $total_net ) ) {
				$products_table .= '<tr class="new_section"><td colspan="' . $colspan . '" class="currency-td"><input type="hidden" hidden name="total_net" value="' . price_format( $total, 1, 0 ) . '" />' . $label . ' = ' . $currency . '</td><td class="total_net">' . apply_filters( 'cart_table_total_net', $total_net, $total ) . '</td></tr>';
			}
			if ( ! empty( $tax_rate['tax_rate'] ) ) {
				//$current_cart_tax	 = ic_get_global( 'current_cart_tax' );
				$current_cart_tax = ic_cart_get_tax( true );
				if ( ! empty( $current_cart_tax ) && is_array( $current_cart_tax ) ) {
					foreach ( $current_cart_tax as $rate => $tax_sum ) {
						$products_table .= '<tr class="order-checkout-tax"><td colspan="' . $colspan . '" class="currency-vat">' . $tax_rate['tax_label'] . ' ' . floatval( $rate ) . '% = ' . $currency . '</td><td class="total_vat">' . apply_filters( 'cart_table_total_tax', price_format( $tax_sum, 1, 0 ), $tax_sum ) . '</td></tr>';
					}
				}
				//$products_table	 .= '<tr class="order-checkout-tax"><td colspan="' . $colspan . '" class="currency-vat"><input type="hidden" hidden class="vat_rate" value="' . $tax_rate[ 'tax_rate' ] . '" /><input type="hidden" hidden class="vat_included" value="' . $tax_rate[ 'tax_included' ] . '" /><input type="hidden" hidden class="vat_rate_round" value="' . $tax_rate[ 'tax_rate_round' ] . '" /><input typ="hidden" hidden name="total_tax" value="' . price_format( $tax, 1, 0 ) . '" />' . $tax_rate[ 'tax_label' ] . ' ' . $tax_rate[ 'tax_rate' ] . '% = ' . $currency . '</td><td class="total_vat">' . apply_filters( 'cart_table_total_tax', price_format( $tax, 1, 0 ), $tax ) . '</td></tr>';
				$total_with_tax = $total + $tax;
				$products_table .= '<tr class="section_sep"><td td colspan="' . $colspan . '"></td><td></td></tr>';
				$products_table .= '<tr class="new_section"><td colspan="' . $colspan . '" class="currency-gross"><input type="hidden" hidden name="total_with_tax" value="' . price_format( $total_with_tax, 1, 0 ) . '" />' . __( 'Total GROSS', 'ecommerce-product-catalog' ) . ' = ' . $currency . '</td><td class="total_gross">' . apply_filters( 'cart_table_total_gross', price_format( $total_with_tax, 1, 0 ), $total_with_tax ) . '</td></tr>';
			}
			$products_table .= apply_filters( 'ic_after_cart_total', '', $total, $tax, $cart, $settings );
		}

		return $products_table;
	}

	function product_image( $cart_id ) {
		$image_html = apply_filters( 'ic_cart_product_image', $cart_id );
		if ( empty( $image_html ) ) {
			$product_id = cart_id_to_product_id( $cart_id );
			$image_html = get_product_image( $product_id );
		}

		return $image_html;
	}

	function product_price( $product_id, $cart_id, $quantity ) {
		$product_price = apply_filters( 'shopping_cart_product_price', product_price( $product_id, 1 ), $cart_id, $quantity );

		return $product_price;
	}

	function ajax_price() {
		$product_id = cart_id_to_product_id( $_POST['product_id'] );
		$cart_id    = $product_id;
		foreach ( $_POST['selected_variation'] as $var_value ) {
			$var_value = ( $var_value == 'not_selected' ) ? '' : $var_value;
			$cart_id   .= '_' . $var_value;
		}
		$product_price = get_shopping_cart_product_price( $product_id, $cart_id, $_POST['quantity'] );
		echo $product_price;
		wp_die();
	}

	function content( $json = null, $cart = 'cart_content' ) {
//$cart_content = '';
		$cart_content = array();
		global $cart_just_created;
		if ( ! isset( $cart_just_created ) && $json ) {
			$save_cart = false;
			if ( isset( $_POST['p_id'] ) ) {
				$product_variations_settings = get_product_variations_settings();
				foreach ( $_POST['p_id'] as $num => $product_id ) {
					$product_id                 = get_cart_id_without_variations( $product_id );
					$current_product_variations = get_current_product_variations_string( $product_id, $product_variations_settings );
					if ( $current_product_variations ) {
						$product_id = create_variation_id( $cart_content, $product_id, $current_product_variations );
						$product_id .= $current_product_variations;
					}
					/*
					  for ( $i = 1; $i <= $_POST[ 'p_quantity' ][ $num ]; $i++ ) {
					  $cart_content .= $product_id . ',';
					  }
					 */
					$cart_content[ $product_id ] = intval( $_POST['p_quantity'][ $num ] );
				}
				do_action( 'ic_cart_updated', $cart_content );
				/* foreach ( $_POST[ 'p_id' ] as $num => $_product_id ) {
				  $product_id		 = strval( $_product_id );
				  $quantity		 = intval( $_POST[ 'p_quantity' ][ $num ] );
				  $cart_content	 = ic_cart_insert( $product_id, $quantity, 'cart_content' );
				  } */
				$save_cart = true;
			} else if ( isset( $_POST[ $cart ] ) ) {
				$current_product    = isset( $_POST['current_product'] ) ? intval( $_POST['current_product'] ) : '';
				$additional_product = isset( $_POST['additional_product'] ) ? $_POST['additional_product'] : '';
				$current_product_id = $current_product;
				$current_quantity   = isset( $_POST['current_quantity'] ) ? intval( $_POST['current_quantity'] ) : 1;
				//$cart_content				 = isset( $_POST[ $cart ] ) ? ic_decode_json_cart( stripslashes( $_POST[ $cart ] ), false ) : array();
				$cart_content = ic_decode_json_cart( ic_cart_get( $cart ) );
				//$cart_content				 = isset( $_SESSION[ $cart ] ) ? ic_decode_json_cart( stripslashes( $_SESSION[ $cart ] ), false ) : '';
				$product_variations_settings = get_product_variations_settings();

				$current_product_variations = get_current_product_variations_string( $current_product_id, $product_variations_settings );
				$current_product            = create_variation_id( $cart_content, $current_product_id, $current_product_variations );
				if ( $current_product_variations ) {
					$current_product .= $current_product_variations;
				}
				/*
				  if ( $current_quantity > 1 ) {
				  $current_products = '';
				  for ( $i = 1; $i <= $current_quantity; $i++ ) {
				  $current_products .= ',' . $current_product;
				  }
				  $current_product = $current_products;
				  }
				 */
				if ( $current_product != '' ) {
					//$cart_content = $cart_content . ',' . $current_product;
					if ( isset( $cart_content[ $current_product ] ) ) {
						$cart_content[ $current_product ] += $current_quantity;
					} else {
						$cart_content[ $current_product ] = $current_quantity;
					}
					do_action( 'ic_cart_added', $current_product, $cart_content );
					if ( ! empty( $additional_product ) ) {
						foreach ( $additional_product as $additional_product_id ) {
							$current_additional_product_variations = get_current_product_variations_string( $additional_product_id, $product_variations_settings );
							$additional_product_id                 = create_variation_id( $cart_content, $additional_product_id, $current_additional_product_variations );
							if ( $current_additional_product_variations ) {
								$additional_product_id .= $current_additional_product_variations;
							}
							if ( isset( $cart_content[ $additional_product_id ] ) ) {
								$cart_content[ $additional_product_id ] += 1;
							} else {
								$cart_content[ $additional_product_id ] = 1;
							}
							do_action( 'ic_cart_added', $additional_product_id, $cart_content );
						}
					}
				} else {
					$cart_content = $cart_content;
				}
				$save_cart = true;
			} else if ( is_ic_cart_initialized( $cart ) ) {
				$cart_content = ic_decode_json_cart( ic_cart_get( $cart ) );
			}
			/* else if ( isset( $_POST[ 'current_product' ] ) ) {
			  $product_id	 = intval( $_POST[ 'current_product' ] );
			  $quantity	 = 1;
			  if ( $_POST[ 'current_quantity' ] ) {
			  $quantity = intval( $_POST[ 'current_quantity' ] );
			  }
			  $cart_content = ic_cart_insert( $product_id, $quantity, 'cart_content' );
			  } else {
			  $cart_content = ic_cart_get( 'cart_content' );
			  } */
			//$cart_content	 = implode( ',', array_filter( explode( ',', $cart_content ) ) );
			$cart_content = array_filter( $cart_content );
			$cart_content = $this->filter( $cart_content, null, $cart );
			if ( ! isset( $_POST['cart_type'] ) && $save_cart ) {
				//$_SESSION[ $cart ] = ic_encode_string_cart( $cart_content );
				ic_cart_save( ic_encode_string_cart( $cart_content ), $cart );
				//session_write_close();
			}
			if ( $json ) {
				$cart_content      = ic_encode_string_cart( $cart_content );
				$cart_just_created = $cart_content;
			}
		} else {
			$cart_content = $cart_just_created;
		}

		return $cart_content;
	}

	function filter( $cart_content, $find_value = null, $cart = null ) {
		if ( ! empty( $cart_content ) ) {
			$cart_content = ic_cart_products_array( $cart_content, $cart );
			if ( ! isset( $find_value ) ) {
				foreach ( $cart_content as $value => $quantity ) {
					$no_var_value = get_cart_id_without_variation_id( $value );
					if ( $no_var_value == $value ) {
						continue;
					}
					foreach ( $cart_content as $sub_value => $sub_quantity ) {
						$var_value = array_filter( get_variation_value_from_cart_id( $sub_value ) );
						if ( empty( $var_value ) ) {
							continue;
						}
						$no_var_sub_value = get_cart_id_without_variation_id( $sub_value );
						if ( $value != $sub_value && strpos( strval( $no_var_value ), strval( $no_var_sub_value ) ) !== false ) {
							unset( $cart_content[ $value ] );
							$cart_content[ $sub_value ] = $quantity + $sub_quantity;
							break;
						}
					}
				}

				return apply_filters( 'filter_ic_cart', $cart_content );
			} else {
				foreach ( $cart_content as $sub_value => $sub_quantity ) {
					if ( $find_value != $sub_value && strpos( $find_value, $sub_value ) !== false ) {
						$find_value = $sub_value;
						break;
					}
				}

				return $find_value;
			}
		}

		return apply_filters( 'filter_ic_cart_empty', $cart_content );
	}

}

global $ic_cart;
$ic_cart = new ic_cart;

function ic_cart_products( $raw = 1, $price = true, $cart = 'cart_content', $settings = null ) {
	global $ic_cart;

	return $ic_cart->product_table( $raw, $price, $cart, $settings );
}

if ( ! function_exists( 'get_shopping_cart_product_price' ) ) {

	function get_shopping_cart_product_price( $product_id, $cart_id, $quantity ) {
		global $ic_cart;

		return $ic_cart->product_price( $product_id, $cart_id, $quantity );
	}

}

function ic_cart_content( $json = null, $cart = 'cart_content' ) {
	global $ic_cart;

	return $ic_cart->content( $json, $cart );
}

/**
 * Returns cart content in array cart_id => qty
 *
 * @param type $cart_content
 * @param type $cart
 *
 * @return type
 */
function ic_cart_products_array( $cart_content = null, $cart = null ) {
	if ( $cart_content == '' ) {
		$cart_content = ic_cart_content( true, $cart );
	}
	$product_array = $cart_content;
	if ( ! empty( $cart_content ) ) {
		if ( is_ic_json_cart( $cart_content ) ) {
			$product_array = json_decode( $cart_content, true );
		} else if ( ! is_array( $cart_content ) ) {
			$product_array = array_count_values( explode( ',', $cart_content ) );
		}
	} else {
		$product_array = array();
	}

	return $product_array;
}

function ic_decode_json_cart( $cart_content, $count = false, $filter = false ) {
	if ( is_ic_json_cart( $cart_content ) ) {
		$json_content = json_decode( $cart_content, true );
		$item_count   = 0;
		if ( $filter ) {
			$cart_content = array();
			global $ic_cart;
			foreach ( $json_content as $value => $quantity ) {

				$value = $ic_cart->filter( $json_content, $value );

				/*
				  for ( $i = 1; $i <= $quantity; $i++ ) {
				  if ( !empty( $cart_content ) ) {
				  $cart_content .= ',';
				  }
				  $cart_content .= $value;
				  }
				 */
				$cart_content[ $value ] = $quantity;
				$item_count             += $quantity;
			}
		} else {
			$cart_content = $json_content;
			$item_count   = count( $cart_content );
		}
		if ( $count ) {
			$temp_content['content'] = $cart_content;
			$temp_content['count']   = $item_count;
			$cart_content            = $temp_content;
		}
	} else if ( $count ) {
		if ( ! is_array( $cart_content ) ) {
			$cart_content = array();
		}
		$content_array           = array_filter( $cart_content );
		$item_count              = count( $content_array );
		$temp_content['content'] = $cart_content;
		$temp_content['count']   = $item_count;
		$cart_content            = $temp_content;
	}
	if ( is_string( $cart_content ) ) {
		$cart_content = array();
	}

	return $cart_content;
}

function ic_encode_string_cart( $cart_content, $filter = false ) {
	if ( ! empty( $cart_content ) && ! is_ic_json_cart( $cart_content ) ) {
		$cart_content = ic_cart_products_array( $cart_content );
		if ( $filter ) {
			global $ic_cart;
			$cart_content = $ic_cart->filter( $cart_content );
		}

		$cart_content = json_encode( $cart_content );
	}
	if ( empty( $cart_content ) ) {
		$cart_content = '';
	}

	return $cart_content;
}

if ( ! function_exists( 'cart_id_to_product_id' ) ) {

	function cart_id_to_product_id( $cart_id ) {
		$product_id = $cart_id;
		if ( strpos( $cart_id, '::' ) !== false ) {
			$temp       = explode( '::', $cart_id );
			$temp       = explode( '_', $temp[1] );
			$product_id = $temp[0];
		} else if ( strpos( $cart_id, '_' ) !== false ) {
			$temp       = explode( '_', $cart_id );
			$product_id = $temp[0];
		}

		return intval( $product_id );
	}

}

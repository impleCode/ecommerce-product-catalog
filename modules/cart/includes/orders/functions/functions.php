<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages digital orders functions
 *
 * Here all plugin functions are defined and managed.
 *
 * @version        1.0.0
 * @package        digital-products-order/functions
 * @author        Norbert Dreszer
 */
function ic_available_payment_status() {
	$status              = array();
	$status['completed'] = __( 'Complete', 'ecommerce-product-catalog' );
	$status['pending']   = __( 'Pending', 'ecommerce-product-catalog' );

	return apply_filters( 'ic_payment_statuses', $status );
}

function ic_product_edit_url( $product_id ) {
	$site    = site_url();
	$product = $site . '/wp-admin/post.php?post=' . $product_id . '&action=edit';

	return $product;
}

if ( ! function_exists( 'ic_order_details_fields' ) ) {
	function ic_order_details_fields() {
		$fields = array(
			'status',
			'date',
			'name',
			'email',
			'billing_name',
			'tax_code',
			'street',
			'postcode',
			'city',
			'country',
			'country_code',
			'shipping_email',
			'currency',
			'vatid',
			'vat_name',
			'vat_address',
			'vat_country',
			'vat_ver_id'
		);

		return $fields;
	}
}


function ic_order_product_fields() {
	$fields = array( 'product_id', 'product_name', 'product_quantity', 'product_price', 'product_summary' );

	return $fields;
}

/**
 * Returns auto order product array
 *
 * @param int $order_id
 *
 * @return array Contains product_id, product_name, product_quantity, product_price, product_summary keys
 */
function ic_get_order_products( $order_id ) {
	$fields         = ic_order_product_fields();
	$order_products = implecode_array_variables_init( $fields, get_post_meta( $order_id, '_order_products', true ) );

	return $order_products;
}

/**
 * Returns manual order product ids array
 *
 * @param int $order_id
 *
 * @return array of product ids
 */
function ic_get_manual_order_products( $order_id ) {
	$order_products  = ic_get_all_manual_order_products( $order_id );
	$custom_products = array();
	if ( is_array( $order_products ) ) {
		foreach ( $order_products as $key => $product ) {
			if ( $product["id"] != '' ) {
				$custom_products[ $key ] = $product;
			}
		}
	}

	return $custom_products;
}

function ic_get_all_manual_order_products( $order_id ) {
	$order_products = get_post_meta( $order_id, 'manual_order_product', true );
	if ( ! is_array( $order_products ) ) {
		$order_products = array();
	}

	return $order_products;
}

/**
 * Returns all order products ids (auto and manual)
 *
 * @param int $order_id
 *
 * @return array
 */
function ic_get_order_product_ids( $order_id ) {
	$order_products        = ic_get_order_products( $order_id );
	$manual_order_products = ic_get_manual_order_products( $order_id );
	$product_ids           = array();
	if ( isset( $order_products['product_id'] ) && ! empty( $order_products['product_id'] ) ) {
		if ( is_array( $order_products['product_id'] ) ) {
			$product_ids = $order_products['product_id'];
		} else {
			$product_ids[] = $order_products['product_id'];
		}
	}
	if ( ! empty( $manual_order_products ) ) {
		foreach ( $manual_order_products as $product ) {
			if ( ! empty( $product['id'] ) ) {
				$product_ids[] = $product['id'];
			}
		}
	}

	return $product_ids;
}

function ic_get_custom_manual_order_products( $order_id ) {
	$order_products  = ic_get_all_manual_order_products( $order_id );
	$custom_products = array();
	if ( ! empty( $order_products ) ) {
		foreach ( $order_products as $product ) {
			if ( $product["id"] == '' ) {
				$custom_products[] = $product;
			}
		}
	}

	return $custom_products;
}

function ic_get_digital_product_name( $product_id ) {
	$product_name = get_the_title( $product_id );
	if ( $product_name == '' ) {
		$order_id       = explode( '_', $product_id );
		$order_id       = $order_id[3];
		$order_products = ic_get_custom_manual_order_products( $order_id );
		foreach ( $order_products as $product ) {
			if ( $product['c_id'] == $product_id ) {
				$product_name = $product['name'];
			}
		}
	}

	return $product_name;
}

function ic_add_row_button() {
	?>
    <div class="add-digital-product">
        <input type="button" id="add-digital-product" class="button" name="add_product"
               value="<?php _e( 'Add Product', 'ecommerce-product-catalog' ) ?>"/>
        <input type="button" id="add-custom-digital-product" class="button" name="add_custom_product"
               value="<?php _e( 'Add Custom Product', 'ecommerce-product-catalog' ) ?>"/></div>
	<?php
}

function ic_get_manual_products( $manual_order_product, $custom_id = null ) {
	$manual_products = array();
	if ( ! empty( $manual_order_product ) ) {
		foreach ( $manual_order_product as $manual_product ) {
			if ( ! empty( $manual_product["id"] ) ) {
				$manual_product_ids[] = $manual_product["id"];
			} else {
				if ( ! empty( $custom_id ) ) {
					$custom_manual_products[] = $manual_product["name"] . '|' . $manual_product["c_id"];
				} else {
					$custom_manual_products[] = $manual_product["name"];
				}
			}
		}
		$manual_product_ids                        = isset( $manual_product_ids ) ? implode( ',', $manual_product_ids ) : '';
		$custom_manual_products                    = isset( $custom_manual_products ) ? implode( ',', $custom_manual_products ) : '';
		$manual_products['manual_product_ids']     = $manual_product_ids;
		$manual_products['custom_manual_products'] = $custom_manual_products;
	}

	return $manual_products;
}

function ic_should_digital_order_be_taxed( $order_id ) {
	$return = false;
	if ( function_exists( 'eu_tax_system_enabled' ) && eu_tax_system_enabled() ) {
		$payment_details                 = ic_get_order_payment_details( $order_id );
		$payment_details['country_code'] = ( isset( $payment_details['country_code'] ) && ! empty( $payment_details['country_code'] ) ) ? $payment_details['country_code'] : $payment_details['country'];
		if ( is_eu_country( $payment_details['country_code'] ) ) {
			if ( ! empty( $payment_details['vatid'] ) && ! empty( $payment_details['vat_country'] ) && ! empty( $payment_details['vat_ver_id'] ) ) {
				if ( is_home_country( $payment_details['vat_country'] ) ) {
					$return = true;
				}
			} else {
				$return = true;
			}
		}
	}

	return $return;
}

if ( ! function_exists( 'ic_get_order_payment_details' ) ) {
	/**
	 * Returns order payment details
	 *
	 * @param int $order_id
	 *
	 * @return array
	 */
	function ic_get_order_payment_details( $order_id ) {
		return ic_sanitize_order_payment_details( ic_decode_payment_details( get_post_meta( $order_id, '_payment_details', true ) ) );
	}
}


function ic_sanitize_order_payment_details( $payment_details ) {
	$fields                      = ic_order_details_fields();
	$fields[]                    = 'address';
	$payment_details             = implecode_array_variables_init( $fields, $payment_details );
	$payment_details['currency'] = ! empty( $payment_details['currency'] ) ? $payment_details['currency'] : get_product_currency_code();

	return $payment_details;
}

/**
 * Returns order totals, currency and email
 *
 * @param int $order_id
 *
 * @return array
 */
function ic_get_order_summary( $order_id ) {
	$fields        = array( 'price', 'email' );
	$order_summary = implecode_array_variables_init( $fields, get_post_meta( $order_id, '_order_summary', true ) );
	if ( empty( $order_summary['currency'] ) ) {
		$payment_details           = ic_get_order_payment_details( $order_id );
		$order_summary['currency'] = $payment_details['currency'];
	}

	return $order_summary;
}

if ( ! function_exists( 'ic_decode_payment_details' ) ) {
	/**
	 * Decodes payment details saved in database
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	function ic_decode_payment_details( $data ) {
		$decoded = array();
		if ( is_array( $data ) ) {
			$charset = get_option( 'blog_charset' );
			foreach ( $data as $key => $value ) {
				if ( is_array( $value ) ) {
					$decoded[ $key ] = $value;
					continue;
				}
				$decoded[ $key ] = stripslashes( $value );
				$decoded[ $key ] = html_entity_decode( $decoded[ $key ], ENT_NOQUOTES, $charset );
				$decoded[ $key ] = html_entity_decode( $decoded[ $key ], ENT_COMPAT, $charset );
			}
		}

		return $decoded;
	}
}


add_filter( 'is_ic_catalog_admin_page', 'ic_orders_as_catalog_admin_page' );

function ic_orders_as_catalog_admin_page( $is_catalog_page ) {
	$post_type = get_post_type();
	if ( $post_type == 'al_digital_orders' ) {
		return true;
	}

	return $is_catalog_page;
}

function ic_send_error_message( $topic, $message ) {
	if ( function_exists( 'start_shopping_cart' ) ) {
		$settings = get_shopping_cart_settings();
		$email    = $settings['receive_cart'];
	} else if ( function_exists( 'start_easy_orders' ) ) {
		$email = get_option( 'easy_email' );
	}
	wp_mail( $email, $topic, $message, 'From: Error Message <' . $email . '>' );
}

//add_action( 'auto_order_completed', 'ic_order_completed_message', 15, 4 );

/**
 * Sends order to customer
 *
 * @param int $order_id
 * @param array $payment_details
 * @param array $order_products
 * @param array $manual_order_product
 */
function ic_order_completed_message( $order_id, $payment_details, $order_products, $manual_order_product = null ) {
	$admin_email    = ic_get_order_messages_email();
	$customer_email = $payment_details['shipping_email'];
	$site_name      = get_easy_order_sitename();
	$attachments    = array();
	$line           = '<br>';
	$attachment     = apply_filters( 'digital_order_attachments', $attachments, $order_id );
	$p              = ic_email_paragraph();
	$ep             = ic_email_paragraph_end();
	$message_intro  = sprintf( __( 'Dear %s,', 'ecommerce-product-catalog' ), trim( $payment_details['name'] ) ) . $line . $line;
	$message_intro  .= sprintf( __( 'Thank you for you order placed on %s.', 'ecommerce-product-catalog' ), $site_name ) . $line . $line;
	$message_intro  .= __( 'We\'ve received your payment.', 'ecommerce-product-catalog' ) . $line . $line;
	$message        = apply_filters( 'digital_order_message_intro', $message_intro, $order_id );
	$message        .= __( 'Feel free to contact us in case of any questions or issues.', 'ecommerce-product-catalog' ) . $line . $line;
	$message        .= __( 'Kind regards,', 'ecommerce-product-catalog' ) . $line;
	$message        .= sprintf( __( '%s Team', 'ecommerce-product-catalog' ), $site_name ) . $line . $line;
	ic_mail( $p . $message . $ep, $site_name, $admin_email, $customer_email, __( 'Order Completed', 'ecommerce-product-catalog' ), true, $attachment );
}

function ic_format_vat_address( $address ) {
	if ( ! empty( $address ) ) {
		if ( strpos( $address, '::' ) !== false ) {
			$address = explode( '::', $address );
		} else if ( strpos( $address, '  ' ) !== false ) {
			$address = explode( '  ', $address );
		} else {
			$address = explode( ', ', $address );
		}
		if ( $address[0] == '' ) {
			unset( $address[0] );
			foreach ( $address as $i => $add ) {
				$new_address[ $i - 1 ] = $add;
			}
			$address = $new_address;
			//$address[ 0 ]	 = isset( $address[ 1 ] ) ? $address[ 1 ] : '';
			//$address[ 1 ]	 = isset( $address[ 2 ] ) ? $address[ 2 ] : '';
			//$address[ 2 ]	 = '';
		}
		foreach ( $address as $key => $val ) {
			if ( $key != 0 && $key != 1 && $val != '' ) {
				$address[1] .= ' ' . $val;
			}
		}
	}
	$address[0] = isset( $address[0] ) ? $address[0] : '';
	$address[1] = isset( $address[1] ) ? $address[1] : '';

	return $address;
}

/**
 * Returns order product price
 *
 * @param type $order_id
 * @param type $product_id
 *
 * @return boolean
 */
function ic_get_order_product_price( $order_id, $product_id ) {
	$order_products = ic_get_order_products( $order_id );
	if ( isset( $order_products['product_id'] ) && is_array( $order_products['product_id'] ) ) {
		$index = array_search( $product_id, $order_products['product_id'] );
		if ( $index !== false && isset( $order_products['product_price'][ $index ] ) ) {
			return $order_products['product_price'][ $index ];
		}
	} else if ( isset( $order_products['product_price'] ) && ! is_array( $order_products['product_price'] ) ) {
		return $order_products['product_price'];
	}

	return false;
}

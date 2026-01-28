<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages paypal settings
 *
 * Here all paypal settings are defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-paypal-getaway/includes
 * @author        Norbert Dreszer
 */
if ( ! function_exists( 'validate_payment_data' ) ) {

	function validate_payment_data( $character_set, $data ) {
		return ic_validate_payment_data( $character_set, $data );
	}

}
if ( ! function_exists( 'ic_validate_payment_data' ) ) {
	function ic_validate_payment_data( $character_set, $data ) {
		if ( ! is_array( $data ) && ! empty( $data ) ) {
			$charset = get_option( 'blog_charset' );
			if ( $character_set ) {
				$data = iconv( $character_set, $charset, $data );
			}
			$data = htmlspecialchars( $data, ENT_COMPAT, $charset );
		}

		return $data;
	}
}


/**
 * Verifies if payment amount is the same or more than expected amount
 *
 * @param array|int $item_id
 * @param float $payment_amount
 * @param string $payment_currency
 *
 * @return boolean
 */
function ic_verify_payment_price( $item_id, $payment_amount, $payment_currency, $order_products, $trans_id ) {
	$expected_currency = apply_filters( 'ic_expected_currency', get_product_currency_code(), $trans_id );
	$expected_total    = 0;
	if ( ! is_array( $item_id ) ) {
		$product_price  = product_price( $item_id, 'unfiltered' );
		$expected_total = apply_filters( 'expected_order_amout', $product_price, $payment_amount, $item_id, $trans_id, $order_products );
	} else if ( isset( $item_id[0]['product_id'] ) ) {
		foreach ( $item_id as $product ) {
			$product_price   = product_price( $product['product_id'], 'unfiltered' );
			$expected_amount = apply_filters( 'expected_order_amout', $product_price, $payment_amount, $product['product_id'], $trans_id, $order_products );
			$expected_total  += $expected_amount;
		}
	} else {
		foreach ( $item_id as $product_id ) {
			$product_price   = product_price( $product_id, 'unfiltered' );
			$expected_amount = apply_filters( 'expected_order_amout', $product_price, $payment_amount, $product_id, $trans_id, $order_products );
			$expected_total  += $expected_amount;
		}
	}
	$expected_total = apply_filters( 'expected_total_amount', $expected_total, $trans_id );
	if ( $expected_total - $payment_amount > 0.01 || strval( $expected_currency ) !== strval( $payment_currency ) ) {
		//ic_send_error_message( 'error', 'expected ' . floatval( $expected_total ) . 'paid ' . floatval( $payment_amount ) . 'curr exp ' . $expected_currency . 'paid curr ' . $payment_currency );
		return false;
	}

	return true;
}

function ic_digital_order_status( $trans_id ) {
	$order_id                  = ic_order_exists( $trans_id );
	$payment_details           = get_post_meta( $order_id, '_payment_details', true );
	$payment_details['status'] = isset( $payment_details['status'] ) ? $payment_details['status'] : 'pending';

	return $payment_details['status'];
}

function ic_create_digital_order() {
	$order_id = wp_insert_post( array( 'post_type' => 'al_digital_orders', 'post_status' => 'publish' ), true );

	return $order_id;
}

function ic_order_exists( $trans_id ) {
	if ( empty( $trans_id ) ) {
		return;
	}
	$order_id = ic_get_global( 'trans_id_to_order_id_' . $trans_id );
	if ( $order_id !== false ) {
		return $order_id;
	}
	global $wpdb;
	$querystr = "SELECT wposts.*
FROM " . $wpdb->posts . " AS wposts
INNER JOIN " . $wpdb->postmeta . " AS wpostmeta
ON wpostmeta.post_id = wposts.ID
AND wpostmeta.meta_key = '_order_id'
AND wpostmeta.meta_value = '$trans_id'
AND wposts.post_type = 'al_digital_orders'
AND wposts.post_status = 'publish'";
	$order    = $wpdb->get_results( $querystr, OBJECT );
	if ( ! empty( $order ) ) {
		$r = $order[0];
		if ( ! empty( $r->ID ) ) {
			ic_save_global( 'trans_id_to_order_id_' . $trans_id, $r->ID );

			return $r->ID;
		}

	}
}

function ic_update_digital_order_status(
	$payment_details, $order_products, $order_summary, $trans_id,
	$just_status = false
) {
	$order_id     = ic_order_exists( $trans_id );
	$just_created = false;
	if ( empty( $order_id ) && ! empty( $order_products ) ) {
		$just_created = true;
		$order_id     = ic_create_digital_order();
	}
	if ( $just_status && ! empty( $payment_details['status'] ) && ! $just_created ) {
		if ( ! empty( $order_summary['price'] ) ) {
			$payment_details['payment_price'] = $order_summary['price'];
		}
		$prev_payment_details = get_post_meta( $order_id, '_payment_details', true );
		$new_payment_details  = array_merge( $prev_payment_details, $payment_details );
		update_post_meta( $order_id, '_payment_details', $new_payment_details );
	} else if ( ! empty( $order_id ) ) {
		if ( ! is_email( $payment_details['shipping_email'] ) ) {
			$prev_payment_details = get_post_meta( $order_id, '_payment_details', true );
			if ( isset( $prev_payment_details['shipping_email'] ) && is_email( $prev_payment_details['shipping_email'] ) ) {
				$payment_details['shipping_email'] = $prev_payment_details['shipping_email'];
			}
		}
		if ( ! empty( $payment_details ) ) {
			update_post_meta( $order_id, '_payment_details', $payment_details );
		}
		if ( ! empty( $order_products ) ) {
			if ( ! empty( $order_products['custom_lines'] ) ) {
				update_post_meta( $order_id, 'manual_order_product', $order_products['custom_lines'] );
				unset( $order_products['custom_lines'] );
			}
			update_post_meta( $order_id, '_order_products', $order_products );
		}
		if ( ! empty( $order_summary ) ) {
			update_post_meta( $order_id, '_order_summary', $order_summary );
		}
		if ( ! empty( $trans_id ) ) {
			update_post_meta( $order_id, '_order_id', $trans_id );
		}
	}
	$triggered = get_post_meta( $order_id, '_order_completed_triggered', true );
	if ( empty( $triggered ) && ( $payment_details['status'] == ic_order_completed_status_trigger() || $payment_details['status'] == 'completed' ) && ! empty( $order_id ) ) {

		$payment_details = ic_get_order_payment_details( $order_id );

		if ( empty( $order_products ) ) {
			$order_products = get_post_meta( $order_id, '_order_products', true );
		}
		do_action( 'order_completed', $order_id, $payment_details, $order_products );
		if ( ! current_user_can( 'edit_digital_orders' ) ) {

			do_action( 'auto_order_completed', $order_id, $payment_details, $order_products );
		}
		update_post_meta( $order_id, '_order_completed_triggered', 1 );
	}

	return $order_id;
}

/**
 * Returns currently processing order customer ID
 *
 * @return type
 * @global type $verify_payment
 */
function ic_get_active_order_customer_id() {
	global $verify_payment;
	$customer_id = '';
	if ( isset( $verify_payment['payment_details']['shipping_email'] ) && is_email( $verify_payment['payment_details']['shipping_email'] ) ) {
		$customer_id = ic_get_customer_id_from_email( $verify_payment['payment_details']['shipping_email'] );
	}

	return apply_filters( 'ic_order_customer_id', $customer_id );
}

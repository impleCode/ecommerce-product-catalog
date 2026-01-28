<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * What it does?
 *
 * Short description
 *
 * Created by Norbert Dreszer.
 * Date: 05-Mar-15
 * Time: 14:59
 * Package: transactions-customer.php
 */
class ic_orders_customer {

	function __construct() {
		add_action( 'order_completed', array( $this, 'create' ), 10, 4 );
		add_filter( 'ic_formbuilder_user_email', array( $this, 'login' ), 6, 2 );
	}

	/**
	 *
	 * @param int $trans_id
	 * @param array $payment_details
	 * @param array $order_products
	 * @param array $manual_order_product
	 *
	 * @return int
	 */
	function create( $trans_id, $payment_details, $order_products, $manual_order_product = null ) {
		$email = isset( $payment_details['shipping_email'] ) ? $payment_details['shipping_email'] : '';
		if ( ! empty( $email ) ) {
			$product_id = isset( $order_products['product_id'] ) ? $order_products['product_id'] : '';
			if ( is_array( $product_id ) ) {
				$product_id = implode( ',', $product_id );
			}
			$manual_products = ic_get_manual_products( $manual_order_product, 1 );
			$customer_id     = username_exists( $email );
			if ( empty( $customer_id ) ) {
				$customer_id = email_exists( $email );
			}
			$just_created = false;
			if ( empty( $customer_id ) ) {
				$password = wp_generate_password();
				$userdata = array(
					'user_login' => $email,
					'user_pass'  => $password,
					'user_email' => $email,
					'role'       => 'customer',
				);
				ic_save_global( 'ic_new_customer_data', $userdata );
				$customer_id  = wp_insert_user( $userdata );
				$just_created = true;
			}
			if ( ! is_ic_digital_customer( $customer_id ) ) {
				$just_created = true;
				add_ic_role( $customer_id, 'customer' );
			}
			if ( $just_created ) {
				$fields           = ic_order_details_fields();
				$transaction_data = implecode_array_variables_init( $fields, get_post_meta( $trans_id, '_payment_details', true ) );
				update_user_meta( $customer_id, '_customer_data', $transaction_data );
				update_user_meta( $customer_id, 'transaction_ids', $trans_id );
				update_user_meta( $customer_id, 'product_ids', $product_id );
				do_action( 'new_digital_customer', $customer_id );
			} else {
				$prev_trans_id         = get_user_meta( $customer_id, 'transaction_ids', true );
				$prev_product_id       = get_user_meta( $customer_id, 'product_ids', true );
				$customer_license      = get_user_meta( $customer_id, '_customer_license', true );
				$prev_trans_array      = explode( ',', $prev_trans_id );
				$prev_product_id_array = explode( ',', $prev_product_id );
				if ( ! in_array( $trans_id, $prev_trans_array ) ) {
					$prev_trans_id .= ',' . $trans_id;
					$new_trans_ids = $prev_trans_id;
					update_user_meta( $customer_id, 'transaction_ids', $new_trans_ids );
				}
				if ( ! empty( $product_id ) && ! in_array( $product_id, $prev_product_id_array ) ) {
					$prev_product_id .= ',' . $product_id;
					$new_product_ids = $prev_product_id;
					update_user_meta( $customer_id, 'product_ids', $new_product_ids );
				}
				if ( ! empty( $payment_details ) ) {
					update_user_meta( $customer_id, '_customer_data', $payment_details );
				}
			}

			$manual_products['manual_product_ids']     = isset( $manual_products['manual_product_ids'] ) ? $manual_products['manual_product_ids'] : '';
			$manual_products['custom_manual_products'] = isset( $manual_products['custom_manual_products'] ) ? $manual_products['custom_manual_products'] : '';
			if ( ! empty( $manual_products['manual_product_ids'] ) ) {
				$old_manual_products = get_user_meta( $customer_id, 'manual_product_ids', true );
				if ( ! empty( $old_manual_products ) ) {
					$old_manual_products .= ',' . $manual_products['manual_product_ids'];
					$new_manual_products = $old_manual_products;
				} else {
					$new_manual_products = $manual_products['manual_product_ids'];
				}
				update_user_meta( $customer_id, 'manual_product_ids', $new_manual_products );
			}
			if ( ! empty( $manual_products['custom_manual_products'] ) ) {
				$old_custom_manual_products = get_user_meta( $customer_id, 'custom_manual_products', true );
				if ( ! empty( $old_custom_manual_products ) ) {
					$old_custom_manual_products .= ',' . $manual_products['custom_manual_products'];
					$new_custom_manual_products = $old_custom_manual_products;
				} else {
					$new_custom_manual_products = $manual_products['custom_manual_products'];
				}
				update_user_meta( $customer_id, 'custom_manual_products', $new_custom_manual_products );
			}

			update_post_meta( $trans_id, '_customer_id', $customer_id );

			do_action( 'existing_customer_buy', $customer_id, $trans_id, $product_id, $manual_products['manual_product_ids'], $manual_products['custom_manual_products'], $payment_details );

			if ( ! is_wp_error( $customer_id ) ) {
				return $customer_id;
			}
		}
	}

	function login( $message, $pre_name ) {
		if ( $pre_name == 'cart_' ) {
			$userdata = ic_get_global( 'ic_new_customer_data' );
			if ( ! empty( $userdata ) && ! empty( $userdata['user_login'] ) && ! empty( $userdata['user_pass'] ) ) {
				$panel_url = ic_customer_panel_panel_url();
				if ( ! empty( $panel_url ) ) {
					$p                 = ic_email_paragraph();
					$ep                = ic_email_paragraph_end();
					$ul                = ic_email_ul();
					$eul               = ic_email_ul_end();
					$li                = ic_email_li();
					$eli               = ic_email_li_end();
					$user_account_info = $p . __( 'Please see your account info below', 'ecommerce-product-catalog' ) . ':' . $ep;
					$user_account_info .= $ul;
					$user_account_info .= $li . __( 'Login', 'ecommerce-product-catalog' ) . ': ' . $userdata['user_login'] . $eli;
					$user_account_info .= $li . __( 'Password', 'ecommerce-product-catalog' ) . ': ' . $userdata['user_pass'] . $eli;
					$user_account_info .= $eul;
					$user_account_info .= ic_email_button( $panel_url ) . __( 'Login Now', 'ecommerce-product-catalog' ) . '</a>';
					$message           = str_replace( '[account_info]', $user_account_info, $message );
				}
			}
			$message = str_replace( '[account_info]', '', $message );
		}

		return $message;
	}

}

$ic_orders_customer = new ic_orders_customer;

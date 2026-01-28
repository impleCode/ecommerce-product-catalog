<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Adds conditional functions for digital customers
 *
 * Created by Norbert Dreszer.
 * Date: 05-Mar-15
 * Time: 15:55
 * Package: conditionals.php
 */
if ( ! function_exists( 'is_ic_digital_customer' ) ) {
	/**
	 * Checks if current user is logged as digital customer
	 *
	 * @param int $customer_id Customer ID to check (optional)
	 *
	 * @return boolean
	 */
	function is_ic_digital_customer( $customer_id = null ) {
		if ( ! is_numeric( $customer_id ) ) {
			$customer_id = 0;
		}
		if ( empty( $customer_id ) && function_exists( 'ic_get_active_order_customer_id' ) ) {
			$customer_id = ic_get_active_order_customer_id();
		}
		if ( empty( $customer_id ) ) {
			$customer_id = ic_get_logged_customer_id();
		}
		if ( ! empty( $customer_id ) ) {
			$ic_roles = get_ic_roles( $customer_id );
			if ( in_array( 'customer', $ic_roles ) ) {
				return true;
			}
			$customer_data = get_userdata( $customer_id );
			if ( in_array( 'administrator', $customer_data->roles ) || in_array( 'customer', $customer_data->roles ) || in_array( 'digital_customer', $customer_data->roles ) || in_array( 'cart_customer', $customer_data->roles ) ) {
				return true;
			}
		}

		return false;
	}
}
function ic_has_customer_product( $customer_id, $product_id ) {
	$customer_products = ic_customer_product_ids( $customer_id );
	if ( array_search( $product_id, $customer_products ) ) {
		return true;
	} else {
		return false;
	}
}

function is_ic_customer_panel() {
	if ( function_exists( 'ic_customer_panel_panel_id' ) ) {
		$customer_pane_page = ic_customer_panel_panel_id();
		if ( ! empty( $customer_pane_page ) && is_ic_page( $customer_pane_page ) ) {
			return true;
		}
	}

	return false;
}

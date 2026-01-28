<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Integrates with transaction extensions
 *
 * Created by Norbert Dreszer.
 * Date: 05-Mar-15
 * Time: 14:40
 * Package: transaction-functions.php
 */

/**
 * Returns all customer transaction IDs
 *
 * @param int $customer_id
 *
 * @return array
 */
function ic_customer_transaction_ids( $customer_id ) {
	$customer_transactions_ids = get_user_meta( $customer_id, 'transaction_ids', true );
	$customer_transactions_ids = explode( ',', $customer_transactions_ids );

	return $customer_transactions_ids;
}

/**
 * Returns all customer product ids array
 *
 * @param int $customer_id
 *
 * @return array
 */
function ic_customer_product_ids( $customer_id ) {
	$product_ids_string         = ic_customer_auto_product_ids( $customer_id );
	$product_ids                = explode( ',', $product_ids_string );
	$manual_products_ids        = explode( ',', get_user_meta( $customer_id, 'manual_product_ids', true ) );
	$custom_manual_products_ids = explode( ',', get_user_meta( $customer_id, 'custom_manual_products', true ) );
	$all_products               = array_filter( array_merge( $product_ids, $manual_products_ids, $custom_manual_products_ids ) );

	return $all_products;
}

/**
 * Returns customer auto product ids
 *
 * @param int $customer_id
 *
 * @return string Comma separated product IDs
 */
function ic_customer_auto_product_ids( $customer_id ) {
	$product_ids = get_user_meta( $customer_id, 'product_ids', true );
	if ( ! empty( $product_ids ) && ic_string_contains( $product_ids, ',' ) ) {
		$product_ids = implode( ',', array_unique( array_filter( explode( ',', $product_ids ) ) ) );
	}

	return apply_filters( 'customer_auto_product_ids', $product_ids );
}

function ic_get_customer_total_spending( $customer_id ) {
	$customer_transaction_ids = ic_customer_transaction_ids( $customer_id );
	$total                    = '';
	if ( ! empty( $customer_transaction_ids[0] ) ) {
		foreach ( $customer_transaction_ids as $trans_id ) {
			$product = ic_get_order_products( $trans_id );
			$total   = $total + $product['product_summary'];
		}
	}

	return $total;
}

function ic_user_has_product( $customer_id, $product_id ) {
	$user_products = ic_customer_product_ids( $customer_id );
	$return        = false;
	foreach ( $user_products as $id ) {
		if ( $product_id == $id ) {
			$return = true;
		}
	}

	return $return;
}

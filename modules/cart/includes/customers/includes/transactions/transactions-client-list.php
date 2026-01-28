<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements transaction columns into client list
 *
 * Created by Norbert Dreszer.
 * Date: 05-Mar-15
 * Time: 14:46
 * Package: transactions-client-list.php
 */
class ic_transaction_client_list {

	function __construct() {
		add_filter( 'client_list_columns', array( $this, 'columns' ) );
		add_filter( 'client_list_data', array( $this, 'data' ), 10, 2 );
	}

	function columns( $columns ) {
		$columns[ 'products' ]	 = __( 'Products', 'ecommerce-product-catalog' );
		$columns[ 'license' ]	 = __( 'License', 'ecommerce-product-catalog' );
		$columns[ 'orders' ]	 = __( 'Orders', 'ecommerce-product-catalog' );
		$columns[ 'total' ]		 = __( 'Total Spendings', 'ecommerce-product-catalog' );
		return $columns;
	}

	function data( $data, $customer_id ) {
		$customer_transactions_urls	 = ic_get_user_urls( $customer_id, 'transaction_ids' );
		$customer_products_urls		 = ic_get_user_urls( $customer_id, 'product_ids' );
		$customer_license_url		 = ic_get_user_urls( $customer_id, 'customer_license_id' );
		$total						 = ic_get_customer_total_spending( $customer_id );
		$data[ 'products' ]			 = $customer_products_urls;
		$data[ 'license' ]			 = $customer_license_url;
		$data[ 'orders' ]			 = $customer_transactions_urls;
		$data[ 'total' ]			 = $total;
		return $data;
	}

}

$ic_transaction_client_list = new ic_transaction_client_list;

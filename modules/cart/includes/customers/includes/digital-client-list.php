<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages digital clients page
 *
 * Here all digital client page classess defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-digital-customers/includes
 * @author        Norbert Dreszer
 */
if ( !class_exists( 'WP_List_Table' ) ) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class digital_customer_list_table extends WP_List_Table {

	function get_columns() {
		$columns = array(
			'customer_id'	 => __( 'Customer ID', 'ecommerce-product-catalog' ),
			'name'			 => __( 'Customer Name', 'ecommerce-product-catalog' ),
			'email'			 => __( 'Customer Email', 'ecommerce-product-catalog' )
		);
		return apply_filters( 'client_list_columns', $columns );
	}

	function prepare_items() {
		$args		 = array(
			'role__in'	 => array( 'digital_customer', 'customer' ),
			'fields'	 => 'ID',
		);
		$users		 = get_users( $args );
		$users_table = array();
		foreach ( $users as $customer_id ) {
			$customer_data	 = get_user_meta( $customer_id, '_customer_data', true );
			$user_array		 = array(
				'customer_id'	 => ic_get_customer_url( $customer_id, $customer_id ),
				'name'			 => $customer_data[ 'name' ],
				'email'			 => $customer_data[ 'shipping_email' ]
			);
			$users_table[]	 = apply_filters( 'client_list_data', $user_array, $customer_id );
		}
		$columns				 = $this->get_columns();
		$hidden					 = array();
		$sortable				 = array();
		$this->_column_headers	 = array( $columns, $hidden, $sortable );
		$this->items			 = $users_table;
	}

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'customer_id':
			case 'name':
			case 'email':
			case 'products':
			case 'license':
			case 'orders':
			case 'total':
				return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

}

function ic_add_digital_customers_menu() {
	add_submenu_page( 'edit.php?post_type=al_digital_orders', 'Customers', 'Customers', 'edit_digital_orders', 'digital_customers', 'ic_show_digital_customers_list' );
}

add_action( 'admin_menu', 'ic_add_digital_customers_menu' );

function ic_show_digital_customers_list() {
	$client_table = new digital_customer_list_table();
	echo '<div class="wrap"><h2>Customers</h2>';
	$client_table->prepare_items();
	$client_table->display();
	echo '</div>';
}

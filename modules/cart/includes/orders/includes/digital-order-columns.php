<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages digital orders fields
 *
 * Here all digital orders post type is defined and managed.
 *
 * @version        1.0.0
 * @package        digital-products-orders/includes
 * @author        Norbert Dreszer
 */
class ic_orders_columns {

	function __construct() {
		add_filter( 'manage_edit-al_digital_orders_columns', array( $this, 'add_columns' ) );
		add_action( 'manage_al_digital_orders_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );
	}

	function add_columns( $orders_columns ) {
		$new_columns['cb']         = '<input type="checkbox" />';
		$new_columns['id']         = __( 'ID', 'ecommerce-product-catalog' );
		$new_columns['status']     = __( 'Status', 'ecommerce-product-catalog' );
		$new_columns['product']    = __( 'Product', 'ecommerce-product-catalog' );
		$new_columns['amount']     = __( 'Amount', 'ecommerce-product-catalog' );
		$new_columns['from']       = __( 'From', 'ecommerce-product-catalog' );
		$new_columns['trans_date'] = __( 'Date', 'ecommerce-product-catalog' );

		return $new_columns;
	}

	function manage_columns( $column_name, $order_id ) {
		$payment_details = get_post_meta( $order_id, '_payment_details', true );
		if ( ! is_array( $payment_details ) ) {
			$payment_details = array();
		}
		$payment_details['status'] = isset( $payment_details['status'] ) ? $payment_details['status'] : 'pending';
		$order_products            = ic_get_order_products( $order_id );
		$order_summary             = get_post_meta( $order_id, '_order_summary', true );
		if ( empty( $order_summary ) ) {
			$order_summary = array();
		}
		$customer_id                    = get_post_meta( $order_id, '_customer_id', true );
		$manual_order_product           = ic_get_manual_order_products( $order_id );
		$manual_products                = ic_get_manual_products( $manual_order_product );
		$order_summary['price']         = isset( $order_summary['price'] ) ? $order_summary['price'] : '';
		$order_summary['email']         = isset( $order_summary['email'] ) ? $order_summary['email'] : '';
		$order_products['product_name'] = isset( $order_products['product_name'] ) ? $order_products['product_name'] : '';
		$order_products['product_id']   = isset( $order_products['product_id'] ) ? $order_products['product_id'] : '';
		if ( ! empty( $customer_id ) ) {
			$from_url = '<a href="' . admin_url( 'user-edit.php?user_id=' . $customer_id, 'http' ) . '">' . $order_summary['email'] . '</a>';
		} else {
			$from_url = $order_summary['email'];
		}
		switch ( $column_name ) {
			case 'id':
				echo '<a href="' . get_edit_post_link( $order_id ) . '" title="' . __( 'Edit', 'ecommerce-product-catalog' ) . '">' . $order_id . '</a>';
				echo '<div class="row-actions"><span class="edit"><a href="' . get_edit_post_link( $order_id ) . '" title="Edit this item">Edit</a> | </span><span class="trash"><a class="submitdelete" title="Move this item to the Trash" href="' . get_delete_post_link( $order_id ) . '">Trash</a></span></div>';
				break;

			case 'status':
				$current_status              = strtolower( $payment_details['status'] );
				$statuses                    = ic_available_payment_status();
				$statuses[ $current_status ] = isset( $statuses[ $payment_details['status'] ] ) ? $statuses[ $payment_details['status'] ] : __( 'Complete', 'ecommerce-product-catalog' );
				echo '<div class="box-' . $current_status . '">' . $statuses[ $current_status ] . '</div>';

				break;
			case 'from':
				echo $from_url;
				break;
			case 'amount':
				echo $order_summary['price'];
				break;
			case 'product':
				if ( is_array( $order_products['product_id'] ) ) {
					foreach ( $order_products['product_id'] as $i => $product_id ) {
						echo '<a href="' . ic_product_edit_url( $product_id ) . '">' . $order_products['product_name'][ $i ] . '</a><br>';
					}
				} else {
					echo '<a href="' . ic_product_edit_url( $order_products['product_id'] ) . '">' . $order_products['product_name'] . '</a>';
				}
				if ( ! empty( $manual_products['manual_product_ids'] ) ) {
					$manual_products['manual_product_ids'] = explode( ',', $manual_products['manual_product_ids'] );
					foreach ( $manual_products['manual_product_ids'] as $product_id ) {
						echo '<a href="' . ic_product_edit_url( $product_id ) . '">' . get_the_title( $product_id ) . '</a><br>';
					}
				} else if ( ! empty( $manual_products['custom_manual_products'] ) ) {
					$manual_products['custom_manual_products'] = explode( ',', $manual_products['custom_manual_products'] );
					foreach ( $manual_products['custom_manual_products'] as $product_name ) {
						echo $product_name;
					}
				}
				break;
			case 'trans_date':
				echo human_time_diff( get_the_time( 'U', $order_id ) ) . ' ' . __( 'ago', 'ecommerce-product-catalog' );
				break;
			default:
				break;
		} // end switch
	}

}

$ic_orders_columns = new ic_orders_columns;

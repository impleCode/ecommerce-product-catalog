<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages license functions
 *
 * Here all plugin functions are defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-digital-customers/functions
 * @author        Norbert Dreszer
 */
function ic_get_user_urls( $customer_id, $meta_name ) {
	$customer_urls = get_user_meta( $customer_id, $meta_name, true );
	$customer_urls = explode( ',', $customer_urls );
	$urls          = '';
	foreach ( $customer_urls as $url ) {
		$urls .= '<a href="' . get_edit_post_link( $url ) . '">' . $url . '</a>';
	}

	return $urls;
}

function ic_get_customer_url( $customer_id, $anchor = null ) {
	if ( empty( $anchor ) && ! empty( $customer_id ) ) {
		$user_info = get_userdata( $customer_id );
		$anchor    = $user_info->user_login;
	}
	if ( ! empty( $customer_id ) ) {
		$customer_url = '<a href="' . admin_url( 'user-edit.php?user_id=' . $customer_id, 'http' ) . '">' . $anchor . '</a>';
	} else {
		$customer_url = $anchor;
	}

	return $customer_url;
}

add_action( 'digital-order-summary', 'ic_show_customer_url', 10, 1 );

function ic_show_customer_url( $content_id ) {
	$customer_id = get_post_meta( $content_id, '_customer_id', true );
	if ( ! empty( $customer_id ) ) {
		echo '<label>' . __( 'Customer Login', 'ecommerce-product-catalog' ) . '</label>';
		echo '<div class="license-detail">' . ic_get_customer_url( $customer_id ) . '</div>';
	}
}

function ic_get_customer_id_from_email( $email ) {
	$customer_id = username_exists( $email );
	if ( ! empty( $customer_id ) ) {
		return $customer_id;
	} else {
		return false;
	}
}

function ic_get_customer_id_from_order_id( $order_id ) {
	$payment_details = ic_get_order_payment_details( $order_id );

	return ic_get_customer_id_from_email( $payment_details['shipping_email'] );
}

if ( ! function_exists( 'ic_get_logged_customer_id' ) ) {
	function ic_get_logged_customer_id() {
		$id = get_current_user_id();

		return $id;
	}
}
function ic_get_digital_customer_email( $customer_id = null ) {
	if ( empty( $customer_id ) ) {
		$customer_id = ic_get_logged_customer_id();
	}
	if ( ! empty( $customer_id ) ) {
		$customer_data = get_userdata( $customer_id );

		return $customer_data->user_email;
	}

	return '';
}

function ic_get_digital_customer_login( $customer_id = null ) {
	if ( empty( $customer_id ) ) {
		$customer_id = ic_get_logged_customer_id();
	}
	if ( ! empty( $customer_id ) ) {
		$customer_data = get_userdata( $customer_id );

		return $customer_data->user_login;
	}

	return '';
}

if ( ! function_exists( 'get_ic_roles' ) ) {

	function get_ic_roles( $user_id ) {
		$ic_roles = get_user_meta( $user_id, '_ic_roles', true );
		if ( empty( $ic_roles ) ) {
			$ic_roles = array();
		}

		return $ic_roles;
	}

}

if ( ! function_exists( 'add_ic_role' ) ) {

	function add_ic_role( $user_id, $role_name ) {
		$ic_roles = get_ic_roles( $user_id );
		if ( ! empty( $role_name ) ) {
			$ic_roles[] = $role_name;
			update_user_meta( $user_id, '_ic_roles', $ic_roles );
		}

		return $ic_roles;
	}

}

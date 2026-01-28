<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages plugin activation functions
 *
 * Here all plugin ativation functions are defined and managed.
 *
 * @version		1.0.0
 * @package		digital-products-order/functions
 * @author 		Norbert Dreszer
 */
function is_ic_order_edit_screen() {
	$post_type = get_post_type();
	if ( function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
	}
	if ( $post_type == 'al_digital_orders' && ((isset( $_GET[ 'action' ] ) && $_GET[ 'action' ] === 'edit') || (isset( $screen->action ) && $screen->action == 'add')) ) {
		return true;
	}
	return false;
}

function ic_ic_order_manual( $order_id = null ) {
	if ( empty( $order_id ) && get_post_type() === 'al_digital_orders' ) {
		$order_id = get_the_ID();
	}
	if ( !empty( $order_id ) ) {
		$count = count( ic_get_manual_order_products( $order_id ) );
		if ( !empty( $count ) ) {
			return true;
		}
	}
	return false;
}

function is_ic_digital_order_taxed( $order_id ) {
	if ( function_exists( 'eu_tax_system_enabled' ) && eu_tax_system_enabled() ) {
		$payment_details					 = ic_get_order_payment_details( $order_id );
		$payment_details[ 'vat_country' ]	 = isset( $payment_details[ 'vat_country' ] ) ? $payment_details[ 'vat_country' ] : '';
		if ( $payment_details[ 'vat_country' ] == get_home_country() ) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

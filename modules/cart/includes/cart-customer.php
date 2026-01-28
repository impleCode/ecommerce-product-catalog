<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages shopping customer
 *
 * Here shopping customer is defined and managed.
 *
 * @version		1.0.0
 * @package		implecode-shopping-cart/includes
 * @author 		Norbert Dreszer
 */
class ic_cart_customer {

	function __construct() {
		add_action( 'existing_customer_buy', array( __CLASS__, 'save_cart' ), 10, 1 );
		add_action( 'ic_formbuilder_before_mail', array( __CLASS__, 'save_cart' ), 10, 0 );

		add_filter( 'ic_formbuilder_default_value', array( __CLASS__, 'checkout_defaults' ), 9, 2 );

		add_action( 'init', array( __CLASS__, 'remove_admin_bar' ) );

		add_action( 'wp_ajax_nopriv_restore_shopping_cart', array( __CLASS__, 'ajax_restore_cart' ) );
		add_action( 'wp_ajax_restore_shopping_cart', array( __CLASS__, 'ajax_restore_cart' ) );

		add_filter( 'ic_no_products_text', array( __CLASS__, 'restore_cart_button' ) );
	}

	static function save_cart( $customer_id = null ) {
		$cart_products = shopping_cart_products_array();
		if ( !empty( $cart_products ) ) {
			if ( is_int( $customer_id ) && get_userdata( $customer_id ) ) {
				update_user_meta( $customer_id, '_customer_cart', $cart_products );
			} else {
				$customer_id = get_current_user_id();
				if ( !empty( $customer_id ) ) {
					update_user_meta( $customer_id, '_customer_cart', $cart_products );
				}
			}
		}
	}

	static function checkout_defaults( $field_value, $field_name ) {
		$customer_field_value = ic_cart_customer_data( $field_name );
		if ( !empty( $customer_field_value ) ) {
			$field_value = $customer_field_value;
		}
		return $field_value;
	}

	static function remove_admin_bar() {
		if ( is_ic_cart_customer() && !current_user_can( 'manage_product_settings' ) ) {
			show_admin_bar( false );
			if ( is_admin() && !is_ic_ajax() ) {
				wp_redirect( home_url() );
				exit;
			}
		}
	}

	/**
	 * Handles ajax user cart restore
	 */
	static function ajax_restore_cart() {
		$cart_content	 = ic_encode_string_cart( ic_get_customer_cart() );
		$how_many		 = ic_get_cart_items_count( $cart_content );
		ic_cart_save( $cart_content, 'cart_content' );
		$array[ 0 ]		 = '<a href="' . ic_shopping_cart_page_url() . '"><button class="button ' . design_schemes( 'box', 0 ) . '"><span class="cart_button_text">' . $how_many . ' ' . __( 'selected', 'ecommerce-product-catalog' ) . '</span></button></a>';
		$array[ 1 ]		 = do_shortcode( "[shopping_cart]" );
		$array[ 2 ]		 = shopping_cart_products( 1 );
		echo json_encode( $array );
		wp_die();
	}

	/**
	 *
	 * @param string $text
	 * @return string
	 */
	static function restore_cart_button( $text ) {
		$user_cart = ic_get_customer_cart();
		if ( !empty( $user_cart ) ) {
			$text .= ' <a href="#" class="restore-ic-cart"><button class="button ' . design_schemes( 'box', 0 ) . '"><span class="cart_button_text">' . __( 'Restore Previous Cart', 'ecommerce-product-catalog' ) . '</span></button></a>';
		}
		return $text;
	}

}

$ic_cart_customer = new ic_cart_customer;

function ic_cart_customer_data( $field_name ) {
	$customer_id = ic_cart_customer_id();
	if ( !empty( $customer_id ) ) {
		$customer = get_user_meta( $customer_id, '_customer_data', true );
		if ( is_array( $customer ) && isset( $customer[ $field_name ] ) ) {
			return $customer[ $field_name ];
		}
	}
}

/**
 * Returns customer id
 *
 * @param type $email
 * @return type
 */
function ic_cart_customer_id( $email = null ) {
	$customer_id = get_current_user_id();
	if ( empty( $customer_id ) && !empty( $email ) ) {
		$customer_id = username_exists( $email );
	}
	return $customer_id;
}

/**
 * Returns customer cart
 *
 * @return type
 */
function ic_get_customer_cart() {
	$customer_id = ic_cart_customer_id();
	$user_cart	 = '';
	if ( !empty( $customer_id ) ) {
		$user_cart = get_user_meta( $customer_id, '_customer_cart', true );
	}
	return apply_filters( 'ic_customer_cart', $user_cart );
}

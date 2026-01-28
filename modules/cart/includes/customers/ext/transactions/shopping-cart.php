<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Integrates with digital products extension
 *
 * Created by Norbert Dreszer.
 * Date: 06-Mar-15
 * Time: 11:38
 * Package: digital-products.php
 */
class ic_customers_checkout {

	function __construct() {
		add_filter( 'ic_forumbuilder_field_attributes', array( $this, 'disable_email_field' ), 10, 4 );
		add_filter( 'ic_formbuilder_default_value', array( $this, 'email_field_value' ), 10, 2 );
		add_filter( 'ic_login_form_redirect', array( $this, 'redirect' ) );
	}

	/**
	 * Disables customer_email field to prevent logged user to change it
	 *
	 * @param string $attributes
	 * @param type $field
	 * @param type $pre_name
	 * @param type $default_value
	 * @return string
	 */
	function disable_email_field( $attributes, $field, $pre_name, $default_value ) {
		if ( $field->cid == 'customer_email' && !empty( $default_value ) && is_ic_digital_customer() ) {
			$attributes = ' readonly';
		}
		return $attributes;
	}

	/**
	 * Sets customer_email field default value for logged user
	 *
	 * @param type $value
	 * @param type $cid
	 * @return type
	 */
	function email_field_value( $value, $cid ) {
		if ( $cid == 'customer_email' && is_ic_digital_customer() ) {
			$value = ic_get_digital_customer_email();
		}
		return $value;
	}

	function redirect( $redirect ) {
		if ( is_ic_shopping_cart() || is_ic_shopping_order() ) {
			$redirect = ic_current_page_url();
		}
		return $redirect;
	}

}

$ic_customers_checkout = new ic_customers_checkout;

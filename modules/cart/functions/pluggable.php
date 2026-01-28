<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages shopping functions
 *
 * Here shopping functions are defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-shopping-cart/functions
 * @author        Norbert Dreszer
 */
if ( ! function_exists( 'ic_clear_variable' ) ) {

	/**
	 * For hooks to clear the output
	 * @return string
	 */
	function ic_clear_variable() {
		return '';
	}

}

if ( ! function_exists( 'ic_success_page' ) ) {
	add_shortcode( 'success_page', 'ic_success_page' );

	function ic_success_page() {
		ob_start();
		ic_save_global( 'inside_thank_you', 1 );
		do_action( 'success_page' );
		ic_delete_global( 'inside_thank_you' );

		return ob_get_clean();
	}

}

if ( ! function_exists( 'get_easy_order_sitename' ) ) {

	/**
	 * Returns current sitename (used as email sender)
	 *
	 * @return string
	 */
	function get_easy_order_sitename() {
		$settings = get_shopping_cart_settings();
		if ( ! empty( $settings['cart_name'] ) ) {
			$site_name = $settings['cart_name'];
		} else {
			$site_name = get_bloginfo( 'name' );
		}

		return $site_name;
	}

}

if ( ! function_exists( 'ic_get_order_messages_email' ) ) {

	/**
	 * Returns email to send messages
	 *
	 * @return string
	 */
	function ic_get_order_messages_email() {
		$settings = get_shopping_cart_settings();
		$email    = $settings['send_cart'];

		return $email;
	}

}
if ( ! function_exists( 'ic_get_order_messages_email_send' ) ) {

	/**
	 * Returns email to receive messages
	 *
	 * @return string
	 */
	function ic_get_order_messages_email_send() {
		$settings = get_shopping_cart_settings();
		$email    = $settings['receive_cart'];

		return $email;
	}

}

if ( ! function_exists( 'ic_get_country_code' ) ) {

	/**
	 * Gets country code by its name. Works also if country code is provided.
	 *
	 * @param string $country_name
	 *
	 * @return string
	 */
	function ic_get_country_code( $country_name ) {
		$countries    = implecode_supported_countries();
		$country_code = array_search( $country_name, $countries );
		if ( ! $country_code && isset( $countries[ $country_name ] ) ) {
			$country_code = $country_name;
		}

		return $country_code;
	}

}

if ( ! function_exists( 'ic_get_order_form_emails' ) ) {

	/**
	 * Returns order form email settings
	 *
	 * @return array
	 */
	function ic_get_order_form_emails() {
		$emails['sender']   = ic_get_order_messages_email();
		$emails['receiver'] = ic_get_order_messages_email_send();
		$emails['name']     = get_easy_order_sitename();

		return $emails;
	}

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

if ( ! function_exists( 'ic_thank_you_page' ) ) {

	/**
	 * Returns thank you page URL
	 *
	 * @param int $catalog_id
	 *
	 * @return string
	 */
	function ic_thank_you_page( $catalog_id = null ) {
		$settings       = get_shopping_cart_settings();
		$thank_you_page = site_url();
		if ( ! empty( $settings['thank_you_page'] ) ) {
			$thank_you_page = ic_get_permalink( $settings['thank_you_page'] );
		}

		return $thank_you_page;
	}

}
if ( ! function_exists( 'ic_set_shortcode_content' ) ) {

	function ic_set_shortcode_content( $shortcode ) {
		if ( function_exists( 'register_block_type' ) ) {
			$shortcode_html = '<!-- wp:shortcode -->[' . $shortcode . ']<!-- /wp:shortcode -->';
		} else {
			$shortcode_html = '[' . $shortcode . ']';
		}

		return $shortcode_html;
	}

}

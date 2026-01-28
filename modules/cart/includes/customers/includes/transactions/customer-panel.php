<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Defines customer panel functions
 *
 * Frontend
 *
 * @created Norbert Dreszer
 * @date 05-Mar-15
 * @package implecode-digital-customers/includes/transactions
 */
/*
  function customer_products_table_cells( $product_id ) {
  $product_name = get_product_name( $product_id );
  if ( !empty( $product_name ) ) {
  $permalink = get_product_url( $product_id, 1 );
  if ( !empty( $permalink ) ) {
  $product_url = '<a href="' . $permalink . '">' . $product_name . '</a>';
  } else {
  $product_url = $product_name;
  }
  return apply_filters( 'customer_products_table_cells', array( $product_url ), $product_id );
  }
  return;
  }

  function customer_products_table_heads() {
  return apply_filters( 'customer_products_table_heads', array( __( 'Name', 'ecommerce-product-catalog' ) ) );
  }
 *
 */

class ic_customer_panel {

	function __construct() {
		add_shortcode( 'customer-panel', array( $this, 'customer_panel' ) );
		add_filter( 'customer_panel_tabs', array( $this, 'password_tab' ), 80 );
		add_filter( 'the_content', array( $this, 'shortcode' ) );
		add_filter( 'license_key_email', array( $this, 'info_in_license_email' ) );
		add_action( 'wp_ajax_customer_panel_password_reset', array( $this, 'password_reset' ) );
	}

	function customer_panel( $atts, $content = '' ) {
		ic_enqueue_main_catalog_js_css();
		$customer_id = ic_get_logged_customer_id();
		if ( is_ic_digital_customer( $customer_id ) ) {
			$panel = '<div id="customer_panel">';
			$panel .= '<div id="customer_email">' . __( 'Login', 'ecommerce-product-catalog' ) . ': ' . ic_get_digital_customer_login( $customer_id ) . '</div>';
			$panel .= apply_filters( 'ic_customer_panel_top', '' );
			$panel .= '<div id="customer_logout"><a href="' . wp_logout_url( network_site_url( $_SERVER['REQUEST_URI'] ) ) . '" class="button ' . design_schemes( 'box', 0 ) . '">' . __( 'Logout', 'ecommerce-product-catalog' ) . '</a></div>';
			$panel .= '<div class="customer_panel_actions">' . ic_customer_panel_actions() . '</div>';
			$panel .= '<div id="customer_panel_tabs" class="ui-tabs">';
			$panel .= '<ul class="ui-tabs-nav">' . apply_filters( 'customer_panel_tabs', '', $customer_id ) . '</ul>';
			$panel .= apply_filters( 'customer_panel_content', '', $customer_id );
			$panel .= '<div id="customer_panel_tabs-password">';
			$panel .= $this->password_reset_container( $customer_id );
			$panel .= '</div>';
			$panel .= '</div>';
			$panel .= '</div>';
			$panel .= do_shortcode( $content );

			return $panel;
		} else {
			$pre_panel = ic_customer_panel_actions();
			$pre_panel .= ic_digital_customer_login_form( true, 'login_form panel_login' );

			return $pre_panel;
		}
	}

	function password_tab( $tabs ) {
		$tabs .= '<li><a href="#customer_panel_tabs-password">' . __( 'Password', 'ecommerce-product-catalog' ) . '</a></li>';

		return $tabs;
	}

	function shortcode( $content ) {
		if ( is_ic_customer_panel() && ! has_shortcode( $content, 'customer-panel' ) ) {
			$content .= '[customer-panel]';
		}

		return $content;
	}

	function info_in_license_email( $message ) {
		$message .= sprintf( __( 'You can manage your license when you log in on %s. Use your email as username and license key as the password. You can change the password after login.', 'ecommerce-product-catalog' ), get_bloginfo( 'name' ) );
		$message .= '<br><br>';

		return $message;
	}

	function password_reset_container( $customer_id ) {
		$form = implecode_info( __( 'Please use a strong password with at least 8 characters, including uppercase and lowercase letters, numbers, and symbols for better security.', 'ecommerce-product-catalog' ), 0 );
		$form .= '<div class="new-password"><label for="new_password_1">New Password</label><input type="password" name="new_password_1" id="new_password_1"></div>';
		$form .= '<div class="repeat-new-password"><label for="new_password_2">Repeat New Password</label><input type="password" name="new_password_2" id="new_password_2"></div>';
		$form .= '<div class="password-reset-result"></div>';
		$form .= '<div class="new-password-button"><button class="button">Change Password</button><div class="spinner"><img title="WordPress Loading Animation Image" alt="WordPress Loading Animation Image" src="' . admin_url( '/images/wpspin_light-2x.gif' ) . '" width="25" height="25"></div></div>';

		return $form;
	}

	function password_reset() {
		$nonce               = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';
		$new_password        = isset( $_POST['new_password'] ) ? sanitize_text_field( $_POST['new_password'] ) : '';
		$repeat_new_password = isset( $_POST['repeat_new_password'] ) ? sanitize_text_field( $_POST['repeat_new_password'] ) : '';
		if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'ic_ajax' ) && ! empty( $new_password ) && ! empty( $repeat_new_password ) && $new_password === $repeat_new_password && is_ic_digital_customer() ) {
			$customer_id = ic_get_logged_customer_id();
			if ( empty( $customer_id ) || ! is_numeric( $customer_id ) ) {
				implecode_warning( sprintf( __( 'User not logged in or invalid customer ID. Auto refresh in %s seconds.', 'ecommerce-product-catalog' ), '<span class="time">3</span>' ) );
				wp_die();
			}
			$validate_password = $this->validate_password( $new_password );
			if ( $validate_password !== true ) {
				implecode_warning( $validate_password );
				wp_die();
			}
			wp_set_password( $new_password, $customer_id );
			implecode_success( sprintf( __( 'The password has been changed! Auto refresh in %s seconds.', 'ecommerce-product-catalog' ), '<span class="time">3</span>' ) );
		} else {
			implecode_warning( sprintf( __( 'An error occurred. Please try again. Auto refresh in %s seconds.', 'ecommerce-product-catalog' ), '<span class="time">3</span>' ) );
		}
		wp_die();
	}

	function validate_password( $new_password ) {
		// Validate Password Strength
		$min_length = 8; // Minimum length for the password
		if ( strlen( $new_password ) < $min_length ) {
			return sprintf( __( 'Password must be at least %d characters long. Auto refresh in %s seconds.', 'ecommerce-product-catalog' ), $min_length, '<span class="time">5</span>' );
		}

		// Check for at least one number
		if ( ! preg_match( '/\d/', $new_password ) ) {
			return sprintf( __( 'Password must include at least one number. Auto refresh in %s seconds.', 'ecommerce-product-catalog' ), '<span class="time">3</span>' );
		}

		// Check for at least one uppercase letter
		if ( ! preg_match( '/[A-Z]/', $new_password ) ) {
			return sprintf( __( 'Password must include at least one uppercase letter. Auto refresh in %s seconds.', 'ecommerce-product-catalog' ), '<span class="time">3</span>' );
		}

		// Check for at least one lowercase letter
		if ( ! preg_match( '/[a-z]/', $new_password ) ) {
			return sprintf( __( 'Password must include at least one lowercase letter. Auto refresh in %s seconds.', 'ecommerce-product-catalog' ), '<span class="time">3</span>' );
		}

		// Check for at least one special character
		if ( ! preg_match( '/[\W_]/', $new_password ) ) {
			return sprintf( __( 'Password must include at least one special character. Auto refresh in %s seconds.', 'ecommerce-product-catalog' ), '<span class="time">3</span>' );
		}

		return true;
	}

}

$ic_customer_panel = new ic_customer_panel;

function ic_customer_panel_actions() {
	return apply_filters( 'customer_panel_actions', '' );
}

function ic_customer_panel_panel_url() {
	$url = apply_filters( 'ic_customer_panel_url', '' );
	if ( ! empty( $url ) ) {
		return $url;
	}
	$settings = ic_get_customer_panel_settings();
	if ( ! empty( $settings['page_id'] ) && $settings['page_id'] !== 'noid' ) {
		$status = get_post_status( $settings['page_id'] );
		if ( $status === 'publish' ) {
			return ic_get_permalink( $settings['page_id'] );
		}
	}

	return '';
}

function ic_customer_panel_panel_id() {
	$settings = ic_get_customer_panel_settings();
	if ( ! empty( $settings['page_id'] ) && $settings['page_id'] !== 'noid' ) {
		return $settings['page_id'];
	}

	return '';
}

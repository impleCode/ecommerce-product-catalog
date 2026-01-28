<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages digital customer
 *
 * Here digital customer is defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-digital-customers/includes
 * @author        Norbert Dreszer
 */
add_shortcode( 'login_form', 'ic_digital_customer_login_form' );

/**
 * Defines customer login form shortcode
 *
 * @param boolean $show
 * @param string $form_class
 * @param boolean $closer
 * @param string $title
 * @param string $desc
 *
 * @return string
 */
function ic_digital_customer_login_form(
	$show = true, $form_class = 'login_form', $closer = false, $title = null,
	$desc = ''
) {
	digital_customers_styles();
	$title              = $title != null ? $title : __( 'Customer Login', 'ecommerce-product-catalog' );
	$customer_panel_url = function_exists( 'ic_customer_panel_panel_url' ) ? ic_customer_panel_panel_url() : '';
	$customer_panel_url = empty( $customer_panel_url ) ? network_site_url( $_SERVER['REQUEST_URI'] ) : $customer_panel_url;
	$args               = array(
		'echo'           => false,
		'redirect'       => apply_filters( 'ic_login_form_redirect', $customer_panel_url ),
		'form_id'        => 'loginform',
		'label_username' => __( 'Username or Email', 'ecommerce-product-catalog' ),
		'label_password' => __( 'Password' ),
		'label_remember' => __( 'Remember Me' ),
		'label_log_in'   => __( 'Log In' ),
		'id_username'    => 'user_login',
		'id_password'    => 'user_pass',
		'id_remember'    => 'rememberme',
		'id_submit'      => 'wp-submit',
		'remember'       => true,
		'value_username' => '',
		'value_remember' => false
	);
	$style              = '';
	if ( ! $show ) {
		$style = 'style="display: none"';
	}
	$class = 'not-logged';
	if ( is_user_logged_in() ) {
		$class = 'logged';
	}
	$form = '<div class="' . $form_class . ' ' . $class . '" ' . $style . '>';
	$form .= '<div class="inside_login ui-tabs">';
	if ( $closer ) {
		$form .= '<span class="closer"></span>';
	}
	$form .= '<ul class="ui-tabs-nav">' . apply_filters( 'login_form_tabs', '<li><a href="#login_form">' . __( 'Login', 'catalog-customers-manager' ) . '</a></li>' ) . '</ul>';
	$form .= apply_filters( 'login_form_start', '<div id="login_form">' );
	$form .= '<h2>' . $title . '</h2>';
	$form .= $desc;
	$form .= ic_get_customer_login_actions();
	$form .= wp_login_form( $args ) . '</div>';
	$form .= '</div></div>';

	return $form;
}

/**
 * Shows customer login url
 *
 * @param boolean $echo
 * @param string $label
 *
 * @return string
 */
function ic_digital_customer_login_url( $echo = 1, $desc = '', $lower = false, $panel_redirect = false, $panel = true ) {
	$class  = 'not-logged';
	$button = '';
	if ( function_exists( 'ic_customer_panel_panel_url' ) ) {
		$panel_url = ic_customer_panel_panel_url();
	}
	if ( is_user_logged_in() ) {
		$desc  = '';
		$class = 'logged';
		if ( ! empty( $panel_url ) && $panel ) {
			$button = '<a href="' . $panel_url . '" class="button">' . __( 'My Account', 'ecommerce-product-catalog' ) . '</a> ';
		}
	}
	$redirect = $panel_redirect ? $panel_url : ic_current_page_url();
	$link     = $lower ? strtolower( wp_loginout( $redirect, false ) ) : wp_loginout( $redirect, false );
	$return   = '<span class="login_button ' . $class . '">' . $button . $link . ' ' . $desc . '</span>';
	$return   .= ic_digital_customer_login_form( false, 'login_form popup_login_form', true );

	return echo_ic_setting( $return, $echo );
}

if ( ! function_exists( 'ic_current_page_url' ) ) {

	/**
	 * Get current page URL from server global
	 *
	 * @return string
	 */
	function ic_current_page_url() {
		if ( is_ic_ajax() ) {
			if ( ! empty( $_SERVER['HTTP_REFERER'] ) ) {
				return $_SERVER['HTTP_REFERER'];
			} else {
				return product_listing_url();
			}
		} else if ( isset( $_SERVER['HTTP_HOST'] ) ) {
			$page_url = 'http';
			if ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] == 'on' ) {
				$page_url .= 's';
			}

			return $page_url . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		} else {
			return '';
		}
	}

}

add_filter( 'loginout', 'ic_loginout_digital_customers_class' );

/**
 * Adds a custom class to loginout
 *
 * @param string $link
 *
 * @return string
 */
function ic_loginout_digital_customers_class( $link ) {
	$class = 'class="button"';
	$link  = str_replace( '<a ', '<a ' . $class . ' ', $link );

	return $link;
}

add_shortcode( 'login_url', 'ic_digital_customer_login_url_shortcode' );

/**
 * Defines login URL shortcode
 *
 * @return string
 */
function ic_digital_customer_login_url_shortcode() {
	return ic_digital_customer_login_url( 0, '' );
}

/**
 * Manages login messages
 *
 * @return string
 */
function ic_get_customer_login_actions() {
	return apply_filters( 'customer_login_actions', '' );
}

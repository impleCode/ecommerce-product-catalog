<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Blocks digital customers to enter the default WordPress Admin
 *
 * Created by Norbert Dreszer.
 * Date: 11-Mar-15
 * Time: 13:22
 * Package: customer-panel-security.php
 */
class ic_customer_panel_security {

	function __construct() {
		add_action( 'ic_epc_loaded', array( __CLASS__, 'remove_admin_bar' ) );
		add_action( 'admin_init', array( __CLASS__, 'stop_access_profile' ) );
		add_action( 'admin_menu', array( __CLASS__, 'remove_demo_menus' ) );
		add_action( 'admin_init', array( __CLASS__, 'redirect_admin' ) );
		add_filter( 'login_url', array( __CLASS__, 'login_url' ), 10, 3 );
		add_filter( 'login_redirect', array( __CLASS__, 'login_url' ), 10, 3 );
		add_action( 'wp_login_failed', array( __CLASS__, 'login_fail' ) );
		add_action( 'authenticate', array( __CLASS__, 'check_password' ), 1, 3 );
		add_filter( 'customer_login_actions', array( __CLASS__, 'login_errors' ) );
		//add_action( 'init', array( __CLASS__, 'set_session_ref' ), 1 );
		add_filter( 'login_form_bottom', array( __CLASS__, 'set_session_ref' ), 1 );
	}

	/**
	 * Hide Admin bar for digital customers
	 */
	static function remove_admin_bar() {
		if ( is_ic_digital_customer() && ! current_user_can( 'administrator' ) ) {
			show_admin_bar( false );
		}
	}

	static function stop_access_profile() {
		if ( defined( 'IS_PROFILE_PAGE' ) && IS_PROFILE_PAGE && is_ic_digital_customer() && ! current_user_can( 'administrator' ) ) {
			wp_die( 'You are not permitted to change profile details.' );
		}
	}

	static function remove_demo_menus() {
		if ( is_ic_digital_customer() && ! current_user_can( 'administrator' ) ) {
			remove_menu_page( 'profile.php' );
			remove_menu_page( 'upload.php' );
			remove_submenu_page( 'users.php', 'profile.php' );
		}
	}

	static function redirect_admin() {
		if ( is_ic_digital_customer() && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) && ! current_user_can( 'administrator' ) ) {
			$url = ic_customer_panel_panel_url();
			if ( is_ic_shopping_cart() || is_ic_shopping_order() ) {
				$url = ic_current_page_url();
			}
			if ( ! empty( $url ) ) {
				wp_redirect( $url );
				exit;
			}
		}
	}

	/**
	 * Filters and modifies the login URL based on the user's roles and other conditions.
	 *
	 * @param string $url The original login URL.
	 * @param object $request The request object, typically representing the data passed during the login attempt.
	 * @param object $user The user object, containing details about the user attempting to log in.
	 *
	 * @return string The modified login URL after applying roles-based or condition-based redirects, or the original URL if no modifications are made.
	 */
	static function login_url( $url, $request, $user ) {
		if ( ! is_object( $user ) || is_wp_error( $user ) ) {
			return $url;
		}
		$panel_url   = ic_customer_panel_panel_url();
		$customer_id = null;
		if ( ! empty( $user->ID ) ) {
			$customer_id = intval( $user->ID );
		}
		if ( isset( $user->roles ) && is_array( $user->roles ) ) {
			// Redirect admins to the dashboard
			if ( in_array( 'administrator', $user->roles, true ) ) {
				return apply_filters( 'ic_admin_redirect_url', admin_url() );
			}

			// Redirect any user who can edit posts or pages to dashboard
			if ( user_can( $user->ID, 'edit_posts' ) || user_can( $user->ID, 'edit_pages' ) ) {
				return apply_filters( 'ic_admin_redirect_url', admin_url() );
			}
		}
		if ( function_exists( 'is_ic_shopping_cart' ) && is_ic_shopping_cart() ||
		     function_exists( 'is_ic_shopping_order' ) && is_ic_shopping_order() ) {
			$panel_url = ic_current_page_url();
		}
		if ( ! empty( $panel_url ) && is_ic_digital_customer( $customer_id ) && ! in_array( 'administrator', $user->roles, true ) ) {
			if ( is_string( $panel_url ) ) {
				return esc_url( $panel_url );
			}
		}

		return $url;
	}

	static function login_fail( $username ) {
		$ic_session = get_product_catalog_session();

		if ( empty( $ic_session['referrer'] ) ) {
			return;
		}
		$referrer = $ic_session['referrer'];
		if ( ! empty( $referrer ) && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' ) && ! current_user_can( 'administrator' ) ) {
			$url = ic_customer_panel_panel_url();
			if ( ! empty( $url ) ) {
				$url = add_query_arg( 'login', 'failed', $url );
				wp_redirect( $url );
				exit;
			}
		}
	}

	/**
	 * Redirects to customer panel when username or password is empty
	 *
	 * @param type $login
	 * @param string $username
	 * @param string $password
	 */
	static function check_password( $login, $username, $password ) {
		$ic_session = get_product_catalog_session();
		if ( empty( $ic_session['referrer'] ) ) {
			return;
		}
		$referrer = $ic_session['referrer'];
		if ( ! empty( $referrer ) && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' ) ) {
			if ( $username == "" || $password == "" ) {
				$url = ic_customer_panel_panel_url();
				if ( ! empty( $url ) ) {
					$url = add_query_arg( 'login', 'empty', $url );
					wp_redirect( $url );
					exit;
				}
			}
		}
	}

	/**
	 * Adds customer login error messages
	 *
	 * @param string $actions
	 *
	 * @return string
	 */
	static function login_errors( $actions ) {
		if ( isset( $_GET['login'] ) && $_GET['login'] == 'failed' ) {
			$redirect = ic_customer_panel_panel_url();
			$actions  .= implecode_warning( sprintf( __( 'The password you entered is incorrect, please try again or %sreset password%s.' ), '<a href="' . wp_lostpassword_url( $redirect ) . '">', '</a>' ), 0 );
		} else if ( isset( $_GET['login'] ) && $_GET['login'] == 'empty' ) {
			$actions .= implecode_warning( __( 'Please enter both username and password.' ), 0 );
		}

		return $actions;
	}

	/**
	 * Define session referrer
	 *
	 */
	static function set_session_ref( $login_form = '' ) {
		if ( is_admin() && ( function_exists( 'is_ic_ajax' ) && ! is_ic_ajax() ) ) {
			return $login_form;
		}
		$ic_session = get_product_catalog_session();
		if ( isset( $ic_session['next_referrer'] ) ) {
			// Get existing referrer
			$ic_session['referrer'] = $ic_session['next_referrer'];
		} else if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			// Use given referrer
			$ic_session['referrer'] = $_SERVER['HTTP_REFERER'];
		} else {
			$ic_session['referrer'] = '';
		}

// Save current page as next page's referrer
		$ic_session['next_referrer'] = ic_current_page_url();
		set_product_catalog_session( $ic_session );

		return $login_form;
	}

}

$ic_customer_panel_security = new ic_customer_panel_security;

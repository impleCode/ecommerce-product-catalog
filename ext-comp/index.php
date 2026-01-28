<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages externa compatibility functions folder
 *
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/ext-comp
 * @author        impleCode
 */
add_action( 'ic_epc_loaded', 'run_ext_comp_files' );

function run_ext_comp_files() {
	require_once( AL_BASE_PATH . '/ext-comp/builders.php' );
	if ( class_exists( 'Polylang' ) || function_exists( 'icl_object_id' ) ) {
		require_once( AL_BASE_PATH . '/ext-comp/multilingual.php' );
	}

	if ( defined( 'WPSEO_VERSION' ) ) {
		require_once( AL_BASE_PATH . '/ext-comp/wpseo.php' );
	}

	if ( defined( 'QTS_VERSION' ) ) {
		require_once( AL_BASE_PATH . '/ext-comp/qtranslate-slug.php' );
	}

	if ( class_exists( 'Jetpack' ) ) {
		require_once( AL_BASE_PATH . '/ext-comp/jetpack.php' );
	}
	if ( did_action( 'elementor/loaded' ) ) {
		add_action( 'elementor/init', 'ic_load_elementor_integration' );
		if ( ! function_exists( 'ic_load_elementor_integration' ) ) {
			function ic_load_elementor_integration() {
				require_once( AL_BASE_PATH . '/ext-comp/elementor/index.php' );
			}
		}
	}

	if ( class_exists( 'WooCommerce' ) ) {
		require_once( AL_BASE_PATH . '/ext-comp/woocommerce.php' );
	}
}

if ( ! function_exists( 'run_ic_session' ) ) {
	add_action( 'ic_epc_included', 'run_ic_session', - 1 );
	add_filter( 'wp_session_expiration_variant', 'ic_wp_session_expiration_variant', 999999 );
	add_filter( 'wp_session_expiration', 'ic_wp_session_expiration', 999999 );

	function run_ic_session() {
		if ( is_admin() && ! is_ic_ajax() ) {

			return;
		}
		if ( ! class_exists( 'ic_session' ) && ! ic_use_php_session() /* && (!is_admin() || is_ic_front_ajax()) ADD THIS IN NEW VERSION */ ) {
			require_once( AL_BASE_PATH . '/includes/ic_session.php' );
			$ic_session  = new ic_session;
			$initialized = $ic_session->init();
			if ( $initialized ) {
				if ( ic_ic_cookie_enabled() ) {
					$ic_session->set_customer_session_cookie();
				}
				ic_save_global( 'ic_session', $ic_session );
			} else {
				if ( ! defined( 'WP_SESSION_COOKIE' ) ) {
					define( 'WP_SESSION_COOKIE', '_wp_session' );
				}
				if ( ! class_exists( 'WP_Session' ) ) {
					require_once( AL_BASE_PATH . '/ext-comp/wp_session/class-wp-session.php' );
				}
				if ( ! function_exists( 'wp_session_cache_expire' ) ) {
					require_once( AL_BASE_PATH . '/ext-comp/wp_session/wp-session.php' );
				}
			}
		}
		get_product_catalog_session();
	}

	function ic_wp_session_expiration_variant() {
		return 30 * 60 * 23;
	}

	function ic_wp_session_expiration() {
		return 30 * 60 * 24;
	}

}

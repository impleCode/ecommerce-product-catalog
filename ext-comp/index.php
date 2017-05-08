<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages externa compatibility functions folder
 *
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/ext-comp
 * @author 		Norbert Dreszer
 */
add_action( 'ic_epc_loaded', 'run_ext_comp_files' );

function run_ext_comp_files() {
	if ( function_exists( 'pll_get_post' ) || function_exists( 'icl_object_id' ) ) {
		require_once(AL_BASE_PATH . '/ext-comp/multilingual.php');
	}

	if ( defined( 'WPSEO_VERSION' ) ) {
		require_once(AL_BASE_PATH . '/ext-comp/wpseo.php');
	}

	if ( defined( 'QTS_VERSION' ) ) {
		require_once(AL_BASE_PATH . '/ext-comp/qtranslate-slug.php');
	}
}

if ( !function_exists( 'run_ic_session' ) ) {
	add_action( 'ic_epc_loaded', 'run_ic_session', -1 );

	function run_ic_session() {
		if ( (!is_admin() || (defined( 'DOING_AJAX' ) && DOING_AJAX )) && !class_exists( 'WP_Session' ) && !ic_use_php_session() ) {

			if ( !defined( 'WP_SESSION_COOKIE' ) ) {
				define( 'WP_SESSION_COOKIE', '_wp_session' );
			}
			if ( !class_exists( 'Recursive_ArrayAccess' ) ) {
				require_once(AL_BASE_PATH . '/ext-comp/wp_session/class-recursive-arrayaccess.php');
			}
			if ( !class_exists( 'WP_Session_Utils' ) ) {
				require_once(AL_BASE_PATH . '/ext-comp/wp_session/class-wp-session-utils.php');
			}
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				require_once(AL_BASE_PATH . '/ext-comp/wp_session/wp-cli.php');
			}
			require_once(AL_BASE_PATH . '/ext-comp/wp_session/class-wp-session.php');
			require_once(AL_BASE_PATH . '/ext-comp/wp_session/wp-session.php');
			add_filter( 'wp_session_expiration_variant', 'ic_wp_session_expiration_variant', 99999 );
			add_filter( 'wp_session_expiration', 'ic_wp_session_expiration', 99999 );
			//WP_Session::get_instance();
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

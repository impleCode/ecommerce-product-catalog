<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *
 *  @version       1.0.0
 *  @author        impleCode
 *
 */

class ic_cached_cart {

	/**
	 * @var string
	 */
	private $transient_name = 'ic_cached_cart';

	function __construct() {
		add_action( 'admin_init', array( $this, 'detect_cached_cart' ) );
		add_action( 'activate_plugin', array( $this, 'clear_transient' ) );
		add_action( 'deactivate_plugin', array( $this, 'clear_transient' ) );
		add_filter( 'body_class', array( $this, 'cache_class' ) );
	}

	function cache_class( $classes ) {
		if ( defined( 'WP_CACHE' ) && WP_CACHE && $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
			$classes[] = 'ic_cache';
		} else {
			$cached_cart = get_option( $this->transient_name );
			if ( isset( $cached_cart[1] ) && $cached_cart[1] ) {
				$classes[] = 'ic_cache';
			}
		}

		return $classes;
	}

	function detect_cached_cart() {
		if ( ! filter_var( ini_get( 'allow_url_fopen' ), FILTER_VALIDATE_BOOLEAN ) ) {
			return;
		}
		$cart_url = ic_shopping_cart_page_url();
		if ( empty( $cart_url ) || ! wp_http_validate_url( $cart_url ) ) {
			return;
		}
		$cached_cart = get_option( $this->transient_name );
		if ( ! isset( $cached_cart[0] ) || $cached_cart[0] !== $cart_url ) {
			$header = @get_headers( $cart_url );
			if ( $header === false ) {
				return;
			}
			$cache_found   = false;
			$cache_strings = $this->cache_strings();
			foreach ( $cache_strings as $cache_string ) {
				if ( ic_string_contains( $header, $cache_string, false, true ) ) {
					$cache_found = true;
					break;
				}
			}
			update_option( $this->transient_name, array(
				$cart_url,
				intval( $cache_found )
			) );
		}
	}

	function cache_strings() {
		return array(
			'Cache',
			'cloudflare',
			'proxy',
			'varnish',
			'Vary: X-Forwarded-Proto',
			'P-LB',
			'Cache-Control'
		);
	}

	function clear_transient() {
		delete_option( $this->transient_name );
	}
}
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages implecode global variable functions
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        impleCode
 */
if ( ! function_exists( 'ic_get_global' ) ) {

	/**
	 * Returns implecode global
	 *
	 * @param type $name
	 *
	 * @return string
	 * @global type $implecode
	 */
	function ic_get_global( $name = null, $cached = false ) {
		global $implecode;
		if ( ! empty( $name ) ) {
			if ( isset( $implecode[ $name ] ) ) {
				return $implecode[ $name ];
			} else {
				if ( $cached ) {
					$cached_value = wp_cache_get( $name, 'implecode' );
					if ( $cached_value !== false ) {
						return $cached_value;
					}
				}
				$fallback = apply_filters( 'ic_get_global', false, $name, $cached );
				if ( $fallback !== false ) {
					if ( $cached ) {
						wp_cache_set( $name, $fallback, 'implecode' );
					}

					return $fallback;
				}

				return false;
			}
		} else {
			return $implecode;
		}
	}

}

if ( ! function_exists( 'ic_delete_global' ) ) {

	/**
	 * Deletes implecode global
	 *
	 * @param type $name
	 *
	 * @return string
	 * @global type $implecode
	 */
	function ic_delete_global( $name = null ) {
		global $implecode;
		if ( ! empty( $name ) ) {
			do_action( 'ic_delete_global', $name );
			unset( $implecode[ $name ] );
		} else {
			unset( $implecode );
		}
	}

}

if ( ! function_exists( 'ic_save_global' ) ) {

	/**
	 * Saves implecode global
	 *
	 * @param string $name
	 * @param type $value
	 *
	 * @return boolean
	 * @global array $implecode
	 */
	function ic_save_global( $name, $value, $product_listing_globals = false, $cached = false, $admin = false ) {
		if ( ! $admin && is_ic_admin() && ! ic_is_rendering_block() ) {
			return false;
		}
		global $implecode;
		if ( ! empty( $name ) ) {
			if ( $cached ) {
				wp_cache_set( $name, $value, 'implecode' );
			}
			if ( $value === null ) {
				$value = '';
			}
			$implecode[ $name ] = $value;
			do_action( 'ic_save_global', $name, $value, $product_listing_globals, $cached );
			if ( $product_listing_globals ) {
				if ( empty( $implecode['product_listing_globals'] ) ) {
					$implecode['product_listing_globals'] = array();
				}
				if ( ! in_array( $name, $implecode['product_listing_globals'] ) ) {
					$implecode['product_listing_globals'][] = $name;
				}
			}

			return true;
		}

		return false;
	}

}

if ( ! function_exists( 'ic_reset_listing_globals' ) ) {

	function ic_reset_listing_globals() {
		global $implecode;
		if ( empty( $implecode['product_listing_globals'] ) ) {
			$implecode['product_listing_globals'] = array();
		}
		foreach ( $implecode['product_listing_globals'] as $global_name ) {
			if ( ! empty( $global_name ) ) {
				ic_delete_global( $global_name );
			}
		}
		$implecode['product_listing_globals'] = array();
	}

}

if ( ! function_exists( 'ic_set_product_id' ) ) {

	function ic_set_product_id( $product_id, $product_listing = false, $cached = false, $admin = false ) {
		$initial_product_id = ic_get_global( 'prev_product_id' );
		$prev_product_id    = ic_get_global( 'product_id' );
		if ( empty( $initial_product_id ) && ! empty( $prev_product_id ) ) {
			ic_save_global( 'prev_product_id', intval( $prev_product_id ), $product_listing, $cached, $admin );
		}
		ic_save_global( 'product_id', intval( $product_id ), $product_listing, $cached, $admin );
	}
}

if ( ! function_exists( 'ic_reset_product_id' ) ) {

	function ic_reset_product_id() {
		$prev_product_id = ic_get_global( 'prev_product_id' );
		if ( ! empty( $prev_product_id ) ) {
			ic_save_global( 'product_id', intval( $prev_product_id ) );
		} else {
			ic_delete_global( 'product_id' );
		}
	}
}
<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages implecode global variable functions
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        Norbert Dreszer
 */

/**
 * Returns implecode global
 *
 * @global type $implecode
 * @param type $name
 * @return string
 */
function ic_get_global( $name = null ) {
	global $implecode;
	if ( !empty( $name ) ) {
		if ( isset( $implecode[ $name ] ) ) {
			return $implecode[ $name ];
		} else {
			return false;
		}
	} else {
		return $implecode;
	}
}

/**
 * Deletes implecode global
 *
 * @global type $implecode
 * @param type $name
 * @return string
 */
function ic_delete_global( $name = null ) {
	global $implecode;
	if ( !empty( $name ) ) {
		unset( $implecode[ $name ] );
	} else {
		unset( $implecode );
	}
}

/**
 * Saves implecode global
 *
 * @global array $implecode
 * @param string $name
 * @param type $value
 * @return boolean
 */
function ic_save_global( $name, $value ) {
	global $implecode;
	if ( !empty( $name ) ) {
		$implecode[ $name ] = $value;
		return true;
	}
	return false;
}

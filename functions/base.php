<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages wordpress core fields
 *
 * Here all wordpress fields are redefined.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        Norbert Dreszer
 */
add_filter( 'enter_title_here', 'al_enter_title_here' );

/**
 * Modifies product name field placeholder
 *
 * @param str $message
 * @return str
 */
function al_enter_title_here( $message ) {
	$screen = get_current_screen();
	if ( ic_string_contains( $screen->id, 'al_product' ) ) {
		if ( is_plural_form_active() ) {
			$names	 = get_catalog_names();
			$message = sprintf( __( 'Enter %s name here', 'al-ecommerce-product-catalog' ), strtolower( $names[ 'singular' ] ) );
		} else {
			$message = __( 'Enter name here', 'al-ecommerce-product-catalog' );
		}
	}
	return $message;
}

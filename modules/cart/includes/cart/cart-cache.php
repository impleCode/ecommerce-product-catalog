<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages shopping cart
 *
 * Here shopping cart functions are defined and managed.
 *
 * @version		1.0.0
 * @package		implecode-quote-cart/includes
 * @author 		Norbert Dreszer
 */
add_filter( 'body_class', 'ic_cache_body_class' );

/**
 * Adds ic_cache class to body to handle cache support
 *
 * @param string $classes
 * @return string
 */
function ic_cache_body_class( $classes ) {
	if ( defined( 'WP_CACHE' ) && WP_CACHE && $_SERVER[ 'REQUEST_METHOD' ] !== 'POST' ) {
		$classes[] = 'ic_cache';
	}
	return $classes;
}

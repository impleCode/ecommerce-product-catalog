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
if ( ! class_exists( ( 'ic_catalog_widget' ) ) ) {
	require_once( dirname( __FILE__ ) . '/widget.php' );
}
if ( ! class_exists( ( 'ic_catalog_menu_element' ) ) ) {
	require_once( dirname( __FILE__ ) . '/menu.php' );
}

if ( ! function_exists( 'ic_error_log' ) ) {

	function ic_error_log( $what, $param = false, $cron = false ) {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}
		if ( $param && ! isset( $_GET[ $param ] ) && ! is_ic_front_ajax() ) {
			return;
		}
		if ( $cron || ! wp_doing_cron() ) {
			$prefix = '';
			if ( is_ic_ajax() ) {
				$prefix = 'ajax ';
			}
			error_log( $prefix . print_r( $what, 1 ) );
		}
	}

}

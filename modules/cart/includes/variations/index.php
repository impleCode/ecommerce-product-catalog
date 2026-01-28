<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages framework folder
 *
 * Here framework folder files defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog-pro/framework
 * @author        Norbert Dreszer
 */
if ( ! function_exists( 'ic_is_variations_price_effect_active' ) ) {
	require_once( dirname( __FILE__ ) . '/variations-conditionals.php' );
}
if ( ! function_exists( 'product_variations_menu' ) ) {
	require_once( dirname( __FILE__ ) . '/product-variations-settings.php' );
}
if ( ! function_exists( 'add_variation_metaboxes' ) && ! class_exists( 'ic_cart_variations_meta' ) ) {
	require_once( dirname( __FILE__ ) . '/functions.php' );
	require_once( dirname( __FILE__ ) . '/variations-meta.php' );
	require_once( dirname( __FILE__ ) . '/variations-selectors.php' );
	require_once( dirname( __FILE__ ) . '/variations-front.php' );
	require_once( dirname( __FILE__ ) . '/variations-ajax.php' );
	require_once( dirname( __FILE__ ) . '/product-variations.php' );
	require_once( dirname( __FILE__ ) . '/variations-details.php' );
}

if ( ! function_exists( 'ic_variations_register_admin_styles' ) ) {
	add_action( 'register_catalog_admin_styles', 'ic_variations_register_admin_styles' );

	function ic_variations_register_admin_styles() {
		wp_register_style( 'ic_variations_admin', plugins_url( '/', __FILE__ ) . 'css/variations-admin.css' . ic_filemtime( dirname( __FILE__ ) . '/css/variations-admin.css' ) );
		wp_register_script( 'ic_variations_admin', plugins_url( '/', __FILE__ ) . 'js/variations-admin.js' . ic_filemtime( dirname( __FILE__ ) . '/js/variations-admin.js' ), array( 'ic_chosen' ) );
	}

	add_action( 'enqueue_catalog_admin_scripts', 'ic_variations_enqueue_admin_styles' );

	function ic_variations_enqueue_admin_styles() {
		wp_enqueue_style( 'ic_variations_admin' );
		wp_enqueue_script( 'ic_variations_admin' );
	}

	add_action( 'register_catalog_styles', 'ic_variations_register_styles' );

	function ic_variations_register_styles() {
		wp_register_style( 'ic_variations', plugins_url( '/', __FILE__ ) . 'css/variations-front.css' );
		wp_register_script( 'ic_variations', plugins_url( '/', __FILE__ ) . 'js/variations.js' . ic_filemtime( dirname( __FILE__ ) . '/js/variations.js' ) );
	}

	add_action( 'enqueue_catalog_scripts', 'ic_variations_enqueue_styles' );

	function ic_variations_enqueue_styles() {
		wp_enqueue_style( 'ic_variations' );
		wp_enqueue_script( 'ic_variations' );
	}

}

require_once( dirname( __FILE__ ) . '/ext/index.php' );

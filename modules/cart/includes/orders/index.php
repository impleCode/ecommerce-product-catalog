<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'AL_PO_BASE_PATH', dirname( __FILE__ ) );
define( 'AL_PO_PLUGIN_BASE_URL', plugins_url( '/', __FILE__ ) );

require_once(AL_PO_BASE_PATH . '/functions/activation.php');

add_action( 'ecommerce-prodct-catalog-addons', 'start_digital_products_orders', 20 );

function start_digital_products_orders() {
	require_once(AL_PO_BASE_PATH . '/includes/index.php');
	require_once(AL_PO_BASE_PATH . '/functions/index.php');
	require_once(AL_PO_BASE_PATH . '/ext/index.php');
	do_action( 'digital_product_orders_addons' );
}

add_action( 'register_catalog_admin_styles', 'digital_orders_admin_register_styles' );

function digital_orders_admin_register_styles() {
	wp_register_style( 'al_digital_products_admin_styles', '/wp-content/plugins/' . dirname( plugin_basename( __FILE__ ) ) . '/css/admin-style.css?' . filemtime( AL_PO_BASE_PATH . '/css/admin-style.css' ) );
}

add_action( 'enqueue_catalog_admin_styles', 'digital_orders_admin_enqueue_styles' );

function digital_orders_admin_enqueue_styles() {
	wp_enqueue_style( 'al_digital_products_admin_styles' );
}

add_action( 'catalog_register_scripts', 'digital_orders_register_styles' );

function digital_orders_register_styles() {
	wp_register_style( 'al_digital_products_styles', '/wp-content/plugins/' . dirname( plugin_basename( __FILE__ ) ) . '/css/style.css' );
}

add_action( 'catalog_enqueue_scripts', 'digital_orders_enqeueue_styles' );

function digital_orders_enqeueue_styles() {
	wp_enqueue_style( 'al_digital_products_styles' );
}

add_action( 'enqueue_catalog_admin_styles', 'country_chosen_admin_select_script' );

function country_chosen_admin_select_script() {
	if ( is_ic_order_edit_screen() ) {
		wp_enqueue_script( 'digital_order_edit_script', AL_PO_PLUGIN_BASE_URL . 'js/edit-order.js' . ic_filemtime( AL_PO_BASE_PATH . '/js/edit-order.js' ), array( 'ic_chosen' ) );
		//	if ( ic_ic_order_manual() ) {
		if ( function_exists( 'start_digital_products' ) ) {
			$translation_array = array(
				'digital_products_dropdown_action' => digital_products_dropdown( 'new_manual_order_product_id', __( 'Choose product from catalog', 'ecommerce-product-catalog' ), '' ),
			);
		} else {
			$translation_array = array(
				'digital_products_dropdown_action' => ic_select_product( __( 'Choose product from catalog', 'ecommerce-product-catalog' ), '', 'new_manual_order_product_id', 'digital_products_dropdown', 0 ),
			);
		}
		wp_localize_script( 'digital_order_edit_script', 'digital_order_edit_script_trans', apply_filters( 'digital_order_edit_sctipt_translations', $translation_array ) );
		//}
	}
}

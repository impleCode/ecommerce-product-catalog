<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages shopping cart
 *
 * Here shopping cart functions are defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-quote-cart/includes
 * @author        Norbert Dreszer
 */
class ic_cart_ajax {

	function __construct() {
		add_action( 'wp_ajax_nopriv_ic_add_to_cart', array( $this, 'add_to_cart' ) );
		add_action( 'wp_ajax_ic_add_to_cart', array( $this, 'add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_ic_get_cart_button', array( $this, 'get_cart_button' ) );
		add_action( 'wp_ajax_ic_get_cart_button', array( $this, 'get_cart_button' ) );
		add_action( 'register_catalog_styles', array( $this, 'register_styles' ) );
		add_action( 'enqueue_catalog_scripts', array( $this, 'enqueue_styles' ) );
	}

	/**
	 * Manages ajax price format
	 *
	 */
	function add_to_cart() {

		if ( isset( $_POST['add_cart_data'] ) ) {
			$params    = array();
			$ajax_post = $_POST;
			parse_str( $_POST['add_cart_data'], $params );
			$_POST = $params;
//ic_cart_content( true );
			$cart_page = ic_shopping_cart_page_url();
			$return    = array();
			if ( ! empty( $cart_page ) ) {
				$return['cart-added-info'] = apply_filters( 'ic_cart_added_info_html', ic_product_cart_added_info_html( '', $cart_page ), $cart_page );
			} else if ( current_user_can( 'manage_product_settings' ) ) {
				$return['cart-added-info'] = implecode_warning( sprintf( __( 'Please %1$sselect the shopping cart pages%2$s for the cart to work correctly.', 'ecommerce-product-catalog' ), '<a href="' . admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=shopping-cart' ) . '">', '</a>' ), 0 );
			}
			$cart_content = false;
			if ( ! empty( $ajax_post['cart_widget'] ) ) {
				$cart_content          = true;
				$label                 = isset( $ajax_post['label'] ) ? sanitize_text_field( $ajax_post['label'] ) : '';
				$return['cart-widget'] = ic_shopping_cart_button( false, $label );
			}
			if ( ! empty( $ajax_post['cart_container'] ) ) {
				$cart_content             = true;
				$return['cart-container'] = shopping_cart_products( false );
			}
			if ( ! $cart_content ) {
				ic_shopping_cart_content( true );
			}
			$echo = json_encode( $return );
			echo $echo;
		}

		wp_die();
	}

	function get_cart_button() {
		$label = isset( $_POST['label'] ) ? esc_attr( $_POST['label'] ) : '';
		echo ic_shopping_cart_button( false, $label );
		wp_die();
	}

	function register_styles() {
//wp_register_style( 'ic_variations', plugins_url( '/', __FILE__ ) . '/css/variations-front.css' );
		wp_register_script( 'ic_ajax_cart', plugins_url( '/', __FILE__ ) . 'js/ic-cart.js?' . filemtime( dirname( __FILE__ ) . '/js/ic-cart.js' ) );
	}

	function enqueue_styles() {
//wp_enqueue_style( 'ic_variations' );
		wp_enqueue_script( 'ic_ajax_cart' );
	}

}

global $ic_cart_ajax;
$ic_cart_ajax = new ic_cart_ajax;

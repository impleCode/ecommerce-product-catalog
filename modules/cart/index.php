<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ic_shopping_ecommerce_cart' ) ) {

	/**
	 * Shopping Cart module
	 */
	class ic_shopping_ecommerce_cart {

		function __construct() {
			$this->constants();
			$this->activation();
			add_action( 'ecommerce-prodct-catalog-addons', array( $this, 'include_files' ) );
			add_action( 'register_catalog_styles', array( $this, 'register_styles' ) );
			add_action( 'enqueue_catalog_scripts', array( $this, 'enqueue_styles' ) );
			add_action( 'register_catalog_admin_styles', array( $this, 'register_admin_styles' ) );
			add_action( 'enqueue_catalog_admin_scripts', array( $this, 'enqueue_admin_styles' ) );
			add_filter( 'ic_maybe_engueue_all', array( $this, 'enqueue_condition' ) );
		}

		function constants() {
			if ( ! defined( 'IC_USE_COOKIES' ) ) {
				define( 'IC_USE_COOKIES', true );
			}
			if ( ! defined( 'AL_SC_BASE_URL' ) ) {
				define( 'AL_SC_BASE_URL', plugins_url( '/', __FILE__ ) );
			}
			if ( ! defined( 'AL_SC_BASE_PATH' ) ) {
				define( 'AL_SC_BASE_PATH', dirname( __FILE__ ) );
			}
		}

		function activation() {
			require_once( AL_SC_BASE_PATH . '/functions/activation.php' );
			register_activation_hook( __FILE__, 'ic_cart_add_customer_role' );
		}

		function include_files() {
			if ( ! function_exists( 'start_quote_form' ) ) {
				require_once( AL_SC_BASE_PATH . '/ext/formbuilder/index.php' );
			}
			require_once( AL_SC_BASE_PATH . '/includes/index.php' );
			require_once( AL_SC_BASE_PATH . '/functions/index.php' );
		}

		/**
		 * Registers shopping cart CSS and JS
		 *
		 */
		function register_styles() {
			wp_register_style( 'al_shopping_cart_styles', AL_SC_BASE_URL . 'css/shopping-cart.min.css' . ic_filemtime( AL_SC_BASE_PATH . '/css/shopping-cart.min.css' ), array( 'dashicons' ) );
			wp_register_script( 'al_shopping_cart_scripts', AL_SC_BASE_URL . 'js/shopping-cart.min.js' . ic_filemtime( AL_SC_BASE_PATH . '/js/shopping-cart.min.js' ) );
		}

		/**
		 * Enqueues shopping cart CSS and JS
		 *
		 */
		function enqueue_styles() {
			if ( ! function_exists( 'get_currency_settings' ) ) {
				return;
			}
			wp_enqueue_style( 'al_shopping_cart_styles' );
			wp_enqueue_script( 'al_shopping_cart_scripts' );
			$currency = get_currency_settings();
			wp_localize_script( 'al_shopping_cart_scripts', 'ic_cart_ajax_object', array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'dec_sep'     => $currency['dec_sep'],
				'th_sep'      => $currency['th_sep'],
				'total_label' => __( 'Purchase Total', 'ecommerce-product-catalog' )
			) );
		}

		/**
		 * Registers shopping cart CSS and JS
		 *
		 */
		function register_admin_styles() {
			wp_register_script( 'ic_chosen', AL_SC_BASE_URL . 'ext/chosen/chosen.jquery.min.js' );
			//wp_register_script( 'ic_shopping_cart_admin', AL_SC_BASE_URL . 'js/shopping-cart-admin.js', array( 'ic_chosen' ) );
			wp_register_style( 'ic_chosen', AL_SC_BASE_URL . 'ext/chosen/chosen.min.css' );
		}

		/**
		 * Enqueues shopping cart CSS and JS
		 *
		 */
		function enqueue_admin_styles() {
			/*
			wp_localize_script( 'ic_shopping_cart_admin', 'admin_cart_object', array(
				'tax_limit_placeholder' => __( 'Leave empty for any', 'ecommerce-product-catalog' ),
				'tax_limit_label'       => sprintf( __( '%s values', 'ecommerce-product-catalog' ), '[field_name]' )
			) );
			wp_enqueue_script( 'ic_shopping_cart_admin' );
			*/
			wp_enqueue_style( 'ic_chosen' );
		}

		function enqueue_condition( $false ) {
			if ( is_ic_shopping_page() || ( function_exists( 'is_ic_customer_panel' ) && is_ic_customer_panel() ) ) {
				return true;
			}

			return $false;
		}

	}

}

if ( ! function_exists( 'start_shopping_cart' ) ) {


	function start_shopping_cart() {
		global $ic_shopping_ecommerce_cart;
		$ic_shopping_ecommerce_cart = new ic_shopping_ecommerce_cart;
	}

	start_shopping_cart();
}

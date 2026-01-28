<?php
/**
 * Plugin Name: eCommerce Product Catalog for WordPress
 * Plugin URI: https://implecode.com/wordpress/product-catalog/#cam=in-plugin-urls&key=plugin-url
 * Description: Easy to use, powerful and beautiful WordPress eCommerce plugin from impleCode. A Great choice if you want to sell easy and quick. Or beautifully present your products on a WordPress website. Full WordPress integration does a great job not only for Merchants but also for Developers and Theme Constructors.
 * Version: 3.4.7
 * Author: impleCode
 * Author URI: https://implecode.com/#cam=in-plugin-urls&key=author-url
 * Text Domain: ecommerce-product-catalog
 * Domain Path: /lang
 *
 * Copyright: 2025 impleCode.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'eCommerce_Product_Catalog' ) ) {

	/**
	 * Main eCommerce_Product_Catalog Class
	 *
	 * @since 2.4.7
	 */
	final class eCommerce_Product_Catalog {

		/**
		 * @var eCommerce_Product_Catalog The one true eCommerce_Product_Catalog
		 * @since 2.4.7
		 */
		private static $instance;

		/**
		 * Main eCommerce_Product_Catalog Instance
		 *
		 * Insures that only one instance of eCommerce_Product_Catalog exists in memory at any one
		 * time.
		 *
		 * @return type
		 * @since 2.4.7
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof eCommerce_Product_Catalog ) ) {
				self::$instance = new eCommerce_Product_Catalog;
				self::$instance->setup_constants();
				require_once( AL_BASE_PATH . '/includes/tracking.php' );

				//add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
				//add_action( 'plugins_loaded', array( self::$instance, 'implecode_addons' ), 30 );
				add_action( 'admin_enqueue_scripts', array( self::$instance, 'implecode_run_admin_styles' ) );
				add_action( 'admin_init', array( self::$instance, 'implecode_register_styles' ) );
				add_action( 'wp', array( self::$instance, 'implecode_register_styles' ) );
				add_action( 'admin_init', array( self::$instance, 'implecode_register_admin_styles' ) );
				add_action( 'wp_enqueue_scripts', array( self::$instance, 'implecode_enqueue_styles' ), 9 );
				add_action( 'init', array( self::$instance, 'load_textdomain' ) );
				add_action( 'after_setup_theme', array( self::$instance, 'content' ), - 2 );
			}

			return self::$instance;
		}

		public static function content() {
			self::$instance->includes();
			self::$instance->implecode_addons();
			do_action( 'ic_epc_loaded' );
		}

		/**
		 * Disable cloning
		 *
		 *
		 * @return void
		 * @since 2.4.7
		 * @access protected
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ecommerce-product-catalog' ), '4.3' );
		}

		/**
		 * Disable unserializing of the class
		 *
		 * @return void
		 * @since 2.4.7
		 * @access protected
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ecommerce-product-catalog' ), '4.3' );
		}

		/**
		 * Setup plugin constants
		 *
		 * @access private
		 * @return void
		 * @since 2.4.7
		 */
		private function setup_constants() {
			if ( ! defined( 'AL_BASE_PATH' ) ) {
				define( 'AL_BASE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
			}
			if ( ! defined( 'AL_PLUGIN_BASE_PATH' ) ) {
				define( 'AL_PLUGIN_BASE_PATH', plugins_url( '/', __FILE__ ) );
			}
			if ( ! defined( 'AL_PLUGIN_MAIN_FILE' ) ) {
				define( 'AL_PLUGIN_MAIN_FILE', __FILE__ );
			}
			if ( ! defined( 'AL_BASE_TEMPLATES_PATH' ) ) {
				define( 'AL_BASE_TEMPLATES_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
			}
			if ( ! defined( 'IC_EPC_VERSION' ) ) {
				if ( function_exists( 'get_file_data' ) ) {
					$default_headers = array(
						'Version' => 'Version',
					);
					$plugin_data     = get_file_data( AL_PLUGIN_MAIN_FILE, $default_headers, 'plugin' );
				}
				if ( ! empty( $plugin_data["Version"] ) ) {
					define( 'IC_EPC_VERSION', $plugin_data["Version"] );
				} else {
					define( 'IC_EPC_VERSION', '2.7.21' );
				}
			}
			if ( ! defined( 'IC_CATALOG_VERSION' ) ) {
				define( 'IC_CATALOG_VERSION', IC_EPC_VERSION );
			}
			if ( ! defined( 'IC_EPC_FIRST_VERSION' ) ) {
				$first_version = (string) get_option( 'first_activation_version', IC_EPC_VERSION );
				define( 'IC_EPC_FIRST_VERSION', $first_version );
			}
		}

		/**
		 * Include required files
		 *
		 * @access private
		 * @return void
		 * @since 2.4.7
		 */
		private function includes() {
			require_once( AL_BASE_PATH . '/ic/index.php' );
			require_once( AL_BASE_PATH . '/functions/activation.php' );

			require_once( AL_BASE_PATH . '/templates.php' );

			require_once( AL_BASE_PATH . '/functions/index.php' );
			require_once( AL_BASE_PATH . '/includes/index.php' );

			require_once( AL_BASE_PATH . '/includes/blocks/index.php' );

			require_once( AL_BASE_PATH . '/theme-product_adder_support.php' );

			require_once( AL_BASE_PATH . '/includes/product-settings.php' );
			require_once( AL_BASE_PATH . '/functions/base.php' );
			require_once( AL_BASE_PATH . '/functions/capabilities.php' );
			require_once( AL_BASE_PATH . '/functions/functions.php' );
			require_once( AL_BASE_PATH . '/config/const.php' );

			require_once( AL_BASE_PATH . '/functions/shortcodes.php' );
			require_once( AL_BASE_PATH . '/ext-comp/index.php' );

			require_once( AL_BASE_PATH . '/modules/index.php' );
			do_action( 'ic_epc_included' );
		}

		/**
		 * Registers catalog styles and scripts
		 */
		public function implecode_register_styles() {
			wp_register_style( 'al_product_styles', AL_PLUGIN_BASE_PATH . 'css/al_product.min.css' . ic_filemtime( AL_BASE_PATH . '/css/al_product.min.css' ) );
			do_action( 'register_catalog_styles' );
			wp_register_script( 'ic-integration', AL_PLUGIN_BASE_PATH . 'js/integration-script.min.js' . ic_filemtime( AL_BASE_PATH . '/js/integration-script.min.js' ), array(
				'al_product_scripts',
				'jquery-ui-tooltip'
			) );
			wp_register_style( 'ic_chosen', AL_PLUGIN_BASE_PATH . 'js/chosen/chosen.css' . ic_filemtime( AL_BASE_PATH . '/js/chosen/chosen.css' ) );
			wp_register_style( 'al_product_admin_styles', AL_PLUGIN_BASE_PATH . 'css/al_product-admin.min.css' . ic_filemtime( AL_BASE_PATH . '/css/al_product-admin.min.css' ), array(
				'wp-color-picker',
				'ic_chosen',
				'editor-buttons'
			) );
			wp_register_style( 'ic_range_slider', AL_PLUGIN_BASE_PATH . 'js/range-slider/css/ion.rangeSlider.css' . ic_filemtime( AL_BASE_PATH . '/js/range-slider/css/ion.rangeSlider.css' ) );
			wp_register_script( 'ic_ion_range_slider', AL_PLUGIN_BASE_PATH . 'js/range-slider/ion.rangeSlider.min.js' . ic_filemtime( AL_BASE_PATH . '/js/range-slider/ion.rangeSlider.min.js' ), array( 'jquery' ) );
			wp_register_script( 'ic_range_slider', AL_PLUGIN_BASE_PATH . 'js/range-slider.min.js' . ic_filemtime( AL_BASE_PATH . '/js/range-slider.min.js' ), array( 'ic_ion_range_slider' ) );
		}

		/**
		 * Adds catalog admin styles and scripts
		 *
		 */
		public function implecode_run_admin_styles() {
			wp_enqueue_style( 'dashicons' );
			wp_enqueue_style( 'al_product_styles' );
			wp_enqueue_style( 'al_product_admin_styles' );
			do_action( 'enqueue_catalog_admin_styles' );
			if ( function_exists( 'get_current_screen' ) ) {
				$current_screen = get_current_screen();
			}
			if ( is_ic_admin_page() || ( ! empty( $current_screen->id ) && $current_screen->id === "widgets" ) ) {
				wp_enqueue_script( 'al_product_admin-scripts' );
				wp_localize_script( 'al_product_admin-scripts', 'ic_catalog', apply_filters( 'ic_catalog_admin_scrits_localize', array(
					'import_screen_url' => current_user_can( 'manage_product_settings' ) ? admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=csv' ) : '',
					'import_export'     => __( 'Import / Export', 'ecommerce-product-catalog' ),
					'nonce'             => wp_create_nonce( 'ic-ajax-nonce' )
				) ) );
				do_action( 'enqueue_catalog_admin_scripts' );
			}
		}

		/**
		 * Registers catalog admin styles and scripts
		 */
		public function implecode_register_admin_styles() {
			wp_register_style( 'ic_chosen', AL_PLUGIN_BASE_PATH . 'js/chosen/chosen.css' . ic_filemtime( AL_BASE_PATH . '/js/chosen/chosen.css' ) );
			wp_register_style( 'al_product_admin_styles', AL_PLUGIN_BASE_PATH . 'css/al_product-admin.min.css' . ic_filemtime( AL_BASE_PATH . '/css/al_product-admin.min.css' ), array(
				'wp-color-picker',
				'ic_chosen'
			) );
			wp_register_script( 'jquery-validate', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/jquery.validate.min.js', array( 'jquery' ) );
			wp_register_script( 'jquery-validate-add', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.5/additional-methods.min.js', array( 'jquery-validate' ) );
			//wp_register_script( 'jquery-validate', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/jquery.validate.min.js', array( 'jquery' ) );
			//wp_register_script( 'jquery-validate-add', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.2/additional-methods.min.js', array( 'jquery-validate' ) );
			wp_register_script( 'ic_chosen', AL_PLUGIN_BASE_PATH . 'js/chosen/chosen.jquery.js' . ic_filemtime( AL_BASE_PATH . '/js/chosen/chosen.jquery.js' ), array( 'jquery' ) );
			wp_register_script( 'al_product_admin-scripts', AL_PLUGIN_BASE_PATH . 'js/admin-scripts.min.js' . ic_filemtime( AL_BASE_PATH . '/js/admin-scripts.min.js' ), array(
				'jquery-ui-sortable',
				'jquery-ui-tooltip',
				'jquery-validate-add',
				'wp-color-picker',
				'jquery-ui-autocomplete',
				'ic_chosen'
			) );
			do_action( 'register_catalog_admin_styles' );
		}

		/**
		 * Adds catalog front-end styles and scripts
		 *
		 */
		public function implecode_enqueue_styles() {
			if ( ! did_action( 'ic_catalog_localize_scripts' ) ) {
				do_action( 'ic_catalog_localize_scripts' );
			}
			if ( function_exists( 'ic_maybe_engueue_all' ) && ic_maybe_engueue_all() ) {
				if ( function_exists( 'ic_enqueue_main_catalog_js_css' ) ) {
					ic_enqueue_main_catalog_js_css();
				}

				if ( is_ic_integration_wizard_page() ) {
					wp_enqueue_style( 'al_product_admin_styles' );
					wp_enqueue_script( 'ic-integration' );
				}
				do_action( 'enqueue_catalog_scripts' );
			}
		}

		/**
		 * Loads Plugin textdomain
		 */
		public function load_textdomain() {
			if ( ! defined( 'IC_EPC_TEXTDOMAIN_PATH' ) ) {
				define( 'IC_EPC_TEXTDOMAIN_PATH', dirname( plugin_basename( __FILE__ ) ) . '/lang' );
			}
			load_plugin_textdomain( 'ecommerce-product-catalog', false, IC_EPC_TEXTDOMAIN_PATH );
		}

		/**
		 * Adds support for impleCode Addons
		 */
		public function implecode_addons() {
			if ( ! is_network_admin() ) {
				do_action( 'ecommerce-prodct-catalog-addons' );
				do_action( 'ecommerce-product-catalog-addons-v3' );
				do_action( 'implecode_addons' );
			}
		}

	}

} // End if class_exists check

if ( ! function_exists( 'impleCode_EPC' ) ) {

	add_action( 'plugins_loaded', 'impleCode_EPC', - 2 );

	/**
	 * The main function responsible for returning eCommerce_Product_Catalog
	 *
	 * @return type
	 * @since 2.4.7
	 */
	function impleCode_EPC() {
		global $ic_epc_instance;
		if ( empty( $ic_epc_instance ) ) {
			$ic_epc_instance = eCommerce_Product_Catalog::instance();
		}

		return $ic_epc_instance;
	}

}


if ( ! function_exists( 'IC_EPC_install' ) ) {

	register_activation_hook( __FILE__, 'IC_EPC_install' );

	function IC_EPC_install() {
		update_option( 'IC_EPC_install', 1, false );
		eCommerce_Product_Catalog::instance();
		eCommerce_Product_Catalog::content();
		epc_activation_function();
	}

}
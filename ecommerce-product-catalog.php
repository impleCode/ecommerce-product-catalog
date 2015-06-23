<?php

/**
 * Plugin Name: eCommerce Product Catalog by impleCode
 * Plugin URI: http://implecode.com/wordpress/product-catalog/#cam=in-plugin-urls&key=plugin-url
 * Description: WordPress eCommerce easy to use, powerful and beautiful plugin from impleCode. Great choice if you want to sell easy and quick. Or just beautifully present your products on WordPress website. Full WordPress integration does great job not only for Merchants but also for Developers and Theme Constructors.
 * Version: 2.3.3
 * Author: impleCode
 * Author URI: http://implecode.com/#cam=in-plugin-urls&key=author-url
 * Text Domain: al-ecommerce-product-catalog
 * Domain Path: /lang/

  Copyright: 2015 impleCode.
  License: GNU General Public License v3.0
  License URI: http://www.gnu.org/licenses/gpl-3.0.html */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

define( 'AL_BASE_PATH', dirname( __FILE__ ) );
define( 'AL_PLUGIN_BASE_PATH', plugins_url( '/', __FILE__ ) );
define( 'AL_PLUGIN_MAIN_FILE', __FILE__ );

require_once(AL_BASE_PATH . '/functions/index.php' );
require_once(AL_BASE_PATH . '/includes/index.php' );
require_once(AL_BASE_PATH . '/includes/product.php' );
require_once(AL_BASE_PATH . '/includes/product-settings.php' );
require_once(AL_BASE_PATH . '/includes/settings-defaults.php' );
require_once(AL_BASE_PATH . '/functions/base.php' );
require_once(AL_BASE_PATH . '/functions/capabilities.php' );
require_once(AL_BASE_PATH . '/functions/functions.php' );
require_once(AL_BASE_PATH . '/templates.php' );
require_once(AL_BASE_PATH . '/theme-product_adder_support.php' );
require_once(AL_BASE_PATH . '/config/const.php' );
require_once(AL_BASE_PATH . '/functions/shortcodes.php' );
require_once(AL_BASE_PATH . '/functions/activation.php' );
require_once(AL_BASE_PATH . '/ext-comp/index.php' );

register_activation_hook( __FILE__, 'add_product_caps' );
register_activation_hook( __FILE__, 'epc_activation_function' );
add_action( 'wp_enqueue_scripts', 'implecode_enqueue_styles' );

/**
 * Adds catalog front-end styles and scripts
 *
 */
function implecode_enqueue_styles() {
	wp_enqueue_style( 'al_product_styles' );
	$colorbox_set = json_decode( apply_filters( 'colorbox_set', '{"transition": "elastic", "initialWidth": 200, "maxWidth": "90%", "maxHeight": "90%", "rel":"gal"}' ) );
	wp_localize_script( 'al_product_scripts', 'product_object', array( 'lightbox_settings' => $colorbox_set ) );
	wp_enqueue_script( 'al_product_scripts' );
	do_action( 'enqueue_catalog_scripts' );
}

add_action( 'admin_init', 'implecode_register_admin_styles' );

/**
 * Registers catalog admin styles and scripts
 */
function implecode_register_admin_styles() {
	wp_register_style( 'al_product_admin_styles', plugins_url() . '/' . dirname( plugin_basename( __FILE__ ) ) . '/css/al_product-admin.css?' . filemtime( plugin_dir_path( __FILE__ ) . '/css/al_product-admin.css' ) );
	wp_register_script( 'jquery-validate', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.13.1/jquery.validate.min.js', array( 'jquery' ) );
	wp_register_script( 'jquery-validate-add', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.13.1/additional-methods.min.js', array( 'jquery-validate' ) );
	wp_register_script( 'admin-scripts', AL_PLUGIN_BASE_PATH . 'js/admin-scripts.js?' . filemtime( AL_BASE_PATH . '/js/admin-scripts.js' ), array( 'jquery-ui-sortable', 'jquery-ui-tooltip', 'jquery-validate-add' ) );
	do_action( 'register_catalog_admin_styles' );
}

add_action( 'init', 'implecode_register_styles' );

/**
 * Registers catalog styles and scripts
 */
function implecode_register_styles() {
	wp_register_style( 'al_product_styles', plugins_url() . '/' . dirname( plugin_basename( __FILE__ ) ) . '/css/al_product.css?' . filemtime( plugin_dir_path( __FILE__ ) . '/css/al_product.css' ) );
	do_action( 'register_catalog_styles' );
}

add_action( 'admin_enqueue_scripts', 'implecode_run_admin_styles' );

/**
 * Adds catalog admin styles and scripts
 *
 */
function implecode_run_admin_styles() {
	wp_enqueue_style( 'al_product_styles' );
	wp_enqueue_style( 'al_product_admin_styles' );
	do_action( 'enqueue_catalog_admin_styles' );
	if ( is_ic_admin_page() ) {
		wp_enqueue_script( 'admin-scripts' );
		do_action( 'enqueue_catalog_admin_scripts' );
	}
}

add_action( 'plugins_loaded', 'implecode_addons' );

/**
 * Executes all installed catalog extensions
 */
function implecode_addons() {
	load_plugin_textdomain( 'al-ecommerce-product-catalog', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	if ( !is_network_admin() ) {
		do_action( 'ecommerce-prodct-catalog-addons' );
	}
}

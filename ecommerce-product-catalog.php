<?php
/**
 * Plugin Name: eCommerce Product Catalog by impleCode
 * Plugin URI: http://implecode.com/#cam=in-plugin-urls&key=plugin-url
 * Description: WordPress eCommerce easy to use, powerful and beautiful plugin from impleCode. Great choice if you want to sell easy and quick. Or just beautifully present your products on WordPress website. Full WordPress integration does great job not only for Merchants but also for Developers and Theme Constructors.
 * Version: 2.0.3
 * Author: impleCode
 * Author URI: http://implecode.com/#cam=in-plugin-urls&key=author-url
	
	Copyright: 2014 impleCode.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html */
	
	 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

load_plugin_textdomain( 'al-ecommerce-product-catalog', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

define('AL_BASE_PATH',dirname(__FILE__));
define('AL_PLUGIN_BASE_PATH',plugins_url( '/', __FILE__ ));
define('AL_PLUGIN_MAIN_FILE', __FILE__ );

require_once(AL_BASE_PATH .  '/functions/index.php' );
require_once(AL_BASE_PATH .  '/includes/index.php' );
require_once(AL_BASE_PATH .  '/includes/product.php' );
require_once(AL_BASE_PATH .  '/includes/product-settings.php' );
require_once(AL_BASE_PATH .  '/includes/settings-defaults.php' );
require_once(AL_BASE_PATH .  '/functions/base.php' );
require_once(AL_BASE_PATH .  '/functions/capabilities.php' );
require_once(AL_BASE_PATH .  '/functions/functions.php' );
require_once(AL_BASE_PATH .  '/templates.php' );
require_once(AL_BASE_PATH .  '/theme-product_adder_support.php' );
require_once(AL_BASE_PATH .  '/config/const.php' );
require_once(AL_BASE_PATH .  '/functions/shortcodes.php' );
require_once(AL_BASE_PATH .  '/functions/activation.php' );
require_once(AL_BASE_PATH .  '/ext-comp/index.php' );

register_activation_hook( __FILE__, 'add_product_caps' );
register_activation_hook( __FILE__, 'epc_activation_function' );

function implecode_register_styles() {
 wp_register_style( 'al_product_styles',  plugins_url(). '/' . dirname( plugin_basename( __FILE__ ) ) . '/css/al_product.css?' . filemtime( plugin_dir_path(__FILE__) . '/css/al_product.css') );
 wp_enqueue_style( 'al_product_styles' ); 
}

add_action('wp_enqueue_scripts', 'implecode_register_styles');
add_action('admin_enqueue_scripts', 'implecode_register_styles');

function implecode_register_admin_styles() {
 wp_register_style( 'al_product_admin_styles',  plugins_url(). '/' . dirname( plugin_basename( __FILE__ ) ) . '/css/al_product-admin.css?' . filemtime( plugin_dir_path(__FILE__) . '/css/al_product-admin.css') );
}

add_action('admin_init', 'implecode_register_admin_styles');

function implecode_run_admin_styles() {
wp_enqueue_style( 'al_product_admin_styles' ); 
}
add_action('admin_enqueue_scripts', 'implecode_run_admin_styles');

function implecode_addons() {
do_action('ecommerce-prodct-catalog-addons'); }

 add_action( 'plugins_loaded', 'implecode_addons' );

?>
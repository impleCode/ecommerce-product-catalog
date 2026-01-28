<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages quote cart includes folder
 *
 * Here includes folder files defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-quote-cart/includes
 * @author        Norbert Dreszer
 */
require_once( dirname( __FILE__ ) . '/cart-conditionals.php' );
require_once( dirname( __FILE__ ) . '/functions.php' );
require_once( dirname( __FILE__ ) . '/ic_cached_cart.php' );
require_once( dirname( __FILE__ ) . '/ic-cart.php' );
require_once( dirname( __FILE__ ) . '/cart-info.php' );
require_once( dirname( __FILE__ ) . '/ic-cart-ajax.php' );
if ( function_exists( 'is_ic_shipping_enabled' ) ) {
	require_once( dirname( __FILE__ ) . '/cart-shipping.php' );
}



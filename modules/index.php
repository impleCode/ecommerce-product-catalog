<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product includes folder
 *
 * Here all plugin includes folder is defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		impleCode
 */
require_once(AL_BASE_PATH . '/modules/price/index.php');
require_once(AL_BASE_PATH . '/modules/attributes/index.php');
require_once(AL_BASE_PATH . '/modules/shipping/index.php');
require_once(AL_BASE_PATH . '/modules/sku/index.php');
require_once(AL_BASE_PATH . '/modules/mpn/index.php');
$mode = ic_get_catalog_mode();
if ( ($mode === 'store' || $mode === 'inquiry') && !function_exists( 'start_quote_cart' ) && !class_exists( 'ic_shopping_ecommerce_cart' ) ) {
	require_once ( AL_BASE_PATH . '/modules/cart/index.php' );
	if ( $mode === 'inquiry' ) {
		require_once ( AL_BASE_PATH . '/modules/quote-cart/index.php' );
	}
} elseif ( $mode === 'affiliate' ) {
	require_once(AL_BASE_PATH . '/modules/button/index.php');
}



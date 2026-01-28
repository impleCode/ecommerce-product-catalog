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
$dirname = dirname( __FILE__ );
require_once($dirname . '/sku-functions.php');
require_once($dirname . '/sku-meta.php');
require_once($dirname . '/sku-settings.php');
require_once($dirname . '/sku-shortcodes.php');


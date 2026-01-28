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
require_once($dirname . '/attributes-conditionals.php');

require_once($dirname . '/attributes-settings.php');

require_once($dirname . '/attributes-meta.php');

require_once($dirname . '/attributes-functions.php');
require_once($dirname . '/product-attributes.php');
require_once($dirname . '/attributes-shortcodes.php');
require_once($dirname . '/attribute-filters.php');
require_once($dirname . '/comparison.php');
require_once($dirname . '/ext/index.php');



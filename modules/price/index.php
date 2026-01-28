<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product includes folder
 *
 * Here all plugin includes folder is defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
$dirname = dirname( __FILE__ );
require_once( $dirname . '/compatibility.php' );
require_once( $dirname . '/price-conditionals.php' );
require_once( $dirname . '/price-filters.php' );
require_once( $dirname . '/price-functions.php' );
require_once( $dirname . '/price-meta.php' );
require_once( $dirname . '/price-settings.php' );
require_once( $dirname . '/price-shortcodes.php' );
require_once( $dirname . '/config/currencies.php' );
require_once( $dirname . '/widgets/index.php' );
require_once( $dirname . '/blocks/index.php' );



<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages ext files
 *
 * Here all ext files are defined and managed.
 *
 * @version		1.0.0
 * @package		implecode-digital-customers/ext
 * @author 		Norbert Dreszer
 */

if ( function_exists( 'start_shopping_cart' ) ) {
	require_once(AL_CUSTOMERS_BASE_PATH . '/ext/transactions/shopping-cart.php');
}
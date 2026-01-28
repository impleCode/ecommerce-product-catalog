<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages ext compatibility files
 *
 * Here all compatibility files are defined and managed.
 *
 * @version		1.0.0
 * @package		implecode-update-server/ext
 * @author 		Norbert Dreszer
 */
if ( defined( 'IC_PD_PLUGIN_BASE_URL' ) ) {
	require_once(AL_PO_BASE_PATH . '/ext/product-discounts.php');
}
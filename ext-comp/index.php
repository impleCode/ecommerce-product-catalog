<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages externa compatibility functions folder
 *
 *
 * @version		1.0.0
 * @package		digital-products-order/functions
 * @author 		Norbert Dreszer
 */
require_once(AL_BASE_PATH . '/ext-comp/wpseo.php');
if ( function_exists( 'pll_get_post' ) || function_exists( 'icl_object_id' ) ) {
	require_once(AL_BASE_PATH . '/ext-comp/multilingual.php');
}

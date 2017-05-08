<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product functions folder
 *
 * Here all plugin functions folder is defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        Norbert Dreszer
 */
require_once(AL_BASE_PATH . '/functions/content-functions.php');

require_once(AL_BASE_PATH . '/functions/support.php');
require_once(AL_BASE_PATH . '/functions/conditionals.php');
require_once(AL_BASE_PATH . '/functions/compatibility.php');
require_once(AL_BASE_PATH . '/functions/globals.php');
require_once(AL_BASE_PATH . '/functions/rewrite.php');

function start_admin_only_functions() {
	if ( !is_admin() && is_user_logged_in() ) {
		require_once(AL_BASE_PATH . '/functions/catalog-admin.php');
	} else if ( is_admin() ) {
		require_once(AL_BASE_PATH . '/functions/duplicate.php');
	}
}

add_action( 'ic_epc_loaded', 'start_admin_only_functions' );

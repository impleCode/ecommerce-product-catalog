<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages includes files
 *
 * Here all includes files are defined and managed.
 *
 * @version		1.0.0
 * @package		implecode-digital-customers/includes
 * @author 		Norbert Dreszer
 */
//require_once(AL_CUSTOMERS_BASE_PATH . '/includes/digital-client-list.php');
require_once(AL_CUSTOMERS_BASE_PATH . '/includes/digital-customer.php');
require_once(AL_CUSTOMERS_BASE_PATH . '/includes/login-url-widget.php');
require_once(AL_CUSTOMERS_BASE_PATH . '/includes/login-form-widget.php');

function digital_customers_register_widgets() {
	register_widget( 'ic_digital_customers_popup_login' );
	register_widget( 'ic_digital_customers_login_form' );
}

add_action( 'widgets_init', 'digital_customers_register_widgets' );

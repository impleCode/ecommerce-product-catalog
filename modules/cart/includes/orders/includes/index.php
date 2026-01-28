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
 * @package		digital-products-order/includes
 * @author 		Norbert Dreszer
 */
require_once(AL_PO_BASE_PATH . '/includes/verify-payment.php');
require_once(AL_PO_BASE_PATH . '/includes/register-digital-orders.php');
require_once(AL_PO_BASE_PATH . '/includes/digital-order-columns.php');
require_once(AL_PO_BASE_PATH . '/includes/save-order.php');

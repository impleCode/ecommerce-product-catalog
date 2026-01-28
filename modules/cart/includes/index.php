<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages shopping cart includes folder
 *
 * Here includes folder files defined and managed.
 *
 * @version		1.0.0
 * @package		implecode-shopping-cart/includes
 * @author 		Norbert Dreszer
 */
require_once(AL_SC_BASE_PATH . '/includes/product-status.php');
require_once(AL_SC_BASE_PATH . '/includes/cart-widget.php');
require_once(AL_SC_BASE_PATH . '/includes/shopping-cart.php');
require_once(AL_SC_BASE_PATH . '/includes/cart-form.php');
require_once(AL_SC_BASE_PATH . '/includes/shopping-settings.php');
require_once(AL_SC_BASE_PATH . '/includes/cart-customer.php');
/*
  if ( !function_exists( 'product_variations_menu' ) ) {
  require_once(AL_SC_BASE_PATH . '/includes/product-variations-settings.php');
  }
  if ( !function_exists( 'add_variation_metaboxes' ) ) {
  require_once(AL_SC_BASE_PATH . '/includes/product-variations.php');
  }
 *
 */

if ( !function_exists( 'is_ic_json_cart' ) ) {
	require_once(AL_SC_BASE_PATH . '/includes/cart/index.php');
}
require_once(AL_SC_BASE_PATH . '/includes/variations/index.php');
require_once(AL_SC_BASE_PATH . '/includes/payment.php');
if ( !function_exists( 'modify_tax_cart_rate' ) ) {
	require_once(AL_SC_BASE_PATH . '/includes/tax.php');
}

$settings = get_shopping_cart_settings();

if ( $settings[ 'cart_page_template' ] == 'no_qty' ) {
	require_once(AL_SC_BASE_PATH . '/includes/no-qty-cart.php');
}

if ( empty( $settings[ 'order_registration_disable' ] ) ) {
	require_once(AL_SC_BASE_PATH . '/includes/orders/index.php');
}

if ( $settings[ 'form_registration' ] == 'user' ) {
	require_once(AL_SC_BASE_PATH . '/includes/customers/index.php');
}





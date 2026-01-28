<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages plugin activation functions
 *
 * Here all plugin activation functions are defined and managed.
 *
 * @version		1.0.0
 * @package		implecode-shopping-cart/functions
 * @author 		Norbert Dreszer
 */
add_action( 'admin_init', 'ic_cart_add_customer_role' );

function ic_cart_add_customer_role() {
	$role = get_role( 'customer' );
	if ( empty( $role ) ) {
		add_role( 'customer', __( 'Customer', 'ecommerce-product-catalog' ) );
	}
}

<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product discounts compatibility
 *
 * Here compatibility is defined and managed.
 *
 * @version		1.0.0
 * @package		digital-products-order/includes
 * @author 		Norbert Dreszer
 */
add_filter( 'expected_order_amout', 'ic_discount_modify_expected_amount', 10, 3 );

function ic_discount_modify_expected_amount( $oryg_price, $payment_amount, $product_id ) {
	$disc_price = discounted_product_price( $product_id );
	if ( $disc_price ) {
		return $disc_price;
	} else {
		return $oryg_price;
	}
}

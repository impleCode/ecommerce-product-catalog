<?php

/**
 * Template Name:  Product Template
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates
 * @author 		impleCode
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header( 'shop' );

do_action( 'woo-adder-top' );

content_product_adder();

do_action( 'woo-adder-bottom' );

get_footer( 'shop' );

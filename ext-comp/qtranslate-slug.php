<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages compatibility functions with WordPress SEO plugin
 *
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/ext-comp
 * @author 		impleCode
 */
add_filter( 'product_query_var', 'ic_qtranslate_mod_queryvar' );

function ic_qtranslate_mod_queryvar() {
	return 'al_product';
}

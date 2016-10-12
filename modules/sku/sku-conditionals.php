<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */

/**
 * Checks if SKU is enabled
 * 
 * @return boolean
 */
function is_ic_sku_enabled() {
	$archive_multiple_settings = get_multiple_settings();
	if ( $archive_multiple_settings[ 'disable_sku' ] != 1 ) {
		return true;
	}
	return false;
}

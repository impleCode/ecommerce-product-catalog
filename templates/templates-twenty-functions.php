<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WP Product template functions
 *
 * Here all plugin template functions are defined.
 *
 * @version		1.1.3
 * @package		ecommerce-product-catalog/
 * @author 		impleCode
 */
class ic_catalog_twenty_themes {

	function __construct() {
		add_filter( "theme_mod_page_layout", array( $this, 'twentyseventeen_layout' ) );
		//add_filter( 'ic_catalog_default_listing_content', array( $this, 'default_listing_content' ) );
	}

	function twentyseventeen_layout( $value ) {
		if ( is_ic_catalog_page() && is_ic_shortcode_integration() ) {
			return 'one-column';
		}
		return $value;
	}

	function default_listing_content() {
		return ''; //Disabled default shortcode integration
	}

}

$ic_catalog_twenty_themes = new ic_catalog_twenty_themes;

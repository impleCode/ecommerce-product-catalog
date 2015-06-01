<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product conditional functions
 *
 * Here all plugin conditional functions are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
function is_ic_catalog_page() {
	if ( is_ic_product_page() || is_ic_product_listing() || is_ic_taxonomy_page() ) {
		return true;
	}
	return false;
}

function is_ic_taxonomy_page() {
	if ( is_tax( product_taxonomy_array() ) ) {
		return true;
	}
	return false;
}

/**
 * Checks if current page is main product listing
 *
 * @return boolean
 */
function is_ic_product_listing() {
	$listing_id = get_product_listing_id();
	if ( is_post_type_archive( product_post_type_array() ) || is_home_archive() || (is_ic_product_listing_enabled() && is_page( $listing_id )) ) {
		return true;
	}
	return false;
}

function is_ic_product_page() {
	if ( is_singular( product_post_type_array() ) ) {
		return true;
	}
	return false;
}

function is_ic_admin_page() {
	if ( is_ic_catalog_admin_page() || isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'implecode-settings' ) {
		return true;
	}
	return false;
}

function is_ic_catalog_admin_page() {
	$screen = get_current_screen();
	if ( ic_string_contains( $screen->id, 'al_product' ) ) {
		return true;
	}
	return false;
}

function is_ic_price_enabled() {
	$product_currency = get_currency_settings();
	if ( $product_currency[ 'price_enable' ] == 'on' ) {
		return true;
	}
	return false;
}

function is_ic_sku_enabled() {
	$archive_multiple_settings = get_multiple_settings();
	if ( $archive_multiple_settings[ 'disable_sku' ] != 1 ) {
		return true;
	}
	return false;
}

function is_ic_product_listing_enabled() {
	$enable_product_listing = get_option( 'enable_product_listing', 1 );
	if ( $enable_product_listing == 1 ) {
		return true;
	}
	return false;
}

function ic_string_contains( $string, $contains ) {
	if ( strpos( $string, $contains ) !== false ) {
		return true;
	}
	return false;
}

function is_ic_new_product_screen() {
	$screen = get_current_screen();
	if ( is_ic_catalog_admin_page() && $screen->action == 'add' ) {
		return true;
	}
	return false;
}

/**
 * Checks if product gallery should be enabled
 *
 * @return boolean
 */
function is_ic_product_gallery_enabled() {
	$single_options								 = get_option( 'multi_single_options', unserialize( MULTI_SINGLE_OPTIONS ) );
	$single_options[ 'enable_product_gallery' ]	 = isset( $single_options[ 'enable_product_gallery' ] ) ? $single_options[ 'enable_product_gallery' ] : '';
	if ( $single_options[ 'enable_product_gallery' ] == 1 ) {
		return true;
	}
	return false;
}

/**
 * Checks if current product category has children
 * @param object $category
 * @return boolean
 */
function has_category_children( $category = null ) {
	if ( !isset( $category ) ) {
		$taxonomy	 = get_query_var( 'taxonomy' );
		$category	 = get_term_by( 'slug', get_query_var( 'term' ), $taxonomy );
	} else {
		$taxonomy = $category->taxonomy;
	}
	$children = get_term_children( $category->term_id, $taxonomy );
	if ( sizeof( $children ) > 0 ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Checks if product has short description
 *
 * @param int $product_id
 * @return boolean
 */
function has_product_short_description( $product_id ) {
	$desc = get_product_short_description( $product_id );
	if ( !empty( $desc ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Checks if product has long description
 *
 * @param int $product_id
 * @return boolean
 */
function has_product_description( $product_id ) {
	$desc = get_product_description( $product_id );
	if ( !empty( $desc ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Ckecks if product has image attached
 *
 * @param int $product_id
 * @return boolean
 */
function has_product_image( $product_id ) {
	if ( has_post_thumbnail( $product_id ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Checks if current view is triggered by shortcode
 *
 * @global type $cat_shortcode_query
 * @global type $shortcode_query
 * @return boolean
 */
function is_ic_shortcode_query() {
	global $cat_shortcode_query, $shortcode_query;
	if ( (isset( $cat_shortcode_query ) && $cat_shortcode_query[ 'enable' ] == 'yes') || isset( $shortcode_query ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Checks if a plural catalog name should be used
 *
 * @return boolean
 */
function is_plural_form_active() {
	$lang = get_locale();
	if ( $lang != 'de_DE' && $lang != 'pl_PL' ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Checks if WordPress language is english
 *
 * @return boolean
 */
function is_english_catalog_active() {
	$lang = get_locale();
	if ( strpos( $lang, 'en_' ) !== false ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Checks if permalinks are enabled
 *
 * @return boolean
 */
function is_ic_permalink_product_catalog() {
	if ( get_option( 'permalink_structure' ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Checks if only categories should be showed
 * @return boolean
 */
function is_ic_only_main_cats() {
	$multiple_settings = get_multiple_settings();
	if ( is_ic_product_listing() && $multiple_settings[ 'product_listing_cats' ] == 'cats_only' ) {
		return true;
	} else if ( is_ic_taxonomy_page() && $multiple_settings[ 'category_top_cats' ] == 'only_subcategories' ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Checks if product listing is showign product categories
 * @return boolean
 */
function is_ic_product_listing_showing_cats() {
	$multiple_settings = get_multiple_settings();
	if ( $multiple_settings[ 'category_top_cats' ] == 'on' || $multiple_settings[ 'category_top_cats' ] == 'only_subcategories' ) {
		if ( !is_tax() || (is_tax() && has_category_children()) ) {
			return true;
		}
	}
	return false;
}

/**
 * Checks if category image is enabled on category page
 * @return boolean
 */
function is_ic_category_image_enabled() {
	$multiple_settings = get_multiple_settings();
	if ( $multiple_settings[ 'cat_image_disabled' ] != 1 ) {
		return true;
	}
	return false;
}

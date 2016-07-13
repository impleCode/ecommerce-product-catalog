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
	if ( is_ic_product_page() || is_ic_product_listing() || is_ic_taxonomy_page() || is_ic_product_search() ) {
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
 * Checks if current product has certain category assigned or any category if no category is provided
 *
 * @param string|integer|array $category
 * @return boolean
 */
function has_ic_product_category( $category = null ) {
	if ( has_term( $category, 'al_product-cat' ) ) {
		return true;
	}
	return false;
}

/**
 * Checks if product category page is being displayed
 *
 * @param string|integer|array $category
 * @return boolean
 */
function is_ic_product_category( $category = null ) {
	if ( is_tax( product_taxonomy_array(), $category ) ) {
		return true;
	}
	return false;
}

/**
 * Checks if current page is main product listing
 *
 * @return boolean
 */
function is_ic_product_listing( $query = null ) {
	if ( empty( $query ) ) {
		if ( (is_post_type_archive( product_post_type_array() ) && !is_search()) || is_home_archive() || is_custom_product_listing_page() ) {
			return true;
		}
	} else {
		if ( $query->is_post_type_archive( product_post_type_array() ) || is_home_archive( $query ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Checks if selected page for product listing is being displayed
 *
 * @return boolean
 */
function is_custom_product_listing_page() {
	$listing_id = get_product_listing_id();
	if ( !empty( $listing_id ) && is_ic_product_listing_enabled() && is_page( $listing_id ) ) {
		return true;
	}
	return false;
}

/**
 * Checks if product search screen is active
 *
 * @return boolean
 */
function is_ic_product_search() {
	if ( is_search() && isset( $_GET[ 'post_type' ] ) && strpos( $_GET[ 'post_type' ], 'al_product' ) !== false ) {
		return true;
	}
	return false;
}

/**
 * Checks if a product page is displayed
 *
 * @return boolean
 */
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
	if ( is_admin() && function_exists( 'get_current_screen' ) ) {
		$screen = get_current_screen();
		if ( isset( $screen->id ) && ic_string_contains( $screen->id, 'al_product' ) ) {
			return true;
		}
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
		//$category	 = get_term_by( 'slug', get_query_var( 'term' ), $taxonomy );
		$category	 = get_queried_object();
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
 * Checks if product has price set
 * @param type $product_id
 * @return boolean
 */
function has_product_price( $product_id = null ) {
	$product_id	 = empty( $product_id ) ? get_the_ID() : $product_id;
	$price		 = product_price( $product_id, 1 );
	if ( !empty( $price ) ) {
		return true;
	}
	return false;
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
	if ( strpos( $lang, 'en_' ) !== false ) {
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

/**
 * Checks if product shipping is enabled
 *
 * @return boolean
 */
function is_ic_shipping_enabled() {
	$shipping_count = get_shipping_options_number();
	if ( $shipping_count > 0 ) {
		return true;
	}
	return false;
}

/**
 * Checks if product attributes are enabled
 *
 * @return boolean
 */
function is_ic_attributes_enabled() {
	$attributes_count = product_attributes_number();
	if ( $attributes_count > 0 ) {
		return true;
	}
	return false;
}

/**
 * Checks if product has any attributes selected
 *
 * @param type $product_id
 * @return boolean
 */
function has_product_any_attributes( $product_id ) {
	$attributes_number = product_attributes_number();
	if ( $attributes_number > 0 ) {
		$attributes_number = product_attributes_number();
		for ( $i = 1; $i <= $attributes_number; $i++ ) {
			$at_val = get_post_meta( $product_id, "_attribute" . $i, true );
			if ( !empty( $at_val ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Checks if product name on product page is enabled
 *
 * @return boolean
 */
function is_ic_product_name_enabled() {
	$multiple_settings = get_multiple_settings();
	if ( $multiple_settings[ 'disable_name' ] == 1 ) {
		return false;
	}
	return true;
}

/**
 * Checks if theme default sidebar should be enabled on product pages
 *
 * @return boolean
 */
function is_ic_default_theme_sidebar_active() {
	$settings = get_multiple_settings();
	if ( isset( $settings[ 'default_sidebar' ] ) && $settings[ 'default_sidebar' ] == 1 ) {
		return true;
	}
	return false;
}

/**
 * Checks if theme default sidebar catalog styled should be enabled on product pages
 *
 * @return boolean
 */
function is_ic_default_theme_sided_sidebar_active() {
	$settings = get_multiple_settings();
	if ( isset( $settings[ 'default_sidebar' ] ) && ($settings[ 'default_sidebar' ] == 'left' || $settings[ 'default_sidebar' ] == 'right') ) {
		return true;
	}
	return false;
}

/**
 * Checks if current page is integration wizard page
 *
 * @return boolean
 */
function is_ic_integration_wizard_page() {
	if ( sample_product_id() == get_the_ID() && current_user_can( "manage_product_settings" ) && !is_advanced_mode_forced() ) {
		return true;
	}
	return false;
}

/**
 * Checks if current page is home catalog listing
 *
 * @param object $query
 * @return boolean
 */
function is_home_archive( $query = null ) {
	if ( !is_object( $query ) && is_front_page() && is_product_listing_home_set() ) {
		return true;
	} else if ( is_object( $query ) && $query->get( 'page_id' ) == get_option( 'page_on_front' ) && is_product_listing_home_set() ) {
		return true;
	}
	return false;
}

/**
 * Checks if the home page catalog listing configuration is active
 *
 * @return boolean
 */
function is_product_listing_home_set() {
	$frontpage			 = get_option( 'page_on_front' );
	$product_listing_id	 = get_product_listing_id();
	if ( !empty( $frontpage ) && !empty( $product_listing_id ) && $frontpage == $product_listing_id ) {
		return true;
	}
	return false;
}

/**
 * Checks if sort drop down should be shown
 *
 * @global int $product_sort
 * @global object $wp_query
 * @return boolean
 */
function is_product_sort_bar_active() {
	global $product_sort, $wp_query;
	if ( get_integration_type() != 'simple' && (is_product_filters_active() || (isset( $product_sort ) && $product_sort == 1) || (!is_ic_shortcode_query() && ($wp_query->max_num_pages > 1 || $wp_query->found_posts > 0))) ) {
		return true;
	}
	return false;
}

/**
 * Checks if any filter is active now
 *
 * @return boolean
 */
function is_product_filters_active() {
	$session = get_product_catalog_session();
	if ( isset( $session[ 'filters' ] ) && !empty( $session[ 'filters' ] ) ) {
		return true;
	}
	return false;
}

/**
 * Checks if product filter is active
 *
 * @param string $filter_name
 * @return boolean
 */
function is_product_filter_active( $filter_name, $value = null ) {
	$session = get_product_catalog_session();
	if ( isset( $session[ 'filters' ][ $filter_name ] ) && !empty( $session[ 'filters' ][ $filter_name ] ) ) {
		if ( isset( $value ) && $session[ 'filters' ][ $filter_name ] == $value ) {
			return true;
		} else if ( !isset( $value ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Checks if currently the filter bar is being displayed
 *
 * @global boolean $is_filter_bar
 * @return boolean
 */
function is_filter_bar() {
	global $is_filter_bar;
	if ( isset( $is_filter_bar ) && $is_filter_bar ) {
		return true;
	}
	return false;
}

/**
 * Checks if current page has show_products shortcode
 *
 * @global type $post
 * @return boolean
 */
function has_show_products_shortcode() {
	global $post;
	if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'show_products' ) ) {
		return true;
	}
	return false;
}

/**
 * Checks if product exists
 *
 * @param type $product_id
 * @return boolean
 */
function ic_product_exists( $product_id ) {
	if ( FALSE === get_post_status( $product_id ) ) {
		return false;
	}
	return true;
}

/**
 * Checks if default image should be displayed for product
 *
 * @param type $product_id
 * @return boolean
 */
function is_ic_default_image( $product_id ) {
	if ( has_post_thumbnail( $product_id ) ) {
		return false;
	} else {
		return true;
	}
}

/**
 * Check if PHP sessions are available
 *
 * @return boolean
 */
function ic_use_php_session() {
	$return = false;
	if ( function_exists( 'session_start' ) && !ini_get( 'safe_mode' ) ) {
		$return = true;
	}
	return apply_filters( 'ic_use_php_session', $return );
}

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
 * @author 		Norbert Dreszer
 */
function implecode_wpseo_compatible() {
	$post_type = get_quasi_post_type();
	if ( $post_type == 'al_product' ) {
		add_filter( 'wpseo_metabox_prio', 'implecode_wpseo_compatible_priority' );
	}
}

add_action( 'add_meta_boxes', 'implecode_wpseo_compatible' );

function implecode_wpseo_compatible_priority() {
	return 'low';
}

add_action( 'wp', 'remove_default_catalog_title', 100 );

/**
 * Allows to set product listing title tag from wpseo settings
 */
function remove_default_catalog_title() {
	remove_filter( 'wp_title', 'product_archive_title', 99, 3 );
}

add_action( 'add_meta_boxes', 'product_listing_remove_wpseo', 16 );

/**
 * Removes the WPSEO metabox from product listing edit screen
 * The title and description is managed from WPSEO settings
 */
function product_listing_remove_wpseo() {
	$id = get_product_listing_id();
	if ( is_admin() && isset( $_GET[ 'post' ] ) && $_GET[ 'post' ] == $id ) {
		remove_meta_box( 'wpseo_meta', 'page', 'normal' );
	}
}

/**
 * Removes yoast seo script to avoid javascript errors on product listing edit screen
 */
function product_listing_remove_wpseo_js() {
	$id = get_product_listing_id();
	if ( is_admin() && isset( $_GET[ 'post' ] ) && $_GET[ 'post' ] == $id ) {
		wp_deregister_script( 'yoast-seo' );
	}
}

add_action( 'admin_print_footer_scripts', 'product_listing_remove_wpseo_js', 1 );

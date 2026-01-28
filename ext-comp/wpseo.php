<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages compatibility functions with WordPress SEO plugin
 *
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/ext-comp
 * @author        impleCode
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
	if ( is_admin() && isset( $_GET['post'] ) && $_GET['post'] == $id && ! is_ic_shortcode_integration() ) {
		remove_meta_box( 'wpseo_meta', 'page', 'normal' );
	}
}

/**
 * Removes yoast seo script to avoid javascript errors on product listing edit screen
 */
function product_listing_remove_wpseo_js() {
	$id = get_product_listing_id();
	if ( is_admin() && isset( $_GET['post'] ) && $_GET['post'] == $id && ! is_ic_shortcode_integration() ) {
		wp_deregister_script( 'yoast-seo' );
	}
}

add_action( 'admin_print_footer_scripts', 'product_listing_remove_wpseo_js', 1 );

add_filter( 'wpseo_title', 'ic_remove_seo_archives', 20 );

/**
 * Removes unnecessary archives element from title
 *
 * @param type $title
 *
 * @return type
 */
function ic_remove_seo_archives( $title ) {
	if ( is_ic_admin() ) {
		return $title;
	}

	return str_replace( array(
		' ' . __( 'Archives', 'wordpress-seo' ),
		' ' . __( 'Archive', 'wordpress-seo' )
	), '', $title );
}

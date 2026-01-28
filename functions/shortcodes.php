<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages plugin shortcodes
 *
 * Here all shortcodes are defined.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        impleCode
 */
add_shortcode( 'display_product_categories', 'parent_cat_list' );

/**
 * Shows product category child urls
 *
 * @param type $atts
 *
 * @return type
 */
function parent_cat_list( $atts ) {
	$output = wp_list_categories( 'title_li=&orderby=name&depth=1&taxonomy=al_product-cat&echo=0' );

	return $output;
}

add_shortcode( 'show_categories', 'product_cat_shortcode' );

/**
 * Defines [show_categories] shortcode
 *
 * @param type $atts
 *
 * @return string
 * @global type $cat_shortcode_query
 */
function product_cat_shortcode( $atts ) {
	global $cat_shortcode_query, $product_sort;
	ic_enqueue_main_catalog_js_css();
	$cat_shortcode_query            = array();
	$cat_shortcode_query['current'] = 0;
	$available_args                 = apply_filters( 'show_categories_shortcode_args', array(
		'exclude'          => array(),
		'include'          => array(),
		'archive_template' => get_product_listing_template(),
		'parent'           => '',
		'sort'             => 0,
		'shortcode_query'  => 'yes',
		'orderby'          => 'name',
		'order'            => 'ASC',
		'per_row'          => get_current_category_per_row()
	), $atts );
	$args                           = apply_filters( 'show_categories_args', shortcode_atts( $available_args, $atts ) );
	if ( $args['orderby'] == 'none' ) {
		$args['orderby'] = 'include';
	}
	if ( ! is_array( $args['include'] ) ) {
		$args['include'] = explode( ',', $args['include'] );
	}
	if ( ! empty( $args['include'] ) && empty( $atts['orderby'] ) ) {
		$args['orderby'] = 'include';
	}
	//$div		 = '<div class="product-subcategories responsive ' . $args[ 'archive_template' ] . ' ' . product_list_class( $args[ 'archive_template' ], 'category-list' ) . '">';
	$taxonomy                      = apply_filters( 'show_categories_taxonomy', 'al_product-cat', $args );
	$args['taxonomy']              = $taxonomy;
	$cat_shortcode_query['enable'] = $args['shortcode_query'];
	$product_sort                  = intval( $args['sort'] );
	$inside                        = '';
	$per_row                       = intval( $args['per_row'] );
	if ( ! empty( $per_row ) ) {
		ic_save_global( 'shortcode_per_row', $per_row, true );
	}
	$cache_order   = $args['order'] === 'ASC' ? '' : $args['order'];
	$cache_orderby = $args['orderby'] === 'name' ? '' : $args['orderby'];
	if ( $args['parent'] == '' && empty( $args['include'] ) ) {

		//$old_args			 = $args;
		$args['parent'] = '0';
		//$cats				 = get_terms( $args );
		$cats = apply_filters( 'ic_categories_ready_to_show', ic_catalog_get_categories( $args['parent'], $args['taxonomy'], '', $args['include'], $args['exclude'], $args['order'], $args['orderby'], $args ), $args );
		if ( ! is_wp_error( $cats ) ) {
			$cat_shortcode_query['count'] = count( $cats );
			foreach ( $cats as $cat ) {
				$inside .= get_product_category_template( $args['archive_template'], $cat );
				$cat_shortcode_query['current'] ++;
				$inside .= get_sub_product_subcategories( $args, $cat );
			}
		}
	} else {
		//$cats	 = get_terms( $args );
		$cats = apply_filters( 'ic_categories_ready_to_show', ic_catalog_get_categories( $args['parent'], $args['taxonomy'], '', $args['include'], $args['exclude'], $args['order'], $args['orderby'], $args ), $args );
		if ( ! is_wp_error( $cats ) ) {
			$cat_shortcode_query['count'] = count( $cats );
			foreach ( $cats as $cat ) {
				$inside .= get_product_category_template( $args['archive_template'], $cat );
				$cat_shortcode_query['current'] ++;
			}
		}
	}

	if ( ! empty( $inside ) ) {
		$ready = apply_filters( 'category_list_ready', $inside, $args['archive_template'] );
		ic_save_global( 'current_product_categories', $ready );
		ic_save_global( 'current_product_archive_template', $args['archive_template'] );
		ob_start();
		do_action( 'before_category_list', $args['archive_template'] );
		$inside = ob_get_clean();
		ob_start();
		ic_show_template_file( 'product-listing/categories-listing.php' );
		$inside .= ob_get_clean();
		ic_delete_global( 'current_product_categories' );
		ic_delete_global( 'current_product_archive_template' );
	}
	unset( $GLOBALS['cat_shortcode_query'] );
	unset( $GLOBALS['product_sort'] );
	ic_delete_global( 'shortcode_per_row' );
	reset_row_class();

	return $inside;
}

add_shortcode( 'product_category_name', 'product_category_name' );

/**
 * Returns current product category name
 */
function product_category_name() {
	//$the_tax = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
	$the_tax = ic_get_queried_object();
	$name    = '';
	if ( is_ic_taxonomy_page() && isset( $the_tax->name ) ) {
		$name = $the_tax->name;
	}

	return $name;
}

function get_sub_product_subcategories( $args, $parent_cat, $nest = true ) {
	global $cat_shortcode_query;
	$args['parent']   = $parent_cat->term_id;
	$args['taxonomy'] = 'al_product-cat';
	$cats             = ic_get_terms( $args );
	$return           = '';
	if ( ! empty( $cat_shortcode_query['count'] ) ) {
		$cat_shortcode_query['count'] += count( $cats );
	}
	foreach ( $cats as $cat ) {
		$return .= get_product_category_template( $args['archive_template'], $cat );
		$cat_shortcode_query['current'] ++;
		if ( $nest ) {
			$return .= get_sub_product_subcategories( $args, $cat );
		}
	}

	return $return;
}

add_shortcode( 'product_name', 'ic_product_name' );

/**
 * Shows product name
 *
 * @param type $atts
 *
 * @return string
 */
function ic_product_name( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );

	return get_product_name( $args['product'] );
}

add_shortcode( 'product_description', 'ic_product_description' );

/**
 * Shows product description
 *
 * @param type $atts
 *
 * @return string
 */
function ic_product_description( $atts ) {
	$args                = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	$product_description = get_product_description( $args['product'] );
	$add_filter          = false;
	if ( has_filter( 'the_content', array( 'ic_catalog_template', "product_page_content" ) ) ) {
		remove_filter( 'the_content', array( 'ic_catalog_template', "product_page_content" ) );
		$add_filter = true;
	}

	$content = apply_filters( 'the_content', $product_description );
	if ( $add_filter ) {
		add_filter( 'the_content', array( 'ic_catalog_template', "product_page_content" ) );
	}

	return $content;
}

add_shortcode( 'product_short_description', 'ic_product_short_description' );

/**
 * Shows product short description
 *
 * @param type $atts
 *
 * @return string
 */
function ic_product_short_description( $atts ) {
	$args      = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	$shortdesc = get_product_short_description( $args['product'] );

	return apply_filters( 'product_short_description', $shortdesc );
}

add_shortcode( 'product_gallery', 'ic_product_gallery' );

/**
 * Shows product gallery
 *
 * @param type $atts
 *
 * @return string
 */
function ic_product_gallery( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );

	return get_product_gallery( $args['product'] );
}

add_shortcode( 'product_related_categories', 'ic_product_related_categories' );

/**
 * Shows product related categories
 *
 * @param type $atts
 *
 * @return string
 */
function ic_product_related_categories( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );

	return get_related_categories( $args['product'] );
}

add_shortcode( 'related_products', 'ic_related_products' );

/**
 * Shows related products
 *
 * @param type $atts
 *
 * @return string
 */
function ic_related_products( $atts ) {
	$args = shortcode_atts( array(
		'limit' => 3,
	), $atts );

	return get_related_products( $args['limit'] );
}

add_shortcode( 'back_to_products_url', 'ic_back_to_prodcts_url' );

/**
 * Shows back to products URL
 *
 * @param type $atts
 *
 * @return string
 */
function ic_back_to_prodcts_url( $atts ) {
	return get_back_to_products_url();
}

add_shortcode( 'product_breadcrumbs', 'ic_product_breadcrumbs' );

/**
 * Shows product breadcrumbs
 *
 * @return string
 */
function ic_product_breadcrumbs() {
	return product_breadcrumbs();
}

add_shortcode( 'product_listing_products', 'ic_product_listing_products_shortcode' );

/**
 * Shows products on product listing for custom templates usage
 *
 * @return type
 */
function ic_product_listing_products_shortcode() {
	ob_start();
	$multiple_settings = get_multiple_settings();
	$archive_template  = get_product_listing_template();
	ic_product_listing_products( $archive_template, $multiple_settings );

	return ob_get_clean();
}

add_shortcode( 'product_listing_categories', 'ic_product_listing_categories_shortcode' );

/**
 * Shows categories on product listing for custom templates usage
 *
 * @return string
 */
function ic_product_listing_categories_shortcode() {
	ob_start();
	$multiple_settings = get_multiple_settings();
	$archive_template  = get_product_listing_template();
	ic_product_listing_categories( $archive_template, $multiple_settings );

	return ob_get_clean();
}

add_shortcode( 'product_page_class', 'ic_product_pages_class' );

/**
 * Shows product listing or product page class for templates usage
 *
 * @param type $atts
 *
 * @return string
 */
function ic_product_pages_class( $atts ) {
	$args          = shortcode_atts( array(
		'custom' => '',
	), $atts );
	$listing_class = apply_filters( 'product_listing_classes', 'al_product responsive' );
	if ( ! empty( $args['custom'] ) ) {
		$listing_class .= ' ' . $args['custom'];
	}
	ob_start();
	post_class( $listing_class );

	return ob_get_clean();
}

add_shortcode( 'product_page_id', 'ic_current_page_id' );

/**
 * Shows current page ID for template usage
 *
 * @return string
 */
function ic_current_page_id() {
	return get_the_ID();
}

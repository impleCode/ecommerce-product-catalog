<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages plugin shortcodes
 *
 * Here all shortcodes are defined.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        Norbert Dreszer
 */
add_shortcode( 'display_product_categories', 'parent_cat_list' );

/**
 * Shows product category child urls
 *
 * @param type $atts
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
 * @global type $cat_shortcode_query
 * @param type $atts
 * @return string
 */
function product_cat_shortcode( $atts ) {
	global $cat_shortcode_query, $product_sort, $archive_template;
	$cat_shortcode_query				 = array();
	$cat_shortcode_query[ 'current' ]	 = 0;
	$args								 = shortcode_atts( array(
		'exclude'			 => array(),
		'include'			 => array(),
		'archive_template'	 => get_option( 'archive_template', 'default' ),
		'parent'			 => '',
		'sort'				 => 0,
		'shortcode_query'	 => 'yes',
	), $atts );
	$div								 = '<div class="product-subcategories ' . $args[ 'archive_template' ] . ' ' . product_list_class( 'category-list' ) . '">';
	$cats								 = get_terms( 'al_product-cat', $args );
	$cat_shortcode_query[ 'count' ]		 = count( $cats );
	$cat_shortcode_query[ 'enable' ]	 = $args[ 'shortcode_query' ];
	$product_sort						 = intval( $args[ 'sort' ] );
	$inside								 = '';
	if ( $args[ 'parent' ] == '' && empty( $args[ 'include' ] ) ) {
		$old_args			 = $args;
		$args[ 'parent' ]	 = 0;
		$cats				 = get_terms( 'al_product-cat', $args );
		foreach ( $cats as $cat ) {
			$inside .= get_product_category_template( $args[ 'archive_template' ], $cat );
			$cat_shortcode_query[ 'current' ] ++;
			$inside .= get_sub_product_subcategories( $args, $cat );
		}
	} else {
		foreach ( $cats as $cat ) {
			$inside .= get_product_category_template( $args[ 'archive_template' ], $cat );
			$cat_shortcode_query[ 'current' ] ++;
		}
	}
	if ( !empty( $inside ) ) {
		do_action( 'before_category_list', $archive_template );
		$inside = $div . $inside;
		$inside .= '</div>';
	}
	reset_row_class();
	return $inside;
}

add_shortcode( 'product_category_name', 'product_category_name' );

/**
 * Returns current product category name
 */
function product_category_name() {
	$the_tax = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
	return $the_tax->name;
}

function get_sub_product_subcategories( $args, $parent_cat ) {
	global $cat_shortcode_query;
	$args[ 'parent' ]	 = $parent_cat->term_id;
	$cats				 = get_terms( 'al_product-cat', $args );
	$return				 = '';
	foreach ( $cats as $cat ) {
		$return .= get_product_category_template( $args[ 'archive_template' ], $cat );
		$cat_shortcode_query[ 'current' ] ++;
		$return .= get_sub_product_subcategories( $args, $cat );
	}
	return $return;
}

?>
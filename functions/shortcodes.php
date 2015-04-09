<?php

/**
 * Manages plugin shortcodes
 *
 * Here all shortcodes are defined.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        Norbert Dreszer
 */
if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

function parent_cat_list( $atts ) {
	$output = wp_list_categories( 'title_li=&orderby=name&depth=1&taxonomy=al_product-cat&echo=0' );
	return $output;
}

add_shortcode( 'display_product_categories', 'parent_cat_list' );

function product_cat_shortcode( $atts ) {
	global $cat_shortcode_query;
	$cat_shortcode_query				 = array();
	$cat_shortcode_query[ 'current' ]	 = 0;
	$args								 = shortcode_atts( array(
		'exclude'			 => array(),
		'include'			 => array(),
		'archive_template'	 => get_option( 'archive_template', 'default' ),
		'parent'			 => '',
	), $atts );
	$inside								 = '<div class="product-subcategories ' . $args[ 'archive_template' ] . ' ' . product_list_class() . '">';
	$cats								 = get_terms( 'al_product-cat', $args );
	$cat_shortcode_query[ 'count' ]		 = count( $cats );
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
	$inside .= '</div>';
	reset_row_class();
	return $inside;
}

add_shortcode( 'show_categories', 'product_cat_shortcode' );

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
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
add_action( 'ic_set_product_filters', 'ic_price_filter' );

/**
 * Handles price filter
 *
 * @param type $session
 */
function ic_price_filter( $session ) {
	if ( isset( $_GET[ 'min-price' ] ) ) {
		$filter_value = floatval( $_GET[ 'min-price' ] );
		if ( !empty( $filter_value ) ) {
			if ( !isset( $session[ 'filters' ] ) ) {
				$session[ 'filters' ] = array();
			}
			$session[ 'filters' ][ 'min-price' ] = $filter_value;
		} else if ( isset( $session[ 'filters' ][ 'min-price' ] ) ) {
			unset( $session[ 'filters' ][ 'min-price' ] );
		}
	} else if ( isset( $session[ 'filters' ][ 'min-price' ] ) ) {
		unset( $session[ 'filters' ][ 'min-price' ] );
	}
	if ( isset( $_GET[ 'max-price' ] ) ) {
		$filter_value = floatval( $_GET[ 'max-price' ] );
		if ( !empty( $filter_value ) ) {
			if ( !isset( $session[ 'filters' ] ) ) {
				$session[ 'filters' ] = array();
			}
			$session[ 'filters' ][ 'max-price' ] = $filter_value;
		} else if ( isset( $session[ 'filters' ][ 'max-price' ] ) ) {
			unset( $session[ 'filters' ][ 'max-price' ] );
		}
	} else if ( isset( $session[ 'filters' ][ 'max-price' ] ) ) {
		unset( $session[ 'filters' ][ 'max-price' ] );
	}
	set_product_catalog_session( $session );
}

add_action( 'apply_product_filters', 'ic_price_filter_apply' );

/**
 * Applies product price filter
 *
 * @param type $query
 */
function ic_price_filter_apply( $query ) {
	if ( is_product_filter_active( 'min-price' ) || is_product_filter_active( 'max-price' ) ) {
		$metaquery	 = array();
		$min_price	 = get_product_filter_value( 'min-price' );
		if ( !empty( $min_price ) ) {
			$metaquery[] = array(
				'key'		 => '_price',
				'compare'	 => '>=',
				'value'		 => $min_price,
				'type'		 => 'NUMERIC'
			);
		}
		$max_price = get_product_filter_value( 'max-price' );
		if ( !empty( $max_price ) ) {
			$metaquery[] = array(
				'key'		 => '_price',
				'compare'	 => '<=',
				'value'		 => $max_price,
				'type'		 => 'NUMERIC'
			);
		}
		$query->set( 'meta_query', $metaquery );
	}
}

add_filter( 'shortcode_query', 'ic_price_filter_shortcode_apply' );
add_filter( 'home_product_listing_query', 'ic_price_filter_shortcode_apply' );
add_filter( 'category_count_query', 'ic_price_filter_shortcode_apply' );

/**
 * Applies product price filter to shortcode query
 * @param type $shortcode_query
 * @return string
 */
function ic_price_filter_shortcode_apply( $shortcode_query ) {
	if ( is_product_filter_active( 'min-price' ) || is_product_filter_active( 'max-price' ) ) {
		$metaquery	 = array();
		$min_price	 = get_product_filter_value( 'min-price' );
		if ( !empty( $min_price ) ) {
			$metaquery[] = array(
				'key'		 => '_price',
				'compare'	 => '>=',
				'value'		 => $min_price,
				'type'		 => 'NUMERIC'
			);
		}
		$max_price = get_product_filter_value( 'max-price' );
		if ( !empty( $max_price ) ) {
			$metaquery[] = array(
				'key'		 => '_price',
				'compare'	 => '<=',
				'value'		 => $max_price,
				'type'		 => 'NUMERIC'
			);
		}
		$shortcode_query[ 'meta_query' ] = $metaquery;
	}
	return $shortcode_query;
}

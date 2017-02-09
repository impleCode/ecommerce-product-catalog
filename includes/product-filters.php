<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product search widget
 *
 * Here product search widget is defined.
 *
 * @version		1.4.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
if ( !function_exists( 'get_product_catalog_session' ) ) {

	function get_product_catalog_session() {
		if ( (!is_admin() || (defined( 'DOING_AJAX' ) && DOING_AJAX ) ) ) {
			if ( ic_use_php_session() ) {
				if ( !session_id() && !headers_sent() ) {
					session_start();
				}
				if ( !isset( $_SESSION[ 'implecode' ] ) ) {
					$_SESSION[ 'implecode' ] = array();
				}
				$wp_session = $_SESSION[ 'implecode' ];
			} else {
				$wp_session = WP_Session::get_instance();
			}
			return $wp_session;
		}
		return '';
	}

}

/**
 * Saves product catalog session
 *
 * @param type $session
 */
function set_product_catalog_session( $session ) {
	if ( ic_use_php_session() ) {
		$_SESSION[ 'implecode' ] = $session;
	}
}

function product_filter_element( $id, $what, $label, $class = null ) {
	$class = isset( $class ) ? 'filter-url ' . $class : 'filter-url';
	if ( is_product_filter_active( $what ) ) {
		if ( is_product_filter_active( $what, $id ) ) {
			$class	 .= ' active-filter';
			$id		 = 'clear';
		} else {
			$class .= ' not-active-filter';
		}
	}
	if ( is_paged() ) {
		if ( is_ic_permalink_product_catalog() ) {
//echo get_pagenum_link( 1 );
			$url = esc_url_raw( remove_query_arg( $what, get_pagenum_link( 1, false ) ) );
		} else {
			$url = esc_url_raw( remove_query_arg( array( 'paged', $what ) ) );
		}
	} else {
		$url = esc_url_raw( remove_query_arg( array( $what ) ) );
	}
	$attr = '';
	if ( is_ic_ajax() && !empty( $_GET[ 'page' ] ) ) {
		$attr = 'data-page="' . intval( $_GET[ 'page' ] ) . '"';
	}
	return '<a class="' . $class . '" href="' . add_query_arg( array( $what => $id ), $url ) . '" ' . $attr . '>' . $label . '</a>';
}

function get_product_category_filter_element( $category ) {
	$count = total_product_category_count( $category->term_id );
	if ( $count > 0 ) {
		$name = $category->name . ' (' . $count . ')';
		return product_filter_element( $category->term_id, 'product_category', $name );
	}
	return;
}

add_action( 'wp_loaded', 'set_product_filter' );

/*
 * Sets up active filters
 *
 */

function set_product_filter() {
	if ( is_admin() && !is_ic_ajax() ) {
		return;
	}
	$session = get_product_catalog_session();
	if ( isset( $_GET[ 'product_category' ] ) ) {
		$filter_value = intval( $_GET[ 'product_category' ] );
		if ( !empty( $filter_value ) ) {
			if ( !isset( $session[ 'filters' ] ) ) {
				$session[ 'filters' ] = array();
			}
			$session[ 'filters' ][ 'product_category' ] = $filter_value;
		} else if ( isset( $session[ 'filters' ][ 'product_category' ] ) ) {
			unset( $session[ 'filters' ][ 'product_category' ] );
		}
	}
	set_product_catalog_session( $session );
	do_action( 'ic_set_product_filters', $session );
	$session = get_product_catalog_session();
	if ( !empty( $session[ 'filters' ] ) && empty( $session[ 'filters' ][ 'filtered-url' ] ) ) {
		$active_filters = get_active_product_filters();
		if ( isset( $_POST[ 'request_url' ] ) && is_ic_ajax() ) {
			$old_request_url						 = $_SERVER[ 'REQUEST_URI' ];
			$_SERVER[ 'REQUEST_URI' ]				 = $_POST[ 'request_url' ];
			add_filter( 'get_pagenum_link', 'ic_ajax_pagenum_link' );
			$session[ 'filters' ][ 'filtered-url' ]	 = remove_query_arg( $active_filters, get_pagenum_link( 1, false ) );
			remove_filter( 'get_pagenum_link', 'ic_ajax_pagenum_link' );
			$_SERVER[ 'REQUEST_URI' ]				 = $old_request_url;
		} else {
			$session[ 'filters' ][ 'filtered-url' ] = remove_query_arg( $active_filters, get_pagenum_link( 1, false ) );
		}
		if ( ic_string_contains( $session[ 'filters' ][ 'filtered-url' ], '&s=' ) ) {
			if ( is_ic_ajax() && empty( $_POST[ 'is_search' ] ) ) {
				$session[ 'filters' ][ 'filtered-url' ] = remove_query_arg( array( 's', 'post_type' ), $session[ 'filters' ][ 'filtered-url' ] );
			} else {
				$session[ 'filters' ][ 'filtered-url' ] = add_query_arg( 'reset_filters', 'y', $session[ 'filters' ][ 'filtered-url' ] );
			}
		}
		if ( is_ic_shortcode_query() ) {
			$session[ 'filters' ][ 'filtered-url' ] = add_query_arg( 'reset_filters', 'y', $session[ 'filters' ][ 'filtered-url' ] );
		}
		$session[ 'filters' ][ 'filtered-url' ] = esc_url( $session[ 'filters' ][ 'filtered-url' ] . '#product_filters_bar' );
		set_product_catalog_session( $session );
	}
}

//add_action( 'ic_pre_get_products', 'delete_product_filters', 2 );
//add_action( 'ic_pre_get_products_shortcode', 'delete_product_filters', 2 );
add_action( 'wp_loaded', 'delete_product_filters', 11 );

/**
 * Clears current filters if there is a page reload without new filter assignment
 *
 */
function delete_product_filters() {
//if ( !is_admin() && is_product_filters_active() && (!is_search() || isset( $_GET[ 'reset_filters' ] ) ) && $query->is_main_query() ) {
	if ( is_product_filters_active() && (!is_search() || isset( $_GET[ 'reset_filters' ] ) ) ) {
		$active_filters	 = get_active_product_filters();
		$out			 = false;
		foreach ( $active_filters as $filter ) {
			if ( isset( $_GET[ $filter ] ) ) {
				$out = true;
			}
		}
		if ( !$out ) {
			$session = get_product_catalog_session();
			unset( $session[ 'filters' ] );
			set_product_catalog_session( $session );
		}
	}
}

/**
 * Defines active product filters
 *
 * @return array
 */
function get_active_product_filters() {
	return apply_filters( 'active_product_filters', array( 'product_category', 'min-price',
		'max-price' ) );
}

function get_product_filter_value( $filter_name ) {
	if ( is_product_filter_active( $filter_name ) ) {
		$session = get_product_catalog_session();
		return $session[ 'filters' ][ $filter_name ];
	}
	return '';
}

add_action( 'ic_pre_get_products', 'apply_product_filters', 20 );

/**
 * Applies current filters to the query
 *
 * @param object $query
 */
function apply_product_filters( $query ) {
//if ( ((!is_admin() && $query->is_main_query()) || (defined( 'DOING_AJAX' ))) && !is_home_archive( $query ) && (is_ic_product_listing( $query ) || is_ic_taxonomy_page() || is_ic_product_search()) ) {
	if ( is_product_filters_active() && is_product_filter_active( 'product_category' ) ) {
		$category_id = get_product_filter_value( 'product_category' );
		$taxonomy	 = get_current_screen_tax();
		$taxquery	 = array(
			array(
				'taxonomy'	 => $taxonomy,
				'terms'		 => $category_id,
			)
		);
		$query->set( 'tax_query', $taxquery );
	}
	do_action( 'apply_product_filters', $query );
//}
}

add_filter( 'shortcode_query', 'apply_product_category_filter' );
add_filter( 'home_product_listing_query', 'apply_product_category_filter' );

/**
 * Applies product category filter to shortcode query
 *
 * @param type $shortcode_query
 * @return type
 */
function apply_product_category_filter( $shortcode_query ) {
	if ( is_product_filter_active( 'product_category' ) ) {
		$category_id					 = get_product_filter_value( 'product_category' );
		$taxonomy						 = get_current_screen_tax();
		$taxquery						 = array(
			array(
				'taxonomy'	 => $taxonomy,
				'terms'		 => $category_id,
			)
		);
		$shortcode_query[ 'tax_query' ]	 = $taxquery;
	}
	return $shortcode_query;
}

/**
 * Returns category product count with product in child categories
 *
 * @param type $cat_id
 * @return type
 */
function total_product_category_count( $cat_id ) {
	$taxonomy	 = get_current_screen_tax();
	$query_args	 = apply_filters( 'category_count_query', array(
		'nopaging'	 => true,
		'tax_query'	 => array(
			array(
				'taxonomy'			 => $taxonomy,
				'terms'				 => $cat_id,
				'include_children'	 => true,
			),
		),
		'fields'	 => 'ids',
	) );
	if ( isset( $_GET[ 's' ] ) ) {
		$query_args[ 's' ] = $_GET[ 's' ];
	}
	remove_action( 'pre_get_posts', 'ic_pre_get_products', 99 );
	$q = new WP_Query( $query_args );
	add_action( 'pre_get_posts', 'ic_pre_get_products', 99 );
	return $q->post_count;
}

add_filter( 'product_search_button_text', 'modify_search_widget_filter' );

/**
 * Deletes search button text in filter bar
 *
 * @param string $text
 * @return string
 */
function modify_search_widget_filter( $text ) {
	if ( is_filter_bar() ) {
		$text = '';
	}
	return $text;
}

add_action( 'wp_ajax_hide_empty_bar_message', 'hide_empty_bar_message' );

/**
 * Ajax receiver to hide the filters bar empty message
 *
 */
function hide_empty_bar_message() {
	update_option( 'hide_empty_bar_message', 1 );
	wp_die();
}

/**
 * Returns the URL to redirect after filters reset
 *
 * @return type
 */
function get_filters_bar_reset_url() {
	$session = get_product_catalog_session();
	if ( !empty( $session[ 'filters' ][ 'filtered-url' ] ) ) {
		return $session[ 'filters' ][ 'filtered-url' ];
	}
	return '';
}

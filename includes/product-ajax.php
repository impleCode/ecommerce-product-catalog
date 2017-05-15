<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product post type
 *
 * Here all product fields are defined.
 *
 * @version        1.1.1
 * @package        ecommerce-product-catalog/includes
 * @author        Norbert Dreszer
 */
add_action( 'wp_ajax_nopriv_ic_self_submit', 'ic_ajax_self_submit' );
add_action( 'wp_ajax_ic_self_submit', 'ic_ajax_self_submit' );

/**
 * Manages ajax price format
 *
 */
function ic_ajax_self_submit() {
	check_ajax_referer( 'ic_ajax', 'security' );
	if ( isset( $_POST[ 'self_submit_data' ] ) ) {
		$params				 = array();
		parse_str( $_POST[ 'self_submit_data' ], $params );
		$_GET				 = $params;
		global $ic_ajax_query_vars;
		$ic_ajax_query_vars	 = json_decode( stripslashes( $_POST[ 'query_vars' ] ), true );
		if ( (isset( $ic_ajax_query_vars[ 'post_type' ] ) && !in_array( $ic_ajax_query_vars[ 'post_type' ], product_post_type_array() ) ) ) {
			wp_die();
			return;
		}
		do_action( 'ic_ajax_self_submit', $ic_ajax_query_vars, $params );
		if ( isset( $params[ 's' ] ) ) {
			$ic_ajax_query_vars[ 's' ] = $params[ 's' ];
		}
		if ( isset( $params[ 'page' ] ) ) {
			$ic_ajax_query_vars[ 'paged' ] = $params[ 'page' ];
		}
		if ( !empty( $ic_ajax_query_vars[ 'post_type' ] ) && !ic_string_contains( $ic_ajax_query_vars[ 'post_type' ], 'al_product' ) ) {
			$_GET[ 'post_type' ] = $ic_ajax_query_vars[ 'post_type' ];
		}
		$ic_ajax_query_vars[ 'post_status' ] = 'publish';
		//print_r( $ic_ajax_query_vars );
		if ( !empty( $ic_ajax_query_vars[ 'posts_per_page' ] ) ) {
			remove_action( 'ic_pre_get_products', 'set_products_limit', 99 );
			remove_action( 'pre_get_posts', 'set_multi_products_limit', 99 );
		}
		$posts = new WP_Query( $ic_ajax_query_vars );
		if ( !empty( $ic_ajax_query_vars[ 'paged' ] ) && $ic_ajax_query_vars[ 'paged' ] > 1 && empty( $posts->post ) ) {
			unset( $ic_ajax_query_vars[ 'paged' ] );
			unset( $_GET[ 'page' ] );
			$return[ 'remove_pagination' ]	 = 1;
			$posts							 = new WP_Query( $ic_ajax_query_vars );
		}
		if ( !empty( $_POST[ 'shortcode' ] ) ) {
			global $shortcode_query;
			$shortcode_query = $posts;
		}
		$GLOBALS[ 'wp_query' ]	 = $posts;
		$archive_template		 = get_product_listing_template();
		$multiple_settings		 = get_multiple_settings();
		remove_all_actions( 'before_product_list' );
		ob_start();
		ic_product_listing_products( $archive_template, $multiple_settings );

		$return[ 'product-listing' ]	 = ob_get_clean();
		$old_request_url				 = $_SERVER[ 'REQUEST_URI' ];
		$_SERVER[ 'REQUEST_URI' ]		 = ic_get_ajax_request_url();
		ob_start();
		add_filter( 'get_pagenum_link', 'ic_ajax_pagenum_link' );
		product_archive_pagination();
		remove_filter( 'get_pagenum_link', 'ic_ajax_pagenum_link' );
		$return[ 'product-pagination' ]	 = ob_get_clean();
		if ( !empty( $_POST[ 'ajax_elements' ][ 'product-category-filter-container' ] ) ) {
			ob_start();
			the_widget( 'product_category_filter' );
			$return[ 'product-category-filter-container' ] = ob_get_clean();
		}
		if ( !empty( $_POST[ 'ajax_elements' ][ 'product_price_filter' ] ) ) {
			ob_start();
			the_widget( 'product_price_filter' );
			$return[ 'product_price_filter' ] = ob_get_clean();
		}
		if ( !empty( $_POST[ 'ajax_elements' ][ 'product_order' ] ) ) {
			ob_start();
			the_widget( 'product_sort_filter' );
			$return[ 'product_order' ] = ob_get_clean();
		}
		if ( !empty( $_POST[ 'ajax_elements' ][ 'product-sort-bar' ] ) ) {
			ob_start();
			show_product_sort_bar( $archive_template, $multiple_settings );
			$return[ 'product-sort-bar' ] = ob_get_clean();
		}
		$_SERVER[ 'REQUEST_URI' ]	 = $old_request_url;
		$echo						 = json_encode( $return );
		echo $echo;
	}
	wp_die();
}

add_action( 'register_catalog_styles', 'ic_product_ajax_register_styles' );

function ic_product_ajax_register_styles() {
	//wp_register_style( 'ic_variations', plugins_url( '/', __FILE__ ) . '/css/variations-front.css', array( 'al_product_styles' ) );
	wp_register_script( 'ic_product_ajax', AL_PLUGIN_BASE_PATH . 'js/product-ajax.js?' . filemtime( AL_BASE_PATH . '/js/product-ajax.js' ) );
}

add_action( 'enqueue_catalog_scripts', 'ic_product_ajax_enqueue_styles' );

function ic_product_ajax_enqueue_styles() {
	//wp_enqueue_style( 'ic_variations' );
	wp_enqueue_script( 'ic_product_ajax' );
	$query_vars = array();
	if ( is_ic_catalog_page() ) {
		global $wp_query;
		$query_vars = apply_filters( 'ic_product_ajax_query_vars', $wp_query->query );
		if ( empty( $query_vars ) && is_home_archive() ) {
			$query_vars = array( 'post_type' => 'al_product' );
		}
		if ( empty( $query_vars[ 'post_type' ] ) ) {
			$query_vars[ 'ic_post_type' ] = get_post_type();
		}
	}
	$active_filters = get_active_product_filters();
	wp_localize_script( 'ic_product_ajax', 'ic_ajax', array(
		'query_vars'		 => json_encode( $query_vars ),
		'request_url'		 => esc_url( remove_query_arg( array_merge( array( 'page', 'paged' ), $active_filters ) ) ),
		//'request_url'		 => remove_query_arg( array( 'page', 'paged' ) ),
		'filters_reset_url'	 => get_filters_bar_reset_url(),
		'is_search'			 => is_search(),
		'nonce'				 => wp_create_nonce( "ic_ajax" )
	) );
}

add_filter( 'product-list-attr', 'ic_ajax_shortcode_query_data', 10, 2 );

function ic_ajax_shortcode_query_data( $attr, $query ) {
	global $shortcode_query;
	if ( !empty( $shortcode_query->query ) ) {
		unset( $shortcode_query->query[ 'post_status' ] );
		$attr .= " data-ic_ajax_query='" . json_encode( $shortcode_query->query ) . "'";
	}
	return $attr;
}

function ic_ajax_pagenum_link( $link ) {
	if ( is_ic_ajax() ) {
		global $wp_rewrite;
		$query_string	 = str_replace( '?', '', strstr( $link, '?' ) );
		parse_str( $query_string, $params );
		$pagenum		 = isset( $params[ 'paged' ] ) ? (int) $params[ 'paged' ] : 0;
		$request		 = remove_query_arg( array( 'paged' ), ic_get_ajax_request_url() );
		$active_filters	 = get_active_product_filters( true );

		$request = add_query_arg( $active_filters, $request );

		$site_url	 = home_url();
		$home_root	 = parse_url( home_url() );
		$home_root	 = ( isset( $home_root[ 'path' ] ) ) ? $home_root[ 'path' ] : '';
		$home_root	 = preg_quote( $home_root, '|' );

		$request = preg_replace( '|^' . $home_root . '|i', '', $request );
		$request = preg_replace( '|^/+|', '', $request );
		$request = $site_url . '/' . $request;
		if ( !$wp_rewrite->using_permalinks() ) {
			$base = trailingslashit( get_bloginfo( 'url' ) );

			if ( $pagenum > 1 ) {
				$result = add_query_arg( 'paged', $pagenum, $base . $request );
			} else {
				$result = $base . $request;
			}
		} else {
			$qs_regex = '|\?.*?$|';
			preg_match( $qs_regex, $request, $qs_match );

			if ( !empty( $qs_match[ 0 ] ) ) {
				$query_string	 = $qs_match[ 0 ];
				$request		 = preg_replace( $qs_regex, '', $request );
			} else if ( !empty( $pagenum ) ) {
				$query_string = str_replace( 'paged=' . $pagenum, '', $query_string );
			}

			$request = preg_replace( "|$wp_rewrite->pagination_base/\d+/?$|", '', $request );
			$request = preg_replace( '|^' . preg_quote( $wp_rewrite->index, '|' ) . '|i', '', $request );
			$request = ltrim( $request, '/' );

			$base = trailingslashit( get_bloginfo( 'url' ) );

			if ( $wp_rewrite->using_index_permalinks() && ( $pagenum > 1 || '' != $request ) )
				$base .= $wp_rewrite->index . '/';

			if ( $pagenum > 1 ) {
				$request = ( (!empty( $request ) ) ? trailingslashit( $request ) : $request ) . user_trailingslashit( $wp_rewrite->pagination_base . "/" . $pagenum, 'paged' );
			}

			//$result = $base . $request . $query_string;
			$result = $request . $query_string;
		}

		return $result;
	}
	return $link;
}

function ic_get_ajax_request_url() {
	return esc_url( $_POST[ 'request_url' ] );
}

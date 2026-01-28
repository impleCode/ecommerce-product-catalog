<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product post type
 *
 * Here all product fields are defined.
 *
 * @version        1.1.1
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
class ic_catalog_ajax {

	function __construct() {
		add_action( 'wp_loaded', array( $this, 'ajax_get' ), 5 );
		add_action( 'wp_ajax_nopriv_ic_self_submit', array( $this, 'ajax_self_submit' ) );
		add_action( 'wp_ajax_ic_self_submit', array( $this, 'ajax_self_submit' ) );
		add_action( 'register_catalog_styles', array( $this, 'register_styles' ) );
		add_action( 'enqueue_main_catalog_scripts', array( __CLASS__, 'enqueue_styles' ) );
		add_filter( 'product-list-attr', array( $this, 'shortcode_query_data' ), 10, 2 );
	}

	function ajax_get() {
		if ( is_admin() && ! empty( $_POST['self_submit_data'] ) ) {
			$params = array();
			parse_str( $_POST['self_submit_data'], $params );
			$_GET = $params;
		}
	}

	/**
	 * Handles AJAX self-submissions for modifying query variables, retrieving products,
	 * and generating updated parts of a product archive, such as listings, pagination,
	 * and various filters.
	 *
	 * This function processes submitted form data, performs query modifications based
	 * on the incoming parameters, and returns the updated HTML for product listing,
	 * pagination, and filters in a JSON format.
	 *
	 * @return void Outputs a JSON-encoded string containing the updated product
	 *              listing, pagination, and filter elements. Terminates script execution.
	 */
	function ajax_self_submit() {
		if ( isset( $_POST['self_submit_data'] ) && isset( $_POST['security'] ) && wp_verify_nonce( $_POST['security'], 'ic_ajax' ) ) {
			ic_set_time_limit( 3 );
			remove_filter( 'parse_tax_query', 'exclude_products_from_child_cat' );
			$params = array();
			parse_str( $_POST['self_submit_data'], $params );
			$params = array_map( 'ic_sanitize', $params );
			$_GET   = $params;
			global $ic_ajax_query_vars;
			$ic_ajax_query_vars = apply_filters( 'ic_catalog_query', json_decode( stripslashes( $_POST['query_vars'] ), true ) );
			/*if ( is_array( $ic_ajax_query_vars ) ) {
				$ic_ajax_query_vars = array_map( 'ic_sanitize', $ic_ajax_query_vars );
			} else */
			if ( ! empty( $ic_ajax_query_vars ) ) {
				$ic_ajax_query_vars = ic_sanitize( $ic_ajax_query_vars );
			}
			$pre_ic_ajax_query_vars = $ic_ajax_query_vars;
			unset( $ic_ajax_query_vars['pagename'] );
			unset( $ic_ajax_query_vars['page_id'] );
			do_action( 'ic_ajax_self_submit_init', $ic_ajax_query_vars, $params, $pre_ic_ajax_query_vars );
			if ( isset( $ic_ajax_query_vars['post_type'] ) && ! is_ic_valid_post_type( $ic_ajax_query_vars['post_type'] ) ) {
				wp_die();

				return;
			}

			do_action( 'ic_ajax_self_submit', $ic_ajax_query_vars, $params );

			if ( isset( $params['s'] ) ) {
				$ic_ajax_query_vars['s'] = $params['s'];
				foreach ( $ic_ajax_query_vars as $query_var_key => $query_var_value ) {
					if ( ic_string_contains( $query_var_key, 'al_product-cat' ) ) {
						unset( $ic_ajax_query_vars[ $query_var_key ] );
					}
				}
				$ic_ajax_query_vars = array_merge( $ic_ajax_query_vars, $params );
			}
			if ( isset( $params['page'] ) ) {
				$ic_ajax_query_vars['paged'] = $params['page'];
			}
			if ( ! empty( $ic_ajax_query_vars['post_type'] ) && ! ic_string_contains( $ic_ajax_query_vars['post_type'], 'al_product' ) ) {
				$_GET['post_type'] = $ic_ajax_query_vars['post_type'];
			}
			$ic_ajax_query_vars['post_status'] = ic_visible_product_status();
			if ( ! empty( $ic_ajax_query_vars['posts_per_page'] ) ) {
				remove_action( 'ic_pre_get_products', 'set_products_limit', 99 );
				remove_action( 'pre_get_posts', 'set_multi_products_limit', 99 );
			}
			add_filter( 'parse_tax_query', 'exclude_products_from_child_cat' );
			$posts = apply_filters( 'ic_catalog_ajax_posts', '', $ic_ajax_query_vars );
			if ( empty( $posts ) ) {
				foreach ( $ic_ajax_query_vars as $query_var_key => $query_var_value ) {
					$GLOBALS['wp_query']->set( $query_var_key, $query_var_value );
				}
				$GLOBALS['wp_query']->get_posts();
				$posts = $GLOBALS['wp_query'];
			}
			if ( ! empty( $ic_ajax_query_vars['paged'] ) && $ic_ajax_query_vars['paged'] > 1 && empty( $posts->post ) ) {
				unset( $ic_ajax_query_vars['paged'] );
				$GLOBALS['wp_query']->set( 'paged', false );
				unset( $_GET['page'] );
				$return['remove_pagination'] = 1;
				$GLOBALS['wp_query']->get_posts();
				$posts = $GLOBALS['wp_query'];
			}
			remove_filter( 'parse_tax_query', 'exclude_products_from_child_cat' );
			if ( ! empty( $posts->query['post_type'] ) && count( $posts->query ) === 2 && ic_string_contains( $posts->query['post_type'], 'al_product' ) ) {
				$posts->is_post_type_archive = true;
			}
			if ( ! empty( $_POST['ic_shortcode'] ) ) {
				global $shortcode_query;
				$shortcode_query = $posts;
			}
			if ( ! empty( $ic_ajax_query_vars['archive_template'] ) ) {
				$archive_template = $ic_ajax_query_vars['archive_template'];
			} else {
				$archive_template = get_product_listing_template();
			}
			$multiple_settings = get_multiple_settings();
			remove_all_actions( 'before_product_list' );
			ob_start();
			do_action( 'before_ajax_product_list', $GLOBALS['wp_query'] );
			ic_product_listing_products( $archive_template, $multiple_settings );
			$return['product-listing'] = ob_get_clean();
			$old_request_url           = $_SERVER['REQUEST_URI'];
			$_SERVER['REQUEST_URI']    = $this->get_ajax_request_url();
			ob_start();
			add_filter( 'get_pagenum_link', array( $this, 'pagenum_link' ) );
			product_archive_pagination();
			remove_filter( 'get_pagenum_link', array( $this, 'pagenum_link' ) );
			$return['product-pagination'] = ob_get_clean();
			if ( ! empty( $_POST['ajax_elements']['product-category-filter-container'] ) ) {
				ob_start();
				the_widget( 'product_category_filter', $_POST['ajax_elements']['product-category-filter-container']['instance'], $_POST['ajax_elements']['product-category-filter-container']['args'] );
				$return['product-category-filter-container'] = ob_get_clean();
			}
			if ( ! empty( $_POST['ajax_elements']['price-filter'] ) ) {
				ob_start();
				the_widget( 'product_price_filter', $_POST['ajax_elements']['price-filter']['instance'], $_POST['ajax_elements']['price-filter']['args'] );
				$return['price-filter'] = ob_get_clean();
			}
			if ( ! empty( $_POST['ajax_elements']['product-size-filter-container'] ) ) {
				ob_start();
				the_widget( 'ic_product_size_filter' );
				$return['product-size-filter-container'] = ob_get_clean();
			}
			if ( ! empty( $_POST['ajax_elements']['product_order'] ) ) {
				ob_start();
				the_widget( 'product_sort_filter' );
				$return['product_order'] = ob_get_clean();
			}
			if ( ! empty( $_POST['ajax_elements']['product-sort-bar'] ) ) {
				ob_start();
				show_product_sort_bar( $archive_template, $multiple_settings );
				$return['product-sort-bar'] = ob_get_clean();
			}
			if ( ! empty( $_POST['ajax_elements']['ic-active-filters'] ) ) {
				$return['ic-active-filters'] = ic_get_active_filters_html();
			}
			$return                 = apply_filters( 'ic_ajax_self_submit_return', $return );
			$_SERVER['REQUEST_URI'] = $old_request_url;
			$encoded                = array();
			foreach ( $return as $key => $string ) {
				if ( function_exists( 'mb_convert_encoding' ) ) {
					$encoded[ $key ] = mb_convert_encoding( $string, 'UTF-8', 'UTF-8' );
				} else if ( function_exists( 'iconv' ) ) {
					$encoded[ $key ] = iconv( 'UTF-8', 'UTF-8', $string );
				} else {
					$encoded[ $key ] = $string;
				}
			}
			echo json_encode( $encoded );
		}
		wp_die();
	}

	function register_styles() {
		wp_register_script( 'ic_product_ajax', AL_PLUGIN_BASE_PATH . 'js/product-ajax.min.js' . ic_filemtime( AL_BASE_PATH . '/js/product-ajax.min.js' ), array( 'al_product_scripts' ), false, true );
	}

	static function enqueue_styles() {
		wp_enqueue_script( 'ic_product_ajax' );
		global $post, $wp_query;
		$query_vars = ic_get_catalog_query_vars( false, true );
		if ( empty( $query_vars ) ) {
			$catalog_query = ic_get_catalog_query();
			if ( isset( $catalog_query->query ) ) {
				$query_vars = $catalog_query->query;
			} else if ( isset( $wp_query->query ) ) {
				$query_vars = $wp_query->query;
			}
		}
		$query_vars = apply_filters( 'ic_product_ajax_query_vars', $query_vars );
		if ( is_ic_catalog_page() ) {
			if ( ( empty( $query_vars ) && is_home_archive() ) || ( is_ic_shortcode_integration() && is_ic_product_listing() ) ) {
				//$query_vars = array( 'post_type' => 'al_product' );
				$query_vars['post_type'] = get_current_screen_post_type();
			}
			if ( empty( $query_vars['post_type'] ) ) {
				$post_type = get_post_type();
				if ( ! ic_string_contains( $post_type, 'al_product' ) ) {
					$post_type = 'al_product';
				}
				//$query_vars[ 'ic_post_type' ] = $post_type;
				$query_vars['ic_post_type'] = get_current_screen_post_type();
			}
		} else if ( isset( $post->post_content ) && ic_has_page_catalog_shortcode( $post ) ) {
			$query_vars['post_type'] = 'al_product';
		}
		$active_filters = get_active_product_filters();
		wp_localize_script( 'ic_product_ajax', 'ic_ajax', array(
			'query_vars'        => json_encode( $query_vars ),
			//'request_url'		 => esc_url( remove_query_arg( array_merge( array( 'page', 'paged' ), $active_filters ) ) ),
			'request_url'       => esc_url( remove_query_arg( $active_filters, get_pagenum_link( 1, false ) ) ),
			//'request_url'		 => remove_query_arg( array( 'page', 'paged' ) ),
			'filters_reset_url' => get_filters_bar_reset_url(),
			'is_search'         => is_search(),
			'nonce'             => wp_create_nonce( "ic_ajax" )
		) );
	}

	function shortcode_query_data( $attr, $query ) {
		global $shortcode_query;
		if ( ! empty( $shortcode_query->query ) ) {
			unset( $shortcode_query->query['post_status'] );
			$attr .= " data-ic_ajax_query='" . esc_attr( json_encode( $shortcode_query->query ) ) . "'";
		}

		return $attr;
	}

	function pagenum_link( $link ) {
		if ( is_ic_ajax() ) {
			global $wp_rewrite;
			$query_string = str_replace( '?', '', strstr( $link, '?' ) );
			parse_str( $query_string, $params );
			$pagenum        = isset( $params['paged'] ) ? (int) $params['paged'] : 0;
			$request        = remove_query_arg( array( 'paged' ), $this->get_ajax_request_url() );
			$active_filters = get_active_product_filters( true, true );
			$request        = add_query_arg( $active_filters, $request );
			if ( isset( $_POST['self_submit_data'] ) ) {
				parse_str( $_POST['self_submit_data'], $submit_params );
				if ( isset( $submit_params['s'] ) && isset( $submit_params['post_type'] ) ) {
					$request = add_query_arg( $submit_params, $request );
				}
			}
			$site_url  = home_url();
			$home_root = parse_url( home_url() );
			$home_root = ( isset( $home_root['path'] ) ) ? $home_root['path'] : '';
			$home_root = preg_quote( $home_root, '|' );

			$request = preg_replace( '|^' . $home_root . '|i', '', $request );
			$request = preg_replace( '|^/+|', '', $request );
//$request = $site_url . '/' . $request;

			if ( ! $wp_rewrite->using_permalinks() ) {
				$base = trailingslashit( get_bloginfo( 'url' ) );

				if ( $pagenum > 1 ) {
					$result = add_query_arg( 'paged', $pagenum, $base . $request );
				} else {
					$result = $base . $request;
				}
			} else {
				$qs_regex = '|\?.*?$|';
				preg_match( $qs_regex, $request, $qs_match );

				if ( ! empty( $qs_match[0] ) ) {
					$query_string = $qs_match[0];
					$request      = preg_replace( $qs_regex, '', $request );
				} else if ( ! empty( $pagenum ) ) {
					$query_string = str_replace( 'paged=' . $pagenum, '', $query_string );
				}

				$request = preg_replace( "|$wp_rewrite->pagination_base/\d+/?$|", '', $request );
				$request = preg_replace( '|^' . preg_quote( $wp_rewrite->index, '|' ) . '|i', '', $request );
				$request = ltrim( $request, '/' );

				$base = trailingslashit( get_bloginfo( 'url' ) );

				if ( $wp_rewrite->using_index_permalinks() && ( $pagenum > 1 || '' != $request ) ) {
					$base .= $wp_rewrite->index . '/';
				}

				if ( $pagenum > 1 ) {
					$request = ( ( ! empty( $request ) ) ? trailingslashit( $request ) : $request ) . user_trailingslashit( $wp_rewrite->pagination_base . "/" . $pagenum, 'paged' );
				}
				$query_string = str_replace( '#038;', '&', $query_string );
//$result = $base . $request . $query_string;
				$result = $request . $query_string;
			}

			return $result;
		}

		return $link;
	}

	function get_ajax_request_url() {
		return esc_url_raw( $_POST['request_url'] );
	}

}

$ic_catalog_ajax = new ic_catalog_ajax;

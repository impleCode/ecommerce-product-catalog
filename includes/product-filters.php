<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product search widget
 *
 * Here product search widget is defined.
 *
 * @version        1.4.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
if ( ! function_exists( 'get_product_catalog_session' ) ) {

	function get_product_catalog_session() {
		if ( ! is_admin() || is_ic_front_ajax() ) {
			global $IC_Session;
			$prefix = ic_get_session_prefix();
			if ( ic_use_php_session() ) {
				if ( ! ic_is_session_started() && ! headers_sent() && ic_ic_cookie_enabled() && ( ! is_admin() || is_ic_front_ajax() ) ) {
					ic_php_session_start();
				}
				if ( ! isset( $_SESSION[ $prefix ] ) || ( isset( $_SESSION[ $prefix ] ) && ! is_array( $_SESSION[ $prefix ] ) ) ) {
					$_SESSION[ $prefix ] = array();
				}
				if ( empty( $IC_Session ) ) {
					if ( isset( $_SESSION[ $prefix ] ) ) {
						$IC_Session[ $prefix ] = $_SESSION[ $prefix ];
					} else {
						$IC_Session[ $prefix ] = array();
					}
				}
				$session = $IC_Session[ $prefix ];
			} else {
				$implecode_session = ic_get_global( 'ic_session' );
				if ( ! empty( $implecode_session ) ) {
					$IC_Session[ $prefix ] = $implecode_session->get();
				} else if ( empty( $IC_Session ) && class_exists( 'WP_Session' ) ) {
					$IC_Session = WP_Session::get_instance();
				}
				if ( ! isset( $IC_Session[ $prefix ] ) || ( isset( $IC_Session[ $prefix ] ) && ! is_array( $IC_Session[ $prefix ] ) ) ) {
					$IC_Session[ $prefix ] = array();
				}
				$session = $IC_Session[ $prefix ];
			}

			if ( empty( $session ) || ! is_array( $session ) ) {
				$session = array();
			}

			return $session;
		}

		return array();
	}

	function ic_get_session_prefix() {
		$prefix = 'implecode';
		if ( is_multisite() ) {
			$prefix .= '_' . get_current_blog_id();
		}

		return $prefix;
	}

}
if ( ! function_exists( 'set_product_catalog_session' ) ) {

	/**
	 * Saves product catalog session
	 *
	 * @param array $session
	 */
	function set_product_catalog_session( $session ) {
		if ( ! is_admin() || is_ic_front_ajax() ) {
			global $IC_Session;
			$prefix = ic_get_session_prefix();
			if ( ic_use_php_session() ) {
				if ( ! headers_sent() && ic_ic_cookie_enabled() && ( ! is_admin() || is_ic_front_ajax() ) ) {
					if ( ic_is_session_started() ) {
						if ( ! has_action( 'send_headers', 'ic_session_save_end' ) ) {
							add_action( 'send_headers', 'ic_session_save_end' );
							add_filter( 'wp_die_ajax_handler', 'ic_ajax_session_save_end' );
							add_filter( 'wp_redirect', 'ic_ajax_session_save_end' );
							add_action( 'shutdown', 'ic_session_save', 10, 0 );
						}
					} else {
						ic_php_session_start();
					}
				}
			} else {
				$implecode_session = ic_get_global( 'ic_session' );
				if ( ! empty( $implecode_session ) ) {
					$implecode_session->replace( $session );
				} else if ( empty( $IC_Session ) && ic_ic_cookie_enabled() && class_exists( 'WP_Session' ) ) {
					$IC_Session = WP_Session::get_instance();
				}
			}
			$IC_Session[ $prefix ] = $session;
		}
	}
}

function ic_ajax_session_save_end( $handler ) {
	ic_session_save_end();

	return $handler;
}

function ic_php_session_start() {
	session_start();
	add_action( 'shutdown', 'ic_session_save', 10, 0 );
	add_action( 'shutdown', 'session_write_close', 99, 0 );
	add_action( 'send_headers', 'ic_session_save_end' );
	add_filter( 'wp_die_ajax_handler', 'ic_ajax_session_save_end' );
	add_filter( 'wp_redirect', 'ic_ajax_session_save_end' );
	add_action( 'ic_session_save_end', 'session_write_close', 10, 0 );
	add_action( 'requests-curl.before_request', 'session_write_close', 10, 0 );
}

function ic_session_save() {
	global $IC_Session;
	$prefix = ic_get_session_prefix();
	if ( ic_is_session_started() && isset( $IC_Session[ $prefix ] ) ) {
		if ( empty( $_SESSION[ $prefix ] ) ) {
			$_SESSION[ $prefix ] = array();
		}
		$_SESSION[ $prefix ] = $IC_Session[ $prefix ];
	}
}

function ic_session_save_end() {
	ic_session_save();
	do_action( 'ic_session_save_end' );
}

function product_filter_element( $id, $what, $label, $class = null ) {
	$category_id = $id;
	$class       = isset( $class ) ? 'filter-url ' . $class : 'filter-url';
	if ( is_product_filter_active( $what ) ) {
		if ( is_product_filter_active( $what, $id ) ) {
			$class .= ' active-filter';
			$id    = 'clear';
		} else {
			$class .= ' not-active-filter';
		}
	}
	$attr = '';
	if ( is_ic_ajax() && ! empty( $_GET['page'] ) ) {
		$attr = 'data-page="' . intval( $_GET['page'] ) . '"';
	}
	$final_url = ic_filter_url( $what, $id );

	return apply_filters( 'ic_category_filter_element', '<a class="' . $class . '" href="' . esc_url( $final_url ) . '" ' . $attr . '>' . $label . '</a>', $label, $category_id );
}

function get_product_category_filter_element( $category, $posts = null, $show_count = true, $check_count = true ) {
	if ( empty( $category->term_id ) ) {
		return '';
	}
	if ( false && is_ic_product_listing() && ! empty( $category->count ) && ! is_product_filters_active() && ! apply_filters( 'ic_force_query_count_calculation', false ) ) {
		$count = $category->count;
	} else if ( $check_count ) {
		$count = total_product_category_count( $category->term_id, $category->taxonomy, $posts );
	} else {
		$count      = 1;
		$show_count = false;
	}
	if ( empty( $count ) && ! $check_count ) {
		$show_count = false;
	}
	if ( $count > 0 || ! $check_count ) {
		$name = $category->name;
		if ( $show_count ) {
			$name .= ' <span class="ic-catalog-category-count">(' . $count . ')</span>';
		}

		return product_filter_element( $category->term_id, 'product_category', $name );
	}
}

add_action( 'wp_loaded', 'set_product_filter' );

/*
 * Sets up active filters
 *
 */

function set_product_filter() {
	if ( is_ic_admin() || ( is_ic_ajax() && ! is_ic_front_ajax() ) ) {

		return;
	}
	$session = get_product_catalog_session();
	$save    = false;
	if ( isset( $_GET['product_category'] ) ) {
		$filter_value = apply_filters( 'ic_catalog_save_product_filter_value', intval( $_GET['product_category'] ), $_GET['product_category'] );
		if ( ! empty( $filter_value ) ) {
			if ( ! isset( $session['filters'] ) ) {
				$session['filters'] = array();
			}
			$session['filters']['product_category'] = $filter_value;
			$save                                   = true;
		} else if ( isset( $session['filters']['product_category'] ) ) {
			unset( $session['filters']['product_category'] );
			$save = true;
		}
	} else if ( isset( $session['filters']['product_category'] ) ) {
		unset( $session['filters']['product_category'] );
		$save = true;
	}
	if ( $save ) {
		set_product_catalog_session( $session );
	}
	do_action( 'ic_set_product_filters', $session );
	$session = get_product_catalog_session();
	if ( ! empty( $session['filters'] ) && empty( $session['filters']['filtered-url'] ) ) {
		if ( isset( $_POST['request_url'] ) && is_ic_ajax() ) {
			$session['filters']['filtered-url'] = esc_url( $_POST['request_url'] );
		} else {
			$active_filters                     = get_active_product_filters();
			$session['filters']['filtered-url'] = remove_query_arg( $active_filters, get_pagenum_link( 1, false ) );
		}
		if ( ic_string_contains( $session['filters']['filtered-url'], '&s=' ) ) {
			if ( is_ic_ajax() && empty( $_POST['is_search'] ) ) {
				$session['filters']['filtered-url'] = remove_query_arg( array(
					's',
					'post_type'
				), $session['filters']['filtered-url'] );
			} else {
				$session['filters']['filtered-url'] = add_query_arg( 'reset_filters', 'y', $session['filters']['filtered-url'] );
			}
		}
		if ( is_ic_shortcode_query() ) {
			$session['filters']['filtered-url'] = add_query_arg( 'reset_filters', 'y', $session['filters']['filtered-url'] );
		}
		if ( ic_string_contains( $session['filters']['filtered-url'], '/wp-admin/' ) || ic_string_contains( $session['filters']['filtered-url'], '/wp-json/' ) ) {
			$session['filters']['filtered-url'] = '';
		} else {
			$session['filters']['filtered-url'] = $session['filters']['filtered-url'] . '#product_filters_bar';
		}
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
	if ( is_ic_ajax() ) {
		return;
	}
	$keep_filters              = true;
	$force_remove_filtered_url = false;
	if ( ! is_product_filters_active() ) {
		$keep_filters              = false;
		$force_remove_filtered_url = true;
		/*
		$session = get_product_catalog_session();
		if ( isset( $session['filters'] ) ) {
			unset( $session['filters'] );
			set_product_catalog_session( $session );
		}
		*/
	} else if ( ! is_search() || isset( $_GET['reset_filters'] ) ) {
		$active_filters = get_active_product_filters();
		$keep_filters   = false;
		foreach ( $active_filters as $filter ) {
			if ( isset( $_GET[ $filter ] ) ) {
				$keep_filters = true;
				break;
			}
		}
	}
	if ( ! $keep_filters ) {
		$session = get_product_catalog_session();
		if ( ! isset( $session['permanent-filters'] ) ) {
			$session['permanent-filters'] = array();
		}
		if ( ! empty( $session['filters'] ) ) {
			foreach ( $session['filters'] as $filter_name => $filter_value ) {
				if ( ! in_array( $filter_name, $session['permanent-filters'] ) ) {
					unset( $session['filters'][ $filter_name ] );
				}
			}
		}
		if ( $force_remove_filtered_url || ( count( $session['filters'] ) === 1 && isset( $session['filters']['filtered-url'] ) ) ) {
			unset( $session['filters']['filtered-url'] );
		}
		if ( empty( $session['filters'] ) ) {
			unset( $session['filters'] );
		}
		set_product_catalog_session( $session );
	}
}

/**
 * Defines active product filters
 *
 * @return array
 */
function get_active_product_filters( $values = false, $encode = false ) {
	$active_filters = ic_get_global( 'ic_active_product_filters' );
	if ( $active_filters === false ) {
		$active_filters = apply_filters( 'active_product_filters', array(
			'product_category',
			'min-price',
			'max-price',
			'product_order'
		) );
		ic_save_global( 'ic_active_product_filters', $active_filters, false, false, true );
	}

	if ( $values ) {
		$filters = array();
		foreach ( $active_filters as $filter_name ) {
			$filter_value = get_product_filter_value( $filter_name );
			if ( ! empty( $filter_value ) ) {
				if ( $encode ) {
					$filter_value = ic_urlencode( $filter_value );
				}
				$filters[ $filter_name ] = $filter_value;
			}
		}

		return $filters;
	}

	return $active_filters;
}

function ic_urlencode( $data ) {
	if ( is_array( $data ) ) {
		$data = array_map( 'ic_urlencode', $data );
	} else {
		$data = urlencode( $data );
	}

	return $data;
}

function get_product_filter_value( $filter_name, $encode = false ) {
	$filter_value = '';
	if ( is_product_filter_active( $filter_name ) ) {
		$session = get_product_catalog_session();
		if ( isset( $session['filters'][ $filter_name ] ) ) {
			if ( $encode ) {
				$filter_value = htmlentities( $session['filters'][ $filter_name ] );
			} else {
				$filter_value = $session['filters'][ $filter_name ];
			}
		}
	}

	return apply_filters( 'ic_epc_filter_value', $filter_value, $filter_name );
}

//add_action( 'ic_pre_get_products', 'apply_product_filters', 20 );
//add_action( 'ic_pre_get_products_listing', 'apply_product_filters', 20 );
//add_action( 'ic_pre_get_products_tax', 'apply_product_filters', 20 );
//add_action( 'ic_pre_get_products_search', 'apply_product_filters', 20 );
add_action( 'ic_pre_get_products_only', 'apply_product_filters', 20 );

/**
 * Applies current filters to the query
 *
 * @param object $query
 */
function apply_product_filters( $query ) {
	if ( ! empty( $query->query['pagename'] ) ) {
		return;
	}
	ic_set_pre_filters_query_vars( $query->query );
	global $ic_product_filters_query;
	if ( is_product_filters_active() ) {
		do_action( 'apply_product_filters_active_start', $query );
		if ( is_product_filter_active( 'product_category' ) ) {
			$category_id = get_product_filter_value( 'product_category' );
			$taxonomy    = get_current_screen_tax();
			if ( empty( $query->query['ic_exclude_tax'] ) || ( ! empty( $query->query['ic_exclude_tax'] ) && ! in_array( $taxonomy, $query->query['ic_exclude_tax'] ) ) ) {
				$tax_query = $query->get( 'tax_query' );
				if ( empty( $tax_query ) ) {
					$tax_query = array();
				}
				if ( is_array( $category_id ) ) {
					$category_id = array_values( $category_id );
				}
				$filter_tax_query = array(
					'taxonomy' => $taxonomy,
					'terms'    => $category_id,
					//'field'		 => 'term_id'
				);
				if ( ! in_array( $filter_tax_query, $tax_query ) ) {
					$tax_query[] = $filter_tax_query;
				}
				$query->set( 'tax_query', apply_filters( 'ic_catalog_product_category_filter_query', $tax_query ) );
			}
		}
	}
	do_action( 'apply_product_filters', $query );
	if ( is_product_filters_active() ) {
		$ic_product_filters_query = $query;
	}
}

add_filter( 'ic_filterable_query', 'apply_product_category_filter' );
add_filter( 'shortcode_query', 'apply_product_category_filter' );
add_filter( 'home_product_listing_query', 'apply_product_category_filter' );
add_filter( 'category_count_query', 'apply_product_category_filter', 10, 2 );

/**
 * Applies product category filter to shortcode query
 *
 * @param type $shortcode_query
 *
 * @return type
 */
function apply_product_category_filter( $shortcode_query, $taxonomy = null ) {
	if ( ! empty( $taxonomy ) && ! is_array( $taxonomy ) && ic_string_contains( $taxonomy, 'al_product-cat' ) ) {
		return $shortcode_query;
	}
	ic_set_pre_filters_query_vars( $shortcode_query, true );
	if ( is_product_filter_active( 'product_category' ) ) {
		$category_id = get_product_filter_value( 'product_category' );
		if ( empty( $shortcode_query['tax_query'] ) ) {
			$shortcode_query['tax_query'] = array();
		}
		if ( is_array( $category_id ) ) {
			$category_id = array_values( $category_id );
		}
		$tax_query = apply_filters( 'ic_catalog_product_category_filter_query', array(
			'taxonomy' => get_current_screen_tax(),
			'terms'    => $category_id,
		) );
		if ( ! in_array( $tax_query, $shortcode_query['tax_query'] ) ) {
			$shortcode_query['tax_query'][] = $tax_query;
		}
	}

	return apply_filters( 'apply_shortcode_product_filters', $shortcode_query, $taxonomy );
}

add_filter( 'product_search_button_text', 'modify_search_widget_filter' );

/**
 * Deletes search button text in filter bar
 *
 * @param string $text
 *
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
	if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ic-ajax-nonce' ) ) {
		update_option( 'hide_empty_bar_message', 1, false );
	}
	wp_die();
}

/**
 * Returns the URL to redirect after filters reset
 *
 * @return type
 */
function get_filters_bar_reset_url() {
	if ( ! is_product_filters_active() ) {

		return '';
	}
	$session = get_product_catalog_session();
	if ( ! empty( $session['filters']['filtered-url'] ) ) {
		return $session['filters']['filtered-url'];
	}

	return '';
}

add_action( 'ic_catalog_wp', 'ic_set_catalog_query', 9 );
add_action( 'before_ajax_product_list', 'ic_set_catalog_query', 9 );

function ic_set_catalog_query() {
	$catalog_query = ic_get_catalog_query();
	if ( ! $catalog_query ) {
		if ( is_home_archive() || ( ! more_products() && is_custom_product_listing_page() ) ) {
			$catalog_query = ic_set_home_listing_query();
			if ( ! empty( $catalog_query ) && ! empty( $catalog_query->query_vars ) ) {
				ic_save_global( 'catalog_query', $catalog_query );
			}
		} else {
			/*
			$catalog_query = apply_filters( 'ic_catalog_query', '', $GLOBALS['wp_query']->query );
			if ( empty( $catalog_query ) ) {
				$catalog_query = $GLOBALS['wp_query'];
			}
			*/
			if ( ! empty( $GLOBALS['wp_query'] ) && ! empty( $GLOBALS['wp_query']->query_vars ) ) {
				ic_save_global( 'catalog_query', $GLOBALS['wp_query'] );
			}
		}
		ic_get_product_id();
		ic_get_current_category_id();
	}
}

function ic_get_catalog_query( $default = false ) {
	$catalog_query = ic_get_global( 'catalog_query' );
	if ( $default && empty( $catalog_query ) ) {
		global $wp_query;
		$catalog_query = $wp_query;
	}

	return $catalog_query;
}

function ic_set_pre_filters_query_vars( $query_vars = null, $force_save = false ) {
	if ( ! ic_get_global( 'catalog_pre_filters_query' ) || $force_save ) {
		if ( empty( $query_vars ) ) {
			global $wp_query;
			$query_vars = $wp_query->query;
		}
		ic_save_global( 'catalog_pre_filters_query_vars', $query_vars );
	}
}

function ic_get_catalog_query_vars( $default = false, $pre_filters = false ) {
	if ( $pre_filters ) {
		$catalog_query_vars = ic_get_global( 'catalog_pre_filters_query_vars' );
	}
	if ( empty( $catalog_query_vars ) ) {
		$catalog_query = ic_get_catalog_query( $default );
		if ( ! empty( $catalog_query->query ) ) {
			$catalog_query_vars = $catalog_query->query;
		}
	}
	if ( empty( $catalog_query_vars ) ) {
		$catalog_query_vars = array();
	}

	return $catalog_query_vars;
}

/**
 * @return array of taxonomies as keys and filter names as values
 */
function ic_filter_taxonomies( $only_tax = false ) {
	$filter_taxonomies = apply_filters( 'ic_filter_taxonomies', array( 'al_product-cat' => 'product_category' ) );
	if ( $only_tax ) {
		return array_keys( $filter_taxonomies );
	}

	return $filter_taxonomies;
}

function ic_get_active_filters_html() {
	$inside = '';
	if ( is_product_filters_active() ) {
		$active_filters = get_active_product_filters( true );
		foreach ( $active_filters as $filter_name => $filter_value ) {
			if ( $filter_value === 'all' ) {
				continue;
			}
			$inside .= ic_get_active_filter_html( $filter_name, $filter_value );
		}
	}
	$out = '<div class="ic-active-filters ic_ajax" data-ic_responsive_label="' . __( 'Active Filters', 'ecommerce-product-catalog' ) . '" data-ic_ajax="ic-active-filters">';
	$out .= $inside;
	$out .= '</div>';


	return $out;
}

function ic_get_active_filter_html( $filter_name, $filter_value = null, $new_value = false ) {
	if ( is_array( $filter_value ) && empty( $new_value ) && ! ic_string_contains( $filter_name, '_size_' ) ) {
		$out = '';
		foreach ( $filter_value as $key => $val ) {
			if ( is_array( $val ) ) {
				continue;
			}
			if ( $filter_name === '' ) {
				$new_filter_value = array();
			} else {
				$new_filter_value = $filter_value;
				unset( $new_filter_value[ $key ] );
			}
			$out .= ic_get_active_filter_html( $filter_name, $val, $new_filter_value );
		}
		if ( ! empty( $out ) ) {
			return $out;
		}
	}
	if ( ! is_product_filter_active( $filter_name, $filter_value ) && apply_filters( 'ic_active_filter_html_check_active', true, $filter_name, $filter_value ) ) {
		return '';
	}
	$out = apply_filters( 'ic_get_active_filter_html', '', $filter_name, $new_value );
	if ( empty( $out ) ) {
		$out                   = '<div class="ic-active-filter"><span class="ic-active-filter-name">' . __( ic_filter_friendly_name( $filter_name ), 'ecommerce-product-catalog' ) . '</span>';
		$friendly_filter_value = ic_filter_friendly_value( $filter_value, $filter_name );
		if ( ! empty( $friendly_filter_value ) && ! is_numeric( $friendly_filter_value ) ) {
			if ( $friendly_filter_value === 'Ic_zero_count' ) {
				$out = str_replace( 'class="ic-active-filter"', 'class="ic-active-filter ic-active-filter-zero-count"', $out );
			} else if ( ic_string_contains( $friendly_filter_value, '(0)' ) ) {
				$out = str_replace( 'class="ic-active-filter"', 'class="ic-active-filter ic-active-filter-zero-count-visible"', $out );
			}
			$out .= '<span class="ic-active-filter-value">: ' . $friendly_filter_value . '</span>';
		}
		if ( ! empty( $new_value ) && is_array( $new_value ) ) {
			foreach ( $new_value as $new_value_key => $new_value_val ) {
				$friendly_value = ic_filter_friendly_value( $new_value_val, $filter_name );
				if ( $friendly_value === 'Ic_zero_count' || ic_string_contains( $friendly_value, '(0)' ) ) {
					unset( $new_value[ $new_value_key ] );
				}
			}
		}
		$out .= '<a class="ic-remove-active-filter" href="' . ic_filter_url( $filter_name, $new_value ) . '"><span class="dashicons dashicons-no-alt"></span></a>';
		$out .= '</div>';
	}

	return $out;
}

function ic_filter_friendly_name( $filter_name ) {
	$friendly_name = str_replace( array(
		'_',
		'-',
		'product'
	), ' ', apply_filters( 'ic_filter_friendly_name', $filter_name ) );

	return trim( ucwords( $friendly_name ) );
}

function ic_filter_friendly_value( $filter_value, $filter_name = false ) {
	if ( is_numeric( $filter_value ) ) {
		$friendly_value = ic_filter_numeric_friendly_value( $filter_value );
	} else if ( is_array( $filter_value ) ) {
		$friendly_value = array();
		foreach ( $filter_value as $val ) {
			if ( is_numeric( $val ) ) {
				$friendly_value[] = ic_filter_numeric_friendly_value( $val );
			}
		}
	} else if ( $filter_name ) {
		$friendly_value = apply_filters( 'ic_filter_friendly_value', array(), $filter_value, $filter_name );
	}
	if ( ! empty( $friendly_value ) && is_array( $friendly_value ) ) {
		$friendly_value = array_filter( $friendly_value );
	}
	if ( ! empty( $friendly_value ) ) {
		$friendly_value = is_array( $friendly_value ) ? implode( ', ', $friendly_value ) : $friendly_value;
	} else {
		$friendly_value = is_array( $filter_value ) ? implode( ', ', $filter_value ) : $filter_value;
	}

	return ucwords( $friendly_value );
}

function ic_filter_numeric_friendly_value( $filter_value, $check_if_empty = false ) {
	$friendly_value = '';
	if ( is_numeric( $filter_value ) ) {
		$term = get_term( $filter_value );
		if ( empty( $term ) || is_wp_error( $term ) || empty( $term->name ) || ! ic_string_contains( $term->taxonomy, 'al_product' ) ) {
			return '';
		}
		$count = total_product_category_count( $term->term_id, $term->taxonomy );
		if ( $check_if_empty && empty( $count ) ) {
			$friendly_value = 'Ic_zero_count';
		} else {
			$friendly_value = $term->name . ' (' . $count . ')';
		}
	}

	return $friendly_value;
}

function ic_filter_url( $filter_name, $filter_value = false ) {
	$original_filter_name = $filter_name;
	$filter_name          = apply_filters( 'ic_filter_url_filter_name', $filter_name );
	if ( ! is_ic_catalog_page() && ! ic_get_global( 'inside_show_catalog_shortcode' ) ) {
		$url = product_listing_url();
	} else if ( is_paged() ) {
		if ( is_ic_permalink_product_catalog() ) {
			$url = remove_query_arg( $filter_name );
		} else {
			$url = remove_query_arg( array( 'paged', $filter_name ) );
		}

	} else {
		if ( is_ic_ajax() ) {
			$url = add_query_arg( $_GET );
			$url = remove_query_arg( array( $filter_name ), $url );
		} else {
			$url = remove_query_arg( array( $filter_name ) );
		}
	}

	$active_filters = get_active_product_filters( true );
	unset( $active_filters[ $filter_name ] );
	$active_filters = apply_filters( 'ic_filter_url_active_filters', $active_filters, $original_filter_name, $filter_value );
	if ( ! empty( $active_filters ) && is_array( $active_filters ) ) {
		foreach ( $active_filters as $active_filter_name => $active_filter_value ) {
			if ( is_array( $active_filter_value ) ) {
				foreach ( $active_filter_value as $active_filter_value_key => $active_filter_value_val ) {
					$friendly_value = ic_filter_friendly_value( $active_filter_value_val, $active_filter_name );
					if ( $friendly_value === 'Ic_zero_count' || ic_string_contains( $friendly_value, '(0)' ) || ! is_product_filter_active( $active_filter_name, $active_filter_value_val ) ) {
						if ( ! is_array( $active_filters[ $active_filter_name ][ $active_filter_value_key ] ) ) {
							unset( $active_filters[ $active_filter_name ][ $active_filter_value_key ] );
						}
					}
				}
			} else {
				$friendly_value = ic_filter_friendly_value( $active_filter_value, $active_filter_name );
				if ( $friendly_value === 'Ic_zero_count' || ic_string_contains( $friendly_value, '(0)' ) || ! is_product_filter_active( $active_filter_name, $active_filter_value ) ) {
					unset( $active_filters[ $active_filter_name ] );
				}
			}

		}
	}
	if ( $filter_value === false ) {
		$filter_value = 'all';
	}
	if ( $filter_value ) {
		$final_url = add_query_arg( array_merge( array( $filter_name => ic_urlencode( $filter_value ) ), ic_urlencode( $active_filters ) ), $url );
	} else {
		$final_url = add_query_arg( ic_urlencode( $active_filters ), $url );
	}
	if ( ! empty( $_GET['s'] ) && ! empty( $_GET['post_type'] ) ) {
		$final_url = add_query_arg(
			array(
				's'         => ic_urlencode( sanitize_text_field( $_GET['s'] ) ),
				'post_type' => ic_urlencode( sanitize_text_field( $_GET['post_type'] ) )
			), $final_url );
	}

	return $final_url;
}
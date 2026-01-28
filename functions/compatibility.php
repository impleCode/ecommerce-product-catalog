<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Defines compatibility functions with previous versions
 *
 * Created by impleCode.
 * Date: 10-Mar-15
 * Time: 12:49
 * Package: compatibility.php
 */
function product_adder_theme_check_notice() {
	// Necessary for extensions before v2.7.4 to work
}

/*
add_action( 'init', 'ic_start_compatibility' );

function ic_start_compatibility() {
	add_filter( 'get_product_short_description', 'compatibility_product_short_description', 10, 2 );
	add_filter( 'get_product_description', 'compatibility_product_description', 10, 2 );
}

function compatibility_product_short_description( $product_desc, $product_id ) {
	if ( empty( $product_desc ) ) {
		$old_desc = get_post_meta( $product_id, '_shortdesc', true );
		if ( ! empty( $old_desc ) ) {
			if ( current_user_can( 'edit_products' ) ) {
				update_post_meta( $product_id, 'excerpt', $old_desc );
				delete_post_meta( $product_id, '_shortdesc' );
			}

			return $old_desc;
		} else {
			$excerpt = get_post_meta( $product_id, 'excerpt', true );

			return $excerpt;
		}
	}

	return $product_desc;
}

function compatibility_product_description( $product_desc, $product_id ) {
	if ( empty( $product_desc ) ) {
		$old_desc = get_post_meta( $product_id, '_desc', true );
		if ( ! empty( $old_desc ) ) {
			if ( current_user_can( 'edit_products' ) ) {
				update_post_meta( $product_id, 'content', $old_desc );
				delete_post_meta( $product_id, '_desc' );
			}

			return $old_desc;
		} else {
			$content = get_post_meta( $product_id, 'content', true );

			return $content;
		}
	}

	return $product_desc;
}
*/

add_action( 'before_product_page', 'set_product_page_image_html' );

/**
 * Sets product page image html if was modified by third party
 */
function set_product_page_image_html() {
	if ( has_filter( 'post_thumbnail_html' ) ) {
		add_filter( 'post_thumbnail_html', 'get_default_product_page_image_html', 1 );
		add_filter( 'post_thumbnail_html', 'product_page_image_html', 99 );
	}
}

/**
 * Inserts default thumbnail html to global
 *
 * @param type $html
 *
 * @return type
 * @global type $product_page_image_html
 */
function get_default_product_page_image_html( $html ) {
	global $product_page_image_html;
	$product_page_image_html = $html;

	return $html;
}

/**
 * Replaces the product page image HTML with the default
 *
 * @param type $html
 *
 * @return \type
 * @global type $product_page_image_html
 */
function product_page_image_html( $html ) {
	if ( is_ic_product_page() ) {
		global $product_page_image_html;

		return $product_page_image_html;
	}

	return $html;
}

/**
 * Compatibility with PHP <5.3 for ic_lcfirst
 *
 * @param string $string
 *
 * @return string
 */
function ic_lcfirst( $string ) {
	if ( ic_is_multibyte( $string ) ) {
		$firstChar = mb_substr( $string, 0, 1 );
		$then      = mb_substr( $string, 1, null );

		return mb_strtolower( $firstChar ) . $then;
	} else if ( function_exists( 'lcfirst' ) ) {
		return lcfirst( $string );
	} else {
		$string['0'] = strtolower( $string['0'] );

		return $string;
	}
}

if ( ! function_exists( 'ic_ucfirst' ) ) {
	/**
	 * Compatibility with PHP <5.3 for ic_ucfirst
	 *
	 * @param type $string
	 *
	 * @return type
	 */
	function ic_ucfirst( $string ) {
		if ( ic_is_multibyte( $string ) ) {
			$firstChar = mb_substr( $string, 0, 1 );
			$then      = mb_substr( $string, 1, null );

			return mb_strtoupper( $firstChar ) . $then;
		} else if ( function_exists( 'ucfirst' ) ) {
			return ucfirst( $string );
		} else {
			$string['0'] = strtoupper( $string['0'] );

			return $string;
		}
	}
}


function ic_ucwords( $string ) {
	if ( ic_is_multibyte( $string ) ) {

		return mb_convert_case( $string, MB_CASE_TITLE );
	} else if ( function_exists( 'ucwords' ) ) {
		return ucwords( $string );
	} else {
		$string['0'] = strtoupper( $string['0'] );

		return $string;
	}
}

if ( ! function_exists( 'ic_is_multibyte' ) ) {
	function ic_is_multibyte( $string ) {
		if ( function_exists( 'mb_check_encoding' ) ) {
			return ! mb_check_encoding( $string, 'ASCII' ) && mb_check_encoding( $string, 'UTF-8' );
		}

		return false;
	}
}


/**
 * Check if any post type has the same rewrite parameter
 *
 * @return boolean
 */
function ic_check_rewrite_compatibility() {
	$post_types = get_post_types( array( 'publicly_queryable' => true ), 'object' );
	if ( empty( $post_types['al_product'] ) ) {
		return true;
	}
	$slug = $post_types['al_product']->rewrite['slug'];
	foreach ( $post_types as $post_type => $type ) {
		if ( $post_type != 'al_product' && isset( $type->rewrite['slug'] ) ) {
			if ( $type->rewrite['slug'] == $slug || $type->rewrite['slug'] == '/' . $slug ) {
				return false;
			}
		}
	}

	return true;
}

/**
 * Check if any post type has the same rewrite parameter
 *
 * @return boolean
 */
function ic_check_tax_rewrite_compatibility() {
	$taxonomies = get_taxonomies( array( 'public' => true ), 'object' );
	if ( isset( $taxonomies['al_product-cat'] ) ) {
		$slug = $taxonomies['al_product-cat']->rewrite['slug'];
		foreach ( $taxonomies as $taxonomy_name => $tax ) {
			if ( $taxonomy_name != 'al_product-cat' && isset( $tax->rewrite['slug'] ) ) {
				if ( $tax->rewrite['slug'] == $slug || $tax->rewrite['slug'] == '/' . $slug ) {
					return false;
				}
			}
		}
	}

	return true;
}

function ic_get_product_image( $product_id, $size = 'full', $attributes = array() ) {
	$image_id = get_post_thumbnail_id( $product_id );
	if ( empty( $image_id ) ) {
		$image_id = ic_default_product_image_id();
	}
	if ( ! empty( $image_id ) ) {
		$image = wp_get_attachment_image( $image_id, $size, false, $attributes );
	} else {
		$image = '<img alt="default-image" src="' . default_product_thumbnail_url() . '" >';
	}

	return $image;
}

add_action( 'ic_pre_get_products_search', 'ic_product_search_fix' );

function ic_product_search_fix( $query ) {
	if ( ! empty( $_GET['post_type'] ) ) {
		$query->query_vars['post_type'] = is_array( $_GET['post_type'] ) ? array_map( 'esc_attr', $_GET['post_type'] ) : esc_attr( $_GET['post_type'] );
	}
}

function ic_get_terms( $def_params = array() ) {
	if ( is_ic_admin() ) {

		return ic_get_terms_simple( $def_params );
	}
	$params = apply_filters( 'ic_get_terms_params', $def_params );
	if ( ! isset( $params['update_term_meta_cache'] ) ) {
		$params['update_term_meta_cache'] = false;
	}
	if ( isset( $params['fields'] ) && ( $params['fields'] === 'names' || $params['fields'] === 'ids' || $params['fields'] === 'id=>name' ) ) {
		$fields           = $params['fields'];
		$params['fields'] = 'all';
	} else {
		$fields = 'all';
	}
	if ( ! empty( $params['taxonomy'] ) && ! is_array( $params['taxonomy'] ) && ! empty( $params['object_ids'] ) ) {
		$filter_taxonomies = ic_filter_taxonomies( true );
		if ( count( $filter_taxonomies ) > 1 && in_array( $params['taxonomy'], $filter_taxonomies ) ) {
			$return_taxonomy = $params['taxonomy'];
			if ( ! empty( $params['number'] ) ) {
				$return_number = $params['number'];
				unset( $params['number'] );
			}
			$params['taxonomy'] = $filter_taxonomies;
			if ( isset( $params['parent'] ) ) {
				$return_parent = $params['parent'];
				unset( $params['parent'] );
			}
			if ( ! empty( $params['orderby'] ) && $params['orderby'] === 'term_id' ) {
				if ( empty( $params['order'] ) || ( ! empty( $params['order'] ) && $params['order'] === 'ASC' ) ) {
					$return_orderby = $params['orderby'];
					unset( $params['orderby'] );
				}
			}
		}
	}
	if ( ! isset( $params['hide_empty'] ) ) {
		$params['hide_empty'] = true;
	}
	if ( ! isset( $params['fields'] ) ) {
		$params['fields'] = 'all';
	}
	if ( ! isset( $params['orderby'] ) ) {
		$params['orderby'] = 'name';
	}
	if ( ! isset( $params['order'] ) ) {
		$params['order'] = 'ASC';
	}
	if ( isset( $params['ic_post_type'] ) && ! empty( $params['object_ids'] ) ) {
		unset( $params['ic_post_type'] );
	}
	$cache_key = 'ic_get_terms_' . md5( serialize( $params ) );
	$terms     = ic_get_global( $cache_key );
	if ( $terms === false ) {
		$terms = ic_get_terms_simple( $params );
		ic_save_global( $cache_key, $terms );
	}
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	if ( ! empty( $return_taxonomy ) || isset( $return_parent ) ) {
		$new_terms = array();
		$num       = 0;
		foreach ( $terms as $term ) {
			if ( ! empty( $return_taxonomy ) && $term->taxonomy !== $return_taxonomy ) {
				continue;
			}
			if ( isset( $return_parent ) && $term->parent !== $return_parent ) {
				continue;
			}
			$new_terms[] = $term;
			$num ++;
			if ( ! empty( $return_number ) && $return_number === $num ) {
				break;
			}
		}
		$terms = $new_terms;
	}
	if ( ! empty( $return_orderby ) ) {
		usort( $terms, "ic_compare_term_ids" );
	}
	if ( $fields !== 'all' ) {
		if ( $fields === 'names' ) {
			$fields = 'name';
		} else if ( $fields === 'ids' ) {
			$fields = 'term_id';
		}
		if ( $fields === 'id=>name' ) {
			$new_terms = array();
			foreach ( $terms as $term ) {
				$new_terms[ $term->term_id ] = $term->name;
			}
			$terms = $new_terms;
		} else {
			$terms = wp_list_pluck( $terms, $fields );
		}
	}

	return $terms;
}

/**
 * Compatibility get_terms before WP 4.5
 *
 * @param $params
 *
 * @return int[]|string|string[]|WP_Error|WP_Term[]
 */
function ic_get_terms_simple( $params ) {
	global $wp_version;
	$terms = array();
	if ( version_compare( $wp_version, 4.5 ) < 0 ) {
		if ( ! empty( $params['taxonomy'] ) ) {
			$terms = get_terms( $params['taxonomy'], $params );
		}
	} else {
		$terms = get_terms( $params );
	}

	return $terms;
}

/**
 * Compare term ids for sorting
 *
 * @param $term_first
 * @param $term_second
 *
 * @return mixed
 */
function ic_compare_term_ids( $term_first, $term_second ) {
	return $term_first->term_id - $term_second->term_id;
}

add_action( 'before_product_page', 'ic_restore_wpautop' );

/**
 * Some themes and plugins remove wpautoop so we readd it for the product pages
 */
function ic_restore_wpautop() {
	if ( ! has_filter( 'the_content', 'wpautop' ) ) {
		add_filter( 'the_content', 'wpautop' );
	}
}

if ( ! function_exists( 'ic_array_key_last' ) ) {

	function ic_array_key_last( $array ) {
		if ( function_exists( "array_key_last" ) ) {
			return array_key_last( $array );
		}
		if ( ! is_array( $array ) || empty( $array ) ) {
			return null;
		}

		return array_keys( $array )[ count( $array ) - 1 ];
	}

}

add_filter( 'esc_html', 'ic_esc_html', 10, 2 );

function ic_esc_html( $safe_text, $text ) {
	if ( ic_string_contains( $text, 'ic-search-keyword' ) ) {
		return $text;
	}

	return $safe_text;
}

function ic_http_get() {
	if ( empty( $_GET ) && isset( $_POST['self_submit_data'] ) ) {
		$params = array();
		parse_str( $_POST['self_submit_data'], $params );
		$_GET = $params;
	}

	return $_GET;
}
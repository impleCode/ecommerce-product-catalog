<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product post type
 *
 * Here all product fields are defined.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
add_action( 'init', 'ic_create_product_categories', 4 );

/**
 * Registers product categories
 *
 */
function ic_create_product_categories() {
	$archive_multiple_settings = get_multiple_settings();
	$category_enable           = true;
	if ( get_integration_type() == 'simple' ) {
		$category_enable = false;
	}
	if ( is_plural_form_active() ) {
		$names             = get_catalog_names();
		$names['singular'] = ic_ucfirst( $names['singular'] );
		$labels            = array(
			'name'              => sprintf( __( '%s Categories', 'ecommerce-product-catalog' ), $names['singular'] ),
			'singular_name'     => sprintf( __( '%s Category', 'ecommerce-product-catalog' ), $names['singular'] ),
			'search_items'      => sprintf( __( 'Search %s Categories', 'ecommerce-product-catalog' ), $names['singular'] ),
			'all_items'         => sprintf( __( 'All %s Categories', 'ecommerce-product-catalog' ), $names['singular'] ),
			'parent_item'       => sprintf( __( 'Parent %s Category', 'ecommerce-product-catalog' ), $names['singular'] ),
			'parent_item_colon' => sprintf( __( 'Parent %s Category:', 'ecommerce-product-catalog' ), $names['singular'] ),
			'edit_item'         => sprintf( __( 'Edit %s Category', 'ecommerce-product-catalog' ), $names['singular'] ),
			'update_item'       => sprintf( __( 'Update %s Category', 'ecommerce-product-catalog' ), $names['singular'] ),
			'add_new_item'      => sprintf( __( 'Add New %s Category', 'ecommerce-product-catalog' ), $names['singular'] ),
			'new_item_name'     => sprintf( __( 'New %s Category', 'ecommerce-product-catalog' ), $names['singular'] ),
			'menu_name'         => sprintf( __( '%s Categories', 'ecommerce-product-catalog' ), $names['singular'] ),
		);
	} else {
		$labels = array(
			'name'              => __( 'Categories', 'ecommerce-product-catalog' ),
			'singular_name'     => __( 'Category', 'ecommerce-product-catalog' ),
			'search_items'      => __( 'Search Categories', 'ecommerce-product-catalog' ),
			'all_items'         => __( 'All Categories', 'ecommerce-product-catalog' ),
			'parent_item'       => __( 'Parent Category', 'ecommerce-product-catalog' ),
			'parent_item_colon' => __( 'Parent Category:', 'ecommerce-product-catalog' ),
			'edit_item'         => __( 'Edit Category', 'ecommerce-product-catalog' ),
			'update_item'       => __( 'Update Category', 'ecommerce-product-catalog' ),
			'add_new_item'      => __( 'Add New Category', 'ecommerce-product-catalog' ),
			'new_item_name'     => __( 'New Category', 'ecommerce-product-catalog' ),
			'menu_name'         => __( 'Categories', 'ecommerce-product-catalog' )
		);
	}

	$args = array(
		'public'            => $category_enable,
		'show_in_rest'      => true,
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array(
			'hierarchical' => true,
			'slug'         => apply_filters( 'product_category_slug_value_register', urldecode( sanitize_title( $archive_multiple_settings['category_archive_url'] ) ) ),
			'with_front'   => false
		),
		'capabilities'      => array(
			'manage_terms' => 'manage_product_categories',
			'edit_terms'   => 'edit_product_categories',
			'delete_terms' => 'delete_product_categories',
			'assign_terms' => 'assign_product_categories'
		)
	);

	register_taxonomy( 'al_product-cat', 'al_product', $args );
	//register_taxonomy_for_object_type( 'al_product-cat', 'al_product' );
}


function ic_update_category_count() {
	$update_taxonomy = 'al_product-cat';
	$get_terms_args  = array(
		'taxonomy'   => $update_taxonomy,
		'fields'     => 'ids',
		'hide_empty' => false,
	);

	$update_terms = ic_get_terms( $get_terms_args );
	wp_update_term_count_now( $update_terms, $update_taxonomy );
}


add_action( 'ic_pre_get_products_tax', 'ic_limit_products_to_current_cat' );

function ic_limit_products_to_current_cat( $query ) {
	$multiple_settings = get_multiple_settings();
	if ( $multiple_settings['category_top_cats'] != 'only_subcategories' ) {
		return;
	}
	if ( ! empty( $query->tax_query->queries ) ) {
		$tax_query = $query->get( 'tax_query' );
		if ( empty( $tax_query ) ) {
			$tax_query = array();
		}
		foreach ( $query->tax_query->queries as $querie ) {
			if ( isset( $querie['include_children'] ) ) {
				$querie['include_children'] = 0;
			}
			$tax_query[] = $querie;
		}
		$query->set( 'tax_query', $tax_query );
	}
}


add_action( 'ic_pre_get_products_listing', 'ic_limit_products_to_loose' );

function ic_limit_products_to_loose( $query ) {
	$multiple_settings = get_multiple_settings();
	if ( $multiple_settings['product_listing_cats'] == 'cats_only' && ic_is_main_query( $query ) ) {
		$tax_query = $query->get( 'tax_query' );
		if ( empty( $tax_query ) ) {
			$tax_query = array();
		}
		$tax_query[] = ic_get_limit_loose_products_args();
		$query->set( 'tax_query', $tax_query );
	}
}


add_filter( 'home_product_listing_query', 'ic_limit_products_to_loose_home' );

function ic_limit_products_to_loose_home( $query ) {
	$multiple_settings = get_multiple_settings();
	if ( $multiple_settings['product_listing_cats'] == 'cats_only' ) {
		if ( empty( $query['tax_query'] ) ) {
			$query['tax_query'] = array();
		}
		$query['tax_query'][] = ic_get_limit_loose_products_args();
	}

	return $query;
}

function ic_get_limit_loose_products_args() {
	//return array( 'taxonomy' => get_current_screen_tax(), 'field' => 'id', 'terms' => ' ', 'operator' => 'NOT IN' );
	return array(
		'taxonomy' => get_current_screen_tax(),
		'field'    => 'term_id',
		'operator' => 'NOT EXISTS',
		'terms'    => array( '' )
	);
}

add_filter( 'wp_terms_checklist_args', 'ic_disable_checked_on_top' );
function ic_disable_checked_on_top( $args ) {
	if ( ! empty( $args['taxonomy'] ) && 'al_product-cat' === $args['taxonomy'] ) {
		// Necessary to maintain the categories hierarchy in the metabox on the product edit screen
		$args['checked_ontop'] = false;
	}

	return $args;
}


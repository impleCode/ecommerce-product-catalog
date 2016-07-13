<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product post type
 *
 * Here all product fields are defined.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
add_action( 'init', 'create_product_categories', 9 );

/**
 * Registers product categories
 *
 */
function create_product_categories() {
	$archive_multiple_settings	 = get_multiple_settings();
	$category_enable			 = true;
	if ( get_integration_type() == 'simple' ) {
		$category_enable = false;
	}
	if ( is_plural_form_active() ) {
		$names				 = get_catalog_names();
		$names[ 'singular' ] = ic_ucfirst( $names[ 'singular' ] );
		$labels				 = array(
			'name'				 => sprintf( __( '%s Categories', 'ecommerce-product-catalog' ), $names[ 'singular' ] ),
			'singular_name'		 => sprintf( __( '%s Category', 'ecommerce-product-catalog' ), $names[ 'singular' ] ),
			'search_items'		 => sprintf( __( 'Search %s Categories', 'ecommerce-product-catalog' ), $names[ 'singular' ] ),
			'all_items'			 => sprintf( __( 'All %s Categories', 'ecommerce-product-catalog' ), $names[ 'singular' ] ),
			'parent_item'		 => sprintf( __( 'Parent %s Category', 'ecommerce-product-catalog' ), $names[ 'singular' ] ),
			'parent_item_colon'	 => sprintf( __( 'Parent %s Category:', 'ecommerce-product-catalog' ), $names[ 'singular' ] ),
			'edit_item'			 => sprintf( __( 'Edit %s Category', 'ecommerce-product-catalog' ), $names[ 'singular' ] ),
			'update_item'		 => sprintf( __( 'Update %s Category', 'ecommerce-product-catalog' ), $names[ 'singular' ] ),
			'add_new_item'		 => sprintf( __( 'Add New %s Category', 'ecommerce-product-catalog' ), $names[ 'singular' ] ),
			'new_item_name'		 => sprintf( __( 'New %s Category', 'ecommerce-product-catalog' ), $names[ 'singular' ] ),
			'menu_name'			 => sprintf( __( '%s Categories', 'ecommerce-product-catalog' ), $names[ 'singular' ] ),
		);
	} else {
		$labels = array(
			'name'				 => __( 'Categories', 'ecommerce-product-catalog' ),
			'singular_name'		 => __( 'Category', 'ecommerce-product-catalog' ),
			'search_items'		 => __( 'Search Categories', 'ecommerce-product-catalog' ),
			'all_items'			 => __( 'All Categories', 'ecommerce-product-catalog' ),
			'parent_item'		 => __( 'Parent Category', 'ecommerce-product-catalog' ),
			'parent_item_colon'	 => __( 'Parent Category:', 'ecommerce-product-catalog' ),
			'edit_item'			 => __( 'Edit Category', 'ecommerce-product-catalog' ),
			'update_item'		 => __( 'Update Category', 'ecommerce-product-catalog' ),
			'add_new_item'		 => __( 'Add New Category', 'ecommerce-product-catalog' ),
			'new_item_name'		 => __( 'New Category', 'ecommerce-product-catalog' ),
			'menu_name'			 => __( 'Categories', 'ecommerce-product-catalog' )
		);
	}

	$args = array(
		'public'			 => $category_enable,
		'hierarchical'		 => true,
		'labels'			 => $labels,
		'show_ui'			 => true,
		'show_admin_column'	 => true,
		'query_var'			 => true,
		'rewrite'			 => array( 'hierarchical' => true, 'slug' => apply_filters( 'product_category_slug_value_register', urldecode( sanitize_title( $archive_multiple_settings[ 'category_archive_url' ] ) ) ), 'with_front' => false ),
		'capabilities'		 => array(
			'manage_terms'	 => 'manage_product_categories',
			'edit_terms'	 => 'edit_product_categories',
			'delete_terms'	 => 'delete_product_categories',
			'assign_terms'	 => 'assign_product_categories'
		)
	);

	register_taxonomy( 'al_product-cat', 'al_product', $args );
	register_taxonomy_for_object_type( 'al_product-cat', 'al_product' );
}

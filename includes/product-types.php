<?php
/**
 * Manages product types
 *
 * Here all product types are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
 
// hook into the init action and call create_book_taxonomies when it fires
add_action( 'init', 'create_product_types', 0 );

// create two taxonomies, genres and writers for the post type "book"
function create_product_types() {
	// Add new taxonomy, make it hierarchical (like categories)
	$labels = array(
		'name'                       => __( 'Product Types', 'al-ecommerce-product-catalog' ),
		'singular_name'              => __( 'Product Type', 'al-ecommerce-product-catalog' ),
		'search_items'               => __( 'Search Product Types', 'al-ecommerce-product-catalog' ),
		'popular_items'              => __( 'Popular Product Types', 'al-ecommerce-product-catalog' ),
		'all_items'                  => __( 'All Product Types', 'al-ecommerce-product-catalog' ),
		'parent_item'                => null,
		'parent_item_colon'          => null,
		'edit_item'                  => __( 'Edit Product Type', 'al-ecommerce-product-catalog' ),
		'update_item'                => __( 'Update Product Type', 'al-ecommerce-product-catalog' ),
		'add_new_item'               => __( 'Add New Product Type', 'al-ecommerce-product-catalog' ),
		'new_item_name'              => __( 'New Product Type', 'al-ecommerce-product-catalog' ),
		'separate_items_with_commas' => __( 'Separate product types with commas', 'al-ecommerce-product-catalog' ),
		'add_or_remove_items'        => __( 'Add or remove product types', 'al-ecommerce-product-catalog' ),
		'choose_from_most_used'      => __( 'Choose from the most used product types', 'al-ecommerce-product-catalog' ),
		'not_found'                  => __( 'No product types found.', 'al-ecommerce-product-catalog' ),
		'menu_name'                  => __( 'Product Types', 'al-ecommerce-product-catalog' ),
	);

	$args = array(
		'hierarchical'          => false,
		'labels'                => $labels,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'update_count_callback' => '_update_post_term_count',
		'query_var'             => true,
		'rewrite'               => array( 'slug' => 'type' ),
	);

	register_taxonomy( 'al_product-type', array( 'al_product' ), $args );
}

?>

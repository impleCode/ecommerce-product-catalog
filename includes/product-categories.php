<?php
/**
 * Manages product post type
 *
 * Here all product fields are defined.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
 require_once('category-widget.php');

add_action( 'init', 'create_product_categories');


function create_product_categories() {
$archive_multiple_settings = get_multiple_settings();
$category_enable = true;
if (get_integration_type() == 'simple') {
$category_enable = false;
}
	$labels = array(
		'name'              => __( 'Product Categories', 'al-ecommerce-product-catalog' ),
		'singular_name'     => __( 'Product Category', 'al-ecommerce-product-catalog' ),
		'search_items'      => __( 'Search Product Categories', 'al-ecommerce-product-catalog' ),
		'all_items'         => __( 'All Product Categories', 'al-ecommerce-product-catalog' ),
		'parent_item'       => __( 'Parent Product Category', 'al-ecommerce-product-catalog' ),
		'parent_item_colon' => __( 'Parent Product Category:', 'al-ecommerce-product-catalog' ),
		'edit_item'         => __( 'Edit Product Category', 'al-ecommerce-product-catalog' ),
		'update_item'       => __( 'Update Product Category', 'al-ecommerce-product-catalog' ),
		'add_new_item'      => __( 'Add New Product Category', 'al-ecommerce-product-catalog' ),
		'new_item_name'     => __( 'New Product Category', 'al-ecommerce-product-catalog' ),
		'menu_name'         => __( 'Product Categories', 'al-ecommerce-product-catalog' ),
	);

	$args = array(
		'public' => $category_enable,
		'hierarchical' => true,
		'labels' => $labels,
		'show_ui' => true,
		'show_admin_column' => true,
		'query_var' => true,
		'rewrite' => array( 'slug' => apply_filters ('product_category_slug_value_register', sanitize_title($archive_multiple_settings['category_archive_url'])), 'with_front' => false ),
		'capabilities' => array (
            'manage_terms' => 'manage_product_categories', 
            'edit_terms' => 'edit_product_categories',
            'delete_terms' => 'delete_product_categories',
            'assign_terms' => 'assign_product_categories' 
            )
	);

	register_taxonomy( 'al_product-cat', 'al_product', $args );
	register_taxonomy_for_object_type( 'al_product-cat', 'al_product' );
	// flush_rewrite_rules(false);
	check_permalink_options_update();
}

?>

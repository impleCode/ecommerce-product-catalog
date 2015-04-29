<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages capabilities
 *
 * Here all capabilities are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
function add_product_caps() {
	// gets the author role
	$role = get_role( 'administrator' );

	$role->add_cap( 'publish_products' );
	$role->add_cap( 'edit_products' );
	$role->add_cap( 'edit_others_products' );
	$role->add_cap( 'edit_private_products' );
	$role->add_cap( 'delete_products' );
	$role->add_cap( 'delete_others_products' );
	$role->add_cap( 'read_private_products' );
	$role->add_cap( 'delete_private_products' );
	$role->add_cap( 'delete_published_products' );
	$role->add_cap( 'edit_published_products' );
	$role->add_cap( 'manage_product_categories' );
	$role->add_cap( 'edit_product_categories' );
	$role->add_cap( 'delete_product_categories' );
	$role->add_cap( 'assign_product_categories' );
	$role->add_cap( 'manage_product_settings' );

	$current_user = wp_get_current_user();
	foreach ( $current_user->roles as $current_role ) {
		if ( $current_role != 'administrator' ) {
			$role = get_role( $current_role );
			$role->add_cap( 'publish_products' );
			$role->add_cap( 'edit_products' );
			$role->add_cap( 'edit_others_products' );
			$role->add_cap( 'edit_private_products' );
			$role->add_cap( 'delete_products' );
			$role->add_cap( 'delete_others_products' );
			$role->add_cap( 'read_private_products' );
			$role->add_cap( 'delete_private_products' );
			$role->add_cap( 'delete_published_products' );
			$role->add_cap( 'edit_published_products' );
			$role->add_cap( 'manage_product_categories' );
			$role->add_cap( 'edit_product_categories' );
			$role->add_cap( 'delete_product_categories' );
			$role->add_cap( 'assign_product_categories' );
			$role->add_cap( 'manage_product_settings' );
		}
	}
}

add_filter( 'map_meta_cap', 'products_map_meta_cap', 10, 4 );

function products_map_meta_cap( $caps, $cap, $user_id, $args ) {

	/* If editing, deleting, or reading a product, get the post and post type object. */
	if ( 'edit_product' == $cap || 'delete_product' == $cap || 'read_product' == $cap ) {
		$post		 = get_post( $args[ 0 ] );
		$post_type	 = get_post_type_object( $post->post_type );

		/* Set an empty array for the caps. */
		$caps = array();
	}

	/* If editing a product, assign the required capability. */
	if ( 'edit_product' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[]	 = $post_type->cap->edit_posts;
		else
			$caps[]	 = $post_type->cap->edit_others_posts;
	}

	/* If deleting a product, assign the required capability. */
	elseif ( 'delete_product' == $cap ) {
		if ( $user_id == $post->post_author )
			$caps[]	 = $post_type->cap->delete_posts;
		else
			$caps[]	 = $post_type->cap->delete_others_posts;
	}

	/* If reading a private product, assign the required capability. */
	elseif ( 'read_product' == $cap ) {

		if ( 'private' != $post->post_status )
			$caps[]	 = 'read';
		elseif ( $user_id == $post->post_author )
			$caps[]	 = 'read';
		else
			$caps[]	 = $post_type->cap->read_private_posts;
	}

	/* Return the capabilities required by the user. */
	return $caps;
}

?>
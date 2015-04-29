<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages admin only functions
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
function toolbar_link_to_products_archive_edit( $wp_admin_bar ) {
	$listing_id = get_product_listing_id();
	if ( is_post_type_archive( 'al_product' ) ) {
		$args = array(
			'id'	 => 'edit',
			'title'	 => 'Edit Product Listing',
			'href'	 => admin_url( 'post.php?post=' . $listing_id . '&action=edit' ),
			'meta'	 => array( 'class' => 'edit-products-page' ),
		);
		$wp_admin_bar->add_node( $args );
	}
}

add_action( 'admin_bar_menu', 'toolbar_link_to_products_archive_edit', 999 );

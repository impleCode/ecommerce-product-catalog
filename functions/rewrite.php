<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Defines URL rewrite rules
 *
 * Created by Norbert Dreszer.
 * Package: functions
 */
add_action( 'post_updated', 'ic_rewrite_product_listing_change', 10, 2 );

/**
 * Enables permalink rewrite when editing the product listing page
 *
 * @param type $post_id
 * @param type $post
 * @return type
 */
function ic_rewrite_product_listing_change( $post_id, $post ) {
	if ( isset( $post->post_type ) && $post->post_type == 'page' ) {
		$id = get_product_listing_id();
		if ( $post_id == $id ) {
			permalink_options_update();
		}
	}
	return;
}

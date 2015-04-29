<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Defines compatibility functions with previous versions
 *
 * Created by Norbert Dreszer.
 * Date: 10-Mar-15
 * Time: 12:49
 * Package: compatibility.php
 */
function ic_start_compatibility() {
	$first_version = (string) get_option( 'first_activation_version' );
	if ( version_compare( $first_version, '2.2.0' ) < 0 ) {
		add_filter( 'get_product_short_description', 'compatibility_product_short_description', 10, 2 );
		add_filter( 'get_product_description', 'compatibility_product_description', 10, 2 );
	}
}

add_action( 'init', 'ic_start_compatibility' );

function compatibility_product_short_description( $product_desc, $product_id ) {
	$old_desc = get_post_meta( $product_id, '_shortdesc', true );
	if ( empty( $product_desc ) && !empty( $old_desc ) ) {
		if ( current_user_can( 'edit_products' ) ) {
			update_post_meta( $product_id, 'excerpt', $old_desc );
		}
		return $old_desc;
	}
	return $product_desc;
}

function compatibility_product_description( $product_desc, $product_id ) {
	$old_desc = get_post_meta( $product_id, '_desc', true );
	if ( empty( $product_desc ) && !empty( $old_desc ) ) {
		if ( current_user_can( 'edit_products' ) ) {
			update_post_meta( $product_id, 'content', $old_desc );
		}
		return $old_desc;
	}
	return $product_desc;
}

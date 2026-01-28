<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Defines URL rewrite rules
 *
 * Created by impleCode.
 * Package: functions
 */
class ic_catalog_listing_modified {

	function __construct() {
		add_action( 'post_updated', array( $this, 'rewrite_listing' ), 10, 2 );
		add_action( 'delete_post', array( $this, 'remove_listing' ) );
		add_action( 'trashed_post', array( $this, 'remove_listing' ) );
	}

	/**
	 * Enables permalink rewrite when editing the product listing page
	 *
	 * @param type $post_id
	 * @param type $post
	 * @return type
	 */
	static function rewrite_listing( $post_id, $post = null ) {
		if ( (isset( $post->post_type ) && $post->post_type == 'page') || !isset( $post->post_type ) ) {
			$id = get_product_listing_id();
			if ( $post_id == $id ) {
				permalink_options_update();
			}
		}
		return;
	}

	static function remove_listing( $post_id ) {
		$id = get_product_listing_id();
		if ( $post_id == $id ) {
			delete_option( 'product_archive_page_id' );
			delete_option( 'product_archive' );
			permalink_options_update();
		}
		return;
	}

}

$ic_catalog_listing_modified = new ic_catalog_listing_modified;

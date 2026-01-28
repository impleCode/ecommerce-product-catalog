<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product custom status
 *
 *
 * @version        1.0.0
 * @package        implecode-quote-cart/includes
 * @author        Norbert Dreszer
 */
if ( ! function_exists( 'product_sold_post_status' ) ) {

	add_action( 'init', 'product_sold_post_status' );

	/**
	 * Registers product sold status
	 *
	 */
	function product_sold_post_status() {
		register_post_status( 'sold', array(
			'label'                     => _x( 'Sold', 'ecommerce-product-catalog' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Sold <span class="count">(%s)</span>', 'Sold <span class="count">(%s)</span>' ),
		) );
	}

	add_action( 'post_submitbox_misc_actions', 'ic_append_product_status_list' );

	/**
	 * Adds sold product status to product page publish box status dropdown
	 * @global type $post
	 */
	function ic_append_product_status_list() {
		global $post;
		$selected = '';
		$label    = '';
		if ( in_array( $post->post_type, product_post_type_array() ) ) {
			if ( $post->post_status == 'sold' ) {
				$selected = ' selected=\"selected\"';
				$label    = '<span id=\"post-status-display\"> ' . __( 'Sold', 'ecommerce-product-catalog' ) . '</span>';
			}
			echo '<script>jQuery(document).ready(function($){
			$("select#post_status,.inline-edit-status select[name=\"_status\"]").append("<option value=\"sold\" ' . $selected . '>' . __( 'Sold', 'ecommerce-product-catalog' ) . '</option>");
				$(".misc-pub-section label").append("' . $label . '");});</script>';
		}
	}

	add_action( 'admin_footer-edit.php', 'ic_append_product_archive_status_list' );

	/**
	 * Adds sold product status to bulk edit
	 *
	 * @global type $post
	 */
	function ic_append_product_archive_status_list() {
		global $post;
		if ( isset( $post->post_type ) && in_array( $post->post_type, product_post_type_array() ) ) {
			echo '<script>jQuery(document).ready(function($){
			$("select#post_status,.inline-edit-status select[name=\"_status\"]").append("<option value=\"sold\">' . __( 'Sold', 'ecommerce-product-catalog' ) . '</option>");});</script>';
		}
	}

	add_filter( 'display_post_states', 'ic_display_sold_state' );

	/**
	 * Shows sold state on product list in admin
	 *
	 * @param type $states
	 *
	 * @return type
	 * @global type $post
	 */
	function ic_display_sold_state( $states ) {
		global $post;
		$arg = get_query_var( 'post_status' );
		if ( $arg != 'sold' ) {
			if ( ! empty( $post->post_status ) && $post->post_status == 'sold' ) {
				return array( __( 'Sold', 'ecommerce-product-catalog' ) );
			}
		}

		return $states;
	}

	add_action( 'single_product_begin', 'disable_add_to_cart_button_for_sold' );

	/**
	 * Disables all shopping/buy buttons if sold
	 *
	 * @global type $post
	 */
	function disable_add_to_cart_button_for_sold() {
		global $post;
		if ( ! empty( $post->post_status ) && $post->post_status == 'sold' ) {
			remove_action( 'product_details', 'quote_cart_button', 10, 0 );
			remove_action( 'price_table', 'ic_cart_add_button', 10, 0 );
			remove_action( 'product_details', 'availability_form_top', 15 );
			remove_action( 'single_product_end', 'availability_form_bottom' );
			remove_action( 'single_product_end', 'bottom_quote_button' );
			remove_action( 'product_details', 'quote_button' );
		}
	}

	add_action( 'product_details', 'front_sold_info', 10, 1 );

	/**
	 * Shows sold info on product page
	 *
	 * @param type $post
	 * @param type $single_names
	 */
	function front_sold_info( $post ) {
		if ( ! empty( $post->post_status ) && $post->post_status == 'sold' ) {
			$names = get_catalog_names();
			echo '<p class="item-sold-info">' . sprintf( __( 'This %s is currently not available.', 'ecommerce-product-catalog' ), strtolower( $names['singular'] ) ) . '</p>';
		}
	}

	add_filter( 'ic_visible_product_status', 'ic_add_sold_status_as_visible' );

	function ic_add_sold_status_as_visible( $status ) {
		if ( is_array( $status ) ) {
			$status[] = 'sold';
		}

		return $status;
	}

}
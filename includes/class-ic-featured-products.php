<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *
 *
 *
 *
 *  @version       1.0.0
 *  @package       featured
 *  @author        impleCode

 */

class ic_featured_products {

	function __construct() {
		add_filter( 'admin_product_details', array( $this, 'add_checkbox' ), 3, 2 );
		add_filter( 'product_details_box_visible', array( $this, 'ret_true' ) );
		add_filter( 'product_meta_save', array( $this, 'save' ) );
		add_filter( 'show_products_shortcode_args', array( $this, 'shortcode_arg' ) );
		add_filter( 'shortcode_query', array( $this, 'shortcode_query' ), 10, 2 );
		add_shortcode( 'show_featured_products', array( $this, 'featured_shortcode' ) );
	}

	function shortcode_arg( $args ) {
		$args['featured'] = '';

		return $args;
	}

	function shortcode_query( $query, $args = null ) {
		if ( ! empty( $args['featured'] ) ) {
			$meta_query          = isset( $query['meta_query'] ) ? $query['meta_query'] : array();
			$meta_query[]        = array(
				'key'     => '_featured',
				'compare' => '=',
				'value'   => 1,
				'type'    => 'DECIMAL'
			);
			$query['meta_query'] = $meta_query;
		}

		return $query;
	}

	function featured_shortcode( $atts ) {
		$available_args = apply_filters( 'show_featured_products_shortcode_args', array(
			'products_limit'   => '',
			'archive_template' => '',
			'per_row'          => '',
			'empty'            => '',
			'header'           => __( 'Featured Products', 'ecommerce-product-catalog' )
		) );
		$args           = shortcode_atts( $available_args, $atts );
		$args_string    = 'featured="1"';
		foreach ( $args as $name => $arg ) {
			if ( empty( $arg ) ) {
				continue;
			}
			$args_string .= ' ' . sanitize_title( $name ) . '="' . esc_html( $arg ) . '"';
		}
		$header = '';
		if ( ! empty( $args['header'] ) ) {
			$header = '<h2>' . esc_html( $args['header'] ) . '</h2>';
		}
		$content = do_shortcode( '[show_products ' . $args_string . ']' . $header . '[/show_products]' );

		return '<div class="ic-featured-products-container">' . $content . '</div>';
	}

	function add_checkbox( $product_details, $product_id ) {
		$product_details .= '<table><tbody>';
		$product_details .= '<tr>';
		$product_details .= '<td class="label-column">' . __( 'Featured', 'ecommerce-product-catalog' ) . '</td>';
		$product_details .= '<td class="featured-column">' . $this->checkbox( $product_id ) . '</td>';
		$product_details .= '</tr>';
		$product_details .= '</tbody></table>';

		return $product_details;
	}

	function checkbox( $product_id ) {
		$selected = '';
		if ( $this->is_featured( $product_id ) ) {
			$selected = 'checked';
		}
		$checkbox = '<input ' . $selected . ' type="checkbox" name="_featured" value="1" />';

		return $checkbox;
	}

	function is_featured( $product_id ) {
		$featured = get_post_meta( $product_id, '_featured', true );
		if ( ! empty( $featured ) ) {
			return true;
		}

		return false;
	}

	function save( $product_meta ) {
		$featured                  = isset( $_POST['_featured'] ) && $_POST['_featured'] != null ? intval( $_POST['_featured'] ) : '';
		$product_meta['_featured'] = $featured;

		return $product_meta;
	}

	function ret_true() {
		return true;
	}

}

$ic_featured_products = new ic_featured_products;

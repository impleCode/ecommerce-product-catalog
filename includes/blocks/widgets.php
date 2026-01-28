<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *
 *  @version       1.0.0
 *  @package
 *  @author        impleCode
 *
 */

class ic_epc_widget_blocks {

	/**
	 * @var ic_epc_context_blocks
	 */
	public $context_blocks;

	function __construct() {
		add_action( 'ic_register_blocks', array( $this, 'register' ) );
		add_filter( 'ic_epc_blocks_localize', array( $this, 'localize' ) );
		add_filter( 'widget_block_dynamic_classname', array( $this, 'widget_block_classname' ), 10, 2 );
		add_filter( 'ic_widget_block_content', array( $this, 'default_block_content' ), 10, 3 );
	}

	function default_block_content( $block_content, $block_name, $attributes ) {
		$attributes['title']             = isset( $attributes['title'] ) ? $attributes['title'] : '';
		$attributes['shortcode_support'] = isset( $attributes['shortcode_support'] ) ? $attributes['shortcode_support'] : '';
		ob_start();
		if ( $block_name === 'product-search-widget' ) {
			the_widget( 'product_widget_search', $attributes );
		} else if ( $block_name === 'product-sort-filter' ) {
			the_widget( 'product_sort_filter', $attributes );
		} else if ( $block_name === 'product-category-filter' ) {
			the_widget( 'product_category_filter', $attributes );
		} else if ( $block_name === 'related-products' ) {
			the_widget( 'related_products_widget', $attributes );
		} else if ( $block_name === 'product-size-filter' ) {
			the_widget( 'ic_product_size_filter', $attributes );
		} else if ( $block_name === 'product-category-widget' ) {
			$attributes['dropdown']     = isset( $attributes['dropdown'] ) ? $attributes['dropdown'] : '';
			$attributes['count']        = isset( $attributes['count'] ) ? $attributes['count'] : '';
			$attributes['hierarchical'] = isset( $attributes['hierarchical'] ) ? $attributes['hierarchical'] : '';
			the_widget( 'product_cat_widget', $attributes );
		} else {
			do_action( 'ic_the_widget_block_content', $block_name, $attributes );
		}
		$new_block_content = ob_get_clean();
		if ( ! empty( $new_block_content ) ) {
			$block_content = $new_block_content;
		}

		return $block_content;
	}

	function widget_block_classname( $classname, $block_name ) {
		if ( ic_string_contains( $block_name, 'ic-epc' ) ) {
			$classname .= ' ' . str_replace( 'ic-epc/', '', $block_name );
		}

		return $classname;
	}

	function render( $attributes, $block_content, $block ) {
		$block_name    = explode( '/', $block->name );
		$block_name    = $block_name[1];
		$block_title   = $block->block_type->title;
		$block_content = apply_filters( 'ic_widget_block_content', $block_content, $block_name, $attributes );
		if ( ( empty( $block_content ) || ic_string_contains( $block_content, 'ic-empty-filter' ) ) && ic_is_rendering_block() ) {
			$block_content = '<div style="padding: 20px 10px">' . sprintf( __( '%s will only show up on the front-end if something is available in the context.', 'ecommerce-product-catalog' ), $block_title ) . '</div>';
		}

		return $this->container( $attributes, $block_content, $block_name );
	}

	function container( $attr, $content, $name, $product_id = null ) {
		if ( ! empty( $this->context_blocks ) ) {
			return $this->context_blocks->container( $attr, $content, $name );
		} else {
			return $content;
		}
	}

	function register() {
		$blocks = apply_filters( 'ic_product_widget_blocks', array(
			__DIR__ . '/search/',
			__DIR__ . '/category-links/',
			__DIR__ . '/sort/',
			__DIR__ . '/category-filter/',
			__DIR__ . '/related/'
		) );
		if ( function_exists( 'ic_size_field_names' ) ) {
			$blocks[] = __DIR__ . '/size-filter/';
		}
		foreach ( $blocks as $block_dir ) {
			$args = array(
				'render_callback' => array( $this, 'render' ),
			);
			if ( file_exists( $block_dir . 'block.json' ) ) {
				$args['title'] = ! empty( $block_dir ) ? json_decode( file_get_contents( $block_dir . 'block.json' ), true )['title'] ?? '' : '';
				if ( ! empty( $args['title'] ) ) {
					$args['title'] = __( $args['title'], 'ecommerce-product-catalog' );
				}
			}
			register_block_type( $block_dir, $args );
		}
	}

	function localize( $localize ) {
		if ( is_plural_form_active() ) {
			$names                  = get_catalog_names();
			$search_label           = sprintf( __( '%s Search', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) );
			$sort_label             = sprintf( __( '%s Sort', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) );
			$category_filter_label  = sprintf( __( '%s Category Filter', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) );
			$size_filter_label      = sprintf( __( '%s Size Filter', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) );
			$related_products_label = sprintf( __( 'Related %s', 'ecommerce-product-catalog' ), ic_ucfirst( $names['plural'] ) );
		} else {
			$search_label           = __( 'Product Search', 'ecommerce-product-catalog' );
			$sort_label             = __( 'Catalog Sort', 'ecommerce-product-catalog' );
			$category_filter_label  = __( 'Catalog Category Filter', 'ecommerce-product-catalog' );
			$size_filter_label      = __( 'Catalog Size Filter', 'ecommerce-product-catalog' );
			$related_products_label = __( 'Related Catalog Items', 'ecommerce-product-catalog' );
		}
		$localize['strings']['search_widget']            = $search_label;
		$localize['strings']['sort_widget']              = $sort_label;
		$localize['strings']['category_filter_widget']   = $category_filter_label;
		$localize['strings']['size_filter_widget']       = $size_filter_label;
		$localize['strings']['related_products_widget']  = $related_products_label;
		$localize['strings']['settings']                 = __( 'Settings', 'ecommerce-product-catalog' );
		$localize['strings']['select_title']             = __( 'Title', 'ecommerce-product-catalog' );
		$localize['strings']['category_widget']          = __( 'Category Links', 'ecommerce-product-catalog' );
		$localize['strings']['select_dropdown']          = __( 'Display as dropdown', 'ecommerce-product-catalog' );
		$localize['strings']['select_count']             = __( 'Show product counts', 'ecommerce-product-catalog' );
		$localize['strings']['select_hierarchical']      = __( 'Show hierarchy', 'ecommerce-product-catalog' );
		$localize['strings']['select_shortcode_support'] = __( 'Enable also for shortcodes', 'ecommerce-product-catalog' );

		return $localize;
	}

}
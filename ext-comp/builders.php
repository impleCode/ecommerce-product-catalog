<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages externa compatibility functions folder
 *
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/ext-comp
 * @author        impleCode
 */
class ic_catalog_builders_compat {

	function __construct() {
		add_action( 'ic_catalog_wp', array( $this, 'wp' ) );
		add_filter( 'et_builder_post_types', array( $this, 'divi_builder_enable' ) );
		add_filter( 'ic_shortcode_catalog_apply', array( $this, 'disable_shortcode_catalog' ) );
		if ( defined( 'CT_VERSION' ) ) {
			add_action( 'ic_shortcode_catalog_hooks_added', array( $this, 'oxygen' ) );
		}
	}

	function divi_builder_enable( $post_types ) {
		$post_types[] = 'al_product';

		return $post_types;
	}

	function wp() {
		remove_action( 'wp_enqueue_scripts', 'et_divi_replace_stylesheet', 99999998 );
	}

	function disable_shortcode_catalog( $disable ) {
		if ( function_exists( 'et_theme_builder_get_template_layouts' ) ) {
			$layouts = et_theme_builder_get_template_layouts();
			if ( ! empty( $layouts['et_body_layout']['enabled'] ) && ! empty( $layouts['et_body_layout']['override'] ) ) {
				$disable = true;
			}
		}

		return $disable;
	}

	function oxygen( $shortcode_catalog ) {
		remove_action( 'ic_catalog_wp_head_start', array( $shortcode_catalog, 'catalog_query_force' ), - 1, 0 );
		remove_action( 'ic_catalog_wp_head_start', array( $shortcode_catalog, 'catalog_query' ), - 1, 0 );
		remove_filter( 'single_post_title', array( $shortcode_catalog, 'product_page_title' ), 99, 1 );
	}

}

$ic_catalog_builders_compat = new ic_catalog_builders_compat;

<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product post type
 *
 * Here all product fields are defined.
 *
 * @version        1.1.1
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
class ic_catalog_hooks {

	function __construct() {
		add_action( 'wp', array( $this, 'wp' ), - 1 );
		add_action( 'template_redirect', array( $this, 'template_redirect' ), 999 );
		add_action( 'wp_head', array( $this, 'wp_head_start' ), - 1 );
		add_action( 'wp_head', array( $this, 'wp_head' ), 999 );
		add_filter( 'body_class', array( $this, 'body_class_start' ), - 1 );
		add_filter( 'body_class', array( $this, 'body_class' ), 999 );

		add_filter( 'wp_nav_menu', array( $this, 'nav_menu' ), 999 );
		add_filter( 'wp_page_menu', array( $this, 'nav_menu' ), 999 );
		add_filter( 'wp_get_nav_menu_items', array( $this, 'nav_menu_items' ), 999 );
		add_filter( 'pre_wp_nav_menu', array( $this, 'pre_nav_menu' ), 999 );
		add_filter( 'wp_page_menu_args', array( $this, 'pre_nav_menu' ), 999 );
		add_filter( 'wp_get_nav_menu_object', array( $this, 'pre_nav_menu_items' ), 999 );
	}

	function filter_hook_template( $name, $return = null ) {
		if ( is_ic_catalog_page() ) {
			$return = apply_filters( 'ic_catalog_' . $name, $return );
			if ( is_ic_product_page() ) {
				$return = apply_filters( 'ic_catalog_single_' . $name, $return );
			} else if ( is_ic_taxonomy_page() ) {
				$return = apply_filters( 'ic_catalog_tax_' . $name, $return );
			} else if ( is_ic_product_listing() ) {
				$return = apply_filters( 'ic_catalog_listing_' . $name, $return );
			}
		}

		return $return;
	}

	function action_hook_template( $name ) {
		if ( is_ic_catalog_page() ) {
			do_action( 'ic_catalog_' . $name );
			if ( is_ic_product_page() ) {
				do_action( 'ic_catalog_single_' . $name );
			} else if ( ic_ic_catalog_archive() ) {
				do_action( 'ic_catalog_archive_' . $name );
				if ( is_ic_taxonomy_page() ) {
					do_action( 'ic_catalog_tax_' . $name );
				} else if ( is_ic_product_search() ) {
					do_action( 'ic_catalog_search_' . $name );
				} else if ( is_ic_product_listing() ) {
					do_action( 'ic_catalog_listing_' . $name );
				}
			}
		}
	}

	function wp() {
		$this->action_hook_template( 'wp' );
	}

	function template_redirect() {
		if ( is_ic_catalog_page() ) {
			do_action( 'ic_catalog_template_redirect' );
		}
	}

	function wp_head_start() {
		$this->action_hook_template( 'wp_head_start' );
	}

	function wp_head() {
		$this->action_hook_template( 'wp_head' );
	}

	function body_class_start( $body_class ) {
		if ( is_ic_catalog_page() ) {
			$body_class = apply_filters( 'ic_catalog_body_class_start', $body_class );
		}

		return $body_class;
	}

	function body_class( $body_class ) {
		return $this->filter_hook_template( 'body_class', $body_class );
	}

	function nav_menu( $nav_menu ) {
		return $this->filter_hook_template( 'nav_menu', $nav_menu );
	}

	function pre_nav_menu( $pre_nav_menu ) {
		return $this->filter_hook_template( 'pre_nav_menu', $pre_nav_menu );
	}

	function nav_menu_items( $nav_menu ) {
		return $this->filter_hook_template( 'nav_menu_items', $nav_menu );
	}

	function pre_nav_menu_items( $pre_nav_menu ) {
		return $this->filter_hook_template( 'pre_nav_menu_items', $pre_nav_menu );
	}

}

$ic_register_product = new ic_catalog_hooks;

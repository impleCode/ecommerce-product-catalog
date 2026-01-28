<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages plugin activation functions
 *
 * Here all plugin ativation functions are defined and managed.
 *
 * @version        1.0.0
 * @package        digital-products-order/functions
 * @author        Norbert Dreszer
 */
class ic_orders_caps {

	function __construct() {
		add_action( 'admin_init', array( $this, 'add_caps' ), 10 );
		add_action( 'admin_init', array( $this, 'add_orders_manager_role' ), 20 );
		add_filter( 'map_meta_cap', array( $this, 'map_meta_cap' ), 10, 4 );
	}

	function caps() {
		return array(
			'publish_digital_orders',
			'edit_digital_orders',
			'edit_others_digital_orders',
			'edit_private_digital_orders',
			'delete_digital_orders',
			'delete_others_digital_orders',
			'read_private_digital_orders',
			'delete_private_digital_orders',
			'delete_published_digital_orders',
			'edit_published_digital_orders'
		);
	}

	function add_caps() {
		$caps = $this->caps();
		if ( current_user_can( 'administrator' ) && ! current_user_can( 'publish_digital_orders' ) ) {
			$role = get_role( 'administrator' );
			foreach ( $caps as $cap ) {
				if ( $role->has_cap( $cap ) ) {
					break;
				}
				$role->add_cap( $cap );
			}
		}
	}

	function add_orders_manager_role() {
		if ( ! current_user_can( 'administrator' ) ) {
			return;
		}
		$manager_role = get_role( 'ic_order_manager' );
		$caps         = $this->caps();
		if ( ! empty( $manager_role ) ) {
			foreach ( $caps as $cap ) {
				if ( $manager_role->has_cap( $cap ) ) {
					break;
				}
				$manager_role->add_cap( $cap );
			}

			return;
		}
		$role = get_role( 'catalog_manager' );
		if ( ! empty( $role ) ) {
			$capabilities = $role->capabilities;
		} else {
			$capabilities = array();
		}
		$manager_role = add_role( 'ic_order_manager', __( 'Orders Manager', 'ecommerce-product-catalog' ), $capabilities );
		if ( is_object( $manager_role ) ) {
			if ( empty( $capabilities ) ) {
				$manager_role->add_cap( 'moderate_comments' );
				//$manager_role->add_cap( 'manage_categories' );
				$manager_role->add_cap( 'manage_links' );
				$manager_role->add_cap( 'upload_files' );
				$manager_role->add_cap( 'unfiltered_html' );
				$manager_role->add_cap( 'edit_posts' );
				//$manager_role->add_cap( 'edit_others_posts' );
				$manager_role->add_cap( 'edit_published_posts' );
				$manager_role->add_cap( 'publish_posts' );
				$manager_role->add_cap( 'edit_pages' );
				$manager_role->add_cap( 'read' );
				$manager_role->add_cap( 'level_7' );
				$manager_role->add_cap( 'level_6' );
				$manager_role->add_cap( 'level_5' );
				$manager_role->add_cap( 'level_4' );
				$manager_role->add_cap( 'level_3' );
				$manager_role->add_cap( 'level_2' );
				$manager_role->add_cap( 'level_1' );
				$manager_role->add_cap( 'level_0' );
			}

			ic_catalog_add_caps( $manager_role );
			foreach ( $caps as $cap ) {
				if ( $manager_role->has_cap( $cap ) ) {
					break;
				}
				$manager_role->add_cap( $cap );
			}
		}
	}

	function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( empty( $args[0] ) ) {
			return $caps;
		}
		if ( 'edit_digital_order' == $cap || 'delete_digital_order' == $cap || 'read_digital_order' == $cap ) {
			$post      = get_post( $args[0] );
			$post_type = get_post_type_object( $post->post_type );
			$caps      = array();
		}
		if ( empty( $post ) || empty( $post_type ) ) {
			return $caps;
		}
		if ( 'edit_digital_order' == $cap ) {
			if ( $user_id == $post->post_author ) {
				$caps[] = $post_type->cap->edit_posts;
			} else {
				$caps[] = $post_type->cap->edit_others_posts;
			}
		} elseif ( 'delete_digital_order' == $cap ) {
			if ( $user_id == $post->post_author ) {
				$caps[] = $post_type->cap->delete_posts;
			} else {
				$caps[] = $post_type->cap->delete_others_posts;
			}
		} elseif ( 'read_digital_order' == $cap ) {
			if ( 'private' != $post->post_status ) {
				$caps[] = 'read';
			} elseif ( $user_id == $post->post_author ) {
				$caps[] = 'read';
			} else {
				$caps[] = $post_type->cap->read_private_posts;
			}
		}

		return $caps;
	}

}

$ic_orders_caps = new ic_orders_caps;

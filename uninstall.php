<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
/**
 * eCommerce Product Catalog Uninstall
 *
 * Uninstalling eCommerce Product Catalog deletes user roles and options.
 *
 * @package     ecommerce-product-catalog/uninstall
 * @version     2.3.7
 */
if ( ! defined( 'AL_BASE_PATH' ) ) {
	$uninstall_products = get_option( 'ic_delete_products_uninstall', 0 );

	if ( $uninstall_products == 1 ) {

		if ( ! function_exists( 'ic_delete_all_attribute_terms' ) ) {

			/**
			 * Delete all product attribute terms
			 *
			 * @global type $wpdb
			 */
			function ic_delete_all_attribute_terms() {
				global $wpdb;
				$taxonomy = 'al_product-attributes';
				$terms    = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

				// Delete Terms
				if ( $terms ) {
					foreach ( $terms as $term ) {
						$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
						$wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
						$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
					}
				}
			}

		}

		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'al_product' );" );
		$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );

		$taxonomy = 'al_product-cat';
		$terms    = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

		// Delete Terms
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
				$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
				delete_option( 'al_product_cat_image_' . $term->term_id );
			}
		}

		// Delete Taxonomy
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );

		ic_delete_all_attribute_terms();
		$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => 'al_product-attributes' ), array( '%s' ) );

		remove_role( 'catalog_manager' );
		remove_role( 'ic_order_manager' );
	}


	if ( ! function_exists( 'all_ic_options' ) ) {

		/**
		 * Returns all eCommerce Product Catalog option names
		 * (needs optimisation)
		 * @return type
		 */
		function all_ic_options( $which = 'all' ) {
			$options = array(
				'product_adder_theme_support_check',
				'product_attributes_number',
				'al_display_attributes',
				'product_attribute',
				'product_attribute_label',
				'product_attribute_unit',
				'archive_template',
				'modern_grid_settings',
				'classic_grid_settings',
				'catalog_lightbox',
				'catalog_magnifier',
				'multi_single_options',
				'default_product_thumbnail',
				'design_schemes',
				'archive_names',
				'single_names',
				'product_listing_url',
				'product_currency',
				'product_currency_settings',
				'product_archive',
				'enable_product_listing',
				'archive_multiple_settings',
				'product_shipping_options_number',
				'display_shipping',
				'product_shipping_cost',
				'product_shipping_label',
				'product_archive_page_id'
			);
			$tools   = array(
				'ic_epc_tracking_last_send',
				'ic_epc_tracking_notice',
				'ic_epc_allow_tracking',
				'ic_delete_products_uninstall',
				'ecommerce_product_catalog_ver',
				'sample_product_id',
				'al_permalink_options_update',
				'custom_license_code',
				'implecode_license_owner',
				'no_implecode_license_error',
				'license_active_plugins',
				'product_adder_theme_support_check',
				'implecode_hide_plugin_review_info_count',
				'hide_empty_bar_message',
				'ic_hidden_boxes',
				'ic_hidden_notices',
				'old_sort_bar',
				'first_activation_version',
				'ic_allow_woo_template_file',
				'ic_block_woo_template_file'
			);
			if ( $which == 'all' ) {
				return array_merge( $options, $tools );
			} else if ( $which == 'tools' ) {
				return $tools;
			} else {
				return $options;
			}
		}

	}

	$all_options = all_ic_options();
	foreach ( $all_options as $option ) {
		delete_option( $option );
	}

	delete_user_meta( get_current_user_id(), 'ic_review_hidden' );
	global $wpdb;
	$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => '_ic_hidden_notices' ) );

}
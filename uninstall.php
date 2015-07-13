<?php

if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
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
$uninstall_products = get_option( 'ic_delete_products_uninstall', 0 );

if ( $uninstall_products == 1 ) {
	global $wpdb;
	$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type IN ( 'al_product' );" );
	$wpdb->query( "DELETE meta FROM {$wpdb->postmeta} meta LEFT JOIN {$wpdb->posts} posts ON posts.ID = meta.post_id WHERE posts.ID IS NULL;" );

	$taxonomy	 = 'al_product-cat';
	$terms		 = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

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
}

foreach ( all_ic_options() as $option ) {
	delete_option( $option );
}
/*
delete_option( 'product_attributes_number' );
delete_option( 'al_display_attributes' );
delete_option( 'product_attribute' );
delete_option( 'product_attribute_label' );
delete_option( 'product_attribute_unit' );
delete_option( 'archive_template' );
delete_option( 'modern_grid_settings' );
delete_option( 'classic_grid_settings' );
delete_option( 'catalog_lightbox' );
delete_option( 'multi_single_options' );
delete_option( 'default_product_thumbnail' );
delete_option( 'design_schemes' );
delete_option( 'archive_names' );
delete_option( 'single_names' );
delete_option( 'product_listing_url' );
delete_option( 'product_currency' );
delete_option( 'product_currency_settings' );
delete_option( 'product_archive' );
delete_option( 'enable_product_listing' );
delete_option( 'archive_multiple_settings' );
delete_option( 'product_shipping_options_number' );
delete_option( 'display_shipping' );
delete_option( 'product_shipping_cost' );
delete_option( 'product_shipping_label' );

delete_option( 'ic_delete_products_uninstall' );
delete_option( 'ecommerce_product_catalog_ver' );
delete_option( 'sample_product_id' );
delete_option( 'al_permalink_options_update' );
delete_option( 'custom_license_code' );
delete_option( 'implecode_license_owner' );
delete_option( 'no_implecode_license_error' );
delete_option( 'license_active_plugins' );
delete_option( 'product_adder_theme_support_check' );
delete_option( 'implecode_hide_plugin_review_info_count' );
 */




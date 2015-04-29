<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages functions necessary on plugin activation.
 *
 *
 * @version		1.1.3
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
function epc_activation_function() {
	create_products_page();
	create_sample_product();
	implecode_plugin_review_notice_hide();
	permalink_options_update();
}

function create_products_page() {
	$product_page = array(
		'post_title'	 => __( 'Products', 'al-ecommerce-product-catalog' ),
		'post_type'		 => 'page',
		'post_content'	 => '',
		'post_status'	 => 'publish',
		'comment_status' => 'closed'
	);

	$plugin_data	 = get_plugin_data( AL_PLUGIN_MAIN_FILE );
	$plugin_version	 = $plugin_data[ "Version" ];
	$first_version	 = get_option( 'first_activation_version', '1.0' );

	if ( $first_version == '1.0' ) {
		add_option( 'first_activation_version', $plugin_version );
		add_option( 'ecommerce_product_catalog_ver', $plugin_version );
		$post_id = wp_insert_post( $product_page );
		add_option( 'product_archive_page_id', $post_id );
	}
}

function create_sample_product() {
	if ( !is_advanced_mode_forced() ) {
		$product_sample							 = array(
			'post_title'	 => __( 'Sample Product Page', 'al-ecommerce-product-catalog' ),
			'post_type'		 => 'al_product',
			'post_content'	 => '',
			'post_status'	 => 'publish',
			'comment_status' => 'closed'
		);
		$product_id								 = wp_insert_post( $product_sample );
		$product_field[ '_price' ]				 = 30;
		$product_field[ '_sku' ]				 = 'INT102';
		$product_field[ '_attribute-label1' ]	 = __( 'Color', 'al-ecommerce-product-catalog' );
		$product_field[ '_attribute-label2' ]	 = __( 'Size', 'al-ecommerce-product-catalog' );
		$product_field[ '_attribute-label3' ]	 = __( 'Weight', 'al-ecommerce-product-catalog' );
		$product_field[ '_attribute1' ]			 = __( 'White', 'al-ecommerce-product-catalog' );
		$product_field[ '_attribute2' ]			 = __( 'Big', 'al-ecommerce-product-catalog' );
		$product_field[ '_attribute3' ]			 = 130;
		$product_field[ '_attribute-unit1' ]	 = '';
		$product_field[ '_attribute-unit2' ]	 = '';
		$product_field[ '_attribute-unit3' ]	 = __( 'lbs', 'al-ecommerce-product-catalog' );
		$product_field[ '_shipping-label1' ]	 = 'UPS';
		$product_field[ '_shipping1' ]			 = 15;
		$product_field[ 'excerpt' ]				 = '[theme_integration class="fixed-box"]';
		$product_field[ 'excerpt' ] .= '<p>' . __( 'Welcome on product test page. This is short description. It should show up on the left of the product image and below product name. You shouldn\'t see nothing between product name and short description. No author, time or date. Absolutely nothing. If there is something that you don\'t want to see than you probably need Advanced Integration Mode.', 'al-ecommerce-product-catalog' ) . '</p>';
		$product_field[ 'excerpt' ] .= '<p><strong>' . __( 'Please read this page carefully to fully understand the difference between simple and advanced mode and how the product page look like.', 'al-ecommerce-product-catalog' ) . '</strong></p>';

		$long_desc					 = '<p>' . __( 'This section is product long description. It should appear under the attributes table. Between the short description and the attributes table you should see the price, SKU and shipping options (all can be disabled). The attributes also can be disabled.', 'al-ecommerce-product-catalog' ) . '</p>';
		$long_desc .= '<h2>' . __( 'Advanced Theme Integration Mode', 'al-ecommerce-product-catalog' ) . '</h2>';
		$long_desc .= '<p>' . sprintf( __( 'With Advanced Mode you will be able to use eCommerce Product Catalog in %s. The product listing page, category pages, product search and category widget will be enabled in advanced mode. You can enable the Advanced Mode %s free. To see how please see <a target="_blank" href="%s">Theme Integration Guide</a>', 'al-ecommerce-product-catalog' ), '100%', '100%', 'http://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=sample-product-page&key=integration-mode-test' ) . '</p>';
		$long_desc .= '<p>' . __( 'The Advanced Mode works out of the box on all default WordPress themes and all themes with the integration done properly.', 'al-ecommerce-product-catalog' ) . '</p>';
		$long_desc .= '<h2>' . __( 'Simple Theme Integration Mode', 'al-ecommerce-product-catalog' ) . '</h2>';
		$long_desc .= '<p>' . sprintf( __( 'The simple mode allows to use eCommerce Product Catalog most features. You can build the product listing pages and category pages by using a %s shortcode. Simple mode uses your theme page layout so it can show unwanted elements on product page. If it does please switch to Advanced Mode and see if it works out of the box.', 'al-ecommerce-product-catalog' ), '[[show_products]]' ) . '</p>';
		$long_desc .= '<p>' . __( 'Switching to Advanced Mode also gives additional features: automatic product listing, category pages, product search and category widget. Building a product catalog in Advanced Mode will be less time consuming as you don\'t need to use a shortcode for everything.', 'al-ecommerce-product-catalog' ) . '</p>';
		$long_desc .= '<h2>' . __( 'How to switch to Advanced Mode?', 'al-ecommerce-product-catalog' ) . '</h2>';
		$long_desc .= '<p>' . sprintf( __( 'Click <a href="%s">here</a> to test the Automatic Advanced Mode. If the test goes well you can keep it enabled and enjoy full eCommerce Product Catalog functionality. If the page layout during the test will not be satisfying please see <a target="_blank" href="%s">Theme Integration Guide</a>', 'al-ecommerce-product-catalog' ), '?test_advanced=1', 'http://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=sample-product-page&key=integration-mode-test' ) . '</p>';
		$long_desc .= '<p>' . __( 'The theme integration guide will show you a step by step process. If you finish it successfully the integration will be done. It is recommended to use theme integration guide even if the page looks good in simple mode or automatic advanced mode because it reassures 100% theme integrity.', 'al-ecommerce-product-catalog' ) . '</p>';
		$long_desc .= '<h2>' . __( 'Product Description End', 'al-ecommerce-product-catalog' ) . '</h2>';
		$long_desc .= '<p>' . __( 'Below the product description you should see nothing apart of return to products URL and Advanced Mode Test which will not show up on your product pages. When using advanced mode also the related products will show up.', 'al-ecommerce-product-catalog' ) . '</p>';
		$long_desc .= '<p>' . sprintf( __( 'Thank you for choosing eCommerce Product Catalog. If you have any questions or comments please use <a target="_blank" href="%s">plugin support forum</a>.', 'al-ecommerce-product-catalog' ), 'https://wordpress.org/support/plugin/ecommerce-product-catalog' ) . '</p>';
		$long_desc .= '[theme_integration]';
		$product_field[ 'content' ]	 = $long_desc;
		foreach ( $product_field as $key => $value ) {
			add_post_meta( $product_id, $key, $value, true );
		}
		update_option( 'sample_product_id', $product_id );
		return $product_id;
	}
}

function sample_product_id() {
	return get_option( 'sample_product_id' );
}

function ecommerce_product_catalog_upgrade() {
	if ( is_admin() ) {
		$plugin_data			 = get_plugin_data( AL_PLUGIN_MAIN_FILE );
		$plugin_version			 = $plugin_data[ "Version" ];
		$database_plugin_version = get_option( 'ecommerce_product_catalog_ver', $plugin_version );
		if ( $database_plugin_version != $plugin_version ) {
			update_option( 'ecommerce_product_catalog_ver', $plugin_version );
			$first_version = (string) get_option( 'first_activation_version', $plugin_version );
			if ( version_compare( $first_version, '1.9.0' ) < 0 && version_compare( $database_plugin_version, '2.2.4' ) < 0 ) {
				$hide_info = 0;
				enable_advanced_mode( $hide_info );
			}
			if ( version_compare( $first_version, '2.0.0' ) < 0 && version_compare( $database_plugin_version, '2.2.4' ) < 0 ) {
				$archive_multiple_settings							 = get_multiple_settings();
				$archive_multiple_settings[ 'product_listing_cats' ] = 'off';
				$archive_multiple_settings[ 'cat_template' ]		 = 'link';
				update_option( 'archive_multiple_settings', $archive_multiple_settings );
			}
			if ( version_compare( $first_version, '2.0.1' ) < 0 && version_compare( $database_plugin_version, '2.2.4' ) < 0 ) {
				add_product_caps();
			}
			if ( version_compare( $first_version, '2.0.4' ) < 0 && version_compare( $database_plugin_version, '2.2.4' ) < 0 ) {
				delete_transient( 'implecode_extensions_data' );
			}
			if ( version_compare( $first_version, '2.2.5' ) < 0 && version_compare( $database_plugin_version, '2.2.5' ) < 0 ) {
				$archive_names							 = get_option( 'archive_names' );
				$archive_names[ 'all_main_categories' ]	 = '';
				$archive_names[ 'all_products' ]		 = '';
				$archive_names[ 'all_subcategories' ]	 = '';
				update_option( 'archive_names', $archive_names );
			}
			flush_rewrite_rules();
		}
	}
}

add_action( 'admin_init', 'ecommerce_product_catalog_upgrade' );
?>
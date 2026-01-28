<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *
 *  @version       1.0.0
 *  @author        impleCode
 *
 */

if ( ! function_exists( 'register_block_type' ) ) {
	// Gutenberg is not active.
	return;
}

add_filter( 'block_type_metadata', 'ic_block_price_table_registration' );

function ic_block_price_table_registration( $metadata ) {
	if ( $metadata['name'] === 'ic-price-field/ic-price-table' && has_action( 'price_table', 'ic_cart_add_button' ) ) {
		if ( empty( $metadata['attributes'] ) ) {
			$metadata['attributes'] = array();
		}
		$metadata['attributes']['no_add_to_cart'] = array( 'type' => 'boolean' );
	}

	return $metadata;
}

add_filter( 'ic_catalog_admin_scrits_localize', 'ic_block_price_table_localize' );

function ic_block_price_table_localize( $localize ) {
	if ( has_action( 'price_table', 'ic_cart_add_button' ) ) {
		$localize['price_add_cart_added'] = 1;
	}

	return $localize;
}


// Register the block by passing the path to it's block.json file.
register_block_type( __DIR__,
	array(
		'title'           => __( 'Price Table', 'ecommerce-product-catalog' ),
		'render_callback' => 'ic_block_price_table_render',
	)
);

function ic_block_price_table_render( $attr ) {
	$selected_product = isset( $attr['selectedProduct'] ) ? intval( $attr['selectedProduct'] ) : '';
	if ( ! empty( $selected_product ) ) {
		$product_id = $selected_product;
	} else {
		$product_id = get_the_ID();
	}
	if ( ! empty( $product_id ) && class_exists( 'ic_price_display' ) && function_exists( 'is_ic_product' ) && is_ic_product( $product_id ) ) {
		if ( ! empty( $attr['no_add_to_cart'] ) ) {
			$removed_add_to_cart = remove_action( 'price_table', 'ic_cart_add_button' );
		}
		if ( isset( $attr['metaField'] ) ) {
			$new_price = $attr['metaField'];
			add_filter( 'product_price', function ( $price ) use ( $new_price ) {
				return $new_price;
			} );
		}
		$price_table = ic_price_display::get_product_price_table( $product_id );
		if ( isset( $removed_add_to_cart ) && $removed_add_to_cart ) {
			add_action( 'price_table', 'ic_cart_add_button', 10, 0 );
		}
		if ( empty( $price_table ) && ic_is_rendering_block() ) {
			$price_table = __( 'Price not available.', 'ecommerce-product-catalog' );
		}

		return ic_blocks_generate_container( $attr, $price_table, 'price-table', $product_id );
	} else if ( ic_is_rendering_block() ) {
		return __( 'Price not available.', 'ecommerce-product-catalog' );
	}
}

function ic_block_price_enqueue() {
	if ( ! function_exists( 'generate_block_asset_handle' ) ) {
		return;
	}
	$script_handle = generate_block_asset_handle( 'ic-price-field/ic-price-table', 'editorScript' );
	$localize      = array(
		'strings' => array(
			'title'              => __( 'Price Table', 'ecommerce-product-catalog' ),
			'options'            => __( 'Options', 'ecommerce-product-catalog' ),
			'disable_add_cart'   => __( 'Disable add to cart button', 'ecommerce-product-catalog' ),
			'search_product'     => __( 'Search Product', 'ecommerce-product-catalog' ),
			'select_product'     => __( 'Select Product', 'ecommerce-product-catalog' ),
			'search_placeholder' => __( 'Search by item name and select it in the section below', 'ecommerce-product-catalog' ),
			'edit_block'         => __( 'Edit selected product.', 'ecommerce-product-catalog' ),
		)
	);
	if ( has_action( 'price_table', 'ic_cart_add_button' ) ) {
		$localize['price_add_cart_added'] = 1;
	}
	wp_localize_script( $script_handle, 'ic_block_price_table', $localize );
}

add_action( 'admin_enqueue_scripts', 'ic_block_price_enqueue', 99 );
/*
if ( function_exists( 'wp_set_script_translations' ) ) {

	$script_handle = generate_block_asset_handle( 'ic-price-field/ic-price-table', 'editorScript' );
	wp_set_script_translations( $script_handle, 'ecommerce-product-catalog', IC_EPC_TEXTDOMAIN_PATH );
}
*/


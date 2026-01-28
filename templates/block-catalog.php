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

class ic_catalog_block_templates {
	public function __construct() {
		add_action( 'init', array( $this, 'register_block_templates' ) );
	}

	public function register_block_templates() {
		if ( function_exists( 'register_block_template' ) ) {
			register_block_template(
				'ecommerce-product-catalog//archive-al_product',
				array(
					'title'      => __( 'Main Catalog Page', 'ecommerce-product-catalog' ),
					'content'    => file_get_contents( AL_BASE_PATH . '/templates/block/archive-al_product.html' ),
					'post_types' => [ 'al_product' ],
				)
			);
			register_block_template(
				'ecommerce-product-catalog//single-al_product',
				array(
					'title'      => __( 'Single Catalog Page', 'ecommerce-product-catalog' ),
					'content'    => file_get_contents( AL_BASE_PATH . '/templates/block/single-al_product.html' ),
					'post_types' => [ 'al_product' ],
				)
			);
			register_block_template(
				'ecommerce-product-catalog//taxonomy-al_product-cat',
				array(
					'title'      => __( 'Catalog Category Page', 'ecommerce-product-catalog' ),
					'content'    => file_get_contents( AL_BASE_PATH . '/templates/block/taxonomy-al_product-cat.html' ),
					'post_types' => [ 'al_product' ],
				)
			);
		}
	}
}

new ic_catalog_block_templates();
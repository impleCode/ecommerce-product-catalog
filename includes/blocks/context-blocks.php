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

class ic_epc_context_blocks {

	function __construct() {
		add_action( 'ic_register_blocks', array( $this, 'register' ) );
		add_filter( 'block_type_metadata', array( $this, 'configure_metadata' ) );
	}

	function render( $attributes, $block_content, $block ) {
		$product_id = isset( $attributes['selectedProduct'] ) ? $attributes['selectedProduct'] : '';
		if ( empty( $product_id ) && ! is_ic_product_page() ) {
			//return;
		}
		if ( empty( $product_id ) && ! empty( $attributes['ic_context_id'] ) && ic_is_rendering_block() ) {
			$product_id = intval( $attributes['ic_context_id'] );
		}
		if ( empty( $product_id ) ) {
			$product_id = ic_get_product_id();
		}
		$block_name  = explode( '/', $block->name );
		$block_name  = $block_name[1];
		$block_title = $block->block_type->title;
		if ( empty( $product_id ) || ! is_ic_product( $product_id ) ) {
			if ( ic_is_rendering_block() ) {
				$block_content = $this->block_sample( $attributes, $block_name );
			} else {
				return;
			}
		} else {
			$block_content = apply_filters( 'ic_block_content', $block_content, $product_id, $block_name, $attributes );
		}
		if ( empty( $block_content ) && ic_is_rendering_block() ) {
			$block_content = '<div style="padding: 20px 10px">' . sprintf( __( '%s will only show up on the product page if the product has the related data assigned.', 'ecommerce-product-catalog' ), $block_title ) . '</div>';
		}

		return $this->container( $attributes, $block_content, $block_name, $product_id );
	}

	function register() {
		$blocks = apply_filters( 'ic_product_parts_blocks', array(
			__DIR__ . '/image-gallery/',
			__DIR__ . '/name/',
			__DIR__ . '/short-description/'
		) );
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

	function configure_metadata( $metadata ) {
		if ( ! empty( $metadata['name'] ) && ( ic_string_contains( $metadata['name'], 'ic-epc' ) || ic_string_contains( $metadata['name'], 'ic-price-field' ) ) ) {
			if ( ! empty( $metadata['attributes']['fontSize'] ) ) {
				$metadata['attributes']['style']    = array(
					'type'    => 'object',
					'default' => array()
				);
				$metadata['supports']['typography'] = array(
					'fontSize'                      => true,
					'lineHeight'                    => true,
					'__experimentalFontStyle'       => true,
					'__experimentalFontWeight'      => true,
					'__experimentalLetterSpacing'   => true,
					'__experimentalTextTransform'   => true,
					'__experimentalDefaultControls' => array(
						'fontSize' => true
					)
				);
			}
			if ( ! empty( $metadata['attributes']['colors'] ) ) {
				$metadata['attributes']['textColor']       = array(
					'type'    => 'string',
					'default' => ''
				);
				$metadata['attributes']['backgroundColor'] = array(
					'type'    => 'string',
					'default' => ''
				);
				$metadata['supports']['color']             = true;
				unset( $metadata['attributes']['colors'] );
			}
			if ( ! empty( $metadata['attributes']['alignment'] ) ) {
				$metadata['attributes']['align'] = array(
					'type'    => 'string',
					'default' => ''
				);
				$metadata['supports']['align']   = true;
			}

			if ( ! empty( $metadata['attributes']['spacing'] ) ) {
				unset( $metadata['attributes']['spacing'] );
				$metadata['supports']['spacing'] = array(
					'margin'  => true,
					'padding' => true
				);
				$metadata['attributes']['style'] = array(
					'type'    => 'object',
					'default' => array()
				);
			}
			if ( ! empty( $metadata['attributes']['border'] ) ) {
				$metadata['supports']['__experimentalBorder'] = array(
					'radius' => true,
					'color'  => true,
					'width'  => true,
					'style'  => true
				);
				$metadata['attributes']['style']              = array(
					'type'    => 'object',
					'default' => array()
				);
			}
			$metadata['attributes']['ic_context_id']        = array(
				'type'    => 'integer',
				'default' => 0
			);
			$metadata['attributes']['ic_context_post_type'] = array(
				'type'    => 'string',
				'default' => ''
			);
			$block_name                                     = explode( '/', $metadata['name'] );
			$block_name                                     = $block_name[1];
			$metadata                                       = apply_filters( 'ic_block_metadata', $metadata, $block_name );
		}

		return $metadata;
	}

	function block_sample( $attributes, $block_name ) {
		$block_content = '';
		if ( empty( $attributes['sample'] ) ) {
			$product_id = $this->sample_product_id();
			if ( $product_id ) {
				$block_content = apply_filters( 'ic_block_content', '', $product_id, $block_name, $attributes );
			}
		} else {
			$block_content = $attributes['sample'];
		}
		if ( ! empty( $block_content ) ) {
			//$block_content = '<div class="ic-block-sample-container">[RANDOM DATA] ' . $block_content . '</div>';
		}

		return $block_content;
	}

	function sample_product_id() {
		$products = get_all_catalog_products( null, null, 1 );
		if ( ! empty( $products[0] ) && isset( $products[0]->ID ) ) {
			return $products[0]->ID;
		}

		return 0;
	}

	function container( $attr, $content, $name, $product_id = null ) {
		if ( ic_is_rendering_block() ) {
			return $content;
		}
		$wrapper_attributes = get_block_wrapper_attributes();
		$class              = 'ic-block-' . $name;
		if ( isset( $attr['alignment'] ) ) {
			if ( $attr['alignment'] === 'center' ) {
				$class .= ' ic-align-center';
			} else if ( $attr['alignment'] === 'right' ) {
				$class .= ' ic-align-right';
			}
		}
		$container_class = apply_filters( 'ic_block_container_class', $class, $product_id, $name );
		if ( ic_string_contains( $wrapper_attributes, 'class="' ) ) {
			$wrapper_attributes = str_replace( 'class="', 'class="' . $container_class . ' ', $wrapper_attributes );
		} else {
			$wrapper_attributes .= ' class="' . $container_class . '"';
		}


		return '<div ' . $wrapper_attributes . '>' . $content . '</div>';
	}

}
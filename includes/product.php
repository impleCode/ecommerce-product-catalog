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
class ic_product {

	public $ID, $post;

	function __construct( $product_id = null, $post = null ) {
		if ( empty( $product_id ) && empty( $post ) ) {
			$product_id = ic_get_product_id();
		}
		if ( ! empty( $product_id ) && empty( $post ) ) {
			$product_id = apply_filters( 'ic_product_id', $product_id );
			$post       = get_post( $product_id );
		}
		//if ( is_ic_product( $product_id ) ) {
		$this->ID   = $product_id;
		$this->post = $post;
		//}
	}

	/**
	 * Returns product name
	 *
	 * @return string
	 */
	function name() {
		$name = get_the_title( $this->ID );

		return apply_filters( 'ic_product_name', $name, $this->ID );
	}

	function archive_price_html() {
		if ( empty( $this->post ) ) {
			return '';
		}

		return apply_filters( 'archive_price_filter', '', $this->post );
	}

	/**
	 * Returns product description
	 *
	 * @return string
	 */
	function description() {
		$product_desc = '';
		if ( ! empty( $this->post->post_content ) ) {
			$product_desc = $this->post->post_content;
		}

		return apply_filters( 'get_product_description', $product_desc, $this->ID );
	}

	/**
	 * Returns product short description
	 *
	 * @return string
	 */
	function short_description() {
		$product_desc = '';
		if ( ! empty( $this->post->post_excerpt ) ) {
			$product_desc = $this->post->post_excerpt;
		}

		return apply_filters( 'get_product_short_description', $product_desc, $this->ID );
	}

	function url() {
		$permalink = ic_get_permalink( $this->ID );

		return apply_filters( 'ic_product_url', $permalink, $this->ID );
	}

	function image_html( $show_default = true ) {
		$product_image = ic_get_global( $this->ID . "_product_image" );
		if ( $product_image ) {
			return $product_image;
		}
		do_action( 'ic_before_get_image_html', $this->ID );
		if ( has_post_thumbnail( $this->ID ) ) {
			$image_size = apply_filters( 'product_image_size', 'product-page-image' );
			$attr       = '&class=attachment-product-page-image size-product-page-image';
			if ( is_ic_magnifier_enabled() && is_ic_product_page() ) {
				$attr .= ' ic_magnifier';
				$attr .= '&data-zoom-image=' . $this->image_url();
			}
			$attr          .= '&loading=eager';
			$product_image = get_the_post_thumbnail( $this->ID, $image_size, $attr );
		} else if ( $show_default ) {
			$single_options = get_product_page_settings();
			if ( $single_options['enable_product_gallery_only_when_exist'] != 1 ) {
				$product_image = default_product_thumbnail();
			}
		}
		$product_image = apply_filters( 'ic_get_product_image', $product_image, $this->ID );
		ic_save_global( $this->ID . "_product_image", $product_image );

		return $product_image;
	}

	function default_listing_image_html( $product_id ) {
		$image_id          = get_post_thumbnail_id( $product_id );
		$thumbnail_product = wp_get_attachment_image_src( $image_id, 'classic-grid-listing' );
		$product_name      = get_product_name( $product_id );
		if ( $thumbnail_product ) {
			$img_class['alt']   = $product_name;
			$img_class['class'] = 'classic-grid-image default-listing-image';
			$image_html         = wp_get_attachment_image( $image_id, 'classic-grid-listing', false, $img_class );
		} else {
			$default_image = apply_filters( 'ic_default_product_listing_image', '' );
			if ( ! empty( $default_image ) ) {
				return $default_image;
			}
			$url        = default_product_thumbnail_url();
			$image_html = '<img src="' . $url . '" class="classic-grid-image default-image" alt="' . $product_name . '" >';
		}

		return $image_html;
	}

	function listing_image_html() {
		$image_html = ic_get_global( 'ic_listing_image_html_' . $this->ID );
		if ( ! empty( $image_html ) ) {
			return $image_html;
		} else {
			$image_html = apply_filters( 'ic_listing_pre_image_html', '', $this->ID, $this );
			if ( empty( $image_html ) ) {
				$image_html = apply_filters( 'ic_listing_image_html', '', $this->ID, $this );
			}
		}
		if ( empty( $image_html ) ) {
			$image_html = $this->default_listing_image_html( $this->ID );
		}
		$image_html = apply_filters( 'ic_listing_image_final_html', $image_html, $this->ID, $this );
		ic_save_global( 'ic_listing_image_html_' . $this->ID, $image_html );

		return $image_html;
	}

	function image_url() {
		if ( is_ic_magnifier_enabled() || is_lightbox_enabled() ) {
			$size = 'full';
		} else {
			$size = 'large';
		}
		$image_id = $this->image_id();
		if ( ! empty( $image_id ) ) {
			$img_url = wp_get_attachment_image_src( $image_id, $size );
		}
		if ( empty( $img_url ) || ! is_array( $img_url ) ) {
			$img_url = array();
		}
		if ( empty( $img_url[0] ) ) {
			$img_url[0] = default_product_thumbnail_url();
		}

		return $img_url[0];
	}

	function image_id() {
		$image_id = get_post_thumbnail_id( $this->ID );
		if ( empty( $image_id ) ) {
			$image_id = ic_default_product_image_id();
		}

		return $image_id;
	}

}

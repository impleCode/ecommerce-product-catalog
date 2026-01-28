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
if ( ! function_exists( 'ic_blocks_generate_container' ) ) {

	function ic_blocks_generate_container( $attr, $content, $name, $product_id = null ) {
		if ( ic_is_rendering_block() ) {
			return $content;
		}
		$wrapper_attributes = get_block_wrapper_attributes();
		$class              = 'ic-block-' . $name;
		if ( $attr['alignment'] === 'center' ) {
			$class .= ' ic-align-center';
		} else if ( $attr['alignment'] === 'right' ) {
			$class .= ' ic-align-right';
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


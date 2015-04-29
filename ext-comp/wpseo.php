<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages compatibility functions with WordPress SEO plugin
 *
 *
 * @version		1.0.0
 * @package		digital-products-order/functions
 * @author 		Norbert Dreszer
 */
function implecode_wpseo_compatible() {
	$post_type = get_quasi_post_type();
	if ( $post_type == 'al_product' ) {
		add_filter( 'wpseo_metabox_prio', 'implecode_wpseo_compatible_priority' );
	}
}

add_action( 'add_meta_boxes', 'implecode_wpseo_compatible' );

function implecode_wpseo_compatible_priority() {
	return 'low';
}

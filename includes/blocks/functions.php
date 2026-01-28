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
if ( ! function_exists( 'ic_blocks_context' ) ) {

	function ic_blocks_context() {
		$context = array(
			'id'   => apply_filters( 'ic_block_context_id', get_the_ID() ),
			'type' => apply_filters( 'ic_block_context_type', get_post_type(), get_the_ID() )
		);
		if ( empty( $context['id'] ) ) {
			$context['id'] = 0;
		}
		if ( empty( $context['type'] ) ) {
			$context['type'] = '';
		}

		return $context;
	}

}


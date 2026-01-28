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

add_filter( 'pll_copy_post_metas', 'ic_polylang_translate_shipping', 10, 2 );

function ic_polylang_translate_shipping( $metas, $sync ) {
	if ( $sync === true ) {
		return $metas;
	}
	$shipping_meta_keys = array();
	$max_shipping       = get_shipping_options_number();
	for ( $i = 1; $i <= $max_shipping; $i ++ ) {
		$shipping_meta_keys[] = '_shipping' . $i;
		$shipping_meta_keys[] = '_shipping-label' . $i;
	}

	return array_merge( $metas, $shipping_meta_keys );
}

add_filter( 'wpml_config_array', 'ic_wpml_config_array_shipping' );

function ic_wpml_config_array_shipping( $array ) {
	if ( ! empty( $array['wpml-config']['custom-fields']['custom-field'] ) ) {
		$max_shipping = get_shipping_options_number();
		for ( $i = 1; $i <= $max_shipping; $i ++ ) {

			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => '_shipping' . $i,
				'attr'  => array(
					'action' => 'copy',
					'label'  => __( 'Shipping Price', 'ecommerce-product-catalog' )
				)
			);
			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => '_shipping-label' . $i,
				'attr'  => array(
					'action' => 'translate',
					'label'  => __( 'Shipping Label', 'ecommerce-product-catalog' )
				)
			);
		}
	}

	return $array;
}

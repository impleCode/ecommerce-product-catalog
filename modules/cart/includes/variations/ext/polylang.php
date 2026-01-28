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

add_filter( 'pll_copy_post_metas', 'ic_polylang_copy_variations', 10, 2 );

function ic_polylang_copy_variations( $metas, $sync ) {
	$var_meta_keys               = array();
	$product_variations_settings = get_product_variations_settings();
	for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {
		if ( empty( $sync ) ) {
			$var_meta_keys[] = $i . '_variation_label';
			$var_meta_keys[] = $i . '_variation_values';
		} else {
			$var_meta_keys[] = $i . '_variation_prices';
			$var_meta_keys[] = $i . '_variation_shipping';
			$var_meta_keys[] = $i . '_variation_mod';
			$var_meta_keys[] = $i . '_variation_type';
		}
	}

	return array_merge( $metas, $var_meta_keys );
}

add_filter( 'wpml_config_array', 'ic_wpml_config_array_variations' );

function ic_wpml_config_array_variations( $array ) {
	if ( ! empty( $array['wpml-config']['custom-fields']['custom-field'] ) ) {
		$product_variations_settings = get_product_variations_settings();
		for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {

			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => $i . '_variation_label',
				'attr'  => array(
					'action' => 'translate',
					'label'  => __( 'Variation Label', 'ecommerce-product-catalog' )
				)
			);
			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => $i . '_variation_values',
				'attr'  => array(
					'action' => 'translate',
					'label'  => __( 'Variation Value', 'ecommerce-product-catalog' )
				)
			);
			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => $i . '_variation_prices',
				'attr'  => array(
					'action' => 'copy',
					'label'  => __( 'Variation Price', 'ecommerce-product-catalog' )
				)
			);
			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => $i . '_variation_shipping',
				'attr'  => array(
					'action' => 'copy'
				)
			);
			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => $i . '_variation_mod',
				'attr'  => array(
					'action' => 'copy'
				)
			);
			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => $i . '_variation_type',
				'attr'  => array(
					'action' => 'copy'
				)
			);
			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => $i . '_variation_values_filterable',
				'attr'  => array(
					'action' => 'copy_first'
				)
			);
			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => $i . '_variation_prices_filterable',
				'attr'  => array(
					'action' => 'copy'
				)
			);
			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => $i . '_variation_shipping_filterable',
				'attr'  => array(
					'action' => 'copy'
				)
			);
		}
	}

	return $array;
}

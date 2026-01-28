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

add_filter( 'pll_copy_post_metas', 'ic_polylang_translate_attributes', 10, 2 );

function ic_polylang_translate_attributes( $metas, $sync ) {
	if ( $sync === true ) {
		return $metas;
	}
	$attr_meta_keys = array();
	$max_attributes = product_attributes_number();
	for ( $i = 1; $i <= $max_attributes; $i ++ ) {
		$attr_meta_keys[] = '_attribute' . $i;
		$attr_meta_keys[] = '_attribute-label' . $i;
		$attr_meta_keys[] = '_attribute-unit' . $i;
	}

	return array_merge( $metas, $attr_meta_keys );
}

add_filter( 'wpml_config_array', 'ic_wpml_config_array_attributes' );

function ic_wpml_config_array_attributes( $array ) {
	if ( ! empty( $array['wpml-config']['custom-fields']['custom-field'] ) ) {
		$max_attributes = product_attributes_number();
		for ( $i = 1; $i <= $max_attributes; $i ++ ) {

			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => '_attribute' . $i,
				'attr'  => array(
					'action' => 'translate',
					'label'  => __( 'Attribute Value', 'ecommerce-product-catalog' )
				)
			);
			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => '_attribute-label' . $i,
				'attr'  => array(
					'action' => 'translate',
					'label'  => __( 'Attribute Label', 'ecommerce-product-catalog' )
				)
			);
			$array['wpml-config']['custom-fields']['custom-field'] [] = array(
				'value' => '_attribute-unit' . $i,
				'attr'  => array(
					'action' => 'translate',
					'label'  => __( 'Attribute Unit', 'ecommerce-product-catalog' )
				)
			);
		}
	}

	return $array;
}


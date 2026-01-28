<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
add_action( 'after_product_details', 'show_product_attributes', 20, 1 );

/**
 * Shows product attributes table on product page
 *
 * @param object $post
 * @param array $single_names
 */
function show_product_attributes( $product_id = false ) {
	if ( is_object( $product_id ) && isset( $product_id->ID ) ) {
		$product_id = $product_id->ID;
	}
	ic_show_template_file( 'product-page/product-attributes.php', AL_BASE_TEMPLATES_PATH, $product_id );
	//echo get_product_attributes( $post->ID, $single_names );
}

/**
 * Returns product attributes HTML table
 *
 * @param int $product_id
 * @param array $v_single_names
 *
 * @return string
 */
function get_product_attributes( $product_id, $v_single_names = null ) {
	ob_start();
	show_product_attributes( $product_id );

	return ob_get_clean();
}

/**
 * Returns selected attribute label
 *
 * @param type $i
 * @param type $product_id
 *
 * @return type
 */
function get_attribute_label( $i = 1, $product_id = null ) {
	if ( empty( $product_id ) ) {
		$product_id = ic_get_product_id();
	}
	$field_name = apply_filters( 'ic_attribute_label_field_name', "_attribute-label" ) . $i;
	$label      = ic_get_global( $product_id . $field_name );
	if ( $label === false ) {
		$label         = get_post_meta( $product_id, $field_name, true );
		$default_label = get_default_product_attribute_label( $i );
		if ( empty( $label ) ) {
			$label = $default_label;
		} else if ( $label === $default_label ) {
			delete_post_meta( $product_id, $field_name );
		}
		ic_save_global( $product_id . $field_name, $label );
	}

	return apply_filters( 'ic_attribute_label', $label, $product_id );
}

/**
 * Returns selected attribute value
 *
 * @param type $i
 * @param type $product_id
 *
 * @return type
 */
function get_attribute_value( $i = 1, $product_id = null ) {
	if ( empty( $product_id ) ) {
		$product_id = ic_get_product_id();
	}
	$field_name = ic_attr_value_field_name( $i );
	$value      = ic_get_global( $product_id . $field_name );
	if ( $value === false ) {
		$value = get_post_meta( $product_id, $field_name, true );
	}
	if ( ! is_array( $value ) && apply_filters( 'ic_get_attr_value_from_tax', true ) ) {
		$label         = get_attribute_label( $i, $product_id );
		$default_label = get_default_product_attribute_label( $i );
		if ( ! empty( $label ) && ! empty( $default_label ) && $default_label === $label ) {
			$values = ic_get_attribute_values( $label, 'names', false, array( $product_id ) );
			if ( $values === false ) {
				global $wp_filter;
				if ( ! empty( $wp_filter['option_product_attribute_label'] ) ) {
					$restore = $wp_filter['option_product_attribute_label'];
					unset( $wp_filter['option_product_attribute_label'] );
					$default_label                               = get_default_product_attribute_label( $i );
					$wp_filter['option_product_attribute_label'] = $restore;
					$values                                      = ic_get_attribute_values( $default_label, 'names', false, array( $product_id ) );
				}
			}
			$sanitized = ic_sanitize_product_attribute( $value );
			//if ( $values !== false ) {
			if ( ! empty( $values[0] ) && $values[0] !== $sanitized ) {
				$value = $values[0];
				//update_post_meta( $product_id, $field_name, $value );
			} else if ( ! empty( $sanitized ) && empty( $values [0] ) ) {
				$all_attribute_values = get_all_attribute_values( $product_id );
				if ( ! empty( $all_attribute_values ) && is_array( $all_attribute_values ) && in_array( $sanitized, $all_attribute_values ) ) {
					$value = '';
				}
			}
			//}
		}
	}
	if ( is_array( $value ) && ! function_exists( 'start_attributes_pro' ) ) {
		$value = implode( ',', $value );
	}


	if ( function_exists( 'is_ic_product_page' ) && is_ic_product_page() && ! is_array( $value ) ) {
		$value = str_replace( 'rel="nofollow"', '', make_clickable( $value ) );
	}
	ic_save_global( $product_id . $field_name, $value );

	return apply_filters( 'ic_attribute_value', $value, $product_id, $i );
}

/**
 * Meta name for attribute value
 *
 * @param int $i
 *
 * @return string
 */
function ic_attr_value_field_name( $i ) {
	$base       = ic_attr_value_field_base();
	$field_name = $base . $i;

	return $field_name;
}

/**
 * Base for the attribute value meta name
 *
 * @return string
 */
function ic_attr_value_field_base() {
	$base = apply_filters( 'ic_attribute_value_field_name', "_attribute" );

	return $base;
}

/**
 * Returns selected attribute unit
 *
 * @param type $i
 * @param type $product_id
 *
 * @return type
 */
function get_attribute_unit( $i = 1, $product_id = null ) {
	if ( empty( $product_id ) ) {
		$product_id = ic_get_product_id();
	}
	$field_name = apply_filters( 'ic_attribute_unit_field_name', "_attribute-unit" ) . $i;
	$unit       = ic_get_global( $product_id . $field_name );
	if ( $unit === false ) {
		$unit = get_post_meta( $product_id, $field_name, true );
		if ( empty( $unit ) ) {
			$unit = get_default_product_attribute_unit( $i );
		}
		ic_save_global( $product_id . $field_name, $unit );
	}

	return $unit;
}

if ( ! function_exists( 'get_attribute_label_id' ) ) {

	function get_attribute_label_id( $label ) {
		//$cache_meta      = 'attr_label_id' . $label;
		$cache_label_ids = ic_get_global( 'attr_label_id' );
		if ( empty( $cache_label_ids ) ) {
			$cache_label_ids = array();
		} else if ( ! empty( $cache_label_ids[ $label ] ) ) {
			return $cache_label_ids[ $label ];
		}
		$args ['taxonomy'] = 'al_product-attributes';
		$args ['name']     = $label;
		$args['parent']    = 0;
		$args['fields']    = 'ids';
		//$args[ 'update_term_meta_cache' ]	 = false;
		$label_ids = ic_get_terms( $args );
		if ( ! empty( $label_ids ) && ! is_wp_error( $label_ids ) ) {
			$label_id = intval( $label_ids[0] );
			if ( ! empty( $label_id ) ) {
				$cache_label_ids[ $label ] = $label_id;
				ic_save_global( 'attr_label_id', $cache_label_ids );

				return $label_id;
			}
		}

		return false;
	}

}

if ( ! function_exists( 'get_attribute_value_id' ) ) {

	function get_attribute_value_id( $label_id, $value, $by_name = false ) {
		if ( ! is_numeric( $label_id ) ) {
			$label_id = get_attribute_label_id( $label_id );
		}
		if ( empty( $label_id ) ) {
			return false;
		}
		$cached_term_ids = ic_get_global( 'attr_value_id' );
		if ( empty( $cached_term_ids ) ) {
			$cached_term_ids = array();
		}
		if ( ! empty( $cached_term_ids[ $label_id ][ $value ] ) ) {
			return $cached_term_ids[ $label_id ][ $value ];
		}
		$args['taxonomy'] = 'al_product-attributes';
		if ( $by_name ) {
			$args['name'] = strval( $value );
		} else {
			$args['slug'] = trim( wp_unslash( sanitize_term_field( 'slug', $value, 0, 'al_product-attributes', 'db' ) ) );
		}
		$args['child_of'] = $label_id;
		$args['fields']   = 'ids';
		$value_ids        = ic_get_terms( $args );
		if ( ! empty( $value_ids ) && ! is_wp_error( $value_ids ) ) {
			$value_id = intval( $value_ids[0] );
		} else if ( ! $by_name ) {
			$value_id = get_attribute_value_id( $label_id, $value, true );
		}
		if ( ! empty( $value_id ) ) {
			$cached_term_ids[ $label_id ][ $value ] = $value_id;
			ic_save_global( 'attr_value_id', $cached_term_ids );

			return $value_id;
		} else {
			return false;
		}
	}

}

add_action( 'product_details', 'ic_show_size', 9, 1 );

/**
 * Shows product SKU table
 *
 * @param object $post
 * @param array $single_names
 */
function ic_show_size( $product_id = false ) {
	if ( is_object( $product_id ) && isset( $product_id->ID ) ) {
		$product_id = $product_id->ID;
	}
	ic_show_template_file( 'product-page/product-size.php', AL_BASE_TEMPLATES_PATH, $product_id );
}

add_action( 'product_details', 'ic_show_weight', 9, 1 );

/**
 * Shows product SKU table
 *
 * @param object $post
 * @param array $single_names
 */
function ic_show_weight( $product_id = false ) {
	if ( is_object( $product_id ) && isset( $product_id->ID ) ) {
		$product_id = $product_id->ID;
	}
	ic_show_template_file( 'product-page/product-weight.php', AL_BASE_TEMPLATES_PATH, $product_id );
}

add_action( 'classic_grid_product_listing_element_inside', 'ic_listing_add_attributes', 10, 2 );
add_action( 'classic_list_entry_bottom', 'ic_listing_add_attributes', 10, 2 );
add_action( 'modern_grid_entry_inside', 'ic_listing_add_attributes', 10, 2 );

function ic_listing_add_attributes( $product_id, $settings ) {
	if ( $settings['attributes'] == 1 ) {
		ic_save_global( 'listing_attributes_num', $settings['attributes_num'] );
		ic_show_template_file( 'product-listing/listing-attributes.php' );
	}
}

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
add_action( 'init', 'ic_create_product_attributes' );

/**
 * Registers attributes taxonomy
 *
 */
function ic_create_product_attributes() {
	$args = array(
		'label'        => 'Attributes',
		'hierarchical' => true,
		'public'       => false,
		'query_var'    => false,
		'rewrite'      => false,
	);
	if ( function_exists( 'product_post_type_array' ) ) {
		$post_types_def = product_post_type_array();
	} else {
		$post_types_def = array();
	}
	$post_types = apply_filters( 'ic_attributes_register_post_types', $post_types_def );
	register_taxonomy( 'al_product-attributes', $post_types, $args );
}

/**
 * Adds product attribute label and returns attribute label ID
 *
 * @param type $label
 *
 * @return type
 */
function ic_add_product_attribute_label( $label ) {
	if ( is_array( $label ) ) {
		foreach ( $label as $single_label ) {
			$term_id = ic_add_product_attribute_label( $single_label );
		}

		return $term_id;
	} else {
		$term_id = get_attribute_label_id( $label );
		if ( ! empty( $term_id ) ) {
			return $term_id;
		}
		$term = wp_insert_term( $label, 'al_product-attributes' );
		if ( ! is_wp_error( $term ) ) {
			return $term['term_id'];
		} else if ( ! empty( $term->error_data['term_exists'] ) ) {
			return intval( $term->error_data['term_exists'] );
		}
	}

	return '';
}

/**
 * Adds product attribute value and returns attribute value ID
 *
 * @param type $label_id
 * @param type $value
 *
 * @return type
 */
function ic_add_product_attribute_value( $label_id, $value ) {
	if ( empty( $label_id ) ) {
		return '';
	}
	if ( is_array( $value ) ) {
		foreach ( $value as $current_value ) {
			$term_id = ic_add_product_attribute_value( $label_id, $current_value );
		}
	} else {
		$term_id = get_attribute_value_id( $label_id, $value, true );
		if ( empty( $term_id ) ) {
			$term = wp_insert_term( strval( $value ), 'al_product-attributes', array( 'parent' => intval( $label_id ) ) );
			if ( ! is_wp_error( $term ) ) {
				$term_id = $term['term_id'];
			} else if ( ! empty( $term->error_data['term_exists'] ) ) {
				return intval( $term->error_data['term_exists'] );
			}
		}
	}

	return $term_id;
}

//add_filter( 'product_meta_save', 'ic_assign_product_attributes', 2, 2 );
add_action( 'product_meta_save_update', 'ic_assign_product_attributes', 2, 2 );

/**
 * Adds product attributes to the database
 *
 * @param type $product_meta
 * @param type $post
 *
 * @return type
 */
function ic_assign_product_attributes( $product_meta, $post, $clear_empty = true ) {
	$max_attr = apply_filters( 'ic_max_indexed_attributes', product_attributes_number() );
	if ( $max_attr > 0 ) {
		$product_id = isset( $post->ID ) ? $post->ID : $post;
		if ( ! isset( $post->ID ) && ! empty( $product_id ) ) {
			$post = get_post( $product_id );
		}
		$attr_ids             = array();
		$process_product_meta = $product_meta;
		if ( function_exists( 'ic_visible_product_status' ) && in_array( $post->post_status, ic_visible_product_status() ) ) {
			for ( $i = 1; $i <= $max_attr; $i ++ ) {
				$attr_ids   = apply_filters( 'ic_assign_product_attribute', $attr_ids, $process_product_meta, $i );
				$value_meta = ic_attr_value_field_name( $i );
				$label_meta = '_attribute-label' . $i;
				if ( empty( $process_product_meta[ $value_meta ] ) || ( is_array( $process_product_meta[ $value_meta ] ) && isset( $process_product_meta[ $value_meta ][0] ) && empty( $process_product_meta[ $value_meta ][0] ) ) ) {
					continue;
				}
				//$process_product_meta[ $value_meta ] = get_post_meta( $product_id, $value_meta, true );
				$attr_ids = array_merge( $attr_ids, ic_assign_product_attribute( $process_product_meta, $label_meta, $value_meta, $i ) );
			}
		}
		if ( ! empty( $attr_ids ) ) {
			$attr_ids = array_unique( array_map( 'intval', $attr_ids ) );
		} else {
			$attr_ids = '';
		}
		wp_set_object_terms( $product_id, $attr_ids, 'al_product-attributes' );
		if ( $clear_empty ) {
			ic_clear_empty_attributes();
		}
	}

	return $product_meta;
}

function ic_assign_product_attribute( $product_meta, $label_meta, $value_meta, $i ) {
	$default_label = get_default_product_attribute_label( $i );
	if ( ! empty( $product_meta[ $label_meta ] ) ) {
		$label = ic_sanitize_product_attribute( $product_meta[ $label_meta ] );
	} else {
		$label = ic_sanitize_product_attribute( $default_label );
	}
	$value = ic_sanitize_product_attribute( $product_meta[ $value_meta ], $label );

	return ic_add_product_attribute( $label, $value );
}

function ic_add_product_attribute( $label, $value ) {
	$attr_ids = array();
	if ( empty( $label ) || empty( $value ) ) {
		return $attr_ids;
	}
	$label_id = ic_add_product_attribute_label( $label );
	if ( empty( $label_id ) ) {
		return $attr_ids;
	}
	$attr_ids[] = $label_id;
	if ( ! is_array( $value ) ) {
		$value = array( $value );
	}
	foreach ( $value as $val ) {
		$value_id   = ic_add_product_attribute_value( $label_id, $val );
		$attr_ids[] = $value_id;
	}

	return $attr_ids;
}

add_action( 'ic_scheduled_attributes_clear', 'ic_clear_empty_attributes' );

/**
 * Clears empty product attributes
 *
 */
function ic_clear_empty_attributes() {
	if ( wp_defer_term_counting() ) {
		if ( ! wp_get_schedule( 'ic_scheduled_attributes_clear' ) ) {
			wp_schedule_single_event( time() + MINUTE_IN_SECONDS, 'ic_scheduled_attributes_clear' );
		}

		return;
	}
	$max_attr   = product_attributes_number();
	$attributes = ic_get_terms( array(
		'taxonomy'   => 'al_product-attributes',
		'orderby'    => 'count',
		'hide_empty' => 0,
		'number'     => $max_attr
	) );
	$schedule   = false;
	if ( ! empty( $attributes ) && is_array( $attributes ) && ! is_wp_error( $attributes ) ) {
		$prev_suspend = wp_suspend_cache_invalidation();
		foreach ( $attributes as $attribute ) {
			if ( $attribute->count == 0 && ! empty( $attribute->term_id ) ) {
				$schedule = true;
				wp_delete_term( $attribute->term_id, 'al_product-attributes' );
			} else {
				$schedule = false;
				break;
			}
		}
		wp_suspend_cache_invalidation( $prev_suspend );
		if ( /* !wp_get_schedule( 'ic_scheduled_attributes_clear' ) && */ $schedule ) {
			//wp_schedule_event( time(), 'hourly', 'ic_scheduled_attributes_clear' );
			wp_schedule_single_event( time(), 'ic_scheduled_attributes_clear' );
		} else {
			wp_clear_scheduled_hook( 'ic_scheduled_attributes_clear' );
		}
	} else {
		wp_clear_scheduled_hook( 'ic_scheduled_attributes_clear' );
	}
}

add_action( 'ic_scheduled_attributes_assignment', 'ic_reassign_all_products_attributes' );

/**
 * Scheduled even to reassign all product attributes
 *
 * @return string
 */
function ic_reassign_all_products_attributes() {
	$start_time = microtime( true );
	$max_attr   = product_attributes_number();
	if ( empty( $max_attr ) ) {
		return;
	}
	$option_name = 'ic_product_upgrade_done';
	$done        = get_option( $option_name, 0 );
	if ( empty( $done ) ) {
		if ( ! get_transient( $option_name ) ) {
			update_option( $option_name, - 1, false );
			wp_schedule_single_event( time(), 'ic_scheduled_attributes_assignment' );
		} else {
			return __( 'Just Finished! Wait 10 minutes before restarting.', 'ecommerce-product-catalog' );
		}

		return $done;
	}

	if ( $done < 0 ) {
		$done = 0;
	}
	$done = ic_reassign_products_attributes( $done, $start_time );
	if ( $done !== 'done' ) {
		update_option( $option_name, $done, false );
		wp_schedule_single_event( time(), 'ic_scheduled_attributes_assignment' );
	} else {
		delete_option( $option_name );
		wp_clear_scheduled_hook( 'ic_scheduled_attributes_assignment' );
		set_transient( $option_name, 1, MINUTE_IN_SECONDS * 10 );
		ic_clear_empty_attributes();
		do_action( 'ic_attr_reassignment_done' );
	}

	return $done;
}

function ic_reassign_products_attributes( $done, $start_time = 0, $repeat = true, $rounds = 1, $max_round = 0 ) {
	if ( empty( $start_time ) ) {
		$start_time = microtime( true );
	}
	$safe_max_time = function_exists( 'ic_get_safe_time' ) ? ic_get_safe_time() : 25;
	if ( empty( $max_round ) ) {
		$max_attr  = product_attributes_number();
		$max_round = intval( 300 / $max_attr );
		if ( $max_round > 100 ) {
			$max_round = 100;
		}
		if ( $done > 100 ) {
			$max_round = apply_filters( 'ic_database_upgrade_max_round', $max_round * 2 );
		}
	}
	$products = get_all_catalog_products( 'date', 'ASC', 200, $done, apply_filters( 'ic_reassign_attr_product_args', array() ) );
	foreach ( $products as $post ) {
		if ( $rounds > $max_round ) {
			$repeat = false;
			break;
		}
		$product_meta = get_post_meta( $post->ID );
		ic_assign_product_attributes( $product_meta, $post, false );
		$done ++;
		$rounds ++;
		$time_elapsed_secs = microtime( true ) - $start_time;
		if ( $safe_max_time > 30 && $time_elapsed_secs < $safe_max_time ) {
			$max_round ++;
		} else {
			$repeat = false;
		}
	}
	wp_cache_flush();
	if ( ! empty( $products ) ) {
		if ( $repeat && $rounds < $max_round && ( ! function_exists( 'ic_is_reaching_memory_limit' ) || ! ic_is_reaching_memory_limit() ) ) {

			return ic_reassign_products_attributes( $done, $start_time, $repeat, $rounds, $max_round );
		} else {

			return $done;
		}
	}

	return 'done';
}

add_action( 'ic_system_tools', 'ic_system_tools_attributes_upgrade' );

/**
 * Shows a database upgrade button in system tools
 *
 */
function ic_system_tools_attributes_upgrade() {
	$done = get_option( 'ic_product_upgrade_done', 0 );
	//$products_count = ic_products_count();
	if ( ! empty( $done ) || isset( $_GET['reassign_all_products_attributes'] ) ) {
		if ( empty( $done ) && isset( $_GET['reassign_all_products_attributes'] ) && check_admin_referer( 'ic_reassign_all_products_attributes' ) ) {
			$done = ic_reassign_all_products_attributes();
		}
		if ( ! wp_next_scheduled( 'ic_scheduled_attributes_assignment' ) ) {
			wp_schedule_single_event( time(), 'ic_scheduled_attributes_assignment' );
		}
		echo '<tr>';
		echo '<td>Database Upgrade</td>';
		echo '<td><a class="button" href="' . admin_url( 'edit.php?post_type=al_product&page=system.php&reassign_all_products_attributes=1' ) . '">Speed UP Pending Database Upgrade</a>';
		if ( isset( $_GET['reassign_all_products_attributes'] ) ) {
			if ( is_numeric( $done ) ) {
				if ( $done < 0 ) {
					$done = 0;
				}

			}
			$message = $done;
			if ( is_numeric( $done ) ) {
				$message .= ' Items Done! Another round needed.';
			}
			echo '<p>' . $message . '</p>';

		}
		echo '</td></tr>';
	} else if ( empty( $done ) ) {
		echo '<tr>';
		echo '<td>Reassign Attributes</td>';
		$reassign_attributes_url = wp_nonce_url( admin_url( 'edit.php?post_type=al_product&page=system.php&reassign_all_products_attributes=1' ), 'ic_reassign_all_products_attributes' );
		echo '<td><a class="button" href="' . $reassign_attributes_url . '">Reassign attributes</a>';
		echo '</td></tr>';
	}
	if ( wp_get_schedule( 'ic_scheduled_attributes_clear' ) ) {
		if ( isset( $_GET['clear_products_attributes'] ) ) {
			ic_clear_empty_attributes();
		}
	}
	if ( wp_get_schedule( 'ic_scheduled_attributes_clear' ) ) {
		echo '<tr>';
		echo '<td>Clear Attributes</td>';
		echo '<td><a class="button" href="' . admin_url( 'edit.php?post_type=al_product&page=system.php&clear_products_attributes=1' ) . '">Speed UP Clearing Empty Attributes</a></td>';
		echo '</tr>';
	}
}

/**
 * Returns attribute ID by label
 *
 * @param type $label
 *
 * @return boolean
 */
function ic_get_attribute_id( $label ) {
	$term_id = get_attribute_label_id( $label );
	if ( ! empty( $term_id ) ) {
		return intval( $term_id );
	}

	return false;
}

/**
 * Returns attribute name when ID is provided
 *
 * @param int $attribute_id
 *
 * @return boolean|string
 */
function ic_get_attribute_name( $attribute_id ) {
	$cache_meta = 'attr_name' . $attribute_id;
	$attr_name  = ic_get_global( $cache_meta );
	if ( $attr_name !== false ) {
		return $attr_name;
	}
	$attribute = get_term_by( 'id', $attribute_id, 'al_product-attributes' );
	if ( $attribute && $attribute->count > 0 ) {
		$attr_name = $attribute->name;
		if ( ! empty( $attr_name ) ) {
			ic_save_global( $cache_meta, $attr_name );

			return $attr_name;
		}
	}

	return false;
}

/**
 * Returns available attribute values as array
 *
 * @param type $label
 *
 * @return boolean
 */
function ic_get_attribute_values( $label, $format = 'names', $current = false, $product_ids = array() ) {
	$attribute_id = ic_get_attribute_id( $label );
	if ( $attribute_id === false ) {

		return false;
	}
	$cache_key = 'attribute_values' . $label . $format;
	if ( ! empty( $product_ids ) ) {
		$cache_key .= md5( serialize( $product_ids ) );
	}
	$attributes = ic_get_global( $cache_key, true );
	if ( false === $attributes ) {
		$args = array(
			'taxonomy'     => 'al_product-attributes',
			'hide_empty'   => true,
			'parent'       => $attribute_id,
			'fields'       => 'id=>name',
			'ic_post_type' => array( get_current_screen_post_type() )
		);
		if ( ! empty( $product_ids ) && is_array( $product_ids ) ) {
			$args['object_ids'] = array_map( 'intval', $product_ids );
			$current            = false;
		}

		if ( $current ) {
			if ( is_product_filter_active( 'attribute_filter' ) && ic_exclude_tax_query( 'al_product-attributes', $label ) ) {
				$exclude_tax = array( 'al_product-attributes' );
			} else {
				$exclude_tax = array();
			}
			$current_products = ic_get_current_products( array(), $exclude_tax );
			if ( ! empty( $current_products ) && $current_products !== 'all' ) {
				$args['object_ids'] = $current_products;
				$cache_key          .= md5( serialize( $current_products ) );
				$attributes         = ic_get_global( $cache_key, true );
			} else if ( empty( $current_products ) ) {
				return false;
			}
		}
		/*
				if ( $current || ( ! empty( $current_products ) && $current_products === 'all' ) ) {
					$current_cache_meta = 'current_products_attributes' . intval( false ) . intval( true ) . intval( $attribute_id );
					if ( ! empty( $current_products ) ) {
						$current_cache_meta .= md5( serialize( $current_products ) );
					}
					$attributes = ic_get_global( $current_cache_meta );
				}
		*/
		if ( $attributes === false ) {
			$values = ic_get_terms( $args );
			if ( empty( $values ) || is_wp_error( $values ) || ! is_array( $values ) ) {
				return false;
			}
			$attributes = $values;
			if ( ! empty( $cache_key ) ) {
				ic_save_global( $cache_key, $attributes, false, true );
			}
		}
	}
	$cache_term_ids = ic_get_global( 'attr_value_id' );
	if ( $cache_term_ids === false ) {
		$cache_term_ids = array();
	}
	foreach ( $attributes as $term_id => $term_name ) {
		//$cache_meta                                    = 'attr_value_id' . $attribute_id . $term_name;
		$cache_term_ids[ $attribute_id ][ $term_name ] = $term_id;
		//ic_save_global( $cache_meta, $term_id );
	}
	ic_save_global( 'attr_value_id', $cache_term_ids );

	if ( $format === 'names' ) {
		$attributes = array_values( $attributes );
		if ( ! empty( $current_cache_meta ) ) {
			ic_save_global( $current_cache_meta, $attributes );
		}
	} else if ( $format === 'ids' ) {
		$attributes = array_keys( $attributes );
	}

	return $attributes;
}

/**
 * Sanitize attribute before adding as taxonomy
 *
 * @param type $attribute
 *
 * @return type
 */
function ic_sanitize_product_attribute( $attribute, $label = null ) {
	if ( is_array( $attribute ) ) {
		$sanitized_attribute = array();
		foreach ( $attribute as $key => $attr ) {
			$sanitized = ic_sanitize_product_attribute( $attr, $label );
			if ( ! empty( $sanitized ) ) {
				$sanitized_attribute[ $key ] = $sanitized;
			}
		}
		//$sanitized_attribute = array_map( 'ic_sanitize_product_attribute', $attribute );
		if ( ! empty( $sanitized_attribute[0] ) && is_array( $sanitized_attribute[0] ) ) {
			return $sanitized_attribute[0];
		}

		return $sanitized_attribute;
	} else if ( is_serialized( $attribute ) ) {
		$unserialized = unserialize( $attribute );
		if ( ! empty( $unserialized ) && is_array( $unserialized ) ) {
			return ic_sanitize_product_attribute( $unserialized, $label );
		}
	}
	$sanitized_attribute = trim( wp_unslash( sanitize_term_field( 'name', $attribute, 0, 'al_product-attributes', 'db' ) ) );
	if ( strlen( $sanitized_attribute ) > 200 ) {
		return '';
	}

	return apply_filters( 'ic_sanitize_product_attribute', $sanitized_attribute, $label );
}

function ic_delete_all_attribute_terms() {
	global $wpdb;
	$taxonomy = 'al_product-attributes';
	$terms    = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

	// Delete Terms
	if ( $terms ) {
		foreach ( $terms as $term ) {
			$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
			$wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
			$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
		}
	}
}

if ( ! function_exists( 'get_all_attribute_labels' ) ) {

	/**
	 * Returns all attributes labels
	 *
	 * @return type
	 */
	function get_all_attribute_labels() {
		$post_type         = get_current_screen_post_type();
		$cache_key         = 'all_attribute_labels_' . $post_type;
		$attributes_labels = ic_get_global( $cache_key );
		if ( false === $attributes_labels ) {
			$attributes_labels = ic_get_terms( array(
				'taxonomy'     => 'al_product-attributes',
				'parent'       => 0,
				'fields'       => 'names',
				'hide_empty'   => true,
				'ic_post_type' => array( $post_type )
			) );

			if ( ! empty( $cache_key ) ) {
				ic_save_global( $cache_key, $attributes_labels );
			}
		}

		return $attributes_labels;
	}

}

if ( ! function_exists( 'get_all_attribute_values' ) ) {

	/**
	 * Returns all attributes labels
	 *
	 * @return type
	 */
	function get_all_attribute_values( $product_id = null ) {
		if ( ! empty( $product_id ) ) {
			$post_type = get_post_type( $product_id );
		} else {
			$post_type = get_current_screen_post_type();
		}
		$cache_key         = 'all_attribute_values_' . $post_type . $product_id;
		$attributes_values = ic_get_global( $cache_key );
		if ( false === $attributes_values ) {
			$args = array(
				'taxonomy'   => 'al_product-attributes',
				'fields'     => 'names',
				'hide_empty' => true,
				'childless'  => true
			);
			if ( ! empty( $product_id ) ) {
				$args['object_ids'] = intval( $product_id );
			} else {
				$args['ic_post_type'] = array( $post_type );
			}

			$attributes_values = ic_get_terms( $args );
			if ( ! empty( $cache_key ) ) {
				ic_save_global( $cache_key, $attributes_values );
			}
		}

		return $attributes_values;
	}

}

add_filter( 'wp_unique_term_slug', 'ic_wp_unique_slug_bug_fix', 10, 2 );

function ic_wp_unique_slug_bug_fix( $slug, $term ) {
	global $wpdb;
	if ( ! empty( $term->term_id ) ) {
		$query = $wpdb->prepare( "SELECT slug FROM $wpdb->terms WHERE slug = %s AND term_id != %d", $slug, $term->term_id );
	} else {
		$query = $wpdb->prepare( "SELECT slug FROM $wpdb->terms WHERE slug = %s", $slug );
	}

	if ( $wpdb->get_var( $query ) ) {
		$num = 2;
		do {
			$alt_slug = $slug . "-$num";
			$num ++;
			$slug_check = $wpdb->get_var( $wpdb->prepare( "SELECT slug FROM $wpdb->terms WHERE slug = %s", $alt_slug ) );
		} while ( $slug_check );
		$slug = $alt_slug;
	}

	return $slug;
}

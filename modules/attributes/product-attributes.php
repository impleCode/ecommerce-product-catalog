<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
add_action( 'init', 'ic_create_product_attributes' );

/**
 * Registers attributes taxonomy
 *
 */
function ic_create_product_attributes() {
	$args		 = array(
		'label'			 => 'Attributes',
		'hierarchical'	 => true,
		'public'		 => false,
		'query_var'		 => false,
		'rewrite'		 => false,
	);
	$post_types	 = apply_filters( 'ic_attributes_register_post_types', product_post_type_array() );
	register_taxonomy( 'al_product-attributes', $post_types, $args );
}

/**
 * Adds product attribute label and returns attribute label ID
 *
 * @param type $label
 * @return type
 */
function ic_add_product_attribute_label( $label ) {
	$term = term_exists( $label, 'al_product-attributes', 0 );
	if ( empty( $term ) ) {
		$term = wp_insert_term( $label, 'al_product-attributes' );
	}
	if ( is_wp_error( $term ) ) {
		//print_r( $term );
		//exit;
	}
	return $term[ 'term_id' ];
}

/**
 * Adds product attribute value and returns attribute value ID
 *
 * @param type $label_id
 * @param type $value
 * @return type
 */
function ic_add_product_attribute_value( $label_id, $value ) {
	$term = term_exists( $value, 'al_product-attributes', $label_id );
	if ( empty( $term ) ) {
		$term = wp_insert_term( $value, 'al_product-attributes', array( 'parent' => $label_id ) );
	}
	return $term[ 'term_id' ];
}

add_filter( 'product_meta_save', 'ic_assign_product_attributes', 2, 2 );

/**
 * Adds product attributes to the database
 *
 * @param type $product_meta
 * @param type $post
 * @return type
 */
function ic_assign_product_attributes( $product_meta, $post, $clear_empty = true ) {
	$max_attr = product_attributes_number();
	if ( $max_attr > 0 ) {
		$product_id	 = isset( $post->ID ) ? $post->ID : $post;
		$attr_ids	 = array();

		for ( $i = 1; $i <= $max_attr; $i++ ) {
			if ( empty( $product_meta[ '_attribute' . $i ] ) || (isset( $product_meta[ '_attribute' . $i ][ 0 ] ) && empty( $product_meta[ '_attribute' . $i ][ 0 ] )) ) {
				continue;
			}
			if ( !empty( $product_meta[ '_attribute-label' . $i ] ) ) {
				//$label = is_array( $product_meta[ '_attribute-label' . $i ] ) ? ic_sanitize_product_attribute( $product_meta[ '_attribute-label' . $i ][ 0 ] ) : ic_sanitize_product_attribute( $product_meta[ '_attribute-label' . $i ] );
				$label = ic_sanitize_product_attribute( $product_meta[ '_attribute-label' . $i ] );
				if ( !empty( $label ) ) {
					//$value = is_array( $product_meta[ '_attribute' . $i ] ) ? ic_sanitize_product_attribute( $product_meta[ '_attribute' . $i ][ 0 ] ) : ic_sanitize_product_attribute( $product_meta[ '_attribute' . $i ] );
					$value = ic_sanitize_product_attribute( $product_meta[ '_attribute' . $i ] );
					if ( !empty( $value ) ) {
						$label_id = ic_add_product_attribute_label( $label );
						if ( !empty( $label_id ) ) {
							$attr_ids[] = $label_id;
							if ( !is_array( $value ) ) {
								$value = array( $value );
							}
							foreach ( $value as $val ) {
								$value_id	 = ic_add_product_attribute_value( $label_id, $val );
								$attr_ids[]	 = $value_id;
							}
						}
					}
				}
			}
		}
		if ( !empty( $attr_ids ) ) {
			$attr_ids = array_unique( array_map( 'intval', $attr_ids ) );
			wp_set_object_terms( $product_id, $attr_ids, 'al_product-attributes' );
			if ( $clear_empty ) {
				ic_clear_empty_attributes();
			}
		}
	}
	return $product_meta;
}

add_action( 'ic_scheduled_attributes_clear', 'ic_clear_empty_attributes' );

/**
 * Clears empty product attributes
 *
 */
function ic_clear_empty_attributes() {
	$max_attr	 = product_attributes_number();
	$attributes	 = get_terms( 'al_product-attributes', array(
		'orderby'	 => 'count',
		'hide_empty' => 0,
		'number'	 => $max_attr
	) );
	$schedule	 = false;
	if ( !empty( $attributes ) && is_array( $attributes ) && !is_wp_error( $attributes ) ) {
		foreach ( $attributes as $attribute ) {
			if ( $attribute->count == 0 ) {
				$schedule = true;
				wp_delete_term( $attribute->term_id, 'al_product-attributes' );
			} else {
				$schedule = false;
				break;
			}
		}
		if ( !wp_get_schedule( 'ic_scheduled_attributes_clear' ) && $schedule ) {
			wp_schedule_event( time(), 'hourly', 'ic_scheduled_attributes_clear' );
		} else {
			wp_clear_scheduled_hook( 'ic_scheduled_attributes_clear' );
		}
	} else {
		wp_clear_scheduled_hook( 'ic_scheduled_attributes_clear' );
	}
}

add_action( 'ic_scheduled_attributes_assignment', 'ic_reassign_all_products_attributes' );

/**
 * Scheduled even to reassign all products attributes
 *
 * @return string
 */
function ic_reassign_all_products_attributes() {
	$max_attr = product_attributes_number();
	if ( empty( $max_attr ) ) {
		return;
	}
	if ( !wp_get_schedule( 'ic_scheduled_attributes_assignment' ) ) {
		wp_schedule_event( time(), 'hourly', 'ic_scheduled_attributes_assignment' );
		return '';
	}
	$done		 = get_option( 'ic_product_upgrade_done', 0 );
	$products	 = get_all_catalog_products( 'date', 'ASC', 200, $done );
	$max_round	 = intval( 300 / $max_attr );
	if ( $max_round > 100 ) {
		$max_round = 100;
	}
	if ( $done > 100 ) {
		$max_round = apply_filters( 'ic_database_upgrade_max_round', $max_round * 2 );
	}
	$rounds = 1;
	foreach ( $products as $post ) {
		if ( $rounds > $max_round ) {
			break;
		}
		set_time_limit( 30 );
		$product_meta = get_post_meta( $post->ID );
		ic_assign_product_attributes( $product_meta, $post, false );
		$done++;
		$rounds++;
	}
	$products_count = ic_products_count();
	if ( $products_count > $done ) {
		update_option( 'ic_product_upgrade_done', $done );
	} else {
		delete_option( 'ic_product_upgrade_done' );
		wp_clear_scheduled_hook( 'ic_scheduled_attributes_assignment' );
		ic_clear_empty_attributes();
	}
}

add_action( 'ic_system_tools', 'ic_system_tools_attributes_upgrade' );

/**
 * Shows database upgrade button in system tools
 *
 */
function ic_system_tools_attributes_upgrade() {
	if ( wp_get_schedule( 'ic_scheduled_attributes_assignment' ) ) {
		if ( isset( $_GET[ 'reassign_all_products_attributes' ] ) ) {
			ic_reassign_all_products_attributes();
		}
	}
	if ( wp_get_schedule( 'ic_scheduled_attributes_assignment' ) ) {
		echo '<tr>';
		echo '<td>Database Upgrade</td>';
		echo '<td><a class="button" href="' . admin_url( 'edit.php?post_type=al_product&page=system.php&reassign_all_products_attributes=1' ) . '">Speed UP Pending Database Upgrade</a>';
		if ( isset( $_GET[ 'reassign_all_products_attributes' ] ) ) {
			$done = get_option( 'ic_product_upgrade_done', 0 );
			echo '<p>' . $done . ' Items Done! Another round needed.</p>';
		}
		echo '</td></tr>';
	}
	if ( wp_get_schedule( 'ic_scheduled_attributes_clear' ) ) {
		if ( isset( $_GET[ 'clear_products_attributes' ] ) ) {
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
 * @param type $label
 * @return boolean
 */
function ic_get_attribute_id( $label ) {
	$attribute = get_term_by( 'name', $label, 'al_product-attributes' );
	if ( $attribute ) {
		return intval( $attribute->term_id );
	}
	return false;
}

/**
 * Returns attribute name when ID is provided
 *
 * @param int $attribute_id
 * @return boolean|string
 */
function ic_get_attribute_name( $attribute_id ) {
	$attribute = get_term_by( 'id', $attribute_id, 'al_product-attributes' );
	if ( $attribute && $attribute->count > 0 ) {
		return $attribute->name;
	}
	return false;
}

/**
 * Returns available attribute values as array
 *
 * @param type $label
 * @return boolean
 */
function ic_get_attribute_values( $label, $format = 'names' ) {
	$attribute_id	 = ic_get_attribute_id( $label );
	//$values			 = get_term_children( $attribute_id, 'al_product-attributes' );
	$values			 = get_terms( array(
		'taxonomy'	 => 'al_product-attributes',
		'hide_empty' => true,
		'parent'	 => $attribute_id,
		'fields'	 => $format
	) );
	if ( empty( $values ) || is_wp_error( $values ) || !is_array( $values ) ) {
		return false;
	}
	/*
	  $attributes = array();
	  foreach ( $values as $value_id ) {
	  if ( !empty( $value_id ) ) {
	  $attr_name = ic_get_attribute_name( $value_id );
	  if ( $attr_name ) {
	  $attributes[] = $attr_name;
	  }
	  }
	  }
	 *
	 */
	$attributes = $values;
	return $attributes;
}

/**
 * Sanitize attribute before adding as taxonomy
 *
 * @param type $attribute
 * @return type
 */
function ic_sanitize_product_attribute( $attribute ) {
	if ( is_array( $attribute ) ) {
		$sanitized_attribute = array_filter( $attribute, 'ic_sanitize_product_attribute' );
		return $sanitized_attribute;
	}
	$sanitized_attribute = trim( wp_unslash( sanitize_term_field( 'name', $attribute, 0, 'al_product-attributes', 'db' ) ) );
	if ( strlen( $sanitized_attribute ) > 200 ) {
		return '';
	}
	return $sanitized_attribute;
}

function ic_delete_all_attribute_terms() {
	global $wpdb;
	$taxonomy	 = 'al_product-attributes';
	$terms		 = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

	// Delete Terms
	if ( $terms ) {
		foreach ( $terms as $term ) {
			$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
			$wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
			$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
		}
	}
}

if ( !function_exists( 'get_all_attribute_labels' ) ) {

	/**
	 * Returns all attrubutes labels
	 *
	 * @return type
	 */
	function get_all_attribute_labels() {
		$attributes_labels = get_terms( 'al_product-attributes', array( 'parent' => 0, 'fields' => 'names', 'hide_empty' => true ) );
		return $attributes_labels;
	}

}

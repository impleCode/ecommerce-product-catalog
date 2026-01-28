<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product duplication
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        impleCode
 */
add_action( 'admin_init', 'ic_duplicate_product' );

/**
 * Duplicates product
 *
 * @global type $wpdb
 */
function ic_duplicate_product() {
	if ( isset( $_GET['ic-duplicate'] ) && isset( $_GET['ic_duplicate_product_nonce'] ) && current_user_can( 'publish_products' ) ) {
		if ( ! wp_verify_nonce( $_GET['ic_duplicate_product_nonce'], 'ic_duplicate_product' ) ) {
			return;
		}
		global $wpdb;
		// Get variables
		$original_id = intval( $_GET['ic-duplicate'] );

		$duplicate = get_post( $original_id, 'ARRAY_A' );

		$duplicate['post_date']         = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
		$duplicate['post_date_gmt']     = date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ) );
		$duplicate['post_modified']     = date( 'Y-m-d H:i:s', current_time( 'timestamp', 0 ) );
		$duplicate['post_modified_gmt'] = date( 'Y-m-d H:i:s', current_time( 'timestamp', 1 ) );
		$duplicate['post_status']       = 'draft';
		unset( $duplicate['ID'] );
		unset( $duplicate['guid'] );
		unset( $duplicate['comment_count'] );
		unset( $duplicate['post_name'] );
		$duplicate_id = wp_insert_post( $duplicate );
		if ( ! is_wp_error( $duplicate_id ) ) {

			$taxonomies = get_object_taxonomies( $duplicate['post_type'] );
			foreach ( $taxonomies as $taxonomy ) {
				$terms = wp_get_post_terms( $original_id, $taxonomy, array( 'fields' => 'ids' ) );
				wp_set_object_terms( $duplicate_id, $terms, $taxonomy );
			}
			$custom_fields = get_post_custom( $original_id );
			foreach ( $custom_fields as $key => $value ) {
				if ( ic_duplicate_is_restricted( $key ) ) {
					continue;
				}
				if ( is_array( $value ) && count( $value ) > 0 ) {
					foreach ( $value as $i => $v ) {
						$wpdb->insert( $wpdb->prefix . 'postmeta', array(
							'post_id'    => $duplicate_id,
							'meta_key'   => $key,
							'meta_value' => $v
						) );
					}
				} else if ( ! empty( $value ) && ! is_array( $value ) ) {
					$wpdb->insert( $wpdb->prefix . 'postmeta', array(
						'post_id'    => $duplicate_id,
						'meta_key'   => $key,
						'meta_value' => $value
					) );
				}
			}
			$redirect_url = admin_url( 'edit.php?post_type=' . $duplicate['post_type'] . '&ic-duplicated=' . $duplicate_id );
			wp_redirect( $redirect_url );
			exit;
		}
	}
}

/**
 * Checks if the meta key is on the restricted keys list
 *
 * @param $meta_key
 *
 * @return bool
 */
function ic_duplicate_is_restricted( $meta_key ) {
	$restricted_keys = array(
		'_wp_old_slug',
		'_edit_lock',
		'_edit_last'
	);
	if ( in_array( $meta_key, $restricted_keys ) ) {
		return true;
	}

	return false;
}

add_filter( 'post_row_actions', 'ic_product_duplicator_action_row', 99, 2 );

/**
 * Adds duplication link
 *
 * @param array $actions
 * @param type $post
 *
 * @return string
 */
function ic_product_duplicator_action_row( $actions, $post ) {
	if ( ic_string_contains( $post->post_type, 'al_product' ) && current_user_can( 'publish_products' ) && ! isset( $actions['clone'] ) && ! isset( $actions['duplicate_post'] ) ) {
		$label = __( 'Duplicate', 'ecommerce-product-catalog' );
		$url   = wp_nonce_url( admin_url( 'edit.php?post_type=al_product&ic-duplicate=' . $post->ID ), 'ic_duplicate_product', 'ic_duplicate_product_nonce' );

		// Create a nonce & add an action
		$action  = '<a class="ic-duplicate-product" href="' . $url . '">' . $label . '</a>';
		$actions = array_slice( $actions, 0, 3, true ) + array( "duplicate_product" => $action ) + array_slice( $actions, 3, count( $actions ) - 1, true );
	}

	return $actions;
}

add_action( 'ic_catalog_admin_notices', 'ic_post_duplicator_notice' );

/**
 * Shows product duplication notice
 *
 */
function ic_post_duplicator_notice() {
	$ic_duplicated_id = isset( $_GET['ic-duplicated'] ) ? intval( $_GET['ic-duplicated'] ) : '';
	if ( ! empty( $ic_duplicated_id ) ) {
		$names = get_catalog_names();
		$link  = '<a href="' . get_edit_post_link( $ic_duplicated_id ) . '">' . __( 'here', 'ecommerce-product-catalog' ) . '</a>';
		$label = sprintf( __( '%1$s successfully duplicated! You can edit your new %2$s %3$s.', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ), ic_lcfirst( $names['singular'] ), $link );
		?>
        <div class="updated">
            <p><?php echo $label; ?></p>
        </div>
		<?php
	}
}

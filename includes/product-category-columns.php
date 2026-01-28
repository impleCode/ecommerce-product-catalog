<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product category columns
 *
 * Here all product category columns defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
add_action( 'al_product-cat_add_form', 'add_product_category_helper' );

/**
 * Adds info box on product category page
 *
 */
function add_product_category_helper() {
	doc_helper( __( 'category', 'ecommerce-product-catalog' ), 'product-categories', 'left' );
}

add_action( 'al_product-cat_edit_form', 'product_category_edit_form' );
add_action( 'al_product-cat_add_form', 'product_category_edit_form' );

function product_category_edit_form() {
	?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            jQuery('#edittag').attr("enctype", "multipart/form-data").attr("encoding", "multipart/form-data");
        });
    </script>
	<?php
}

add_action( 'edit_al_product-cat', 'save_product_cat_image' );
add_action( 'create_al_product-cat', 'save_product_cat_image' );

/**
 * Saves category image assignment option
 *
 * @param int $term_id
 */
function save_product_cat_image( $term_id ) {
	if ( isset( $_POST['product_cat_image'] ) ) {
		if ( function_exists( 'update_term_meta' ) ) {
			update_term_meta( $term_id, 'thumbnail_id', intval( $_POST['product_cat_image'] ) );
		} else {
			update_option( 'al_product_cat_image_' . $term_id, intval( $_POST['product_cat_image'] ) );
		}
		do_action( 'ic_save_product_category', $term_id );
	}
}

add_action( 'delete_al_product-cat', 'delete_product_cat_image' );

/**
 * Deletes category image assignmend from database
 *
 * @param int $term_id
 */
function delete_product_cat_image( $term_id ) {
	delete_option( 'al_product_cat_image_' . $term_id );
	if ( function_exists( 'delete_term_meta' ) ) {
		delete_term_meta( $term_id, 'thumbnail_id' );
	}
	do_action( 'ic_delete_product_category', $term_id );
}

add_action( 'al_product-cat_add_form_fields', 'product_category_add_form_fields' );

function product_category_add_form_fields( $field ) {
	if ( isset( $field->term_id ) ) {
		$cat_img_src = get_product_category_image_id( $field->term_id );
	} else {
		$cat_img_src = '';
	}
	implecode_upload_image( __( 'Category Image', 'ecommerce-product-catalog' ), 'product_cat_image', $cat_img_src, null, 'id' );
	do_action( 'ic_product_cat_fields', $field );
}

add_action( 'al_product-cat_edit_form_fields', 'product_category_edit_form_fields' );

function product_category_edit_form_fields( $field ) {
	if ( isset( $field->term_id ) ) {
		$cat_img_src = get_product_category_image_id( $field->term_id );
	} else {
		$cat_img_src = '';
	}
	echo '<tr class="form-field">';
	echo '<th scrope="row">';
	echo __( 'Category Image', 'ecommerce-product-catalog' );
	echo '</td>';
	echo '<td>';
	implecode_upload_image( __( 'Category Image', 'ecommerce-product-catalog' ), 'product_cat_image', $cat_img_src, null, 'id' );
	echo '</td>';
	echo '</tr>';
	do_action( 'ic_product_cat_fields_edit', $field );
}

add_filter( 'manage_edit-al_product-cat_columns', 'product_cat_columns' );

/**
 * Adds product category specific column names
 *
 * @param string $product_columns
 *
 * @return array
 */
function product_cat_columns( $product_columns ) {
	$product_columns = array_reverse( $product_columns );
	if ( ! isset( $product_columns['cb'] ) ) {
		return $product_columns;
	}
	$temp = $product_columns['cb'];
	unset( $product_columns['cb'] );
	unset( $product_columns['slug'] );
	$product_columns['img']       = __( 'Image', 'ecommerce-product-catalog' );
	$product_columns['id']        = __( 'ID', 'ecommerce-product-catalog' );
	$product_columns['cb']        = $temp;
	$product_columns              = array_reverse( $product_columns );
	$product_columns['shortcode'] = __( 'Shortcode', 'ecommerce-product-catalog' );

	return $product_columns;
}

add_action( 'manage_al_product-cat_custom_column', 'manage_product_category_columns', 10, 3 );

/**
 * Adds product category specific column values
 *
 * @param type $depr
 * @param string $column_name
 * @param int $term_id
 */
function manage_product_category_columns( $depr, $column_name, $term_id ) {
	switch ( $column_name ) {
		case 'img':
			$attachment_id = get_product_category_image_id( $term_id );
			echo wp_get_attachment_image( $attachment_id, array( 40, 40 ) );
			break;
		case 'id':
			echo $term_id;
			break;

		case 'shortcode':
			$term      = get_term( $term_id );
			$has_count = false;
			if ( empty( $term->count ) ) {
				$children = get_term_children( $term_id, $term->taxonomy );
				foreach ( $children as $child ) {
					$child_term = get_term( $child );
					if ( ! empty( $child_term->count ) ) {
						$has_count = true;
						break;
					}
				}
			} else {
				$has_count = true;
			}
			if ( $has_count ) {
				echo '[show_products category="' . $term_id . '"][show_categories include="' . $term_id . '"]';
			}
			break;

		default:
			break;
	}
}

/**
 * Returns category image ID
 *
 * @param int $cat_id
 *
 * @return int
 */
function get_product_category_image_id( $cat_id ) {
	$image_id = '';
	if ( is_numeric( $cat_id ) ) {
		if ( function_exists( 'get_term_meta' ) ) {
			$image_id = get_term_meta( $cat_id, 'thumbnail_id', true );
		}
		if ( empty( $image_id ) && ! metadata_exists( 'term', $cat_id, 'thumbnail_id' ) ) {
			$image_id = intval( get_option( 'al_product_cat_image_' . $cat_id ) );
			if ( empty( $image_id ) ) {
				$image_id = '';
			}
			if ( function_exists( 'update_term_meta' ) ) {
				update_term_meta( $cat_id, 'thumbnail_id', $image_id );
				delete_option( 'al_product_cat_image_' . $cat_id );
			}
		}
	}

	return apply_filters( 'ic_category_image_id', $image_id, $cat_id );
}

if ( ! function_exists( 'vtde_php_upgrade_notice' ) ) {

	add_action( 'al_product-cat_add_form_fields', 'ic_category_add_tinymce', 1, 0 );

	function ic_category_add_tinymce() {
		global $wp_filter;
		$settings = array(
			'textarea_name' => 'description',
			'textarea_rows' => 7,
			'editor_class'  => 'i18n-multilingual',
		);
		?>
        <div class="form-field term-description-wrap">
            <label for="tag-description"><?php _e( 'Description' ); ?></label>
			<?php
			wp_editor( '', 'html-tag-description', $settings );
			ic_category_editor_word_count();
			?>
            <p><?php _e( 'The description is not prominent by default; however, some themes may show it.' ); ?></p>

            <script type="text/javascript">
                // Remove the non-html field
                jQuery('textarea#tag-description').closest('.form-field').remove();

                jQuery(function () {
                    // Trigger save
                    jQuery('#addtag').on('mousedown', '#submit', function () {
                        tinyMCE.triggerSave();
                    });
                });

            </script>
        </div>
		<?php
	}

	add_action( 'al_product-cat_edit_form_fields', 'ic_category_edit_tinymce', 0, 1 );

	function ic_category_edit_tinymce( $tag ) {
		global $wp_filter;
		if ( ! empty( $wp_filter['al_product-cat_edit_form_fields']->callbacks ) ) {
			/* Check if other plugins are doing the same */
			foreach ( $wp_filter['al_product-cat_edit_form_fields']->callbacks as $key => $value ) {
				if ( ! empty( $value ) ) {
					foreach ( $value as $sub_key => $val ) {
						if ( ic_string_contains( $sub_key, 'category_description_editor' ) || ic_string_contains( $sub_key, 'tax_desc_wp_editor' ) ) {
							/* Rank Math or SEO Press is doing the same */
							return;
						}
					}

				}
			}
		}

		$settings = array(
			'textarea_name' => 'description',
			'textarea_rows' => 10,
			'editor_class'  => 'i18n-multilingual',
		);
		?>
        <tr class="form-field term-description-wrap">
            <th scope="row"><label for="description"><?php _e( 'Description' ); ?></label></th>
            <td>
				<?php
				wp_editor( htmlspecialchars_decode( $tag->description ), 'html-tag-description', $settings );
				ic_category_editor_word_count();
				?>
                <p class="description"><?php _e( 'The description is not prominent by default; however, some themes may show it.' ); ?></p>
            </td>
            <script type="text/javascript">
                // Remove the non-html field
                jQuery('textarea#description').closest('.form-field').remove();
            </script>
        </tr>
		<?php
	}

	function ic_category_editor_word_count() {
		?>
        <div id="post-status-info">
            <div id="description-word-count" class="hide-if-no-js" style="padding: 5px 10px;">
				<?php
				printf(
					__( 'Word count: %s' ), '<span class="word-count">0</span>'
				);
				?>
            </div>
        </div>
		<?php
	}

	add_action( 'admin_init', 'ic_category_remove_filters' );
	add_action( 'edit_terms', 'ic_category_remove_filters' );

	add_filter( 'pre_insert_term', 'ic_category_remove_filters', 10, 2 );

	function ic_category_remove_filters( $term_id = null, $taxonomy = null ) {
		if ( empty( $taxonomy ) ) {
			if ( ! empty( $term_id ) ) {
				$term = get_term( $term_id );
				if ( ! empty( $term->taxonomy ) ) {
					$taxonomy = $term->taxonomy;
				}
			} else if ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === 'al_product-cat' ) {
				$taxonomy = $_GET['taxonomy'];
			} else if ( isset( $_POST['action'] ) && $_POST['action'] === 'editedtag' && isset( $_POST['taxonomy'] ) && $_POST['taxonomy'] === 'al_product-cat' ) {
				$taxonomy = $_POST['taxonomy'];
			}
		}

		if ( current_user_can( 'edit_product_categories' ) && ( isset( $taxonomy ) && $taxonomy === 'al_product-cat' ) ) {

			/* Remove the filters which disallow HTML in term descriptions */
			remove_filter( 'pre_term_description', 'wp_filter_kses' );
			remove_filter( 'term_description', 'wp_kses_data' );

			/* Add filters to disallow unsafe HTML tags */
			if ( ! current_user_can( 'unfiltered_html' ) ) {
				add_filter( 'pre_term_description', 'wp_kses_post' );
				add_filter( 'term_description', 'wp_kses_post' );
			}
		}

		return $term_id;
	}

}
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
add_action( 'add_product_metaboxes', 'ic_attributes_metabox' );

/**
 * Adds attributes meatbox
 *
 * @param array $names
 */
function ic_attributes_metabox( $names ) {
	$names[ 'singular' ] = ic_ucfirst( $names[ 'singular' ] );
	if ( is_plural_form_active() ) {
		$labels[ 'attributes' ] = sprintf( __( '%s Attributes', 'ecommerce-product-catalog' ), $names[ 'singular' ] );
	} else {
		$labels[ 'attributes' ] = __( 'Attributes', 'ecommerce-product-catalog' );
	}
	$attr_num = product_attributes_number();
	if ( $attr_num > 0 ) {
		add_meta_box( 'al_product_attributes', $labels[ 'attributes' ], 'al_product_attributes', 'al_product', apply_filters( 'product_attributes_box_column', 'normal' ), apply_filters( 'product_attributes_box_priority', 'default' ) );
	}
}

/**
 * Shows attributes metabox
 *
 * @global type $post
 */
function al_product_attributes() {
	global $post;
	echo '<input type="hidden" name="attributesmeta_noncename" id="attributesmeta_noncename" value="' .
	wp_create_nonce( AL_BASE_PATH . 'attributes_meta' ) . '" />';
	echo '<div class="al-box info">' . __( 'Only attributes with values set will be shown on product page.', 'ecommerce-product-catalog' ) . ' ' . sprintf( __( 'See <a target="_blank" href="%s">docs</a>.', 'ecommerce-product-catalog' ), 'https://implecode.com/docs/ecommerce-product-catalog/product-attributes/?cam=catalog-add-page-box&key=product-attributes' ) . '</div>';
	do_action( 'before_product_attributes_edit_single' );
	echo '<table class="sort-settings attributes">
	<thead><tr>
	<th class="title"><b>Name</b></th>
	<th></th>
	<th class="title"><b>Value</b></th>
	<th class="title"><b>Unit</b></th>
	<th class="dragger"></th>
	</tr>
	</thead>
	<tbody><tr style="height: 6px;" class="ic-not-sortable"></tr>';
	do_action( 'inside_attributes_edit_table' );
	$attributes_option		 = get_option( 'product_attribute' );
	$attributes_label_option = get_option( 'product_attribute_label' );
	$attributes_unit_option	 = get_option( 'product_attribute_unit' );
	$attributes_number		 = product_attributes_number();
	$available_labels		 = get_all_attribute_labels();
	$label_autocomplete		 = '';
	if ( !empty( $available_labels ) ) {
		$label_autocomplete = 'data-ic-autocomplete="' . esc_attr( json_encode( $available_labels ) ) . '"';
	}
	for ( $i = 1; $i <= $attributes_number; $i++ ) {
		$attributes_option_field		 = get_attribute_value( $i, $post->ID ); //get_post_meta( $post->ID, '_attribute' . $i, true );
		$attributes_label_option_field	 = get_attribute_label( $i, $post->ID ); //get_post_meta( $post->ID, '_attribute-label' . $i, true );
		$attributes_unit_option_field	 = get_attribute_unit( $i, $post->ID ); //get_post_meta( $post->ID, '_attribute-unit' . $i, true );
		$attributes_option[ $i ]		 = isset( $attributes_option[ $i ] ) ? $attributes_option[ $i ] : '';
		$attributes_label_option[ $i ]	 = isset( $attributes_label_option[ $i ] ) ? $attributes_label_option[ $i ] : '';
		$attributes_unit_option[ $i ]	 = isset( $attributes_unit_option[ $i ] ) ? $attributes_unit_option[ $i ] : '';
		if ( is_ic_new_product_screen() ) {
			$attributes_option_field = !empty( $attributes_option_field ) ? $attributes_option_field : $attributes_option[ $i ];
		}
		$attributes_label_option_field	 = !empty( $attributes_label_option_field ) ? $attributes_label_option_field : $attributes_label_option[ $i ];
		$attributes_unit_option_field	 = !empty( $attributes_unit_option_field ) ? $attributes_unit_option_field : $attributes_unit_option[ $i ];
		$attribute_value_field			 = '';
		/*
		  if ( is_array( $attributes_option_field ) ) {
		  $attributes_option_field = '';
		  }
		 *
		 */
		$autocomplete					 = '';
		if ( !empty( $attributes_label_option_field ) ) {
			$available_attribute_values = ic_get_attribute_values( $attributes_label_option_field );
			if ( $available_attribute_values ) {
				$autocomplete = 'data-ic-autocomplete="' . esc_attr( json_encode( $available_attribute_values ) ) . '"';
			}
		}

		$field_value			 = is_array( $attributes_option_field ) ? $attributes_option_field[ 0 ] : $attributes_option_field;
		$attribute_value_field	 = '<input ' . $autocomplete . ' class="ic_autocomplete attribute-value" type="text" name="_attribute' . $i . '" value="' . esc_html( $field_value ) . '" />';
		?>
		<tr>
			<td class="attributes-label-column"><input <?php echo $label_autocomplete ?> class="ic_autocomplete attribute-label" type="text" name="_attribute-label<?php echo $i ?>" value="<?php echo esc_html( $attributes_label_option_field ) ?>"/></td>
			<td class="break-column">:</td>
			<td class="value-column"><?php echo apply_filters( 'product_attribute_value_edit', $attribute_value_field, $i, $attributes_option_field, $attributes_label_option_field ) ?></td>
			<td class="unit-column"><input class="attribute-unit admin-number-field" type="text"
										   name="_attribute-unit<?php echo $i ?>"
										   value="<?php echo esc_html( $attributes_unit_option_field ) ?>"/></td>
			<td class="dragger"></td>
		</tr>
	<?php } ?>
	</tbody>
	</table><?php
	do_action( 'product_attributes_edit_single', $post );
}

add_filter( 'product_meta_save', 'ic_save_product_attributes', 1, 2 );

/**
 * Saves product attributes
 *
 * @param type $product_meta
 * @return type
 */
function ic_save_product_attributes( $product_meta, $post ) {
	$max_attributes = product_attributes_number();
	for ( $i = 1; $i <= $max_attributes; $i++ ) {
		$product_meta[ '_attribute-label' . $i ] = !empty( $_POST[ '_attribute-label' . $i ] ) ? apply_filters( 'save_product_attribute_label', $_POST[ '_attribute-label' . $i ], $i, $post->ID ) : '';
		$product_meta[ '_attribute' . $i ]		 = !empty( $_POST[ '_attribute' . $i ] ) ? apply_filters( 'save_product_attribute_value', $_POST[ '_attribute' . $i ], $i, $post->ID, $product_meta[ '_attribute-label' . $i ] ) : '';
		$product_meta[ '_attribute-unit' . $i ]	 = !empty( $_POST[ '_attribute-unit' . $i ] ) ? apply_filters( 'save_product_attribute_unit', $_POST[ '_attribute-unit' . $i ], $i, $post->ID, $product_meta[ '_attribute-label' . $i ] ) : '';
	}
	return $product_meta;
}

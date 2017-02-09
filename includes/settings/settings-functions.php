<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product functions folder
 *
 * Here all plugin functions folder is defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes/settings
 * @author 		Norbert Dreszer
 */
if ( !function_exists( 'echo_ic_setting' ) ) {

	/**
	 * Shows radio buttons in tr and td tags
	 * @param string $option_label
	 * @param string $option_name
	 * @param string|int $option_value
	 * @param array $elements
	 * @param int $echo
	 * @param string $tip
	 * @return string
	 */
	function implecode_settings_radio( $option_label, $option_name, $option_value, $elements = array(), $echo = 1,
									$tip = '', $line = '<br>' ) {
		if ( !empty( $tip ) && !is_array( $tip ) ) {
			$tip = 'title="' . $tip . '"';
		}
		$return = '<tr>';
		$return .= '<td>' . $option_label . ':</td>';
		$return .= '<td class="ic_radio_td">';
		foreach ( $elements as $key => $element ) {
			$show_tip = is_array( $tip ) ? 'title="' . $tip[ $key ] . '" ' : $tip;
			$return .= '<input type="radio" ' . $show_tip . 'class="number_box" id="' . $option_name . '_' . $key . '" name="' . $option_name . '" value="' . $key . '"' . checked( $key, $option_value, 0 ) . '/>' . '<label for="' . $option_name . '_' . $key . '">' . $element . '</label>' . $line;
		}
		$return .= '</td>';
		$return .= '</tr>';

		return echo_ic_setting( $return, $echo );
	}

	function implecode_settings_dropdown( $option_label, $option_name, $option_value, $elements = array(), $echo = 1 ) {
		$return = '<tr>';
		$return .= '<td>' . $option_label . ':</td>';
		$return .= '<td>';
		$return .= '<select name="' . $option_name . '">';
		foreach ( $elements as $key => $element ) {
			$return .= '<option value="' . $key . '" ' . selected( $key, $option_value, 0 ) . '>' . $element . '</option>';
		}
		$return .= '</select>';
		$return .= '</td>';
		$return .= '</tr>';

		return echo_ic_setting( $return, $echo );
	}

	/**
	 * Displays checkbox as HTML table row
	 *
	 * @param string $option_label
	 * @param string $option_name
	 * @param int $option_enabled
	 * @param int $echo
	 * @param string $tip
	 * @return string
	 */
	function implecode_settings_checkbox( $option_label, $option_name, $option_enabled, $echo = 1, $tip = '', $value = 1 ) {
		if ( !empty( $tip ) && !is_array( $tip ) ) {
			$tip = 'title="' . $tip . '" ';
		}
		$return = '<tr>';
		$return .= '<td>' . $option_label . ':</td>';
		$return .= '<td><input ' . $tip . 'type="checkbox" name="' . $option_name . '" value="' . $value . '" ' . checked( 1, $option_enabled, 0 ) . '/></td>';
		$return .= '</tr>';

		return echo_ic_setting( $return, $echo );
	}

	/**
	 * Shows settings text fiels
	 *
	 * @param string $option_label
	 * @param string $option_name
	 * @param string|int $option_value
	 * @param string $required
	 * @param int $echo
	 * @param string $class
	 * @param string $tip
	 * @param string $disabled
	 * @return string
	 */
	function implecode_settings_text( $option_label, $option_name, $option_value, $required = null, $echo = 1,
								   $class = null, $tip = null, $disabled = '', $attributes = null ) {
		if ( !empty( $disabled ) ) {
			$disabled .= ' ';
		}
		if ( $required != '' ) {
			$regired_field	 = 'required="required"';
			$star			 = '<span class="star"> *</span>';
		} else {
			$regired_field	 = '';
			$star			 = '';
		}
		$tip	 = !empty( $tip ) ? 'title="' . $tip . '" ' : '';
		$return	 = '<tr>';
		if ( $option_label != '' ) {
			$return .= '<td>' . $option_label . $star . ':</td>';
		}
		$return .= '<td><input ' . $attributes . ' ' . $regired_field . ' ' . $disabled . 'class="' . $class . '" ' . $tip . 'type="text" name="' . $option_name . '" value="' . esc_html( $option_value ) . '" /></td>';
		$return .= '</tr>';

		return echo_ic_setting( $return, $echo );
	}

	/**
	 * Generates number field within table tr tags
	 *
	 * @param string $option_label
	 * @param string $option_name
	 * @param float $option_value
	 * @param string $unit
	 * @param int $echo
	 * @param float $step
	 * @param string $tip
	 * @param float $min
	 * @param float $max
	 * @return string
	 */
	function implecode_settings_number( $option_label, $option_name, $option_value, $unit, $echo = 1, $step = 1,
									 $tip = null, $min = null, $max = null ) {
		$return	 = '<tr>';
		$return .= '<td>' . $option_label . ':</td>';
		$tip	 = !empty( $tip ) ? 'title="' . $tip . '" ' : '';
		$min	 = isset( $min ) ? 'min="' . intval( $min ) . '" ' : '';
		$max	 = isset( $max ) ? 'max="' . intval( $max ) . '" ' : '';
		$return .= '<td><input type="number" step="' . $step . '" ' . $min . ' ' . $max . ' ' . $tip . ' class="number_box" name="' . $option_name . '" value="' . floatval( $option_value ) . '" />' . $unit . '</td>';
		$return .= '</tr>';

		return echo_ic_setting( $return, $echo );
	}

	function implecode_settings_textarea( $option_label, $option_name, $option_value, $echo = 1, $attr = null ) {
		$return = '<tr>';
		$return .= '<td>' . $option_label . ':</td>';
		$return .= '<td><textarea name="' . $option_name . '" ' . $attr . '>' . esc_textarea( $option_value ) . '</textarea></td>';
		$return .= '</tr>';

		return echo_ic_setting( $return, $echo );
	}

	function implecode_upload_image( $button_value, $option_name, $option_value, $default_image = null,
								  $upload_image_id = 'url', $echo = 1 ) {
		wp_enqueue_media();
		$option_value	 = !empty( $option_value ) ? $option_value : $default_image;
		$image_src		 = $option_value;
		if ( $upload_image_id != 'url' ) {
			$upload_image_id = 'id';
			if ( strpos( $image_src, 'http' ) === false ) {
				$image_src	 = wp_get_attachment_image_src( $option_value, 'medium' );
				$image_src	 = $image_src[ 0 ];
			}
		}
		$content = '<div class="custom-uploader">';
		$content .= '<input type="hidden" id="upload_type" value="' . $upload_image_id . '" />';
		$content .= '<input type="hidden" id="default" value="' . $default_image . '" />';
		$content .= '<input type="hidden" name="' . $option_name . '" id="uploaded_image" value="' . $option_value . '" />';
//if ($image_src != '') {
		$class	 = '';
		if ( $option_value == null ) {
			$class = 'empty';
		}
		$content .= '<div class="implecode-admin-media-image ' . $class . '">';
		$style = '';
		if ( $option_value == null ) {
			$style = 'style="display: none"';
		}
		$content .= '<span ' . $style . ' option_name="' . $option_name . '" class="catalog-reset-image-button">X</span>';
		$style = '';
		if ( empty( $image_src ) ) {
			$style = ' style="display: none"';
		}
		$content .= '<img' . $style . ' class="media-image" name="' . $option_name . '_image" src="' . $image_src . '" />';
		$content .= '</div>';
//}
		$style = '';
		if ( $option_value != null ) {
			$style = 'style="display: none"';
		}
		$content .= '<a ' . $style . ' href="#" class="button add_catalog_media" option_name="' . $option_name . '" name="' . $option_name . '_button" id="button_' . $option_name . '"><span class="wp-media-buttons-icon"></span> ' . $button_value . '</a>';
		$content .= '</div>';
		return echo_ic_setting( $content, $echo );
	}

	function echo_ic_setting( $return, $echo = 1 ) {
		if ( $echo == 1 ) {
			echo $return;
		} else {
			return $return;
		}
	}

	function implecode_warning( $text, $echo = 1 ) {
		return echo_ic_setting( '<div class="al-box warning">' . $text . '</div>', $echo );
	}

	function implecode_info( $text, $echo = 1, $p = 1 ) {
		$return = '<div class="al-box info">';
		if ( $p == 1 ) {
			$return .= '<p>' . $text . '</p>';
		} else {
			$return .= $text;
		}
		$return .= '</div>';

		return echo_ic_setting( $return, $echo );
	}

	function implecode_success( $text, $echo = 1 ) {
		return echo_ic_setting( '<div class="al-box success">' . $text . '</div>', $echo );
	}

	function implecode_plus( $text, $echo = 1 ) {
		return echo_ic_setting( '<div class="al-box plus">' . $text . '</div>', $echo );
	}

}

if ( !function_exists( 'implecode_settings_text_color' ) ) {

	function implecode_settings_text_color( $option_label, $option_name, $option_value, $required = null, $echo = 1,
										 $class = null, $change = null ) {
		if ( $required != '' ) {
			$regired_field	 = 'required="required"';
			$star			 = '<span class="star"> *</span>';
		} else {
			$regired_field	 = '';
			$star			 = '';
		}
		$return = '<tr>';
		$return .= '<td>' . $option_label . $star . ':</td>';
		$return .= '<td><input ' . $regired_field . ' class="color-picker ' . $class . '" type="text" name="' . $option_name . '" value="' . $option_value . '" /></td>';
		$return .= '<script>jQuery(document).ready(function() {jQuery("input[name=\'' . $option_name . '\']").wpColorPicker(' . $change . ');});</script>';
		$return .= '</tr>';
		return echo_ic_setting( $return, $echo );
	}

}
if ( !function_exists( 'ic_catalog_item_name' ) ) {

	/**
	 * Returns single catalog item name
	 *
	 * @return type
	 */
	function ic_catalog_item_name( $plural = true, $uppercase = false ) {
		if ( is_plural_form_active() ) {
			$names = get_catalog_names();
			if ( $plural ) {
				$item_name = $names[ 'plural' ];
			} else {
				$item_name = $names[ 'singular' ];
			}
		} else {
			if ( $plural ) {
				$item_name = __( 'items', 'ecommerce-product-catalog' );
			} else {
				$item_name = __( 'item', 'ecommerce-product-catalog' );
			}
		}
		if ( $uppercase ) {
			$item_name = ic_ucfirst( $item_name );
		} else {
			$item_name = ic_lcfirst( $item_name );
		}
		return $item_name;
	}

}

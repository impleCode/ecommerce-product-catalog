<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product functions folder
 *
 * Here all plugin functions folder is defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes/settings
 * @author        impleCode
 */
if ( ! function_exists( 'implecode_settings_radio' ) ) {

	/**
	 * Shows radio buttons in tr and td tags
	 *
	 * @param string $option_label
	 * @param string $option_name
	 * @param string|int $option_value
	 * @param array $elements
	 * @param int $echo
	 * @param string $tip
	 * @param string $line
	 * @param string $class
	 *
	 * @return string
	 */
	function implecode_settings_radio(
		$option_label, $option_name, $option_value, $elements = array(), $echo = 1,
		$tip = '', $line = '<br>', $class = "number_box", $table_row = true
	) {
		if ( empty( $option_label ) ) {
			$table_row = false;
			$tip       = '';
		}
		if ( ! empty( $tip ) && ! is_array( $tip ) ) {
			$tip_html = 'title="' . $tip . '"';
		}

		$return = '';
		if ( $table_row ) {
			$return .= '<tr>';
			$return .= '<td style="white-space: nowrap;vertical-align: top;">';
		}
		if ( ! empty( $tip_html ) ) {
			$return .= '<span ' . $tip_html . ' class="dashicons dashicons-editor-help ic_tip"></span>';
		}
		if ( ! empty( $option_label ) ) {
			$return .= $option_label . ':';
		}
		if ( $table_row ) {
			$return .= '</td>';
			$return .= '<td class="ic_radio_td" style="width:100%;">';
		}
		foreach ( $elements as $key => $element ) {
//$show_tip	 = is_array( $tip ) ? 'title="' . $tip[ $key ] . '" ' : $tip;
			$return .= '<div><span style="display:table-cell"><input type="radio" class="' . $class . '" id="' . $option_name . '_' . $key . '" name="' . $option_name . '" value="' . $key . '"' . checked( $key, $option_value, 0 ) . '></span>' . '<label for="' . $option_name . '_' . $key . '" style="display: table-cell">' . $element . '</label></div>';
		}
		if ( $table_row ) {
			$return .= '</td>';
			$return .= '</tr>';
		}
		ic_register_setting( $option_label, $option_name, $tip );

		return echo_ic_setting( $return, $echo );
	}

}
if ( ! function_exists( 'implecode_settings_dropdown' ) ) {

	function implecode_settings_dropdown(
		$option_label, $option_name, $option_value, $elements = array(), $echo = 1,
		$attr = null, $tip = null
	) {
		$return = '';
		if ( ! empty( $tip ) && ! is_array( $tip ) ) {
			$tip_html = 'title="' . $tip . '"';
		}
		if ( ! empty( $option_label ) ) {
			$return .= '<tr>';
			$return .= '<td>';
			if ( ! empty( $tip_html ) ) {
				$return .= '<span ' . $tip_html . ' class="dashicons dashicons-editor-help ic_tip"></span>';
			}
			$return .= $option_label . ':</td>';
			$return .= '<td>';
		}
		$class = '';
		if ( ! empty( $attr ) && ic_string_contains( $attr, 'multiple' ) ) {
			if ( ! ic_string_contains( $option_name, '[]' ) ) {
				$option_name .= '[]';
			}
			$class = 'ic_chosen';
		}
		if ( ! empty( $class ) ) {
			$class = 'class="' . $class . '" ';
		}
		$return            .= '<select name="' . $option_name . '" ' . $attr . ' ' . $class . '>';
		$this_option_value = $option_value;
		$associative       = false;
		if ( array_keys( $elements ) !== range( 0, count( $elements ) - 1 ) ) {
			$associative = true;
		}
		foreach ( $elements as $key => $element ) {
			if ( ! $associative ) {
				$key = $element;
			}
			if ( is_array( $option_value ) ) {
				if ( in_array( $key, $option_value ) ) {
					$this_option_value = $key;
				} else {
					$this_option_value = '';
				}
			}
			$return .= '<option value="' . $key . '" ' . selected( $key, $this_option_value, 0 ) . '>' . $element . '</option>';
		}
		$return .= '</select>';
		if ( ! empty( $option_label ) ) {
			$return .= '</td>';
			$return .= '</tr>';
		}
		ic_register_setting( $option_label, $option_name, $tip );

		return echo_ic_setting( $return, $echo );
	}

}
if ( ! function_exists( 'implecode_settings_checkbox' ) ) {

	/**
	 * Displays checkbox as HTML table row
	 *
	 * @param string $option_label
	 * @param string $option_name
	 * @param int $option_enabled
	 * @param int $echo
	 * @param string $tip
	 *
	 * @return string
	 */
	function implecode_settings_checkbox(
		$option_label, $option_name, $option_enabled, $echo = 1, $tip = '', $value = 1,
		$class = ''
	) {
		if ( ! empty( $tip ) && ! is_array( $tip ) ) {
			$tip_html = 'title="' . $tip . '" ';
		}
		$return = '';
		if ( ! empty( $option_label ) ) {
			$return .= '<tr>';
			$return .= '<td>';
		}

		if ( ! empty( $tip_html ) ) {
			$return .= '<span ' . $tip_html . ' class="dashicons dashicons-editor-help ic_tip"></span>';
		}
		if ( ! empty( $option_label ) ) {
			$return .= $option_label . ':</td>';
		}
		if ( ! empty( $class ) ) {
			$class = 'class="' . $class . '" ';
		}
		if ( ! empty( $option_label ) ) {
			$return .= '<td>';
		}
		$return .= '<input type="checkbox" ' . $class . 'name="' . $option_name . '" value="' . $value . '" ' . checked( $value, $option_enabled, 0 ) . '/>';
		if ( ! empty( $option_label ) ) {
			$return .= '</td>';
			$return .= '</tr>';

		}
		ic_register_setting( $option_label, $option_name, $tip );

		return echo_ic_setting( $return, $echo );
	}

}
if ( ! function_exists( 'implecode_settings_text' ) ) {

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
	 *
	 * @return string
	 */
	function implecode_settings_text(
		$option_label, $option_name, $option_value, $required = null, $echo = 1,
		$class = null, $tip = null, $disabled = '', $attributes = null, $type = 'text'
	) {
		if ( ! empty( $disabled ) ) {
			$disabled .= ' ';
		}
		if ( $required != '' ) {
			$regired_field = 'required="required"';
			$star          = '<span class="star"> *</span>';
		} else {
			$regired_field = '';
			$star          = '';
		}
		$tip_html = ! empty( $tip ) ? 'title="' . $tip . '" ' : '';
		$return   = '';
		if ( $option_label != '' ) {
			$return .= '<tr>';
			$return .= '<td>';
			if ( ! empty( $tip_html ) ) {
				$return .= '<span ' . $tip_html . ' class="dashicons dashicons-editor-help ic_tip"></span>';
			}
			$return .= $option_label . $star . ':</td>';
			$return .= '<td>';
		}
		$return .= '<input ' . $attributes . ' ' . $regired_field . ' ' . $disabled . 'class="' . $class . '" type="' . $type . '" name="' . $option_name . '" value="' . esc_html( $option_value ) . '" />';
		if ( $option_label != '' ) {
			$return .= '</td>';
			$return .= '</tr>';
		}
		ic_register_setting( $option_label, $option_name, $tip );

		return echo_ic_setting( $return, $echo );
	}

}
if ( ! function_exists( 'implecode_settings_number' ) ) {

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
	 *
	 * @return string
	 */
	function implecode_settings_number(
		$option_label, $option_name, $option_value, $unit, $echo = 1, $step = 1,
		$tip = null, $min = null, $max = null, $class = null, $tr_class = null, $attr = null
	) {
		if ( ! empty( $tr_class ) ) {
			$tr_class = ' class="' . $tr_class . '"';
		}
		$return = '';
		if ( ! empty( $option_label ) ) {
			$return .= '<tr' . $tr_class . '>';
			$return .= '<td>';
		}

		$class    = 'number_box ' . $class;
		$tip_html = ! empty( $tip ) ? 'title="' . $tip . '" ' : '';
		if ( ! empty( $tip_html ) ) {
			$return .= '<span ' . $tip_html . ' class="dashicons dashicons-editor-help ic_tip"></span>';
		}
		if ( ! empty( $option_label ) ) {
			$return .= $option_label . ':</td>';
		}
		$min = isset( $min ) ? 'min="' . floatval( $min ) . '" ' : '';
		$max = isset( $max ) ? 'max="' . floatval( $max ) . '" ' : '';
		if ( ! empty( $option_label ) ) {
			$return .= '<td>';
		}
		if ( ! empty( $attr ) ) {
			$attr = ' ' . $attr;
		}
		if ( $option_value !== '' ) {
			$option_value = floatval( $option_value );
		} else {
			$option_value = '';
		}
		$return .= '<input type="number" step="' . $step . '" ' . $min . ' ' . $max . ' class="' . $class . '" name="' . $option_name . '" value="' . $option_value . '"' . $attr . ' />' . $unit;
		if ( ! empty( $option_label ) ) {
			$return .= '</td>';
			$return .= '</tr>';
		}
		ic_register_setting( $option_label, $option_name, $tip );

		return echo_ic_setting( $return, $echo );
	}

}
if ( ! function_exists( 'implecode_settings_textarea' ) ) {

	function implecode_settings_textarea( $option_label, $option_name, $option_value, $echo = 1, $attr = null, $tip = null ) {
		$return = '';
		if ( ! empty( $option_label ) ) {
			$return .= '<tr>';
			$return .= '<td>';
		}

		if ( ! empty( $tip ) ) {
			$tip_html = ! empty( $tip ) ? 'title="' . $tip . '" ' : '';
			$return   .= '<span ' . $tip_html . ' class="dashicons dashicons-editor-help ic_tip"></span>';
		}
		if ( ! empty( $option_label ) ) {
			$return .= $option_label . ':</td>';
			$return .= '<td>';
		}
		$return .= '<textarea name="' . $option_name . '" ' . $attr . '>' . esc_textarea( $option_value ) . '</textarea>';
		if ( ! empty( $option_label ) ) {
			$return .= '</td>';
			$return .= '</tr>';
		}
		ic_register_setting( $option_label, $option_name, $tip );

		return echo_ic_setting( $return, $echo );
	}

}
if ( ! function_exists( 'implecode_upload_image' ) ) {

	function implecode_upload_image(
		$button_value, $option_name, $option_value, $default_image = null,
		$upload_image_id = 'url', $echo = 1, $id = ''
	) {
		if ( function_exists( 'get_current_screen' ) ) {
			$current_screen = get_current_screen();
		}
		if ( empty( $current_screen->id ) || $current_screen->id !== 'widgets' ) {
			wp_enqueue_media();
			if ( function_exists( 'wp_enqueue_editor' ) ) {
				wp_enqueue_editor();
			}
		}
		if ( empty( $id ) ) {
			$id = sanitize_title( $option_name );
		}
		$option_value = ! empty( $option_value ) ? $option_value : $default_image;
		$image_src    = $option_value;
		if ( ! empty( $option_value ) && $upload_image_id != 'url' ) {
			$upload_image_id = 'id';
			if ( empty( $image_src ) || strpos( $image_src, 'http' ) === false ) {
				$image_src = wp_get_attachment_image_src( $option_value, 'medium' );
				if ( ! empty( $image_src[0] ) ) {
					$image_src = $image_src[0];
				} else {
					$image_src = '';
				}
			}
		}
		$class = '';
		if ( $option_value != null ) {
			$class = 'active-image';
		}
		$content = '<div class="custom-uploader ' . $class . '">';
		$content .= '<input type="hidden" class="upload_type" id="upload_type" value="' . $upload_image_id . '" />';
		$content .= '<input type="hidden" class="default" id="default" value="' . $default_image . '" />';
		$content .= '<input type="hidden" name="' . $option_name . '" class="uploaded_image" id="' . $id . '" value="' . $option_value . '" />';
//if ($image_src != '') {
		$class = '';
		if ( $option_value == null ) {
			$class = 'empty';
		}
		$content .= '<div class="implecode-admin-media-image ' . $class . '">';
		$style   = '';
		if ( $option_value == null ) {
			$style = 'style="display: none"';
		}
		$content .= '<span ' . $style . ' option_name="' . $option_name . '" class="catalog-reset-image-button">X</span>';
		$style   = '';
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
		if ( ic_string_contains( $option_name, '[]' ) ) {
			$normal_name = str_replace( '[]', '', $option_name );
			$link_name   = $normal_name . '_button[]';
			global $ic_image_upload_count;
			if ( empty( $ic_image_upload_count ) ) {
				$ic_image_upload_count = 0;
			} else {
				$ic_image_upload_count ++;
			}
			$link_id     = 'button_' . $normal_name . '_' . $ic_image_upload_count;
			$option_name = str_replace( '[]', '[' . $ic_image_upload_count . ']', $option_name );
		} else {
			$link_name = $option_name . '_button';
			$link_id   = 'button_' . $option_name;
		}
		$content .= '<a ' . $style . ' href="#" class="button add_catalog_media" option_name="' . $option_name . '" name="' . $link_name . '" id="' . $link_id . '"><span class="wp-media-buttons-icon"></span> ' . $button_value . '</a>';
		$content .= '<div class="image-label">' . $button_value . '</div>';
		$content .= '</div>';
		ic_register_setting( $button_value, $option_name );

		return echo_ic_setting( $content, $echo );
	}

}
if ( ! function_exists( 'echo_ic_setting' ) ) {

	function echo_ic_setting( $return, $echo = 1 ) {
		if ( $echo == 1 ) {
			echo $return;
		} else {
			return $return;
		}
	}

}
if ( ! function_exists( 'implecode_warning' ) ) {

	function implecode_warning( $text, $echo = 1 ) {
		if ( ! is_ic_admin() ) {
			$text = do_shortcode( $text );
		}

		return echo_ic_setting( '<div class="al-box warning">' . $text . '</div>', $echo );
	}

}
if ( ! function_exists( 'implecode_info' ) ) {

	function implecode_info( $text, $echo = 1, $p = 0, $dismisable = true ) {
		$return = '';
		if ( ! is_ic_admin() ) {
			$text = do_shortcode( $text );
		}
		if ( $p == 1 ) {
			$return .= '<p>' . $text . '</p>';
		} else {
			$return .= $text;
		}
		if ( $dismisable && is_ic_admin() ) {
			$return .= '<span class="notice-dismiss"><span class="screen-reader-text">' . __( 'Dismiss this notice.', 'ecommerce-product-catalog' ) . '</span></span>';
		}
		$hash = ic_message_hash( $return );
		if ( ! ic_is_message_hidden( $return ) ) {
			$return = '<div class="al-box info" data-hash="' . $hash . '">' . $return . '</div>';

			return echo_ic_setting( $return, $echo );
		}
	}

}
if ( ! function_exists( 'implecode_success' ) ) {

	function implecode_success( $text, $echo = 1, $p = 1 ) {
		$return = '<div class="al-box success">';
		if ( ! is_ic_admin() ) {
			$text = do_shortcode( $text );
		}
		if ( $p == 1 ) {
			$return .= '<p>' . $text . '</p>';
		} else {
			$return .= $text;
		}
		$return .= '</div>';

		return echo_ic_setting( $return, $echo );
	}

}
if ( ! function_exists( 'implecode_plus' ) ) {

	function implecode_plus( $text, $echo = 1 ) {
		return echo_ic_setting( '<div class="al-box plus">' . $text . '</div>', $echo );
	}

}
if ( ! function_exists( 'ic_is_message_hidden' ) ) {

	function ic_is_message_hidden( $message ) {
		$hidden = get_option( 'ic_hidden_boxes', array() );
		if ( ! is_array( $hidden ) ) {
			$hidden = array();
		}
		$hash = ic_message_hash( $message );
		if ( in_array( $hash, $hidden ) ) {
			return true;
		}
		if ( get_current_user_id() ) {
			$user_hidden = get_user_meta( get_current_user_id(), '_ic_hidden_boxes', true );
			if ( ! is_array( $user_hidden ) ) {
				$user_hidden = array();
			}
			if ( in_array( $hash, $user_hidden ) ) {
				return true;
			}
		}

		return false;
	}

}
if ( ! function_exists( 'ic_ajax_hide_message' ) ) {

	add_action( 'wp_ajax_ic_ajax_hide_message', 'ic_ajax_hide_message' );

	function ic_ajax_hide_message( $message ) {
		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ic-ajax-nonce' ) ) {
			if ( ! empty( $_POST['hash'] ) ) {
				$hash = strval( $_POST['hash'] );
				if ( get_current_user_id() ) {
					$hidden = get_user_meta( get_current_user_id(), '_ic_hidden_boxes', true );
				} else {
					$hidden = get_option( 'ic_hidden_boxes', array() );
				}
				if ( ! is_array( $hidden ) ) {
					$hidden = array();
				}
				if ( ! in_array( $hash, $hidden ) ) {
					$hidden[] = $hash;
					if ( get_current_user_id() ) {
						update_user_meta( get_current_user_id(), '_ic_hidden_boxes', $hidden );
					} else {
						update_option( 'ic_hidden_boxes', $hidden, false );
					}
				}
			}
		}
		wp_die();
	}

}
if ( ! function_exists( 'ic_message_hash' ) ) {

	function ic_message_hash( $message ) {
		return hash( 'md5', stripslashes( $message ) );
	}

}

if ( ! function_exists( 'implecode_settings_text_color' ) ) {

	function implecode_settings_text_color(
		$option_label, $option_name, $option_value, $required = null, $echo = 1,
		$class = null, $change = null
	) {
		if ( $required != '' ) {
			$regired_field = 'required="required"';
			$star          = '<span class="star"> *</span>';
		} else {
			$regired_field = '';
			$star          = '';
		}
		$return = '<tr>';
		$return .= '<td>' . $option_label . $star . ':</td>';
		$return .= '<td><input ' . $regired_field . ' class="color-picker ' . $class . '" type="text" name="' . $option_name . '" value="' . $option_value . '" /></td>';
		$return .= '<script>jQuery(document).ready(function() {jQuery("input[name=\'' . $option_name . '\']").wpColorPicker(' . $change . ');});</script>';
		$return .= '</tr>';
		ic_register_setting( $option_label, $option_name );

		return echo_ic_setting( $return, $echo );
	}

}
if ( ! function_exists( 'ic_catalog_item_name' ) ) {

	/**
	 * Returns single catalog item name
	 *
	 * @return type
	 */
	function ic_catalog_item_name( $plural = true, $uppercase = false ) {
		if ( is_plural_form_active() ) {
			$names = get_catalog_names();
			if ( $plural ) {
				$item_name = $names['plural'];
			} else {
				$item_name = $names['singular'];
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

if ( ! function_exists( 'ic_select_page' ) ) {

	function ic_select_page(
		$option_name, $first_option, $selected_value, $buttons = false, $custom_view_url = false,
		$echo = 1, $custom = false, $custom_content = '', $create_new_button = false, $multiple = false, $class = ''
	) {
		if ( ( empty( $selected_value ) || $selected_value === 'noid' ) && ! empty( $create_new_button ) && is_array( $create_new_button ) ) {
			if ( ! empty( $_GET['ic_create_new_page_for_settings'] ) && urldecode( $_GET['ic_create_new_page_for_settings'] ) === $option_name ) {
				$selected_value = ic_create_page_for_settings( $create_new_button['title'], $create_new_button['content'], $create_new_button['option'], $create_new_button['option_sub'] );
			}
			if ( ( empty( $_GET['ic_create_new_page_for_settings'] ) || ( ! empty( $_GET['ic_create_new_page_for_settings'] ) && urldecode( $_GET['ic_create_new_page_for_settings'] ) !== $option_name ) ) && ( empty( $selected_value ) || $selected_value === 'noid' ) ) {
				$custom_content .= ' <a class="button button-small" style="vertical-align: middle;" href="' . esc_url( add_query_arg( 'ic_create_new_page_for_settings', $option_name ) ) . '">' . __( 'Create New', 'ecommerce-product-catalog' ) . '</a>';
			}
		}
		$args  = array(
			'orderby'        => 'title',
			'order'          => 'asc',
			'post_type'      => 'page',
			'post_status'    => array( 'publish', 'private' ),
			'posts_per_page' => - 1,
		);
		$pages = get_posts( apply_filters( 'ic_settings_select_page_args', $args ) );
		$attr  = '';
		if ( $multiple ) {
			$attr .= ' multiple';
		}
		$select_box = '<div class="select-page-wrapper"><select id="' . $option_name . '" name="' . $option_name . '" class="' . $class . '"' . $attr . '><option value = "noid">' . $first_option . '</option>';
		foreach ( $pages as $page ) {
			$selected = '';
			if ( $multiple && is_array( $selected_value ) ) {
				if ( in_array( $page->ID, $selected_value ) ) {
					$selected = 'selected';
				}
			} else {
				$selected = selected( $page->ID, $selected_value, 0 );
			}
			$select_box .= '<option name="' . $option_name . '[' . $page->ID . ']" value="' . $page->ID . '" ' . $selected . '>' . $page->post_title . '</option>';
		}
		if ( $custom ) {
			$select_box .= '<option value="custom"' . selected( 'custom', $selected_value, 0 ) . '>' . __( 'Custom URL', 'ecommerce-product-catalog' ) . '</option>';
		}
		$select_box .= '</select>';
		if ( $buttons && ( $selected_value != 'noid' || $custom_view_url != '' ) ) {
			$edit_link  = get_edit_post_link( $selected_value );
			$front_link = $custom_view_url ? $custom_view_url : get_permalink( $selected_value );
			if ( ! empty( $edit_link ) ) {
				$select_box .= ' <a class="button button-small" style="vertical-align: middle;" href="' . $edit_link . '">' . __( 'Edit' ) . '</a>';
			}
			if ( ! empty( $front_link ) ) {
				$select_box .= ' <a class="button button-small" style="vertical-align: middle;" href="' . $front_link . '">' . __( 'View Page' ) . '</a>';
			}
		}
		$select_box .= $custom_content;
		$select_box .= '</div>';
		ic_register_setting( $first_option, $option_name );

		return echo_ic_setting( $select_box, $echo );
	}

}

if ( ! function_exists( 'ic_create_page_for_settings' ) ) {

	function ic_create_page_for_settings( $title, $content, $option, $option_sub = null ) {

		if ( ! current_user_can( 'publish_pages' ) ) {
			return;
		}
		if ( empty( $option ) ) {
			return;
		}
		if ( ! empty( $option_sub ) ) {
			$current_option  = get_option( $option );
			$current_page_id = isset( $current_option[ $option_sub ] ) ? $current_option[ $option_sub ] : '';
		} else {
			$current_page_id = get_option( $option );
		}
		if ( ! empty( $current_page_id ) && $current_page_id !== 'noid' ) {
			return;
		}

		$product_page = array(
			'post_title'     => $title,
			'post_type'      => 'page',
			'post_content'   => $content,
			'post_status'    => 'publish',
			'comment_status' => 'closed'
		);

		$page_id = wp_insert_post( $product_page );
		if ( ! is_wp_error( $page_id ) ) {
			if ( ! empty( $option_sub ) ) {
				$option_value = get_option( $option );
				if ( empty( $option_value ) ) {
					$option_value = array();
				}
				if ( ! is_array( $option_value ) ) {
					return;
				}
				$option_value[ $option_sub ] = $page_id;
			} else {
				$option_value = $page_id;
			}
			update_option( $option, $option_value );
		}

		return $page_id;
	}

}
if ( ! function_exists( 'select_page' ) ) {

	function select_page(
		$option_name, $first_option, $selected_value, $buttons = false, $custom_view_url = false,
		$echo = 1, $custom = false
	) {
		return ic_select_page( $option_name, $first_option, $selected_value, $buttons, $custom_view_url,
			$echo, $custom );
	}

}
if ( ! function_exists( 'ic_register_setting' ) ) {

	function ic_register_setting( $option_label, $option_name, $option_tip = '' ) {
		if ( empty( $option_label ) || empty( $option_name ) ) {
			return;
		}
		if ( ! is_ic_admin() ) {
			return;
		}
		if ( empty( $_GET['page'] ) ) {
			return;
		}
		if ( $_GET['page'] !== 'product-settings.php' ) {
			return;
		}
		$url_args = array(
			'option_label' => $option_label,
			'option_tip'   => $option_tip,
			'tab'          => '',
			'submenu'      => ''
		);
		if ( ! empty( $_GET['tab'] ) ) {
			$url_args['tab'] = sanitize_text_field( $_GET['tab'] );
		}
		if ( ! empty( $_GET['submenu'] ) ) {
			$url_args['submenu'] = sanitize_text_field( $_GET['submenu'] );
		}
		$registered_settings = ic_get_registered_settings();

		global $ic_submenu_settings_updated;
		if ( empty( $ic_submenu_settings_updated ) ) {
			foreach ( $registered_settings as $key => $setting ) {
				if ( $setting['tab'] === $url_args['tab'] && $setting['submenu'] === $url_args['submenu'] ) {
					unset( $registered_settings[ $key ] );
				}
			}
		}
		$ic_submenu_settings_updated         = 1;
		$registered_settings[ $option_name ] = $url_args;
		update_option( 'ic_registered_settings', $registered_settings, false );
	}

}

if ( ! function_exists( 'ic_get_registered_settings' ) ) {

	function ic_get_registered_settings() {
		$registered_settings = get_option( 'ic_registered_settings', array() );
		if ( ! is_array( $registered_settings ) ) {
			$registered_settings = array();
		}

		return $registered_settings;
	}

}

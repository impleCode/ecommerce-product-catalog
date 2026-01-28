<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages formbulder function
 *
 * Here formbulder functions are defined and managed.
 *
 * @version        1.0.0
 * @package        formbuilder/
 * @author        Norbert Dreszer
 */
if ( ! defined( 'IC_FORMBUILDER_URL' ) ) {
	define( 'IC_FORMBUILDER_URL', plugins_url( '/', __FILE__ ) );
	define( 'IC_FORMBUILDER_PATH', dirname( __FILE__ ) );
	require_once( IC_FORMBUILDER_PATH . '/pluggable.php' );
	require_once( IC_FORMBUILDER_PATH . '/recaptcha/index.php' );

	add_action( 'register_catalog_admin_scripts', 'form_builder_register_admin_styles' );

	function form_builder_register_admin_styles() {
		wp_register_style( 'implecode-form-css', IC_FORMBUILDER_URL . 'dist/ic-form.min.css' . ic_filemtime( IC_FORMBUILDER_PATH . '/dist/ic-form.min.css' ) );
		wp_register_script( 'implecode-form-js', IC_FORMBUILDER_URL . 'dist/formbuilder-front.js' . ic_filemtime( IC_FORMBUILDER_PATH . '/dist/formbuilder-front.js' ), array(
			'jquery',
		) );
	}

	add_action( 'register_catalog_styles', 'form_builder_register_styles' );

	function form_builder_register_styles() {
		wp_register_style( 'implecode-form-css', IC_FORMBUILDER_URL . 'dist/ic-form.min.css' . ic_filemtime( IC_FORMBUILDER_PATH . '/dist/ic-form.min.css' ) );
		wp_register_script( 'implecode-form-js', IC_FORMBUILDER_URL . 'dist/formbuilder-front.min.js' . ic_filemtime( IC_FORMBUILDER_PATH . '/dist/formbuilder-front.min.js' ), array(
			'jquery',
		) );
	}

	add_action( 'enqueue_catalog_scripts', 'form_builder_enqueue_styles' );

	function form_builder_enqueue_styles() {
		wp_enqueue_style( 'implecode-form-css' );
		wp_enqueue_script( 'implecode-form-js' );
	}

	function formbuilder_form(
		$form_fields, $pre_name = 'ic_form_', $before_button = '', $button_text = null,
		$form_type = 'label-left', $show_form_after = true, $success_message = null, $line = "\n", $send_mail = true,
		$message_sender = null, $message_receiver = null, $message_topic = null, $button_class = 'classic-button',
		$send_customer_email = true, $customer_message_topic = null, $message_sender_name = null, $redirect = null
	) {
		$content = ic_get_global( 'formbuilder_submit_content_' . $pre_name );
		if ( $content ) {
			return $content;
		} else {
			$content = '';
		}
		$button_text = empty( $button_text ) ? __( 'Submit' ) : $button_text;
		$small_count = 0;
		$contact     = json_decode( $form_fields );
		$show_form   = true;
		$disabled    = '';
		if ( isset( $_POST[ $pre_name . 'submit' ] ) && ( ! isset( $_POST['ic_autocheck'] ) || empty( $_POST['ic_autocheck'] ) ) && ic_get_global( $pre_name . 'submit_done' ) === false ) {
			ic_save_global( $pre_name . 'submit_done', 1 );
			global $submitted_form_name;
			$submitted_form_name = $pre_name;
			$message             = '';
			$error               = false;
			$customer_email      = '';
			$error_output        = array();
			$template            = false;
			if ( $line == '<br>' ) {
				$template = true;
			}
			$before_header = apply_filters( 'ic_catalog_notification_before_header', '_____________________________________________' . $line, $template );
			$after_header  = apply_filters( 'ic_catalog_notification_after_header', $line . $line, $template );

			$attachments = array();
			global $ic_formbuilder_filled_fields;
			if ( $template ) {
				$message .= '<table style="border: 0">';
			}
			foreach ( $contact->fields as $field ) {
				if ( ! is_formbuilder_field_email_visible( $field, $pre_name ) ) {
					continue;
				}
				if ( $template ) {
					$message .= '<tr>';
				}
				$field_id = apply_filters( 'ic_formbuilder_cid', $pre_name . $field->cid, $field, $pre_name );
				if ( isset( $_POST[ $field_id ] ) && ! empty( $_POST[ $field_id ] ) ) {
					$ic_formbuilder_filled_fields[ $field_id ] = is_array( $_POST[ $field_id ] ) ? array_map( 'sanitize_text_field', $_POST[ $field_id ] ) : sanitize_text_field( $_POST[ $field_id ] );
					if ( $field->field_type == 'section_break' ) {
						if ( $template ) {
							$message .= '<td colspan="2" style="font-weight: bold; text-align:left; padding-top:20px;">';
						}
						//$message .= $before_header;
						$message .= strtoupper( sanitize_text_field( $field->label ) );
						//$message .= $after_header;
						if ( $template ) {
							$message .= '</td>';
						}
					} else if ( $field->field_type == 'checkboxes' && is_array( $_POST[ $field_id ] ) ) {
						$field_value = apply_filters( 'ic_formbuilder_message_field_value', implode( ' ', array_map( 'sanitize_text_field', $_POST[ $field_id ] ) ), $field_id, $field->field_type );
						$message     .= ic_message_row( $field, $field_value, $field_id, $line, $template );
					} else {
						$field_value = apply_filters( 'ic_formbuilder_message_field_value', sanitize_text_field( $_POST[ $field_id ] ), $field_id, $field->field_type );
						if ( $field->field_type == 'price' ) {
							$field_value = price_format( $field_value );
						}
						$message .= ic_message_row( $field, $field_value, $field_id, $line, $template );
					}
					if ( $field->field_type == 'email' ) {
						if ( is_email( $_POST[ $field_id ] ) ) {
							$customer_email = sanitize_email( $_POST[ $field_id ] );
						} else {
							$error                     = true;
							$error_output[ $field_id ] = $field->label;
						}
					}
				} else if ( isset( $field->required ) && $field->required ) {
					if ( $field->field_type === 'dropdown_state' ) {
						$selected_country = ic_formbuilder_get_country( $contact->fields, $pre_name );
						$states           = ic_dropdown_state_options( $selected_country );
						if ( ! empty( $states ) ) {
							$error                     = true;
							$error_output[ $field_id ] = $field->label;
						}
					} else {
						$error                     = true;
						$error_output[ $field_id ] = $field->label;
					}
				}

				if ( $template ) {
					$message .= '</tr>';
				}
			}
			if ( $template ) {
				$message .= '</table>';
			}
			$ic_formbuilder_filled_fields = apply_filters( 'ic_formbuilder_filled_fields', $ic_formbuilder_filled_fields );
			$error                        = apply_filters( 'ic_formbuilder_error', $error );
			$error_output                 = apply_filters( 'ic_formbuilder_error_output', $error_output );

			if ( ! $error ) {
				$disabled = ' disabled';
				if ( ! is_email( $customer_email ) && function_exists( 'ic_get_digital_customer_email' ) ) {
					$customer_email = ic_get_digital_customer_email();
				}
				do_action( 'ic_formbuilder_before_mail', $message, $customer_email, $redirect, $contact, $pre_name );
				if ( $send_mail ) {

					if ( is_email( $message_sender ) ) {

						$message_topic = isset( $message_topic ) ? $message_topic : __( 'Message from Website' );
						$message_topic = apply_filters( 'admin_message_topic', $message_topic );
						$admin_message = apply_filters( 'ic_formbuilder_admin_email', $message, $pre_name );
						$attachments   = apply_filters( 'ic_formbuilder_admin_attachments', $attachments, $pre_name, $customer_email );
						$test          = ic_mail( $admin_message, $customer_email, $message_sender, $message_receiver, $message_topic, $template, $attachments );
						if ( $send_customer_email ) {
							$customer_message_topic = isset( $customer_message_topic ) ? $customer_message_topic : __( 'Confirmation' );
							$customer_message_topic = apply_filters( 'user_message_topic', $customer_message_topic );
							$user_message           = apply_filters( 'ic_formbuilder_user_email', $message, $pre_name, $customer_email, $contact );
							$message_sender_name    = empty( $message_sender_name ) ? get_bloginfo( 'name' ) : $message_sender_name;
							$send_customer_email    = apply_filters( 'enable_customer_email', $send_customer_email, $customer_email, $customer_message_topic, $user_message, $message_sender_name, $message_sender );
							if ( $send_customer_email ) {
								ic_mail( $user_message, $message_sender_name, $message_sender, $customer_email, $customer_message_topic, $template );
							}
						}
					}
					do_action( 'ic_formbuilder_email', $message, $customer_email, $redirect, $pre_name, $template );
					$success_message = isset( $success_message ) ? $success_message : __( 'Your message has been sent successfully!' );
					$content         .= '<div class="ic_formbuilder_success center">' . implecode_success( $success_message, 0 ) . '</div>';
					$content         .= '<script>jQuery(document).ready(function() {jQuery(document.body).addClass("form-submitted");});</script>';
				} else {
					do_action( 'ic_formbuilder_no_email', $message, $customer_email, $redirect );
				}
				do_action( 'ic_formbuilder_after_mail', $message, $customer_email, $redirect, $contact );
				if ( ! empty( $redirect ) ) {
					ob_end_clean();
					wp_redirect( esc_url( $redirect, array( 'http', 'https' ) ) );
					exit;
				}
				$show_form = $show_form_after;
			} else {
				$box_content = __( 'Please fix the following', 'implecode' ) . ': <div class="smaller">';
				foreach ( $error_output as $field_name => $field_label ) {
					$box_content .= str_replace( ':', '', $field_label ) . $line;
				}
				$box_content .= '</div>';
				$content     .= '<div class="ic_formbuilder_warning center">' . implecode_warning( $box_content, 0 );

				$content .= '</div>';
				$content .= '<script>jQuery(document).ready(function() {jQuery(document.body).addClass("form-error");});</script>';
			}
		}
		if ( $show_form ) {
			form_builder_enqueue_styles();
			$max_size = 'large';
			foreach ( $contact->fields as $field ) {
				if ( isset( $field->field_options->size ) && $field->field_options->size == 'large' ) {
					$max_size = 'large';
					break;
				} else if ( ! isset( $field->field_options->size ) || ( isset( $field->field_options->size ) && $field->field_options->size == 'medium' ) ) {
					$max_size = 'medium';
				}
			}
			$form_type_class = ( $form_type == 'centered' ) ? 'label-left centered' : $form_type;
			$form_type       = ( $form_type == 'centered' ) ? 'label-left' : $form_type;
			$content         .= '<div class="ic-form ' . $form_type_class . ' ' . $max_size . '"><form action="' . ic_fb_current_url() . '" enctype="multipart/form-data" method="post">';
			if ( ! empty( $redirect ) ) {
				$content .= '<input type="hidden" value="' . esc_url( $redirect, array(
						'http',
						'https'
					) ) . '" name="ic_formbuilder_redirect" />';
			}
			$content    = apply_filters( 'ic_formbuilder_form_beginning', $content, $pre_name );
			$is_section = false;
			//$content .= '<div class="form_section">';
			foreach ( $contact->fields as $field ) {
				if ( ! isset( $field->field_options ) ) {
					$field->field_options = new stdClass();
				}
				$field->field_options->size = isset( $field->field_options->size ) ? $field->field_options->size : 'medium';
				$field->cid                 = apply_filters( 'ic_formbuilder_cid', $pre_name . $field->cid, $field, $pre_name );
				$field_value                = isset( $_POST[ $field->cid ] ) ? $_POST[ $field->cid ] : apply_filters( 'ic_formbuilder_default_value', '', $field->cid );
				$field_attributes           = apply_filters( 'ic_forumbuilder_field_attributes', $disabled, $field, $pre_name, $field_value );
				if ( $form_type != 'label-top' ) {
					$field_comment = isset( $field->field_options->description ) ? '<div class="order_form_row row comment"><div class="label"></div><div class="field"><div class="field-comment">' . $field->field_options->description . '</div></div></div>' : '';
				} else {
					$field_comment              = isset( $field->field_options->description ) ? '<div class="field-comment">' . $field->field_options->description . '</div>' : '';
					$field->field_options->size = ( $field->field_type == 'section_break' || $field->field_type == 'address'/* || $field->field_type == 'date' || $field->field_type == 'time' */ ) ? 'large' : $field->field_options->size;
				}
				if ( isset( $field->required ) && $field->required ) {
					$field->label = $field->label . ' *';
				}
				if ( ! $is_section && $field->field_type != 'section_break' ) {
					$content    .= '<div class="form_section">';
					$is_section = true;
				} else if ( $is_section && $field->field_type == 'section_break' ) {
					$content    .= '</div>';
					$content    .= '<div class="form_section">';
					$is_section = true;
				} else if ( $field->field_type == 'section_break' ) {
					$content    .= '<div class="form_section">';
					$is_section = true;
				}
				if ( $form_type == 'label-left' ) {
					$content .= '<div class="' . $pre_name . 'row row ' . $field->field_type . '">';
				} else if ( $field->field_options->size == 'small' && $small_count == 0 ) {
					$content     .= '<div class="small-table"><div class="' . $pre_name . 'row small-row"><div class="cell ' . $field->field_type . '"><div class="small-container first">';
					$small_count = 1;
				} else if ( $field->field_options->size == 'small' && $small_count == 1 ) {
					$content     .= '</div></div><div class="cell second ' . $field->field_type . '"><div class="small-container second">';
					$small_count = 2;
				} else if ( $field->field_options->size == 'small' && $small_count == 2 ) {
					$content     .= '</div></div></div></div>';
					$content     .= '<div class="small-table"><div class="' . $pre_name . 'row small-row ' . $field->field_type . '"><div class="cell"><div class="small-container first">';
					$small_count = 1;
				} else if ( $field->field_options->size != 'small' && $small_count != 0 ) {
					$content     .= '</div></div></div></div>';
					$small_count = 0;
				}
				$field_value = isset( $_POST[ $field->cid ] ) ? $_POST[ $field->cid ] : apply_filters( 'ic_formbuilder_default_value', '', $field->cid );
				if ( $field->field_type === 'dropdown_country' ) {
					$field->field_options->options              = ic_dropdown_country_options( $field_value );
					$field->field_options->include_blank_option = true;
				} else if ( $field->field_type === 'dropdown_state' ) {
					$field->field_options->options              = ic_dropdown_state_options( $field_value );
					$field->field_options->include_blank_option = true;
				}
				$error_class = apply_filters( 'ic_formbuilder_field_class', '', $field );
				$required    = empty( $field->required ) ? '' : 'required';

				if ( isset( $error_output[ $field->cid ] ) ) {
					$error_class = 'error';
				}
				if ( $field->field_type != 'address' && $field->field_type != 'price' && $field->field_type != 'website' && $field->field_type != 'checkboxes' && $field->field_type != 'time' && $field->field_type != 'number' && $field->field_type != 'paragraph' && $field->field_type != 'radio' && $field->field_type != 'dropdown' && $field->field_type != 'section_break' && ! ic_string_contains( $field->field_type, 'dropdown' ) ) {
					$content .= '<div class="label"><label for="' . $field->cid . '">' . __( $field->label, 'ecommerce-product-catalog' ) . '</label></div><div class="field"><span><input class="input ' . $field->field_options->size . ' ' . $error_class . '" ' . $required . ' ' . $field_attributes . ' type="' . $field->field_type . '" name="' . $field->cid . '" value="' . $field_value . '" /></span></div>';
				} else if ( $field->field_type == 'dropdown' || ( ic_string_contains( $field->field_type, 'dropdown' ) && isset( $field->field_options->options ) ) ) {
					$content .= '<div class="label"><label for="' . $field->cid . '">' . __( $field->label, 'ecommerce-product-catalog' ) . '</label></div><div class="field">';
					if ( count( $field->field_options->options ) > 5 ) {
						$error_class .= ' ic-chosen';
					}
					$content .= '<select data-placeholder="Choose a ' . str_replace( array(
							':',
							'*'
						), '', $field->label ) . '..." class="' . $error_class . ' ' . $required . '" ' . $field_attributes . ' name="' . $field->cid . '">';
					if ( ! empty( $field->field_options->include_blank_option ) ) {
						$content .= '<option value=""></option>';
					}
					foreach ( $field->field_options->options as $radio_option ) {
						if ( ! isset( $_POST[ $field->cid ] ) && $radio_option->checked ) {
							$checked = 'selected';
						} else {
							$checked = '';
						}
						$checked           = apply_filters( 'ic_formbuilder_option_attributes', $checked, $radio_option, $field );
						$find_option_value = isset( $radio_option->value ) ? $radio_option->value : $radio_option->label;
						$content           .= '<option value="' . apply_filters( 'ic_formbuilder_dorpdown_option_value', $find_option_value, $field->cid ) . '" ' . $checked . '>' . apply_filters( 'ic_formbuilder_option_label', __( $radio_option->label, 'ecommerce-product-catalog' ), $radio_option ) . '</option>';
					}
					$content .= '</select>';
					$content .= '</div>';
				} else if ( $field->field_type == 'checkboxes' ) {
					if ( $field->label === ' *' ) {
						$field->label = '';
					}
					$content .= '<div class="label"><label for="' . $field->cid . '">' . __( $field->label, 'ecommerce-product-catalog' ) . '</label></div><div class="field">';
					foreach ( $field->field_options->options as $key => $radio_option ) {
						$radio_option   = apply_filters( 'ic_formbuilder_option', $radio_option );
						$checkbox_value = strip_tags( $radio_option->label );
						if ( ( ! isset( $_POST[ $field->cid ] ) && $radio_option->checked ) || ( is_array( $field_value ) && in_array( $checkbox_value, $field_value ) ) ) {
							$checked = 'checked';
						} else if ( isset( $_POST[ $field->cid ] ) && $_POST[ $field->cid ] == $radio_option->label ) {
							$checked = 'checked';
						} else {
							$checked = '';
						}
						$checked = apply_filters( 'ic_formbuilder_option_attributes', $checked, $radio_option, $field );
						$content .= '<input class="input ' . $error_class . '" id="' . $checkbox_value . $field->cid . '" value="' . $checkbox_value . '" ' . $checked . ' ' . $field_attributes . ' type="checkbox" name="' . $field->cid . '[]" /> <label for="' . $checkbox_value . $field->cid . '">' . apply_filters( 'ic_formbuilder_option_label', __( $radio_option->label, 'ecommerce-product-catalog' ), $radio_option ) . '</label><br>';
					}
					$content .= '</div>';
				} else if ( $field->field_type == 'section_break' ) {
					$content .= '<h5 class="section-break">' . __( $field->label, 'ecommerce-product-catalog' ) . '</h5>';
					$content .= '<input type="hidden" value="' . __( $field->label, 'ecommerce-product-catalog' ) . '" name="' . $field->cid . '" />';
				} else {
					$content .= '<div class="label"><label for="' . $field->cid . '">' . __( $field->label, 'ecommerce-product-catalog' ) . '</label></div><div class="field"><textarea class="' . $field->field_options->size . ' ' . $error_class . '" ' . $required . ' ' . $field_attributes . ' name="' . $field->cid . '">' . $field_value . '</textarea></div>';
				}
				if ( $form_type != 'label-top' ) {
					$content .= '</div>' . $field_comment;
				} else {
					$content .= $field_comment;
				}
				$content = apply_filters( 'ic_formbuilder_after_field', $content, $field->field_type, $field->cid, $form_type );
			}
			//$content .= '</div>';
			$content .= $before_button;
			if ( $is_section ) {
				$content .= '</div>';
			}
			$content = apply_filters( 'ic_formbuilder_before_button', $content, $pre_name );
			$content .= '<input type="checkbox" name="ic_autocheck" class="autocheck" value="1" >';
			$content .= '<div class="' . $pre_name . 'button button-container">';
			$content .= apply_filters( 'ic_formbuilder_button', '<input value="' . $button_text . '" name="' . $pre_name . 'submit" ' . $field_attributes . ' class="button ' . $button_class . '" type="submit">', $pre_name );
			$content .= '</div>';
			$content .= '</form></div>';
		}
		ic_save_global( 'formbuilder_submit_content_' . $pre_name, $content );

		return $content;
	}

	function ic_formbuilder_redirect() {
		if ( isset( $_POST['ic_formbuilder_redirect'] ) && ! empty( $_POST['ic_formbuilder_redirect'] ) ) {
			ob_start();
		}
	}

	add_action( 'wp', 'ic_formbuilder_redirect' );

	if ( ! function_exists( 'get_bootstrap_data' ) ) {

		function get_bootstrap_data( $json_fields ) {
			$json_fields = explode( '[', $json_fields );
			unset( $json_fields[0] );
			$json_fields = implode( '[', $json_fields );
			$json_fields = explode( ']', $json_fields );
			array_pop( $json_fields );
			$json_fields = implode( ']', $json_fields );
//preg_match_all("/\[([^\]]*)\]/", $json_fields, $matches);
//return $matches[1][0];
			return $json_fields;
		}

	}

	function formbuilder_raw_fields( $fields, $in_row = 2, $pre_name = '', $field_values = null, $exclude = array() ) {
		form_builder_enqueue_styles();
		$fields   = apply_filters( 'formbuilder_raw_fields_fields', json_decode( $fields ), $pre_name );
		$raw_form = '';
		$line     = '<br>';
		$row      = 1;
		foreach ( $fields->fields as $field ) {
			$field->cid = apply_filters( 'ic_formbuilder_cid', $pre_name . $field->cid, $field, $pre_name );
			if ( ! in_array( $field->cid, $exclude ) ) {
				if ( is_array( $field_values ) ) {
					if ( isset( $field_values[ $field->cid ] ) && is_array( $field_values[ $field->cid ] ) ) {
						$field_value = array_map( 'htmlspecialchars', $field_values[ $field->cid ] );
					} else {
						$field_value = isset( $field_values[ $field->cid ] ) ? htmlspecialchars( $field_values[ $field->cid ] ) : '';
					}
				} else {
					$field_value = '';
				}
				$added    = false;
				$content  = '';
				$required = empty( $field->required ) ? '' : 'required';
				if ( $field->field_type === 'dropdown_country' ) {
					$field->field_options->options              = ic_dropdown_country_options( $field_value );
					$field->field_options->include_blank_option = true;
				} else if ( $field->field_type === 'dropdown_state' ) {
					$field->field_options->options              = ic_dropdown_state_options( $field_value );
					$field->field_options->include_blank_option = true;
				}
				if ( $row == 1 ) {
					$content .= '<div class="form_row table-row">';
				}
				$content .= '<div class="form_field table-cell ' . $field->field_type . '">';
				if ( $field->field_type != 'paragraph' && $field->field_type != 'checkboxes' && $field->field_type != 'date' && $field->field_type != 'radio' && $field->field_type != 'dropdown' && $field->field_type != 'section_break' && ! ic_string_contains( $field->field_type, 'dropdown' ) ) {
					$added   = true;
					$content .= '<div class="label"><label for="' . esc_attr( $field->cid ) . '">' . $field->label . '</label></div><div class="field"><span><input class="input" ' . $required . ' type="' . esc_attr( $field->field_type ) . '" name="' . esc_attr( $field->cid ) . '" value="' . esc_attr( $field_value ) . '" /></span></div>';
				} else if ( $field->field_type == 'checkboxes' ) {
					$added   = true;
					$content .= '<div class="label"><label for="' . esc_attr( $field->cid ) . '">' . $field->label . '</label></div><div class="field">';
					foreach ( $field->field_options->options as $radio_option ) {
						if ( is_array( $field_value ) && in_array( htmlspecialchars( sanitize_text_field( $radio_option->label ) ), $field_value ) ) {
							$checked = 'checked';
						} else {
							$checked = '';
						}
						$content .= '<input class="input" value="' . esc_attr( $radio_option->label ) . '" ' . $checked . ' type="checkbox" name="' . esc_attr( $field->cid . '[]' ) . '" /> ' . $radio_option->label . $line;
					}
					$content .= '</div>';
				} else if ( $field->field_type == 'date' ) {
					$added   = true;
					$content .= '<div class="label"><label for="' . $field->cid . '">' . $field->label . '</label></div><div class="field"><input class="input date" ' . $required . ' type="text" name="' . esc_attr( $field->cid ) . '" value="' . esc_attr( $field_value ) . '" /></div>';
				} else if ( $field->field_type == 'radio' ) {
					$added   = true;
					$content .= '<div class="label"><label for="' . esc_attr( $field->cid ) . '">' . $field->label . '</label></div><div class="field">';
					foreach ( $field->field_options->options as $radio_option ) {
						if ( $radio_option->label == $field_value ) {
							$checked = 'checked';
						} else {
							$checked = '';
						}
						$content .= '<input class="input" value="' . esc_attr( $radio_option->label ) . '" ' . $checked . ' type="' . esc_attr( $field->field_type ) . '" name="' . esc_attr( $field->cid ) . '" /> ' . $radio_option->label . $line;
					}
					$content .= '</div>';
				} else if ( $field->field_type == 'dropdown' || ic_string_contains( $field->field_type, 'dropdown' ) ) {
					$added   = true;
					$content .= '<div class="label"><label for="' . esc_attr( $field->cid ) . '">' . $field->label . '</label></div><div class="field">';
					$content .= '<select class="' . $required . '" name="' . esc_attr( $field->cid ) . '">';
					foreach ( $field->field_options->options as $radio_option ) {
						$find_option_value = isset( $radio_option->value ) ? $radio_option->value : $radio_option->label;
						$label             = apply_filters( 'ic_formbuilder_dorpdown_option_value', $find_option_value, $field->cid );
						if ( $label == $field_value || $radio_option->checked ) {
							$checked = 'selected';
						} else {
							$checked = '';
						}
						$content .= '<option ' . $checked . ' value="' . esc_attr( $label ) . '">' . $radio_option->label . '</option>';
					}
					$content .= '</select>';
					$content .= '</div>';
				} else if ( $field->field_type != 'section_break' ) {
					$added   = true;
					$content .= '<div class="label"><label for="' . esc_attr( $field->cid ) . '">' . $field->label . '</label></div><div class="field"><textarea ' . $required . ' name="' . esc_attr( $field->cid ) . '">' . esc_textarea( $field_value ) . '</textarea></div>';
				} else if ( $field->field_type == 'section_break' && ! empty( $raw_form ) ) {
					if ( ! empty( $content ) ) {
						$raw_form .= $content;
						$raw_form .= '</div>';
					}
					$raw_form .= '</div>';
					$row      = 1;

					$raw_form .= '<div class="form_row table-row section-break">';
					$raw_form .= '<div class="table-cell"></div><div class="table-cell"></div>';
					$raw_form .= '</div>';
				}
				$content = apply_filters( 'formbuilder_raw_fields_content', $content, $pre_name );
				if ( $added ) {

					$raw_form .= $content;
					$raw_form .= '</div>';
					if ( $row == $in_row || end( $fields->fields ) === $field ) {
						$raw_form .= '</div>';
						$row      = 1;
					} else {
						$row += 1;
					}
				}
			}
		}

		return $raw_form;
	}

	function ic_fb_current_url() {
		if ( isset( $_GET['pr_id'] ) ) {
			$current_url = add_query_arg( 'pr_id', intval( $_GET['pr_id'] ), get_permalink() );
		} else {
			$current_url = get_permalink();
		}

		return $current_url;
	}

	if ( ! function_exists( 'ic_get_country_name' ) ) {

		function ic_get_country_name( $country_code ) {
			$countries = implecode_supported_countries();
			if ( isset( $countries[ $country_code ] ) ) {
				$country_name = $countries[ $country_code ];
			} else {
				$country_name = $country_code;
			}

			return $country_name;
		}

	}

	if ( ! function_exists( 'ic_get_state_name' ) ) {

		function ic_get_state_name( $country_code, $state_code ) {
			$states = implecode_supported_states( $country_code );
			if ( isset( $states[ $state_code ] ) ) {
				$state_name = $states[ $state_code ];
			} else {
				$state_name = $state_code;
			}

			return $state_name;
		}

	}

	if ( ! function_exists( 'ic_formbuilder_field_cid' ) ) {

		function ic_formbuilder_field_cid( $field, $pre_name ) {
			return apply_filters( 'ic_formbuilder_cid', $pre_name . $field->cid, $field, $pre_name );
		}
	}

	if ( ! function_exists( 'ic_formbuilder_field_value' ) ) {

		function ic_formbuilder_field_value( $cid, $pre_name ) {
			return isset( $_POST[ $cid ] ) ? $_POST[ $cid ] : apply_filters( 'ic_formbuilder_default_value', '', $cid, $pre_name );
		}
	}

	if ( ! function_exists( 'ic_formbuilder_get_country' ) ) {

		function ic_formbuilder_get_country( $fields, $pre_name ) {
			foreach ( $fields as $field_key => $field ) {
				if ( $field->field_type === 'dropdown_country' ) {
					$cid = ic_formbuilder_field_cid( $field, $pre_name );

					return ic_formbuilder_field_value( $cid, $pre_name );
				}
			}
		}
	}

	if ( ! function_exists( 'implecode_supported_countries' ) ) {

		/**
		 * Defines supported countries
		 *
		 * @return array
		 */
		function implecode_supported_countries() {
			$countries = ic_get_global( 'supported_countries' );
			if ( $countries !== false ) {
				return $countries;
			}
			$countries_file_name = IC_FORMBUILDER_PATH . '/json/country-states.json';
			if ( file_exists( $countries_file_name ) ) {
				$json = file_get_contents( $countries_file_name );
				if ( ! empty( $json ) ) {
					$country_state = json_decode( $json, true );
					if ( ! empty( $country_state ) && is_array( $country_state ) ) {
						$countries = array();
						foreach ( $country_state as $country_code => $country_data ) {
							$countries[ $country_code ] = __( $country_data['name'], 'ecommerce-product-catalog' ) . ' - ' . $country_code;
						}
					}
				}
			}
			if ( empty( $countries ) ) {
				$countries = array(
					"US" => __( 'United States', 'ecommerce-product-catalog' ),
					"CA" => __( 'Canada', 'ecommerce-product-catalog' ),
					"GB" => __( 'United Kingdom', 'ecommerce-product-catalog' ),
					"AF" => __( 'Afghanistan', 'ecommerce-product-catalog' ),
					"AL" => __( 'Albania', 'ecommerce-product-catalog' ),
					"DZ" => __( 'Algeria', 'ecommerce-product-catalog' ),
					"AS" => __( 'American Samoa', 'ecommerce-product-catalog' ),
					"AD" => __( 'Andorra', 'ecommerce-product-catalog' ),
					"AO" => __( 'Angola', 'ecommerce-product-catalog' ),
					"AI" => __( 'Anguilla', 'ecommerce-product-catalog' ),
					"AQ" => __( 'Antarctica', 'ecommerce-product-catalog' ),
					"AG" => __( 'Antigua and Barbuda', 'ecommerce-product-catalog' ),
					"AR" => __( 'Argentina', 'ecommerce-product-catalog' ),
					"AM" => __( 'Armenia', 'ecommerce-product-catalog' ),
					"AW" => __( 'Aruba', 'ecommerce-product-catalog' ),
					"AU" => __( 'Australia', 'ecommerce-product-catalog' ),
					"AT" => __( 'Austria', 'ecommerce-product-catalog' ),
					"AZ" => __( 'Azerbaijan', 'ecommerce-product-catalog' ),
					"BS" => __( 'Bahamas', 'ecommerce-product-catalog' ),
					"BH" => __( 'Bahrain', 'ecommerce-product-catalog' ),
					"BD" => __( 'Bangladesh', 'ecommerce-product-catalog' ),
					"BB" => __( 'Barbados', 'ecommerce-product-catalog' ),
					"BY" => __( 'Belarus', 'ecommerce-product-catalog' ),
					"BE" => __( 'Belgium', 'ecommerce-product-catalog' ),
					"BZ" => __( 'Belize', 'ecommerce-product-catalog' ),
					"BJ" => __( 'Benin', 'ecommerce-product-catalog' ),
					"BM" => __( 'Bermuda', 'ecommerce-product-catalog' ),
					"BT" => __( 'Bhutan', 'ecommerce-product-catalog' ),
					"BO" => __( 'Bolivia', 'ecommerce-product-catalog' ),
					"BA" => __( 'Bosnia and Herzegovina', 'ecommerce-product-catalog' ),
					"BW" => __( 'Botswana', 'ecommerce-product-catalog' ),
					"BV" => __( 'Bouvet Island', 'ecommerce-product-catalog' ),
					"BR" => __( 'Brazil', 'ecommerce-product-catalog' ),
					"IO" => __( 'British Indian Ocean Territory', 'ecommerce-product-catalog' ),
					"BN" => __( 'Brunei Darrussalam', 'ecommerce-product-catalog' ),
					"BG" => __( 'Bulgaria', 'ecommerce-product-catalog' ),
					"BF" => __( 'Burkina Faso', 'ecommerce-product-catalog' ),
					"BI" => __( 'Burundi', 'ecommerce-product-catalog' ),
					"KH" => __( 'Cambodia', 'ecommerce-product-catalog' ),
					"CM" => __( 'Cameroon', 'ecommerce-product-catalog' ),
					"CV" => __( 'Cape Verde', 'ecommerce-product-catalog' ),
					"KY" => __( 'Cayman Islands', 'ecommerce-product-catalog' ),
					"CF" => __( 'Central African Republic', 'ecommerce-product-catalog' ),
					"TD" => __( 'Chad', 'ecommerce-product-catalog' ),
					"CL" => __( 'Chile', 'ecommerce-product-catalog' ),
					"CN" => __( 'China', 'ecommerce-product-catalog' ),
					"CX" => __( 'Christmas Island', 'ecommerce-product-catalog' ),
					"CC" => __( 'Cocos Islands', 'ecommerce-product-catalog' ),
					"CO" => __( 'Colombia', 'ecommerce-product-catalog' ),
					"KM" => __( 'Comoros', 'ecommerce-product-catalog' ),
					"CD" => __( 'Congo, Democratic People\'s Republic', 'ecommerce-product-catalog' ),
					"CG" => __( 'Congo, Republic of', 'ecommerce-product-catalog' ),
					"CK" => __( 'Cook Islands', 'ecommerce-product-catalog' ),
					"CR" => __( 'Costa Rica', 'ecommerce-product-catalog' ),
					"CI" => __( 'Cote d\'Ivoire', 'ecommerce-product-catalog' ),
					"HR" => __( 'Croatia/Hrvatska', 'ecommerce-product-catalog' ),
					"CU" => __( 'Cuba', 'ecommerce-product-catalog' ),
					"CY" => __( 'Cyprus Island', 'ecommerce-product-catalog' ),
					"CZ" => __( 'Czech Republic', 'ecommerce-product-catalog' ),
					"DK" => __( 'Denmark', 'ecommerce-product-catalog' ),
					"DJ" => __( 'Djibouti', 'ecommerce-product-catalog' ),
					"DM" => __( 'Dominica', 'ecommerce-product-catalog' ),
					"DO" => __( 'Dominican Republic', 'ecommerce-product-catalog' ),
					"TP" => __( 'East Timor', 'ecommerce-product-catalog' ),
					"EC" => __( 'Ecuador', 'ecommerce-product-catalog' ),
					"EG" => __( 'Egypt', 'ecommerce-product-catalog' ),
					"GQ" => __( 'Equatorial Guinea', 'ecommerce-product-catalog' ),
					"SV" => __( 'El Salvador', 'ecommerce-product-catalog' ),
					"ER" => __( 'Eritrea', 'ecommerce-product-catalog' ),
					"EE" => __( 'Estonia', 'ecommerce-product-catalog' ),
					"ET" => __( 'Ethiopia', 'ecommerce-product-catalog' ),
					"FK" => __( 'Falkland Islands', 'ecommerce-product-catalog' ),
					"FO" => __( 'Faroe Islands', 'ecommerce-product-catalog' ),
					"FJ" => __( 'Fiji', 'ecommerce-product-catalog' ),
					"FI" => __( 'Finland', 'ecommerce-product-catalog' ),
					"FR" => __( 'France', 'ecommerce-product-catalog' ),
					"GF" => __( 'French Guiana', 'ecommerce-product-catalog' ),
					"PF" => __( 'French Polynesia', 'ecommerce-product-catalog' ),
					"TF" => __( 'French Southern Territories', 'ecommerce-product-catalog' ),
					"GA" => __( 'Gabon', 'ecommerce-product-catalog' ),
					"GM" => __( 'Gambia', 'ecommerce-product-catalog' ),
					"GE" => __( 'Georgia', 'ecommerce-product-catalog' ),
					"DE" => __( 'Germany', 'ecommerce-product-catalog' ),
					"GR" => __( 'Greece', 'ecommerce-product-catalog' ),
					"GH" => __( 'Ghana', 'ecommerce-product-catalog' ),
					"GI" => __( 'Gibraltar', 'ecommerce-product-catalog' ),
					"GL" => __( 'Greenland', 'ecommerce-product-catalog' ),
					"GD" => __( 'Grenada', 'ecommerce-product-catalog' ),
					"GP" => __( 'Guadeloupe', 'ecommerce-product-catalog' ),
					"GU" => __( 'Guam', 'ecommerce-product-catalog' ),
					"GT" => __( 'Guatemala', 'ecommerce-product-catalog' ),
					"GG" => __( 'Guernsey', 'ecommerce-product-catalog' ),
					"GN" => __( 'Guinea', 'ecommerce-product-catalog' ),
					"GW" => __( 'Guinea-Bissau', 'ecommerce-product-catalog' ),
					"GY" => __( 'Guyana', 'ecommerce-product-catalog' ),
					"HT" => __( 'Haiti', 'ecommerce-product-catalog' ),
					"HM" => __( 'Heard and McDonald Islands', 'ecommerce-product-catalog' ),
					"VA" => __( 'Holy See (City Vatican State)', 'ecommerce-product-catalog' ),
					"HN" => __( 'Honduras', 'ecommerce-product-catalog' ),
					"HK" => __( 'Hong Kong', 'ecommerce-product-catalog' ),
					"HU" => __( 'Hungary', 'ecommerce-product-catalog' ),
					"IS" => __( 'Iceland', 'ecommerce-product-catalog' ),
					"IN" => __( 'India', 'ecommerce-product-catalog' ),
					"ID" => __( 'Indonesia', 'ecommerce-product-catalog' ),
					"IR" => __( 'Iran', 'ecommerce-product-catalog' ),
					"IQ" => __( 'Iraq', 'ecommerce-product-catalog' ),
					"IE" => __( 'Ireland', 'ecommerce-product-catalog' ),
					"IM" => __( 'Isle of Man', 'ecommerce-product-catalog' ),
					"IL" => __( 'Israel', 'ecommerce-product-catalog' ),
					"IT" => __( 'Italy', 'ecommerce-product-catalog' ),
					"JM" => __( 'Jamaica', 'ecommerce-product-catalog' ),
					"JP" => __( 'Japan', 'ecommerce-product-catalog' ),
					"JE" => __( 'Jersey', 'ecommerce-product-catalog' ),
					"JO" => __( 'Jordan', 'ecommerce-product-catalog' ),
					"KZ" => __( 'Kazakhstan', 'ecommerce-product-catalog' ),
					"KE" => __( 'Kenya', 'ecommerce-product-catalog' ),
					"KI" => __( 'Kiribati', 'ecommerce-product-catalog' ),
					"KW" => __( 'Kuwait', 'ecommerce-product-catalog' ),
					"KG" => __( 'Kyrgyzstan', 'ecommerce-product-catalog' ),
					"LA" => __( 'Lao People\'s Democratic Republic', 'ecommerce-product-catalog' ),
					"LV" => __( 'Latvia', 'ecommerce-product-catalog' ),
					"LB" => __( 'Lebanon', 'ecommerce-product-catalog' ),
					"LS" => __( 'Lesotho', 'ecommerce-product-catalog' ),
					"LR" => __( 'Liberia', 'ecommerce-product-catalog' ),
					"LY" => __( 'Libyan Arab Jamahiriya', 'ecommerce-product-catalog' ),
					"LI" => __( 'Liechtenstein', 'ecommerce-product-catalog' ),
					"LT" => __( 'Lithuania', 'ecommerce-product-catalog' ),
					"LU" => __( 'Luxembourgh', 'ecommerce-product-catalog' ),
					"MO" => __( 'Macau', 'ecommerce-product-catalog' ),
					"MK" => __( 'Macedonia', 'ecommerce-product-catalog' ),
					"MG" => __( 'Madagascar', 'ecommerce-product-catalog' ),
					"MW" => __( 'Malawi', 'ecommerce-product-catalog' ),
					"MY" => __( 'Malaysia', 'ecommerce-product-catalog' ),
					"Mv" => __( 'Maldives', 'ecommerce-product-catalog' ),
					"ML" => __( 'Mali', 'ecommerce-product-catalog' ),
					"MT" => __( 'Malta', 'ecommerce-product-catalog' ),
					"MH" => __( 'Marshall Islands', 'ecommerce-product-catalog' ),
					"MQ" => __( 'Martinique', 'ecommerce-product-catalog' ),
					"MR" => __( 'Mauritania', 'ecommerce-product-catalog' ),
					"MU" => __( 'Mauritius', 'ecommerce-product-catalog' ),
					"YT" => __( 'Mayotte', 'ecommerce-product-catalog' ),
					"MX" => __( 'Mexico', 'ecommerce-product-catalog' ),
					"FM" => __( 'Micronesia', 'ecommerce-product-catalog' ),
					"MD" => __( 'Moldova, Republic of', 'ecommerce-product-catalog' ),
					"MC" => __( 'Monaco', 'ecommerce-product-catalog' ),
					"MN" => __( 'Mongolia', 'ecommerce-product-catalog' ),
					"ME" => __( 'Montenegro', 'ecommerce-product-catalog' ),
					"MS" => __( 'Montserrat', 'ecommerce-product-catalog' ),
					"MA" => __( 'Morocco', 'ecommerce-product-catalog' ),
					"MZ" => __( 'Mozambique', 'ecommerce-product-catalog' ),
					"MM" => __( 'Myanmar', 'ecommerce-product-catalog' ),
					"NA" => __( 'Namibia', 'ecommerce-product-catalog' ),
					"NR" => __( 'Nauru', 'ecommerce-product-catalog' ),
					"NP" => __( 'Nepal', 'ecommerce-product-catalog' ),
					"NL" => __( 'Netherlands', 'ecommerce-product-catalog' ),
					"AN" => __( 'Netherlands Antilles', 'ecommerce-product-catalog' ),
					"NC" => __( 'New Caledonia', 'ecommerce-product-catalog' ),
					"NZ" => __( 'New Zealand', 'ecommerce-product-catalog' ),
					"NI" => __( 'Nicaragua', 'ecommerce-product-catalog' ),
					"NE" => __( 'Niger', 'ecommerce-product-catalog' ),
					"NG" => __( 'Nigeria', 'ecommerce-product-catalog' ),
					"NU" => __( 'Niue', 'ecommerce-product-catalog' ),
					"NF" => __( 'Norfolk Island', 'ecommerce-product-catalog' ),
					"KR" => __( 'North Korea', 'ecommerce-product-catalog' ),
					"MP" => __( 'Northern Mariana Islands', 'ecommerce-product-catalog' ),
					"NO" => __( 'Norway', 'ecommerce-product-catalog' ),
					"OM" => __( 'Oman', 'ecommerce-product-catalog' ),
					"PK" => __( 'Pakistan', 'ecommerce-product-catalog' ),
					"PW" => __( 'Palau', 'ecommerce-product-catalog' ),
					"PS" => __( 'Palestinian Territories', 'ecommerce-product-catalog' ),
					"PA" => __( 'Panama', 'ecommerce-product-catalog' ),
					"PG" => __( 'Papua New Guinea', 'ecommerce-product-catalog' ),
					"PY" => __( 'Paraguay', 'ecommerce-product-catalog' ),
					"PE" => __( 'Peru', 'ecommerce-product-catalog' ),
					"PH" => __( 'Phillipines', 'ecommerce-product-catalog' ),
					"PN" => __( 'Pitcairn Island', 'ecommerce-product-catalog' ),
					"PL" => __( 'Poland', 'ecommerce-product-catalog' ),
					"PT" => __( 'Portugal', 'ecommerce-product-catalog' ),
					"PR" => __( 'Puerto Rico', 'ecommerce-product-catalog' ),
					"QA" => __( 'Qatar', 'ecommerce-product-catalog' ),
					"RE" => __( 'Reunion Island', 'ecommerce-product-catalog' ),
					"RO" => __( 'Romania', 'ecommerce-product-catalog' ),
					"RU" => __( 'Russian Federation', 'ecommerce-product-catalog' ),
					"RW" => __( 'Rwanda', 'ecommerce-product-catalog' ),
					"SH" => __( 'Saint Helena', 'ecommerce-product-catalog' ),
					"KN" => __( 'Saint Kitts and Nevis', 'ecommerce-product-catalog' ),
					"LC" => __( 'Saint Lucia', 'ecommerce-product-catalog' ),
					"PM" => __( 'Saint Pierre and Miquelon', 'ecommerce-product-catalog' ),
					"VC" => __( 'Saint Vincent and the Grenadines', 'ecommerce-product-catalog' ),
					"SM" => __( 'San Marino', 'ecommerce-product-catalog' ),
					"ST" => __( 'Sao Tome and Principe', 'ecommerce-product-catalog' ),
					"SA" => __( 'Saudi Arabia', 'ecommerce-product-catalog' ),
					"SN" => __( 'Senegal', 'ecommerce-product-catalog' ),
					"RS" => __( 'Serbia', 'ecommerce-product-catalog' ),
					"SC" => __( 'Seychelles', 'ecommerce-product-catalog' ),
					"SL" => __( 'Sierra Leone', 'ecommerce-product-catalog' ),
					"SG" => __( 'Singapore', 'ecommerce-product-catalog' ),
					"SK" => __( 'Slovak Republic', 'ecommerce-product-catalog' ),
					"SI" => __( 'Slovenia', 'ecommerce-product-catalog' ),
					"SB" => __( 'Solomon Islands', 'ecommerce-product-catalog' ),
					"SO" => __( 'Somalia', 'ecommerce-product-catalog' ),
					"ZA" => __( 'South Africa', 'ecommerce-product-catalog' ),
					"GS" => __( 'South Georgia', 'ecommerce-product-catalog' ),
					"KP" => __( 'South Korea', 'ecommerce-product-catalog' ),
					"ES" => __( 'Spain', 'ecommerce-product-catalog' ),
					"LK" => __( 'Sri Lanka', 'ecommerce-product-catalog' ),
					"SD" => __( 'Sudan', 'ecommerce-product-catalog' ),
					"SR" => __( 'Suriname', 'ecommerce-product-catalog' ),
					"SJ" => __( 'Svalbard and Jan Mayen Islands', 'ecommerce-product-catalog' ),
					"SZ" => __( 'Swaziland', 'ecommerce-product-catalog' ),
					"SE" => __( 'Sweden', 'ecommerce-product-catalog' ),
					"CH" => __( 'Switzerland', 'ecommerce-product-catalog' ),
					"SY" => __( 'Syrian Arab Republic', 'ecommerce-product-catalog' ),
					"TW" => __( 'Taiwan', 'ecommerce-product-catalog' ),
					"TJ" => __( 'Tajikistan', 'ecommerce-product-catalog' ),
					"TZ" => __( 'Tanzania', 'ecommerce-product-catalog' ),
					"TG" => __( 'Togo', 'ecommerce-product-catalog' ),
					"TK" => __( 'Tokelau', 'ecommerce-product-catalog' ),
					"TO" => __( 'Tonga', 'ecommerce-product-catalog' ),
					"TH" => __( 'Thailand', 'ecommerce-product-catalog' ),
					"TT" => __( 'Trinidad and Tobago', 'ecommerce-product-catalog' ),
					"TN" => __( 'Tunisia', 'ecommerce-product-catalog' ),
					"TR" => __( 'Turkey', 'ecommerce-product-catalog' ),
					"TM" => __( 'Turkmenistan', 'ecommerce-product-catalog' ),
					"TC" => __( 'Turks and Caicos Islands', 'ecommerce-product-catalog' ),
					"TV" => __( 'Tuvalu', 'ecommerce-product-catalog' ),
					"UG" => __( 'Uganda', 'ecommerce-product-catalog' ),
					"UA" => __( 'Ukraine', 'ecommerce-product-catalog' ),
					"AE" => __( 'United Arab Emirates', 'ecommerce-product-catalog' ),
					"UY" => __( 'Uruguay', 'ecommerce-product-catalog' ),
					"UM" => __( 'US Minor Outlying Islands', 'ecommerce-product-catalog' ),
					"UZ" => __( 'Uzbekistan', 'ecommerce-product-catalog' ),
					"VU" => __( 'Vanuatu', 'ecommerce-product-catalog' ),
					"VE" => __( 'Venezuela', 'ecommerce-product-catalog' ),
					"VN" => __( 'Vietnam', 'ecommerce-product-catalog' ),
					"VG" => __( 'Virgin Islands (British)', 'ecommerce-product-catalog' ),
					"VI" => __( 'Virgin Islands (USA)', 'ecommerce-product-catalog' ),
					"WF" => __( 'Wallis and Futuna Islands', 'ecommerce-product-catalog' ),
					"EH" => __( 'Western Sahara', 'ecommerce-product-catalog' ),
					"WS" => __( 'Western Samoa', 'ecommerce-product-catalog' ),
					"YE" => __( 'Yemen', 'ecommerce-product-catalog' ),
					"ZM" => __( 'Zambia', 'ecommerce-product-catalog' ),
					"ZW" => __( 'Zimbabwe', 'ecommerce-product-catalog' ),
				);
			}

			$countries = apply_filters( 'ic_supported_countries', $countries );

			ic_save_global( 'supported_countries', $countries );

			return $countries;
		}

	}

	if ( ! function_exists( 'implecode_supported_states' ) ) {

		/**
		 * Defines supported US States
		 *
		 * @return array
		 */
		function implecode_supported_states( $country = 'US' ) {
			$states = ic_get_global( 'supported_states_' . $country );
			if ( $states !== false ) {
				return $states;
			}
			$countries_file_name = IC_FORMBUILDER_PATH . '/json/country-states.json';
			if ( file_exists( $countries_file_name ) ) {
				$json = file_get_contents( $countries_file_name );
				if ( ! empty( $json ) ) {
					$country_state = json_decode( $json, true );
					if ( isset( $country_state[ $country ]['states'] ) ) {
						$states = $country_state[ $country ]['states'];
					}
				}
			}
			if ( empty( $states ) && $country == 'US' ) {
				$states = array(
					'AL' => 'Alabama',
					'AK' => 'Alaska',
					'AZ' => 'Arizona',
					'AR' => 'Arkansas',
					'CA' => 'California',
					'CO' => 'Colorado',
					'CT' => 'Connecticut',
					'DE' => 'Delaware',
					'DC' => 'District Of Columbia (Washington, D.C.)',
					'FL' => 'Florida',
					'GA' => 'Georgia',
					'HI' => 'Hawaii',
					'ID' => 'Idaho',
					'IL' => 'Illinois',
					'IN' => 'Indiana',
					'IA' => 'Iowa',
					'KS' => 'Kansas',
					'KY' => 'Kentucky',
					'LA' => 'Louisiana',
					'ME' => 'Maine',
					'MD' => 'Maryland',
					'MA' => 'Massachusetts',
					'MI' => 'Michigan',
					'MN' => 'Minnesota',
					'MS' => 'Mississippi',
					'MO' => 'Missouri',
					'MT' => 'Montana',
					'NE' => 'Nebraska',
					'NV' => 'Nevada',
					'NH' => 'New Hampshire',
					'NJ' => 'New Jersey',
					'NM' => 'New Mexico',
					'NY' => 'New York',
					'NC' => 'North Carolina',
					'ND' => 'North Dakota',
					'OH' => 'Ohio',
					'OK' => 'Oklahoma',
					'OR' => 'Oregon',
					'PA' => 'Pennsylvania',
					'PR' => 'Puerto Rico',
					'RI' => 'Rhode Island',
					'SC' => 'South Carolina',
					'SD' => 'South Dakota',
					'TN' => 'Tennessee',
					'TX' => 'Texas',
					'UT' => 'Utah',
					'VT' => 'Vermont',
					'VA' => 'Virginia',
					'WA' => 'Washington',
					'WV' => 'West Virginia',
					'WI' => 'Wisconsin',
					'WY' => 'Wyoming',
				);
			}
			if ( empty( $states ) ) {
				$states = array();
			}


			$states = apply_filters( 'ic_supported_states', $states, $country );

			ic_save_global( 'supported_states_' . $country, $states );

			return $states;
		}

	}

	function ic_dropdown_country_options( $selected = null, $null_option = false ) {
		if ( $null_option ) {
			$null_option          = new stdClass();
			$null_option->checked = false;
			$null_option->label   = __( 'Choose your country...', 'ecommerce-product-catalog' );
			$null_option->value   = '';
			$options              = array( $null_option );
		} else {
			$options = array();
		}
		$countries = implecode_supported_countries();
		foreach ( $countries as $country_code => $country_name ) {
			$option          = new stdClass();
			$option->checked = $selected === $country_code;
			$option->label   = $country_name;
			$option->value   = $country_code;
			$options[]       = $option;
		}

		return $options;
	}

	add_action( 'wp_ajax_ic_state_dropdown', 'ic_ajax_dropdown_state' );
	add_action( 'wp_ajax_nopriv_ic_state_dropdown', 'ic_ajax_dropdown_state' );

	function ic_ajax_dropdown_state() {
		$selected_country_code = isset( $_POST['country_code'] ) ? sanitize_text_field( $_POST['country_code'] ) : '';
		if ( ! empty( $selected_country_code ) && ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ic-ajax-nonce' ) ) {
			$selected_state_code = isset( $_POST['state_code'] ) ? sanitize_text_field( $_POST['state_code'] ) : '';
			if ( ! empty( $selected_state_code ) && ic_string_contains( $selected_state_code, $selected_country_code ) ) {
				$selected_country_code = $selected_state_code;
			} else if ( ! empty( $selected_state_code ) ) {
				$selected_country_code .= '_' . $selected_state_code;
			}
			$state_options = ic_dropdown_state_options( $selected_country_code );
			if ( ! empty( $state_options ) ) {
				if ( ob_get_length() > 0 ) {
					ob_end_clean();
				}
				echo json_encode( $state_options );
			}
		}
		wp_die();
	}

	function ic_dropdown_state_options( $selected = null, $null_option = false ) {
		if ( $null_option ) {
			$null_option          = new stdClass();
			$null_option->checked = false;
			$null_option->label   = __( 'Choose your state...', 'implecode-quote-cart' );
			$null_option->value   = '';
			$options              = array( $null_option );
		} else {
			$options = array();
		}

		if ( ic_string_contains( $selected, '_' ) ) {
			$selected_a       = explode( '_', $selected );
			$selected_country = $selected_a[0];
			$selected         = $selected_a[1];
		} else {
			$selected_country = $selected;
		}
		$states = implecode_supported_states( $selected_country );
		foreach ( $states as $state_code => $state_name ) {
			$option          = new stdClass();
			$option->checked = $selected === $state_code;
			$option->label   = $state_name;
			$option->value   = $selected_country . '_' . $state_code;
			$options[]       = $option;
		}

		return $options;
	}

	function get_submitted_form_name() {
		global $submitted_form_name;

		return $submitted_form_name;
	}

	if ( ! function_exists( 'ic_country_selector' ) ) {

		function ic_country_selector( $name, $required = null, $selected = null, $echo = 1 ) {
			$return = '<select ' . $required . ' data-placeholder="' . __( 'Choose your country...', 'ecommerce-product-catalog' ) . '" id="country-selector" name="' . $name . '" class="country-selector">';
			$return .= '<option value=""></option>';
			foreach ( implecode_supported_countries() as $code => $country ) {
				$return .= '<option value="' . $code . '"' . selected( $selected, $code, 0 ) . '>' . $country . '</option>';
			}
			$return .= '</select>';

			return echo_ic_setting( $return, $echo );
		}

	}

	add_filter( 'ic_forumbuilder_field_attributes', 'add_fombuilder_fields_visibility_data', 10, 3 );

	/**
	 * Adds field visibility rules data
	 *
	 * @param type $attributes
	 * @param type $field
	 *
	 * @return string
	 */
	function add_fombuilder_fields_visibility_data( $attributes, $field, $pre_name ) {
		if ( ! empty( $field->field_options->rules ) && ! empty( $field->field_options->rulesval ) ) {
			$attributes .= ' data-rules="' . apply_filters( 'ic_formbuilder_cid', $pre_name . $field->field_options->rules, '', $pre_name ) . '"';
			$attributes .= ' data-rulesval="' . $field->field_options->rulesval . '"';
			$rulestype  = ! empty( $field->field_options->rulestype ) && $field->field_options->rulestype === '!=' ? '!=' : '=';
			$attributes .= ' data-rulestype="' . $rulestype . '"';
		}

		return $attributes;
	}

	/**
	 * Checks if form field should be visible in email
	 *
	 * @param type $field
	 *
	 * @return boolean
	 */
	function is_formbuilder_field_email_visible( $field, $pre_name ) {
		if ( ! empty( $field->field_options->rules ) && ! empty( $field->field_options->rulesval ) ) {
			$rulestype = ! empty( $field->field_options->rulestype ) && $field->field_options->rulestype === '!=' ? '!=' : '=';
			$field_id  = apply_filters( 'ic_formbuilder_cid', $pre_name . $field->field_options->rules, '', $pre_name );
			if ( $rulestype === '=' && ( ! isset( $_POST[ $field_id ] ) || ( isset( $_POST[ $field_id ] ) && $_POST[ $field_id ] != $field->field_options->rulesval ) ) ) {
				return false;
			} else if ( $rulestype === '!=' && ( isset( $_POST[ $field_id ] ) && $_POST[ $field_id ] == $field->field_options->rulesval ) ) {
				return false;
			}
		}

		return true;
	}

	add_filter( 'ic_formbuilder_message_field_value', 'ic_formbuilder_modify_notification_values', 10, 3 );

	/**
	 * Changes country code to country name in customer and admin order email
	 *
	 * @param string $field_value
	 * @param string $field_cid
	 * @param string $field_type
	 *
	 * @return string
	 */
	function ic_formbuilder_modify_notification_values( $field_value, $field_cid, $field_type ) {
		if ( $field_type == 'dropdown' || ic_string_contains( $field_type, 'dropdown' ) ) {
			if ( ic_string_contains( $field_type, 'country' ) || ic_string_contains( $field_cid, 'country' ) ) {
				$field_value = ic_get_country_name( $field_value );
			} else if ( ic_string_contains( $field_type, 'state' ) || ic_string_contains( $field_cid, 'state' ) ) {
				$exploded_field_value = explode( '_', $field_value );
				if ( ! empty( $exploded_field_value[0] ) && ! empty( $exploded_field_value[1] ) ) {
					$country_code = $exploded_field_value[0];
					unset( $exploded_field_value[0] );
					$state_code  = implode( '_', $exploded_field_value );
					$field_value = ic_get_state_name( $country_code, $state_code );
				}
			}
		}

		return $field_value;
	}

	/**
	 * Handles formbuilder file upload
	 *
	 * @param type $field_name
	 *
	 * @return string
	 */
	function handle_formbuilder_file_upload( $field_name ) {
		$attachment = '';
		if ( isset( $_FILES[ $field_name ] ) && ( $_FILES[ $field_name ]['size'] > 0 ) ) {
			$uploading = $_FILES[ $field_name ];
			//print_r( $uploading );
			$destination = ic_mail_attachment_folder( 'dir' );
			$file_name   = sanitize_file_name( $uploading['name'] );
			if ( ! empty( $file_name ) ) {
				$tmp_name      = $uploading['tmp_name'];
				$arr_file_type = wp_check_filetype( basename( $file_name ) );
				if ( ! empty( $arr_file_type['ext'] ) ) {
					$filepath = $destination . '/' . $file_name;
					if ( move_uploaded_file( $tmp_name, $filepath ) ) {
						$attachment = $filepath;
					}
				}
			}
		}

		return $attachment;
	}

	/**
	 * Returns email attachment folder
	 *
	 * @param type $dir
	 *
	 * @return string
	 */
	function ic_mail_attachment_folder( $dir = null ) {
		$wp_uploads_dir = wp_upload_dir();
		if ( ! empty( $dir ) ) {
			$frontend_dest = $wp_uploads_dir['basedir'] . '/ic-mail-att';
			if ( ! file_exists( $frontend_dest ) ) {
				mkdir( $frontend_dest );
			}
		} else {
			$frontend_dest = $wp_uploads_dir['baseurl'] . '/ic-mail-att';
		}

		return $frontend_dest;
	}

	add_filter( 'pre_update_option_shopping_checkout_form', 'ic_validate_formbuilder_form' );

	function ic_validate_formbuilder_form( $form ) {
		$ic_form_updates = get_option( 'ic_formbuilder_updates', 1 );
		$form            = str_replace( array( '"cid":"c', '"rules":"c' ), array(
			'"cid":"' . $ic_form_updates . 'ic',
			'"rules":"' . $ic_form_updates . 'ic'
		), $form );
		$ic_form_updates ++;
		update_option( 'ic_formbuilder_updates', $ic_form_updates, false );

		return apply_filters( 'validate_shopping_checkout_form', $form );
	}

	if ( ! function_exists( 'ic_get_default_date_format' ) ) {
		function ic_get_default_date_format( $preview = false, $jquery = false ) {
			$format = get_option( 'date_format' );
			if ( $preview ) {
				return date_i18n( $format );
			}
			if ( $jquery ) {
				$format = dateformat_PHP_to_jQueryUI( $format );
			}

			return $format;
		}
	}

	if ( ! function_exists( 'ic_get_default_time_format' ) ) {
		function ic_get_default_time_format( $preview = false, $jquery = false ) {
			$format = get_option( 'time_format' );
			if ( $preview ) {
				return date_i18n( $format );
			}
			if ( $jquery ) {
				$format = dateformat_PHP_to_jQueryUI( $format );
			}

			return $format;
		}
	}

	if ( ! function_exists( 'dateformat_PHP_to_jQueryUI' ) ) {
		/*
		 * Matches each symbol of PHP date format standard
		 * with jQuery equivalent codeword
		 * @author Tristan Jahier
		 */

		function dateformat_PHP_to_jQueryUI( $php_format ) {
			$SYMBOLS_MATCHING = array(
				// Day
				'd' => 'dd',
				'D' => 'D',
				'j' => 'd',
				'l' => 'DD',
				'N' => '',
				'S' => '',
				'w' => '',
				'z' => 'o',
				// Week
				'W' => '',
				// Month
				'F' => 'MM',
				'm' => 'mm',
				'M' => 'M',
				'n' => 'm',
				't' => '',
				// Year
				'L' => '',
				'o' => '',
				'Y' => 'yy',
				'y' => 'y',
				// Time
				'a' => 'tt',
				'A' => 'TT',
				'B' => '',
				'g' => 'h',
				'G' => 'H',
				'h' => 'hh',
				'H' => 'HH',
				'i' => 'mm',
				's' => 'ss',
				'T' => 'z'
			);
			$jqueryui_format  = "";
			$escaping         = false;
			for ( $i = 0; $i < strlen( $php_format ); $i ++ ) {
				$char = $php_format[ $i ];
				if ( $char === '\\' ) { // PHP date format escaping character
					$i ++;
					if ( $escaping ) {
						$jqueryui_format .= $php_format[ $i ];
					} else {
						$jqueryui_format .= '\'' . $php_format[ $i ];
					}
					$escaping = true;
				} else {
					if ( $escaping ) {
						$jqueryui_format .= "'";
						$escaping        = false;
					}
					if ( isset( $SYMBOLS_MATCHING[ $char ] ) ) {
						$jqueryui_format .= $SYMBOLS_MATCHING[ $char ];
					} else {
						$jqueryui_format .= $char;
					}
				}
			}

			return $jqueryui_format;
		}
	}

	function ic_get_checkout_field( $cid, $fields = null, $pre_name = 'ic_form_' ) {
		if ( empty( $fields ) && function_exists( 'get_shopping_checkout_form_fields' ) ) {
			$form_fields = get_shopping_checkout_form_fields();
		}
		$contact = json_decode( $form_fields );
		if ( ! empty( $contact->fields ) && is_array( $contact->fields ) ) {
			foreach ( $contact->fields as $field ) {
				$new_cid = $pre_name . $field->cid;
				if ( $field->cid === $cid || $new_cid === $cid ) {
					return $field;
				}
			}
		}

		return;
	}

	function ic_message_td_start( $template = false ) {
		if ( $template ) {
			return '<td>';
		}

		return '';
	}

	function ic_message_td_end( $template = false ) {
		if ( $template ) {
			return '</td>';
		}

		return '';
	}

	function ic_message_row( $field, $field_value, $field_id, $line, $template ) {
		return apply_filters( 'formbuilder_message_row', str_replace( '::', ':', ic_message_td_start( $template ) . $field->label . ': ' . ic_message_td_end( $template ) . ic_message_td_start( $template ) . $field_value . $line . ic_message_td_end( $template ) ), $field, $field_id, $line );
	}

}
<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages quote cart functions folder
 *
 * Here includes folder files defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-quote-cart/includes
 * @author        Norbert Dreszer
 */
class ic_shopping_ecommerce_shortcodes {

	function __construct() {
		add_filter( 'the_content', array( __CLASS__, 'implement_shortcodes' ) );
		add_shortcode( 'shopping_cart', array( __CLASS__, 'shopping_cart' ) );
		add_shortcode( 'cart_submit_form', array( $this, 'cart_submit' ) );
	}

	/**
	 * Adds shopping cart shortcodes to selected pages
	 *
	 * @param type $content
	 *
	 * @return string
	 */
	static function implement_shortcodes( $content ) {
		//$shopping_cart_settings = get_shopping_cart_settings();
		remove_filter( 'the_content', array( __CLASS__, 'implement_shortcodes' ) );
		if ( is_ic_shopping_cart() && ! has_shortcode( $content, 'shopping_cart' ) ) {
			if ( ! ic_string_contains( $content, 'shopping-cart-container' ) ) {
				$content .= '[shopping_cart]';
			}
		} else if ( is_ic_shopping_order() && ! has_shortcode( $content, 'cart_submit_form' ) ) {
			if ( ! ic_string_contains( $content, 'shopping-cart-submit-container' ) ) {
				$content .= '[cart_submit_form]';
			}
		} else if ( is_ic_shopping_thank_you() && ! has_shortcode( $content, 'success_page' ) ) {
			$content .= '[success_page]';
		}

		return $content;
	}

	/**
	 * Defines shopping_cart shortcode content
	 *
	 * @return string
	 */
	static function shopping_cart() {
		$displayed = ic_get_global( 'shopping_cart_displayed' );
		if ( ! empty( $displayed ) || is_ic_shopping_order() ) {
			return;
		}
		ic_save_global( 'inside_shopping_cart', 1 );
		$shopping_cart_settings = get_shopping_cart_settings();
		if ( $shopping_cart_settings['url_button'] != 1 ) {
			$button_class = 'button ' . design_schemes( 'box', 0 );
		} else {
			$button_class = 'link';
		}
		$form = apply_filters( 'shopping_cart_start', '' );
		$form .= '<div id="shopping-cart-container" class="' . $shopping_cart_settings['cart_page_template'] . '">';
		$form .= apply_filters( 'shopping_cart_container_start', '' );
		if ( isset( $_POST['p_quantity'] ) ) {
			$form .= '<div class="success">' . __( 'New quantity values saved.', 'ecommerce-product-catalog' ) . '</div>';
		}
		if ( isset( $_GET['no_variations'] ) ) {
			$form .= '<div class="wrong_message">' . __( 'Please choose product variations.', 'ecommerce-product-catalog' ) . '</div>';
		}
		$back_url = ic_get_shopping_back_url();
		$form     .= '<form method="post" action="' . ic_shopping_submit_page_url() . '">';
		if ( $shopping_cart_settings['cart_page_template'] == 'no_qty' ) {
			$form .= '<div class="form-buttons">';
			if ( ! empty( $shopping_cart_settings['contnue_shopping_label'] ) ) {
				$form .= '<a class="continue_shopping ic-secondary-button ' . esc_attr( $button_class ) . '" href="' . $back_url . '">' . esc_html( $shopping_cart_settings['contnue_shopping_label'] ) . '</a>';
			}
			$form .= '</div>';
		}
		$form .= shopping_cart_products( 0 );
		$form .= '<div class="form-buttons">';
		if ( ! empty( $shopping_cart_settings['contnue_shopping_label'] ) ) {
			$form .= '<a class="continue_shopping ic-secondary-button ' . esc_attr( $button_class ) . '" href="' . $back_url . '">' . esc_html( $shopping_cart_settings['contnue_shopping_label'] ) . '</a>';
		}
		$form .= '<input class="to_cart_submit ' . esc_attr( $button_class ) . '" type="submit" value="' . esc_attr( $shopping_cart_settings['place_order_label'] ) . '"></div>';

		$form .= '</form></div>';
		/*
		$form .= '<script>
jQuery(document).ready(function() {
if(typeof window.history.pushState == "function") {
var url = document.URL.split("?");
window.history.replaceState({}, "Hide", url[0]);
}});</script>';
		*/
		if ( in_the_loop() ) {
			ic_save_global( 'shopping_cart_displayed', 1 );
		}

		$return = apply_filters( 'ic_shopping_cart_shortcode', $form );
		ic_delete_global( 'inside_shopping_cart' );

		return $return;
	}

	function cart_submit() {
		ic_save_global( 'inside_checkout', 1 );

		$shopping_cart_settings = get_shopping_cart_settings();
		$form                   = '<div id="shopping-cart-submit-container" class="' . $shopping_cart_settings['cart_page_template'] . '">';
		if ( $shopping_cart_settings['form_registration'] == 'user' && ! isset( $_POST['cart_submit'] ) ) {
			$form .= $this->customer_login_box();
		}
		$form .= $this->checkout_form();
		$form .= '</div>';
		ic_delete_global( 'inside_checkout' );

		return $form;
	}

	function customer_login_box() {
		if ( ! is_ic_cart_customer() ) {
			return implecode_info( sprintf( __( 'Returning customer? %s', 'ecommerce-product-catalog' ), ic_digital_customer_login_url( 0, '', false, false, false ) ), 0, 0 );
		}
	}

	function checkout_form() {
		$shopping_cart_settings = get_shopping_cart_settings();

		$form = '<div class="shopping-form"><div class="address-form">';
//$form .= implecode_formbuilder_output('cart_', false, false, $captcha, __('Submit', 'implecode-shopping-cart') );
		$form_fields            = get_shopping_checkout_form_fields();
		$success                = __( 'Thank you. We have received your order. We will contact you shortly.', 'ecommerce-product-catalog' );
		$receive_cart           = $shopping_cart_settings['receive_cart'];
		$site_name              = get_shopping_cart_site_name();
		$send_cart              = $shopping_cart_settings['send_cart'];
		$subject                = $shopping_cart_settings['admin_email_subject'];
		$customer_subject       = $shopping_cart_settings['user_email_subject'];
		$redirect_url           = ic_get_permalink( $shopping_cart_settings['thank_you_page'] );
		$checkout_form_settings = get_cart_form_editor_settings();
		$button_class           = design_schemes( 'box', 0 );
		$form                   .= formbuilder_form( $form_fields, 'cart_', '', $checkout_form_settings['form_button_label'], $checkout_form_settings['form_type'], true, $success, '<br>', true, $send_cart, $receive_cart, $subject, $button_class, true, $customer_subject, $site_name, $redirect_url );
		$form                   .= '</div></div>';

		return $form;
	}

}

global $ic_shopping_ecommerce_shortcodes;
$ic_shopping_ecommerce_shortcodes = new ic_shopping_ecommerce_shortcodes;

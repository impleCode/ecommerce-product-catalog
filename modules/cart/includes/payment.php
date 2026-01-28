<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages shopping customer
 *
 * Here shopping customer is defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-shopping-cart/includes
 * @author        Norbert Dreszer
 */
function ic_get_order_payments() {
	$payments = apply_filters( 'ic_normal_payments', array() );

	return $payments;
}

class ic_cart_payments {

	public $cart_settings;

	function __construct() {
		$this->settings();
		$this->hooks();
		add_action( 'wp', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_hooks' ) );
	}

	function init() {
		$this->wp_hooks();
	}

	function hooks() {
		add_filter( 'ic_payment_statuses', array( $this, 'add_payment_status' ) );
		add_filter( 'order_completed_status_trigger', array( $this, 'add_payment_status_trigger' ) );
		add_filter( 'ic_default_order_status', array( $this, 'set_payment_default_order_status' ) );
		add_filter( 'ic_save_order_payment_details', array( $this, 'order_save_payment_label' ) );
	}

	function wp_hooks() {
		if ( is_ic_shopping_order() ) {
			if ( ! function_exists( 'order_form_payment_options' ) ) {
				add_filter( 'ic_formbuilder_before_button', array( $this, 'payment_options_html' ), 8, 2 );
			}
			add_filter( 'ic_formbuilder_user_email', array( $this, 'email_payment_details' ), 10, 2 );
			add_filter( 'ic_formbuilder_admin_email', array( $this, 'email_payment_details' ), 10, 2 );
		}
	}

	function admin_hooks() {
		add_filter( 'ic_payment_statuses', array( $this, 'add_payment_status' ) );
	}

	function settings() {
		$this->cart_settings = get_shopping_cart_settings();
	}

	/**
	 * Adds payment options to order form
	 *
	 * @param string $content
	 * @param string $pre_name
	 *
	 * @return string
	 */
	function payment_options_html( $content, $pre_name ) {
		if ( $pre_name == 'order_form_' || $pre_name == 'cart_' ) {
			$payment_options = apply_filters( "payment_options", '' );
			if ( ! empty( $payment_options ) ) {
				$content .= '<div class="form_section payment-options-section">';
				$content .= '<div class="order_form_row row section_break"><h5 class="section-break"><strong>' . __( 'PAYMENT', 'ecommerce-product-catalog' ) . '</strong></h5></div>';
				$content .= $payment_options;
				$content .= '</div>';
			}
		}

		return $content;
	}

	/**
	 * Adds selected custom payment details if selected
	 *
	 * @param string $message
	 * @param string $pre_name
	 *
	 * @return string
	 */
	function email_payment_details( $message, $pre_name ) {
		if ( ic_string_contains( $message, '[payment_details]' ) ) {
			$payment_details = '';
			if ( $pre_name == 'cart_' && isset( $_POST['gateway'] ) ) {
				$normal_payemts = ic_get_order_payments();
				if ( ! empty( $normal_payemts[ $_POST['gateway'] ] ) ) {
					$p               = ic_email_paragraph();
					$ep              = ic_email_paragraph_end();
					$payment_details = $p . __( 'Payment Method', 'ecommerce-product-catalog' ) . ': ' . $normal_payemts[ $_POST['gateway'] ] . $ep;
				}
			}
			$message = str_replace( '[payment_details]', $payment_details, $message );
		}

		return $message;
	}

	function order_save_payment_label( $payment_details ) {
		$payment_details['payment_label'] = $this->current_payment_label();
		$payment_details['payment_name']  = $this->current_payment_name();

		return $payment_details;
	}

	function current_payment_label() {
		$payment_label = '';
		if ( isset( $_POST['gateway'] ) ) {
			$normal_payments = ic_get_order_payments();
			if ( ! empty( $normal_payments[ $_POST['gateway'] ] ) ) {
				$payment_label = $normal_payments[ $_POST['gateway'] ];
			}
		}

		return $payment_label;
	}

	function current_payment_name() {
		$payment_name = '';
		if ( isset( $_POST['gateway'] ) ) {
			$payment_name = sanitize_text_field( $_POST['gateway'] );
		}

		return $payment_name;
	}

	function add_payment_status( $statuses ) {
		if ( is_ic_any_payment_gateway_active() ) {
			if ( ! empty( $statuses['completed'] ) ) {
				$old_status['completed'] = $statuses['completed'];
				unset( $statuses['completed'] );
			}
			$statuses['confirmed'] = __( 'Paid', 'ecommerce-product-catalog' );
			if ( ! empty( $old_status ) ) {
				$statuses = array_merge( $statuses, $old_status );
			}
		}

		return $statuses;
	}

	function add_payment_status_trigger( $status ) {
		if ( is_ic_any_payment_gateway_active() ) {
			$status = 'confirmed';
		}

		return $status;
	}

	function set_payment_default_order_status( $status ) {
		if ( is_ic_any_payment_gateway_active() ) {
			$status = 'pending';
		}

		return $status;
	}

}

$ic_cart_payments = new ic_cart_payments;

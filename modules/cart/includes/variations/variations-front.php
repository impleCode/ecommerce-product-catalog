<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages framework folder
 *
 * Here framework folder files defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog-pro/framework
 * @author        Norbert Dreszer
 */
class ic_cart_variations_front {

	function __construct() {
		/* Product Page */
		add_action( 'product_details', array( __CLASS__, 'show_selectors' ), 6, 0 );
		add_shortcode( 'product_variations', array( __CLASS__, 'show_selectors_shortcode' ) );

		/* Cart Page */
		add_filter( 'shopping_cart_product_price', array( __CLASS__, 'modify_variations_price' ), 12, 2 );
		add_filter( 'cart_product_name', array( $this, 'variations_in_shopping_cart' ), 10, 2 );

		/* Checkout Page */
		add_filter( 'cart_summary_product_name', array( $this, 'variations_in_shopping_cart_summary' ), 10, 2 );

		/* Notification Email */
		add_filter( 'cart_email_product_name', array( $this, 'variations_in_shopping_cart_email' ), 10, 3 );

		/* Admin Order Screen */
		add_filter( 'ic_order_product_name', array( $this, 'variations_in_orders_screen' ), 10, 3 );


		/* Styling */
		add_filter( 'product_page_additional_styles', array( __CLASS__, 'inline_styling' ) );
		add_filter( 'product_listing_additional_styles', array( __CLASS__, 'inline_styling' ) );

		/* AJAX */
		add_action( 'wp_ajax_nopriv_ic_get_variation_selectors', array( $this, 'ajax_show_selectors' ) );
		add_action( 'wp_ajax_ic_get_variation_selectors', array( $this, 'ajax_show_selectors' ) );
	}

	function ajax_show_selectors() {
		$product_id = isset( $_POST['product_id'] ) ? $_POST['product_id'] : '';
		if ( ! empty( $product_id ) ) {
			do_action( 'ic_ajax_self_submit_init' );
			$this->show_selectors( $product_id, 1 );
		}
		wp_die();
	}

	function equal() {
		$equal = apply_filters( 'ic_var_equal', ' = ' );

		return $equal;
	}

	/**
	 * Adds variation price modificator to shopping cart product price
	 *
	 * @param type $price
	 * @param type $cart_id
	 *
	 * @return type
	 */
	static function modify_variations_price( $price, $cart_id ) {
		if ( $selected = get_variation_value_from_cart_id( $cart_id ) ) {
			$product_id = cart_id_to_product_id( $cart_id );
			$price      = get_variations_modificators( $product_id, $selected, null, false, $price );
		}

		return $price;
	}

	static function show_selectors( $cart_id = null, $echo = 1 ) {
		$product_id = empty( $cart_id ) ? get_the_ID() : cart_id_to_product_id( $cart_id );
		if ( empty( $cart_id ) ) {
			$cart_id = $product_id;
		}
		$return = new ic_cart_variations_selectors( $product_id, $cart_id );

		return echo_ic_setting( $return->selectors(), $echo );
	}

	static function show_selectors_shortcode( $atts ) {
		$args       = shortcode_atts( array(
			'product' => ic_get_product_id(),
		), $atts );
		$product_id = $args['product'];
		if ( empty( $product_id ) ) {
			$product_id = ic_get_product_id();
		}
		$return = new ic_cart_variations_selectors( $product_id );

		return '<div class="ic-variations-shortcode">' . echo_ic_setting( $return->selectors(), 0 ) . '</div>';
	}

	function variations_in_shopping_cart( $content, $product_id ) {
//$product_id = cart_id_to_product_id( $product_id );
		$content .= '<br>' . $this->show_selectors( $product_id, 0 );

		return $content;
	}

	function variations_in_orders_screen( $product_name, $order_products, $i = null ) {
		if ( $i !== null && ! empty( $order_products['variations'][ $i ] ) ) {
			$variations = $order_products['variations'][ $i ];
		} else if ( $i === null && ! empty( $order_products['variations'] ) ) {
			$variations = $order_products['variations'];
		}
		if ( ! empty( $variations ) ) {
			$var_info = '';
			foreach ( $variations as $label => $value ) {
				if ( is_array( $value ) || ( empty( $value ) && ! is_numeric( $value ) ) ) {
					continue;
				}
				if ( ! empty( $var_info ) ) {
					$var_info .= ', ';
				}
				$var_info .= $label . ' ' . $value;
			}
			$product_name .= ' (';
			$product_name .= $var_info;
			$product_name .= ')';
		}

		return $product_name;
	}

	/**
	 * Adds selected variations to product name
	 *
	 * @param type $content
	 * @param type $cart_id
	 *
	 * @return string
	 */
	function variations_in_shopping_cart_summary( $content, $cart_id ) {
		$product_id                  = cart_id_to_product_id( $cart_id );
		$product_variations_settings = get_product_variations_settings();
		$cart_id_non_var             = get_cart_id_without_variations( $cart_id );
//$content .= '<br>';
		$variation_value = false;
		for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {
			$variation_label = get_product_variation_label( $product_id, $i );
			if ( isset( $_POST[ $i . "_variation_" . $cart_id ] ) && $_POST[ $i . "_variation_" . $cart_id ] != '' ) {
				$variation_value = sanitize_text_field( $_POST[ $i . "_variation_" . $cart_id ] );
			} else {
				$variation_value = get_variation_value_from_cart_id( $cart_id, $i );
			}
			if ( $variation_value || is_numeric( $variation_value ) ) {
				$content .= apply_filters( 'ic_cart_summary_variation', '<input name="' . $i . "_variation_" . $cart_id_non_var . '" type="hidden" hidden value="' . sanitize_title( $variation_value ) . '" /><span class="chosen_variation">' . $variation_label . $this->equal() . $variation_value . '</span>', $i, $cart_id );
			}
		}

		return $content;
	}

	/**
	 * Adds selected variations to product name
	 *
	 * @param type $content
	 * @param type $product_id
	 * @param type $cart_id
	 *
	 * @return type
	 */
	function variations_in_shopping_cart_email( $content, $product_id, $cart_id ) {
		$product_variations_settings = get_product_variations_settings();
		$inside_content              = '';
		if ( $product_variations_settings['mode'] == 'normal' ) {
			for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {
				$variation_label = get_product_variation_label( $product_id, $i );
				$variation_value = get_variation_value_from_cart_id( $cart_id, $i );
				if ( isset( $variation_value ) && $variation_value != '' ) {
					if ( $i > 1 && ! empty( $inside_content ) ) {
						$inside_content .= ', ';
					}
					$inside_content .= $variation_label . $this->equal() . $variation_value;
				}
			}
			if ( $inside_content != '' ) {
				$content .= apply_filters( 'ic_catalog_notification_variation', ' (' . $inside_content . ')' );
			}
		} else {
			for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {
				$variation_value = get_variation_value_from_cart_id( $cart_id, $i );
				if ( isset( $variation_value ) && $variation_value != '' ) {
					$inside_content .= $variation_value;
				}
			}
			if ( $inside_content != '' ) {
				$content = $inside_content;
			}
		}

		return $content;
	}

	static function inline_styling( $styles ) {
		$styles .= '.ic_spinner{background: url(' . admin_url() . 'images/spinner.gif) no-repeat;}';

		return $styles;
	}

}

global $ic_cart_variations_front;
$ic_cart_variations_front = new ic_cart_variations_front;

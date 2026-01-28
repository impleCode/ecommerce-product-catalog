<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages shopping cart
 *
 * Here shopping cart functions are defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-shopping-cart/includes
 * @author        Norbert Dreszer
 */
if ( ! function_exists( 'shopping_cart_products' ) ) {

	function shopping_cart_products( $raw = 1 ) {
		$settings = get_shopping_cart_settings();

		return ic_cart_products( $raw, true, 'cart_content', $settings );
	}

}

if ( ! function_exists( 'shopping_cart_products_array' ) ) {

	function shopping_cart_products_array( $cart_content = null ) {
		return ic_cart_products_array( $cart_content, 'cart_content' );
	}

}

/**
 * Returns current cart product total net
 *
 * @return type
 * @global type $ic_shopping_cart_totals
 */
function shopping_cart_products_total() {
	global $ic_shopping_cart_totals;
	if ( isset( $ic_shopping_cart_totals['total'] ) ) {
		return $ic_shopping_cart_totals['total'];
	} else {
		$products  = shopping_cart_products_array();
		$total_net = 0;
		foreach ( $products as $product_id => $p_quantity ) {
			$cart_id    = $product_id;
			$product_id = cart_id_to_product_id( $cart_id );
			//$product_price	 = apply_filters( 'shopping_cart_product_price', product_price( $product_id, 1 ), $cart_id, $p_quantity );
			$product_price = get_shopping_cart_product_price( $product_id, $cart_id, $p_quantity );
			$product_total = $product_price * $p_quantity;
			$total_net     += $product_total;
		}

		return $total_net;
	}
}

if ( ! function_exists( 'ic_ajax_price_format' ) ) {
	add_action( 'wp_ajax_nopriv_ic_price_format', 'ic_ajax_price_format' );
	add_action( 'wp_ajax_ic_price_format', 'ic_ajax_price_format' );

	/**
	 * Manages ajax price format
	 *
	 */
	function ic_ajax_price_format() {
		$price = '';
		if ( isset( $_POST['price'] ) ) {
			$price = price_format( raw_price_format( $_POST['price'] ) );
		}
		echo $price;
		wp_die();
	}

}

add_action( 'wp_ajax_shopping_cart_products', 'ic_ajax_shopping_cart_products' );
add_action( 'wp_ajax_nopriv_shopping_cart_products', 'ic_ajax_shopping_cart_products' );

/**
 * Handles ajax shopping cart products table
 *
 */
function ic_ajax_shopping_cart_products() {
	$raw = isset( $_POST['raw'] ) ? $_POST['raw'] : 1;
	echo shopping_cart_products( $raw );
	wp_die();
}

add_shortcode( 'cart_button', 'ic_shopping_cart_button' );

/**
 * Returns shopping cart button
 *
 */
function ic_shopping_cart_button( $show_empty = true, $label = '' ) {
	$cart_content = ic_shopping_cart_content( true );
	$how_many     = ic_get_cart_items_count( $cart_content );
	$attr         = '';
	if ( ! empty( $label ) ) {
		$attr = 'data-label="' . $label . '"';
	}
	if ( $how_many || $show_empty ) {
		$content   = '<div id="shopping_cart_widget" class="shopping-cart-widget" ' . $attr . '>';
		$content   .= '<div class="product-shopping-cart">';
		$user_cart = ic_get_customer_cart();
		if ( empty( $how_many ) && ! empty( $user_cart ) ) {
			$content .= '<a href="#" class="restore-ic-cart button ' . design_schemes( 'box', 0 ) . '"><span class="cart_button_text">' . __( 'Restore Previous Cart', 'ecommerce-product-catalog' ) . '</span></a>';
		} else {
			$attr = '';
			if ( empty( $label ) ) {
				$label = $how_many . ' ' . __( 'selected', 'ecommerce-product-catalog' );
			}
			$content .= '<a href="' . ic_shopping_cart_page_url() . '" class="button ' . design_schemes( 'box', 0 ) . '"><span class="cart_button_text">' . $label . '</span></a>';
		}
		$content .= '</div>';
	} else {
		$content = '<div id="shopping_cart_widget" class="empty-cart shopping-cart-widget" ' . $attr . '>';
	}
	$content .= '</div>';

	return $content;
}


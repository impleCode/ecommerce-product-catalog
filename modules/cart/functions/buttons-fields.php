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
add_action( 'price_table', 'ic_cart_add_button', 10, 0 );
add_filter( 'ic_cart_add_button', 'ic_cart_added_info_button' );

/**
 * Shows shopping cart button
 *
 * @param boolean $echo
 * @param int $table Returns button in a table
 * @param type $desc Shows description near the button
 *
 * @return type
 */
function ic_cart_add_button(
	$echo = 1, $table = 1, $desc = 1, $product_id = null, $qty = true, $label = null,
	$redirect = false, $before_button = '', $cart_content = null, $additional_products = null
) {
	if ( empty( $product_id ) ) {
		$product_id = get_the_ID();
	}
	if ( product_price( $product_id ) != '' ) {
		if ( empty( $cart_content ) ) {
			$cart_content = ic_shopping_cart_content( true );
		}
		$shopping_cart_settings = get_shopping_cart_settings();
		if ( $shopping_cart_settings['url_button'] != 1 ) {
			$button_class = 'button';
		} else {
			$button_class = 'link';
		}
		$button_class .= ' ' . design_schemes( 'box', 0 );
		$action       = '';
		if ( $shopping_cart_settings['cart_redirect'] == 1 || $redirect ) {
			$action = ic_shopping_cart_page_url();
		}
		$return = '';
		if ( $table == 1 ) {
			$return .= '<tr><td colspan=4>';
		}
		$form_class           = 'add-to-shopping-cart reg_add';
		$form_container_class = 'add_to_cart_form_container';
		$recently_added       = ic_get_recently_added_product();
		if ( ic_has_product_variations( $product_id ) && ! empty( $recently_added ) ) {
			$form_container_class .= ' ic-button-hidden';
		}
		$return .= '<div class="' . $form_container_class . '"><form id="reg_add_' . $product_id . '" class="' . $form_class . '" action="' . $action . '" method="post">';
		$return .= '<input hidden type="hidden" name="current_product" value="' . $product_id . '">';
		if ( ! empty( $additional_products ) && is_array( $additional_products ) ) {
			foreach ( $additional_products as $additional_product_id ) {
				$return .= '<input hidden type="hidden" name="additional_product[]" value="' . $additional_product_id . '">';
			}
		}
		$return                   .= '<input hidden type="hidden" name="cart_content" value=\'' . $cart_content . '\'>';
		$product_variation_values = get_product_variations_values( $product_id );
		foreach ( $product_variation_values as $i => $variation_values ) {
			$var_id   = $i + 1;
			$var_type = ic_cat_get_variation_type( $product_id, $var_id );
			if ( $var_type !== 'custom' ) {
				$variation_values = array_filter( $variation_values );
			}
			if ( ! empty( $variation_values ) ) {
				$return .= '<input type="hidden" name="' . $var_id . '_variation_' . $product_id . '" value="">';
			}
		}
		if ( empty( $label ) ) {
			$label = $shopping_cart_settings['button_label'];
		}
		$add_to_cart_button = apply_filters( 'ic_cart_add_button', '<button type="submit" class="' . $button_class . '">' . $label . '</button>', $product_id );
		$return             .= apply_filters( 'before_shopping_cart_button', $before_button, $product_id );
		if ( $shopping_cart_settings['quantity_box'] != 1 && $qty && $shopping_cart_settings['cart_page_template'] != 'no_qty' ) {
			$return .= ic_cart_quantity_field( $product_id );
		} else {
			$return .= '<input type="hidden" name="current_quantity" value="1">';
		}
		$return .= $add_to_cart_button;
		$return .= '</form>';
		if ( $desc == 1 && $shopping_cart_settings['button_desc'] != '' ) {
			$return .= '<div class="cart_info">' . apply_filters( 'shopping_cart_button_info', $shopping_cart_settings['button_desc'] ) . '</div>';
		}
		$return .= apply_filters( 'after_shopping_cart_button', '', $product_id );

		if ( function_exists( 'get_discount_field' ) ) {
			$disc   = get_discount_field( $product_id );
			$return .= '<form style="display: none;" id="qty_add_' . $product_id . '" class="add-to-shopping-cart qty_add" action="' . $action . '" method="post"><input hidden type="hidden" name="current_product" value="' . $product_id . '"><input hidden type="hidden" name="current_quantity" value="' . $disc['qty'] . '"><input hidden type="hidden" name="cart_content" value=\'' . $cart_content . '\'>';
			foreach ( $product_variation_values as $i => $variation_values ) {
				$variation_values = array_filter( $variation_values );
				if ( ! empty( $variation_values ) ) {
					$i      += 1;
					$return .= '<input hidden type="hidden" name="' . $i . '_variation_' . $product_id . '" value="">';
				}
			}
			$return .= '</form>';
		}
		$return .= '</div>';
		if ( $table == 1 ) {
			$return .= '</td></tr>';
		}

		return echo_ic_setting( $return, $echo );
	}
}

/**
 * Returns choose options button
 *
 * @param type $product_id
 *
 * @return string
 */
function ic_cart_choose_options_button( $product_id ) {
	$shopping_cart_settings = get_shopping_cart_settings();
	if ( $shopping_cart_settings['url_button'] != 1 ) {
		$button_class = 'button';
	} else {
		$button_class = 'link';
	}
	$button_class .= ' ' . design_schemes( 'box', 0 );
	$button       = '<a href="' . ic_get_permalink( $product_id ) . '" class="' . $button_class . ' choose-options">' . __( 'Select Options', 'ecommerce-product-catalog' ) . '</a>';

	return $button;
}

function ic_cart_quantity_field( $product_id ) {
	$min = 1;
	$max = '';

	return apply_filters( 'ic_product_page_quantity_field', '<input name="current_quantity" type="number" min="' . $min . '" max="' . $max . '" step="1" value="' . $min . '"> ' );
}

add_filter( 'design_schemes_output', 'ic_cart_clickable_price', 10, 2 );

/**
 * Adds a class to handle price add to cart feature
 *
 * @param string $design_schemes
 * @param type $which
 *
 * @return string
 */
function ic_cart_clickable_price( $design_schemes, $which ) {
	$shopping_cart_settings = get_shopping_cart_settings();
	if ( $shopping_cart_settings['add_on_price'] != 1 && $which == 'price' && is_ic_product_listing() ) {
		$design_schemes .= ' price-add-to-cart';
	}

	return $design_schemes;
}

if ( ! function_exists( 'ic_listing_add_to_cart' ) ) {
	add_action( 'classic_grid_product_listing_element', 'ic_listing_add_to_cart', 10, 2 );

	function ic_listing_add_to_cart( $product_id, $listing_settings ) {
		if ( ! empty( $listing_settings['add_to_cart'] ) ) {
			ic_cart_add_button( 1, 0, 0, $product_id, false );
		}
	}
}


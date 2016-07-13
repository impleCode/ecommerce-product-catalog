<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product content functions
 *
 * Here all plugin content functions are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
/* General */

/**
 * Transforms number to the price format
 *
 * @param float $price_value The number to be price formatted
 * @param int $clear Set to 1 to skip price_format filter and allow $format variable
 * @param int $format Set to 0 to return not formatted price
 * @param int $raw Set to 0 to return formatted price without currency
 * @return string|float
 */
function price_format( $price_value, $clear = 0, $format = 1, $raw = 0 ) {
	if ( $price_value === null || $price_value == '' ) {
		return '';
	} else if ( empty( $price_value ) ) {
		$single_names = get_single_names();
		return $single_names[ 'free' ];
	}
	$set			 = get_currency_settings();
	$th_symbol		 = addslashes( $set[ 'th_sep' ] );
	$dec_symbol		 = addslashes( $set[ 'dec_sep' ] );
	/*  if ( $set[ 'dec_sep' ] != '.' ) {
	  $raw_price_value = str_replace( array( $th_symbol, $dec_symbol ), array( "", '.' ), $price_value );
	  } else {
	  $raw_price_value = str_replace( $th_symbol, "", $price_value );
	  } */
	$raw_price_value = $price_value;
	$decimals		 = !empty( $set[ 'dec_sep' ] ) ? 2 : 0;
	$price_value	 = number_format( $raw_price_value, $decimals, $set[ 'dec_sep' ], $set[ 'th_sep' ] );
	$space			 = ' ';
	if ( $set[ 'price_space' ] == 'off' ) {
		$space = '';
	}
	$formatted = $price_value . $space . product_currency();
	if ( $set[ 'price_format' ] == 'before' ) {
		$formatted = product_currency() . $space . $price_value;
	}
	if ( $clear == 0 ) {
		return apply_filters( 'price_format', $formatted, $price_value );
	} else if ( $format == 1 ) {
		return $formatted;
	} else if ( $raw == 1 ) {
		return $raw_price_value;
	} else {
		return $price_value;
	}
}

add_filter( 'product_price', 'raw_price_format', 5 );
add_filter( 'unfiltered_product_price', 'raw_price_format', 5 );

/**
 * Transforms price for internal use
 *
 * @param int|string $price_value
 * @return int
 */
function raw_price_format( $price_value ) {
	$set		 = get_currency_settings();
	$th_symbol	 = addslashes( $set[ 'th_sep' ] );
	$dec_symbol	 = addslashes( $set[ 'dec_sep' ] );
	if ( $set[ 'dec_sep' ] != '.' ) {
		$raw_price_value = str_replace( array( $th_symbol, $dec_symbol ), array( "", '.' ), $price_value );
	} else {
		$raw_price_value = str_replace( $th_symbol, "", $price_value );
	}
	return $raw_price_value;
}

add_filter( 'price_format', 'ic_after_price_text' );

/**
 * Handles after price text
 *
 * @param string $price
 * @return string
 */
function ic_after_price_text( $price ) {
	if ( is_ic_product_page() ) {
		$labels = get_single_names();
		if ( !empty( $labels[ 'after_price' ] ) ) {
			$price .= ' <span class="after-price">' . $labels[ 'after_price' ] . '</span>';
		}
	}
	return $price;
}

/* Classic List */

function c_list_desc( $post_id = null, $shortdesc = null ) {
	if ( $shortdesc == '' ) {
		$shortdesc = clean_short_description( $post_id );
	} else {
		$shortdesc	 = strip_tags( $shortdesc );
		$shortdesc	 = trim( strip_shortcodes( $shortdesc ) );
		$shortdesc	 = str_replace( array( "\r\n" ), ' ', $shortdesc );
	}
	$desclenght	 = strlen( $shortdesc );
	$more		 = '';
	$limit		 = apply_filters( 'c_list_desc_limit', 243 );
	if ( $desclenght > $limit ) {
		$more = ' [...]';
	}
	return apply_filters( 'c_list_desc_content', ic_substr( $shortdesc, 0, $limit ) . $more, $post_id );
}

/**
 * Returns short description text without HTML
 *
 * @param int $product_id
 * @return string
 */
function clean_short_description( $product_id, $new_line = ' ' ) {
	$shortdesc	 = get_product_short_description( $product_id );
	$shortdesc	 = strip_tags( $shortdesc );
	$shortdesc	 = trim( strip_shortcodes( $shortdesc ) );
	$shortdesc	 = str_replace( array( "\r\n" ), $new_line, $shortdesc );
	return $shortdesc;
}

/* Single Product */
add_action( 'single_product_end', 'add_back_to_products_url', 99, 2 );

/**
 *
 * @param object $post
 * @param array $single_names
 * @param string $taxonomies
 */
function add_back_to_products_url( $post, $single_names ) {
	if ( is_ic_product_listing_enabled() ) {
		echo get_back_to_products_url( $single_names );
	}
}

/**
 * Returns back to products URL
 *
 * @param array $v_single_names
 * @return string
 */
function get_back_to_products_url( $v_single_names = null ) {
	if ( is_ic_product_listing_enabled() ) {
		$single_names	 = isset( $v_single_names ) ? $v_single_names : get_single_names();
		$listing_url	 = product_listing_url();
		if ( !empty( $listing_url ) ) {
			$url = '<a class="back-to-products" href="' . product_listing_url() . '">' . $single_names[ 'return_to_archive' ] . '</a>';
			return $url;
		}
	}
	return;
}

/**
 * Shows product search form
 */
function product_search_form() {
	$search_button_text = __( 'Search', 'ecommerce-product-catalog' );
	echo '<form role="search" method="get" class="search-form product_search_form" action="' . esc_url( home_url( '/' ) ) . '">
<input type="hidden" name="post_type" value="' . get_current_screen_post_type() . '" />
<input class="product-search-box" type="search" value="' . get_search_query() . '" id="s" name="s" placeholder="' . __( 'Product Search', 'ecommerce-product-catalog' ) . '" />
<input class="search-submit product-search-submit" type="submit" value="' . $search_button_text . '" />
</form>';
}

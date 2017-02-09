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
	if ( $price_value === null || $price_value === '' ) {
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
	if ( function_exists( 'is_ic_product_page' ) && is_ic_product_page() ) {
		$labels = get_single_names();
		if ( !empty( $labels[ 'after_price' ] ) ) {
			$price .= ' <span class="after-price">' . $labels[ 'after_price' ] . '</span>';
		}
	}
	return $price;
}

function example_price() {
	echo '2500.00 EUR';
}

add_action( 'example_price', 'example_price' );
add_action( 'product_details', 'show_price', 7, 0 );

/**
 * Shows price on product page
 *
 * @param type $post
 * @param type $single_names
 */
function show_price() {
	ic_show_template_file( 'product-page/product-price.php' );
}

/**
 * Returns price table for product page
 * @param type $product_id
 * @param type $single_names
 * @return type
 */
function get_product_price_table( $product_id ) {
	ic_save_global( 'product_id', $product_id );
	ob_start();
	show_price();
	ic_delete_global( 'product_id' );
	return ob_get_clean();
}

/**
 * Returns product price
 *
 * @param int $product_id
 * @param string $unfiltered Assign any value to return the original price (without any modifications)
 * @return string
 */
function product_price( $product_id, $unfiltered = null ) {
	if ( empty( $unfiltered ) ) {
		$price_value = apply_filters( 'product_price', get_post_meta( $product_id, "_price", true ), $product_id );
	} else {
		$price_value = apply_filters( 'unfiltered_product_price', get_post_meta( $product_id, "_price", true ), $product_id );
	}
	$price_value = (is_ic_price_enabled()) ? $price_value : '';
	return $price_value;
}

/**
 * 3 letter product currency format
 *
 * @return type
 */
function product_currency_letters() {
	return get_option( 'product_currency', DEF_CURRENCY );
}

/**
 * Returns product currency
 *
 * @return string
 */
function product_currency() {
	$product_currency			 = product_currency_letters();
	$product_currency_settings	 = get_option( 'product_currency_settings', unserialize( DEF_CURRENCY_SETTINGS ) );
	if ( !empty( $product_currency_settings[ 'custom_symbol' ] ) ) {
		$currency = $product_currency_settings[ 'custom_symbol' ];
	} else {
		$currency = $product_currency;
	}
	return apply_filters( 'ic_product_currency', $currency );
}

/* Archive Functions */
add_action( 'archive_price', 'show_archive_price', 10, 1 );

/**
 * Shows product listing price
 *
 * @param type $post
 */
function show_archive_price( $post ) {
	$price_value = product_price( $post->ID );
	if ( !empty( $price_value ) ) {
		?>
		<div class="product-price <?php design_schemes( 'color' ); ?>">
			<?php echo price_format( $price_value ) ?>
		</div>
		<?php
	}
}

/**
 * Sets product listing price
 *
 * @param type $archive_price
 * @param type $post
 * @return string
 */
function set_archive_price( $archive_price, $post ) {
	$price_value = product_price( $post->ID );
	if ( !empty( $price_value ) ) {
		$archive_price = '<span class="product-price ' . design_schemes( 'color', 0 ) . '">';
		$archive_price .= price_format( $price_value );
		$archive_price .= '</span>';
	}
	return $archive_price;
}

add_filter( 'archive_price_filter', 'set_archive_price', 10, 2 );

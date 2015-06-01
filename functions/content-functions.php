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

function price_format( $price_value, $clear = 0, $format = 1, $raw = 0 ) {
	$set			 = get_currency_settings();
	$th_symbol		 = addslashes( $set[ 'th_sep' ] );
	$dec_symbol		 = addslashes( $set[ 'dec_sep' ] );
	/*  if ( $set[ 'dec_sep' ] != '.' ) {
	  $raw_price_value = str_replace( array( $th_symbol, $dec_symbol ), array( "", '.' ), $price_value );
	  } else {
	  $raw_price_value = str_replace( $th_symbol, "", $price_value );
	  } */
	$raw_price_value = $price_value;
	$price_value	 = number_format( $raw_price_value, 2, $set[ 'dec_sep' ], $set[ 'th_sep' ] );
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

/* Classic List */

function c_list_desc( $post_id = null, $shortdesc = null ) {
	if ( $shortdesc == '' ) {
		$shortdesc = strip_tags( get_product_short_description( $post_id ) );
	}
//remove all shortcodes - discsox
	$shortdesc	 = trim( strip_shortcodes( $shortdesc ) );
	$desclenght	 = strlen( $shortdesc );
	$more		 = '';
	$limit		 = apply_filters( 'c_list_desc_limit', 243 );
	if ( $desclenght > $limit ) {
		$more = ' [...]';
	}
	return apply_filters( 'c_list_desc_content', mb_substr( $shortdesc, 0, $limit ) . $more, $post_id );
}

/* Single Product */

function add_back_to_products_url( $post, $single_names, $taxonomies ) {
	if ( is_ic_product_listing_enabled() ) {
		?>
		<a href="<?php echo product_listing_url(); ?>"><?php echo $single_names[ 'return_to_archive' ]; ?></a>
		<?php
	}
}

add_action( 'single_product_end', 'add_back_to_products_url', 99, 3 );

/**
 * Shows product search form
 */
function product_search_form() {
	$search_button_text = __( 'Search', 'al-ecommerce-product-catalog' );
	echo '<form role="search" method="get" class="search-form product_search_form" action="' . esc_url( home_url( '/' ) ) . '">
<input type="hidden" name="post_type" value="al_product" />
<input class="product-search-box" type="search" value="' . get_search_query() . '" id="s" name="s" placeholder="' . __( 'Product Search', 'al-ecommerce-product-catalog' ) . '" />
<input class="search-submit product-search-submit" type="submit" value="' . $search_button_text . '" />
</form>';
}

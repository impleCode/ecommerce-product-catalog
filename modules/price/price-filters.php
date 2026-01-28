<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
add_action( 'ic_set_product_filters', 'ic_price_filter' );

/**
 * Handles price filter
 *
 * @param type $session
 */
function ic_price_filter() {
	$session = get_product_catalog_session();
	if ( isset( $_GET['min-price'] ) ) {
		$filter_value = floatval( $_GET['min-price'] );
		if ( ! empty( $filter_value ) ) {
			if ( ! isset( $session['filters'] ) ) {
				$session['filters'] = array();
			}
			$session['filters']['min-price'] = $filter_value;
		} else if ( isset( $session['filters']['min-price'] ) ) {
			unset( $session['filters']['min-price'] );
		}
	} else if ( isset( $session['filters']['min-price'] ) ) {
		unset( $session['filters']['min-price'] );
	}
	if ( isset( $_GET['max-price'] ) ) {
		$filter_value = floatval( $_GET['max-price'] );
		if ( ! empty( $filter_value ) ) {
			if ( ! isset( $session['filters'] ) ) {
				$session['filters'] = array();
			}
			$session['filters']['max-price'] = $filter_value;
		} else if ( isset( $session['filters']['max-price'] ) ) {
			unset( $session['filters']['max-price'] );
		}
	} else if ( isset( $session['filters']['max-price'] ) ) {
		unset( $session['filters']['max-price'] );
	}
	set_product_catalog_session( $session );
}

add_action( 'apply_product_filters', 'ic_price_filter_apply' );

/**
 * Applies product price filter
 *
 * @param type $query
 */
function ic_price_filter_apply( $query ) {
	if ( is_product_filter_active( 'min-price' ) || is_product_filter_active( 'max-price' ) ) {
		$metaquery = array();
		$min_price = get_product_filter_value( 'min-price' );
		if ( ! empty( $min_price ) ) {
			if ( is_numeric( $min_price ) && floor( $min_price ) != $min_price ) {
				$min_price = price_format( $min_price, 1, 0 );
			}
			$metaquery[] = array(
				'key'     => apply_filters( 'ic_price_meta_name', '_price' ),
				'compare' => '>=',
				'value'   => $min_price,
				'type'    => 'DECIMAL'
			);
		}
		$max_price = get_product_filter_value( 'max-price' );
		if ( ! empty( $max_price ) ) {
			if ( is_numeric( $max_price ) && floor( $max_price ) != $max_price ) {
				$max_price = price_format( $max_price, 1, 0 );
			}
			$metaquery[] = array(
				'key'     => apply_filters( 'ic_price_meta_name', '_price' ),
				'compare' => '<=',
				'value'   => $max_price,
				'type'    => 'DECIMAL'
			);
		}
		$query->set( 'meta_query', $metaquery );
	}
}

//add_filter( 'shortcode_query', 'ic_price_filter_shortcode_apply' );
//add_filter( 'home_product_listing_query', 'ic_price_filter_shortcode_apply' );
//add_filter( 'category_count_query', 'ic_price_filter_shortcode_apply', 10, 2 );
add_filter( 'apply_shortcode_product_filters', 'ic_price_filter_shortcode_apply', 10, 2 );

/**
 * Applies product price filter to shortcode query
 *
 * @param type $shortcode_query
 *
 * @return string
 */
function ic_price_filter_shortcode_apply( $shortcode_query, $taxonomy = null ) {
	if ( ! empty( $taxonomy ) && ! is_array( $taxonomy ) && ic_string_contains( $taxonomy, 'al_product' ) && $taxonomy === 'al_product-price_ranges' ) {
		return $shortcode_query;
	}
	if ( is_product_filter_active( 'min-price' ) || is_product_filter_active( 'max-price' ) ) {
		$min_price = get_product_filter_value( 'min-price' );
		$max_price = get_product_filter_value( 'max-price' );

		if ( ! empty( $min_price ) ) {
			if ( is_numeric( $min_price ) && floor( $min_price ) != $min_price ) {
				$min_price = price_format( $min_price, 1, 0 );
			}
			$metaquery[] = array(
				'key'     => apply_filters( 'ic_price_meta_name', '_price' ),
				'compare' => '>=',
				'value'   => $min_price,
				'type'    => 'DECIMAL'
			);
		}
		if ( ! empty( $max_price ) ) {
			if ( is_numeric( $max_price ) && floor( $max_price ) != $max_price ) {
				$max_price = price_format( $max_price, 1, 0 );
			}
			$metaquery[] = array(
				'key'     => apply_filters( 'ic_price_meta_name', '_price' ),
				'compare' => '<=',
				'value'   => $max_price,
				'type'    => 'DECIMAL'
			);
		}
		$shortcode_query['meta_query'] = $metaquery;
	}

	return $shortcode_query;
}

function ic_catalog_price_filter_reset() {
	if ( is_product_filter_active( 'min-price' ) || is_product_filter_active( 'max-price' ) ) {
		$active_filters = get_active_product_filters( true );
		$url            = remove_query_arg( array_keys( $active_filters ) );
		unset( $active_filters['min-price'] );
		unset( $active_filters['max-price'] );
		unset( $active_filters['price-range'] );
		$reset_url = add_query_arg( $active_filters, $url );
		if ( is_filter_bar() ) {
			$reset_url .= '#product_filters_bar';
		}
		?>
        <a class="price-filter-reset"
           href="<?php echo esc_url( $reset_url ) ?>"><?php _e( 'Reset', 'ecommerce-product-catalog' ) ?></a>
		<?php
	}
}

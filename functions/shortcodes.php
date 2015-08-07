<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages plugin shortcodes
 *
 * Here all shortcodes are defined.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        Norbert Dreszer
 */
add_shortcode( 'display_product_categories', 'parent_cat_list' );

/**
 * Shows product category child urls
 *
 * @param type $atts
 * @return type
 */
function parent_cat_list( $atts ) {
	$output = wp_list_categories( 'title_li=&orderby=name&depth=1&taxonomy=al_product-cat&echo=0' );
	return $output;
}

add_shortcode( 'show_categories', 'product_cat_shortcode' );

/**
 * Defines [show_categories] shortcode
 *
 * @global type $cat_shortcode_query
 * @param type $atts
 * @return string
 */
function product_cat_shortcode( $atts ) {
	global $cat_shortcode_query, $product_sort, $archive_template;
	$cat_shortcode_query				 = array();
	$cat_shortcode_query[ 'current' ]	 = 0;
	$args								 = shortcode_atts( array(
		'exclude'			 => array(),
		'include'			 => array(),
		'archive_template'	 => get_option( 'archive_template', 'default' ),
		'parent'			 => '',
		'sort'				 => 0,
		'shortcode_query'	 => 'yes',
	), $atts );
	$div								 = '<div class="product-subcategories ' . $args[ 'archive_template' ] . ' ' . product_list_class( $args[ 'archive_template' ], 'category-list' ) . '">';
	$cats								 = get_terms( 'al_product-cat', $args );
	$cat_shortcode_query[ 'count' ]		 = count( $cats );
	$cat_shortcode_query[ 'enable' ]	 = $args[ 'shortcode_query' ];
	$product_sort						 = intval( $args[ 'sort' ] );
	$inside								 = '';
	if ( $args[ 'parent' ] == '' && empty( $args[ 'include' ] ) ) {
		$old_args			 = $args;
		$args[ 'parent' ]	 = 0;
		$cats				 = get_terms( 'al_product-cat', $args );
		foreach ( $cats as $cat ) {
			$inside .= get_product_category_template( $args[ 'archive_template' ], $cat );
			$cat_shortcode_query[ 'current' ] ++;
			$inside .= get_sub_product_subcategories( $args, $cat );
		}
	} else {
		foreach ( $cats as $cat ) {
			$inside .= get_product_category_template( $args[ 'archive_template' ], $cat );
			$cat_shortcode_query[ 'current' ] ++;
		}
	}
	if ( !empty( $inside ) ) {
		$ready	 = apply_filters( 'category_list_ready', $inside, $args[ 'archive_template' ] );
		ob_start();
		do_action( 'before_category_list', $archive_template );
		$inside	 = ob_get_contents();
		ob_end_clean();
		$inside .= $div . $ready;
		$inside .= '</div>';
	}
	reset_row_class();
	return $inside;
}

add_shortcode( 'product_category_name', 'product_category_name' );

/**
 * Returns current product category name
 */
function product_category_name() {
	$the_tax = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
	return $the_tax->name;
}

function get_sub_product_subcategories( $args, $parent_cat ) {
	global $cat_shortcode_query;
	$args[ 'parent' ]	 = $parent_cat->term_id;
	$cats				 = get_terms( 'al_product-cat', $args );
	$return				 = '';
	foreach ( $cats as $cat ) {
		$return .= get_product_category_template( $args[ 'archive_template' ], $cat );
		$cat_shortcode_query[ 'current' ] ++;
		$return .= get_sub_product_subcategories( $args, $cat );
	}
	return $return;
}

add_shortcode( 'product_name', 'ic_product_name' );

/**
 * Shows product name
 *
 * @param type $atts
 * @return string
 */
function ic_product_name( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	return get_product_name( $args[ 'product' ] );
}

add_shortcode( 'product_price', 'ic_product_price' );

/**
 * Shows product price
 * @param type $atts
 * @return string
 */
function ic_product_price( $atts ) {
	$args	 = shortcode_atts( array(
		'product'	 => get_the_ID(),
		'formatted'	 => 1,
	), $atts );
	$price	 = product_price( $args[ 'product' ] );
	if ( !empty( $price ) && $args[ 'formatted' ] == 1 ) {
		$price = price_format( $price );
	}
	return $price;
}

add_shortcode( 'product_price_table', 'ic_product_price_table' );

/**
 * Shows product price table
 *
 * @param type $atts
 * @return string
 */
function ic_product_price_table( $atts ) {
	$args			 = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	$single_names	 = get_single_names();
	return get_product_price_table( $args[ 'product' ], $single_names );
}

add_shortcode( 'product_description', 'ic_product_description' );

/**
 * Shows product description
 *
 * @param type $atts
 * @return string
 */
function ic_product_description( $atts ) {
	$args				 = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	$product_description = get_product_description( $args[ 'product' ] );
	return apply_filters( 'the_content', $product_description );
}

add_shortcode( 'product_short_description', 'ic_product_short_description' );

/**
 * Shows product short description
 *
 * @param type $atts
 * @return string
 */
function ic_product_short_description( $atts ) {
	$args		 = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	$shortdesc	 = get_product_short_description( $args[ 'product' ] );
	return apply_filters( 'product_short_description', $shortdesc );
}

add_shortcode( 'product_attributes', 'ic_product_attributes' );

/**
 * Shows product attributes table
 *
 * @param type $atts
 * @return string
 */
function ic_product_attributes( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	return get_product_attributes( $args[ 'product' ] );
}

add_shortcode( 'product_sku', 'ic_product_sku' );

/**
 * Shows product SKU value
 *
 * @param type $atts
 * @return string
 */
function ic_product_sku( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	return get_product_sku( $args[ 'product' ] );
}

add_shortcode( 'product_sku_table', 'ic_product_sku_table' );

/**
 * Shows product SKU value
 *
 * @param type $atts
 * @return string
 */
function ic_product_sku_table( $atts ) {
	$args			 = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	$single_names	 = get_single_names();
	return get_product_sku_table( $args[ 'product' ], $single_names );
}

add_shortcode( 'product_shipping', 'ic_product_shipping' );

/**
 * Shows product shipping table
 *
 * @param type $atts
 * @return string
 */
function ic_product_shipping( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	return get_shipping_options_table( $args[ 'product' ] );
}

add_shortcode( 'product_gallery', 'ic_product_gallery' );

/**
 * Shows product gallery
 *
 * @param type $atts
 * @return string
 */
function ic_product_gallery( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	return get_product_gallery( $args[ 'product' ] );
}

add_shortcode( 'product_related_categories', 'ic_product_related_categories' );

/**
 * Shows product related categories
 *
 * @param type $atts
 * @return string
 */
function ic_product_related_categories( $atts ) {
	$args = shortcode_atts( array(
		'product' => get_the_ID(),
	), $atts );
	return get_related_categories( $args[ 'product' ] );
}

add_shortcode( 'back_to_products_url', 'ic_back_to_prodcts_url' );

/**
 * Shows back to products URL
 *
 * @param type $atts
 * @return string
 */
function ic_back_to_prodcts_url( $atts ) {
	return get_back_to_products_url();
}

add_shortcode( 'product_breadcrumbs', 'ic_product_breadcrumbs' );

/**
 * Shows product breadcrumbs
 *
 * @return string
 */
function ic_product_breadcrumbs() {
	return product_breadcrumbs();
}

add_shortcode( 'product_listing_products', 'ic_product_listing_products' );

/**
 * Shows products on product listing for custom templates usage
 *
 * @return type
 */
function ic_product_listing_products() {
	ob_start();
	global $post;
	$multiple_settings	 = get_multiple_settings();
	$archive_template	 = get_product_listing_template();
	if ( is_home_archive() ) {
		$args	 = array( 'post_type' => 'al_product', 'posts_per_page' => $multiple_settings[ 'archive_products_limit' ] );
		query_posts( $args );
		$is_home = 1;
	}
	if ( (is_tax() || is_search() || !is_ic_only_main_cats()) && more_products() ) {
		do_action( 'before_product_list', $archive_template, $multiple_settings );
		$product_list = '';
		while ( have_posts() ) : the_post();
			$product_list .= get_catalog_template( $archive_template, $post );
		endwhile;
		if ( isset( $is_home ) ) {
			wp_reset_query();
		}
		$product_list = apply_filters( 'product_list_ready', $product_list, $archive_template, 'auto_listing' );
		echo '<div class="product-list responsive ' . $archive_template . ' ' . product_list_class( $archive_template ) . '">' . $product_list . '</div><span class="clear"></span>';
	} else if ( is_search() && !more_products() ) {
		echo '<p>' . __( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'al-ecommerce-product-catalog' ) . '</p>';
		product_search_form();
	}
	return ob_get_clean();
}

add_shortcode( 'product_listing_categories', 'ic_product_listing_categories' );

/**
 * Shows categories on product listing for custom templates usage
 *
 * @return string
 */
function ic_product_listing_categories() {
	ob_start();
	$multiple_settings	 = get_multiple_settings();
	$archive_template	 = get_product_listing_template();
	$taxonomy_name		 = apply_filters( 'current_product_catalog_taxonomy', 'al_product-cat' );
	if ( !is_tax() && !is_search() ) {
		$before_archive = content_product_adder_archive_before();
		if ( $before_archive != '<div class="entry-summary"></div>' ) {
			echo $before_archive;
		}
		if ( $multiple_settings[ 'product_listing_cats' ] == 'on' || $multiple_settings[ 'product_listing_cats' ] == 'cats_only' ) {
			do_action( 'before_product_listing_category_list' );
			if ( $multiple_settings[ 'cat_template' ] != 'template' ) {
				$product_subcategories = wp_list_categories( 'show_option_none=No_cat&echo=0&title_li=&taxonomy=' . $taxonomy_name . '&parent=0' );
				if ( !strpos( $product_subcategories, 'No_cat' ) ) {
					echo '<div class="product-subcategories">' . $product_subcategories . '</div>';
				}
			} else {
				$show_categories = do_shortcode( '[show_categories parent="0" shortcode_query="no"]' );
				if ( !empty( $show_categories ) ) {
					echo $show_categories;
					if ( $archive_template != 'list' && !is_ic_only_main_cats() ) {
						echo '<hr>';
					}
				}
			}
		}
	} else if ( is_tax() ) {
		$term = get_queried_object()->term_id;
		if ( is_ic_category_image_enabled() ) {
			$term_img = get_product_category_image_id( $term );
			echo wp_get_attachment_image( $term_img, apply_filters( 'product_cat_image_size', 'large' ), false, array( 'class' => 'product-category-image' ) );
		}
		$term_description = term_description();
		if ( !empty( $term_description ) ) {
			echo '<div class="taxonomy-description">' . $term_description . '</div>';
		}
		if ( $multiple_settings[ 'category_top_cats' ] == 'on' || $multiple_settings[ 'category_top_cats' ] == 'only_subcategories' ) {
			if ( $multiple_settings[ 'cat_template' ] != 'template' ) {
				$product_subcategories = wp_list_categories( 'show_option_none=No_cat&echo=0&title_li=&taxonomy=' . $taxonomy_name . '&child_of=' . $term );
				if ( !strpos( $product_subcategories, 'No_cat' ) ) {
					?>
					<div class="product-subcategories">
						<?php
						do_action( 'before_category_subcategories' );
						echo $product_subcategories;
						?>
					</div>
					<?php
				}
			} else {
				$show_categories = do_shortcode( '[show_categories parent=' . get_queried_object_id() . ' shortcode_query=no]' );
				if ( !empty( $show_categories ) ) {
					do_action( 'before_category_subcategories' );
					echo $show_categories;
					if ( $archive_template != 'list' && !is_ic_only_main_cats() ) {
						echo '<hr>';
					}
				}
			}
		}
	}
	return ob_get_clean();
}

add_shortcode( 'product_page_class', 'ic_product_pages_class' );

/**
 * Shows product listing or product page class for templates usage
 *
 * @param type $atts
 * @return string
 */
function ic_product_pages_class( $atts ) {
	$args			 = shortcode_atts( array(
		'custom' => '',
	), $atts );
	$listing_class	 = apply_filters( 'product_listing_classes', 'al_product responsive' );
	if ( !empty( $args[ 'custom' ] ) ) {
		$listing_class .= ' ' . $args[ 'custom' ];
	}
	ob_start();
	post_class( $listing_class );
	return ob_get_clean();
}

add_shortcode( 'product_page_id', 'ic_current_page_id' );

/**
 * Shows current page ID for template usage
 *
 * @return string
 */
function ic_current_page_id() {
	return get_the_ID();
}

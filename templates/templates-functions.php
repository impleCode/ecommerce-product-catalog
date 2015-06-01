<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WP Product template functions
 *
 * Here all plugin template functions are defined.
 *
 * @version		1.1.3
 * @package		ecommerce-product-catalog/
 * @author 		Norbert Dreszer
 */
function content_product_adder() {
	if ( is_archive() || is_search() || is_home_archive() || is_ic_product_listing() ) {
		do_action( 'before_product_archive' );
		content_product_adder_archive();
	} else {
		content_product_adder_single();
	}
}

function content_product_adder_archive() {
	include 'content-al_product_archive.php';
}

function content_product_adder_single() {
	include 'content-al_product.php';
}

function content_product_adder_archive_before() {
	$page_id = apply_filters( 'before_archive_post_id', get_product_listing_id() );
	$page	 = get_post( $page_id );
	if ( $page != '' ) {
		if ( get_integration_type() != 'simple' ) {
			$content = apply_filters( "the_content", $page->post_content );
		} else {
			$content = $page->post_content;
		}
	} else {
		$content = '';
	}
	return '<div class="entry-summary">' . $content . '</div>';
}

function content_product_adder_archive_before_title() {
	$def_page_id	 = get_product_listing_id();
	$archive_names	 = get_archive_names();
	$page_id		 = apply_filters( 'before_archive_post_id', $def_page_id );
	$page			 = get_post( $page_id );
	if ( $page == '' ) {
		echo '<h1 class="entry-title">' . $archive_names[ 'all_products' ] . '</h1>';
	} else {
		echo '<h1 class="entry-title">' . $page->post_title . '</h1>';
	}
}

function show_products_outside_loop( $atts ) {
	global $shortcode_query, $product_sort, $archive_template;
	$args				 = shortcode_atts( array(
		'post_type'			 => 'al_product',
		'category'			 => '',
		'product'			 => '',
		'products_limit'	 => -1,
		'archive_template'	 => get_product_listing_template(),
		'design_scheme'		 => '',
		'sort'				 => 0,
	), $atts );
	$category			 = esc_html( $args[ 'category' ] );
	$product			 = esc_html( $args[ 'product' ] );
	$products_limit		 = intval( $args[ 'products_limit' ] );
	$archive_template	 = esc_attr( $args[ 'archive_template' ] );
	$design_scheme		 = esc_attr( $args[ 'design_scheme' ] );
	$product_sort		 = intval( $args[ 'sort' ] );
	if ( $product != 0 ) {

		$product_array	 = explode( ',', $product );
		$query_param	 = array(
			'post_type'		 => 'al_product',
			'post__in'		 => $product_array,
			'posts_per_page' => $products_limit,
		);
	} else if ( !empty( $category ) ) {
		$category_array	 = explode( ',', $category );
		$field			 = 'name';
		if ( is_numeric( $category_array[ 0 ] ) ) {
			$field = 'term_id';
		}
		$query_param = array(
			'post_type'		 => 'al_product',
			'tax_query'		 => array(
				array(
					'taxonomy'	 => 'al_product-cat',
					'field'		 => $field,
					'terms'		 => $category_array,
				),
			),
			'posts_per_page' => $products_limit,
		);
	} else {
		$query_param = array(
			'post_type'		 => 'al_product',
			'posts_per_page' => $products_limit,
		);
	}
	$query_param	 = apply_filters( 'shortcode_query', $query_param );
	$shortcode_query = new WP_Query( $query_param );
	$inside			 = '';
	$i				 = 0;
	do_action( 'before_product_list', $archive_template );
	while ( $shortcode_query->have_posts() ) : $shortcode_query->the_post();
		global $post;
		$i++;
		$inside .= get_catalog_template( $archive_template, $post, $i, $design_scheme );
	endwhile;
	$inside = apply_filters( 'product_list_ready', $inside, $archive_template );
	wp_reset_postdata();
	reset_row_class();
	return '<div class="product-list responsive ' . $archive_template . '-list ' . product_list_class() . '">' . $inside . '<div style="clear:both"></div></div>';
}

add_shortcode( 'show_products', 'show_products_outside_loop' );

function single_scripts() {
	if ( is_lightbox_enabled() ) {
		wp_enqueue_style( 'colorbox' );
	}
}

add_action( 'wp_enqueue_scripts', 'single_scripts' );
add_action( 'pre_get_posts', 'set_products_limit' );

/**
 * Sets product limit on product listing pages
 * @param object $query
 */
function set_products_limit( $query ) {
	$archive_multiple_settings = get_multiple_settings();
	if ( !is_admin() && (is_post_type_archive( 'al_product' ) || is_tax( 'al_product-cat' ) || is_home_archive( $query )) && $query->is_main_query() ) {
		$query->set( 'posts_per_page', $archive_multiple_settings[ 'archive_products_limit' ] );
	}
}

add_action( 'product_listing_end', 'product_archive_pagination' );

/**
 * Adds paginaion to the product listings
 *
 * @global object $wp_query
 * @return string
 */
function product_archive_pagination() {
	if ( is_singular() || (is_ic_product_listing() && is_ic_only_main_cats()) ) {
		return;
	}

	global $wp_query;
	if ( $wp_query->max_num_pages <= 1 )
		return;
	$paged	 = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
	$max	 = intval( $wp_query->max_num_pages );
	if ( $paged >= 1 )
		$links[] = $paged;
	if ( $paged >= 3 ) {
		$links[] = $paged - 1;
		$links[] = $paged - 2;
	}
	if ( ( $paged + 2 ) <= $max ) {
		$links[] = $paged + 2;
		$links[] = $paged + 1;
	}
	echo '<div id="product_archive_nav" class="product-archive-nav ' . design_schemes( 'box', 0 ) . '"><ul>' . "\n";
	if ( get_previous_posts_link() )
		printf( '<li>%s</li> ' . "\n", get_previous_posts_link() );
	if ( !in_array( 1, $links ) ) {
		$class = 1 == $paged ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li> ' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );
		if ( !in_array( 2, $links ) )
			echo '<li>…</li>';
	}
	sort( $links );
	foreach ( (array) $links as $link ) {
		$class = $paged == $link ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li> ' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
	}
	if ( !in_array( $max, $links ) ) {
		if ( !in_array( $max - 1, $links ) )
			echo '<li>…</li>' . "\n";
		$class = $paged == $max ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li> ' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
	}
	if ( get_next_posts_link() ) {
		printf( '<li>%s</li> ' . "\n", get_next_posts_link() );
	}
	echo '</ul></div>' . "\n";

	wp_reset_postdata();
}

function get_catalog_template( $archive_template, $post, $i = null, $design_scheme = null ) {
	$themes_array						 = apply_filters( 'ecommerce_catalog_templates', array(
		'default'	 => get_default_archive_theme( $post, $archive_template ),
		'list'		 => get_list_archive_theme( $post, $archive_template ),
		'grid'		 => get_grid_archive_theme( $post, $archive_template ),
	), $post, $i, $design_scheme, $archive_template );
	$themes_array[ $archive_template ]	 = isset( $themes_array[ $archive_template ] ) ? $themes_array[ $archive_template ] : $themes_array[ 'default' ];
	$themes_array[ $archive_template ]	 = empty( $themes_array[ $archive_template ] ) ? get_default_archive_theme( $post, 'default' ) : $themes_array[ $archive_template ];
	return $themes_array[ $archive_template ];
}

function get_product_category_template( $archive_template, $product_cat, $i = null, $design_scheme = null ) {
	$themes_array						 = apply_filters( 'ecommerce_category_templates', array(
		'default'	 => get_default_category_theme( $product_cat, $archive_template ),
		'list'		 => get_list_category_theme( $product_cat, $archive_template ),
		'grid'		 => get_grid_category_theme( $product_cat, $archive_template ),
	), $product_cat, $i, $design_scheme, $archive_template );
	$themes_array[ $archive_template ]	 = isset( $themes_array[ $archive_template ] ) ? $themes_array[ $archive_template ] : $themes_array[ 'default' ];
	return $themes_array[ $archive_template ];
}

function more_products() {
	global $wp_query, $shortcode_query;
	$post_type	 = apply_filters( 'current_product_post_type', 'al_product' );
	$taxonomy	 = apply_filters( 'current_product_catalog_taxonomy', 'al_product-cat' );
	if ( (isset( $wp_query->query[ 'post_type' ] ) && $wp_query->query[ 'post_type' ] == $post_type) || isset( $wp_query->query[ $taxonomy ] ) ) {
		$y_query = $wp_query;
	} else {
		$y_query = $shortcode_query;
	}
	if ( isset( $y_query->current_post ) ) {
		return $y_query->current_post + 1 < $y_query->post_count;
	} else {
		return false;
	}
}

function more_product_cats() {
	global $cat_shortcode_query;
	if ( isset( $cat_shortcode_query[ 'current' ] ) ) {
		$result = $cat_shortcode_query[ 'current' ] + 1 < $cat_shortcode_query[ 'count' ];
		return $result;
	} else {
		return false;
	}
}

function get_row_class( $grid_settings ) {
	$row_class = 'full';
	if ( $grid_settings[ 'entries' ] != '' ) {
		global $row;
		if ( $row > $grid_settings[ 'entries' ] || !isset( $row ) ) {
			$row = 1;
		}
		$count = $row - $grid_settings[ 'entries' ];
		if ( $row == 1 ) {
			$row_class = 'first';
		} else if ( $count == 0 ) {
			$row_class = 'last';
		} else {
			$row_class = 'middle';
		}
		if ( more_products() || more_product_cats() ) {
			$row++;
		} else {
			$row = 1;
		}
	}
	return $row_class;
}

function reset_row_class() {
	global $row;
	$row = 1;
}

add_filter( 'post_class', 'product_post_class' );

/**
 * Deletes default WordPress has-post-thumbnail class
 *
 * @param array $classes
 * @return array
 */
function product_post_class( $classes ) {
	if ( is_ic_catalog_page() && ($key = array_search( 'has-post-thumbnail', $classes )) !== false ) {
		unset( $classes[ $key ] );
	}
	return $classes;
}

add_action( 'before_product_list', 'product_listing_additional_styles' );
add_action( 'before_category_list', 'product_listing_additional_styles' );

/**
 * Ads product listing inline styles container
 */
function product_listing_additional_styles() {
	$styles	 = '<style>';
	$styles	 = apply_filters( 'product_listing_additional_styles', $styles );
	$styles .= '</style>';
	if ( $styles != '<style></style>' && !is_admin() ) {
		echo $styles;
	}
}

add_action( 'before_product_entry', 'product_page_additional_styles' );

/**
 * Ads product page inline styles container
 */
function product_page_additional_styles() {
	$styles	 = '<style>';
	$styles	 = apply_filters( 'product_page_additional_styles', $styles );
	$styles .= '</style>';
	if ( $styles != '<style></style>' && !is_admin() ) {
		echo $styles;
	}
}

/**
 * Returns product listing template defined in settings
 *
 * @return string
 */
function get_product_listing_template() {
	global $shortcode_query;
	if ( isset( $shortcode_query ) ) {
		global $archive_template;
		$archive_template = isset( $archive_template ) ? $archive_template : get_option( 'archive_template', DEFAULT_ARCHIVE_TEMPLATE );
	} else {
		$archive_template = get_option( 'archive_template', DEFAULT_ARCHIVE_TEMPLATE );
	}
	$archive_template = !empty( $archive_template ) ? $archive_template : 'default';
	return $archive_template;
}

function show_parent_product_categories( $echo = 1, $return = '' ) {
	$multiple_settings	 = get_multiple_settings();
	$taxonomy_name		 = apply_filters( 'current_product_catalog_taxonomy', 'al_product-cat' );
	$archive_template	 = get_product_listing_template();
	if ( $multiple_settings[ 'product_listing_cats' ] == 'on' ) {
		if ( $multiple_settings[ 'cat_template' ] != 'template' ) {
			$product_subcategories = wp_list_categories( 'show_option_none=No_cat&echo=0&title_li=&taxonomy=' . $taxonomy_name . '&parent=0' );
			if ( !strpos( $product_subcategories, 'No_cat' ) ) {
				$return = '<div class="product-subcategories">' . $product_subcategories . '</div>';
			}
		} else {
			$show_categories = do_shortcode( '[show_categories parent="0"]' );
			if ( !empty( $show_categories ) ) {
				$return = '<div class="product-subcategories ' . $archive_template . '">' . $show_categories;
				if ( $archive_template != 'list' ) {
					$return .= '<hr>';
				}
				$return .= '</div>';
			}
		}
	}
	return echo_ic_setting( $return, $echo );
}

function override_product_page_title( $page_title, $id = null ) {
	if ( !is_admin() && is_ic_catalog_page() && !is_ic_product_page() && !in_the_loop() && (empty( $id ) || (get_quasi_post_type( get_post_type( $id ) ) == 'al_product')) ) {
		$archive_names = get_archive_names();
		if ( is_ic_taxonomy_page() ) {
			$the_tax	 = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			$page_title	 = $archive_names[ 'all_prefix' ] . ' ' . $the_tax->name;
		} else if ( is_search() ) {
			$page_title = __( 'Search Results for:', 'al-ecommerce-product-catalog' ) . ' ' . $_GET[ 's' ];
		} else if ( is_ic_product_listing() ) {
			$page_title = get_product_listing_title();
		}
	}
	return $page_title;
}

add_filter( 'the_title', 'override_product_page_title', 10, 2 );

function get_product_listing_title() {
	$archive_names	 = get_archive_names();
	$def_page_id	 = get_product_listing_id();
	$page_id		 = apply_filters( 'before_archive_post_id', $def_page_id );
	$page			 = get_post( $page_id );
	if ( $page == '' ) {
		$page_title = $archive_names[ 'all_products' ];
	} else {
		$page_title = $page->post_title;
	}
	return $page_title;
}

function product_listing_current_nav_class( $classes, $item ) {
	global $post;
	if ( isset( $post->ID ) && $item->object_id == get_product_listing_id() && is_post_type_archive( 'al_product' ) ) {
		$current_post_type		 = get_post_type_object( get_post_type( $post->ID ) );
		$current_post_type_slug	 = $current_post_type->rewrite[ 'slug' ];
		$current_post_type_slug	 = !empty( $current_post_type_slug ) ? '/' . $current_post_type_slug . '/' : $current_post_type_slug;
		$menu_slug				 = strtolower( trim( $item->url ) );
		if ( strpos( $menu_slug, $current_post_type_slug ) !== false ) {
			$classes[] = 'current-menu-item';
		}
	}
	return $classes;
}

add_action( 'nav_menu_css_class', 'product_listing_current_nav_class', 10, 2 );

/**
 * Defines custom classes to product or category listing div
 * @return string
 */
function product_list_class( $where = 'product-list' ) {
	return apply_filters( 'product-list-class', '', $where );
}

/**
 * Defines custom classes to product or category element div
 * @return string
 */
function product_class( $product_id ) {
	return apply_filters( 'product-class', '', $product_id );
}

add_action( 'before_product_listing_category_list', 'product_list_categories_header' );

/**
 * Adds product main categories label on product listing
 *
 */
function product_list_categories_header() {
	$archive_names = get_archive_names();
	if ( !empty( $archive_names[ 'all_main_categories' ] ) && !isset( $shortcode_query ) ) {
		echo '<h2>' . do_shortcode( $archive_names[ 'all_main_categories' ] ) . '</h2>';
	}
}

add_action( 'before_category_subcategories', 'category_list_subcategories_header' );

/**
 * Adds product subcategories label on category product listing
 *
 */
function category_list_subcategories_header() {
	$archive_names = get_archive_names();
	if ( !empty( $archive_names[ 'all_subcategories' ] ) && !is_ic_shortcode_query() ) {
		echo '<h2>' . do_shortcode( $archive_names[ 'all_subcategories' ] ) . '</h2>';
	}
}

add_action( 'before_product_list', 'product_list_header', 9 );

/**
 * Adds product header on product listing
 *
 */
function product_list_header() {
	$archive_names = get_archive_names();
	if ( !empty( $archive_names[ 'all_products' ] ) && !is_ic_shortcode_query() ) {
		if ( !is_tax() ) {
			echo '<h2>' . do_shortcode( $archive_names[ 'all_products' ] ) . '</h2>';
		} else if ( is_tax() && is_ic_product_listing_showing_cats() ) {
			//$the_tax = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			echo '<h2>' . do_shortcode( $archive_names[ 'category_products' ] ) . '</h2>';
		}
	}
}

/**
 * Defines example image URL
 *
 * @return string
 */
function design_settings_examples_image() {
	return AL_PLUGIN_BASE_PATH . 'templates/themes/img/example-product.jpg';
}

add_filter( 'parse_tax_query', 'exclude_products_from_child_cat' );

function exclude_products_from_child_cat( $query ) {
	if ( !is_admin() && $query->is_main_query() && $query->is_tax( 'al_product-cat' ) && is_ic_only_main_cats() ) {
		foreach ( $query->tax_query->queries as $i => $xquery ) {
			$query->tax_query->queries[ $i ][ 'include_children' ] = 0;
		}
	}
}

add_filter( 'product_listing_classes', 'add_classes_on_categories' );

/**
 * Adds neccessary classes for some themes
 * @param string $classes
 * @return string
 */
function add_classes_on_categories( $classes ) {
	if ( is_tax() && is_ic_only_main_cats() ) {
		$classes .= ' hentry status-publish';
	}
	return $classes;
}

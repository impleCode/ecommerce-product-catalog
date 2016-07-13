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
add_shortcode( 'content_product_adder', 'content_product_adder' );

/**
 * The function wrapper to show product catalog content for current URL
 *
 */
function content_product_adder() {
	if ( is_archive() || is_search() || is_home_archive() || is_ic_product_listing() ) {
		do_action( 'before_product_archive' );
		content_product_adder_archive();
		do_action( 'after_product_archive' );
	} else {
		do_action( 'before_product_page' );
		content_product_adder_single();
		do_action( 'after_product_page' );
	}
}

function content_product_adder_archive() {
	$path = get_custom_product_listing_path();
	if ( file_exists( $path ) ) {
		ob_start();
		include apply_filters( 'content_product_adder_archive_path', $path );
		$product_listing = ob_get_clean();
		echo do_shortcode( $product_listing );
	} else {
		include apply_filters( 'content_product_adder_archive_path', AL_BASE_PATH . '/templates/full/product-listing.php' );
	}
}

function content_product_adder_single() {
	$path = get_custom_product_page_path();
	if ( file_exists( $path ) ) {
		ob_start();
		include apply_filters( 'content_product_adder_path', $path );
		$product_page = ob_get_clean();
		echo do_shortcode( $product_page );
	} else {
		include apply_filters( 'content_product_adder_path', AL_BASE_PATH . '/templates/full/product-page.php' );
	}
}

function content_product_adder_archive_before() {
	$page_id = apply_filters( 'before_archive_post_id', get_product_listing_id() );
	$page	 = empty( $page_id ) ? '' : get_post( $page_id );
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
	$page			 = empty( $page_id ) ? '' : get_post( $page_id );
	if ( $page == '' ) {
		echo '<h1 class="entry-title">' . $archive_names[ 'all_products' ] . '</h1>';
	} else {
		echo '<h1 class="entry-title">' . $page->post_title . '</h1>';
	}
}

function show_products_outside_loop( $atts ) {
	global $shortcode_query, $product_sort, $archive_template, $shortcode_args;
	$available_args		 = apply_filters( 'show_products_shortcode_args', array(
		'post_type'			 => 'al_product',
		'category'			 => '',
		'product'			 => '',
		'exclude'			 => '',
		'products_limit'	 => -1,
		'archive_template'	 => get_product_listing_template(),
		'design_scheme'		 => '',
		'sort'				 => 0,
		'orderby'			 => '',
		'order'				 => '',
		'pagination'		 => 0
	) );
	$args				 = shortcode_atts( $available_args, $atts );
	$shortcode_args		 = $args;
	$category			 = esc_html( $args[ 'category' ] );
	$product			 = esc_html( $args[ 'product' ] );
	$exclude			 = esc_html( $args[ 'exclude' ] );
	$products_limit		 = intval( $args[ 'products_limit' ] );
	$archive_template	 = esc_attr( $args[ 'archive_template' ] );
	$design_scheme		 = esc_attr( $args[ 'design_scheme' ] );
	$product_sort		 = intval( $args[ 'sort' ] );
	$post_type			 = empty( $args[ 'post_type' ] ) ? 'al_product' : $args[ 'post_type' ];
	if ( $product != 0 ) {

		$product_array	 = explode( ',', $product );
		$query_param	 = array(
			'post_type'		 => $post_type,
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
			'post_type'		 => $post_type,
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
			'post_type'		 => $post_type,
			'posts_per_page' => $products_limit,
		);
		if ( !empty( $exclude ) ) {
			$query_param[ 'post__not_in' ] = explode( ',', $exclude );
		}
	}
	if ( !empty( $args[ 'orderby' ] ) ) {
		$query_param[ 'orderby' ] = esc_attr( $args[ 'orderby' ] );
	}
	if ( !empty( $args[ 'order' ] ) ) {
		$query_param[ 'order' ] = esc_attr( $args[ 'order' ] );
	}
	if ( !empty( $args[ 'pagination' ] ) ) {
		$paged					 = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
		$query_param[ 'paged' ]	 = $paged;
	}
	$query_param	 = apply_filters( 'shortcode_query', $query_param, $args );
	$shortcode_query = new WP_Query( $query_param );
	$inside			 = '';
	$i				 = 0;
	ob_start();
	do_action( 'before_product_list', $archive_template );
	$before			 = ob_get_contents();
	ob_end_clean();

	while ( $shortcode_query->have_posts() ) : $shortcode_query->the_post();
		global $post;
		$i++;
		$inside .= get_catalog_template( $archive_template, $post, $i, $design_scheme );
	endwhile;
	$pagination = '';
	if ( !empty( $args[ 'pagination' ] ) ) {
		ob_start();
		product_archive_pagination( $shortcode_query );
		$pagination = ob_get_clean();
	}
	$inside = apply_filters( 'product_list_ready', $inside, $archive_template, $args );
	wp_reset_postdata();
	reset_row_class();
	unset( $shortcode_args );
	return $before . '<div class="product-list responsive ' . $archive_template . ' ' . product_list_class( $archive_template ) . '">' . $inside . '<div style="clear:both"></div>' . $pagination . '</div>';
}

add_shortcode( 'show_products', 'show_products_outside_loop' );

function single_scripts() {
	if ( is_lightbox_enabled() ) {
		wp_enqueue_style( 'colorbox' );
	}
}

add_action( 'wp_enqueue_scripts', 'single_scripts' );
add_action( 'pre_get_posts', 'set_products_limit', 99 );

/**
 * Sets product limit on product listing pages
 * @param object $query
 */
function set_products_limit( $query ) {
	$archive_multiple_settings = get_multiple_settings();
	if ( !is_admin() && $query->is_main_query() && (is_ic_product_listing( $query ) || is_ic_taxonomy_page() || is_home_archive( $query ) || is_ic_product_search()) ) {
		$query->set( 'posts_per_page', $archive_multiple_settings[ 'archive_products_limit' ] );
		do_action( 'pre_get_al_products', $query );
	}
}

add_action( 'product_listing_end', 'product_archive_pagination' );

/**
 * Adds paginaion to the product listings
 *
 * @global object $wp_query
 * @return string
 */
function product_archive_pagination( $wp_query = null ) {
	if ( is_ic_product_listing() && is_ic_only_main_cats() ) {
		return;
	}
	if ( !isset( $wp_query ) || !is_object( $wp_query ) ) {
		global $paged, $wp_query;
	}
	if ( $wp_query->max_num_pages <= 1 ) {
		return;
	}

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
	$names = get_archive_names();
	echo '<div id="product_archive_nav" class="product-archive-nav ' . design_schemes( 'box', 0 ) . '"><ul>' . "\n";
	if ( get_previous_posts_link( $names[ 'previous_products' ] ) ) {
		printf( '<li>%s</li> ' . "\n", get_previous_posts_link( $names[ 'previous_products' ] ) );
	}
	if ( !in_array( 1, $links ) ) {
		$class = 1 == $paged ? ' class="active first-num"' : ' class="first-num"';
		printf( '<li%s><a href="%s">%s</a></li> ' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );
		if ( !in_array( 2, $links ) ) {
			echo '<li class="nav-dots">...</li>';
		}
	}
	sort( $links );
	foreach ( (array) $links as $link ) {
		$class = $paged == $link ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li> ' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
	}
	if ( !in_array( $max, $links ) ) {
		if ( !in_array( $max - 1, $links ) ) {
			echo '<li class="nav-dots">...</li>' . "\n";
		}
		$class = $paged == $max ? ' class="active last-num"' : ' class="last-num"';
		printf( '<li%s><a href="%s">%s</a></li> ' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
	}
	if ( get_next_posts_link( $names[ 'next_products' ], $max ) ) {
		printf( '<li>%s</li> ' . "\n", get_next_posts_link( $names[ 'next_products' ], $max ) );
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
	if ( (isset( $wp_query->query[ 'post_type' ] ) && $wp_query->query[ 'post_type' ] == $post_type) || (isset( $wp_query->query_vars[ 'post_type' ] ) && is_array( $wp_query->query_vars[ 'post_type' ] ) && array_search( $post_type, $wp_query->query_vars[ 'post_type' ] ) !== false) || isset( $wp_query->query[ $taxonomy ] ) ) {
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
		global $ic_row;
		if ( $ic_row > $grid_settings[ 'entries' ] || !isset( $ic_row ) ) {
			$ic_row = 1;
		}
		$count = $ic_row - $grid_settings[ 'entries' ];
		if ( $ic_row == 1 ) {
			$row_class = 'first';
		} else if ( $count == 0 ) {
			$row_class = 'last';
		} else {
			$row_class = 'middle';
		}
		if ( more_products() || more_product_cats() ) {
			$ic_row++;
		} else {
			$ic_row = 1;
		}
	}
	return $row_class;
}

function reset_row_class() {
	global $ic_row;
	$ic_row = 1;
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
//add_action( 'before_product_archive', 'product_listing_additional_styles' );

/**
 * Ads product listing inline styles container
 */
function product_listing_additional_styles( $archive_template ) {
	$styles	 = '<style>';
	$styles	 = apply_filters( 'product_listing_additional_styles', $styles, $archive_template );
	$styles .= '</style>';
	if ( $styles != '<style></style>' && !is_admin() ) {
		echo $styles;
	}
}

//add_action( 'before_product_entry', 'product_page_additional_styles' );
add_action( 'before_product_page', 'product_page_additional_styles' );

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

/*
  function show_parent_product_categories( $echo = 1, $return = '' ) {
  $multiple_settings	 = get_multiple_settings();
  $taxonomy_name		 = apply_filters( 'current_product_catalog_taxonomy', 'al_product-cat' );
  $archive_template	 = get_product_listing_template();
  if ( $multiple_settings[ 'product_listing_cats' ] == 'on' ) {
  if ( $multiple_settings[ 'cat_template' ] != 'template' ) {
  $product_subcategories = wp_list_categories( 'show_option_none=No_cat&echo=0&title_li=&taxonomy=' . $taxonomy_name . '&parent=0' );
  if ( !strpos( $product_subcategories, 'No_cat' ) ) {
  ic_save_global( 'current_product_categories', $product_subcategories );
  ob_start();
  ic_show_template_file( 'product-listing/categories-list.php' );
  $return = ob_get_clean();
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
 */
add_filter( 'the_title', 'override_product_page_title', 10, 2 );

/**
 * Replaces auto products listing, product category pages and product search title with appropriate entries
 *
 * @param string $page_title
 * @param int $id
 * @return string
 */
function override_product_page_title( $page_title, $id = null ) {
	if ( !is_admin() && is_ic_catalog_page() && !is_ic_product_page() && !in_the_loop() && !is_filter_bar() && !is_ic_shortcode_query() && (empty( $id ) || (get_quasi_post_type( get_post_type( $id ) ) == 'al_product')) ) {

		$archive_names = get_archive_names();
		if ( is_ic_taxonomy_page() ) {
			$the_tax = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			if ( !empty( $archive_names[ 'all_prefix' ] ) && has_shortcode( $archive_names[ 'all_prefix' ], 'product_category_name' ) ) {
				$page_title = do_shortcode( $archive_names[ 'all_prefix' ] );
			} else if ( !empty( $archive_names[ 'all_prefix' ] ) && isset( $the_tax->name ) ) {
				$page_title = do_shortcode( $archive_names[ 'all_prefix' ] ) . ' ' . $the_tax->name;
			}
		} else if ( is_ic_product_search() ) {
			$page_title = __( 'Search Results for:', 'ecommerce-product-catalog' ) . ' ' . $_GET[ 's' ];
		} else if ( is_ic_product_listing() ) {
			$page_title = get_product_listing_title();
		}
	}
	return $page_title;
}

function get_product_listing_title() {
	$archive_names	 = get_archive_names();
	$def_page_id	 = get_product_listing_id();
	$page_id		 = apply_filters( 'before_archive_post_id', $def_page_id );
	$page			 = empty( $page_id ) ? '' : get_post( $page_id );
	if ( $page == '' ) {
		$archive_multiple_settings = get_multiple_settings();
		if ( $archive_multiple_settings[ 'product_listing_cats' ] == 'off' ) {
			$page_title = $archive_names[ 'all_products' ];
		} else {
			$page_title = $archive_names[ 'all_main_categories' ];
		}
	} else {
		$page_title = apply_filters( 'the_title', $page->post_title, $page_id );
	}
	return apply_filters( 'ic_product_listing_title', $page_title, $page );
}

add_filter( 'nav_menu_css_class', 'product_listing_current_nav_class', 10, 2 );

/**
 * Adds product post type navigation menu current class
 *
 * @global type $post
 * @param string $classes
 * @param type $item
 * @return string
 */
function product_listing_current_nav_class( $classes, $item ) {
	global $post;
	if ( isset( $post->ID ) && is_ic_product_listing() ) {
		if ( $item->object_id == get_product_listing_id() ) {

			//$current_post_type		 = get_post_type_object( get_post_type( $post->ID ) );
			//$current_post_type_slug	 = $current_post_type->rewrite[ 'slug' ];
			//$current_post_type_slug	 = !empty( $current_post_type_slug ) ? '/' . $current_post_type_slug . '/' : $current_post_type_slug;
			//$menu_slug				 = ic_strtolower( trim( $item->url ) );
			//if ( strpos( $menu_slug, $current_post_type_slug ) !== false ) {
			$classes[] = 'current-menu-item';
			//}
		} else {
			if ( ($key = array_search( 'current-menu-item', $classes )) !== false ) {
				unset( $classes[ $key ] );
			}
			if ( ($key = array_search( 'current_page_parent', $classes )) !== false ) {
				unset( $classes[ $key ] );
			}
		}
	} else if ( isset( $post->ID ) && (is_ic_product_page() || is_ic_taxonomy_page()) ) {
		if ( strpos( $item->object, 'al_product-cat' ) === false && $item->object != 'custom' ) {
			if ( ($key = array_search( 'current-menu-item', $classes )) !== false ) {
				unset( $classes[ $key ] );
			}
			if ( ($key = array_search( 'current_page_parent', $classes )) !== false ) {
				unset( $classes[ $key ] );
			}
		}
	}
	return $classes;
}

add_filter( 'page_css_class', 'product_listing_page_nav_class', 10, 2 );

/**
 * Adds products post type navigation class for automatic main menu
 *
 * @global type $post
 * @param string $classes
 * @param type $page
 * @return string
 */
function product_listing_page_nav_class( $classes, $page ) {
	global $post;
	if ( isset( $post->ID ) && is_ic_product_listing() ) {
		if ( $page->ID == get_product_listing_id() ) {
			//$current_post_type		 = get_post_type_object( get_post_type( $post->ID ) );
			//$current_post_type_slug	 = $current_post_type->rewrite[ 'slug' ];
			//$menu_slug				 = $page->post_name;
			//if ( $menu_slug == $current_post_type_slug ) {
			$classes[] = 'current_page_item';
			//}
		} else {
			if ( ($key = array_search( 'current-menu-item', $classes )) !== false ) {
				unset( $classes[ $key ] );
			}
			if ( ($key = array_search( 'current_page_parent', $classes )) !== false ) {
				unset( $classes[ $key ] );
			}
		}
	} else if ( isset( $post->ID ) && (is_ic_product_page() || is_ic_taxonomy_page()) ) {
		if ( ($key = array_search( 'current-menu-item', $classes )) !== false ) {
			unset( $classes[ $key ] );
		}
		if ( ($key = array_search( 'current_page_parent', $classes )) !== false ) {
			unset( $classes[ $key ] );
		}
	}
	return $classes;
}

/**
 * Defines custom classes to product or category listing div
 * @return string
 */
function product_list_class( $archive_template, $where = 'product-list' ) {
	return apply_filters( 'product-list-class', '', $where, $archive_template );
}

/**
 * Defines custom classes to product or category element div
 * @return string
 */
function product_class( $product_id ) {
	$class = get_post_status( $product_id );
	return apply_filters( 'product-class', $class, $product_id );
}

/**
 * Defines custom classes to product or category element div
 * @return string
 */
function product_category_class( $category_id ) {
	return apply_filters( 'product-category-class', '', $category_id );
}

add_action( 'before_product_listing_category_list', 'product_list_categories_header' );

/**
 * Adds product main categories label on product listing
 *
 */
function product_list_categories_header() {
	$archive_names = get_archive_names();
	if ( !empty( $archive_names[ 'all_main_categories' ] ) && !isset( $shortcode_query ) ) {
		$title = do_shortcode( $archive_names[ 'all_main_categories' ] );
		if ( get_product_listing_title() != $title ) {
			echo '<h2 class="catalog-header">' . do_shortcode( $archive_names[ 'all_main_categories' ] ) . '</h2>';
		}
	}
}

add_action( 'before_category_subcategories', 'category_list_subcategories_header' );

/**
 * Adds product subcategories label on category product listing
 *
 */
function category_list_subcategories_header() {
	if ( is_ic_taxonomy_page() ) {
		$archive_names = get_archive_names();
		if ( !empty( $archive_names[ 'all_subcategories' ] ) && !is_ic_shortcode_query() ) {
			echo '<h2 class="catalog-header">' . do_shortcode( $archive_names[ 'all_subcategories' ] ) . '</h2>';
		}
	}
}

add_action( 'before_product_list', 'product_list_header', 9 );

/**
 * Adds product header on product listing
 *
 */
function product_list_header() {
	$archive_names = get_archive_names();
	if ( (!empty( $archive_names[ 'all_products' ] ) || !empty( $archive_names[ 'category_products' ] )) && !is_ic_shortcode_query() ) {
		if ( is_ic_product_listing() && !empty( $archive_names[ 'all_products' ] ) ) {
			$title = do_shortcode( $archive_names[ 'all_products' ] );
			if ( get_product_listing_title() != $title ) {
				echo '<h2 class="catalog-header">' . $title . '</h2>';
			}
		} else if ( is_ic_taxonomy_page() && !empty( $archive_names[ 'category_products' ] ) && is_ic_product_listing_showing_cats() ) {
			//$the_tax = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
			echo '<h2 class="catalog-header">' . do_shortcode( $archive_names[ 'category_products' ] ) . '</h2>';
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
	if ( !is_admin() && $query->is_main_query() && $query->is_tax( product_taxonomy_array() ) && is_ic_only_main_cats() ) {
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

add_action( 'advanced_mode_layout_start', 'advanced_mode_styling' );

/**
 * Adds advanced mode custom styling settings
 *
 */
function advanced_mode_styling() {
	$settings	 = get_integration_settings();
	$styling	 = '<style>';
	if ( $settings[ 'container_width' ] != 100 ) {
		$styling .= '#container.product-catalog {width: ' . $settings[ 'container_width' ] . '%; margin: 0 auto; overflow: hidden; box-sizing: border-box; float: none;}';
	}
	if ( $settings[ 'container_bg' ] != '' ) {
		$styling .= '#container.product-catalog {background: ' . $settings[ 'container_bg' ] . ';}';
	}
	if ( $settings[ 'container_padding' ] != 0 ) {
		$styling .= '.content-area.product-catalog #content {padding: ' . $settings[ 'container_padding' ] . 'px; box-sizing: border-box; float: none; }';
		if ( is_ic_default_theme_sided_sidebar_active() ) {
			$styling .= '.content-area.product-catalog #catalog_sidebar {padding: ' . $settings[ 'container_padding' ] . 'px; box-sizing: border-box;}';
		}
	}
	if ( $settings[ 'default_sidebar' ] == 'left' ) {
		$styling .= '.content-area.product-catalog #catalog_sidebar {float: left;}';
	}
	if ( is_ic_default_theme_sided_sidebar_active() ) {
		$styling .= '.content-area.product-catalog #content {width: 70%;';
		if ( $settings[ 'default_sidebar' ] == 'left' ) {
			$styling .= 'float:right;';
		} else if ( $settings[ 'default_sidebar' ] == 'right' ) {
			$styling .= 'float:left;';
		}
		$styling .= '}';
	}
	$styling .= apply_filters( 'advanced_mode_styling_rules', '' );
	$styling .= '</style>';
	if ( $styling != '<style></style>' ) {
		echo $styling;
	}
}

add_action( 'advanced_mode_layout_start', 'show_advanced_mode_default_sidebar' );

/**
 * Shows theme default catalog styled sidebar if necessary
 */
function show_advanced_mode_default_sidebar() {
	if ( is_ic_default_theme_sided_sidebar_active() || (is_ic_integration_wizard_page() && isset( $_GET[ 'test_advanced' ] ) && $_GET[ 'test_advanced' ] == 1) ) {
		add_action( 'advanced_mode_layout_after_content', 'advanced_mode_default_sided_sidebar' );
	} else if ( is_ic_default_theme_sidebar_active() ) {
		add_action( 'advanced_mode_layout_end', 'advanced_mode_default_sidebar' );
	}
}

/**
 * Shows theme default sidebar if necessary
 */
function advanced_mode_default_sidebar() {
	get_sidebar();
}

/**
 * Shows theme default sidebar if necessary
 */
function advanced_mode_default_sided_sidebar() {
	$sidebar_id			 = apply_filters( 'catalog_default_sidebar_id', 'catalog_sidebar' );
	$class				 = apply_filters( 'catalog_default_sidebar_class', 'catalog_sidebar' );
	echo '<div id="' . $sidebar_id . '" class="' . $class . '" role="complementary">';
	$registered_sidebars = $GLOBALS[ 'wp_registered_sidebars' ];
	unset( $registered_sidebars[ 'product_sort_bar' ] );
	$first_sidebar		 = key( $registered_sidebars );
	dynamic_sidebar( $first_sidebar );
	echo '</div>';
}

/**
 * Returns realted products
 *
 * @global object $post
 * @param int $products_limit
 * @param boolean $markup
 * @return string
 */
function get_related_products( $products_limit = null, $markup = false ) {
	if ( !isset( $products_limit ) ) {
		$products_limit = apply_filters( 'related_products_count', get_current_per_row() );
	}
	$current_product_id	 = ic_get_product_id();
	$taxonomy			 = get_current_screen_tax();
	$post_type			 = get_current_screen_post_type();
	$terms				 = get_the_terms( $current_product_id, $taxonomy );
	if ( is_array( $terms ) && !empty( $taxonomy ) && !empty( $post_type ) ) {
		$terms				 = array_reverse( $terms );
		$archive_template	 = get_product_listing_template();
		$i					 = 0;
		$inside				 = '';
		$products			 = array();
		foreach ( $terms as $term ) {
			$query_param = array(
				'post_type'		 => $post_type,
				'orderby'		 => 'rand',
				'tax_query'		 => array(
					array(
						'taxonomy'	 => $taxonomy,
						'field'		 => 'slug',
						'terms'		 => $term->slug,
					),
				),
				'posts_per_page' => $products_limit,
			);
			$query		 = new WP_Query( $query_param );
			while ( $query->have_posts() ) : $query->the_post();
				global $post;
				if ( $current_product_id != $post->ID ) {
					$i++;
					$products[] = $post->ID;
				}
				if ( $i >= $products_limit ) {
					break;
				}
			endwhile;
			wp_reset_postdata();
			reset_row_class();
			if ( $i >= $products_limit ) {
				break;
			}
		}
		$div = '';
		if ( !empty( $products ) ) {
			$products = implode( ',', $products );
			ic_save_global( 'current_related_products', $products );
			if ( $markup ) {
				ob_start();
				ic_show_template_file( 'product-page/related-products.php' );
				$div = ob_get_clean();
			} else {
				$div = do_shortcode( '[show_products post_type="' . $post_type . '" product="' . $products . '"]' );
			}
		}
		return $div;
	}
	return;
}

add_action( 'product_category_page_start', 'ic_add_product_category_image' );

/**
 * Shows product category image
 *
 * @param type $term_id
 */
function ic_add_product_category_image( $term_id ) {
	if ( is_ic_category_image_enabled() ) {
		ic_save_global( 'current_product_category_id', $term_id );
		ic_show_template_file( 'product-listing/category-image.php' );
	}
}

add_action( 'product_category_page_start', 'ic_add_product_category_description' );

/**
 * Shows product category description
 *
 */
function ic_add_product_category_description() {
	ic_show_template_file( 'product-listing/category-description.php' );
}

add_action( 'product_listing_entry_inside', 'ic_product_listing_categories', 10, 2 );

/**
 * Generates product listing categories
 *
 * @param type $archive_template
 * @param type $multiple_settings
 */
function ic_product_listing_categories( $archive_template, $multiple_settings ) {
	$taxonomy_name = apply_filters( 'current_product_catalog_taxonomy', 'al_product-cat' );
	if ( !is_tax() && !is_search() ) {
		$before_archive = content_product_adder_archive_before();
		if ( $before_archive != '<div class="entry-summary"></div>' ) {
			echo $before_archive;
		}
		if ( $multiple_settings[ 'product_listing_cats' ] == 'on' || $multiple_settings[ 'product_listing_cats' ] == 'cats_only' ) {
			if ( $multiple_settings[ 'cat_template' ] != 'template' ) {
				$product_subcategories = wp_list_categories( 'show_option_none=No_cat&echo=0&title_li=&taxonomy=' . $taxonomy_name . '&parent=0' );
				if ( !strpos( $product_subcategories, 'No_cat' ) ) {
					do_action( 'before_product_listing_category_list' );
					ic_save_global( 'current_product_categories', $product_subcategories );
					ic_save_global( 'current_product_archive_template', get_product_listing_template() );
					ic_show_template_file( 'product-listing/categories-listing.php' );
				}
			} else {
				$show_categories = do_shortcode( '[show_categories parent="0" shortcode_query="no"]' );
				if ( !empty( $show_categories ) ) {
					do_action( 'before_product_listing_category_list' );
					echo $show_categories;
					if ( $archive_template != 'list' && !is_ic_only_main_cats() ) {
						echo '<hr>';
					}
				}
			}
		}
	} else if ( is_tax() ) {
		$term = get_queried_object()->term_id;
		do_action( 'product_category_page_start', $term );
		if ( $multiple_settings[ 'category_top_cats' ] == 'on' || $multiple_settings[ 'category_top_cats' ] == 'only_subcategories' ) {
			if ( $multiple_settings[ 'cat_template' ] != 'template' ) {
				$product_subcategories = wp_list_categories( 'show_option_none=No_cat&echo=0&title_li=&taxonomy=' . $taxonomy_name . '&child_of=' . $term );
				if ( !strpos( $product_subcategories, 'No_cat' ) ) {
					do_action( 'before_category_subcategories' );
					ic_save_global( 'current_product_categories', $product_subcategories );
					ic_save_global( 'current_product_archive_template', get_product_listing_template() );
					ic_show_template_file( 'product-listing/categories-listing.php' );
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
}

add_action( 'product_listing_entry_inside', 'ic_product_listing_products', 20, 2 );

/**
 * Generates product listing products
 *
 * @param type $archive_template
 * @param type $multiple_settings
 */
function ic_product_listing_products( $archive_template, $multiple_settings ) {
	global $post, $ic_is_home;
	if ( is_home_archive() || (!more_products() && is_custom_product_listing_page()) ) {
		if ( get_query_var( 'paged' ) ) {
			$paged = get_query_var( 'paged' );
		} elseif ( get_query_var( 'page' ) ) {
			$paged = get_query_var( 'page' );
		} else {
			$paged = 1;
		}
		$args		 = apply_filters( 'home_product_listing_query', array( 'post_type' => 'al_product', 'posts_per_page' => $multiple_settings[ 'archive_products_limit' ], 'paged' => $paged ) );
		query_posts( $args );
		$ic_is_home	 = 1;
	}
	if ( (is_tax() || is_search() || !is_ic_only_main_cats() || is_product_filters_active()) && more_products() ) {
		do_action( 'before_product_list', $archive_template, $multiple_settings );
		$product_list = '';
		while ( have_posts() ) : the_post();
			$product_list .= get_catalog_template( $archive_template, $post );
		endwhile;
		$product_list = apply_filters( 'product_list_ready', $product_list, $archive_template, 'auto_listing' );
		echo '<div class="product-list responsive ' . $archive_template . ' ' . product_list_class( $archive_template ) . '">' . $product_list . '</div><span class="clear"></span>';
	} else if ( (!is_product_filters_active() && is_search()) && !more_products() ) {
		echo '<p>' . __( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'ecommerce-product-catalog' ) . '</p>';
		product_search_form();
	} else if ( is_product_filters_active() && !more_products() ) {
		show_product_sort_bar();
		echo '<p>' . sprintf( __( 'Sorry, but nothing matched your search terms. Please try again with some different options or %sreset filters%s.', 'ecommerce-product-catalog' ), '<a href="' . get_filters_bar_reset_url() . '">', '</a>' ) . '</p>';
	}
}

add_action( 'product_listing_end', 'ic_reset_home_listing_query', 99 );

/**
 * Resets the query for home product listing
 *
 * @global type $ic_is_home
 */
function ic_reset_home_listing_query() {
	global $ic_is_home;
	if ( isset( $ic_is_home ) && $ic_is_home == 1 ) {
		wp_reset_query();
	}
}

add_filter( 'body_class', 'ic_catalog_page_body_class' );

function ic_catalog_page_body_class( $classes ) {
	if ( is_ic_catalog_page() ) {
		$classes[] = 'type-page';
	}
	return $classes;
}

/**
 * Manages template files paths
 *
 * @param type $file_path
 * @return type
 */
function ic_get_template_file( $file_path, $base_path = AL_BASE_PATH ) {
	$folder		 = get_custom_templates_folder();
	$file_name	 = basename( $file_path );
	if ( file_exists( $folder . $file_name ) ) {
		return $folder . $file_name;
	} else if ( file_exists( $base_path . '/templates/template-parts/' . $file_path ) ) {
		return $base_path . '/templates/template-parts/' . $file_path;
	} else {
		return false;
	}
}

/**
 * Includes template file
 *
 * @param type $file_path
 * @return type
 */
function ic_show_template_file( $file_path, $base_path = AL_BASE_PATH ) {
	$path = ic_get_template_file( $file_path, $base_path );
	if ( $path ) {
		include $path;
	}
	return;
}

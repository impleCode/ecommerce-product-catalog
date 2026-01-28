<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * WP Product template functions
 *
 * Here all plugin template functions are defined.
 *
 * @version        1.1.3
 * @package        ecommerce-product-catalog/
 * @author        impleCode
 */
add_shortcode( 'content_product_adder', 'content_product_adder' );

/**
 * The function wrapper to show product catalog content for current URL
 *
 */
function content_product_adder( $is_catalog = null ) {
    if ( $is_catalog !== 'is_catalog' && ! is_ic_catalog_page() ) {
        return;
    }
    echo '<div class="ic-catalog-container">';
    if ( is_archive() || is_search() || is_home_archive() || is_ic_product_listing() ) {
        do_action( 'before_product_archive' );
        content_product_adder_archive();
        do_action( 'after_product_archive' );
    } else {
        do_action( 'before_product_page' );
        content_product_adder_single();
        do_action( 'after_product_page' );
    }
    echo '</div>';
}

function ic_redirect_to_same() {
    $url = 'http';
    if ( is_ssl() ) {
        $url .= 's';
    }
    $url .= '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    wp_redirect( esc_url_raw( $url ) );
    exit;
}

function content_product_adder_archive() {
    $path = get_custom_product_listing_path();
    if ( file_exists( $path ) ) {
        ob_start();
        include apply_filters( 'content_product_adder_archive_path', $path );
        $product_listing = ob_get_clean();
        echo do_shortcode( $product_listing );
    } else {
        include apply_filters( 'content_product_adder_archive_path', AL_BASE_TEMPLATES_PATH . '/templates/full/product-listing.php' );
    }
}

function content_product_adder_single() {
    add_action( 'product_page_inside', 'content_product_adder_single_content' );
    $path = get_custom_product_page_path();
    if ( file_exists( $path ) ) {
        ob_start();
        include apply_filters( 'content_product_adder_path', $path );
        $product_page = ob_get_clean();
        echo do_shortcode( $product_page );
    } else {
        include apply_filters( 'content_product_adder_path', AL_BASE_TEMPLATES_PATH . '/templates/full/product-page.php' );
    }
}

function content_product_adder_single_content() {
    $path = get_custom_product_page_inside_path();
    if ( file_exists( $path ) ) {
        ob_start();
        include $path;
        $product_page = ob_get_clean();
        echo do_shortcode( $product_page );
    } else {
        include AL_BASE_TEMPLATES_PATH . '/templates/full/product-page-inside.php';
    }
}

function content_product_adder_archive_before() {
    $page_id = apply_filters( 'before_archive_post_id', get_product_listing_id() );
    $page    = empty( $page_id ) ? '' : get_post( $page_id );
    if ( $page != '' && ! is_ic_shortcode_integration() ) {
        if ( get_integration_type() != 'simple' ) {
            if ( ic_has_page_catalog_shortcode( $page ) ) {
                $page->post_content = str_replace( array(
                        '<!-- wp:ic-epc/show-catalog /-->',
                        '[show_product_catalog]'
                ), '', $page->post_content );
            }
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
    $def_page_id   = get_product_listing_id();
    $archive_names = get_archive_names();
    $page_id       = apply_filters( 'before_archive_post_id', $def_page_id );
    $page          = empty( $page_id ) ? '' : get_post( $page_id );
    if ( $page == '' ) {
        echo '<h1 class="entry-title">' . $archive_names['all_products'] . '</h1>';
    } else {
        echo '<h1 class="entry-title">' . $page->post_title . '</h1>';
    }
}

function show_products_outside_loop( $atts, $content = '' ) {
    global $shortcode_query, $product_sort, $archive_template, $shortcode_args;
    ic_reset_listing_globals();
    ic_enqueue_main_catalog_js_css();
    ic_save_global( 'in_shortcode', 1, true );
    $available_args   = apply_filters( 'show_products_shortcode_args', array(
            'post_type'        => 'al_product',
            'category'         => '',
            'product'          => '',
            'exclude'          => '',
            'products_limit'   => 100,
            'archive_template' => get_product_listing_template(),
            'design_scheme'    => '',
            'sort'             => 0,
            'orderby'          => '',
            'order'            => '',
            'pagination'       => 0,
            'page'             => '',
            'per_row'          => '',
            'empty'            => '',
    ) );
    $args             = shortcode_atts( $available_args, $atts );
    $shortcode_args   = $args;
    $category         = esc_html( $args['category'] );
    $product          = esc_html( $args['product'] );
    $exclude          = esc_html( $args['exclude'] );
    $products_limit   = intval( $args['products_limit'] );
    $archive_template = esc_attr( $args['archive_template'] );
    $design_scheme    = esc_attr( $args['design_scheme'] );
    $product_sort     = intval( $args['sort'] );
    $per_row          = intval( $args['per_row'] );
    $args['page']     = intval( $args['page'] );
    if ( ! empty( $per_row ) ) {
        ic_save_global( 'shortcode_per_row', $per_row, true );
    }
    $post_type = empty( $args['post_type'] ) ? 'al_product' : $args['post_type'];
    if ( ! empty( $product ) ) {
        $product_array = explode( ',', $product );
        $query_param   = array(
                'post_type'      => product_post_type_array(),
                'post__in'       => $product_array,
                'posts_per_page' => $products_limit,
        );
    } else if ( ! empty( $category ) ) {
        $category_array = explode( ',', $category );
        $field          = 'name';
        if ( is_numeric( $category_array[0] ) ) {
            $field = 'term_id';
        }
        $query_param = array(
                'post_type'      => $post_type,
                'tax_query'      => array(
                        array(
                                'taxonomy' => 'al_product-cat',
                                'field'    => $field,
                                'terms'    => $category_array,
                        ),
                ),
                'posts_per_page' => $products_limit,
        );
    } else {
        $query_param = array(
                'post_type'      => $post_type,
                'posts_per_page' => $products_limit,
        );
        if ( ! empty( $exclude ) ) {
            $query_param['post__not_in'] = explode( ',', $exclude );
        }
    }
    if ( $args['orderby'] == 'none' ) {
        $args['orderby'] = 'post__in';
    }
    if ( ! empty( $args['orderby'] ) ) {
        $query_param['orderby'] = esc_attr( $args['orderby'] );
    }
    if ( ! empty( $args['order'] ) ) {
        $query_param['order'] = esc_attr( $args['order'] );
    }
    if ( ! empty( $args['pagination'] ) ) {

        if ( get_query_var( 'paged' ) ) {
            $paged = absint( get_query_var( 'paged' ) );
        } elseif ( get_query_var( 'page' ) ) {
            $paged = absint( get_query_var( 'page' ) );
        } else if ( ! empty( $args['page'] ) ) {
            $paged = $args['page'];
        } else {
            $paged = 1;
        }
        $query_param['paged'] = $paged;
    }
    $query_param = apply_filters( 'shortcode_query', $query_param, $args, $post_type, $products_limit );
    remove_all_filters( 'pre_get_posts' );
    $shortcode_query                            = new WP_Query( $query_param );
    $shortcode_query->query['archive_template'] = $archive_template;
    $i                                          = 0;
    ob_start();
    do_action( 'before_product_list', $archive_template );
    do_action( 'before_shortcode_product_list', $shortcode_query, $args, $archive_template );
    echo do_shortcode( $content );
    $before = ob_get_contents();
    ob_end_clean();
    $products_listed = false;
    $inside          = apply_filters( 'pre_show_products_outside_loop_inside', '', $shortcode_query );
    if ( empty( $inside ) ) {
        while ( $shortcode_query->have_posts() ) : $shortcode_query->the_post();
            global $post;
            ic_set_product_id( $post->ID );
            $i ++;
            $inside .= get_catalog_template( $archive_template, $post, $i, $design_scheme );
            ic_reset_product_id();
        endwhile;
    }
    if ( ! empty( $inside ) ) {
        $products_listed = true;
    }
    $pagination = '';
    if ( ! empty( $args['pagination'] ) ) {
        ob_start();
        product_archive_pagination( $shortcode_query );
        $pagination = ob_get_clean();
    }
    if ( ! empty( $args['empty'] ) && ! $products_listed ) {
        $inside .= wp_kses_post( $args['empty'] );
    }
    $inside = apply_filters( 'product_list_ready', $inside, $archive_template, $args );
    wp_reset_postdata();
    if ( ! empty( $inside ) ) {
        $out_class = apply_filters( 'ic_show_products_container_class', 'product-list responsive ' . $archive_template . ' ' . product_list_class( $archive_template ), $args );
        $out       = $before . '<div class="' . $out_class . '" ' . product_list_attr( $shortcode_query ) . '>' . $inside . '<div style="clear:both"></div></div>' . $pagination;
    } else {
        $out = '';
    }
    unset( $GLOBALS['shortcode_args'] );
    unset( $GLOBALS['shortcode_query'] );
    unset( $GLOBALS['archive_template'] );
    unset( $GLOBALS['product_sort'] );
    ic_delete_global( 'in_shortcode' );
    //ic_delete_global( 'shortcode_per_row' );
    reset_row_class();

    return $out;
}

add_shortcode( 'show_products', 'show_products_outside_loop' );

function single_scripts() {
    if ( is_ic_product_page() && is_lightbox_enabled() ) {
        wp_enqueue_style( 'colorbox' );
    }
}

add_action( 'wp_enqueue_scripts', 'single_scripts' );
add_action( 'ic_pre_get_products', 'set_products_limit', 99 );

/**
 * Sets product limit on product listing pages
 *
 * @param object $query
 */
function set_products_limit( $query ) {
    $archive_multiple_settings = get_multiple_settings();
    //if ( !is_admin() && $query->is_main_query() && (is_ic_product_listing( $query ) || is_ic_taxonomy_page() || is_home_archive( $query ) || is_ic_product_search()) ) {
    $current_per_page = $query->get( 'posts_per_page' );
    if ( $current_per_page !== - 1 && ! isset( $query->query['post__in'] ) && empty( $query->query['suppress_filters'] ) ) {
        $query->set( 'posts_per_page', $archive_multiple_settings['archive_products_limit'] );
    }
    if ( ic_ic_catalog_archive( $query ) ) {
        $query->set( 'post_status', ic_visible_product_status() );
    }


    //do_action( 'pre_get_al_products', $query );
    //}
}

add_action( 'parse_tax_query', 'set_category_products_limit', 99 );

function set_category_products_limit( $query ) {
    if ( ! is_admin() && $query->is_main_query() && is_ic_taxonomy_page( $query ) ) {
        $archive_multiple_settings = get_multiple_settings();
        $current_per_page          = $query->get( 'posts_per_page' );
        if ( $current_per_page !== - 1 && ! isset( $query->query['post__in'] ) && empty( $query->query['suppress_filters'] ) ) {
            $query->set( 'posts_per_page', $archive_multiple_settings['archive_products_limit'] );
        }
    }
}


add_action( 'product_listing_end', 'product_archive_pagination' );

/**
 * Adds paginaion to the product listings
 *
 * @return string
 * @global object $wp_query
 */
function product_archive_pagination( $wp_query = null ) {
    /*
      if ( is_ic_only_main_cats() && !is_product_filters_active() && !is_ic_ajax() ) {
      return;
      }
     *
     */
    if ( ! isset( $wp_query ) || ! is_object( $wp_query ) ) {
        global $paged, $wp_query;
    }
    if ( $wp_query->max_num_pages <= 1 ) {
        return;
    }
    $multiple_settings = get_multiple_settings();
    if ( ! is_ic_ajax() && ( is_ic_product_listing( $wp_query ) || ! is_ic_catalog_page( $wp_query ) ) && $multiple_settings['product_listing_cats'] == 'forced_cats_only' ) {
        return;
    }
    if ( $wp_query->get( 'paged' ) ) {
        $paged = absint( $wp_query->get( 'paged' ) );
    } elseif ( $wp_query->get( 'page' ) ) {
        $paged = absint( $wp_query->get( 'page' ) );
    } else {
        $paged = 1;
    }
    $max = intval( $wp_query->max_num_pages );
    if ( $paged >= 1 ) {
        $links[] = $paged;
    }
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
    if ( get_previous_posts_link( $names['previous_products'] ) ) {
        $previous = $paged - 1;
        printf( '<li class="previous-page" data-page="' . $previous . '">%s</li> ' . "\n", get_previous_posts_link( '<span>' . $names['previous_products'] . '</span>' ) );
    }
    $pre_page_buttons = apply_filters( 'ic_product_archive_nav_buttons', '', $links, $max, $paged );
    if ( empty( $pre_page_buttons ) ) {
        if ( ! in_array( 1, $links ) ) {
            $class = 1 == $paged ? ' class="active first-num"' : ' class="first-num"';
            printf( '<li%s data-page="1"><a href="%s">%s</a></li> ' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );
            if ( ! in_array( 2, $links ) ) {
                echo '<li class="nav-dots">...</li>';
            }
        }
        sort( $links );
        foreach ( (array) $links as $link ) {
            $class = $paged == $link ? ' class="active"' : '';
            printf( '<li%s data-page="' . $link . '"><a href="%s">%s</a></li> ' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
        }
        if ( ! in_array( $max, $links ) ) {
            if ( ! in_array( $max - 1, $links ) ) {
                echo '<li class="nav-dots">...</li>' . "\n";
            }
            $class = $paged == $max ? ' class="active last-num"' : ' class="last-num"';
            printf( '<li%s data-page="' . $max . '"><a href="%s">%s</a></li> ' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
        }
    } else {
        echo $pre_page_buttons;
    }
    if ( get_next_posts_link( $names['next_products'], $max ) ) {
        $next = $paged + 1;
        printf( '<li class="next-page" data-page="' . $next . '">%s</li> ' . "\n", get_next_posts_link( '<span>' . $names['next_products'] . '</span>', $max ) );
    }
    echo '</ul></div>' . "\n";
    wp_reset_postdata();
}

function get_catalog_template( $archive_template, $post, $i = null, $design_scheme = null ) {
    $themes_array                      = apply_filters( 'ecommerce_catalog_templates', array(
            'default' => get_default_archive_theme( $post, $archive_template ),
            'list'    => get_list_archive_theme( $post, $archive_template ),
            'grid'    => get_grid_archive_theme( $post, $archive_template ),
    ), $post, $i, $design_scheme, $archive_template );
    $themes_array[ $archive_template ] = isset( $themes_array[ $archive_template ] ) ? $themes_array[ $archive_template ] : $themes_array['default'];
    $themes_array[ $archive_template ] = empty( $themes_array[ $archive_template ] ) ? get_default_archive_theme( $post, 'default' ) : $themes_array[ $archive_template ];

    return $themes_array[ $archive_template ];
}

function get_product_category_template( $archive_template, $product_cat, $i = null, $design_scheme = null ) {
    $themes_array                      = apply_filters( 'ecommerce_category_templates', array(
            'default' => get_default_category_theme( $product_cat, $archive_template ),
            'list'    => get_list_category_theme( $product_cat, $archive_template ),
            'grid'    => get_grid_category_theme( $product_cat, $archive_template ),
    ), $product_cat, $i, $design_scheme, $archive_template );
    $themes_array[ $archive_template ] = isset( $themes_array[ $archive_template ] ) ? $themes_array[ $archive_template ] : $themes_array['default'];
    if ( empty( $themes_array[ $archive_template ] ) ) {
        if ( empty( $themes_array['default'] ) ) {
            $themes_array[ $archive_template ] = get_default_category_theme( $product_cat, 'default' );
        } else {
            $themes_array[ $archive_template ] = $themes_array['default'];
        }
    }

    return $themes_array[ $archive_template ];
}

function ic_get_available_templates() {
    $templates = array(
            'default' => __( 'Modern Grid', 'ecommerce-product-catalog' ),
            'list'    => __( 'Classic List', 'ecommerce-product-catalog' ),
            'grid'    => __( 'Classic Grid', 'ecommerce-product-catalog' )
    );

    return apply_filters( 'ic_epc_available_templates', $templates );
}

function more_products() {
    global $wp_query, $shortcode_query;
    $post_type = apply_filters( 'current_product_post_type', 'al_product' );
    $taxonomy  = apply_filters( 'current_product_catalog_taxonomy', 'al_product-cat' );
    if ( empty( $wp_query->is_single ) && ( empty( $wp_query->is_page ) || ic_get_global( 'inside_show_catalog_shortcode' ) ) && ( ( isset( $wp_query->query['post_type'] ) && $wp_query->query['post_type'] == $post_type ) || ( isset( $wp_query->query_vars['post_type'] ) && is_array( $wp_query->query_vars['post_type'] ) && array_search( $post_type, $wp_query->query_vars['post_type'] ) !== false ) || ( is_string( $taxonomy ) && isset( $wp_query->query[ $taxonomy ] ) ) ) ) {
        $y_query = $wp_query;
    } else {
        $y_query = $shortcode_query;
    }
    if ( empty( $y_query ) ) {
        $y_query = $wp_query;
    }
    if ( ! empty( $y_query->posts ) && is_array( $y_query->posts ) && apply_filters( 'ic_query_check_if_product', true, $y_query ) ) {
        if ( ! empty( $y_query->posts[0]->ID ) && ! is_ic_product( $y_query->posts[0]->ID ) ) {

            return false;
        }
    }
    if ( isset( $y_query->current_post ) ) {
        return $y_query->current_post + 1 < $y_query->post_count;
    } else {

        return false;
    }
}

function more_product_cats() {
    global $cat_shortcode_query;
    if ( isset( $cat_shortcode_query['current'] ) ) {

        return $cat_shortcode_query['current'] + 1 < $cat_shortcode_query['count'];
    } else {
        return false;
    }
}

function get_row_class( $grid_settings, $what = 'products' ) {
    $row_class = 'full';
    if ( is_ic_shortcode_query() ) {
        $shortcode_per_row = ic_get_global( 'shortcode_per_row' );
        if ( $shortcode_per_row ) {
            $grid_settings['entries']            = $shortcode_per_row;
            $grid_settings['per-row-categories'] = $shortcode_per_row;
        }
    }
    if ( $what === 'products' ) {
        $per_row = $grid_settings['entries'];
    } else {
        $per_row = $grid_settings['per-row-categories'];
    }
    if ( $per_row != '' ) {
        global $ic_row;
        if ( $ic_row > $per_row || ! isset( $ic_row ) ) {
            $ic_row = 1;
        }

        $count = $ic_row - $per_row;
        if ( $ic_row == 1 ) {
            $row_class = 'first';
        } else if ( $count == 0 ) {
            $row_class = 'last';
        } else {
            $row_class = 'middle';
        }
        if ( more_products() || more_product_cats() ) {
            $ic_row ++;
        } else {
            $ic_row = 1;
        }
    }

    return $row_class;
}

add_action( 'product_listing_end', 'reset_row_class', 99 );

function reset_row_class() {
    global $ic_row;
    $ic_row = 1;
    ic_delete_global( 'shortcode_per_row' );
}

add_filter( 'post_class', 'product_post_class', - 1 );

/**
 * Deletes default WordPress has-post-thumbnail class
 *
 * @param array $classes
 *
 * @return array
 */
function product_post_class( $classes ) {
    if ( is_ic_catalog_page() ) {
        if ( $key = array_search( 'has-post-thumbnail', $classes ) !== false ) {
            unset( $classes[ $key ] );
        }
        $classes[] = 'al_product';
        $classes[] = 'responsive';
        if ( is_ic_product_page() ) {
            $single_options = get_product_page_settings();
            $classes[]      = $single_options['template'];
            $classes[]      = 'ic-template-' . $single_options['template'];
            $product_id     = ic_get_product_id();
            if ( ! empty( $product_id ) ) {
                $classes[] = 'product-' . $product_id;
            }
            $classes = apply_filters( 'product-page-class', $classes, $product_id );
        }
    }

    return $classes;
}

add_action( 'before_product_list', 'product_listing_additional_styles' );
add_action( 'before_ajax_product_list', 'product_listing_additional_styles' );
add_action( 'before_category_list', 'product_listing_additional_styles' );
add_action( 'product_listing_entry_inside', 'product_listing_additional_styles' );
/**
 * Ads product listing inline styles container
 */
function product_listing_additional_styles( $archive_template ) {
    if ( current_filter() === 'product_listing_entry_inside' ) {
        remove_action( 'before_product_list', 'product_listing_additional_styles' );
    } else if ( current_filter() === 'before_product_list' ) {
        remove_action( 'product_listing_entry_inside', 'product_listing_additional_styles' );
    }

    $styles = esc_html( wp_strip_all_tags( apply_filters( 'product_listing_additional_styles', '', $archive_template ) ) );
    if ( ! empty( $styles ) && ! is_ic_admin() ) {
        echo '<style>' . $styles . '</style>';
    }
}

//add_action( 'before_product_entry', 'product_page_additional_styles' );
add_action( 'before_product_page', 'product_page_additional_styles' );

/**
 * Ads product page inline styles container
 */
function product_page_additional_styles() {
    $styles = esc_html( wp_strip_all_tags( apply_filters( 'product_page_additional_styles', '' ) ) );
    if ( ! empty( $styles ) && ! is_ic_admin() ) {
        echo '<style>' . $styles . '</style>';
    }
}

/**
 * Returns product listing template defined in settings
 *
 * @return string
 */
function get_product_listing_template() {
    global $shortcode_query;
    $default = 'default';
    if ( ! empty( $shortcode_query ) ) {
        global $archive_template;
        $archive_template = isset( $archive_template ) ? $archive_template : get_option( 'archive_template', $default );
    } else {
        $archive_template = get_option( 'archive_template', $default );
    }
    $archive_template = ! empty( $archive_template ) ? $archive_template : $default;

    return apply_filters( 'product_listing_template', $archive_template );
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
//add_filter( 'the_title', 'override_product_page_title', 11, 2 );

/**
 * Replaces auto products listing, product category pages and product search title with appropriate entries
 *
 * @param string $page_title
 * @param int $id
 *
 * @return string
 */
function override_product_page_title( $page_title, $id = null ) {
    $listing_id = get_product_listing_id();
    if ( ! is_admin() && is_ic_catalog_page() && ! is_ic_product_page() && ! in_the_ic_loop() && ! is_filter_bar() && ( empty( $id ) || ( get_quasi_post_type( get_post_type( $id ) ) == 'al_product' ) ) || $listing_id == $id ) {
        $query = null;
        if ( $listing_id === 'noid' ) {
            return $page_title;
        }
        if ( is_ic_shortcode_integration() ) {
            global $wp_query;
            $this_query = ic_get_global( 'pre_shortcode_query' );
            if ( $this_query ) {
                $wp_query = $this_query;
            }
        } else {
            return $page_title;
        }
        if ( is_ic_taxonomy_page( $query ) && ( empty( $id ) || ! is_ic_product( $id ) ) && $listing_id != $id ) {
            $page_title = get_product_tax_title( $page_title );
        } else if ( is_ic_product_search( $query ) ) {
            $page_title = ic_get_search_page_title();
        } else if ( is_ic_product_listing( $query ) && ( empty( $id ) || $id == $listing_id ) ) {
            $page_title = get_product_listing_title();
        }
    }

    return $page_title;
}

add_action( 'ic_catalog_wp', 'ic_catalog_set_post_title' );

function ic_catalog_set_post_title() {
    if ( is_ic_simple_mode() || is_ic_theme_mode() ) {
        return;
    }
    if ( is_ic_product_listing() ) {
        $page_title = get_product_listing_title();
    } else if ( is_ic_taxonomy_page() ) {
        $page_title = get_product_tax_title();
    }
    if ( ! empty( $page_title ) ) {
        global $post;
        if ( ! empty( $post->ID ) ) {
            ic_save_global( 'pre_title_post_' . $post->ID, $post->post_title );
            $post->post_title = $page_title;
            add_action( 'before_product_list', 'ic_catalog_reset_post_title' );
        }
    }
}

function ic_catalog_reset_post_title() {
    if ( is_ic_in_shortcode() ) {
        return;
    }
    global $post;
    the_post();
    if ( ! empty( $post->ID ) ) {
        $pre_title = ic_get_global( 'pre_title_post_' . $post->ID );
        if ( ! empty( $pre_title ) ) {
            $post->post_title = $pre_title;
            ic_delete_global( 'pre_title_post_' . $post->ID );
            remove_action( 'before_product_list', 'ic_catalog_reset_post_title' );
        }
    }
    rewind_posts();
}

function ic_get_search_page_title() {
    if ( ! empty( $_GET['s'] ) ) {
        $search = $_GET['s'];
    } else {
        $search = get_search_query( false );
        if ( empty( $search ) ) {
            $query = ic_get_catalog_query();
            if ( ! empty( $query->query['s'] ) ) {
                $search = $query->query['s'];
            }
        }

    }

    return __( 'Search Results for:', 'ecommerce-product-catalog' ) . ' <span class="ic-search-keyword">' . wp_unslash( esc_html( strval( $search ) ) ) . '</span>';
}

function get_product_listing_title() {
    $archive_names = get_archive_names();
    $def_page_id   = get_product_listing_id();
    $page_id       = apply_filters( 'before_archive_post_id', $def_page_id );

    $page = empty( $page_id ) ? '' : get_post( $page_id );
    if ( $page == '' ) {
        $archive_multiple_settings = get_multiple_settings();
        if ( $archive_multiple_settings['product_listing_cats'] == 'off' ) {
            $page_title = $archive_names['all_products'];
        } else {
            $page_title = $archive_names['all_main_categories'];
        }
    } else {
        //remove_filter( 'the_title', 'override_product_page_title', 11, 2 );
        $page_title = apply_filters( 'the_title', $page->post_title, $page_id );
        //add_filter( 'the_title', 'override_product_page_title', 11, 2 );
    }

    return apply_filters( 'ic_product_listing_title', $page_title, $page );
}

function get_product_tax_title( $page_title = null ) {
    $archive_names = get_archive_names();
    $the_tax       = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
    if ( isset( $the_tax->name ) ) {
        $name = $the_tax->name;
    } else {
        $name = $page_title;
    }
    if ( ! empty( $archive_names['all_prefix'] ) && has_shortcode( $archive_names['all_prefix'], 'product_category_name' ) ) {
        $page_title = do_shortcode( $archive_names['all_prefix'] );
    } else if ( ! empty( $archive_names['all_prefix'] ) && ! empty( $name ) ) {
        $page_title = do_shortcode( $archive_names['all_prefix'] ) . ' ' . $name;
    } else {
        $page_title = $name;
    }

    return $page_title;
}

add_filter( 'nav_menu_css_class', 'product_listing_current_nav_class', 10, 2 );

/**
 * Adds product post type navigation menu current class
 *
 * @param string $classes
 * @param type $item
 *
 * @return string
 * @global type $post
 */
function product_listing_current_nav_class( $classes, $item ) {
    global $post;
    if ( isset( $post->ID ) && is_ic_product_listing() ) {
        if ( isset( $item->object_id ) && $item->object_id == get_product_listing_id() ) {

            //$current_post_type		 = get_post_type_object( get_post_type( $post->ID ) );
            //$current_post_type_slug	 = $current_post_type->rewrite[ 'slug' ];
            //$current_post_type_slug	 = !empty( $current_post_type_slug ) ? '/' . $current_post_type_slug . '/' : $current_post_type_slug;
            //$menu_slug				 = ic_strtolower( trim( $item->url ) );
            //if ( strpos( $menu_slug, $current_post_type_slug ) !== false ) {
            $classes[] = 'current-menu-item';
            $classes[] = 'current_page_item';
            //}
        } else {
            if ( ( $key = array_search( 'current-menu-item', $classes ) ) !== false ) {
                unset( $classes[ $key ] );
            }
            if ( ( $key = array_search( 'current_page_parent', $classes ) ) !== false ) {
                unset( $classes[ $key ] );
            }
        }
    } else if ( isset( $post->ID ) && ( is_ic_product_page() || is_ic_taxonomy_page() ) ) {
        if ( isset( $item->object ) && strpos( $item->object, 'al_product-cat' ) === false && $item->object != 'custom' ) {
            if ( ( $key = array_search( 'current-menu-item', $classes ) ) !== false ) {
                unset( $classes[ $key ] );
            }
            if ( ( $key = array_search( 'current_page_parent', $classes ) ) !== false ) {
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
 * @param string $classes
 * @param type $page
 *
 * @return string
 * @global type $post
 */
function product_listing_page_nav_class( $classes, $page ) {
    global $post;
    if ( isset( $post->ID ) && is_ic_product_listing() ) {
        if ( $page->ID == get_product_listing_id() ) {
            //$current_post_type		 = get_post_type_object( get_post_type( $post->ID ) );
            //$current_post_type_slug	 = $current_post_type->rewrite[ 'slug' ];
            //$menu_slug				 = $page->post_name;
            //if ( $menu_slug == $current_post_type_slug ) {
            $classes[] = 'current-menu-item';
            $classes[] = 'current_page_item';
            //}
        } else {
            if ( ( $key = array_search( 'current-menu-item', $classes ) ) !== false ) {
                unset( $classes[ $key ] );
            }
            if ( ( $key = array_search( 'current_page_parent', $classes ) ) !== false ) {
                unset( $classes[ $key ] );
            }
        }
    } else if ( isset( $post->ID ) && ( is_ic_product_page() || is_ic_taxonomy_page() ) ) {
        if ( ( $key = array_search( 'current-menu-item', $classes ) ) !== false ) {
            unset( $classes[ $key ] );
        }
        if ( ( $key = array_search( 'current_page_parent', $classes ) ) !== false ) {
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

function product_list_attr( $query = null ) {
    return apply_filters( 'product-list-attr', '', $query );
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
    if ( ! empty( $archive_names['all_main_categories'] ) && ! isset( $shortcode_query ) ) {
        $title = do_shortcode( $archive_names['all_main_categories'] );
        if ( get_product_listing_title() != $title ) {
            echo '<h2 class="catalog-header">' . do_shortcode( $archive_names['all_main_categories'] ) . '</h2>';
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
        if ( ! empty( $archive_names['all_subcategories'] ) && ! is_ic_shortcode_query() ) {
            echo '<h2 class="catalog-header">' . do_shortcode( $archive_names['all_subcategories'] ) . '</h2>';
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
    if ( ( ! empty( $archive_names['all_products'] ) || ! empty( $archive_names['category_products'] ) ) && ! is_ic_shortcode_query() ) {
        if ( is_ic_product_listing() && ! empty( $archive_names['all_products'] ) ) {
            $title = do_shortcode( $archive_names['all_products'] );
            if ( get_product_listing_title() != $title ) {
                echo '<h2 class="catalog-header">' . $title . '</h2>';
            }
        } else if ( is_ic_taxonomy_page() && ! empty( $archive_names['category_products'] ) && is_ic_product_listing_showing_cats() ) {
            //$the_tax = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
            echo '<h2 class="catalog-header">' . do_shortcode( $archive_names['category_products'] ) . '</h2>';
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
    if ( empty( $_GET['product_category'] ) && ( ! is_admin() || is_ic_ajax() ) && ( is_ic_ajax() || $query->is_main_query() ) && is_ic_taxonomy_page( $query ) && is_ic_only_main_cats( $query ) && ! is_product_filter_active( 'product_category' ) ) {
        foreach ( $query->tax_query->queries as $i => $xquery ) {
            if ( ! empty( $query->tax_query->queries[ $i ] ) && is_array( $query->tax_query->queries[ $i ] ) && ! empty( $query->tax_query->queries[ $i ]['taxonomy'] ) ) {
                $query->tax_query->queries[ $i ]['include_children'] = 0;
            }
        }
    }
}

add_filter( 'product_listing_classes', 'add_classes_on_categories' );

/**
 * Adds neccessary classes for some themes
 *
 * @param string $classes
 *
 * @return string
 */
function add_classes_on_categories( $classes ) {
    if ( is_tax() /* && is_ic_only_main_cats() */ ) {
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
    $settings = ic_catalog_theme_integration::settings();
    $styling  = '<style>';
    if ( $settings['container_width'] != 100 ) {
        $styling .= '#container.content-area.product-catalog {width: ' . $settings['container_width'] . '%; margin: 0 auto; overflow: hidden; box-sizing: border-box; float: none;}';
    }
    if ( $settings['container_bg'] !== '' && ic_string_contains( $settings['container_bg'], '#' ) ) {
        $styling .= '#container.content-area.product-catalog {background: ' . $settings['container_bg'] . ';}';
    }
    if ( $settings['container_padding'] != 0 ) {
        $styling .= '.content-area.product-catalog #content {padding: ' . $settings['container_padding'] . 'px; box-sizing: border-box; float: none; }';
        if ( is_ic_default_theme_sided_sidebar_active() ) {
            $styling .= '.content-area.product-catalog #catalog_sidebar {padding: ' . $settings['container_padding'] . 'px; box-sizing: border-box;}';
        }
    }
    if ( $settings['container_text'] !== '' && ic_string_contains( $settings['container_text'], '#' ) ) {
        $styling .= '#container.content-area.product-catalog * {color: ' . $settings['container_text'] . ';}';
    }
    if ( $settings['default_sidebar'] == 'left' ) {
        $styling .= '.content-area.product-catalog #catalog_sidebar {float: left;}';
    }
    if ( is_ic_default_theme_sided_sidebar_active() ) {
        $styling .= '.content-area.product-catalog #content {width: 70%;';
        if ( $settings['default_sidebar'] == 'left' ) {
            $styling .= 'float:right;';
        } else if ( $settings['default_sidebar'] == 'right' ) {
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
    if ( is_ic_default_theme_sided_sidebar_active() || ( is_ic_integration_wizard_page() && isset( $_GET['test_advanced'] ) && $_GET['test_advanced'] == 1 ) ) {
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
    $sidebar_id = apply_filters( 'catalog_default_sidebar_id', 'catalog_sidebar' );
    $class      = apply_filters( 'catalog_default_sidebar_class', 'catalog_sidebar' );
    echo '<div id="' . $sidebar_id . '" class="' . $class . '" role="complementary">';
    $first_sidebar = ic_get_theme_primary_sidebar();
    dynamic_sidebar( $first_sidebar );
    echo '</div>';
}

function ic_get_theme_primary_sidebar() {
    $registered_sidebars = $GLOBALS['wp_registered_sidebars'];
    unset( $registered_sidebars['product_sort_bar'] );
    foreach ( $registered_sidebars as $sidebar_name => $sidebar ) {
        if ( ic_string_contains( $sidebar['name'], 'primary' ) || ic_string_contains( $sidebar['name'], 'Primary' ) ) {
            $first_sidebar = $sidebar_name;
            break;
        }
    }
    if ( ! isset( $first_sidebar ) ) {
        reset( $registered_sidebars );
        $first_sidebar = key( $registered_sidebars );
    }

    return apply_filters( 'advanced_mode_sidebar_name', $first_sidebar, $registered_sidebars );
}

/**
 * Returns realted products
 *
 * @param int $products_limit
 * @param boolean $markup
 *
 * @return string
 * @global object $post
 */
function get_related_products( $products_limit = null, $markup = false, $product_id = null ) {
    if ( ! isset( $products_limit ) ) {
        $products_limit = apply_filters( 'related_products_count', get_current_per_row() );
    }
    if ( ! empty( $product_id ) ) {
        $current_product_id = intval( $product_id );
    }
    if ( empty( $current_product_id ) ) {
        $current_product_id = ic_get_product_id();
    }
    $taxonomy  = apply_filters( 'ic_cat_related_products_tax', get_current_screen_tax() );
    $post_type = get_current_screen_post_type();
    $terms     = ic_get_product_categories( $current_product_id );
    if ( is_array( $terms ) && ! empty( $taxonomy ) && ! empty( $post_type ) ) {
        $terms = apply_filters( 'ic_catalog_related_product_terms', array_reverse( $terms ), $current_product_id );
        //$archive_template	 = get_product_listing_template();
        $i = 0;
        //$inside				 = '';
        $products = array();
        foreach ( $terms as $term ) {
            $query_param = array(
                    'post_type'      => $post_type,
                    'orderby'        => 'rand',
                    'tax_query'      => array(
                            array(
                                    'taxonomy' => $taxonomy,
                                    'field'    => 'slug',
                                    'terms'    => $term->slug,
                            ),
                    ),
                    'posts_per_page' => $products_limit * 2,
            );
            $query       = new WP_Query( $query_param );
            while ( $query->have_posts() ) : $query->the_post();
                global $post;
                if ( $current_product_id != $post->ID ) {
                    $i ++;
                    $products[] = $post->ID;
                }
                if ( $i >= $products_limit ) {
                    break;
                }
                //ic_delete_global( 'product_id' );
            endwhile;
            wp_reset_postdata();
            reset_row_class();
            if ( $i >= $products_limit ) {
                break;
            }
        }
        $div = '';
        if ( ! empty( $products ) ) {
            $products = apply_filters( 'ic_cat_related_products', implode( ',', $products ), $products_limit );
            ic_save_global( 'current_related_products', $products );
            remove_filter( 'shortcode_query', 'set_shortcode_product_order', 10, 2 );
            if ( $markup ) {
                ob_start();
                ic_show_template_file( 'product-page/related-products.php' );
                $div = ob_get_clean();
            } else {
                $div = do_shortcode( '[show_products post_type="' . $post_type . '" product="' . $products . '"]' );
            }
            add_filter( 'shortcode_query', 'set_shortcode_product_order', 10, 2 );
        }

        return $div;
    }

    return;
}

function ic_get_product_categories( $product_id, $taxonomy = null, $args = null ) {
    if ( empty( $taxonomy ) ) {
        $taxonomy = apply_filters( 'ic_cat_related_products_tax', get_current_screen_tax() );
    }
    $terms = get_the_terms( $product_id, $taxonomy );

    return $terms;
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
    add_filter( 'ic_product_cat_desc', 'wptexturize' );
    add_filter( 'ic_product_cat_desc', 'convert_smilies' );
    add_filter( 'ic_product_cat_desc', 'convert_chars' );
    add_filter( 'ic_product_cat_desc', 'wpautop' );
    add_filter( 'ic_product_cat_desc', 'shortcode_unautop' );
    add_filter( 'ic_product_cat_desc', 'do_shortcode', 11 );
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
    if ( ! is_tax() && ! is_search() ) {
        $before_archive = content_product_adder_archive_before();
        if ( $before_archive != '<div class="entry-summary"></div>' ) {
            echo $before_archive;
        }
        if ( $multiple_settings['product_listing_cats'] == 'on' || $multiple_settings['product_listing_cats'] == 'cats_only' || $multiple_settings['product_listing_cats'] == 'forced_cats_only' ) {

            if ( $multiple_settings['cat_template'] != 'template' ) {
                $product_subcategories = wp_list_categories( 'show_option_none=No_cat&echo=0&title_li=&taxonomy=' . $taxonomy_name . '&parent=0' );
                if ( ! strpos( $product_subcategories, 'No_cat' ) ) {
                    do_action( 'before_product_listing_category_list' );
                    ic_save_global( 'current_product_categories', $product_subcategories );
                    ic_save_global( 'current_product_archive_template', get_product_listing_template() );
                    ic_show_template_file( 'product-listing/categories-listing.php' );
                }
            } else {
                $show_categories = do_shortcode( '[show_categories parent="0" shortcode_query="no"]' );
                if ( ! empty( $show_categories ) ) {
                    do_action( 'before_product_listing_category_list' );
                    echo $show_categories;
                    if ( $archive_template != 'list' && more_products() && $multiple_settings['product_listing_cats'] !== 'forced_cats_only'/* && !is_ic_only_main_cats() */ ) {
                        echo '<hr>';
                    }
                }
            }
        }
    } else if ( is_tax() ) {
        $term = ic_get_queried_object();
        if ( empty( $term->term_id ) ) {
            return;
        }
        if ( current_filter() === 'product_listing_entry_inside' ) {
            if ( has_shortcode( $term->description, 'product_listing_products' ) ) {
                remove_action( 'product_listing_entry_inside', 'ic_product_listing_products', 20, 2 );
                add_action( 'after_product_list', 'product_archive_pagination', 99, 0 );
                remove_action( 'product_listing_end', 'product_archive_pagination' );
            }
            do_action( 'product_category_page_start', $term->term_id );

            if ( has_shortcode( $term->description, 'product_listing_categories' ) ) {
                return;
            }
        }
        if ( $multiple_settings['category_top_cats'] == 'on' || $multiple_settings['category_top_cats'] == 'only_subcategories' ) {
            if ( $multiple_settings['cat_template'] != 'template' ) {
                $product_subcategories = wp_list_categories( 'show_option_none=No_cat&echo=0&title_li=&taxonomy=' . $taxonomy_name . '&child_of=' . $term->term_id );
                if ( ! strpos( $product_subcategories, 'No_cat' ) ) {
                    do_action( 'before_category_subcategories' );
                    ic_save_global( 'current_product_categories', $product_subcategories );
                    ic_save_global( 'current_product_archive_template', get_product_listing_template() );
                    ic_show_template_file( 'product-listing/categories-listing.php' );
                }
            } else {
                $show_categories = do_shortcode( '[show_categories parent=' . get_queried_object_id() . ' shortcode_query=no]' );
                if ( ! empty( $show_categories ) ) {
                    do_action( 'before_category_subcategories' );
                    echo $show_categories;
                    if ( $archive_template != 'list' && more_products() /* && !is_ic_only_main_cats() */ ) {
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
    global $ic_is_home;
    if ( ! is_ic_ajax() && ( is_ic_product_listing() || ! is_ic_catalog_page() ) && $multiple_settings['product_listing_cats'] == 'forced_cats_only' ) {
        return;
    }

    if ( is_home_archive() || ( ! more_products() && ( is_custom_product_listing_page() || ( ! is_ic_catalog_page() && ic_get_global( 'inside_show_catalog_shortcode' ) && ! is_ic_only_main_cats() ) ) ) ) {
        $catalog_query = ic_set_home_listing_query();
        if ( ! empty( $catalog_query ) ) {
            global $wp_query;
            $wp_query = $catalog_query;

        }
        $ic_is_home = 1;
    }
    global $wp_query;
    if ( /* (!is_ic_only_main_cats() || is_search() || is_product_filters_active() || is_ic_ajax() ) && */ more_products() ) {
        do_action( 'before_product_list', $archive_template, $multiple_settings );
        $product_list = '';
        while ( have_posts() ) : the_post();
            $post = get_post();
            if ( empty( $post->ID ) ) {

                continue;
            }
            ic_set_product_id( $post->ID );
            $product_list .= get_catalog_template( $archive_template, $post );
            ic_reset_product_id();
        endwhile;
        $product_list = apply_filters( 'product_list_ready', $product_list, $archive_template, 'auto_listing' );
        echo '<div class="product-list ' . $archive_template . ' ' . product_list_class( $archive_template ) . '" ' . product_list_attr() . '>' . $product_list . '</div>';
        do_action( 'after_product_list', $archive_template, $multiple_settings );
        add_action( 'product_listing_end', 'ic_product_clear_span', 99 );
    } else if ( ( ! is_product_filters_active() && is_search() ) && ! more_products() ) {
        do_action( 'ic_before_empty_search', $archive_template, $multiple_settings );
        ob_start();
        $notfound_text = __( 'Sorry, but nothing matched your search terms. Please try again with some different keywords.', 'ecommerce-product-catalog' );
        echo '<div class="product-list ' . product_list_class( $archive_template ) . '"><p>' . apply_filters( 'ic_catalog_not_found_message', $notfound_text ) . '</p></div>';
        product_search_form();
        echo apply_filters( 'ic_catalog_not_found_content', ob_get_clean() );
    } else if ( is_product_filters_active() && ! more_products() ) {
        show_product_sort_bar();
        $notfound_filters_text = sprintf( __( 'Sorry, but nothing matched your search terms. Please try again with some different options or %sreset filters%s.', 'ecommerce-product-catalog' ), '<a href="' . esc_url( get_filters_bar_reset_url() ) . '">', '</a>' );
        echo '<div class="product-list ' . product_list_class( $archive_template ) . '"><p>' . apply_filters( 'ic_catalog_not_found_filters_message', $notfound_filters_text ) . '</p></div>';
    } else if ( ! more_products() && ! ic_is_rendering_catalog_block() && ! is_ic_admin() && ( ! is_ic_only_main_cats() || ( is_ic_taxonomy_page() && ! has_category_children() ) ) ) {
        echo ic_empty_list_text();
    }

}

function ic_home_listing_query_args() {
    $args = ic_get_global( 'home_listing_query_args' );
    if ( $args !== false ) {
        return $args;
    }
    $multiple_settings = get_multiple_settings();
    if ( get_query_var( 'paged' ) ) {
        $paged = get_query_var( 'paged' );
    } elseif ( get_query_var( 'page' ) ) {
        $paged = get_query_var( 'page' );
    } else {
        $paged = 1;
    }
    $args = apply_filters( 'home_product_listing_query', array(
            'post_status'    => ic_visible_product_status(),
            'post_type'      => 'al_product',
            'posts_per_page' => isset( $multiple_settings['archive_products_limit'] ) ? $multiple_settings['archive_products_limit'] : 12,
            'paged'          => $paged
    ) );
    ic_save_global( 'home_listing_query_args', $args );

    return $args;
}

function ic_set_home_listing_query() {
    $args = ic_home_listing_query_args();
    if ( ! empty( $args ) ) {
        $catalog_query = apply_filters( 'ic_home_query', '', $args );
        if ( empty( $catalog_query ) ) {
            $cache_meta    = 'ic_home_listing_query_' . serialize( array_values( $args ) );
            $catalog_query = ic_get_global( $cache_meta );
            if ( $catalog_query !== false ) {
                return $catalog_query;
            }
            do_action( 'ic_before_home_listing_query' );
            $catalog_query = new WP_Query( $args );
            do_action( 'ic_after_home_listing_query' );

            if ( ! empty( $args['is_archive'] ) ) {
                $catalog_query->is_post_type_archive = true;
            }
            ic_save_global( $cache_meta, $catalog_query );
        }

        return $catalog_query;
    }

    return false;
}

function ic_product_clear_span() {
    ?>
    <span class="clear"></span>
    <?php

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

add_filter( 'body_class', 'ic_catalog_page_body_class', 99 );

function ic_catalog_page_body_class( $classes ) {
    if ( is_ic_catalog_page() ) {
        $classes[] = 'ecommerce-product-catalog';
        if ( is_ic_product_listing() ) {
            $classes[] = 'main-catalog-page';
        }
        if ( ! is_ic_theme_mode() ) {
            $classes[]     = 'type-page';
            $classes[]     = 'page';
            $listing_id    = intval( get_product_listing_id() );
            $template_slug = apply_filters( 'ic_catalog_page_template', get_page_template_slug( $listing_id ) );
            if ( ! empty( $template_slug ) ) {
                $classes[]      = "page-template";
                $template_parts = explode( '/', $template_slug );
                foreach ( $template_parts as $part ) {
                    $classes[] = "page-template-" . sanitize_html_class( str_replace( array(
                                    '.',
                                    '/'
                            ), '-', basename( $part, '.php' ) ) );
                }
                $classes[] = "page-template-" . sanitize_html_class( str_replace( '.', '-', $template_slug ) );
            } else {
                $classes[] = "page-template-default";
            }
        }
    }

    return $classes;
}

add_filter( 'ic_catalog_single_body_class', 'ic_catalog_product_page_body_class' );

function ic_catalog_product_page_body_class( $body_class ) {
    $product_id   = ic_get_product_id();
    $body_class[] = 'single-product-page';
    $terms        = wp_get_post_terms( $product_id, 'al_product-cat', array( "fields" => "ids", "orderby" => "none" ) );
    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term_id ) {
            $body_class[] = 'product-category-' . $term_id;
            $parent_terms = ic_get_parent_terms( $term_id );
            if ( ! empty( $parent_terms ) ) {
                foreach ( $parent_terms as $parent_term_id ) {
                    $body_class[] = 'parent-product-category-' . $parent_term_id;
                }
            }
        }
    }

    return $body_class;
}

add_filter( 'ic_catalog_tax_body_class', 'ic_catalog_product_category_body_class' );

function ic_catalog_product_category_body_class( $body_class ) {
    $term_id = ic_get_current_category_id();
    if ( empty( $term_id ) ) {
        return $body_class;
    }
    $term = get_term( $term_id );
    if ( ! is_wp_error( $term ) ) {
        $body_class[] = 'product-category-page';
        $body_class[] = 'product-category-page-' . $term_id;
    }
    if ( ! empty( $term->parent ) ) {
        $terms = array( $term->parent );
        foreach ( $terms as $term_id ) {
            $body_class[] = 'product-category-page-parent-' . $term_id;
            $parent_terms = ic_get_parent_terms( $term_id );
            if ( ! empty( $parent_terms ) ) {
                foreach ( $parent_terms as $parent_term_id ) {
                    $body_class[] = 'product-category-page-parent-' . $parent_term_id;
                }
            }
        }
    }

    return $body_class;
}

function ic_get_parent_terms( $term_id ) {
    $term = get_term( $term_id );
    if ( ! empty( $term->parent ) ) {
        $term_ids    = array( $term->parent );
        $parent_term = ic_get_parent_terms( $term->parent );
        if ( ! empty( $parent_term ) ) {
            $term_ids = array_merge( $term_ids, $parent_term );
        }

        return array_unique( $term_ids );
    }

    return array();
}

if ( ! function_exists( 'ic_get_template_file' ) ) {

    /**
     * Manages template files paths
     *
     * @param type $file_path
     *
     * @return type
     */
    function ic_get_template_file( $file_path, $base_path = AL_BASE_TEMPLATES_PATH, $product_id = false ) {
        if ( empty( $base_path ) ) {
            $base_path = AL_BASE_TEMPLATES_PATH;
        }
        $folder    = get_custom_templates_folder();
        $file_path = apply_filters( 'ic_template_file_path', $file_path, $product_id );
        $base_path = apply_filters( 'ic_template_file_base_path', $base_path, $file_path );
        $file_name = basename( $file_path );
        if ( file_exists( $folder . $file_name ) ) {
            return $folder . $file_name;
        } else if ( file_exists( $base_path . '/templates/template-parts/' . $file_path ) ) {
            return $base_path . '/templates/template-parts/' . $file_path;
        } else {
            return false;
        }
    }

}

if ( ! function_exists( 'ic_show_template_file' ) ) {

    /**
     * Includes template file
     *
     * @param type $file_path
     *
     * @return type
     */
    function ic_show_template_file( $file_path, $base_path = AL_BASE_TEMPLATES_PATH, $product_id = false ) {
        $path = ic_get_template_file( $file_path, $base_path, $product_id );
        if ( $path ) {
            ic_enqueue_main_catalog_js_css();
            if ( $product_id ) {
                $prev_id = ic_get_global( 'product_id' );
                if ( $prev_id !== $product_id && is_ic_product( $product_id ) ) {
                    ic_save_global( 'product_id', $product_id, false, false, true );
                }
            }
            include $path;
            if ( $product_id && isset( $prev_id ) ) {
                //$prev_id = isset( $prev_id ) ? $prev_id : null;
                if ( $prev_id && $prev_id !== $product_id && is_ic_product( $prev_id ) ) {
                    ic_save_global( 'product_id', $prev_id, false, false, true );
                } else if ( $prev_id === false ) {
                    ic_delete_global( 'product_id' );
                }
            }
        }

        return;
    }

}

add_filter( 'get_the_archive_title', 'ic_catalog_archive_title' );

function ic_catalog_archive_title( $title ) {
    if ( is_ic_taxonomy_page() ) {
        $title = single_term_title( '', false );
    } else if ( is_ic_product_listing() ) {
        $title = post_type_archive_title( '', false );
    }

    return $title;
}

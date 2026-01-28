<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Manages product functions
 *
 * Here all plugin functions are defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        impleCode
 */

/**
 * Returns default product image
 *
 * @return string
 */
function default_product_thumbnail() {
    $default_image = apply_filters( 'ic_default_product_image', '' );
    if ( ! empty( $default_image ) ) {
        return $default_image;
    }
    $default_url = default_product_thumbnail_url( false );
    if ( ! empty( $default_url ) ) {
        $url = $default_url;
    } else {
        $product_id = get_the_ID();
        $sample_id  = sample_product_id();
        if ( ! empty( $sample_id ) && $product_id == $sample_id && is_ic_product_page() ) {
            $url = AL_PLUGIN_BASE_PATH . 'img/implecode.jpg';
        } else {
            $url = AL_PLUGIN_BASE_PATH . 'img/no-default-thumbnail.png';
        }
    }

    return '<img src="' . $url . '"  />';
}

/**
 * Returns default product image URL
 *
 * @return string
 */
function default_product_thumbnail_url( $get_plugin_default = true, $get_user_default = true ) {
    if ( $get_user_default ) {
        $default_id = ic_default_product_image_id();
        if ( ! empty( $default_id ) ) {
            $image = wp_get_attachment_image_src( $default_id, 'full' );
            if ( ! empty( $image[0] ) ) {
                return $image[0];
            }
        }
    }
    $url = '';
    if ( get_option( 'default_product_thumbnail' ) ) {
        $url = get_option( 'default_product_thumbnail' );
    } else if ( $get_plugin_default ) {
        $url = AL_PLUGIN_BASE_PATH . 'img/no-default-thumbnail.png';
    }

    return $url;
}

function ic_default_product_image_id() {
    return get_option( 'ic_default_product_image_id', '' );
}

add_filter( 'ic_category_image_id', 'ic_set_default_category_image_id', 50 );

function ic_set_default_category_image_id( $image_id ) {
    if ( empty( $image_id ) ) {
        $image_id = ic_default_product_image_id();
    }

    return $image_id;
}

//add_action( 'wp', 'redirect_listing_on_non_permalink' );

/**
 * Redirects the product listing page to archive page on non permalink configuration
 *
 */
function redirect_listing_on_non_permalink() {
    if ( ! is_ic_permalink_product_catalog() && get_integration_type() == 'advanced' ) {
        $product_listing_id = get_product_listing_id();
        if ( ! empty( $product_listing_id ) && is_ic_product_listing_enabled() && is_ic_page( $product_listing_id ) ) {
            $url = product_listing_url();
            wp_redirect( $url, 301 );
            exit;
        }
    }
}

function upload_product_image( $name, $button_value, $option_name, $option_value = null, $default_image = null ) {
    wp_enqueue_media();
    if ( empty( $option_value ) ) {
        $option_value = get_option( $option_name );
    }
    if ( empty( $default_image ) ) {
        $default_image = AL_PLUGIN_BASE_PATH . 'img/no-default-thumbnail.png';
    }
    if ( $option_value ) {
        $src = $option_value;
    } else {
        $src = $default_image;
    }
    ?>
    <div class="custom-uploader">
        <input type="hidden" id="default" value="<?php echo $default_image; ?>"/>
        <input type="hidden" name="<?php echo $option_name; ?>" id="<?php echo $name; ?>"
               value="<?php echo $option_value; ?>"/>

        <div class="admin-media-image"><img class="media-image" src="<?php echo $src; ?>" width="100%" height="100%"/>
        </div>
        <a href="#" class="button insert-media add_media" name="<?php echo $name; ?>_button"
           id="button_<?php echo $name; ?>"><span class="wp-media-buttons-icon"></span> <?php echo $button_value; ?></a>
        <a class="button" id="reset-image-button"
           href="#"><?php _e( 'Reset image', 'ecommerce-product-catalog' ); ?></a>
    </div>
    <script>
        jQuery(document).ready(function () {
            jQuery('#button_<?php echo $name; ?>').on('click', function () {
                wp.media.editor.send.attachment = function (props, attachment) {
                    jQuery('#<?php echo $name; ?>').val(attachment.url);
                    jQuery('.media-image').attr("src", attachment.url);
                }

                wp.media.editor.open(this);

                return false;
            });
        });

        jQuery('#reset-image-button').on('click', function () {
            jQuery('#<?php echo $name; ?>').val('');
            src = jQuery('#default').val();
            jQuery('.media-image').attr("src", src);
        });
    </script>
    <?php
}

if ( ! function_exists( 'ic_select_product' ) ) {

    function ic_select_product(
            $first_option, $selected_value, $select_name, $class = null, $echo = 1, $attr = null,
            $exclude = '', $orderby = '', $order = ''
    ) {
        $product_count = ic_products_count();
        if ( $product_count < 1000 ) {
            $catalogs = product_post_type_array();
            $set      = array(
                    'posts_per_page'   => - 1,
                    'offset'           => 0,
                    'orderby'          => 'name',
                    'order'            => 'ASC',
                    'post_type'        => $catalogs,
                    'post_status'      => ic_visible_product_status(),
                    'suppress_filters' => true,
                    'fields'           => 'ids',
                    'exclude'          => $exclude,
            );
            if ( ! empty( $orderby ) ) {
                $set['orderby'] = $orderby;
            }
            if ( ! empty( $order ) ) {
                $set['order'] = $order;
            }

            $pages        = get_posts( $set );
            $field_number = filter_var( $select_name, FILTER_SANITIZE_NUMBER_INT );

            $select_box = '<select custom="' . $field_number . '" id="' . $select_name . '" name="' . $select_name . '" class="all-products-dropdown ' . $class . '" ' . $attr . '>';
            if ( ! empty( $first_option ) ) {
                $select_box .= '<option value="noid">' . $first_option . '</option>';
            }
            foreach ( $pages as $product_id ) {
                if ( is_array( $selected_value ) ) {
                    $selected = in_array( $product_id, $selected_value ) ? 'selected' : '';
                } else {
                    $selected = selected( $product_id, $selected_value, 0 );
                }
                $name = get_product_name( $product_id ) . ' (ID:' . $product_id;
                if ( function_exists( 'is_ic_sku_enabled' ) && is_ic_sku_enabled() ) {
                    $name .= ', SKU:' . get_product_sku( $product_id );
                }
                $name       .= ')';
                $select_box .= '<option class="id_' . $product_id . '" value="' . $product_id . '" ' . $selected . '>' . $name . '</option>';
            }
            $select_box .= '</select>';
        } else {
            if ( is_array( $selected_value ) ) {
                $selected_value = implode( ',', $selected_value );
            }
            $select_box = '<input type="text" name="' . $select_name . '" placeholder="' . __( 'Set Product ID', 'al-implecode-product-sidebar' ) . '" value="' . $selected_value . '"/>';
        }

        return echo_ic_setting( $select_box, $echo );
    }

}

function show_page_link( $page_id ) {
    $page_url  = post_permalink( $page_id );
    $page_link = '<a target="_blank" href=' . $page_url . '>' . $page_url . '</a>';
    echo $page_link;
}

function verify_page_status( $page_id ) {
    $page_status = get_post_status( $page_id );
    if ( $page_status != 'publish' and $page_status != '' ) {
        echo '<div class="al-box warning">This page has wrong status: ' . $page_status . '.<br>Don\'t forget to publish it before going live!</div>';
    }
}

if ( ! function_exists( 'design_schemes' ) ) {

    /**
     *
     * @param string $which color, size, box or none
     * @param int $echo
     *
     * @return string
     */
    function design_schemes( $which = null, $echo = 1 ) {
        $design_schemes                = ic_get_design_schemes();
        $design_schemes['price-color'] = isset( $design_schemes['price-color'] ) ? $design_schemes['price-color'] : '';
        $design_schemes['price-size']  = isset( $design_schemes['price-size'] ) ? $design_schemes['price-size'] : '';
        $design_schemes['box-color']   = isset( $design_schemes['box-color'] ) ? $design_schemes['box-color'] : '';
        if ( $which == 'color' ) {
            $output = $design_schemes['price-color'];
        } else if ( $which == 'size' ) {
            $output = $design_schemes['price-size'];
        } else if ( $which == 'box' ) {
            $output = $design_schemes['box-color'];
        } else if ( $which == 'none' ) {
            $output = '';
        } else {
            $output = $design_schemes['price-color'] . ' ' . $design_schemes['price-size'];
        }
        if ( ! empty( $output ) ) {
            $output .= ' ic-design';
        }

        return echo_ic_setting( apply_filters( 'design_schemes_output', $output, $which ), $echo );
    }

}

/* Single Product Functions */
add_action( 'before_product_entry', 'single_product_header', 10, 1 );

/**
 * Displays header on product pages
 *
 * @param object $post
 * @param array $single_names
 */
function single_product_header( $product_id = false ) {
    if ( ( get_integration_type() != 'simple' && ! is_ic_shortcode_integration() ) || apply_filters( 'ic_catalog_force_product_header', false ) ) {
        if ( is_object( $product_id ) && isset( $product_id->ID ) ) {
            $product_id = $product_id->ID;
        }
        ic_show_template_file( 'product-page/product-header.php', AL_BASE_TEMPLATES_PATH, $product_id );
    }
}

add_action( 'single_product_header', 'add_product_name', 10, 1 );

/**
 * Shows product name on product page
 */
function add_product_name( $product_id = false ) {
    if ( is_ic_product_name_enabled() ) {
        if ( is_object( $product_id ) && isset( $product_id->ID ) ) {
            $product_id = $product_id->ID;
        }
        ic_show_template_file( 'product-page/product-name.php', AL_BASE_TEMPLATES_PATH, $product_id );
    }
}

add_action( 'before_product_listing_entry', 'product_listing_header' );

/**
 * Shows product listing header
 *
 * @param object $post
 * @param array $archive_names
 */
function product_listing_header() {
    if ( ( get_integration_type() != 'simple' && ! is_ic_shortcode_integration() ) || apply_filters( 'ic_catalog_force_category_header', false ) ) {
        ic_show_template_file( 'product-listing/listing-header.php' );
    }
}

add_action( 'product_listing_header', 'add_product_listing_name' );

/**
 * Shows product listing title tag
 */
function add_product_listing_name() {
    ic_show_template_file( 'product-listing/listing-title.php' );
}

add_shortcode( 'product_listing_title', 'get_product_catalog_page_title' );

function get_product_catalog_page_title() {
    if ( is_ic_taxonomy_page() ) {
        $archive_names = get_archive_names();
        //$the_tax		=get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
        $the_tax = ic_get_queried_object();
        if ( ! empty( $archive_names['all_prefix'] ) ) {
            if ( has_shortcode( $archive_names['all_prefix'], 'product_category_name' ) ) {
                $title = do_shortcode( $archive_names['all_prefix'] );
            } else {
                $title = do_shortcode( $archive_names['all_prefix'] ) . ' ' . $the_tax->name;
            }
        } else {
            $title = $the_tax->name;
        }
    } else if ( is_ic_product_search() && isset( $_GET['s'] ) ) {
        $search_keyword = apply_filters( 'ic_search_keayword', wp_unslash( strval( $_GET['s'] ) ) );
        if ( ! empty( $search_keyword ) ) {
            $title = __( 'Search Results for:', 'ecommerce-product-catalog' ) . ' <span class="ic-search-keyword">' . wp_unslash( esc_html( $search_keyword ) ) . '</span>';
        } else {
            $title = '';
        }
    } else if ( is_ic_product_listing() ) {
        $title = get_product_listing_title();
    } else {
        $title = get_the_title();
    }

    return $title;
}

add_action( 'product_details', 'show_short_desc', 5, 1 );

/**
 * Shows short description
 *
 */
function show_short_desc( $product_id = false ) {
    if ( is_object( $product_id ) && isset( $product_id->ID ) ) {
        $product_id = $product_id->ID;
    }
    add_filter( 'product_short_description', 'wptexturize' );
    add_filter( 'product_short_description', 'convert_smilies' );
    add_filter( 'product_short_description', 'convert_chars' );
    add_filter( 'product_short_description', 'wpautop' );
    add_filter( 'product_short_description', 'shortcode_unautop' );
    add_filter( 'product_short_description', 'do_shortcode', 11 );
    ic_show_template_file( 'product-page/product-short-description.php', AL_BASE_TEMPLATES_PATH, $product_id );
}

add_action( 'after_product_details', 'show_product_description', 10, 1 );

/**
 * Shows product description
 *
 * @param type $post
 * @param type $single_names
 */
function show_product_description( $product_id = false ) {
    if ( is_object( $product_id ) && isset( $product_id->ID ) ) {
        $product_id = $product_id->ID;
    }
    add_filter( 'product_simple_description', 'wptexturize' );
    add_filter( 'product_simple_description', 'convert_smilies' );
    add_filter( 'product_simple_description', 'convert_chars' );
    add_filter( 'product_simple_description', 'wpautop' );
    add_filter( 'product_simple_description', 'shortcode_unautop' );
    $add_filter = false;
    if ( has_filter( 'the_content', array( 'ic_catalog_template', 'product_page_content' ) ) ) {
        remove_filter( 'the_content', array( 'ic_catalog_template', 'product_page_content' ) );
        $add_filter = true;
    }
    ic_show_template_file( 'product-page/product-description.php', AL_BASE_TEMPLATES_PATH, $product_id );
    if ( $add_filter ) {
        add_filter( 'the_content', array( 'ic_catalog_template', 'product_page_content' ) );
    }
}

add_action( 'single_product_end', 'show_related_categories', 10, 3 );

/**
 * Shows related categories table on product page
 *
 * @param object $post
 * @param array $single_names
 * @param string $taxonomy_name
 *
 * @return string
 */
function show_related_categories( $post, $single_names, $taxonomy_name ) {
    $settings = get_multiple_settings();
    if ( $settings['related'] == 'categories' ) {
        if ( ! empty( $post->ID ) ) {
            $product_id = $post->ID;
        } else if ( is_numeric( $post ) ) {
            $product_id = $post;
        }
        if ( empty( $product_id ) ) {
            return;
        }
        echo get_related_categories( $product_id, $single_names, $taxonomy_name );
    } else if ( $settings['related'] == 'products' ) {
        echo get_related_products( null, true );
    }
}

/**
 * Returns related categories table
 *
 * @param int $product_id
 * @param array $v_single_names
 * @param string $taxonomy_name
 *
 * @return string
 */
function get_related_categories( $product_id, $v_single_names = null, $taxonomy_name = 'al_product-cat' ) {
    $single_names = isset( $v_single_names ) ? $v_single_names : get_single_names();
    $terms        = wp_get_post_terms( $product_id, $taxonomy_name, array(
            'fields'    => 'ids',
            'orderby'   => 'none',
            'childless' => true
    ) );
    if ( empty( $terms ) || is_wp_error( $terms ) || get_integration_type() == 'simple' ) {
        return;
    }
    //$term       = end( $terms );
    $args       = array(
            'title_li'     => '',
            'taxonomy'     => $taxonomy_name,
            'include'      => $terms,
            'echo'         => 0,
            'hierarchical' => 0,
            'style'        => 'none'
    );
    $categories = wp_list_categories( $args );
    $table      = '';
    if ( $categories != '<li class="cat-item-none">No categories</li>' ) {
        $table .= '<div id="product_subcategories" class="product-subcategories">';
        $table .= '<table>';
        $table .= '<tr>';
        $table .= '<td>';
        $table .= $single_names['other_categories'];
        $table .= '</td>';
        $table .= '<td>';
        $table .= trim( trim( str_replace( '<br />', ', ', $categories ) ), ',' );
        $table .= '</td>';
        $table .= '</tr>';
        $table .= '</table>';
        $table .= '</div>';

        return $table;
    }

    return;
}

add_filter( 'the_content', 'show_simple_product_listing' );

/**
 * Shows product listing in simple mode if no shortcode exists.
 *
 * @param string $content
 *
 * @return string
 */
function show_simple_product_listing( $content ) {
    if ( is_main_query() && in_the_loop() && get_integration_type() == 'simple' && is_ic_product_listing() && ! is_ic_shortcode_integration() && is_ic_product_listing_enabled() ) {
        if ( ! has_shortcode( $content, 'show_products' ) ) {
            $archive_multiple_settings = get_multiple_settings();
            $content                   .= do_shortcode( '[show_products pagination=1 products_limit="' . $archive_multiple_settings['archive_products_limit'] . '"]' );
        }
    }

    return $content;
}

function get_quasi_post_type( $post_type = null ) {
    if ( ( empty( $post_type ) && is_home_archive() ) || ( is_array( $post_type ) && in_array( 'al_product', $post_type ) ) ) {
        $post_type = 'al_product';
    } else if ( empty( $post_type ) ) {
        $post_type = get_post_type();
    }
    $quasi_post_type = substr( $post_type, 0, 10 );

    return $quasi_post_type;
}

function get_quasi_post_tax_name( $tax_name, $exact = true ) {
    if ( $exact ) {
        $quasi_tax_name = substr( $tax_name, 0, 14 );
    } else if ( strpos( $tax_name, 'al_product-cat' ) !== false ) {
        $quasi_tax_name = 'al_product-cat';
    }

    return $quasi_tax_name;
}

function product_breadcrumbs() {
    if ( get_integration_type() != 'simple' && ! is_front_page() ) {
        $post_type = get_post_type();
        if ( empty( $post_type ) && isset( $_GET['post_type'] ) ) {
            $post_type = $_GET['post_type'];
        }
        $home_page = get_home_url();
        if ( function_exists( 'additional_product_listing_url' ) && $post_type != 'al_product' && ic_string_contains( $post_type, 'al_product' ) ) {
            if ( ! ic_string_contains( $post_type, 'al_product' ) ) {
                return;
            }
            $catalog_id          = catalog_id( $post_type );
            $product_archives    = additional_product_listing_url();
            $product_archive     = $product_archives[ $catalog_id ];
            $archives_ids        = get_option( 'additional_product_archive_id' );
            $breadcrumbs_options = get_option( 'product_breadcrumbs', unserialize( DEFAULT_PRODUCT_BREADCRUMBS ) );
            if ( empty( $breadcrumbs_options['enable_product_breadcrumbs'][ $catalog_id ] ) || ( ! empty( $breadcrumbs_options['enable_product_breadcrumbs'][ $catalog_id ] ) && $breadcrumbs_options['enable_product_breadcrumbs'][ $catalog_id ] != 1 ) ) {
                return;
            }
            $product_archive_title_options = $breadcrumbs_options['breadcrumbs_title'][ $catalog_id ];
            if ( $product_archive_title_options != '' ) {
                $product_archive_title = $product_archive_title_options;
            } else {
                if ( $archives_ids[ $catalog_id ] === 'noid' ) {
                    $product_archive_title = get_catalogs_names( $catalog_id )['plural'];
                } else {
                    $product_archive_title = get_the_title( $archives_ids[ $catalog_id ] );
                }
            }
        } else {
            $archive_multiple_settings = get_multiple_settings();
            if ( empty( $archive_multiple_settings['enable_product_breadcrumbs'] ) || $archive_multiple_settings['enable_product_breadcrumbs'] != 1 ) {
                return;
            }
            $product_archive = product_listing_url();
            if ( $archive_multiple_settings['breadcrumbs_title'] != '' ) {
                $product_archive_title = $archive_multiple_settings['breadcrumbs_title'];
            } else {
                $product_archive_title = get_product_listing_title();
            }
        }
        $additional = '';
        if ( is_ic_product_page() ) {
            $current_product = apply_filters( 'ic_catalog_breadcrumbs_current_product', get_the_title() );
        } else if ( is_ic_taxonomy_page() ) {
            $obj                 = ic_get_queried_object();
            $current_product     = $obj->name;
            $taxonomy            = isset( $obj->taxonomy ) ? $obj->taxonomy : 'al_product-cat';
            $current_category_id = $obj->term_id;
            $category_parents    = ic_get_product_category_parents( $current_category_id, $taxonomy, true, '|', null, array(), ' itemprop="item" ', '<span itemprop="name">', '</span>' );
            if ( $category_parents && ! is_wp_error( $category_parents ) ) {
                $parents = array_filter( explode( '|', $category_parents ) );
                if ( is_array( $parents ) ) {
                    array_pop( $parents );
                }
            }
        } else if ( is_search() ) {
            $current_product = __( 'Product Search', 'ecommerce-product-catalog' );
        } else {
            $current_product = '';
        }
        $bread_divider = apply_filters( 'ic_breadcrumbs_divider', '»' );
        $archive_names = get_archive_names();
        $bread         = '<p id="breadcrumbs"><span>';
        if ( ! empty( $archive_names['bread_home'] ) ) {
            $bread .= apply_filters( 'product_breadcrumbs_home', '<span class="breadcrumbs-home"><a href="' . $home_page . '"><span>' . $archive_names['bread_home'] . '</span></a></span> ' . $bread_divider . ' ' );
        }
        if ( ! empty( $product_archive ) ) {
            $bread .= '<span class="breadcrumbs-product-archive"><a href="' . $product_archive . '"><span>' . $product_archive_title . '</span></a></span>';
        }
        if ( ! empty( $parents ) && is_array( $parents ) ) {
            foreach ( $parents as $parent ) {
                if ( ! empty( $parent ) ) {
                    $additional .= ' ' . $bread_divider . ' <span>' . $parent . '</span>';
                }
            }
            if ( ! empty( $additional ) ) {
                $bread .= $additional;
            }
        }
        if ( ! empty( $current_product ) ) {
            $bread .= ' ' . $bread_divider . ' <span><span class="breadcrumb_last">' . $current_product . '</span></span>';
        }
        $bread .= '</span>';
        $bread .= '</p>';

        return $bread;
    }
}

function ic_get_current_category_id() {
    $current_category_id = '';
    if ( is_ic_taxonomy_page() ) {
        $cached = ic_get_global( 'current_category_id' );
        if ( $cached !== false ) {
            return apply_filters( 'ic_current_category_id', $cached );
        }
        $obj = ic_get_queried_object();
        if ( ! empty( $obj->term_id ) ) {
            $current_category_id = $obj->term_id;
            ic_save_global( 'current_category_id', $current_category_id );
        }
    }

    return apply_filters( 'ic_current_category_id', $current_category_id );
}

function ic_get_category_url( $category_id ) {
    $link = '';
    if ( is_numeric( $category_id ) ) {
        $link = get_term_link( $category_id );
    }

    return apply_filters( 'ic_category_url', $link, $category_id );
}

function ic_get_category_listing_image_html( $term_id ) {
    $image_html = ic_get_global( 'ic_category_listing_image_html_' . $term_id );
    if ( $image_html ) {
        return $image_html;
    } else {
        return '';
    }
}

function ic_get_product_category_parents(
        $id, $taxonomy, $link = false, $separator = '/', $nicename = false,
        $visited = array(), $attr = '', $open = null, $close = null
) {
    $chain  = '';
    $parent = get_term( $id, $taxonomy );

    if ( is_wp_error( $parent ) ) {
        return $parent;
    }

    if ( $nicename ) {
        $name = $parent->slug;
    } else {
        $name = $parent->name;
    }

    if ( $parent->parent && ( $parent->parent != $parent->term_id ) && ! in_array( $parent->parent, $visited ) ) {
        $visited[] = $parent->parent;
        $chain     .= ic_get_product_category_parents( $parent->parent, $taxonomy, $link, $separator, $nicename, $visited, $attr, $open, $close );
    }

    if ( ! $link ) {
        $chain .= $name . $separator;
    } else {
        $url   = ic_get_category_url( $parent->term_id );
        $chain .= '<a ' . $attr . ' href="' . $url . '">' . $open . $name . $close . '</a>' . $separator;
    }

    return $chain;
}

add_action( 'single_product_begin', 'add_product_breadcrumbs' );
add_action( 'product_listing_begin', 'add_product_breadcrumbs' );

/**
 * Shows product breadcrumbs
 *
 */
function add_product_breadcrumbs() {
    echo product_breadcrumbs();
}

function al_product_register_widgets() {
    register_widget( 'product_cat_widget' );
    register_widget( 'product_widget_search' );
    do_action( 'implecode_register_widgets' );
}

add_action( 'widgets_init', 'al_product_register_widgets' );

function ic_hide_legacy_widget( $widget_types ) {
    $widget_types[] = 'product_categories';
    $widget_types[] = 'product_search';
    $widget_types[] = 'product_category_filter';
    $widget_types[] = 'product_sort_filter';
    $widget_types[] = 'product_price_filter';
    $widget_types[] = 'ic_product_size_filter';
    $widget_types[] = 'related_products_widget';

    return $widget_types;
}

add_filter( 'widget_types_to_hide_from_legacy_widget_block', 'ic_hide_legacy_widget' );

if ( ! function_exists( 'permalink_options_update' ) ) {

    /**
     * Updates the permalink rewrite option that triggers the rewrite function
     */
    function permalink_options_update() {
        update_option( 'al_permalink_options_update', 1, false );
    }

}
if ( ! function_exists( 'check_permalink_options_update' ) ) {

    add_action( 'admin_footer', 'check_permalink_options_update', 99 );

    /**
     * Checks if the permalinks should be rewritten and does it if necessary
     */
    function check_permalink_options_update() {
        $options_update = get_option( 'al_permalink_options_update', 'none' );
        if ( $options_update != 'none' && ( defined( 'DOING_CRON' ) || ( current_user_can( 'manage_product_settings' ) || current_user_can( 'edit_pages' ) ) ) ) {
            flush_rewrite_rules();
            update_option( 'al_permalink_options_update', 'none', false );
        }
    }

}


add_action( 'before_product_details', 'show_product_gallery', 10, 2 );

/**
 * Shows product gallery on product page
 *
 * @param int $product_id
 * @param array $single_options
 *
 * @return string
 */
function show_product_gallery( $product_id, $single_options ) {
    if ( $single_options['enable_product_gallery'] == 1 ) {
        //echo get_product_gallery( $product_id, $single_options );
        $product_image = get_product_image( $product_id );
        if ( ! empty( $product_image ) ) {
            ic_show_template_file( 'product-page/product-image.php', AL_BASE_TEMPLATES_PATH, $product_id );
        }
    } else {
        return;
    }
}

/**
 * Returns whole product gallery for product page
 *
 * @param int $product_id
 * @param array $v_single_options
 *
 * @return string
 */
function get_product_gallery( $product_id, $v_single_options = null ) {
    $single_options = isset( $v_single_options ) ? $v_single_options : get_product_page_settings();
    if ( $single_options['enable_product_gallery'] == 1 ) {
        $product_image = get_product_image( $product_id );
        if ( ! empty( $product_image ) ) {
            ob_start();
            ic_show_template_file( 'product-page/product-image.php', AL_BASE_TEMPLATES_PATH, $product_id );
            $product_gallery = ob_get_clean();

            return $product_gallery;
        }
    } else {
        return;
    }
}

function product_gallery_enabled( $enable, $enable_inserted, $post ) {
    $details_class = 'no-image';
    if ( $enable == 1 ) {
        if ( $enable_inserted == 1 && ! has_post_thumbnail() ) {
            return $details_class;
        } else {
            return;
        }
    } else {
        return $details_class;
    }
}

if ( ! function_exists( 'product_post_type_array' ) ) {

    function product_post_type_array() {
        $array = apply_filters( 'product_post_type_array', array( 'al_product' ) );

        return $array;
    }

}

function product_taxonomy_array() {

    return apply_filters( 'product_taxonomy_array', array( 'al_product-cat' ) );
}

if ( ! function_exists( 'array_to_url' ) ) {

    function array_to_url( $array ) {
        return urlencode( json_encode( $array ) );
    }

}

if ( ! function_exists( 'url_to_array' ) ) {

    function url_to_array( $url, $maybe_serialized = true ) {
        $data = stripslashes( urldecode( $url ) );
        if ( $maybe_serialized && is_serialized( $data ) ) { // Don't attempt to unserialize data that wasn't serialized going in.
            return @unserialize( trim( $data ), [ 'allowed_classes' => false ] );
        } else {
            $json_data = json_decode( trim( $data ), true );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                return $json_data;
            }
        }

        return $data;
    }

}

add_action( 'wp', 'modify_product_listing_title_tag', 99 );

function modify_product_listing_title_tag() {
    if ( is_ic_product_listing() ) {
        add_filter( 'wp_title', 'product_archive_title', 99, 3 );
        add_filter( 'wp_title', 'product_archive_custom_title', 99, 3 );
        add_filter( 'document_title_parts', 'product_archive_title', 99, 3 );
        add_filter( 'document_title_parts', 'product_archive_custom_title', 99, 3 );
    }
}

function product_archive_custom_title( $title = null, $sep = null, $seplocation = null ) {
    global $post;
    if ( is_post_type_archive( 'al_product' ) && is_object( $post ) && $post->post_type == 'al_product' ) {
        $settings = get_multiple_settings();
        if ( $settings['seo_title'] != '' ) {
            $settings['seo_title']     = isset( $settings['seo_title'] ) ? $settings['seo_title'] : '';
            $settings['seo_title_sep'] = isset( $settings['seo_title_sep'] ) ? $settings['seo_title_sep'] : '';
            if ( $settings['seo_title_sep'] == 1 ) {
                if ( $sep != '' ) {
                    $sep = ' ' . $sep . ' ';
                }
            } else {
                $sep = '';
            }
            if ( is_array( $title ) ) {
                $title['title'] = $settings['seo_title'];
            } else {
                if ( $seplocation == 'right' ) {
                    $title = $settings['seo_title'] . $sep;
                } else {
                    $title = $sep . $settings['seo_title'];
                }
            }
        }
    }

    return $title;
}

function product_archive_title( $title = null, $sep = null, $seplocation = null ) {
    global $post;
    if ( is_ic_product_listing() && is_object( $post ) && $post->post_type == 'al_product' ) {
        $settings = get_multiple_settings();
        if ( $settings['seo_title'] == '' ) {
            $id = get_product_listing_id();
            if ( ! empty( $id ) ) {
                if ( is_array( $title ) ) {
                    $title['title'] = get_the_title( $id );
                } else {
                    $title = ic_get_single_post_title( $title, $id, $sep, $seplocation );
                }
            }
        }
    }

    return $title;
}

function ic_get_single_post_title( $title, $post_id, $sep, $seplocation ) {
    global $wp_query;
    $old_wp_query = $wp_query;
    $wp_query     = new WP_Query( array( 'page_id' => $post_id ) );
    if ( ! empty( $wp_query->posts ) ) {
        remove_filter( 'wp_title', 'product_archive_title', 99, 3 );
        $title = wp_title( $sep, false, $seplocation );
    }
    $wp_query = $old_wp_query;

    return $title;
}

function add_support_link( $links, $file ) {

    $plugin = plugin_basename( AL_PLUGIN_MAIN_FILE );

    // create link
    if ( $file == $plugin ) {
        return array_merge(
                $links, array( sprintf( '<a href="edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=support">%s</a>', __( 'Support' ) ) )
        );
    }

    return $links;
}

add_filter( 'plugin_row_meta', 'add_support_link', 10, 2 );

if ( ! function_exists( 'implecode_al_box' ) ) {

    function implecode_al_box( $text, $type = 'info', $echo = 1 ) {
        $box = '<div class="al-box ' . $type . '">';
        $box .= $text;
        $box .= '</div>';

        return echo_ic_setting( $box, $echo );
    }

}

/**
 * Returns all products array
 *
 * @param type $orderby
 * @param type $order
 * @param type $per_page
 * @param type $offset
 *
 * @return type
 */
function get_all_catalog_products( $orderby = null, $order = null, $per_page = null, $offset = null, $custom = null ) {
    ic_raise_memory_limit();
    if ( ic_is_reaching_memory_limit() ) {
        wp_cache_flush();
    }
    $product_post_types = product_post_type_array();
    $post_types         = array();
    foreach ( $product_post_types as $post_type ) {
        if ( ic_string_contains( $post_type, 'al_product' ) ) {
            $post_types[] = $post_type;
        }
    }
    $args = apply_filters( 'ic_get_all_catalog_products_args', array(
            'post_type'      => $post_types,
            'post_status'    => ic_visible_product_status(),
            'posts_per_page' => 500,
    ) );
    if ( ! empty( $orderby ) ) {
        $args['orderby'] = $orderby;
    }
    if ( ! empty( $order ) ) {
        $args['order'] = $order;
    }
    if ( ! empty( $per_page ) ) {
        $args['posts_per_page'] = $per_page;
    }
    if ( ! empty( $offset ) ) {
        $args['offset'] = $offset;
    }
    if ( ! empty( $custom ) ) {
        $args = array_merge( $args, $custom );
    }
    $products = get_posts( $args );
    if ( ic_is_reaching_memory_limit() ) {
        wp_cache_flush();
    }

    return $products;
}

function all_ctalog_products_dropdown(
        $option_name, $first_option, $selected_value, $orderby = null, $order = null,
        $all_option = false, $attr = '', $before_options = array(), $pages = array()
) {
    if ( empty( $pages ) ) {
        $pages = get_all_catalog_products( $orderby, $order );
    }
    if ( is_array( $selected_value ) ) {
        $attr .= ' multiple';
    }
    $select_box = '<select class="all_products_dropdown ic_chosen" data-placeholder="' . $first_option . '" id="' . $option_name . '" name="' . $option_name . '"' . $attr . '>';
//if ( !empty( $first_option ) ) {
    $select_box .= '<option value=""></option>';
    //}
    foreach ( $before_options as $option_value => $option_label ) {
        $select_box .= '<option value="' . $option_value . '">' . $option_label . '</option>';
    }
    foreach ( $pages as $page ) {
        $selected_attr = '';
        if ( is_array( $selected_value ) ) {
            if ( in_array( $page->ID, $selected_value ) ) {
                $selected_attr = 'selected';
            }
        } else {
            $selected_attr = selected( $page->ID, $selected_value, 0 );
        }
        $select_box .= '<option class="id_' . $page->ID . '" name="' . $option_name . '[' . $page->ID . ']" value="' . $page->ID . '" ' . $selected_attr . '>' . $page->post_title . '</option>';
    }
    if ( $all_option ) {
        $select_box .= '<option class="id_all" name="' . $option_name . '[all]" value="all" ' . selected( 'all', $selected_value, 0 ) . '>' . __( 'All', 'ecommerce-product-catalog' ) . '</option>';
    }
    $select_box .= '</select>';

    return $select_box;
}

add_action( 'after_setup_theme', 'thumbnail_support_products', 99 );
add_action( 'init', 'thumbnail_support_products', 99 );

/**
 * Adds featured image support for products
 *
 */
function thumbnail_support_products() {
    $support       = get_theme_support( 'post-thumbnails' );
    $support_array = product_post_type_array();
    if ( is_array( $support ) ) {
        if ( ! in_array( 'al_product', $support[0] ) ) {
            $support_array = array_merge( $support[0], $support_array );
            add_theme_support( 'post-thumbnails', $support_array );
        }
    } else if ( ! $support ) {
        add_theme_support( 'post-thumbnails', $support_array );
    } else {
        add_theme_support( 'post-thumbnails' );
    }
}

add_action( 'pre_get_posts', 'ic_pre_get_products', 99 );

function ic_pre_get_products( $query ) {
    if ( ( ( ! is_admin() && $query->is_main_query() ) || ( is_ic_ajax() && is_object( $query ) && empty( $query->query['ic_current_products'] ) ) ) && ! isset( $_GET['order'] ) && ( ( ! empty( $query->query['post_type'] ) && ic_string_contains( $query->query['post_type'], 'al_product' ) ) || is_ic_product_listing( $query ) || is_ic_taxonomy_page( $query ) || is_ic_product_search( $query ) || is_home_archive( $query ) ) ) {
        do_action( 'ic_pre_get_products', $query );
        if ( is_ic_product_listing( $query ) ) {
            do_action( 'ic_pre_get_products_listing', $query );
        } else if ( is_ic_taxonomy_page( $query ) ) {
            do_action( 'ic_pre_get_products_tax', $query );
        } else if ( is_ic_product_search( $query ) ) {
            do_action( 'ic_pre_get_products_search', $query );
        }
        if ( apply_filters( 'ic_force_pre_get_products_only', false, $query ) || ( ! empty( $query->query['post_type'] ) && ic_string_contains( $query->query['post_type'], 'al_product' ) && empty( $query->query['name'] ) ) || ( ! empty( $query->query ) && is_array( $query->query ) && ( ic_string_contains( implode( '::', array_keys( $query->query ) ), 'al_product-cat' ) || ! empty( $query->query['al_product-cat'] ) ) ) ) {
            do_action( 'ic_pre_get_products_only', $query );
        }
    }
}

add_filter( 'shortcode_query', 'ic_pre_get_products_shortcode' );

function ic_pre_get_products_shortcode( $query ) {
    do_action( 'ic_pre_get_products_shortcode', $query );

    return $query;
}

add_action( 'ic_pre_get_products_only', 'set_product_order', 30 );

/**
 * Sets default product order
 *
 * @param object $query
 */
function set_product_order( $query ) {
    //if ( ((!is_admin() && $query->is_main_query()) || (defined( 'DOING_AJAX' ) && DOING_AJAX)) && !isset( $_GET[ 'order' ] ) && (is_ic_product_listing( $query ) || is_ic_taxonomy_page( $query )) ) {
    if ( ! ic_is_main_query( $query ) ) {
        return;
    }
    $archive_multiple_settings = get_multiple_settings();
    $excluded_orders           = apply_filters( 'ic_excluded_product_orders', array() );
    if ( ! is_ic_product_search( $query ) && ( ! isset( $_GET['product_order'] ) || in_array( $_GET['product_order'], $excluded_orders ) ) ) {
        if ( $archive_multiple_settings['product_order'] == 'product-name' ) {
            $query->set( 'orderby', 'title' );
            $query->set( 'order', 'ASC' );
        } else {
            $query = apply_filters( 'modify_product_order', $query, $archive_multiple_settings );
        }
        $session = get_product_catalog_session();
        if ( isset( $session['filters']['product_order'] ) ) {
            unset( $session['filters']['product_order'] );
            set_product_catalog_session( $session );
        }
    } else if ( ! empty( $_GET['product_order'] ) ) {
        $orderby = translate_product_order();
        $query->set( 'orderby', $orderby );
        if ( $orderby == 'date' ) {
            $query->set( 'order', 'DESC' );
        } else {
            $query->set( 'order', 'ASC' );
        }
        $query                               = apply_filters( 'modify_product_order-dropdown', $query, $archive_multiple_settings );
        $session                             = get_product_catalog_session();
        $session['filters']['product_order'] = esc_attr( $_GET['product_order'] );
        set_product_catalog_session( $session );
    }
    do_action( 'ic_product_order_set', $query );
    //}
}

add_filter( 'shortcode_query', 'set_shortcode_product_order', 10, 2 );
add_filter( 'home_product_listing_query', 'set_shortcode_product_order' );

function set_shortcode_product_order( $shortcode_query, $args = null ) {
    $archive_multiple_settings = get_multiple_settings();
    $excluded_orders           = apply_filters( 'ic_excluded_product_orders', array() );
    if ( ! isset( $_GET['product_order'] ) || in_array( $_GET['product_order'], $excluded_orders ) ) {
        if ( $archive_multiple_settings['product_order'] == 'product-name' && empty( $args['orderby'] ) ) {
            $shortcode_query['orderby'] = 'title';
            $shortcode_query['order']   = 'ASC';
        }
        if ( isset( $shortcode_query['orderby'] ) && $shortcode_query['orderby'] == 'name' ) {
            $shortcode_query['orderby'] = 'title';
        }
        $shortcode_query = apply_filters( 'shortcode_modify_product_order', $shortcode_query, $archive_multiple_settings, $args );
    } else if ( $_GET['product_order'] != 'newest' && ! empty( $_GET['product_order'] ) ) {
        $orderby                    = translate_product_order();
        $shortcode_query['orderby'] = $orderby;
        $shortcode_query['order']   = 'ASC';
        $shortcode_query            = apply_filters( 'shortcode_modify_product_order-dropdown', $shortcode_query, $archive_multiple_settings );
    }

    return apply_filters( 'ic_shortcode_product_order_set', $shortcode_query );
}

//add_action( 'before_product_list', 'show_product_order_dropdown', 10, 2 );

/**
 * Shows sorting drop down
 *
 * @param string $archive_template
 * @param array $multiple_settings
 *
 * @global string $product_sort
 */
function show_product_order_dropdown( $archive_template = null, $multiple_settings = null, $instance = null ) {
    $multiple_settings = empty( $multiple_settings ) ? get_multiple_settings() : $multiple_settings;
    $sort_options      = apply_filters( 'ic_product_order_dropdown_options', get_product_sort_options(), $instance );
    $selected          = isset( $_GET['product_order'] ) ? esc_attr( $_GET['product_order'] ) : apply_filters( 'ic_product_order_dropdown_selected', $multiple_settings['product_order'], $instance );
    $action            = get_filter_widget_action( $instance );
    $class             = '';
    if ( is_product_filter_active( 'product_order' ) ) {
        $class .= 'filter-active';
    }
    echo '<form class="product_order ic_ajax ' . $class . '" data-ic_responsive_label="' . __( 'Sort by', 'ecommerce-product-catalog' ) . '" data-ic_ajax="product_order" action="' . $action . '"><select class="product_order_selector ic_self_submit" name="product_order">';
    if ( is_ic_product_search() ) {
        $selected = isset( $_GET['product_order'] ) ? esc_attr( $_GET['product_order'] ) : '';
        echo '<option value="" ' . selected( "", $selected, 0 ) . '>' . __( 'Sort by Relevance', 'ecommerce-product-catalog' ) . '</option>';
    }

    foreach ( $sort_options as $name => $value ) {
        $option = '<option value="' . $name . '" ' . selected( $name, $selected, 0 ) . '>' . $value . '</option>';
        echo apply_filters( 'product_order_dropdown_options', $option, $name, $value, $multiple_settings, $selected );
    }
    echo '</select>';
    echo ic_get_to_hidden_field( $_GET, 'product_order' );
    do_action( 'ic_product_order_dropdown_form', $instance );
    echo '</form>';
}

/**
 * Show html hidden form input for each array element
 *
 * @param type $get
 * @param type $exclude
 */
function ic_get_to_hidden_field( $get, $exclude = '', $name = '', $value = null ) {
    $fields = '';
    foreach ( $get as $key => $get_value ) {
        $arrarized = false;
        if ( ( is_array( $exclude ) && ! in_array( strval( $key ), $exclude ) ) || ( ! is_array( $exclude ) && $key !== $exclude ) ) {
            if ( is_array( $get_value ) && empty( $name ) ) {
                $fields .= ic_get_to_hidden_field( $get_value, $exclude, $key, $value );
            } else {
                if ( ! is_array( $get_value ) ) {
                    $get_value = array( $get_value );
                    $arrarized = true;
                }

                if ( ! empty( $name ) && $name !== $key ) {
                    $key = $name . '[' . $key . ']';
                    if ( empty( $arrarized ) ) {
                        $key .= '[]';
                    }
                }

                foreach ( $get_value as $val ) {
                    if ( $value === null || $value != $val ) {
                        $fields .= '<input type="hidden" value="' . esc_attr( ic_sanitize( $val ) ) . '" name="' . esc_attr( ic_sanitize( $key ) ) . '" />';
                    }
                }
            }
        }
    }

    return $fields;
}

add_action( 'before_product_list', 'show_product_sort_bar', 10, 2 );

/**
 * Shows product sort and filters bar
 *
 * @param string $archive_template
 * @param array $multiple_settings
 *
 * @global boolean $is_filter_bar
 */
function show_product_sort_bar( $archive_template = null, $multiple_settings = null ) {
    if ( is_product_sort_bar_active() || is_ic_ajax() ) {
        if ( is_active_sidebar( 'product_sort_bar' ) || is_ic_ajax() ) {
            ic_catalog_filters_bar();
        } else {
            show_default_product_sort_bar( $archive_template, $multiple_settings );
        }
    }
}

add_shortcode( 'catalog_filters_bar', 'ic_catalog_filters_bar_shortcode' );

function ic_catalog_filters_bar_shortcode() {
    ob_start();
    ic_catalog_filters_bar();

    return ob_get_clean();
}

function ic_catalog_filters_bar() {
    global $is_filter_bar;
    $is_filter_bar = true;
    ob_start();
    dynamic_sidebar( 'product_sort_bar' );
    $sidebar_content = ob_get_clean();

    if ( ! empty( $sidebar_content ) ) {
        echo '<div id="product_filters_bar" class="product-sort-bar ' . design_schemes( 'box', 0 ) . ' ' . apply_filters( 'ic_filters_bar_class', '' ) . '">';
        echo $sidebar_content;
        echo '<div class="clear-both"></div>';
        echo '</div>';
        $reset_url = get_filters_bar_reset_url();
        if ( ! empty( $reset_url ) ) {
            echo '<div class="reset-filters"><a href="' . esc_url( $reset_url ) . '">' . __( 'Reset Filters', 'ecommerce-product-catalog' ) . '</a></div>';
        }
    }
    $is_filter_bar = false;
    unset( $is_filter_bar );
}

/**
 * Shows default product sort bar content
 *
 */
function show_default_product_sort_bar( $archive_template, $multiple_settings = null ) {
    if ( get_option( 'old_sort_bar' ) == 1 ) {
        show_product_order_dropdown( $archive_template, $multiple_settings );
    } else if ( current_user_can( 'edit_theme_options' ) && function_exists( 'is_customize_preview' ) ) {
        $show = get_option( 'hide_empty_bar_message', 0 );
        if ( $show == 0 ) {
            global $is_filter_bar;
            $is_filter_bar = true;
            echo '<div class="product-sort-bar ' . design_schemes( 'box', 0 ) . '">';
            echo '<div class="empty-filters-info">';
            echo '<h3>' . __( 'Product Filters Bar has no widgets', 'ecommerce-product-catalog' ) . '</h3>';
            $current_url   = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $customize_url = add_query_arg( array(
                    'url'                           => urlencode( $current_url ),
                    urlencode( 'autofocus[panel]' ) => 'widgets'
            ), wp_customize_url() );
            echo sprintf( __( '%sAdd widgets to the filters bar now%s or %sdismiss this notice%s.', 'ecommerce-product-catalog' ), '<a href="' . $customize_url . '">', '</a>', '<a class="dismiss-empty-bar" href="#">', '</a>' );
            echo '</div>';
            echo '</div>';
            $is_filter_bar = false;
            unset( $is_filter_bar );
        }
    }
}

function translate_product_order( $order = null ) {
    if ( empty( $order ) ) {
        $order = esc_attr( $_GET['product_order'] );
    }
    if ( $order == 'product-name' ) {
        $orderby = 'title';
    } else if ( $order == 'newest' ) {
        $orderby = 'date';
    } else {
        $orderby = apply_filters( 'product_order_translate', $order );
    }

    return $orderby;
}

/**
 * Returns all products count
 *
 * @return type
 */
function ic_products_count( $post_types = '' ) {
    $total = get_transient( 'ic_product_count_cache' );
    if ( is_numeric( $total ) ) {
        return $total;
    }
    if ( empty( $post_types ) ) {
        $post_types = product_post_type_array();
    }
    if ( ! is_array( $post_types ) ) {
        $post_types = array( $post_types );
    }
    $total = 0;
    foreach ( $post_types as $post_type ) {
        if ( ic_string_contains( $post_type, 'al_product' ) ) {
            $count = wp_count_posts( $post_type );
            if ( ! isset( $count->publish ) ) {
                $count = ic_fallback_products_count( $post_type );
            }
            if ( ! empty( $count->publish ) ) {
                $total += intval( $count->publish );
            }
        }
    }

    set_transient( 'ic_product_count_cache', $total );

    return intval( $total );
}

add_action( 'ic_product_status_change', 'ic_product_count_cache_clear' );

function ic_product_count_cache_clear() {
    delete_transient( 'ic_product_count_cache' );
}


function ic_fallback_products_count( $post_type ) {
    global $wpdb;
    $query = "SELECT post_status, COUNT( * ) AS num_posts FROM {$wpdb->posts} WHERE post_type=%s";
    $query .= ' GROUP BY post_status';

    $results = (array) $wpdb->get_results( $wpdb->prepare( $query, $post_type ), ARRAY_A );
    $counts  = array_fill_keys( get_post_stati(), 0 );

    foreach ( $results as $row ) {
        $counts[ $row['post_status'] ] = $row['num_posts'];
    }

    $counts = (object) $counts;

    return $counts;
}

/**
 * Returns per row setting for current product listing theme
 * @return int
 */
function get_current_per_row() {
    $archive_template = get_product_listing_template();
    $per_row          = 3;
    if ( $archive_template == 'default' ) {
        $settings = get_modern_grid_settings();
        $per_row  = $settings['per-row'];
    } else if ( $archive_template == 'grid' ) {
        $settings = get_classic_grid_settings();
        $per_row  = $settings['entries'];
    }

    return apply_filters( 'current_per_row', $per_row, $archive_template );
}

function get_current_screen_tax() {
    $obj        = ic_get_queried_object();
    $taxonomies = array();
    if ( empty( $obj ) ) {
        $taxonomies = array( apply_filters( 'current_product_catalog_taxonomy', 'al_product-cat' ) );
    }
    if ( isset( $obj->ID ) ) {
        $taxonomies = get_object_taxonomies( $obj );
    } else if ( isset( $obj->taxonomies ) ) {
        $taxonomies = $obj->taxonomies;
    } else if ( isset( $obj->taxonomy ) ) {
        $taxonomies = array( $obj->taxonomy );
    }
    $current_tax = apply_filters( 'ic_current_def_product_tax', 'al_product-cat' );
    foreach ( $taxonomies as $tax ) {
        if ( ic_string_contains( $tax, 'al_product-cat' ) ) {
            $current_tax = $tax;
            break;
        }
    }

    return apply_filters( 'ic_current_product_tax', $current_tax );
}

function get_current_screen_post_type() {
    $obj       = ic_get_queried_object();
    $post_type = apply_filters( 'current_product_post_type', 'al_product' );
    if ( isset( $obj->post_type ) && ic_string_contains( $obj->post_type, 'al_product' ) ) {
        $post_type = $obj->post_type;
    } else if ( isset( $obj->name ) && ic_string_contains( $obj->name, 'al_product' ) ) {
        $post_type = $obj->name;
    } else if ( isset( $_GET['post_type'] ) && ! is_array( $_GET['post_type'] ) && ic_string_contains( $_GET['post_type'], 'al_product' ) ) {
        $post_type = sanitize_text_field( $_GET['post_type'] );
    } /* else if ( is_array( $_GET[ 'post_type' ] ) && is_ic_valid_post_type( $_GET[ 'post_type' ] ) ) {
	  $post_type = array_map( 'sanitize_text_field', $_GET[ 'post_type' ] );
	  } */

    return apply_filters( 'ic_current_post_type', $post_type );
}

if ( ! function_exists( 'ic_strtolower' ) ) {
    function ic_strtolower( $string ) {
        if ( function_exists( 'mb_strtolower' ) ) {
            return mb_strtolower( $string );
        } else {
            return strtolower( $string );
        }
    }
}

if ( ! function_exists( 'ic_strtoupper' ) ) {

    function ic_strtoupper( $string ) {
        if ( function_exists( 'mb_strtoupper' ) ) {
            return mb_strtoupper( $string );
        } else {
            return strtoupper( $string );
        }
    }
}
if ( ! function_exists( 'ic_substr' ) ) {

    function ic_substr( $string, $start, $length ) {
        if ( function_exists( 'mb_substr' ) ) {
            return mb_substr( $string, $start, intval( $length ) );
        } else {
            return substr( $string, $start, intval( $length ) );
        }
    }
}
/**
 * Returns current product ID
 *
 * @return type
 */
function ic_get_product_id() {
    $product_id = ic_get_global( 'product_id' );
    if ( ! $product_id ) {
        do_action( 'ic_catalog_set_product_id' );
        $product_id = get_the_ID();
        if ( ! empty( $product_id ) && function_exists( 'is_ic_product' ) && is_ic_product( $product_id ) ) {
            ic_set_product_id( $product_id );
        }
    }

    return $product_id;
}

add_action( 'ic_catalog_wp_head', 'ic_handle_post_thumbnail' );

/**
 * Removes post thumbnail from product header
 *
 */
function ic_handle_post_thumbnail() {
    add_filter( 'get_post_metadata', 'ic_override_product_post_thumbnail', 10, 3 );
    add_filter( 'has_post_thumbnail', 'ic_override_product_post_thumbnail', 10, 2 );
    //add_action( 'before_product_page', 'ic_handle_back_post_thumbnail' );
    add_action( 'single_product_begin', 'ic_handle_back_post_thumbnail' );
    add_action( 'product_listing_begin', 'ic_handle_back_post_thumbnail' );
    add_action( 'before_category_list', 'ic_handle_back_post_thumbnail' );
    add_action( 'before_product_list', 'ic_handle_back_post_thumbnail' );
    add_action( 'ic_before_get_image_html', 'ic_handle_back_post_thumbnail' );
}

/**
 * Adds post thumbnail back to product page
 *
 */
function ic_handle_back_post_thumbnail() {
    remove_filter( 'get_post_metadata', 'ic_override_product_post_thumbnail' );
    remove_filter( 'has_post_thumbnail', 'ic_override_product_post_thumbnail' );
}

/**
 * Clears thumbnail id value
 *
 * @param string $metadata
 * @param type $object_id
 * @param type $meta_key
 *
 * @return string
 */
function ic_override_product_post_thumbnail( $metadata, $object_id, $meta_key = null ) {
    if ( $object_id === null ) {
        $object_id = get_post();
    }
    if ( is_object( $object_id ) ) {
        $object_id = $object_id->ID;
    }
    if ( ( ( isset( $meta_key ) && $meta_key == '_thumbnail_id' ) || ( ! isset( $meta_key ) && current_filter() === 'has_post_thumbnail' ) ) && is_ic_product( $object_id ) ) {
        $metadata = 0;
    }

    return $metadata;
}

if ( ! function_exists( 'ic_filemtime' ) ) {

    function ic_filemtime( $path ) {
        if ( file_exists( $path ) ) {
            return '?timestamp=' . filemtime( $path );
        }
    }

}

/**
 * Returns product current or selected product object
 *
 * @param type $product_id
 *
 * @return \ic_product
 */
function ic_get_product_object( $product_id = null ) {
    if ( empty( $product_id ) ) {
        $product_id = ic_get_product_id();
    } else if ( is_object( $product_id ) && isset( $product_id->ID ) ) {
        $product_id = intval( $product_id->ID );
    }
    $ic_product = ic_get_global( 'ic_product_' . $product_id );
    if ( empty( $ic_product ) ) {
        $ic_product = new ic_product( $product_id );
        ic_save_global( 'ic_product_' . $product_id, $ic_product );
    }

    return $ic_product;
}

function ic_get_permalink( $id = null ) {
    if ( ! empty( $id ) ) {
        $id = apply_filters( 'ic_permalink_id', $id );
    }

    return get_permalink( $id );
}

function ic_get_post_type( $id = null ) {
    if ( empty( $id ) && is_ic_ajax() ) {
        if ( isset( $_POST['query_vars'] ) ) {
            $query_vars = json_decode( stripslashes( $_POST['query_vars'] ), true );
            if ( ! empty( $query_vars['post_type'] ) ) {
                return $query_vars['post_type'];
            }
        }
    }
    $post_type = get_post_type( $id );
    if ( empty( $id ) && $post_type === 'page' ) {
        $catalog_query = ic_get_catalog_query();
        if ( ! empty( $catalog_query->posts[0]->ID ) ) {
            $post_type = ic_get_post_type( $catalog_query->posts[0]->ID );
        } else {
            global $shortcode_query;
            if ( ! empty( $shortcode_query->query['post_type'] ) ) {
                if ( ic_string_contains( $shortcode_query->query['post_type'], 'al_product' ) ) {
                    $post_type = $shortcode_query->query['post_type'];
                }
            }
        }
    }

    return $post_type;
}

function ic_get_archive_price( $product_id ) {
    $post          = get_post( $product_id );
    $archive_price = apply_filters( 'archive_price_filter', '', $post );

    return $archive_price;
}

function ic_empty_list_text() {
    $names = get_catalog_names();
    $text  = sprintf( __( 'No %s available.', 'ecommerce-product-catalog' ), ic_strtolower( $names['plural'] ) );

    return apply_filters( 'ic_empty_list_text', $text );
}

/**
 * Defines not supported query vars
 *
 * @return type
 */
function ic_forbidden_query_vars() {
    return array(
            'title',
            'attachment',
            'attachment_id',
            'author',
            'author_name',
            'cat',
            'calendar',
            'category_name',
            'comments_popup',
            'cpage',
            'day',
            'error',
            'exact',
            'feed',
            'hour',
            'm',
            'minute',
            'monthnum',
            'more',
            'name',
            'order',
            'orderby',
            'p',
            'page_id',
            'page',
            'paged',
            'pagename',
            'pb',
            'post_type',
            'posts',
            'preview',
            'robots',
            's',
            'search',
            'second',
            'sentence',
            'static',
            'subpost',
            'subpost_id',
            'taxonomy',
            'tag',
            'tb',
            'tag_id',
            'term',
            'tb',
            'w',
            'withcomments',
            'withoutcomments',
            'year'
    );
}

if ( ! function_exists( 'ic_filter_objects' ) ) {

    function ic_filter_objects( $var ) {
        if ( is_object( $var ) ) {
            return false;
        }

        return true;
    }

}
if ( ! function_exists( 'ic_is_function_disabled' ) ) {

    function ic_is_function_disabled( $function ) {
        $disabled = explode( ',', ini_get( 'disable_functions' ) );

        return in_array( $function, $disabled );
    }

}

if ( ! function_exists( 'ic_set_time_limit' ) ) {

    function ic_set_time_limit( $limit = 0 ) {
        if ( filter_var( ini_get( 'safe_mode' ), FILTER_VALIDATE_BOOLEAN ) ) {
            return;
        }
        if ( function_exists( 'set_time_limit' ) && ! ic_is_function_disabled( 'set_time_limit' ) ) {
            @set_time_limit( $limit );
        }
    }

}

function ic_visible_product_status( $check_current_user = true ) {
    $visible_status = array( 'publish' );
    if ( $check_current_user && current_user_can( 'read_private_products' ) ) {
        $visible_status[] = 'private';
    }

    return apply_filters( 'ic_visible_product_status', $visible_status, $check_current_user );
}

function ic_get_catalog_mode() {
    $settings                 = get_multiple_settings();
    $settings['catalog_mode'] = ! empty( $settings['catalog_mode'] ) ? $settings['catalog_mode'] : 'simple';

    return $settings['catalog_mode'];
}

function ic_data_should_be_hidden( $product_status ) {
    if ( defined( 'IC_COMPRESS_PRIVATE_PRODUCTS_DATA' ) && IC_COMPRESS_PRIVATE_PRODUCTS_DATA && ! in_array( $product_status, ic_visible_product_status( false ) ) ) {
        return true;
    }

    return false;
}

function ic_sanitize( $data, $strict = true ) {
    if ( is_array( $data ) ) {
        $return = array();
        foreach ( $data as $key => $value ) {
            $return[ $key ] = ic_sanitize( $value, $strict );
        }

        return $return;
    }
    if ( $strict ) {
        return sanitize_text_field( $data );
    } else {
        return addslashes( wp_kses( stripslashes( $data ), 'implecode' ) );
    }
}

add_filter( 'wp_kses_allowed_html', 'ic_wp_kses_allowed_html', 10, 2 );

function ic_wp_kses_allowed_html( $allowedposttags, $context ) {
    if ( $context === 'implecode' ) {
        if ( ! empty( $allowedposttags['a'] ) ) {
            $allowedposttags['a']['target'] = true;
            $allowedposttags['a']['rel']    = true;
        }
        $allowedposttags = apply_filters( 'ic_wp_kses_allowed_html', $allowedposttags );
    }

    return $allowedposttags;
}

add_action( 'wp_print_footer_scripts', 'ic_loading_icon' );

function ic_loading_icon() {
    ?>
    <style>
        body.ic-disabled-body:before {
            background-image: url("<?php echo includes_url( 'js/thickbox/loadingAnimation.gif', 'relative' ) ?>");
        }
    </style>
    <?php
}

if ( ! function_exists( 'ic_setcookie' ) ) {
    function ic_setcookie( $name, $value, $expire = 0, $secure = false, $httponly = false ) {
        if ( ! defined( 'IC_USE_COOKIES' ) || ( defined( 'IC_USE_COOKIES' ) && ! IC_USE_COOKIES ) ) {
            return;
        }
        if ( headers_sent() ) {
            return;
        }
        $options = apply_filters(
                'ic_set_cookie_options',
                array(
                        'expires'  => $expire,
                        'secure'   => $secure,
                        'path'     => COOKIEPATH ? COOKIEPATH : '/',
                        'domain'   => COOKIE_DOMAIN,
                        'httponly' => $httponly,
                        'samesite' => 'lax'
                ),
                $name,
                $value
        );

        if ( version_compare( PHP_VERSION, '7.3.0', '>=' ) ) {
            setcookie( $name, $value, $options );
        } else {
            if ( ! ic_string_contains( $options['path'], 'samesite' ) ) {
                $options['path'] .= '; samesite=' . $options['samesite'];
            }
            setcookie( $name, $value, $options['expires'], $options['path'], $options['domain'], $options['secure'], $options['httponly'] );
        }
        /*
        if ( ! headers_sent() ) {
            setcookie( $name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure, $httponly );
        } /* elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
            headers_sent( $file, $line );
            trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE ); // @codingStandardsIgnoreLine
        } */
    }
}

if ( ! function_exists( 'ic_site_is_https' ) ) {
    /**
     * Check if the home URL is https.
     *
     * @return bool
     */
    function ic_site_is_https() {
        return false !== strstr( get_option( 'home' ), 'https:' );
    }
}

function ic_get_queried_object() {
    $object = get_queried_object();
    if ( empty( $object ) && current_filter() === 'parse_tax_query' ) {
        global $wp_query;
        if ( ! empty( $wp_query->query_vars ) && is_array( $wp_query->query_vars ) ) {
            $taxonomies = product_taxonomy_array();
            foreach ( $wp_query->query_vars as $taxonomy => $slug ) {
                if ( in_array( $taxonomy, $taxonomies ) ) {
                    $object = get_term_by( 'slug', $slug, $taxonomy );
                    if ( ! empty( $object ) ) {
                        break;
                    }
                }
            }

        }
    }

    return $object;
}

function ic_doing_it_wrong( $function, $message, $version ) {
    if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
        return;
    }
    if ( ! defined( 'WP_DEBUG_DISPLAY' ) || WP_DEBUG_DISPLAY ) {
        return;
    }
    if ( ! defined( 'IC_DEBUG' ) || ! IC_DEBUG ) {
        return;
    }
    $message .= ' Backtrace: ' . wp_debug_backtrace_summary();
    _doing_it_wrong( $function, $message, $version );
}

if ( ! function_exists( 'implecode_array_variables_init' ) ) {

    function implecode_array_variables_init( $fields, $data = array() ) {
        if ( ! is_array( $data ) ) {
            $data = array();
        }
        foreach ( $fields as $field ) {
            $data[ $field ] = isset( $data[ $field ] ) ? $data[ $field ] : '';
        }

        return $data;
    }

}

if ( ! function_exists( 'get_supported_country_name' ) ) {

    /**
     * Returns country name by its code
     *
     * @param string $country_code
     *
     * @return string
     */
    function get_supported_country_name( $country_code ) {
        $return    = 'none';
        $countries = implecode_supported_countries();
        foreach ( $countries as $key => $country ) {
            if ( $country_code == $key ) {
                $return = $country;
            }
        }
        if ( $return == 'none' && array_search( $country_code, $countries ) ) {
            $return = $country_code;
        }

        return $return;
    }

}

function ic_get_products_limit() {
    $multiple_settings = get_multiple_settings();
    if ( ! empty( $multiple_settings['archive_products_limit'] ) ) {
        $limit = $multiple_settings['archive_products_limit'];
    } else {
        $limit = 12;
    }

    return $limit;
}

if ( ! function_exists( 'ic_force_purge_cache' ) ) {
    function ic_force_clear_cache() {
        // LiteSpeed Cache
        if ( defined( 'LSCWP_V' ) ) {
            do_action( 'litespeed_purge_all', '3rd impleCode' );
        }
        // W3 Total Cache
        if ( function_exists( 'w3tc_flush_all' ) ) {
            w3tc_flush_all();
        }
        // WP Super Cache
        if ( function_exists( 'wp_cache_clean_cache' ) ) {
            global $file_prefix;
            wp_cache_clean_cache( $file_prefix );
        }
        // FastCGI Cache
        if ( function_exists( 'nginx_helper_purge_cache' ) ) {
            do_action( 'nginx_helper_purge_all' );
        }
        // WP Rocket
        if ( function_exists( 'rocket_clean_domain' ) ) {
            rocket_clean_domain();
        }
        // Redis Cache
        if ( function_exists( 'wp_cache_flush' ) ) {
            wp_cache_flush();
        }
        // Autoptimize
        if ( class_exists( 'autoptimizeCache' ) ) {
            autoptimizeCache::clearall();
        }
    }
}

<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements shortcode catalog functionality
 *
 * @version        1.1.3
 * @package        ecommerce-product-catalog/
 * @author        impleCode
 */
class ic_shortcode_catalog {

    private $multiple_settings, $status = 'catalog', $title = '', $after_header = 0, $loop_started = 0, $settings = array(), $the_content_filters = array(), $shortcode_listing_generated = 0, $post_type = '';

    function __construct() {
        add_shortcode( 'show_product_catalog', array( $this, 'catalog_shortcode' ) );
        add_action( 'init', array( $this, 'init' ) );
    }

    function init() {
        add_action( 'wp_ajax_ic_assign_listing', array( $this, 'assign_listing' ) );
        if ( ! is_ic_shortcode_integration() ) {
            return;
        }
        $this->settings = ic_get_shortcode_mode_settings();
        add_action( 'ic_catalog_wp', array( $this, 'hooks' ) );

        add_action( 'ic_shortcode_mode_settings_html', array( $this, 'settings_html' ) );
        add_action( 'ic_after_main_catalog_page_setting_html', array( $this, 'main_catalog_page_warning' ) );
        add_filter( 'ic_archive_multiple_settings_validation', array( $this, 'update_template' ) );
        add_action( 'ic_ajax_self_submit_init', array( $this, 'is_ajax_inside_shortcode' ), 10, 3 );
        do_action( 'ic_shortcode_integration_init' );
    }

    function hooks() {
        if ( ! is_ic_catalog_page() || apply_filters( 'ic_shortcode_catalog_apply', false ) ) {
            return;
        }
        $this->get_pre_shortcode_query();
        $this->get_pre_shortcode_post();
        remove_all_actions( 'loop_no_results' );
        add_action( 'loop_no_results', array( $this, 'no_results_show_catalog' ) );

        add_filter( 'the_title', array( $this, 'fix_title' ), 10, 2 );

        add_action( 'ic_catalog_template_redirect', array( $this, 'overwrite_query' ), 999 );
        add_action( 'get_header', array( $this, 'catalog_query' ), 999, 0 );
        add_filter( 'body_class', array( $this, 'body_class' ), 1, 2 );
        add_filter( 'ic_catalog_body_class_start', array( $this, 'overwrite_query' ), 1 );
        ////add_filter( 'ic_catalog_single_body_class', array( $this, 'catalog_query' ), 99, 1 );
        ////add_filter( 'ic_catalog_tax_body_class', array( $this, 'catalog_query' ), 98, 1 );
        add_filter( 'ic_catalog_tax_body_class', array( $this, 'tax_body_class' ), 98, 1 );
        add_filter( 'ic_catalog_single_body_class', array( $this, 'single_body_class' ), 98, 1 );
//add_filter( 'ic_catalog_body_class', array( $this, 'catalog_query' ), 98 );
        //add_filter( 'get_custom_logo', array( $this, 'catalog_query' ), 98 );

        add_filter( 'ic_catalog_pre_nav_menu', array( $this, 'remove_overwrite_filters' ), 99 );
//add_filter( 'ic_catalog_tax_nav_menu', array( $this, 'fake_tax_first_post' ), 99 );
//add_filter( 'ic_catalog_tax_nav_menu', array( $this, 'overwrite_query' ), 99 );
        add_filter( 'ic_catalog_pre_nav_menu_items', array( $this, 'remove_title_override' ) );
        add_filter( 'ic_catalog_pre_nav_menu', array( $this, 'remove_title_override' ), - 2 );
        add_filter( 'ic_catalog_nav_menu_items', array( $this, 'add_title_override' ) );
        add_filter( 'wp_list_pages_excludes', array( $this, 'remove_title_override' ), - 2 );
        add_filter( 'wp_list_pages', array( $this, 'add_title_override' ), 999 );
        add_filter( 'ic_catalog_nav_menu', array( $this, 'add_title_override' ), 99 );
        ////add_filter( 'ic_catalog_single_nav_menu', array( $this, 'catalog_query' ), 99 );
        add_filter( 'breadcrumb_trail_args', array( $this, 'remove_title_override' ) );
        add_filter( 'breadcrumb_trail_args', array( $this, 'catalog_query' ), 99 );
        //add_filter( 'breadcrumb_trail', array( $this, 'overwrite_query' ), 99 );
        add_filter( 'breadcrumb_trail', array( $this, 'add_title_override' ), 99 );


        add_filter( 'ic_catalog_listing_nav_menu', array( $this, 'fake_listing_first_post' ), 99 );
        add_action( 'get_template_part', array( $this, 'overwrite_query' ), 99, 0 );
        add_action( 'wp_after_load_template', array( $this, 'overwrite_query' ), 99, 0 );
        //add_action( 'get_template_part', array( $this, 'break_query' ), 100, 0 ); // Shows no content when multiple loops present
//add_action( 'get_template_part_content', array( $this, 'overwrite_query' ), 101, 0 );
//add_action( 'get_template_part_loop', array( $this, 'overwrite_query' ), 101, 0 );
//add_action( 'get_template_part_index', array( $this, 'overwrite_query' ), 101, 0 );
//( 'get_template_part_page', array( $this, 'overwrite_query' ), 101, 0 );
        //add_filter( 'post_class', array( $this, 'catalog_query' ), -2 );
        add_filter( 'post_class', array( $this, 'overwrite_query' ), - 2 );
        add_filter( 'post_class', array( $this, 'check_post_class' ), 99 );
        //add_filter( 'post_class', array( $this, 'overwrite_query' ), 999 );
        //add_filter( 'post_class', array( $this, 'catalog_query' ), 999 ); //Better to add it only for some themes
        if ( ! empty( $this->settings['show_everywhere'] ) ) {
            add_action( 'ic_catalog_wp_head_start', array( $this, 'catalog_query_force' ), - 1, 0 );
        } else {
            add_action( 'ic_catalog_wp_head_start', array( $this, 'catalog_query' ), - 1, 0 );
        }
        add_action( 'ic_catalog_wp_head', array( $this, 'set_after_header' ), 9999, 0 );
//add_action( 'ic_catalog_listing_wp_head', array( $this, 'overwrite_query' ), 999 );
        add_action( 'ic_catalog_search_wp_head', array( $this, 'overwrite_query' ), 999 );

        add_filter( 'single_post_title', array( $this, 'product_page_title' ), 99, 1 );

        add_action( 'loop_start', array( $this, 'loop_start' ), 10, 1 );
        add_action( 'loop_end', array( $this, 'loop_end' ) );

        add_action( 'shortcode_catalog_init', array( $this, 'catalog_query' ), 10, 0 );
        add_action( 'shortcode_catalog_init', array( $this, 'remove_overwrite_filters' ) );
        add_action( 'product_listing_begin', array( $this, 'remove_overwrite_filters' ) );

        add_action( 'admin_bar_menu', array( $this, 'catalog_query' ), - 1, 0 );
        add_action( 'admin_bar_menu', array( $this, 'overwrite_query' ), 9999, 0 );

        add_action( 'wp_footer', array( $this, 'catalog_query' ), - 1, 0 );
        add_action( 'wp_footer', array( $this, 'overwrite_query' ), 9999, 0 );

        add_action( 'ic_before_widget', array( $this, 'widget_switch_before' ) );
        add_action( 'ic_after_widget', array( $this, 'widget_switch_after' ) );

        add_filter( 'get_post_metadata', array( $this, 'listing_metadata' ), 10, 4 );

        //add_filter( 'the_content', array( $this, 'auto_add_shortcode' ), 99999999 );
        add_filter( 'the_content', array( $this, 'auto_add_shortcode' ), 99 );

        add_filter( 'the_content', array( $this, 'the_content_filter' ), - 999 );

        add_filter( 'next_post_link', array( $this, 'next_previous_post_link' ) );
        add_filter( 'previous_post_link', array( $this, 'next_previous_post_link' ) );

        add_filter( 'comments_open', array( $this, 'disable_comments' ), 10, 2 );

        add_action( 'ic_catalog_set_product_id', array( $this, 'catalog_query' ), - 1, 0 );

        add_filter( 'ic_catalog_force_product_header', array( $this, 'force_product_header' ) );
        add_filter( 'ic_catalog_force_category_header', array( $this, 'force_category_header' ) );
        $this->default_the_content();
        $this->theme_specific();
        do_action( 'ic_shortcode_catalog_hooks_added', $this );
    }

    function is_ajax_inside_shortcode( $ajax_query_vars, $params, $pre_ajax_query_vars ) {
        if ( ! empty( $pre_ajax_query_vars['page_id'] ) ) {
            $listing_id = get_product_listing_id();
            if ( $listing_id == $pre_ajax_query_vars['page_id'] ) {

                return;
            }
            $post = get_post( $pre_ajax_query_vars['page_id'] );
            if ( isset( $post->post_content ) && ic_has_page_catalog_shortcode( $post ) && is_product_filters_active() ) {
                ic_save_global( 'inside_show_catalog_shortcode', 1 );
            }
        }
    }

    function default_the_content() {
        $this->the_content_filters = array();
        $to_remove                 = array( 'basic_social_share_buttons' );
        foreach ( $to_remove as $remove ) {
            if ( ! function_exists( $remove ) ) {
                continue;
            }
            remove_filter( 'the_content', $remove );
            $this->the_content_filters[] = $remove;
        }
        /*
          remove_all_filters( 'the_content' );
          if ( function_exists( 'do_blocks' ) ) {
          add_filter( 'the_content', 'do_blocks', 9 );
          }
          add_filter( 'the_content', 'wptexturize' );
          add_filter( 'the_content', 'convert_smilies', 20 );
          add_filter( 'the_content', 'wpautop' );
          add_filter( 'the_content', 'shortcode_unautop' );
          add_filter( 'the_content', 'prepend_attachment' );
          if ( function_exists( 'wp_filter_content_tags' ) ) {
          add_filter( 'the_content', 'wp_filter_content_tags' );
          }
          if ( function_exists( 'wp_replace_insecure_home_url' ) ) {
          add_filter( 'the_content', 'wp_replace_insecure_home_url' );
          }
          add_filter( 'the_content', 'capital_P_dangit', 11 );
          add_filter( 'the_content', 'do_shortcode', 11 ); // AFTER wpautop().
         *
         */
    }

    function reset_the_content() {
        if ( empty( $this->the_content_filters ) ) {
            return;
        }
        foreach ( $this->the_content_filters as $filter_name ) {
            add_filter( 'the_content', $filter_name );
        }
    }

    function the_content_filter( $return ) {
        if ( $this->status == 'page' ) {
            $this->catalog_query();
            add_filter( 'the_content', array( $this, 'overwrite_query' ), 999 );
        } else {
            remove_filter( 'the_content', array( $this, 'overwrite_query' ), 999 );
        }

        return $return;
    }

    function remove_title_override( $nav_menu ) {
        remove_filter( 'the_title', array( $this, 'fix_title' ), 10, 2 );

        return $nav_menu;
    }

    function add_title_override( $nav_menu ) {
        add_filter( 'the_title', array( $this, 'fix_title' ), 10, 2 );

        return $nav_menu;
    }

    function settings_html( $settings ) {

        if ( ! $this->no_page_id() ) {
            $available_templates = $this->available_templates();
            if ( is_array( $available_templates ) && ! empty( $available_templates ) ) {
                $current_template     = $this->get_true_listing_template();
                $settings['template'] = $current_template;
                implecode_settings_dropdown( __( 'Template', 'ecommerce-product-catalog' ), 'archive_multiple_settings[shortcode_mode][template]', $settings['template'], array_merge( array( '0' => __( 'Default Template', 'ecommerce-product-catalog' ) ), $this->available_templates() ), 1, null, __( 'Choose one of the available page templates to display catalog pages.', 'ecommerce-product-catalog' ) );
            }
        }
    }

    function main_catalog_page_warning() {
        if ( $this->no_page_id() ) {
            $available_templates = $this->available_templates();
            if ( is_array( $available_templates ) && ! empty( $available_templates ) ) {
                echo '<br>';
                implecode_warning( __( "Select an existing page or create new to have an option to choose different page template.", 'ecommerce-product-catalog' ) );
            }
        }
    }

    function force_product_header( $force ) {
        if ( ! empty( $this->settings['force_name'] ) ) {
            $force = true;
        }

        return $force;
    }

    function force_category_header( $force ) {
        if ( ! empty( $this->settings['force_category_name'] ) ) {
            $force = true;
        }

        return $force;
    }

    function set_after_header() {
        $this->after_header = 1;
    }

    function set_loop_started() {
        $this->loop_started = 1;
    }

    function disable_comments( $open, $page_id ) {
        $listing_id = get_product_listing_id();
        if ( $page_id === $listing_id ) {
            return false;
        }

        return $open;
    }

    function next_previous_post_link( $link ) {
        if ( is_ic_product_listing() || is_ic_taxonomy_page() || is_ic_product_search() ) {
            return '';
        }

        return $link;
    }

    function auto_add_shortcode( $content ) {
        if ( is_ic_product_listing() && ! $this->has_listing_shortcode() ) {
            if ( ! $this->is_page_builder_edit() ) {
                $content .= do_shortcode( '[show_product_catalog]' );
            }
        }

        return $content;
    }

    function clear_known( $content ) {
        if ( empty( $this->settings['show_everywhere'] ) ) {
            $content = '';
        }

        if ( empty( $content ) ) {
            return $content;
        }
        add_filter( 'strip_shortcodes_tagnames', array( $this, 'known_shortcodes' ) );
        $content = strip_shortcodes( $content );
        remove_filter( 'strip_shortcodes_tagnames', array( $this, 'known_shortcodes' ) );

        $content = $this->strip_blocks( $content );

        return $content;
    }

    function known_shortcodes() {
        return array( 'show_products', 'show_categories' );
    }

    function strip_blocks( $content ) {
        if ( empty( $content ) ) {
            return $content;
        }
        $block_start  = '<!-- wp:';
        $block_end    = '/-->';
        $known_blocks = $this->known_blocks();
        foreach ( $known_blocks as $block_name ) {
            $start = strpos( $content, $block_start . $block_name );
            if ( $start !== false ) {
                $end = strpos( $content, $block_end, $start );
                if ( $end !== false ) {
                    $strip   = substr( $content, $start, $end + strlen( $block_end ) - $start );
                    $content = trim( str_replace( $strip, '', $content ) );
                }
            }
        }

        return $content;
    }

    function known_blocks() {
        if ( function_exists( 'register_block_type' ) ) {
            return array( 'ic-epc/show-products', 'ic-epc/show-categories' );
        }

        return array();
    }

    function forced_meta() {
        if ( empty( $this->settings['show_everywhere'] ) ) {
            return array();
        } else {
            return array(
                    'et_',
                    '_avia',
                    '_builder',
                    'cs_'
            );
        }
    }

    function included_meta() {
        return array(
                '_wp_page_template',
        );
    }

    function included_meta_contains() {
        return array(
                'sidebar',
                'layout',
        );
    }

    function is_forced_meta( $meta_key ) {
        $forced_meta = $this->forced_meta();
        if ( in_array( $meta_key, $forced_meta ) ) {
            return true;
        } else {
            foreach ( $forced_meta as $part ) {
                if ( ic_string_contains( $meta_key, $part ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    function is_included_meta( $meta_key ) {
        if ( $this->is_forced_meta( $meta_key ) || in_array( $meta_key, $this->included_meta() ) ) {
            return true;
        } else {
            $excluded_parts = $this->included_meta_contains();
            foreach ( $excluded_parts as $part ) {
                if ( ic_string_contains( $meta_key, $part ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    function listing_metadata( $value, $object_id, $meta_key, $single ) {
        if ( empty( $meta_key ) ) {
            return $value;
        }
        $listing_id = get_product_listing_id();
        if ( ! is_ic_admin() && $this->is_included_meta( $meta_key ) && ! $this->no_page_id() && ( is_ic_product( $object_id ) || is_ic_product_category( $object_id ) ) ) {
            remove_filter( 'get_post_metadata', array( $this, 'listing_metadata' ), 10, 4 );
            $listing_meta = get_post_meta( $listing_id, $meta_key, $single );
            if ( ! empty( $listing_meta ) ) {
                $custom_keys = get_post_custom_keys( $object_id );
                if ( is_array( $custom_keys ) && in_array( $meta_key, $custom_keys ) ) {
                    $this_meta = get_post_meta( $object_id, $meta_key, $single );
                    if ( empty( $this_meta ) && $this->is_forced_meta( $meta_key ) ) {
                        unset( $this_meta );
                    }
                }
                if ( ! isset( $this_meta ) ) {
                    if ( $single && is_array( $listing_meta ) ) {
                        $listing_meta = array( $listing_meta );
                    }
                    $value = $listing_meta;
                }
            } else if ( $meta_key === '_wp_page_template' ) {
                $catalog_template = $this->get_template();
                if ( ! empty( $catalog_template ) ) {
                    return $catalog_template;
                }
            }
            add_filter( 'get_post_metadata', array( $this, 'listing_metadata' ), 10, 4 );
        } else if ( $object_id == $listing_id && $meta_key === '_wp_page_template' ) {
            $catalog_template = $this->get_template();
            if ( ! empty( $catalog_template ) || is_numeric( $catalog_template ) ) {
                return $catalog_template;
            }
        } else if ( $meta_key === '_thumbnail_id' && empty( $this->settings['show_everywhere'] ) && $object_id == $listing_id && ! is_ic_product_listing() ) {
            $value = '';
        }

        return $value;
    }

    function get_template() {
        $current_template           = $this->get_true_listing_template();
        $this->settings['template'] = $current_template;

        return apply_filters( 'ic_catalog_shortcode_mode_template', $this->settings['template'] );
    }

    function get_true_listing_template() {
        if ( $this->no_page_id() ) {
            return 0;
        }
        $listing_id = get_product_listing_id();
        remove_filter( 'get_post_metadata', array( $this, 'listing_metadata' ), 10, 4 );
        $current_template = get_post_meta( $listing_id, '_wp_page_template', true );
        add_filter( 'get_post_metadata', array( $this, 'listing_metadata' ), 10, 4 );

        return $current_template;
    }

    function available_templates() {
        $theme = wp_get_theme();
        if ( $theme->exists() ) {
            $available_templates = $theme->get_page_templates( null, 'page' );
        } else {
            $available_templates = array();
        }

        return $available_templates;
    }

    function update_template( $new_settings ) {
        if ( isset( $new_settings['shortcode_mode']['template'] ) ) {
            $available_templates = $this->available_templates();
            if ( $new_settings['shortcode_mode']['template'] === '0' || isset( $available_templates[ $new_settings['shortcode_mode']['template'] ] ) ) {
                $listing_id = get_product_listing_id();
                if ( ! empty( $listing_id ) ) {
                    if ( empty( $new_settings['shortcode_mode']['template'] ) && ! is_numeric( $new_settings['shortcode_mode']['template'] ) ) {
                        delete_post_meta( $listing_id, '_wp_page_template' );
                    } else {
                        update_post_meta( $listing_id, '_wp_page_template', $new_settings['shortcode_mode']['template'] );
                    }
                }
            }
        }

        return $new_settings;
    }

    function theme_specific() {
        if ( function_exists( 'genesis_load_framework' ) || function_exists( 'awada_customizer_config' ) ) {
            remove_filter( 'post_class', array( $this, 'catalog_query' ), 999 );
        }

        //Customizr
        add_action( '__before_content', array( $this, 'overwrite_query' ), 99, 0 );

        //theme.co
        add_action( 'x_get_view', array( $this, 'overwrite_query' ) );

        //Astra
        add_filter( 'astra_dynamic_theme_css', array( $this, 'overwrite_query' ), - 99 );
        add_filter( 'astra_dynamic_theme_css', array( $this, 'catalog_query' ), 99 );

        add_filter( 'astra_breadcrumb_trail_args', array( $this, 'remove_title_override' ) );
        add_filter( 'astra_breadcrumb_trail_args', array( $this, 'catalog_query' ), 99 );
        add_filter( 'astra_breadcrumb_trail', array( $this, 'add_title_override' ), 99 );
        //add_filter( 'astra_get_post_id', 'ic_get_product_id', 99 );
        //DIVI
        if ( function_exists( 'et_setup_theme' ) ) {
            add_action( 'wp', array( $this, 'overwrite_query' ), 998 ); // for the dynamic CSS to work correctly
            add_action( 'wp', array( $this, 'overwrite_query' ), 1 ); // for the custom header template to work
        }
    }

    function fix_title( $title, $id = null ) {
        if ( is_ic_taxonomy_page() || is_ic_product_page() ) {
            if ( ! empty( $this->title ) && ( empty( $id ) || $id == get_product_listing_id() ) ) {
                $title = $this->title;
//$title = '';
            }
        }

        //remove_filter( 'the_title', array( $this, 'fix_title' ), 10, 2 );
        return $title;
    }

    function widget_switch_before() {
        if ( ! is_filter_bar() && ! $this->is_inside_shortcode() ) {
            $this->catalog_query();
        }
    }

    function widget_switch_after() {
        if ( ! is_filter_bar() && ! $this->is_inside_shortcode() ) {
            $this->overwrite_query();
        }
    }

    function loop_start( &$wp_query ) {
        if ( empty( $this->after_header ) ) {
            return $wp_query;
        }
        if ( $this->is_main_query( $wp_query ) ) {
            $this->set_loop_started();
            add_filter( 'the_title', array( $this, 'fix_title' ), 10, 2 );
            remove_action( 'loop_start', array( $this, 'loop_start' ), 10, 1 );
            $this->widget_switch_before();
            $this->breadcrumbs();
        } else if ( empty( $this->settings['show_everywhere'] ) && is_ic_product_listing( $wp_query ) && is_ic_product_page() ) {
            $pre_wp_query = $this->get_pre_shortcode_query();
            foreach ( $wp_query as $name => $val ) {
                if ( isset( $pre_wp_query->$name ) ) {
                    $wp_query->$name = $pre_wp_query->$name;
                }
            }
        }

        return $wp_query;
    }

    function breadcrumbs() {
        if ( ! empty( $this->settings['move_breadcrumbs'] ) ) {
            remove_action( 'single_product_begin', 'add_product_breadcrumbs' );
            remove_action( 'product_listing_begin', 'add_product_breadcrumbs' );
            add_product_breadcrumbs();
        }
    }

    function loop_end( &$wp_query ) {
        if ( empty( $this->after_header ) ) {
            return $wp_query;
        }
        if ( $this->is_main_query( $wp_query ) ) {
            $this->set_loop_started();
        }

        return $wp_query;
    }

    function is_main_query( $query = null ) {
        if ( empty( $query ) ) {
            global $wp_query;
            $query = $wp_query;
        }
        $pre_query = $this->get_pre_shortcode_query();
        if ( $pre_query && $pre_query === $query ) {
            return true;
        } else if ( ! $pre_query && $query->is_main_query() ) {
            return true;
        } else if ( ! empty( $query->ic_main_page_query ) ) {
            return true;
        } else if ( ic_get_global( 'ic_shortcode_new_query' ) === $query ) {
            return true;
        }

        return false;
    }

    function shortcode_init() {
        ic_save_global( 'inside_show_catalog_shortcode', 1 );
        $this->multiple_settings = get_multiple_settings();
        if ( ! is_ic_catalog_page() ) {
            return;
        }
        $this->reset_the_content();
        remove_action( 'loop_no_results', array( $this, 'no_results_show_catalog' ) );
        remove_filter( 'the_title', array( $this, 'title' ), 99, 2 );

        remove_filter( 'the_content', array( $this, 'set_content' ), 99999999 );
        remove_filter( 'the_content', array( $this, 'auto_add_shortcode' ), 99 );
        remove_filter( 'the_content', array( $this, 'catalog_query' ), - 999 );
        remove_filter( 'the_content', array( $this, 'overwrite_query' ), 999 );
        remove_all_filters( 'loop_start' );
        remove_filter( 'the_title', array( $this, 'fix_title' ), 10, 2 );
        add_action( 'before_shortcode_catalog', array( $this, 'setup_postdata' ) );
        add_action( 'before_shortcode_catalog', array( $this, 'setup_loop' ) );
        add_action( 'after_shortcode_catalog', array( $this, 'end_query' ) );
//add_action( 'the_content', array( $this, 'end_query' ) );

        add_action( 'product_page_inside', 'content_product_adder_single_content' );

        remove_filter( 'post_class', array( $this, 'catalog_query' ), - 2 );
        remove_filter( 'post_class', array( $this, 'overwrite_query' ), - 2 );
        remove_filter( 'post_class', array( $this, 'check_post_class' ), 99 );
        remove_filter( 'post_class', array( $this, 'overwrite_query' ), 999 );
        remove_filter( 'post_class', array( $this, 'catalog_query' ), 999 );

        remove_filter( 'get_post_metadata', array( $this, 'listing_metadata' ), 10, 4 );

        $this->catalog_query( '', true );

        ic_enqueue_main_catalog_js_css();
        do_action( 'shortcode_catalog_init' );
        if ( ! is_ic_product_listing() || $this->no_page_id() ) {
            rewind_posts();
        }
    }

    function catalog_shortcode() {
        if ( $this->is_inside_shortcode() ) {
            return;
        }
        $cache_meta = 'ic_catalog_shortcode_output';
        $content    = ic_get_global( $cache_meta );

        if ( $content === false ) {
            $this->shortcode_init();
            ob_start();
            do_action( 'before_shortcode_catalog' );
            $this->shortcode_product_adder();
            do_action( 'after_shortcode_catalog' );
            $content = ob_get_clean();
            ic_save_global( 'inside_show_catalog_shortcode', 0 );
            ic_save_global( $cache_meta, $content );
        }

        return $content;
    }

    function show_catalog_shortcode() {
        echo $this->catalog_shortcode();
    }

    function no_results_show_catalog( $query ) {
        if ( $this->is_main_query( $query ) ) {
            $this->show_catalog_shortcode();
        }
    }

    function is_inside_shortcode() {
        $test = ic_get_global( 'inside_show_catalog_shortcode' );
        if ( ! empty( $test ) ) {
            return true;
        }

        return false;
    }

    function advanced_integration_type() {
        return 'advanced';
    }

    function is_page_builder_edit() {
        if ( ! empty( $_POST['is_fb_preview'] ) || ! empty( $_GET['et_fb'] ) ) {
            // DIVI PAGE BUILDER DETECTED
            return true;
        }
        if ( ! empty( $_GET['elementor-preview'] ) || ( ! empty( $_GET['action'] ) && $_GET['action'] === 'elementor' ) ) {
            // ELEMENTOR PAGE BUILDER DETECTED

            return true;
        }
        if ( ! empty( $_GET['ct_builder'] ) ) {
            // OXYGEN PAGE BUILDER DETECTED
            return true;
        }

        if ( class_exists( 'Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
            // ELEMENTOR PAGE BUILDER DETECTED

            return true;
        }

        return false;
    }

    function default_message() {
        if ( ! current_user_can( 'manage_product_settings' ) || ic_is_rendering_catalog_block() ) {
            return;
        }
        $page_url   = product_listing_url();
        $listing_id = get_product_listing_id();
        $page_id    = get_the_ID();
        $post_type  = get_post_type();
        if ( $this->is_page_builder_edit() ) {
            return;
        }
        if ( $listing_id == $page_id || ic_string_contains( $post_type, 'al_product' ) ) {
            return;
        }
        echo "<style>.ic_spinner{background: url(" . admin_url() . "/images/spinner.gif) no-repeat;display:none;width:20px;height:20px;margin-left:2px;vertical-align:middle;</style>";
        if ( ! empty( $page_url ) ) {
            $message = '<p style="margin-bottom: 5px;font-weight: normal;">' . sprintf( __( 'Currently %sanother page%s is set to show main product listing. Would you like to show product listing here instead?', 'ecommerce-product-catalog' ), '<a href="' . $page_url . '">', '</a>' ) . '</p>';
        } else {
            $message = '<p style="margin-bottom: 5px;font-weight: normal;">' . __( 'Currently, no page is set to show the main product listing. Would you like to show product listing here?', 'ecommerce-product-catalog' ) . '</p>';
        }
        if ( ! empty( $message ) && ! empty( $page_id ) ) {
            ic_enqueue_main_catalog_js_css();
            $message .= '<p style="margin-bottom: 5px;">' . __( 'All your individual product pages will include this page slug as a parent.', 'ecommerce-product-catalog' ) . '</p>';
            $message .= '<button  style="margin-bottom: 5px;" type="button" class="button assign-listing-button ' . design_schemes( 'box', 0 ) . '">' . __( 'Yes', 'ecommerce-product-catalog' ) . '</button><div class="ic_spinner"></div>';
//$message .= '<p style="font-size: 0.8em">* ' . sprintf( __( 'Please remove the %s shortcode to disable this info.', 'ecommerce-product-catalog' ), '[show_product_catalog]' ) . '</p>';
            $message .= '<p style="font-size: 0.8em">* ' . sprintf( __( 'Use the %s or %s shortcode if you just want to display products or categories.', 'ecommerce-product-catalog' ), '<code>[[[show_products]]]</code>', '<code>[[[show_categories]]]</code>' ) . '</p>';
            implecode_info( $message );
            echo "<script>jQuery('.assign-listing-button').click(function() {" . $this->assing_listing_script( $page_id ) . "});</script>";
        }
    }

    function assing_listing_script( $page_id ) {
        return "var data = {
        'action': 'ic_assign_listing',
		'page_id': '" . $page_id . "',
    };
	jQuery('.ic_spinner').css('display', 'inline-block');
	jQuery('.assign-listing-button').prop('disabled', true);
	jQuery.post( product_object.ajaxurl, data, function() {
		window.location.reload(false);
});";
    }

    function assign_listing() {
        if ( ! empty( $_POST['page_id'] ) ) {
            $page_id = intval( $_POST['page_id'] );
            if ( ! empty( $page_id ) && is_ic_shortcode_integration( $page_id ) ) {
                update_option( 'product_archive_page_id', $page_id );
                update_option( 'product_archive', $page_id );
                permalink_options_update();
            }
        }
        wp_die();
    }

    function shortcode_product_adder() {
        $query = $this->get_pre_shortcode_query();
        if ( is_ic_product_listing_enabled() && empty( $query ) ) {
            $listing_id = intval( get_product_listing_id() );
            $id         = 'product_listing';
            if ( ! empty( $listing_id ) && $this->multiple_settings['product_listing_cats'] !== 'forced_cats_only' ) {
                global $wp_query, $paged;
                if ( empty( $paged ) ) {
                    $page = 1;
                } else {
                    $page = $paged;
                }
                $wp_query = $query = new WP_Query( array( 'page_id' => $listing_id, 'paged' => $page ) );
            }
            $this->default_message();
        }
        $listing_status = ic_get_product_listing_status();
        if ( empty( $id ) ) {
            if ( is_archive() || is_search() || is_home_archive() || is_ic_product_listing( $query ) || is_ic_taxonomy_page( $query ) || is_ic_product_search( $query ) ) {
                if ( ! is_ic_product_listing( $query ) || ( is_ic_product_listing( $query ) && ( $listing_status === 'publish' || current_user_can( 'edit_private_products' ) ) ) ) {
                    $id = 'product_listing';
                }
            } else if ( is_ic_product_page() ) {
                $id = 'product_page';
            }
        }
        if ( empty( $id ) ) {
            if ( is_admin() ) {
                echo '<br>';
            }

            return;
        }
        $class_exists = ic_get_global( 'ic_post_class_exists' );
        if ( is_ic_admin() ) {
            $class_exists = true;
        }
        if ( $class_exists === false ) {
            ?>
            <div id="<?php echo $id ?>" <?php post_class() ?>>
            <?php
        }
        //echo '<div class="ic-catalog-container alignwide">';
        echo '<div class="ic-catalog-container">';
        if ( $id === 'product_listing' ) {
            $this->product_listing();
        } else {
            $this->product_page();
        }
        echo '</div>';

        if ( $class_exists === false ) {
            ?>
            </div>
            <?php
        }
    }

    function product_page() {
        do_action( 'before_product_page' );
        $path = $this->get_custom_product_page_path();
        if ( file_exists( $path ) ) {
            ob_start();
            include apply_filters( 'content_product_adder_path', $path );
            $product_page = ob_get_clean();
            echo do_shortcode( $product_page );
        } else {
            include apply_filters( 'content_product_adder_path', AL_BASE_TEMPLATES_PATH . '/templates/full/shortcode-product-page.php' );
        }
        do_action( 'after_product_page' );
    }

    function get_custom_product_page_path() {
        $folder = get_custom_templates_folder();

        return $folder . 'shortcode-product-page.php';
    }

    function product_listing() {
        if ( ! empty( $this->shortcode_listing_generated ) ) {
            return;
        }
        if ( empty( $this->multiple_settings ) ) {
            $this->multiple_settings = get_multiple_settings();
        }
        $archive_template = get_product_listing_template();
        do_action( 'product_listing_begin', $this->multiple_settings );
        do_action( 'before_product_archive' );
        do_action( 'before_product_listing_entry' );

        do_action( 'product_listing_entry_inside', $archive_template, $this->multiple_settings );

        do_action( 'product_listing_end', $archive_template, $this->multiple_settings );
        do_action( 'after_product_archive' );
        $this->shortcode_listing_generated = 1;
    }

    function catalog_query_force( $return = null ) {
        return $this->catalog_query( $return, true );
    }

    function catalog_query( $return = null, $force = false ) {
        if ( is_ic_shortcode_integration() && ( $this->status == 'page' || $force ) && is_ic_catalog_page() ) {
            $pre_query   = $this->get_pre_shortcode_query();
            $check_query = null;
            if ( $pre_query !== false ) {
                $check_query = $pre_query;
            }
            if ( is_ic_product_listing( $check_query ) && ! $this->no_page_id() ) {
                return $return;
            }

            $this->status = 'catalog';
            global $wp_query, $wp_the_query, $post;
            $pre_post = $this->get_pre_shortcode_post();
            if ( empty( $pre_query ) || empty( $pre_post ) ) {
                return;
            }
            $wp_query = $pre_query;
//			if ( !$this->ended_query() ) {
            $wp_the_query = $pre_query;
//			}

            if ( ( is_ic_product_page() || is_ic_taxonomy_page() ) && ( ( ! is_ic_taxonomy_page() && empty( $this->settings['show_everywhere'] ) ) || $force || $this->is_inside_shortcode() ) ) { // added show everywhere check to keep Elementor HTML output
                $post = $pre_post;
            }

            if ( empty( $wp_query->posts ) && is_ic_only_main_cats() ) {
                $listing_id = intval( get_product_listing_id() );
                if ( ! empty( $listing_id ) ) {
                    $post               = $this->listing_post();
                    $wp_query->posts    = array();
                    $wp_query->posts[0] = $post;
                }
                $wp_query->post_count = 1;
                add_action( 'shortcode_catalog_init', array( $this, 'clear_posts' ) );
            }
            if ( is_ic_product_page() ) {
                $wp_query->is_page = true;
            }

            /*
              $wp_query->is_page = true;

              if ( !isset( $wp_query->queried_object->ID ) && $this->no_page_id() && !is_ic_taxonomy_page() ) {
              $args = $this->empty_post_args();
              foreach ( $args as $key => $value ) {
              if ( !isset( $wp_query->queried_object->$key ) ) {
              $wp_query->queried_object->$key = $value;
              }
              }
              $wp_query->queried_object->ancestors = array();
              }
             *
             */
            do_action( 'ic_catalog_shortcode_catalog_query' );
        }

        return $return;
    }

    function fake_tax_first_post( $return = null ) {
        if ( ! is_ic_shortcode_integration() || ( is_ic_product_listing() && ! $this->no_page_id() ) ) {
            return $return;
        }
        global $wp_query;
        if ( ! empty( $wp_query->queried_object->name ) || is_ic_taxonomy_page( $wp_query ) ) {
//add_filter( 'the_title', array( $this, 'fake_post_title' ), 10, 2 );
//add_filter( 'the_title', array( $this, 'title' ), 99, 2 );
        }

        return $return;
    }

    function fake_listing_first_post( $return = null ) {
        if ( ! is_ic_shortcode_integration() || ( is_ic_product_listing() && ! $this->no_page_id() ) ) {
            return $return;
        }
        global $wp_query;
        $listing_id = get_product_listing_id();
        if ( ( ! empty( $wp_query->queried_object->ID ) && $wp_query->queried_object->ID == $listing_id ) || is_ic_product_listing( $wp_query ) ) {
            add_filter( 'the_title', array( $this, 'fake_post_title' ), 10, 2 );
        }

        return $return;
    }

    function fake_post_title( $title, $id = null ) {
        if ( ! empty( $id ) ) {
            global $wp_query;
            $post       = get_post();
            $listing_id = get_product_listing_id();
            if ( is_ic_product_listing( $wp_query ) ) {
                remove_filter( 'the_title', array( $this, 'fake_post_title' ), 10, 2 );
                $title = get_product_listing_title();
            } else if ( ( $post->ID == $id && $listing_id != $id ) || is_ic_taxonomy_page( $wp_query ) ) {
//remove_filter( 'the_title', array( $this, 'fake_post_title' ), 10, 2 );
                if ( ! empty( $wp_query->queried_object->name ) ) {
                    $title = get_product_tax_title( $wp_query->queried_object->name );
                }
            }
        }

        return $title;
    }

    function product_page_title( $title ) {
        if ( is_ic_product_page() ) {
            return get_product_name();
        } else if ( is_ic_taxonomy_page() ) {
            $this->catalog_query();

            return get_product_tax_title( $title );
        }

        return $title;
    }

    function title( $title, $id = null ) {
        $listing_id = get_product_listing_id();
        if ( ! is_admin() && is_ic_catalog_page() && ! is_ic_product_page() && ! in_the_ic_loop() && ! is_filter_bar() && ( empty( $id ) || ( get_quasi_post_type( get_post_type( $id ) ) == 'al_product' ) ) || $listing_id == $id ) {
            if ( is_ic_product_page() ) {
                return get_product_name();
            } else if ( is_ic_taxonomy_page() && ( empty( $id ) || ! is_ic_product( $id ) ) ) {
                $this->catalog_query();

                return get_product_tax_title( $title );
            }
        }

        return $title;
    }

    function remove_overwrite_filters( $content = null ) {
        remove_filter( 'the_title', array( $this, 'fake_post_title' ), 10, 2 );

        return $content;
    }

    function clear_posts() {
        if ( is_ic_only_main_cats() ) {
            global $wp_query;
            if ( $wp_query->post_count == 1 ) {
                $wp_query->post_count = 0;
                $wp_query->posts      = array();
            }
        }
    }

    function end_query() {
        if ( ( is_ic_product_listing() && ! $this->no_page_id() ) || ! is_ic_catalog_page() ) {
            return;
        }
//add_filter( 'the_content', array( $this, 'clear' ) );
        global $wp_query, $wp_the_query, $ic_catalog_shortcode_query_ended;
        if ( is_ic_taxonomy_page() || is_ic_product_search() ) {
            $wp_query     = $this->main_listing_query();
            $wp_the_query = $wp_query;
        }

        if ( ! empty( $wp_query->post ) ) {
            global $post;
            $post = $wp_query->post;
        }

        if ( ! empty( $wp_query->post_count ) ) {
            $wp_query->post_count = 0;
        }
        if ( ! empty( $wp_query->found_posts ) ) {
            $wp_query->found_posts = 0;
        }
        $wp_query->posts = array();
        if ( is_ic_product_listing() && $this->no_page_id() ) {
            $wp_query->is_archive = false;
        }
        $this->status = 'page';

        ic_save_global( 'in_the_loop', 0 );
        remove_filter( 'the_content', array( $this, 'set_content' ), 99999999 );
        remove_filter( 'the_content', array( $this, 'auto_add_shortcode' ), 99 );
        remove_filter( 'the_content', array( $this, 'catalog_query' ), - 999 );
        remove_filter( 'the_content', array( $this, 'overwrite_query' ), 999 );
        remove_filter( 'the_content', array( $this, 'the_content_filter' ), - 999 );
        add_filter( 'get_post_metadata', array( $this, 'listing_metadata' ), 10, 4 );
        //remove_all_actions( 'the_content', 10 );
        //remove_all_filters( 'the_content', 10 );
        $ic_catalog_shortcode_query_ended = 1;
    }

    function ended_query() {
        global $ic_catalog_shortcode_query_ended;
        if ( ! empty( $ic_catalog_shortcode_query_ended ) ) {
            return true;
        }

        return false;
    }

    function main_listing_query() {
        $listing_query = ic_get_global( 'ic_main_listing_query' );
        if ( $listing_query ) {
            return $listing_query;
        }
        $args          = array( 'pagename' => $this->get_listing_slug() );
        $listing_query = new WP_Query( $args );
        ic_save_global( 'ic_main_listing_query', $listing_query );

        return $listing_query;
    }

    function break_query() {
        global $wp_query;
        if ( ! empty( $this->after_header ) && ! empty( $this->loop_started ) && ! empty( $wp_query->query['pagename'] ) ) {
            $wp_query->current_post = 0;
            remove_action( 'get_template_part', array( $this, 'overwrite_query' ), 99, 0 );
        }
    }

    function overwrite_query( $return = null ) {
        if ( $this->status == 'catalog' && is_ic_shortcode_integration() && is_ic_catalog_page() && ( ! is_ic_product_listing() || $this->no_page_id() ) ) {
            $this->set_post_type();
            $this->status = 'page';
            $this->get_pre_shortcode_query();
            $this->get_pre_shortcode_post();
            $this->page_query();
            if ( isset( $return->post ) ) {
                global $wp_query;
                $return = $wp_query;
            }
        }

        return $return;
    }

    function set_post_type() {
        add_filter( 'current_product_post_type', array( $this, 'post_type' ) );
        $current_post_type = get_post_type();
        if ( is_ic_catalog_post_type( $current_post_type ) && empty( $this->post_type ) ) {
            $this->post_type = $current_post_type;
        }
    }

    function post_type( $post_type = null ) {
        if ( ! empty( $this->post_type ) ) {
            $post_type = $this->post_type;
        }

        return $post_type;
    }

    function get_pre_shortcode_query() {
        if ( is_ic_catalog_page() && is_ic_shortcode_integration() ) {
            $pre_query = ic_get_global( 'pre_shortcode_query' );
            if ( $pre_query === false ) {
                do_action( 'shortcode_catalog_query_first_save', $GLOBALS['wp_query'] );
                ic_save_global( 'pre_shortcode_query', $GLOBALS['wp_query'] );
                ic_set_catalog_query();

                return $GLOBALS['wp_query'];
            }

            return $pre_query;
        }

        return false;
    }

    function main_listing_content() {
        $listing_id = intval( get_product_listing_id() );
        if ( ! empty( $listing_id ) ) {
            $post = $this->listing_post();
            if ( isset( $post->post_content ) ) {
                return $post->post_content;
            }
        }

        return '';
    }

    function has_listing_shortcode() {
        return ic_has_listing_shortcode();
    }

    function get_pre_shortcode_post() {
        if ( is_ic_catalog_page() && is_ic_shortcode_integration() ) {
            $pre_post = ic_get_global( 'pre_shortcode_post' );
            if ( ! $pre_post ) {
                if ( ( is_ic_taxonomy_page() || is_ic_product_page() ) && isset( $GLOBALS['post']->post_content ) ) {
                    $content = $this->main_listing_content();
                    if ( ! empty( $content ) ) {
                        //$listing_id = intval( get_product_listing_id() );
                        //if ( !empty( $listing_id ) && ic_has_page_catalog_shortcode( get_post( $listing_id ) ) ) {
                        $GLOBALS['post']->post_content = $content;
                        //}
                    }
                }
                do_action( 'shortcode_catalog_post_first_save', $GLOBALS['post'] );
                ic_save_global( 'pre_shortcode_post', $GLOBALS['post'] );

                return $GLOBALS['post'];
            }

            return $pre_post;
        }

        return false;
    }

    function listing_post() {
        $listing_id = get_product_listing_id();
        if ( $this->no_page_id() ) {
            $listing_post = $this->empty_post();
        } else {
            $listing_post = get_post( $listing_id );
        }
        $pre_post = $this->get_pre_shortcode_query();
        if ( ! is_ic_product_listing( $pre_post ) || $this->no_page_id() ) {
            $listing_post->post_content = $this->clear_known( $listing_post->post_content );
            if ( ! ic_has_page_catalog_shortcode( $listing_post ) ) {
                $listing_post->post_content .= ic_catalog_shortcode();
            }
        }

        return $listing_post;
    }

    function no_page_id() {
        $listing_id = intval( get_product_listing_id() );
        if ( empty( $listing_id ) ) {
            return true;
        }
        $listing_post = get_post( $listing_id );
        if ( empty( $listing_post ) ) {
            return true;
        }

        return false;
    }

    function empty_post_args() {
        $single_names = get_catalog_names();
        $args         = array(
                'ID'                    => 0,
                'post_status'           => 'publish',
                'post_author'           => '',
                'post_parent'           => 0,
                'post_type'             => 'page',
                'post_date'             => '',
                'post_date_gmt'         => '',
                'post_modified'         => '',
                'post_modified_gmt'     => '',
                'post_content'          => '',
                'post_title'            => $single_names['plural'],
                'post_excerpt'          => '',
                'post_content_filtered' => '',
                'post_mime_type'        => '',
                'post_password'         => '',
                'post_name'             => sanitize_title( $single_names['plural'] ),
                'guid'                  => '',
                'menu_order'            => 0,
                'pinged'                => '',
                'to_ping'               => '',
                'ping_status'           => '',
                'comment_status'        => 'closed',
                'comment_count'         => 0,
                'filter'                => 'raw',
        );

        return $args;
    }

    function empty_post() {
        $args = $this->empty_post_args();
        $post = new WP_Post( (object) $args );

        return $post;
    }

    function page_query( $return = null ) {
        if ( ! is_admin() && is_ic_catalog_page() && is_ic_shortcode_integration() ) {
            global $wp_query, $wp_the_query, $post;
            $listing_slug = $this->get_listing_slug();
            if ( ! empty( $wp_query->query['pagename'] ) && $wp_query->query['pagename'] === $listing_slug ) {
                return $return;
            }
            $new_query = ic_get_global( 'ic_shortcode_new_query' );
            $new_post  = ic_get_global( 'ic_shortcode_new_post' );
            if ( $new_query && $new_post ) {
                $wp_query     = $new_query;
                $wp_the_query = $wp_query;
                $post         = $new_post;
                setup_postdata( $post );

                return $return;
            }

//query_posts( $args );
            $wp_query     = $this->main_listing_query();
            $listing_post = $this->listing_post();
            $post         = $listing_post;

//if ( !ic_has_page_catalog_shortcode( $post ) ) {
//$post->post_content .= apply_filters( 'ic_catalog_default_listing_content', '[show_product_catalog]' );
//}
            $pre_post = $this->get_pre_shortcode_query();
//generate_postdata( $listing_post );
            $wp_query->post           = $post;
            $wp_query->queried_object = $post;
            $wp_query->posts          = array( 0 => $post );
//if ( empty( $wp_query->post_count ) ) {
            $wp_query->post_count = 1;
//}
//if ( empty( $wp_query->found_posts ) ) {
            $wp_query->found_posts = 1;
//$wp_query->current_post	 = 0;
//$wp_query->in_the_loop	 = true;
//}
            if ( is_ic_product_search( $pre_post ) ) {
                $search_title = ic_get_search_page_title();
                $this->title  = $search_title;
            } else if ( ! empty( $pre_post->queried_object->labels->name ) ) {
                $this->title = $pre_post->queried_object->labels->name;
            } else if ( is_ic_product_listing( $pre_post ) ) {
                $this->title = $listing_post->post_title;
//$wp_query->post->post_title			 = $listing_post->post_title;
//$wp_query->posts[ 0 ]->post_title	 = $listing_post->post_title;
            } else if ( ! empty( $pre_post->queried_object->name ) ) {
                $tax_title         = get_product_tax_title( $pre_post->queried_object->name );
                $this->title       = $tax_title;
                $post->post_status = 'publish';
            } else if ( ! empty( $pre_post->post->post_title ) ) {
                $this->title       = $pre_post->post->post_title;
                $post->post_status = $pre_post->post->post_status;
            }
            if ( ! empty( $this->title ) ) {
                $post->post_title               = $this->title;
                $wp_query->post->post_title     = $this->title;
                $wp_query->posts[0]->post_title = $this->title;
            }
            global $wp_version;
            if ( version_compare( $wp_version, 6.1, '>=' ) ) {
                $wp_query->ic_main_page_query = 1;
            }
            $wp_the_query = $wp_query;
            ic_save_global( 'ic_shortcode_new_query', $wp_query );
            ic_save_global( 'ic_shortcode_new_post', $post );
            add_filter( 'the_content', array( $this, 'set_content' ), 99999999 );
            setup_postdata( $post );
        }

        return $return;
    }

    function set_content( $content ) {
        if ( empty( $this->loop_started ) ) {
            return $content;
        }
        $listing_id = intval( get_product_listing_id() );
        global $wp_query;
        if ( empty( $listing_id ) || ( ! empty( $wp_query->queried_object->ID ) && $wp_query->queried_object->ID == $listing_id ) || is_ic_product_page() ) {
            if ( is_ic_catalog_page() ) {
                if ( ! $this->has_listing_shortcode() ) {
                    $content = do_shortcode( '[show_product_catalog]' );
                }
            }

            return $content;
        }
        $page  = $this->listing_post();
        $readd = false;
        if ( has_filter( 'the_content', array( $this, 'set_content' ) ) ) {
            remove_filter( 'the_content', array( $this, 'set_content' ), 99999999 );
            $readd = true;
        }
        if ( ! ic_has_page_catalog_shortcode( $page ) ) {
            if ( ! $this->has_listing_shortcode() ) {
                $page->post_content = '';
            } else {
                $page->post_content = $this->clear_known( $page->post_content );
            }
            $page->post_content .= ic_catalog_shortcode();
        }
//$content = apply_filters( 'the_content', $page->post_content );

        if ( function_exists( 'do_blocks' ) ) {
            add_filter( 'ic_catalog_shortcode_default_content', 'do_blocks' );
        }
        add_filter( 'ic_catalog_shortcode_default_content', 'wptexturize' );
        add_filter( 'ic_catalog_shortcode_default_content', 'convert_smilies', 20 );
        add_filter( 'ic_catalog_shortcode_default_content', 'wpautop' );
        add_filter( 'ic_catalog_shortcode_default_content', 'shortcode_unautop' );
        add_filter( 'ic_catalog_shortcode_default_content', 'prepend_attachment' );
        if ( function_exists( 'wp_make_content_images_responsive' ) ) {
            add_filter( 'ic_catalog_shortcode_default_content', 'wp_make_content_images_responsive' );
        }
        add_filter( 'ic_catalog_shortcode_default_content', 'do_shortcode', 11 );
        $content = apply_filters( 'ic_catalog_shortcode_default_content', $page->post_content );
//$content = do_shortcode( '[show_product_catalog]' );
        if ( ! $this->ended_query() ) {
            add_filter( 'the_content', array( $this, 'set_content' ), 99999999 );
        }

        return $content;
    }

    function clear() {
        return '';
    }

    function get_listing_slug() {
        $listing_id = intval( get_product_listing_id() );
        if ( ! empty( $listing_id ) ) {
            $post = get_post( $listing_id );
            if ( ! empty( $post->post_name ) ) {
                return $post->post_name;
            }
        }
        if ( $this->no_page_id() ) {
            return sanitize_title( get_catalog_names( 'plural' ) );
        } else {
            return false;
        }
    }

    function setup_postdata() {
        global $post, $wp_query;
        if ( is_ic_catalog_page() ) {
            if ( is_ic_product_listing() && ! $this->no_page_id() ) {
                return;
            }
            if ( isset( $wp_query->queried_object->ID ) ) {
                $product_id = $wp_query->queried_object->ID;
                ic_set_product_id( $product_id );
                $post = get_post( $product_id );

                if ( empty( $post->post_content ) ) {
                    $post->post_content = ' ';
                }
                setup_postdata( $post );
            }
        } else if ( ! is_ic_page() ) {
            $listing_id = intval( get_product_listing_id() );
            if ( ! empty( $listing_id ) ) {
                $post     = $this->listing_post();
                $wp_query = $this->main_listing_query();
            }
        }
    }

    function setup_loop() {
        ic_save_global( 'in_the_loop', 1 );
    }

    function check_post_class( $class ) {
        ic_save_global( 'ic_post_class_exists', 1 );
        $class[] = 'page';
        $class[] = 'type-page';
        remove_filter( 'ic_catalog_body_class_start', array( $this, 'overwrite_query' ), 1 );
        remove_filter( 'ic_catalog_single_body_class', array( $this, 'catalog_query' ), 99, 1 );
        remove_filter( 'ic_catalog_tax_body_class', array( $this, 'catalog_query' ), 98, 1 );
        remove_filter( 'ic_catalog_tax_body_class', array( $this, 'tax_body_class' ), 98, 1 );
        remove_filter( 'ic_catalog_single_body_class', array( $this, 'single_body_class' ), 98, 1 );

        return $class;
    }

    function body_class( $class, $css_class = '' ) {
        $this->catalog_query();
        remove_filter( 'body_class', array( $this, 'body_class' ), 1, 2 );
        $class = get_body_class( $css_class );
        add_filter( 'body_class', array( $this, 'body_class' ), 1, 2 );
        $this->overwrite_query();

        return $class;
    }

    function tax_body_class( $body_class ) {
        $key = array_search( 'archive', $body_class );
        if ( $key !== false ) {
            unset( $body_class[ $key ] );
        }

        return $body_class;
    }

    function single_body_class( $body_class ) {
        $key = array_search( 'single', $body_class );
        if ( $key !== false ) {
            unset( $body_class[ $key ] );
        }

        return $body_class;
    }

}

global $ic_shortcode_catalog;
$ic_shortcode_catalog = new ic_shortcode_catalog;

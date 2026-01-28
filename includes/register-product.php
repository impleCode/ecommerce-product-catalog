<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Manages product post type
 *
 * Here all product fields are defined.
 *
 * @version        1.1.1
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
class ic_register_product {

    function __construct() {
        add_action( 'register_catalog_styles', array( $this, 'frontend_scripts' ) );
        add_action( 'init', array( $this, 'ic_create_product' ), 4 );

        //add_action( 'init', array( $this, 'register_meta' ) );
        add_action( 'admin_head', array( $this, 'product_icons' ) );


        add_action( 'post_updated', array( $this, 'implecode_save_products_meta' ), 1, 3 );
        add_action( 'transition_post_status', array( $this, 'status_change' ), 10, 3 );
        add_action( 'current_screen', array( $this, 'edit_screen' ) );


        add_filter( 'generate_rewrite_rules', array( $this, 'rewrite_rules' ) );

        add_filter( 'use_block_editor_for_post_type', array( $this, 'can_gutenberg' ), 999, 2 );
        add_filter( 'gutenberg_can_edit_post_type', array( $this, 'can_gutenberg' ), 999, 2 );
        add_filter( 'use_block_editor_for_post', array( $this, 'can_gutenberg' ), 999, 2 );
        add_filter( 'gutenberg_can_edit_post', array( $this, 'can_gutenberg' ), 999, 2 );


        add_action( 'wp_print_scripts', array( $this, 'structured_data' ) );
        if ( defined( 'IC_COMPRESS_PRIVATE_PRODUCTS_DATA' ) && ! empty( IC_COMPRESS_PRIVATE_PRODUCTS_DATA ) ) {
            add_filter( 'get_post_metadata', array( $this, 'hidden_product_data' ), 10, 4 );
        }

        add_action( 'ic_scheduled_hidden_data_processing', array( $this, 'process_hidden_data' ) );

        require_once( AL_BASE_PATH . '/includes/product-categories.php' );
    }

    function edit_screen() {
        if ( is_ic_new_product_screen() || is_ic_edit_product_screen() ) {
            add_action( 'edit_form_after_title', array( $this, 'ic_remove_default_desc_editor' ) );
            add_action( 'edit_form_after_editor', array( $this, 'ic_restore_default_desc_editor' ) );

            add_action( 'admin_menu', array( $this, 'ic_remove_unnecessary_metaboxes' ) );
            add_action( 'admin_head', array( $this, 'ic_remove_unnecessary_metaboxes' ), 999 );

            add_action( 'do_meta_boxes', array( $this, 'change_image_box' ) );

            add_filter( 'post_updated_messages', array( $this, 'set_product_messages' ) );
        }
    }

    function can_gutenberg( $can_edit, $post_type ) {
        $enabled_post_types = product_post_type_array();
        if ( ! in_array( $post_type, $enabled_post_types ) ) {
            return $can_edit;
        }
        if ( apply_filters( 'ic_epc_allow_gutenberg', false ) ) {

            return true;
        }
        if ( isset( $post_type->post_type ) ) {
            $post_type = $post_type->post_type;
        }
        if ( ic_string_contains( $post_type, 'al_product' ) ) {
            return false;
        }

        return $can_edit;
    }


    function register_meta() {
        $post_types = product_post_type_array();
        foreach ( $post_types as $post_type ) {
            register_post_meta(
                    $post_type,
                    '_thumbnail_id',
                    [
                            'auth_callback' => '__return_true',
                            'show_in_rest'  => true,
                            'single'        => true,
                            'type'          => 'number',
                    ]
            );
            do_action( 'ic_register_post_meta', $post_type );
        }

    }

    /**
     * Registers product related front-end scripts
     */
    function frontend_scripts() {
        $dependence = array( 'jquery' );
        wp_register_script( 'ic_magnifier', AL_PLUGIN_BASE_PATH . 'js/magnifier/magnifier.min.js' . ic_filemtime( AL_BASE_PATH . '/js/magnifier/magnifier.min.js' ), array( 'jquery' ), true );
        wp_register_script( 'colorbox', AL_PLUGIN_BASE_PATH . 'js/colorbox/jquery.colorbox.min.js' . ic_filemtime( AL_BASE_PATH . '/js/colorbox/jquery.colorbox.min.js' ), array( 'jquery' ), false, true );
        wp_register_style( 'colorbox', AL_PLUGIN_BASE_PATH . 'js/colorbox/colorbox.css' . ic_filemtime( AL_BASE_PATH . '/js/colorbox/colorbox.css' ) );
        if ( is_ic_product_page() ) {
            if ( is_ic_magnifier_enabled() || ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) ) {
                $dependence[] = 'ic_magnifier';
            }
            if ( ( function_exists( 'is_customize_preview' ) && is_customize_preview() ) || ( is_lightbox_enabled() && is_ic_product_gallery_enabled() ) ) {
                $dependence[] = 'colorbox';
            }
        }
        wp_register_script( 'al_product_scripts', AL_PLUGIN_BASE_PATH . 'js/product.min.js' . ic_filemtime( AL_BASE_PATH . '/js/product.min.js' ), apply_filters( 'al_product_scripts_dependence', $dependence ), false, true );
    }

    /**
     * Registers products post type
     * @global type $wp_version
     */
    function ic_create_product() {
        global $wp_version;
        $page_id = get_product_listing_id();
        $slug    = get_product_slug();
//$listing_status	 = ic_get_product_listing_status();
        if ( is_ic_product_listing_enabled() && ( ( get_integration_type() != 'simple' && ! is_ic_shortcode_integration() ) || ! is_numeric( $page_id ) ) ) {
            $product_listing_t = $slug;
        } else {
            $product_listing_t = false;
//$product_listing_t	 = $slug;
        }
        $names     = get_catalog_names();
        $query_var = $this->get_product_query_var();
        if ( is_plural_form_active() ) {
            $labels = array(
                    'name'                  => $names['plural'],
                    'singular_name'         => $names['singular'],
                    'add_new'               => sprintf( __( 'Add New %s', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) ),
                    'add_new_item'          => sprintf( __( 'Add New %s', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) ),
                    'edit_item'             => sprintf( __( 'Edit %s', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) ),
                    'new_item'              => sprintf( __( 'Add New %s', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) ),
                    'view_item'             => sprintf( __( 'View %s', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) ),
                    'search_items'          => sprintf( __( 'Search %s', 'ecommerce-product-catalog' ), ic_ucfirst( $names['plural'] ) ),
                    'not_found'             => sprintf( __( 'No %s found', 'ecommerce-product-catalog' ), $names['plural'] ),
                    'not_found_in_trash'    => sprintf( __( 'No %s found in trash', 'ecommerce-product-catalog' ), $names['plural'] ),
                    'set_featured_image'    => sprintf( __( 'Set main %s image', 'ecommerce-product-catalog' ), ic_lcfirst( $names['singular'] ) ),
                    'remove_featured_image' => sprintf( __( 'Remove main %s image', 'ecommerce-product-catalog' ), ic_lcfirst( $names['singular'] ) ),
                    'featured_image'        => sprintf( __( '%s Image', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) ),
            );
        } else {
            $labels = array(
                    'name'                  => $names['plural'],
                    'singular_name'         => $names['singular'],
                    'add_new'               => __( 'Add New', 'ecommerce-product-catalog' ),
                    'add_new_item'          => __( 'Add New Item', 'ecommerce-product-catalog' ),
                    'edit_item'             => __( 'Edit Item', 'ecommerce-product-catalog' ),
                    'new_item'              => __( 'Add New Item', 'ecommerce-product-catalog' ),
                    'view_item'             => __( 'View Item', 'ecommerce-product-catalog' ),
                    'search_items'          => __( 'Search Items', 'ecommerce-product-catalog' ),
                    'not_found'             => __( 'Nothing found', 'ecommerce-product-catalog' ),
                    'not_found_in_trash'    => __( 'Nothing found in trash', 'ecommerce-product-catalog' ),
                    'set_featured_image'    => __( 'Set main image', 'ecommerce-product-catalog' ),
                    'remove_featured_image' => __( 'Remove main image', 'ecommerce-product-catalog' ),
                    'featured_image'        => __( 'Image', 'ecommerce-product-catalog' )
            );
        }
        if ( version_compare( $wp_version, 3.8 ) < 0 ) {
            $reg_settings = array(
                    'labels'               => $labels,
                    'public'               => true,
                    'show_in_rest'         => true,
                    'show_in_nav_menus'    => true,
                    'hierarchical'         => false,
                    'has_archive'          => $product_listing_t,
                    'rewrite'              => array(
                            'slug'       => apply_filters( 'product_slug_value_register', $slug ),
                            'with_front' => false
                    ),
                    'query_var'            => $query_var,
                    'supports'             => apply_filters( 'ic_products_type_support', array(
                            'title',
                            'thumbnail',
                            'editor',
                            'excerpt',
                        //'custom-fields'
                    ) ),
                    'register_meta_box_cb' => array( $this, 'add_product_metaboxes' ),
                    'taxonomies'           => array( 'al_product_cat' ),
                    'menu_icon'            => plugins_url() . '/ecommerce-product-catalog/img/product.png',
                    'capability_type'      => 'product',
                    'map_meta_cap'         => true,
                    'menu_position'        => 30,
                /*
                  'capabilities'			 => array(
                  'publish_posts'			 => 'publish_products',
                  'edit_posts'			 => 'edit_products',
                  'edit_others_posts'		 => 'edit_others_products',
                  'edit_published_posts'	 => 'edit_published_products',
                  'edit_private_posts'	 => 'edit_private_products',
                  'delete_posts'			 => 'delete_products',
                  'delete_others_posts'	 => 'delete_others_products',
                  'delete_private_posts'	 => 'delete_private_products',
                  'delete_published_posts' => 'delete_published_products',
                  'read_private_posts'	 => 'read_private_products',
                  'edit_post'				 => 'edit_product',
                  'delete_post'			 => 'delete_product',
                  'read_post'				 => 'read_product',
                  ),
                 *
                 */
                    'exclude_from_search'  => false,
            );
        } else {
            $reg_settings = array(
                    'labels'               => $labels,
                    'public'               => true,
                    'show_in_rest'         => true,
                    'show_in_nav_menus'    => true,
                    'hierarchical'         => false,
                    'has_archive'          => $product_listing_t,
                    'rewrite'              => array(
                            'slug'       => apply_filters( 'product_slug_value_register', $slug ),
                            'with_front' => false,
                            'pages'      => true
                    ),
                    'query_var'            => $query_var,
                    'supports'             => apply_filters( 'ic_products_type_support', array(
                            'title',
                            'thumbnail',
                            'editor',
                            'excerpt',
                        //'custom-fields'
                    ) ),
                    'register_meta_box_cb' => array( $this, 'add_product_metaboxes' ),
                    'taxonomies'           => array( 'al_product-cat' ),
                    'capability_type'      => 'product',
                    'map_meta_cap'         => true,
                    'menu_position'        => 30,
                /*
                                'capabilities'        => array(
                                    'publish_posts'          => 'publish_products',
                                    'edit_posts'             => 'edit_products',
                                    'edit_others_posts'      => 'edit_others_products',
                                    'edit_published_posts'   => 'edit_published_products',
                                    'edit_private_posts'     => 'edit_private_products',
                                    'delete_posts'           => 'delete_products',
                                    'delete_others_posts'    => 'delete_others_products',
                                    'delete_private_posts'   => 'delete_private_products',
                                    'delete_published_posts' => 'delete_published_products',
                                    'read_private_posts'     => 'read_private_products',
                                    'edit_post'              => 'edit_product',
                                    'delete_post'            => 'delete_product',
                                    'read_post'              => 'read_product',
                                ),
                */
                    'exclude_from_search'  => false,
            );
            if ( apply_filters( 'ic_epc_allow_gutenberg', false ) && ! in_array( 'custom-fields', $reg_settings['supports'] ) ) {
                $reg_settings['supports'][] = 'custom-fields';
            }
        }
        register_post_type( 'al_product', $reg_settings );
    }

    function get_product_query_var() {
        $query_var = 'al_product';
        if ( ! is_ic_permalink_product_catalog() ) {
            $names         = get_catalog_names();
            $new_query_var = sanitize_title( ic_strtolower( $names['singular'] ) );
            $new_query_var = ( strpos( $new_query_var, '%' ) !== false ) ? 'product' : $new_query_var;
            $forbidden     = ic_forbidden_query_vars();
            if ( array_search( $new_query_var, $forbidden ) === false ) {
                $query_var = $new_query_var;
            }
        }

        return apply_filters( 'product_query_var', $query_var );
    }

    function product_icons() {
        ?>
        <style>
            <?php if ( isset( $_GET[ 'post_type' ] ) == 'al_product' ) : ?>
            #icon-edit {
                background: transparent url('<?php echo plugins_url() . '/ecommerce-product-catalog/img/product-32.png';
			?>') no-repeat;
            }

            <?php endif; ?>
        </style>
        <?php
    }

    function add_product_metaboxes() {
        $names             = get_catalog_names();
        $names['singular'] = ic_ucfirst( $names['singular'] );
        $labels            = array();
        if ( is_plural_form_active() ) {
            $labels['s_desc']  = sprintf( __( '%s Short Description', 'ecommerce-product-catalog' ), $names['singular'] );
            $labels['desc']    = sprintf( __( '%s description', 'ecommerce-product-catalog' ), $names['singular'] );
            $labels['details'] = sprintf( __( '%s Details', 'ecommerce-product-catalog' ), $names['singular'] );
        } else {
            $labels['s_desc']  = __( 'Short Description', 'ecommerce-product-catalog' );
            $labels['desc']    = __( 'Long Description', 'ecommerce-product-catalog' );
            $labels['details'] = __( 'Details', 'ecommerce-product-catalog' );
        }
        $labels = apply_filters( 'ic_add_meta_box_labels', $labels );
        add_meta_box( 'al_product_short_desc', $labels['s_desc'], array(
                $this,
                'al_product_short_desc'
        ), 'al_product', apply_filters( 'short_desc_box_column', 'normal' ), apply_filters( 'short_desc_box_priority', 'default' ) );
        add_meta_box( 'al_product_desc', $labels['desc'], array(
                $this,
                'al_product_desc'
        ), 'al_product', apply_filters( 'desc_box_column', 'normal' ), apply_filters( 'desc_box_priority', 'default' ) );
        if ( ic_product_details_box_visible() ) {
            add_meta_box( 'al_product_details', $labels['details'], array(
                    $this,
                    'al_product_details'
            ), 'al_product', apply_filters( 'product_details_box_column', 'side' ), apply_filters( 'product_details_box_priority', 'default' ) );
        }
        do_action( 'add_product_metaboxes', $names, $labels );
    }

    function al_product_details() {
        global $post;
        echo '<input type="hidden" name="pricemeta_noncename" id="pricemeta_noncename" value="' .
             wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
        $product_details = '';
        echo apply_filters( 'admin_product_details', $product_details, $post->ID );
    }

    function al_product_short_desc() {
        global $post;
        echo '<input type="hidden" name="shortdescmeta_noncename" id="shortdescmeta_noncename" value="' . wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
        $shortdesc        = get_product_short_description( $post->ID );
        $override_excerpt = apply_filters( 'ic_product_short_desc_input', '' );
        if ( empty( $override_excerpt ) ) {
            $short_desc_settings = array(
                    'media_buttons' => false,
                    'textarea_rows' => 5,
                    'tinymce'       => array(
                            'menubar'            => false,
                            'toolbar1'           => 'bold,italic,underline,blockquote,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo,link,unlink,fullscreen',
                            'toolbar2'           => '',
                            'toolbar3'           => '',
                            'toolbar4'           => '',
                            'add_unload_trigger' => false,
                    )
            );
            if ( apply_filters( 'ic_epc_allow_gutenberg', false ) ) {
                $short_desc_settings['tinymce']['wp_skip_init'] = true;
            }
            wp_editor( $shortdesc, 'excerpt', $short_desc_settings );
        } else {
            echo $override_excerpt;
        }
    }

    function al_product_desc() {
        global $post;
        echo '<input type="hidden" name="descmeta_noncename" id="descmeta_noncename" value="' .
             wp_create_nonce( plugin_basename( __FILE__ ) ) . '" />';
        //$desc			 = get_product_description( $post->ID );
        $desc_settings = array( 'textarea_rows' => 30, 'tinymce' => array( 'add_unload_trigger' => false ) );
        wp_editor( $post->post_content, 'content', $desc_settings );
    }

    function status_change( $new_status, $old_status, $post ) {
        if ( $new_status === $old_status || empty( $post->ID ) || empty( $post->post_type ) ) {
            return;
        }
        $post_type_now = substr( $post->post_type, 0, 10 );
        if ( $post_type_now == 'al_product' ) {
            do_action( 'ic_product_status_change', $new_status, $old_status, $post );
            if ( ! ic_data_should_be_hidden( $post->post_status ) ) {
                $this->hidden_to_public( $post->ID );
                do_action( 'ic_product_status_change_visible', $new_status, $old_status, $post );
            } else {
                $this->public_to_hidden( $post->ID );
                do_action( 'ic_product_status_change_hidden', $new_status, $old_status, $post );
            }
        }
    }

    /**
     * Handles product data save
     *
     * @param type $post_id
     * @param type $post
     *
     * @return type
     */
    function implecode_save_products_meta( $post_id, $post, $post_prev = null ) {
        $hook_existed  = remove_filter( 'get_post_metadata', array( $this, 'hidden_product_data' ) );
        $post_type_now = substr( $post->post_type, 0, 10 );
        if ( $post_type_now == 'al_product' ) {
            $pricemeta_noncename = isset( $_POST['pricemeta_noncename'] ) ? $_POST['pricemeta_noncename'] : '';
            if ( empty( $pricemeta_noncename ) || ( ! empty( $pricemeta_noncename ) && ! wp_verify_nonce( $pricemeta_noncename, plugin_basename( __FILE__ ) ) ) ) {
                return $post->ID;
            }
            if ( ! isset( $_POST['action'] ) ) {
                return $post->ID;
            } else if ( isset( $_POST['action'] ) && $_POST['action'] != 'editpost' ) {
                return $post->ID;
            }
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return $post->ID;
            }
            if ( is_ic_ajax() ) {
                return $post->ID;
            }
            if ( ! current_user_can( 'edit_post', $post->ID ) ) {
                return $post->ID;
            }
            $product_meta = array();
            //$product_meta['excerpt'] = isset( $_POST['excerpt'] ) && ! empty( $_POST['excerpt'] ) ? $_POST['excerpt'] : '';
            //$product_meta['content'] = isset( $_POST['content'] ) && ! empty( $_POST['content'] ) ? $_POST['content'] : '';
            //$product_meta['_product_name'] = isset( $_POST['post_title'] ) && ! empty( $_POST['post_title'] ) ? $_POST['post_title'] : '';

            $product_meta = apply_filters( 'product_meta_save', $product_meta, $post );

            $product_meta = apply_filters( 'ic_product_meta_save_update_data', $product_meta, $post );

            do_action( 'product_meta_save_update', $product_meta, $post );

            $this->save_meta( $post, $product_meta );

            do_action( 'product_edit_save', $post, $product_meta, $post_prev, $this );
        }
        if ( $hook_existed ) {
            add_filter( 'get_post_metadata', array( $this, 'hidden_product_data' ), 10, 4 );
        }
    }

    function save_meta( $post, $product_meta, $value = null ) {
        if ( empty( $post->ID ) ) {
            if ( is_numeric( $post ) ) {
                $post = get_post( $post );
            } else {
                return false;
            }
        }
        if ( ! is_array( $product_meta ) ) {
            if ( $value === null ) {
                return false;
            }
            $product_meta = array( $product_meta => $value );
        }
        $hook_existed = remove_filter( 'get_post_metadata', array( $this, 'hidden_product_data' ) );
        $product_meta = apply_filters( 'product_meta_save_anywhere', $product_meta, $post );
        if ( ! ic_data_should_be_hidden( $post->post_status ) ) {
            if ( ! isset( $_POST['action'] ) || ( isset( $_POST['action'] ) && $_POST['action'] != 'editpost' ) ) {
                $this->hidden_to_public( $post->ID );
            }
            foreach ( $product_meta as $key => $value ) {
                $this->save_single_meta( $post->ID, $key, $value, false );
            }
            delete_post_meta( $post->ID, '_ic_hidden_product_data' );
        } else {
            $this->public_to_hidden( $post->ID, $product_meta );
        }
        if ( $hook_existed ) {
            add_filter( 'get_post_metadata', array( $this, 'hidden_product_data' ), 10, 4 );
        }
    }

    function save_single_meta( $product_id, $key, $value, $check_status = false ) {
        $hook_existed = remove_filter( 'get_post_metadata', array( $this, 'hidden_product_data' ) );
        if ( $check_status ) {
            $post = get_post( $product_id );
            if ( ic_data_should_be_hidden( $post->post_status ) ) {
                return $this->save_single_hidden_meta( $product_id, $key, $value );
            }
        }
        $current_post_keys = get_post_custom_keys( $product_id );
        if ( is_ic_multiple_key( $key ) ) {
            $single = false;
            if ( ! is_array( $value ) ) {
                $value = explode( ',', $value );
            }
        } else {
            $single = true;
        }
        if ( is_array( $current_post_keys ) && in_array( $key, $current_post_keys ) ) {
            $current_value = get_post_meta( $product_id, $key, $single );
        }
        $allow_empty = apply_filters( 'ic_meta_allow_empty', ! empty( $value ) || is_numeric( $value ), $key );
        if ( $allow_empty && ! isset( $current_value ) ) {
            if ( ! $single ) {
                delete_post_meta( $product_id, $key );
                foreach ( $value as $this_value ) {
                    add_post_meta( $product_id, $key, trim( $this_value ), false );
                }
            } else {
                add_post_meta( $product_id, $key, $value, true );
                $this->save_filterable_meta( $product_id, $key, $value );
            }
        } else if ( isset( $current_value ) && $allow_empty && ( ( is_numeric( $value ) && $value != $current_value ) || ( ( ! is_numeric( $value ) && $value !== $current_value ) ) ) ) {
            if ( ! $single ) {
                delete_post_meta( $product_id, $key );
                foreach ( $value as $this_value ) {
                    add_post_meta( $product_id, $key, trim( $this_value ), false );
                }
            } else {
                update_post_meta( $product_id, $key, $value );
                $this->save_filterable_meta( $product_id, $key, $value );
            }
        } else if ( ! $allow_empty && empty( $value ) && ! is_numeric( $value ) && isset( $current_value ) ) {
            delete_post_meta( $product_id, $key );
            delete_post_meta( $product_id, $key . '_filterable' );
        }
        if ( $hook_existed ) {
            add_filter( 'get_post_metadata', array( $this, 'hidden_product_data' ), 10, 4 );
        }
        //unset( $current_value );
    }

    function save_single_hidden_meta( $product_id, $key, $value ) {
        $hidden_data = get_post_meta( $product_id, '_ic_hidden_product_data', true );
        if ( empty( $hidden_data ) ) {
            $hidden_data = array();
        }
        $hidden_data[ $key ] = $value;
        update_post_meta( $product_id, '_ic_hidden_product_data', $hidden_data );
        if ( $key === '_sku' ) {
            $this->save_single_meta( $product_id, $key, $value );
        }
    }

    function save_filterable_meta( $product_id, $key, $value ) {
        if ( ! is_array( $value ) || apply_filters( 'ic_save_meta_avoid_filterable', false, $key ) ) {
            return;
        }
        delete_post_meta( $product_id, $key . '_filterable' );
        foreach ( $value as $val ) {
            if ( is_array( $val ) || ( empty( $val ) && ! is_numeric( $val ) ) ) {
                break;
            }
            add_post_meta( $product_id, $key . '_filterable', $val, false );
        }
    }

    function hidden_to_public( $product_id ) {
        $hidden_data = get_post_meta( $product_id, '_ic_hidden_product_data', true );
        if ( ! empty( $hidden_data ) ) {
            foreach ( $hidden_data as $key => $value ) {
                $this->save_single_meta( $product_id, $key, $value );
            }
            delete_post_meta( $product_id, '_ic_hidden_product_data' );
        }
    }

    function public_to_hidden( $product_id, $product_meta = array() ) {
        if ( empty( $product_meta ) ) {
            $meta_keys = $this->data_keys( $product_id );
            foreach ( $meta_keys as $key ) {
                $meta_value = get_post_meta( $product_id, $key, true );
                if ( ! empty( $meta_value ) || is_numeric( $meta_value ) ) {
                    $product_meta[ $key ] = $meta_value;
                }
            }

        }
        if ( ! isset( $_POST['action'] ) || ( isset( $_POST['action'] ) && $_POST['action'] != 'editpost' ) ) {
            $current_hidden = get_post_meta( $product_id, '_ic_hidden_product_data', true );
            if ( ! empty( $current_hidden ) ) {
                foreach ( $current_hidden as $hidden_key => $hidden_value ) {
                    if ( ! isset( $product_meta[ $hidden_key ] ) ) {
                        $product_meta[ $hidden_key ] = $hidden_value;
                    }
                }
            }
        }
        update_post_meta( $product_id, '_ic_hidden_product_data', $product_meta );
        foreach ( $product_meta as $key => $val ) {
            if ( $key === '_sku' ) {
                continue;
            }
            delete_post_meta( $product_id, $key );
            delete_post_meta( $product_id, $key . '_filterable' );
        }
    }

    function hidden_product_data( $metadata, $object_id, $meta_key = null, $single = false ) {
        if ( ! defined( 'IC_COMPRESS_PRIVATE_PRODUCTS_DATA' ) || ( defined( 'IC_COMPRESS_PRIVATE_PRODUCTS_DATA' ) && empty( IC_COMPRESS_PRIVATE_PRODUCTS_DATA ) ) ) {
            return $metadata;
        }
        if ( ! empty( $object_id ) && empty( $metadata ) && ! is_numeric( $metadata ) && ! empty( $meta_key ) && $meta_key !== '_ic_hidden_product_data' ) {
            /*
            $post_type = get_post_type( $object_id );
            if ( ! in_array( $post_type, product_post_type_array() ) ) {
                return $metadata;
            }
            */

            $hidden_data = get_post_meta( $object_id, '_ic_hidden_product_data', true );
            if ( isset( $hidden_data[ $meta_key ] ) ) {

                return array( $hidden_data[ $meta_key ] );
            }
        } /*else if ( empty( $meta_key ) ) {
			$hidden_data = get_post_meta( $object_id, '_ic_hidden_product_data', $single );
			if ( ! empty( $hidden_data ) ) {
				return $hidden_data;
			}
		} */

        return $metadata;
    }

    function process_hidden_data() {
        $done = get_option( 'ic_product_hidden_data_upgrade_done', 0 );
        if ( empty( $done ) ) {
            update_option( 'ic_product_hidden_data_upgrade_done', - 1, false );
            wp_schedule_single_event( time(), 'ic_scheduled_hidden_data_processing' );

            return $done;
        }

        if ( $done < 0 ) {
            $done = 0;
        }

        $post_statuses    = get_post_statuses();
        $visible_statuses = ic_visible_product_status( false );
        $fetch_status     = array();
        foreach ( $post_statuses as $post_status => $post_status_label ) {
            if ( ! in_array( $post_status, $visible_statuses ) ) {
                $fetch_status[] = $post_status;
            }
        }
        if ( empty( $fetch_status ) ) {
            return;
        }
        $products  = get_all_catalog_products( 'date', 'ASC', 200, $done, apply_filters( 'ic_scheduled_hidden_data_processing_args', array( 'post_status' => $fetch_status ) ) );
        $max_round = 200;
        $rounds    = 1;
        foreach ( $products as $post ) {
            if ( $rounds > $max_round ) {
                break;
            }
            ic_set_time_limit( 30 );
            if ( defined( 'IC_COMPRESS_PRIVATE_PRODUCTS_DATA' ) && IC_COMPRESS_PRIVATE_PRODUCTS_DATA ) {
                $this->public_to_hidden( $post->ID );
            } else {
                $this->hidden_to_public( $post->ID );
            }
            $done ++;
            $rounds ++;
        }
        if ( ! empty( $products ) ) {
            update_option( 'ic_product_hidden_data_upgrade_done', $done, false );
            wp_schedule_single_event( time(), 'ic_scheduled_hidden_data_processing' );
        } else {
            delete_option( 'ic_product_hidden_data_upgrade_done' );
            wp_clear_scheduled_hook( 'ic_scheduled_hidden_data_processing' );
        }

        return $done;
    }

    function data_keys( $product_id ) {
        $current_post_keys = get_post_custom_keys( $product_id );
        if ( empty( $current_post_keys ) ) {
            return array();
        }
        $restricted = $this->restricted_meta();
        foreach ( $current_post_keys as $akey => $key ) {
            if ( in_array( $key, $restricted ) ) {
                unset( $current_post_keys[ $akey ] );
            }
        }

        return $current_post_keys;
    }

    function restricted_meta() {
        return array(
                '_wp_old_slug',
                '_edit_lock',
                '_edit_last'
        );
    }

    /**
     * Disables the default editor screen on product add/edit page
     *
     */
    function ic_remove_default_desc_editor() {
        remove_post_type_support( 'al_product', 'editor' );
    }

    /**
     * Restores editor support
     */
    function ic_restore_default_desc_editor() {
        add_post_type_support( 'al_product', 'editor' );
    }

    /**
     * Removes unnecessary metaboxes for product edit/add screen
     *
     */
    function ic_remove_unnecessary_metaboxes() {
        remove_meta_box( 'postexcerpt', 'al_product', 'normal' );
    }

    function change_image_box() {
        $names = get_catalog_names();
        remove_meta_box( 'postimagediv', 'al_product', 'side' );
        if ( is_plural_form_active() ) {
            $label = sprintf( __( '%s Image', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) );
        } else {
            $label = __( 'Image', 'ecommerce-product-catalog' );
        }
        add_meta_box( 'postimagediv', $label, 'post_thumbnail_meta_box', 'al_product', apply_filters( 'product_image_box_column', 'side' ), apply_filters( 'product_image_box_priority', 'high' ) );
    }

    /*
      function change_thumbnail_html( $content ) {
      if ( is_ic_catalog_admin_page() ) {
      //add_filter( 'admin_post_thumbnail_html', 'modify_add_product_image_label' );
      }
      }

      //add_action( 'admin_head-post-new.php', 'change_thumbnail_html' );
      //add_action( 'admin_head-post.php', 'change_thumbnail_html' );

      function modify_add_product_image_label( $label ) {
      if ( is_plural_form_active() ) {
      $names				 = get_catalog_names();
      $names[ 'singular' ] = ic_strtolower( $names[ 'singular' ] );
      $label				 = str_replace( __( 'Set featured image' ), sprintf( __( 'Set %s image', 'ecommerce-product-catalog' ), $names[ 'singular' ] ), $label );
      $label				 = str_replace( __( 'Remove featured image' ), sprintf( __( 'Remove %s image', 'ecommerce-product-catalog' ), $names[ 'singular' ] ), $label );
      } else {
      $label	 = str_replace( __( 'Set featured image' ), __( 'Set image', 'ecommerce-product-catalog' ), $label );
      $label	 = str_replace( __( 'Remove featured image' ), __( 'Remove image', 'ecommerce-product-catalog' ), $label );
      }
      return $label;
      }
     *
     *
     */

    function set_product_messages( $messages ) {
        global $post, $post_ID;
        $quasi_post_type = get_quasi_post_type();
        $post_type       = get_post_type( $post_ID );
        if ( $quasi_post_type == 'al_product' ) {
            $obj      = get_post_type_object( $post_type );
            $singular = $obj->labels->singular_name;

            $messages[ $post_type ] = array(
                    0  => '',
                    1  => sprintf( __( '%1$s updated. <a href="%2$s">View %1$s</a>', 'ecommerce-product-catalog' ), ic_strtoupper( $singular ), esc_url( get_permalink( $post_ID ) ) ),
                    2  => __( 'Custom field updated.', 'ecommerce-product-catalog' ),
                    3  => __( 'Custom field deleted.', 'ecommerce-product-catalog' ),
                    4  => sprintf( __( '%s updated.', 'ecommerce-product-catalog' ), $singular ),
                    5  => isset( $_GET['revision'] ) ? sprintf( __( '%1$s restored to revision from %2$s', 'ecommerce-product-catalog' ), $singular, wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
                    6  => sprintf( __( $singular . ' published. <a href="%1$s">View %2$s</a>', 'ecommerce-product-catalog' ), esc_url( get_permalink( $post_ID ) ), $singular ),
                    7  => __( 'Page saved.' ),
                    8  => sprintf( __( '%1$s submitted. <a target="_blank" href="%2$s">Preview %1$s</a>', 'ecommerce-product-catalog' ), $singular, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ), strtolower( $singular ) ),
                    9  => sprintf( __( '%3$s scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview %3$s</a>', 'ecommerce-product-catalog' ), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink( $post_ID ) ), $singular ),
                    10 => sprintf( __( '%1$s draft updated. <a target="_blank" href="%2$s">Preview %1$s</a>', 'ecommerce-product-catalog' ), $singular, esc_url( add_query_arg( 'preview', 'true', get_permalink( $post_ID ) ) ) ),
            );
        }

        return $messages;
    }

    /**
     * Rewrite to support pagination on shortcode archive
     *
     * @param type $wp_rewrite
     */
    function rewrite_rules( $wp_rewrite ) {
        if ( is_ic_shortcode_integration() ) {
            $slug       = get_product_slug();
            $listing_id = intval( get_product_listing_id() );
            if ( ! empty( $slug ) && ! empty( $listing_id ) ) {
                $rule           = $slug . '/page/?([0-9]{1,})/?$';
                $rewrite        = 'index.php?page_id=' . $listing_id . '&paged=$matches[1]';
                $rules[ $rule ] = $rewrite;
            }
        }

        if ( ! empty( $rules ) ) {
            $wp_rewrite->rules = $rules + $wp_rewrite->rules;
        }

        return apply_filters( 'ic_cat_urls_rewrite', $wp_rewrite );
    }

    function structured_data() {
        $archive_multiple_settings = get_multiple_settings();
        if ( ! empty( $archive_multiple_settings['enable_structured_data'] ) && is_ic_product_page() ) {
            ic_show_template_file( 'product-page/structured-data.php', AL_BASE_TEMPLATES_PATH );
        }
    }

}

global $ic_register_product;
$ic_register_product = new ic_register_product;

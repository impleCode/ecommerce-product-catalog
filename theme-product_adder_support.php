<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Plugin compatibility checker
 *
 * Here current theme is checked for compatibility with WP PRODUCT ADDER.
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/
 * @author        impleCode
 */
class ic_catalog_notices extends ic_activation_wizard {

    function __construct( $run = false ) {
        if ( $run ) {
            add_action( 'in_admin_header', array( $this, 'notices' ), 9 );

            add_filter( 'plugin_action_links_' . plugin_basename( AL_PLUGIN_MAIN_FILE ), array(
                    $this,
                    'catalog_links'
            ) );

            add_action( 'wp_ajax_hide_review_notice', array( $this, 'ajax_hide_review_notice' ) );
            add_action( 'wp_ajax_hide_ic_notice', array( $this, 'ajax_hide_ic_notice' ) );
            add_action( 'wp_ajax_hide_translate_notice', array( $this, 'ajax_hide_translation_notice' ) );
            add_action( 'wp_ajax_ic_add_catalog_shortcode', array( $this, 'add_catalog_shortcode' ) );

            add_action( 'wp', array( $this, 'remove_catalog_shortcode' ) );


            add_action( 'edit_form_top', array( $this, 'listing_page_info' ) );
            add_action( 'page_attributes_meta_box_template', array( $this, 'listing_template_info' ), 10, 2 );
            add_action( 'edit_form_before_permalink', array( $this, 'listing_slug_info' ) );

            add_action( 'ic_cat_activation_wizard_bottom', array( __CLASS__, 'getting_started_docs_info' ) );

            add_action( 'admin_footer', array( __CLASS__, 'add_catalog_shortcode_script' ) );
        }
    }

    function notices() {
        if ( is_ic_admin_page() ) {
            remove_all_actions( 'admin_notices' );
            add_action( 'admin_notices', array( $this, 'catalog_admin_priority_notices' ), - 2 );
            add_action( 'admin_notices', array( $this, 'catalog_admin_notices' ), 9 );
            add_action( 'ic_catalog_admin_notices', array( $this, 'admin_notices' ) );
            add_action( 'ic_catalog_admin_notices', array( $this, 'woocommerce_notice' ) );
        }
    }

    static function getting_started_docs_info() {
        $getting_started_url = apply_filters( 'ic_cat_getting_started_url', 'https://implecode.com/docs/ecommerce-product-catalog/getting-started/#cam=default-mode&key=getting-started' );
        ?>
        <p class="bottom-container">
            <a rel="noopener" target="_blank" href="<?php echo esc_url( $getting_started_url ) ?>">
                <?php _e( 'Getting started guide with step by step instructions', 'ecommerce-product-catalog' ) ?>
            </a>
        </p>
        <?php
    }

    function listing_page_info( $post ) {
        $listing_id = get_product_listing_id();
        if ( $listing_id == $post->ID ) {
            implecode_info( sprintf( __( 'This page is defined as the main catalog listing. You can change this in %scatalog settings%s.', 'ecommerce-product-catalog' ), '<a href="' . admin_url( 'edit.php?post_type=al_product&page=product-settings.php' ) . '">', '</a>' ) );
            if ( ! is_ic_shortcode_integration() ) {
                echo '<div></div>';
                implecode_info( sprintf( __( 'If you have any problems with your catalog display, please add %s to the page content and save.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() ) );
            }
        }
    }

    function listing_slug_info( $post ) {
        $listing_id = get_product_listing_id();
        if ( $listing_id == $post->ID && ! is_product_listing_home_set() ) {
            implecode_info( __( 'Use the permalink option below to define the parent URL for all catalog pages.', 'ecommerce-product-catalog' ) );
        }
    }

    function listing_template_info( $template, $post ) {
        if ( ! is_ic_shortcode_integration() ) {
            return;
        }
        $listing_id = get_product_listing_id();
        if ( $listing_id == $post->ID ) {
            implecode_info( __( 'Use the dropdown below to define the template for all catalog pages.', 'ecommerce-product-catalog' ) );
        }
    }

    function catalog_admin_priority_notices() {
        if ( is_ic_admin_page() ) {
            do_action( 'ic_catalog_admin_priority_notices', $this );
        }
    }

    function catalog_admin_notices() {
        if ( is_ic_admin_page() ) {
            do_action( 'ic_catalog_admin_notices', $this );
        }
    }

    function admin_notices() {
        if ( current_user_can( 'activate_plugins' ) ) {
            if ( ! is_advanced_mode_forced() || ic_get_product_listing_status() ) {
                $template = get_option( 'template' );
//$integration_type	 = get_integration_type();
                $current_check = $this->theme_support_check();
                if ( ! empty( $_GET['hide_al_product_adder_support_check'] ) ) {
                    $current_check[ $template ] = $template;
                    update_option( 'product_adder_theme_support_check', $current_check );

                    return;
                }
                if ( empty( $current_check[ $template ] ) && current_user_can( 'manage_product_settings' ) ) {
                    $this->theme_check_notice();
                }
            }
            if ( is_ic_catalog_admin_page() ) {
                $product_count = ic_products_count();
                if ( $product_count > 5 ) {
                    if ( false === get_site_transient( 'implecode_hide_plugin_review_info' ) && $this->get_notice_status( 'ic-catalog-review' ) === 0 ) {
                        $this->review_notice();
                        //set_site_transient( 'implecode_hide_plugin_translation_info', 1, WEEK_IN_SECONDS );
                    } else if ( false === get_site_transient( 'implecode_hide_plugin_translation_info' ) && ! is_english_catalog_active() ) {
                        $this->translation_notice();
                    }
                } else if ( false === get_site_transient( 'implecode_hide_plugin_review_info' ) ) {
                    set_site_transient( 'implecode_hide_plugin_review_info', 1, WEEK_IN_SECONDS );
                }
            }
        }
    }

    function theme_check_notice() {
        if ( $this->get_notice_status( 'notice-ic-catalog-welcome' ) || ! is_ic_admin_page() ) {
            return;
        }
        if ( is_integration_mode_selected() && get_integration_type() == 'simple' ) {
            ?>
            <div class="notice notice-updated is-dismissible ic-notice" data-ic_dismissible="notice-ic-catalog-welcome">
                <div class="squeezer">
                    <h4><?php echo sprintf( __( 'You are currently using %s in Simple Mode. It is perfectly fine to use it this way, however some features are limited.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ); ?></h4>
                    <h4><?php echo sprintf( __( 'To switch to Advanced Mode, please add %s to your product listing page.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() ) ?></h4>
                    <h4><?php echo sprintf( __( 'You can also use awesome %sCatalog Me! theme%s.', 'ecommerce-product-catalog' ), '<a href="' . admin_url( 'theme-install.php?search=Catalog%20me' ) . '">', '</a>' ) ?></h4>
                    <p class="submit">
                        <?php /* <a href="https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=top-message" target="_blank" class="button-primary"><?php _e( 'Theme Integration Guide', 'ecommerce-product-catalog' ); ?></a> */ ?>
                        <a class="button-primary add-catalog-shortcode"><?php _e( 'Add Shortcode Now', 'ecommerce-product-catalog' ); ?></a>
                        <a class="skip button"
                           href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=support' ) ?>"><?php _e( 'Plugin Support', 'ecommerce-product-catalog' ); ?></a>
                        <a class="skip button"
                           href="<?php echo esc_url( add_query_arg( 'hide_al_product_adder_support_check', 'true' ) ); ?>"><?php _e( 'I know, don\'t bug me', 'ecommerce-product-catalog' ); ?></a>
                    </p>
                </div>
            </div>
            <div class="clear"></div><?php
        } else if ( is_integration_mode_selected() && get_integration_type() == 'advanced' && ! is_ic_shortcode_integration() ) {

            /* ?>
              <div id="implecode_message" class="updated product-adder-message messages-connect">
              <div class="squeezer">
              <h4><?php _e( 'You are currently using eCommerce Product Catalog in Advanced Mode without the integration file. It is perfectly fine to use it this way, however the file may be very handy if you need more control over product pages. See the guide for quick integration file creation.', 'ecommerce-product-catalog' ); ?></h4>
              <p class="submit"><a href="https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=advanced-mode&key=top-message" target="_blank" class="button-primary"><?php _e( 'Theme Integration Guide', 'ecommerce-product-catalog' ); ?></a> <a class="skip button" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=support' ) ?>"><?php _e( 'Plugin Support', 'ecommerce-product-catalog' ); ?></a> <a class="skip button" href="<?php echo esc_url( add_query_arg( 'hide_al_product_adder_support_check', 'true' ) ); ?>"><?php _e( 'I know, don\'t bug me', 'ecommerce-product-catalog' ); ?></a></p>
              </div>
              </div>

              <div id="implecode_message" class="updated product-adder-message messages-connect">
              <div class="squeezer">
              <h4><?php echo sprintf( __( 'Congratulations! Now your theme is fully integrated with %s.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ); ?></h4>
              <p class="submit"><a href="<?php echo admin_url( 'post-new.php?post_type=al_product' ) ?>" class="button-primary"><?php _e( 'Add Product', 'ecommerce-product-catalog' ); ?></a> <a class="skip button" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php' ) ?>"><?php _e( 'Product Settings', 'ecommerce-product-catalog' ); ?></a> <a href="https://implecode.com/docs/ecommerce-product-catalog/#cam=advanced-mode&key=top-message-docs" class="button"><?php _e( 'Help & Documentation', 'ecommerce-product-catalog' ); ?></a>
              </p>
              </div>
              </div>
             * */

            $template      = get_option( 'template' );
            $current_check = $this->theme_support_check();
            if ( empty( $current_check[ $template ] ) ) {
                $current_check[ $template ] = $template;
                update_option( 'product_adder_theme_support_check', $current_check );
            }
        } else {
            return; // default notice disabled because of activation info
            $product_id         = sample_product_id();
            $sample_product_url = get_permalink( $product_id );
            if ( ! $sample_product_url || get_post_status( $product_id ) != 'publish' ) {
                $sample_product_url = esc_url( add_query_arg( 'create_sample_product_page', 'true' ) );
            }
            ?>
            <div class="notice notice-success is-dismissible ic-notice" data-ic_dismissible="notice-ic-catalog-welcome">
            <div class="squeezer">
                <h4><?php echo sprintf( __( 'Thank you for choosing %1$s! If you have any questions or issues feel free to %2$spost a support ticket%3$s.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME, '<a href="https://implecode.com/support/#cam=simple-mode&key=support-top">', '</a>' ) ?></h4>
                <?php sprintf( __( '%s requires initial configuration in order to work properly &#8211; please click the Initial Configuration button to proceed.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) ?>
                <?php sprintf( __( '<strong>Your theme does not declare %s support</strong> &#8211; please proceed to sample product page where automatic layout adjustment can be made.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) ?>
                <?php sprintf( __( 'You can create your product listing page automatically or insert %s on any of your existing pages.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() ) ?>
                <h4><?php echo sprintf( __( 'If you are looking for a great customizable product theme we recommend free %sCatalog Me! theme%s.', 'ecommerce-product-catalog' ), '<a href="' . admin_url( 'theme-install.php?search=Catalog+me%21' ) . '">', '</a>' ) ?></h4>
                <p class="submit">
                    <?php
                    $listing_status = ic_get_product_listing_status();
                    $label          = '';
                    if ( $listing_status === 'private' ) {
                    ?>
                <h4><?php echo __( 'Your main catalog listing page has been created automatically with private status (it is not visible for visitors).', 'ecommerce-product-catalog' ) ?></h4>
            <?php
            $label = __( 'Publish Main Catalog Listing', 'ecommerce-product-catalog' );
            }
            ?>
                <a class="button-primary"
                   href="https://implecode.com/docs/ecommerce-product-catalog/getting-started/#cam=default-mode&key=getting-started"><?php _e( 'Getting Started Guide', 'ecommerce-product-catalog' ) ?></a>
                <?php
                echo $this->create_listing_page_button( $label, true, 'button-secondary' );
                ?>

                <?php // echo sample_product_button()                                ?>
                <?php /* <a href="https://implecode.com/docs/ecommerce-product-catalog/theme-integration-wizard/#cam=default-mode&key=top-message-video" class="button"><?php _e( 'Configuration Video', 'ecommerce-product-catalog' ); ?></a> */ ?>
                <?php /* <a href="https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=default-mode&key=top-message" target="_blank" class="button"><?php _e( 'Theme Integration Guide', 'ecommerce-product-catalog' ); ?></a> */ ?>
                <?php /* <a class="skip button" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=support' ) ?>"><?php _e( 'Plugin Support', 'ecommerce-product-catalog' ); ?></a> */ ?>
                <?php /* <a class="skip button" href="<?php echo esc_url( add_query_arg( 'hide_al_product_adder_support_check', 'true' ) ); ?>"><?php _e( 'Hide Forever', 'ecommerce-product-catalog' ); ?></a> */ ?>
                </p>
            </div>
            </div><?php
        }
    }

    function woocommerce_notice() {
        if ( $this->get_notice_status( 'notice-ic-woocommerce-compat' ) || ! is_ic_admin_page() ) {
            return;
        }
        if ( $this->show_woocommerce_notice() ) {
            ?>
            <div class="notice notice-info is-dismissible ic-notice" data-ic_dismissible="notice-ic-woocommerce-compat">
                <h4><?php echo sprintf( __( 'Hey! It looks like you have %1$s installed. You can use %2$s with or without %1$s.', 'ecommerce-product-catalog' ), 'WooCommerce', IC_CATALOG_PLUGIN_NAME ) ?><?php echo ' ';
                    _e( 'See the usage scenarios below:', 'ecommerce-product-catalog' ) ?></h4>
                <ul style="list-style: initial;list-style-position: inside;">
                    <li><?php echo sprintf( __( '%1$s and %2$s separately for different product catalogs', 'ecommerce-product-catalog' ), 'WooCommerce', IC_CATALOG_PLUGIN_NAME ) ?>
                        - <?php _e( "doesn't need any specific configuration", 'ecommerce-product-catalog' ) ?></li>
                    <li><?php echo sprintf( __( '%1$s as an alternative to %2$s', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME, 'WooCommerce' ) ?>
                        - <?php echo sprintf( __( 'go to %1$s %3$simport screen%4$s to transfer all %2$s products to %1$s and remove %2$s after that', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME, 'WooCommerce', '<a href="' . admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=csv' ) . '">', '</a>' ) ?></li>
                    <li><?php echo sprintf( __( '%1$s with %2$s design', 'ecommerce-product-catalog' ), 'WooCommerce', IC_CATALOG_PLUGIN_NAME ) ?>
                        - <?php echo sprintf( __( 'install free %1$s plugin from %2$scatalog extensions menu%3$s', 'ecommerce-product-catalog' ), 'WooCommerce Catalog', '<a href="' . admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=product-extensions' ) . '">', '</a>' ) ?></li>
                </ul>
            </div>
            <?php
        }
    }

    static function simple_mode_notice() {
        if ( get_integration_type() !== 'simple' ) {
            return;
        }
        $listing_status = ic_get_product_listing_status();
        if ( $listing_status !== 'publish' ) {
            implecode_warning( sprintf( __( 'Disabled due to a lack of main catalog listing.%s', 'ecommerce-product-catalog' ), ic_catalog_notices::create_listing_page_button() ) );
        } else {
            implecode_warning( sprintf( __( 'Disabled in simple mode. Add %1$s to your %2$smain catalog listing page%3$s to enable it.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name(), '<a href="' . product_listing_url() . '">', '</a>' ) );
        }
    }

    static function create_listing_page_button( $label = null, $listing_button = false, $primary_class = 'button-primary' ) {
        ob_start();
        if ( $listing_button && $primary_class !== 'button-primary' ) {
            self::main_listing_button();
            $listing_button = false;
        }
        $listing_status = ic_get_product_listing_status();
        if ( $listing_status !== 'publish' ) {
            if ( empty( $label ) ) {
                $label = __( 'Create Main Catalog Listing', 'ecommerce-product-catalog' );
            }
            ?>
            <a class="<?php echo $primary_class ?> add-catalog-shortcode"><?php echo $label ?></a>
            <?php
        }
        if ( $listing_button ) {
            self::main_listing_button();
        }

        return ob_get_clean();
    }

    static function add_catalog_shortcode_script() {
        $screen = get_current_screen();
        if ( empty( $screen->id ) || $screen->id !== 'widgets' ) {
            return;
        }
        ?>
        <script>jQuery(".add-catalog-shortcode").on("click", function (event) {
                event.preventDefault();
                var data = {
                    'action': 'ic_add_catalog_shortcode',
                    'nonce': '<?php echo wp_create_nonce( 'ic-ajax-nonce' ) ?>'
                };
                jQuery(this).prop("disabled", true);
                jQuery.post(ajaxurl, data, function (response) {
                    jQuery('<a style="margin-left: 5px;" href="' + response + '" class="button-primary"><?php _e( 'See Your Product Listing', 'ecommerce-product-catalog' ); ?></a>').insertAfter(".add-catalog-shortcode");
                    jQuery(".add-catalog-shortcode").replaceWith("<?php _e( 'The listing has been added successfully!', 'ecommerce-product-catalog' ) ?>");
                    jQuery(".skip.button").remove();
                });
            });</script>
        <?php
    }

    static function main_listing_button() {
        $listing_url = product_listing_url();
        if ( ! empty( $listing_url ) ) {
            if ( ! is_integration_mode_selected() && ! is_ic_shortcode_integration() ) {
                return;
            }
            ?>
            <a href="<?php echo $listing_url ?>"
               class="button-secondary"><?php _e( 'See Main Catalog Listing', 'ecommerce-product-catalog' ); ?></a>
            <?php
        }
    }

    function catalog_links( $links ) {
        if ( function_exists( 'get_admin_url' ) ) {
            $links['extensions'] = '<a href="' . get_admin_url( null, 'edit.php?post_type=al_product&page=extensions.php' ) . '">' . __( 'Add-ons & Integrations', 'ecommerce-product-catalog' ) . '</a>';
            $links['settings']   = '<a href="' . get_admin_url( null, 'edit.php?post_type=al_product&page=product-settings.php' ) . '">' . __( 'Settings', 'ecommerce-product-catalog' ) . '</a>';
        }

        return apply_filters( 'ic_epc_links', array_reverse( $links ) );
    }

    function review_notice() {
        $hidden = get_user_meta( get_current_user_id(), 'ic_review_hidden', true );
        if ( $hidden ) {
            return;
        }
        /* ?>
          <div class="update-nag implecode-review"><strong><?php _e( 'Rate this Plugin!', 'ecommerce-product-catalog' ) ?></strong> <?php echo sprintf( __( 'Please <a target="_blank" href="%s">rate</a> %s and tell me if it works for you or not. It really helps development.', 'ecommerce-product-catalog' ), 'https://wordpress.org/support/view/plugin-reviews/ecommerce-product-catalog#postform', 'eCommerce Product Catalog' ) ?> <span class="dashicons dashicons-no"></span></div> */
        $text = apply_filters( 'ic_review_notice_text', sprintf( __( '%s is free software. Would you mind taking <strong>5 seconds</strong> to <a target="_blank" href="%s">rate the plugin</a> for us, please? Your comments <strong>help others know what to expect</strong> when they install %s.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME, 'https://wordpress.org/support/plugin/' . IC_CATALOG_PLUGIN_SLUG . '/reviews/#new-post', IC_CATALOG_PLUGIN_NAME ) )
        ?>
        <div class="notice notice-warning implecode-review ic-notice is-dismissible"
             data-ic_dismissible="ic-catalog-review"
             data-ic_dismissible_type="temp">
            <p><?php echo $text . ' ' . __( 'A <strong>huge thank you</strong> from impleCode and WordPress community in advance!', 'ecommerce-product-catalog' ) ?></p>
            <p><a target="_blank"
                  href="https://wordpress.org/support/view/plugin-reviews/ecommerce-product-catalog#new-post"
                  class="button-primary ic-user-dismiss"><?php _e( 'Rate Now & Hide Forever', 'ecommerce-product-catalog' ); ?></a>
                <?php /* <a href="" class="button"><?php _e( 'Hide Forever', 'ecommerce-product-catalog' ); ?></a> */ ?>
        </div>
        <div class="update-nag notice notice-warning inline implecode-review-thanks"
             style="display: none"><?php echo sprintf( __( 'Thank you for <a target="_blank" href="%s">your rating</a>! We appreciate your time and input.', 'ecommerce-product-catalog' ), 'https://wordpress.org/support/view/plugin-reviews/ecommerce-product-catalog#new-post' ) ?>
        <span class="dashicons dashicons-yes"></span></div><?php
    }

    function translation_notice() {
        ?>
        <div class="update-nag notice notice-warning inline implecode-translate"><?php echo sprintf( __( "<strong>Psst, it's less than 1 minute</strong> to add some translations to %s collaborative <a target='_blank' href='%s'>translation project</a>.", 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME, 'https://translate.wordpress.org/projects/wp-plugins/ecommerce-product-catalog', IC_CATALOG_PLUGIN_NAME ) ?>
        <span class="dashicons dashicons-no"></span></div><?php
    }

    public static function review_notice_hide( $forever = false ) {
        $user_id = get_current_user_id();
        if ( $forever && ! empty( $user_id ) ) {
//set_site_transient( 'implecode_hide_plugin_review_info', 1, 0 );
            update_user_meta( $user_id, 'ic_review_hidden', 1 );
        } else {
            $count = get_option( 'implecode_hide_plugin_review_info_count', 1 );
            //$count	 = ($count < 6) ? $count : 0;
            set_site_transient( 'implecode_hide_plugin_review_info', 1, WEEK_IN_SECONDS * $count );
            $count += 1;
            update_option( 'implecode_hide_plugin_review_info_count', $count, false );
        }
    }

    function ajax_hide_review_notice() {
        if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ic-ajax-nonce' ) ) {
            $forever = isset( $_POST['forever'] ) ? true : false;
            $this->review_notice_hide( $forever );
        }
        wp_die();
    }

    function translation_notice_hide() {
        set_site_transient( 'implecode_hide_plugin_translation_info', 1 );
    }

    function ajax_hide_translation_notice() {
        if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ic-ajax-nonce' ) ) {
            $this->translation_notice_hide();
        }
        wp_die();
    }

    public static function theme_support_check() {
        $current_check = get_option( 'product_adder_theme_support_check', array() );
        if ( ! is_array( $current_check ) ) {
            $old_current_check                   = $current_check;
            $current_check                       = array();
            $current_check[ $old_current_check ] = $old_current_check;
        }

        return $current_check;
    }

    function add_catalog_shortcode() {
        if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ic-ajax-nonce' ) ) {
            $page_id = get_product_listing_id();
            if ( empty( $page_id ) || $page_id == 'noid' ) {
                $page_id = create_products_page();
            }
            if ( ! empty( $page_id ) ) {
                $post              = get_post( $page_id );
                $post->post_status = 'publish';
                if ( ! is_ic_shortcode_integration( $page_id ) ) {
                    $post->post_content .= ic_catalog_shortcode();
                }
                wp_update_post( $post );
            }
            permalink_options_update();
//create_sample_product();
            echo product_listing_url();
        }
        wp_die();
    }

    function remove_catalog_shortcode() {
        if ( ! empty( $_GET['remove_shortcode_integration'] ) && current_user_can( 'manage_product_settings' ) ) {
            $page_id = get_product_listing_id();
            if ( ! empty( $page_id ) && is_ic_shortcode_integration( $page_id ) ) {
                $post               = get_post( $page_id );
                $post->post_content = str_replace( array(
                        '<!-- wp:ic-epc/show-catalog /-->',
                        '[show_product_catalog]'
                ), '', $post->post_content );
                wp_update_post( $post );
                permalink_options_update();
                wp_redirect( remove_query_arg( 'remove_shortcode_integration' ) );
            }
        }
    }

}

$ic_notices = new ic_catalog_notices( true );

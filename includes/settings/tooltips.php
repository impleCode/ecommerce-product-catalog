<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
/*
 *
 *  @version       1.0.0
 *  @package
 *  @author        impleCode
 *
 */

if ( ! function_exists( 'implecode_enable_wp_tooltips' ) ) {
    add_action( 'admin_enqueue_scripts', 'implecode_enable_wp_tooltips' );

    function implecode_enable_wp_tooltips() {
        if ( ! is_ic_admin_page() ) {
            return;
        }
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_script( 'wp-pointer' );
        //hook the pointer
        add_action( 'admin_print_footer_scripts', 'implecode_show_wp_tooltips' );
    }

}

if ( ! function_exists( 'implecode_show_wp_tooltips' ) ) {

    function implecode_show_wp_tooltips() {
        $tooltips = apply_filters( 'implecode_wp_tooltips', implecode_wp_tooltip_get() );
        if ( empty( $tooltips ) || ! is_array( $tooltips ) ) {
            return;
        }
        foreach ( $tooltips as $key => $tooltip ) {
            foreach ( $tooltip as $key_t => $tool ) {
                $tooltips[ $key ][ $key_t ] = htmlspecialchars( $tool );
            }
        }
        $json_tooltips = json_encode( $tooltips, JSON_HEX_APOS | JSON_HEX_QUOT );
        /*
          $tooltip_content = '';
          foreach ( $tooltips as $tooltip ) {
          $tooltip_content	 .= '<h3>' . $tooltip[ 'title' ] . '</h3>';
          $tooltip_content	 .= '<p>' . $tooltip[ 'text' ] . '</p>';
          $tooltip_selector	 = $tooltip[ 'selector' ];
          break;
          }
          if ( empty( $tooltip_selector ) ) {
          return;
          }
         *
         */
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                    if (jQuery(".ic_cat-activation-wizard").length > 0) {
                        return false;
                    }
                    setTimeout(function () {
                        var tooltips = JSON.parse('<?php echo $json_tooltips ?>');
                        ic_show_next_pointer(tooltips, 1);
                    }, 5000);

                    function ic_show_next_pointer(tooltips, do_not_scroll) {
                        jQuery.each(tooltips, function (index, value) {
                            var selector = value.selector.replace(/&quot;/g, '"');
                            var last_selector = 0;
                            if (selector === '') {
                                if (jQuery(".ic-settings-search .button-secondary").length !== 0) {
                                    selector = 'implecode_settings .ic-settings-search .button-secondary';
                                } else {
                                    selector = 'help';
                                }
                                last_selector = 1;
                            }
                            if (jQuery("#" + selector).length > 0) {
                                var position = 'top';
                                if (jQuery("#al_product_desc").length > 0) {
                                    position = 'bottom';
                                }
                                if (jQuery(".nav-tab-wrapper").find("#" + selector).length > 0) {
                                    position = {'edge': 'left', 'align': 'middle'};
                                }
                                if (selector.includes("implecode_settings")) {
                                    position = {'edge': 'right', 'align': 'left'};
                                }
                                var pointer_selector = jQuery('#' + selector);
                                var original_selector = pointer_selector;
                                /*
								 var selected_selector = jQuery( '#' + selector + ":checked" );
								 if ( selected_selector.length !== 0 ) {
								 pointer_selector = selected_selector;
								 } else {
								 pointer_selector = pointer_selector.first();
								 }
								 */
                                pointer_selector = pointer_selector.first();

                                var selector_label = pointer_selector.closest("tr");
                                if (selector_label.length !== 0) {
                                    pointer_selector = selector_label;
                                }
                                var open_pointer = pointer_selector.first();
                                open_pointer.addClass("ic-pointer-opened");
                                if (do_not_scroll === 0) {
                                    ic_pointer_out_of_screen(open_pointer);
                                }
                                open_pointer.pointer({
                                    pointerClass: "ic_pointer_" + index + " wp-pointer",
                                    content: "<h3>" + value.title + "</h3>" + "<p>" + value.text + "</p>",
                                    /* position: { 'edge': 'top', 'align': 'middle' },*/
                                    position: position,
                                    close: function () {
                                        open_pointer.removeClass("ic-pointer-opened");
                                    },
                                    buttons: function (event, t) {
                                        if (last_selector === 1) {
                                            return ic_pointer_default_buttons(t);
                                        } else {
                                            return ic_pointer_buttons(t, selector);
                                        }
                                    },
                                }).pointer('open');
                                var change_action = 'change';
                                original_selector.on(change_action, function () {
                                    var active_pointer = jQuery(".ic_pointer_" + index);
                                    if (active_pointer.is(":visible")) {
                                        active_pointer.find(".close").click();
                                        ic_hide_pointer(selector);
                                    }
                                });
                                return false;
                            }
                        });
                    }

                    function ic_hide_pointer(selector) {
                        var data = {
                            'action': 'implecode_wp_tooltip_hide',
                            'selector': selector,
                            'nonce': '<?php echo wp_create_nonce( 'ic-ajax-nonce' ) ?>'
                        };
                        jQuery.post(ajaxurl, data, function (response) {
                            if (response !== undefined) {
                                var tooltips = JSON.parse(response);
                                ic_show_next_pointer(tooltips, 0);
                            }
                        });
                    }

                    function ic_dismiss_all_pointers() {
                        var data = {
                            'action': 'implecode_wp_tooltip_dismiss_all',
                            'nonce': '<?php echo wp_create_nonce( 'ic-ajax-nonce' ) ?>'
                        };
                        jQuery.post(ajaxurl, data);
                    }

                    function ic_pointer_out_of_screen(element) {
                        var top_of_element = element.offset().top;
                        var bottom_of_element = top_of_element + element.outerHeight();
                        var bottom_of_screen = jQuery(window).scrollTop() + jQuery(window).innerHeight();
                        var top_of_screen = jQuery(window).scrollTop();

                        if ((bottom_of_screen > top_of_element) && (top_of_screen < bottom_of_element)) {
                            return false;
                        } else {
                            jQuery([document.documentElement, document.body]).animate({
                                scrollTop: top_of_element - 220
                            }, 2000);
                            return true;
                        }
                    }

                    function ic_pointer_buttons(t, selector) {
                        var button1 = jQuery('<a class="close ic-pointer-dismiss" href="#"></a>').text("<?php _e( 'Hide Forever', 'ecommerce-product-catalog' ) ?>");
                        var button2 = jQuery('<a class="button-primary" href="#"></a>').text("<?php _e( 'Next', 'ecommerce-product-catalog' ) ?>");
                        var wrapper = jQuery('<div class=\"wc-pointer-buttons\" />');
                        button1.on('click.pointer', function (e) {
                            e.preventDefault();
                            //if (confirm("<?php _e( 'Are you sure? This will disable all tutorial boxes on all screens.', 'ecommerce-product-catalog' ) ?>") == true) {
                            t.element.pointer('close');
                            ic_dismiss_all_pointers();
                            // }
                        });
                        button2.on('click.pointer', function (e) {
                            e.preventDefault();
                            t.element.pointer('close');
                            ic_hide_pointer(selector);
                        });
                        wrapper.append(button2);
                        wrapper.append(button1);
                        return wrapper;
                    }

                    function ic_pointer_default_buttons(t) {
                        var button = jQuery('<a class="close" href="#"></a>').text(wp.i18n.__('Dismiss'));

                        return button.on('click.pointer', function (e) {
                            e.preventDefault();
                            t.element.pointer('close');
                        });
                    }
                }
            );
        </script>
        <?php
    }

}
if ( ! function_exists( 'implecode_wp_tooltip_hide' ) ) {
    add_action( 'wp_ajax_implecode_wp_tooltip_hide', 'implecode_ajax_wp_tooltip_hide' );

    function implecode_ajax_wp_tooltip_hide() {
        if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ic-ajax-nonce' ) ) {
            $selector = isset( $_POST['selector'] ) ? stripslashes( $_POST['selector'] ) : '';
            implecode_wp_tooltip_hide( $selector );
            $tooltips = implecode_wp_tooltip_get();
            if ( is_array( $tooltips ) ) {
                $tooltips[] = implecode_wp_tooltip_default();
            } else {
                $tooltips = array();
            }
            echo json_encode( $tooltips );
        }
        wp_die();
    }

}

if ( ! function_exists( 'implecode_wp_tooltip_dismiss_all' ) ) {
    add_action( 'wp_ajax_implecode_wp_tooltip_dismiss_all', 'implecode_wp_tooltip_dismiss_all' );

    function implecode_wp_tooltip_dismiss_all() {
        if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ic-ajax-nonce' ) ) {
            update_option( 'implecode_wp_tooltips', 'disabled', false );
        }
        wp_die();
    }

}
if ( ! function_exists( 'implecode_wp_tooltip_hide' ) ) {

    function implecode_wp_tooltip_hide( $selector ) {
        if ( empty( $selector ) ) {
            return;
        }
        $tooltips = implecode_wp_tooltip_get();
        if ( ! is_array( $tooltips ) ) {
            return;
        }
        foreach ( $tooltips as $key => $tooltip ) {
            if ( $tooltip['selector'] === $selector ) {
                unset( $tooltips[ $key ] );
            }
        }
        update_option( 'implecode_wp_tooltips', $tooltips, false );
        implecode_wp_tooltip_hidden_update( $selector );
    }

}

if ( ! function_exists( 'implecode_wp_tooltip_get' ) ) {

    function implecode_wp_tooltip_get() {
        $tooltips = get_option( 'implecode_wp_tooltips', array() );
        if ( ! is_array( $tooltips ) && $tooltips !== 'disabled' ) {
            $tooltips = array();
        }

        return $tooltips;
    }

}

if ( ! function_exists( 'implecode_wp_tooltip_hidden_get' ) ) {

    function implecode_wp_tooltip_hidden_get() {
        $tooltips = get_option( 'implecode_wp_hidden_tooltips', array() );

        return $tooltips;
    }

}

if ( ! function_exists( 'implecode_wp_tooltip_hidden_update' ) ) {

    function implecode_wp_tooltip_hidden_update( $selector ) {
        $hidden_tooltips   = implecode_wp_tooltip_hidden_get();
        $hidden_tooltips[] = $selector;
        update_option( 'implecode_wp_hidden_tooltips', $hidden_tooltips, false );
    }

}

if ( ! function_exists( 'implecode_is_wp_tooltip_hidden' ) ) {

    function implecode_is_wp_tooltip_hidden( $selector ) {
        $tooltips = implecode_wp_tooltip_get();
        if ( $tooltips === 'disabled' ) {
            return true;
        }
        $hidden_tooltips = implecode_wp_tooltip_hidden_get();
        if ( in_array( $selector, $hidden_tooltips ) ) {
            return true;
        }

        return false;
    }

}

if ( ! function_exists( 'implecode_wp_tooltip_exists' ) ) {

    function implecode_wp_tooltip_exists( $selector ) {
        $tooltips = implecode_wp_tooltip_get();
        if ( ! is_array( $tooltips ) ) {
            return false;
        }
        foreach ( $tooltips as $tooltip ) {
            if ( $tooltip['selector'] === $selector ) {
                return true;
            }
        }

        return false;
    }

}

if ( ! function_exists( 'implecode_wp_tooltip_add' ) ) {

    function implecode_wp_tooltip_add( $title, $text, $selector, $on_top = false ) {
        if ( empty( $title ) || empty( $text ) || empty( $selector ) ) {
            return false;
        }
        if ( implecode_wp_tooltip_exists( $selector ) ) {
            return false;
        }

        if ( implecode_is_wp_tooltip_hidden( $selector ) ) {
            return false;
        }

        $tooltips = implecode_wp_tooltip_get();
        if ( ! is_array( $tooltips ) ) {
            $tooltips = array();
        }
        $tooltip = array(
                'title'    => $title,
                'text'     => $text,
                'selector' => $selector
        );
        if ( $on_top ) {
            $tooltips = array_merge( array( $tooltip ), $tooltips );
        } else {
            $tooltips[] = $tooltip;
        }
        update_option( 'implecode_wp_tooltips', $tooltips, false );

        return true;
    }

}

if ( ! function_exists( 'implecode_wp_tooltip_default' ) ) {

    function implecode_wp_tooltip_default() {
        $tooltip = array(
                'title'    => __( 'Screen Tutorial Complete', 'ecommerce-product-catalog' ),
                'text'     => __( 'Congratulations! You finished the tutorial on this screen. Check all the options here and go to another screen to continue.', 'ecommerce-product-catalog' ) . '<br><br>' . sprintf( __( 'If you have any questions or issues, you can reach the developers on the %1$ssupport forum%2$s.', 'ecommerce-product-catalog' ), '<a href="https://wordpress.org/support/plugin/ecommerce-product-catalog/">', '</a>' ),
                'selector' => ''
        );

        return $tooltip;
    }

}


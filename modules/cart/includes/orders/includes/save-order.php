<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Manages digital orders save
 *
 *
 * @version        1.0.0
 * @package        digital-products-orders/includes
 * @author        Norbert Dreszer
 */
class ic_orders_save {

    function __construct() {
        add_action( 'ic_formbuilder_before_mail', array( $this, 'save' ), 10, 5 );
        add_action( 'digital_order_delivery_details', array( $this, 'shipping_summary' ) );
        add_action( 'digital_order_data', array( $this, 'payment_label' ), 10, 2 );
        add_filter( 'ic_checkout_products_table_end', array( $this, 'trans_id' ) );

        add_shortcode( 'customer-orders', array( $this, 'orders_table' ) );

        add_filter( 'customer_panel_content', array( $this, 'orders_panel' ), 10, 2 );
        add_filter( 'customer_panel_tabs', array( $this, 'orders_tab' ) );

        add_filter( 'filter_ic_cart_empty', array( $this, 'replace_cart_content' ), 99 );
        add_filter( 'filter_ic_cart', array( $this, 'replace_cart_content' ), 99 );

        add_action( 'wp_ajax_change_order_name', array( $this, 'rename' ) );

        add_filter( 'ic_formbuilder_admin_email', array( $this, 'url_shortcode' ), 6, 2 );

        add_action( 'payment_status_change', array( $this, 'paid' ), 10, 4 );

        add_action( 'payment_quick_status_change', array( $this, 'quick_paid' ) );

        add_action( 'auto_order_completed', array( $this, 'delete_cron_customer_email' ), 10, 3 );
    }

    function save( $message, $customer_email, $redirect, $contact, $pre_name ) {
        if ( $pre_name == 'cart_' ) {
            global $ic_formbuilder_filled_fields, $ic_orders;
            $payment_details                   = apply_filters( 'ic_save_order_payment_details', $ic_formbuilder_filled_fields );
            $payment_details['status']         = apply_filters( 'ic_default_order_status', 'completed' );
            $payment_details['date']           = current_time( 'timestamp' );
            $payment_details['shipping_email'] = empty( $payment_details['shipping_email'] ) ? $customer_email : $payment_details['shipping_email'];
            $payment_details['email']          = empty( $payment_details['email'] ) ? $customer_email : $payment_details['email'];
            $order                             = get_email_order_details();
            $order_products                    = array(
                    'product_id'           => $order['product_id'],
                    'cart_id'              => isset( $order['cart_id'] ) ? $order['cart_id'] : '',
                    'product_name'         => $order['product'],
                    'product_quantity'     => $order['quantity'],
                    'product_net_price'    => $order['sum'],
                    'product_subtotal_net' => isset( $order['product_total_net'] ) ? $order['product_total_net'] : '',
            );
            if ( isset( $order['product_total'] ) ) {
                $order_products['product_price']   = $order['product_total'];
                $order_products['product_summary'] = $order['product_total'];
            }
            if ( isset( $order['product_gross'] ) ) {
                $order_products['product_gross_price'] = $order['product_gross'];
            }
            if ( isset( $order['variations'] ) ) {
                $order_products['variations'] = $order['variations'];
            }
            $order_summary = array(
                    'price'     => $order['total_taxed'],
                    'total_net' => $order['total_net'],
                    'tax'       => $order['tax'],
                    'handling'  => $order['handling'],
                    'email'     => $customer_email,
            );
            $trans_id      = isset( $_POST['trans_id'] ) ? strval( $_POST['trans_id'] ) : '';
            $order_id      = ic_update_digital_order_status( $payment_details, $order_products, $order_summary, $trans_id );
            ic_save_global( 'ic_save_order_id', $order_id );
            if ( function_exists( 'ic_count_shipping_cost_payment' ) ) {
                $shipping_summary['price'] = ic_count_shipping_cost_payment( 0, 'cart_' );
                if ( ! empty( $order_id ) && ! is_wp_error( $order_id ) && ! empty( $shipping_summary['price'] ) ) {
                    $labels                     = ic_get_order_shipping_labels( 'cart_' );
                    $shipping_summary['labels'] = $labels;
                    update_post_meta( $order_id, '_shipping_summary', $shipping_summary );
                }
            }
            $cart_content = ic_shopping_cart_content( true );
            if ( ! empty( $cart_content ) ) {
                update_post_meta( $order_id, '_cart_content', $cart_content );
            }
            do_action( 'ic_save_order', $order_id, $pre_name, $payment_details, $order_products, $order_summary, $trans_id, $cart_content );
        }
    }

    function shipping_summary( $order_id ) {
        $shipping_summary = get_post_meta( $order_id, '_shipping_summary', true );
        if ( ! empty( $shipping_summary ) && ! empty( $shipping_summary['price'] ) && ! empty( $shipping_summary['labels'] ) ) {
            ?>
            <tr>
                <td><?php echo __( 'Shipping', 'ecommerce-product-catalog' ) . ' (' . $shipping_summary['labels'] . ')' ?></td>
                <td><?php echo price_format( $shipping_summary['price'] ) ?></td>
            </tr>
            <?php
        }
    }

    function payment_label( $order_id, $payment_details ) {
        if ( ! empty( $payment_details['payment_label'] ) ) {
            ?>
            <tr>
                <td><?php echo __( 'Payment Gateway', 'ecommerce-product-catalog' ) ?>
                    : <?php echo esc_attr( $payment_details['payment_label'] ) ?></td>
            </tr>
            <?php
        }
        if ( ! empty( $payment_details['payment_id'] ) ) {
            ?>
            <tr>
                <td><?php echo __( 'Payment ID', 'implecode-shopping-cart' ) ?>
                    : <?php echo esc_attr( $payment_details['payment_id'] ) ?></td>
            </tr>
            <?php
        }

    }

    function trans_id( $content ) {
        $time    = current_time( 'timestamp' );
        $rand    = rand( 1, 9999 );
        $content .= '<input type="hidden" value="' . $time . $rand . '" name="trans_id">';

        return $content;
    }

    function orders_table( $customer_id = null ) {
        if ( empty( $customer_id ) && function_exists( 'ic_get_logged_customer_id' ) ) {
            $customer_id = ic_get_logged_customer_id();
        }
        if ( empty( $customer_id ) ) {
            $pre_panel = ic_customer_panel_actions();
            $pre_panel .= ic_digital_customer_login_form( true, 'login_form panel_login' );

            return $pre_panel;
        }
        $trans_ids = array_filter( ic_customer_transaction_ids( $customer_id ) );
        $table     = apply_filters( 'before_customer_panel_orders_table', '', $customer_id );
        if ( ! empty( $trans_ids ) ) {
            $table       .= '<div id="customer_orders_table" class="table">';
            $table       .= '<div class="table-row">';
            $table_heads = $this->table_heads();
            foreach ( $table_heads as $head ) {
                $table .= '<div class="table-head">' . $head . '</div>';
            }
            $table .= '</div>';
            $table .= apply_filters( 'before_customer_panel_orders', '', $customer_id );
            foreach ( $trans_ids as $trans_id ) {
                $order_customer_id = get_post_meta( $trans_id, '_customer_id', true );
                if ( empty( $order_customer_id ) || intval( $order_customer_id ) !== intval( $customer_id ) ) {
                    continue;
                }
                $table       .= '<div class="table-row" data-order_id="' . $trans_id . '">';
                $table_cells = $this->table_cells( $trans_id );
                foreach ( $table_cells as $cell ) {
                    $table .= '<div class="table-cell">' . $cell . '</div>';
                }
                $table .= '</div>';
            }
            $table .= '</div>';
        } else {
            $table .= __( "You don't have any completed orders yet.", 'ecommerce-product-catalog' );
        }

        return $table;
    }

    function orders_tab( $tabs ) {
        $tabs .= '<li><a href="#customer_panel_tabs-orders">' . __( 'Orders', 'ecommerce-product-catalog' ) . '</a></li>';

        return $tabs;
    }

    function orders_panel( $panel, $customer_id ) {
        $panel .= '<div id="customer_panel_tabs-orders">';
        $panel .= $this->orders_table( $customer_id );
        $panel .= '</div>';

        return $panel;
    }

    function table_cells( $order_id ) {
        $cart_content = $this->cart_content( $order_id );
        $name         = $this->name( $order_id );
        $cells        = array();
        if ( ! empty( $name ) ) {
            $cells[] = '<span class="dashicons dashicons-edit"></span>' . '<span class="order-name">' . $name . '</span>';
        }
        if ( ! empty( $cells ) && ! empty( $cart_content ) ) {
            $cells[] = $this->replace_cart_content_button( $cart_content, $order_id );
        }

        return apply_filters( 'customer_orders_table_cells', $cells, $order_id );
    }

    function replace_cart_content_button( $new_cart_content, $order_id ) {
        if ( ! empty( $new_cart_content ) ) {
            $shopping_cart_settings = get_shopping_cart_settings();
            if ( ! isset( $shopping_cart_settings['url_button'] ) || $shopping_cart_settings['url_button'] != 1 ) {
                $button_class = 'button';
            } else {
                $button_class = 'link';
            }
            $button_class .= ' ' . design_schemes( 'box', 0 );
            $form         = '<form action="' . ic_shopping_cart_page_url() . '" method="post">';
            $form         .= '<input type="hidden" name="replace_cart_content" value=\'' . $new_cart_content . '\' >';
            $form         .= '<input type="hidden" name="cart_content" value=\'' . $new_cart_content . '\' >';
            $form         .= '<button type="submit" class="' . $button_class . '">' . __( 'Add to Cart', 'ecommerce-product-catalog' ) . ' (' . __( 'Order ID', 'ecommerce-product-catalog' ) . ': ' . $order_id . ')</button>';
            $form         .= '</form>';
        }

        return $form;
    }

    function replace_cart_content( $cart_content ) {
        if ( ! empty( $_POST['replace_cart_content'] ) ) {
            $cart_content = ic_decode_json_cart( stripslashes( $_POST['replace_cart_content'] ) );
        }

        return $cart_content;
    }

    function table_heads() {
        return apply_filters( 'customer_orders_table_heads', array(
                __( 'Your Orders', 'ecommerce-product-catalog' ),
                ''
        ) );
    }

    function cart_content( $order_id ) {
        $cart_content = get_post_meta( $order_id, '_cart_content', true );

        return $cart_content;
    }

    function name( $order_id ) {
        $payment_details = ic_get_order_payment_details( $order_id );
        if ( ! empty( $payment_details['order_name'] ) ) {
            $order_name = $payment_details['order_name'];
        } else if ( is_numeric( $payment_details['date'] ) ) {
            $order_name = date_i18n( get_option( 'date_format' ), $payment_details['date'] );
        } else {
            $order_name = $payment_details['date'];
        }

        return $order_name;
    }

    function rename() {
        if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ic_ajax' ) && ! empty( $_POST['order_id'] ) && ! empty( $_POST['new_name'] ) && is_ic_digital_customer() ) {
            $customer_id              = ic_get_logged_customer_id();
            $customer_transaction_ids = ic_customer_transaction_ids( $customer_id );
            if ( ! empty( $customer_transaction_ids ) ) {
                $order_id = intval( $_POST['order_id'] );
                if ( in_array( $order_id, $customer_transaction_ids ) ) {
                    $payment_details               = get_post_meta( $order_id, '_payment_details', true );
                    $payment_details['order_name'] = sanitize_text_field( $_POST['new_name'] );
                    update_post_meta( $order_id, '_payment_details', $payment_details );
                    echo 'done';
                }
            }
        }
        wp_die();
    }

    function url_shortcode( $message, $pre_name ) {
        if ( $pre_name == 'cart_' ) {
            $order_id = ic_get_global( 'ic_save_order_id' );
            if ( ! empty( $order_id ) ) {
                $message = str_replace( '[edit_order_url]', ic_email_button( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) ) . __( 'See Order', 'ecommerce-product-catalog' ) . '</a>', $message );
            } else {
                $message = str_replace( '[edit_order_url]', '', $message );
            }
        }

        return $message;
    }

    function paid( $payment_details, $order_products, $order_summary, $trans_id ) {
        global $verify_payment;
        $verify_payment['payment_details'] = $payment_details;
        $verify_payment['order_products']  = $order_products;
        $verify_payment['order_summary']   = $order_summary;
        do_action( 'ic_verify_payment_start', $verify_payment );
        if ( ic_verify_payment_price( $order_products['product_id'], $order_summary['price'], $payment_details['currency'], $order_products, $trans_id ) ) {
            $payment_details['status'] = 'confirmed';
            if ( ic_digital_order_status( $trans_id ) != $payment_details['status'] ) {
                do_action( 'ic_verify_payment_paid', $payment_details, $order_products, $order_summary, $trans_id );
                ic_update_digital_order_status( $payment_details, $order_products, $order_summary, $trans_id, true );
            }
        }
        do_action( 'ic_verify_payment_end', $verify_payment );
    }

    function quick_paid( $trans_id ) {
        $payment_details['status'] = 'confirmed';
        ic_update_digital_order_status( $payment_details, '', '', $trans_id, true );
    }

    function delete_cron_customer_email( $order_id, $payment_details, $order_products ) {
        $this->delete_order_email_cron( $payment_details['shipping_email'] );
    }

    function delete_order_email_cron( $email ) {
        $crons = _get_cron_array();
        if ( empty( $crons ) ) {
            return;
        }
        foreach ( $crons as $timestamp => $cron ) {
            if ( isset( $cron['send_cron_email_order'] ) ) {
                foreach ( $cron['send_cron_email_order'] as $hash => $current_cron ) {
                    if ( $current_cron['args'][0] == $email ) {
                        unset( $crons[ $timestamp ]['send_cron_email_order'][ $hash ] );
                        if ( empty( $crons[ $timestamp ]['send_cron_email_order'] ) ) {
                            unset( $crons[ $timestamp ]['send_cron_email_order'] );
                        }
                    }
                }
            }
        }
        _set_cron_array( $crons );
    }

}

$ic_orders_save = new ic_orders_save;

if ( ! function_exists( 'get_email_order_details' ) ) {

    function get_email_order_details() {
        $order = apply_filters( 'payment_order_details', array() );

        return $order;
    }

}

function ic_order_completed_status_trigger() {
    return apply_filters( 'order_completed_status_trigger', 'completed' );
}

<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Defines pluggable functions
 *
 * @version        1.0.0
 * @package        product-gallery-advanced/functions
 * @author        Norbert Dreszer
 */
if ( ! function_exists( 'get_all_products' ) ) {

    /**
     * Returns array of all products objects
     * @return array
     */
    function get_all_products( $args = null ) {
        $args['post_type']      = product_post_type_array();
        $args['post_status']    = 'publish';
        $args['posts_per_page'] = - 1;
        $digital_products       = get_posts( $args );

        return $digital_products;
    }

}

if ( ! function_exists( 'ic_htmlize_email' ) ) {

    /**
     * Initializes HTML email template
     *
     * @param string $message
     * @param string $title
     * @param string $sender_name
     *
     * @return string
     * @global string $ic_mail_content
     */
    function ic_htmlize_email( $message, $title, $sender_name ) {
        ob_start();
        ?>
        <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            <title><?php echo $title ?></title>
            <style type="text/css">li {
                    font-size: 16px;
                    line-height: 1.5em;
                    font-weight: bold
                }

                ul {
                    width: 80%;
                    padding-left: 30%
                }

                p {
                    font-size: 16px;
                }

                <?php echo apply_filters( 'ic_catalog_notification_styling', '' ) ?>
            </style>
        </head>
        <body style="">

        <div
                style="font-family: Verdana, sans-serif;color:#555555;font-size:16px;line-height:20px;background:#f5f5f5;width:100%;padding:25px 0 25px 0;margin:0;">
            <div style="width:598px;max-width:100%;padding:0 0 10px 0;margin:0 auto 0 auto;">
                <?php echo apply_filters( 'ic_catalog_notification_before_border_div', '' ) ?>
                <div
                        style="<?php echo apply_filters( 'ic_catalog_notification_border_div_styling', 'background:#ffffff;width: 100%;border: 1px solid #cdcdcd;clear:both;' ) ?>">
                    <?php echo apply_filters( 'ic_catalog_notification_before_top_div', '' ) ?>
                    <div
                            style="<?php echo apply_filters( 'ic_catalog_notification_top_div_styling', 'height:30px;clear:both;float:none;' ) ?>">
                        <?php echo apply_filters( 'ic_catalog_notification_top_div_content', ' ' ) ?>
                    </div>
                    <div style="padding:15px 20px 20px 20px;margin: 0;font-size:16px;text-align:left;line-height:1.5em">
                        <?php echo str_replace( '<br>', '<br>' . "\n", $message ) ?>
                    </div>
                    <div style="height:30px;clear:both;float:none;"></div>
                </div>
            </div>
            <?php
            if ( ! is_email( $sender_name ) ) {
                ?>
                <div style="text-align:center;line-height:1.5em;padding:5px;font-size:12px;color:#696969;width:100%;">
                    <?php echo sprintf( __( 'This email is a service from %s.', 'implecode-shopping-cart' ), '<a href="' . site_url() . '" style="color:#696969">' . $sender_name . '</a>' ) ?>
                </div>
                <?php
            }
            ?>
        </div>
        </body>
        </html>
        <?php
        $htmlized = ob_get_clean();
        global $ic_mail_content;
        $ic_mail_content = $message;

        return $htmlized;
    }

}

if ( ! function_exists( 'ic_mail_alternate' ) ) {
    //add_filter( 'phpmailer_init', 'ic_mail_alternate' );

    /**
     * Adds text email as alternative to the HTML
     *
     * @param object $mailer
     *
     * @return object
     * @global string $ic_mail_content
     */
    function ic_mail_alternate( $mailer ) {
        global $ic_mail_content;
        if ( isset( $ic_mail_content ) && ! empty( $ic_mail_content ) ) {
            $table_tag = ic_email_table();
            if ( strpos( $ic_mail_content, $table_tag ) !== false ) {
                preg_match_all( "/<table[^>]*>(.*?)<\/table>/s", $ic_mail_content, $matches );
                $tables = $matches[1];
                foreach ( $tables as $table_count => $table ) {
                    if ( $table_count === 0 ) {
                        preg_match_all( "/<tr[^>]*>(.*?)<\/tr>/s", $table, $matches );
                        $rows = $matches[1];
                        preg_match_all( "/<td[^>]*>(.*?)<\/td>/s", $rows[0], $matches );
                        $th      = $matches[1];
                        $replace = '';
                        foreach ( $rows as $row_key => $row ) {
                            if ( $row_key != 0 ) {
                                preg_match_all( "/<td[^>]*>(.*?)<\/td>/s", $row, $matches );
                                $row_fields = $matches[1];
                                foreach ( $row_fields as $key => $field ) {
                                    if ( ! isset( $th[ $key ] ) ) {
                                        continue;
                                    }
                                    $replace .= $th[ $key ] . ': ' . $field . "<br>";
                                }
                                $replace .= "<br>";
                            }
                        }
                        //$search			 = "/[^<table[^>]*>](.*)[^</table>]/s";
                        //$ic_mail_content = preg_replace( "/<table[^>]*>(.*?)<\/table>/s", $replace, $ic_mail_content );
                        $ic_mail_content = str_replace( $table, $replace, $ic_mail_content );
                    } else {
                        $replace         = wp_strip_all_tags( $table );
                        $ic_mail_content = str_replace( $table, $replace, $ic_mail_content );
                    }
                }
            }
            $button = addslashes( ic_email_button( '(.*?)' ) );
            if ( strpos( $button, $ic_mail_content ) !== false ) {
                $ic_mail_content = preg_replace( '/' . $button . '(.*?)<\/a>/i', '', $ic_mail_content );
            }
            $mailer->AltBody = strip_tags( str_replace( array( '<br>', '</p>', '<ul>' ), array(
                    "\n",
                    "\n",
                    "\n"
            ), $ic_mail_content ) );
            $ic_mail_content = '';
            unset( $ic_mail_content );
        }

        return $mailer;
    }

}

if ( ! function_exists( 'ic_email_paragraph' ) ) {

    /**
     * Initializes HTML email paragraph
     *
     * @return string
     */
    function ic_email_paragraph( $style = null ) {
        if ( ! empty( $style ) ) {
            $style = ' style="' . $style . '"';
        }
        $p = '<p' . $style . '>';

        return $p;
    }

}

if ( ! function_exists( 'ic_email_paragraph_end' ) ) {

    /**
     * Initializes HTML email paragraph
     *
     * @return string
     */
    function ic_email_paragraph_end() {
        $p = '</p>';

        return $p;
    }

}

if ( ! function_exists( 'ic_email_span' ) ) {

    /**
     * Initializes HTML email paragraph
     *
     * @return string
     */
    function ic_email_span( $style = null ) {
        if ( ! empty( $style ) ) {
            $style = ' style="' . $style . '"';
        }
        $p = '<span' . $style . '>';

        return $p;
    }

}

if ( ! function_exists( 'ic_email_span_end' ) ) {

    /**
     * Initializes HTML email paragraph
     *
     * @return string
     */
    function ic_email_span_end() {
        $p = '</span>' . "\n";

        return $p;
    }

}

if ( ! function_exists( 'ic_email_ul' ) ) {

    /**
     * Initializes HTML email ul
     * @return string
     */
    function ic_email_ul() {
        $p = '<ul style="width:70%;padding-left:10%">';

        return $p;
    }

}

if ( ! function_exists( 'ic_email_ul_end' ) ) {

    /**
     * Initializes HTML email ul
     * @return string
     */
    function ic_email_ul_end() {
        $p = '</ul>' . "\n";

        return $p;
    }

}

if ( ! function_exists( 'ic_email_li' ) ) {

    /**
     * Initializes HTML email li
     * @return string
     */
    function ic_email_li() {
        $p = '<li style="font-size:16px;line-height:1.5em;font-weight:bold">';

        return $p;
    }

}

if ( ! function_exists( 'ic_email_li_end' ) ) {

    /**
     * Initializes HTML email li
     * @return string
     */
    function ic_email_li_end() {
        $p = '</li>' . "\n";

        return $p;
    }

}

if ( ! function_exists( 'ic_email_table' ) ) {

    /**
     * Initializes HTML email table
     * @return string
     */
    function ic_email_table() {
        $p = '<table cellspacing="0" cellpadding="10" border="0" style="margin: 15px 0;color:#555555;border: 1px solid #555555;width: 100%;">';

        return $p;
    }

}

if ( ! function_exists( 'ic_email_table_end' ) ) {

    /**
     * Finishes HTML email table
     * @return string
     */
    function ic_email_table_end() {
        $p = '</table>' . "\n";

        return $p;
    }

}

if ( ! function_exists( 'ic_email_table_tr' ) ) {

    /**
     * Initializes HTML email tr
     * @return string
     */
    function ic_email_table_tr() {
        $p = '<tr>';

        return $p;
    }

}

if ( ! function_exists( 'ic_email_table_tr_end' ) ) {

    /**
     * Finishes HTML email tr
     * @return string
     */
    function ic_email_table_tr_end() {
        $p = '</tr>' . "\n";

        return $p;
    }

}

if ( ! function_exists( 'ic_email_table_th' ) ) {

    /**
     * Initializes HTML email tr
     * @return string
     */
    function ic_email_table_th() {
        $p = '<tr style="font-weight: bold;">';

        return $p;
    }

}

if ( ! function_exists( 'ic_email_table_th_end' ) ) {

    /**
     * Finishes HTML email tr
     * @return string
     */
    function ic_email_table_th_end() {
        $p = '</tr>' . "\n";

        return $p;
    }

}

if ( ! function_exists( 'ic_email_table_td' ) ) {

    /**
     * Initializes HTML email td
     * @return string
     */
    function ic_email_table_td() {
        $p = '<td style="text-align: center;">';

        return $p;
    }

}

if ( ! function_exists( 'ic_email_table_td_first' ) ) {

    /**
     * Initializes HTML email td
     * @return string
     */
    function ic_email_table_td_first() {
        $p = '<td>';

        return $p;
    }

}

if ( ! function_exists( 'ic_email_table_td_end' ) ) {

    /**
     * Finishes HTML email td
     * @return string
     */
    function ic_email_table_td_end() {
        $p = '</td>' . "\n";

        return $p;
    }

}

if ( ! function_exists( 'ic_email_button' ) ) {

    /**
     * Initializes HTML email button
     *
     * @param type $link
     *
     * @return string
     */
    function ic_email_button( $link ) {
        $a = '<a class="remove-plain" style="width:125px; display: block; font-size:15px; background-color:#bb0000;color:#ffffff; text-decoration:none; text-align:center; border-radius:10px; margin:10px auto; padding: 15px 10px 15px 10px;" target="_blank" href="' . $link . '">';

        return $a;
    }

}

if ( ! function_exists( 'ic_mail' ) ) {

    /**
     * Sends email
     *
     * @param string $sender_name
     * @param email $sender_email
     * @param email $receiver_email
     * @param string $title
     * @param boolean $template
     */
    function ic_mail( $message, $sender_name, $sender_email, $receiver_email, $title, $template = true, $attachments = array() ) {
        if ( ic_is_message_spam( $sender_name, $sender_email, $message ) ) {
            return false;
        }

        if ( is_email( $sender_name ) ) {
            $headers[] = 'From: ' . $sender_email . ' <' . $sender_email . '>';
            $headers[] = 'Reply-To: <' . $sender_name . '>';
        } else {
            $headers[] = 'From: ' . $sender_name . ' <' . $sender_email . '>';
        }
        if ( $template ) {
            //$headers[]	 = 'Content-type: text/html';
            add_filter( 'wp_mail_content_type', 'ic_mail_set_html' );
            $message = ic_htmlize_email( $message, $title, $sender_name );
        } else {
            //$headers[]	 = 'Content-type: text/plain';
            $message = strip_tags( str_replace( array( '<br>', '</p>', '<ul>' ), array(
                    "\r\n",
                    "\r\n",
                    "\r\n"
            ), $message ), "\r\n" );
        }
        wp_mail( $receiver_email, $title, $message, $headers, $attachments );
        remove_filter( 'wp_mail_content_type', 'ic_mail_set_html' );
    }

}
if ( ! function_exists( 'ic_mail_set_html' ) ) {

    function ic_mail_set_html() {
        return apply_filters( 'ic_mail_html_content_type', 'text/html' );
    }

}

if ( ! function_exists( 'ic_mail_set_multipart' ) ) {

    function ic_mail_set_multipart() {
        return apply_filters( 'ic_mail_multipart_content_type', 'multipart/alternative' );
    }

}

if ( ! function_exists( 'ic_container_shortcode' ) ) {
    add_shortcode( 'ic_container', 'ic_container_shortcode' );

    function ic_container_shortcode( $atts ) {
        $available_args = array(
                'container' => 'span',
                'style'     => '',
        );
        $args           = shortcode_atts( $available_args, $atts );
        $style          = $args['style'];
        if ( $args['container'] !== 'div' ) {
            $args['container'] = 'span';
        }

        return '<' . $args['container'] . ' style="' . esc_attr( $style ) . '">';
    }

}

if ( ! function_exists( 'ic_container_shortcode_close' ) ) {
    add_shortcode( 'ic_container_close', 'ic_container_shortcode_close' );

    function ic_container_shortcode_close( $atts ) {
        $available_args = array(
                'container' => 'span',
        );
        $args           = shortcode_atts( $available_args, $atts );
        if ( $args['container'] !== 'div' ) {
            $args['container'] = 'span';
        }

        return '</' . $args['container'] . '>';
    }

}

if ( ! function_exists( 'ic_get_checkout_field_selector' ) ) {

    function ic_get_checkout_field_selector(
            $fields, $pre_name, $name, $selected, $attr = 'multiple',
            $include_empty = false, $placeholder = null, $field_types = array( 'dropdown' )
    ) {
//$product_currency_settings	 = get_currency_settings();
        if ( $placeholder == null ) {
            $placeholder = __( 'Select Checkout Fields', 'implecode-shopping-cart' );
        }
        $selector = '<select class="chosen checkout_limit_selector_' . $pre_name . '" ' . $attr . ' data-placeholder="' . $placeholder . '" name="' . $name . '">';
//$pre_name					 = 'cart_';
        if ( $include_empty ) {
            $selector .= '<option value=""></option>';
        }
        foreach ( $fields->fields as $field ) {
            if ( $field_types === 'any' || in_array( $field->field_type, $field_types ) ) {
                $option_selected = '';
                $field_id        = apply_filters( 'ic_formbuilder_cid', $pre_name . $field->cid, $field, $pre_name );
                if ( ! empty( $selected ) && ( ! is_array( $selected ) && $field_id == $selected ) || ( is_array( $selected ) && array_search( $field_id, $selected ) !== false ) ) {
                    $option_selected = 'selected';
                }
                $options = '';
                if ( isset( $field->field_options->options ) && is_array( $field->field_options->options ) ) {
                    $options = json_encode( $field->field_options->options );
                }
                $selector .= '<option ' . $option_selected . ' data-options="' . htmlspecialchars( $options ) . '" value="' . $field_id . '">' . str_replace( ":", "", $field->label ) . "</option>";
            }
        }
        $selector .= '</select>';

        return $selector;
    }

}

if ( ! function_exists( 'ic_get_default_date_format' ) ) {

    function ic_get_default_date_format( $preview = false, $jquery = false ) {
        $format = get_option( 'date_format' );
        if ( $preview ) {
            return date_i18n( $format );
        }
        if ( $jquery ) {
            $format = dateformat_PHP_to_jQueryUI( $format );
        }

        return $format;
    }

}

if ( ! function_exists( 'ic_is_message_spam' ) ) {


    function ic_is_message_spam( $sender_name, $sender_email, $message ) {
        $userip    = isset( $_SERVER['REMOTE_ADDR'] ) ? preg_replace( '/[^0-9a-fA-F:., ]/', '', $_SERVER['REMOTE_ADDR'] ) : '';
        $useragent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( $_SERVER['HTTP_USER_AGENT'], 0, 254 ) : '';
        if ( function_exists( 'wp_check_comment_disallowed_list' ) && wp_check_comment_disallowed_list( $sender_name, $sender_email, '', $message, $userip, $useragent ) ) {
            return true;
        } else if ( ! function_exists( 'wp_check_comment_disallowed_list' ) && wp_blacklist_check( $sender_name, $sender_email, '', $message, $userip, $useragent ) ) {
            return true;
        }

        return false;
    }

}

if ( ! function_exists( 'dateformat_PHP_to_jQueryUI' ) ) {
    /*
     * Matches each symbol of PHP date format standard
     * with jQuery equivalent codeword
     * @author Tristan Jahier
     */

    function dateformat_PHP_to_jQueryUI( $php_format ) {
        $SYMBOLS_MATCHING = array(
            // Day
                'd' => 'dd',
                'D' => 'D',
                'j' => 'd',
                'l' => 'DD',
                'N' => '',
                'S' => '',
                'w' => '',
                'z' => 'o',
            // Week
                'W' => '',
            // Month
                'F' => 'MM',
                'm' => 'mm',
                'M' => 'M',
                'n' => 'm',
                't' => '',
            // Year
                'L' => '',
                'o' => '',
                'Y' => 'yy',
                'y' => 'y',
            // Time
                'a' => 'tt',
                'A' => 'TT',
                'B' => '',
                'g' => 'h',
                'G' => 'H',
                'h' => 'hh',
                'H' => 'HH',
                'i' => 'mm',
                's' => 'ss',
                'T' => 'z'
        );
        $jqueryui_format  = "";
        $escaping         = false;
        for ( $i = 0; $i < strlen( $php_format ); $i ++ ) {
            $char = $php_format[ $i ];
            if ( $char === '\\' ) { // PHP date format escaping character
                $i ++;
                if ( $escaping ) {
                    $jqueryui_format .= $php_format[ $i ];
                } else {
                    $jqueryui_format .= '\'' . $php_format[ $i ];
                }
                $escaping = true;
            } else {
                if ( $escaping ) {
                    $jqueryui_format .= "'";
                    $escaping        = false;
                }
                if ( isset( $SYMBOLS_MATCHING[ $char ] ) ) {
                    $jqueryui_format .= $SYMBOLS_MATCHING[ $char ];
                } else {
                    $jqueryui_format .= $char;
                }
            }
        }

        return $jqueryui_format;
    }

}

if ( ! function_exists( 'ic_get_default_time_format' ) ) {

    function ic_get_default_time_format( $preview = false, $jquery = false ) {
        $format = get_option( 'time_format' );
        if ( $preview ) {
            return date_i18n( $format );
        }
        if ( $jquery ) {
            $format = dateformat_PHP_to_jQueryUI( $format );
        }

        return $format;
    }

}
<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages cart cart settings
 *
 * Here cart settings are defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-shopping-cart/includes
 * @author        Norbert Dreszer
 */
function shopping_cart_menu() {
	?>
    <a id="shopping-cart-settings" class="element"
       href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=shopping-cart' ) ?>"><?php echo apply_filters( 'ic_simple_cart_settings_menu_name', __( 'Shopping Cart', 'ecommerce-product-catalog' ) ) ?> </a><?php
}

add_action( 'general_submenu', 'shopping_cart_menu' );

function shopping_cart_settings() {
	register_setting( 'shopping_cart', 'shopping_cart_settings' );
	register_setting( 'checkout_form', 'shopping_checkout_form' );
	register_setting( 'checkout_form', 'cart_form_editor_settings' );
	do_action( 'shopping_cart_settings' );
}

add_action( 'product-settings-list', 'shopping_cart_settings' );

function get_shopping_cart_settings() {
	$shopping_cart_settings = get_option( 'shopping_cart_settings' );
	if ( ! is_array( $shopping_cart_settings ) ) {
		$shopping_cart_settings = array();
	}
	$shopping_cart_settings['shopping_cart_page']      = isset( $shopping_cart_settings['shopping_cart_page'] ) ? $shopping_cart_settings['shopping_cart_page'] : '';
	$shopping_cart_settings['cart_submit_page']        = isset( $shopping_cart_settings['cart_submit_page'] ) ? $shopping_cart_settings['cart_submit_page'] : '';
	$shopping_cart_settings['thank_you_page']          = isset( $shopping_cart_settings['thank_you_page'] ) ? $shopping_cart_settings['thank_you_page'] : '';
	$shopping_cart_settings['receive_cart']            = isset( $shopping_cart_settings['receive_cart'] ) ? trim( $shopping_cart_settings['receive_cart'] ) : '';
	$shopping_cart_settings['receive_packing_list']    = isset( $shopping_cart_settings['receive_packing_list'] ) ? trim( $shopping_cart_settings['receive_packing_list'] ) : '';
	$shopping_cart_settings['send_cart']               = isset( $shopping_cart_settings['send_cart'] ) ? trim( $shopping_cart_settings['send_cart'] ) : '';
	$shopping_cart_settings['cart_name']               = isset( $shopping_cart_settings['cart_name'] ) ? $shopping_cart_settings['cart_name'] : '';
	$shopping_cart_settings['form_registration']       = isset( $shopping_cart_settings['form_registration'] ) ? $shopping_cart_settings['form_registration'] : 'cookie';
	$shopping_cart_settings['country_field']           = isset( $shopping_cart_settings['country_field'] ) ? $shopping_cart_settings['country_field'] : 'dropdown';
	$shopping_cart_settings['stick_cart']              = isset( $shopping_cart_settings['stick_cart'] ) ? $shopping_cart_settings['stick_cart'] : '';
	$shopping_cart_settings['disable_captcha']         = isset( $shopping_cart_settings['disable_captcha'] ) ? $shopping_cart_settings['disable_captcha'] : '';
	$shopping_cart_settings['button_desc']             = isset( $shopping_cart_settings['button_desc'] ) ? $shopping_cart_settings['button_desc'] : __( 'Click to add this item to cart.', 'ecommerce-product-catalog' );
	$shopping_cart_settings['button_label']            = isset( $shopping_cart_settings['button_label'] ) ? $shopping_cart_settings['button_label'] : __( 'Add to Cart', 'ecommerce-product-catalog' );
	$shopping_cart_settings['url_button']              = isset( $shopping_cart_settings['url_button'] ) ? $shopping_cart_settings['url_button'] : '';
	$shopping_cart_settings['cart_redirect']           = isset( $shopping_cart_settings['cart_redirect'] ) ? $shopping_cart_settings['cart_redirect'] : '';
	$shopping_cart_settings['empty_cart']              = isset( $shopping_cart_settings['empty_cart'] ) ? $shopping_cart_settings['empty_cart'] : '';
	$shopping_cart_settings['place_order_label']       = isset( $shopping_cart_settings['place_order_label'] ) ? $shopping_cart_settings['place_order_label'] : __( 'Place an order', 'ecommerce-product-catalog' );
	$shopping_cart_settings['contnue_shopping_label']  = isset( $shopping_cart_settings['contnue_shopping_label'] ) ? $shopping_cart_settings['contnue_shopping_label'] : __( 'Continue shopping', 'ecommerce-product-catalog' );
	$shopping_cart_settings['contnue_shopping_target'] = isset( $shopping_cart_settings['contnue_shopping_target'] ) ? $shopping_cart_settings['contnue_shopping_target'] : 'product_listing';
	$shopping_cart_settings['quantity_box']            = isset( $shopping_cart_settings['quantity_box'] ) ? $shopping_cart_settings['quantity_box'] : '';
	$shopping_cart_settings['add_on_price']            = isset( $shopping_cart_settings['add_on_price'] ) ? $shopping_cart_settings['add_on_price'] : '';
	$shopping_cart_settings['listing_separate_name']   = isset( $shopping_cart_settings['listing_separate_name'] ) ? $shopping_cart_settings['listing_separate_name'] : '';
	$shopping_cart_settings['email_separate_name']     = isset( $shopping_cart_settings['listing_separate_name'] ) ? $shopping_cart_settings['listing_separate_name'] : '';
	$shopping_cart_settings['cart_separate_name']      = isset( $shopping_cart_settings['listing_separate_name'] ) ? $shopping_cart_settings['listing_separate_name'] : '';
	$shopping_cart_settings['user_email']              = isset( $shopping_cart_settings['user_email'] ) ? $shopping_cart_settings['user_email'] : apply_filters( 'ic_simple_cart_default_customer_email_text', __( 'Dear Customer,', 'ecommerce-product-catalog' ) . ' ' . "\n\r" . __( 'Thank you for your order.', 'ecommerce-product-catalog' ) . ' ' . "\r" . '[customer_details] ' . "\n\r" . '[account_info] ' . "\n\r" . __( 'We will get in touch with you soon.', 'ecommerce-product-catalog' ) . "\n\r" . '[payment_details]' . "\n\r" . __( 'Kind regards,', 'ecommerce-product-catalog' ) . "\r" . 'impleCode Team' );
	$shopping_cart_settings['admin_email']             = isset( $shopping_cart_settings['admin_email'] ) ? $shopping_cart_settings['admin_email'] : apply_filters( 'ic_simple_cart_default_admin_email_text', __( 'New order from a customer.', 'ecommerce-product-catalog' ) . "\n\r" . __( 'Order details', 'ecommerce-product-catalog' ) . ': ' . "\r" . '[customer_details]' . "\n\r" . '[edit_order_url]' . "\n\r" . '[payment_details]' . "\n\r" . __( 'Kind regards,', 'ecommerce-product-catalog' ) . "\r" . 'impleCode Team' );
	$shopping_cart_settings['qty_box']                 = isset( $shopping_cart_settings['qty_box'] ) ? $shopping_cart_settings['qty_box'] : '';
	$shopping_cart_settings['max_qty_box']             = isset( $shopping_cart_settings['max_qty_box'] ) ? $shopping_cart_settings['max_qty_box'] : '';
	$shopping_cart_settings['custom_payments']         = isset( $shopping_cart_settings['custom_payments'] ) ? array_filter( array_map( 'array_filter', $shopping_cart_settings['custom_payments'] ) ) : array();
	$shopping_cart_settings['cart_page_template']      = isset( $shopping_cart_settings['cart_page_template'] ) ? $shopping_cart_settings['cart_page_template'] : 'qty';
	if ( ! empty( $shopping_cart_settings['cart_name'] ) ) {
		$site_name = $shopping_cart_settings['cart_name'];
	} else {
		$site_name = get_bloginfo( 'name' );
	}
	$shopping_cart_settings['admin_email_subject']        = isset( $shopping_cart_settings['admin_email_subject'] ) ? $shopping_cart_settings['admin_email_subject'] : sprintf( __( 'Order from %s', 'ecommerce-product-catalog' ), $site_name );
	$shopping_cart_settings['user_email_subject']         = isset( $shopping_cart_settings['user_email_subject'] ) ? $shopping_cart_settings['user_email_subject'] : sprintf( __( 'New Order on %s', 'ecommerce-product-catalog' ), $site_name );
	$shopping_cart_settings['packing_email_subject']      = isset( $shopping_cart_settings['packing_email_subject'] ) ? $shopping_cart_settings['packing_email_subject'] : sprintf( __( 'New Packing List on %s', 'ecommerce-product-catalog' ), $site_name );
	$shopping_cart_settings['order_registration_disable'] = isset( $shopping_cart_settings['order_registration_disable'] ) ? $shopping_cart_settings['order_registration_disable'] : '';
	$shopping_cart_settings['disable_payment']            = isset( $shopping_cart_settings['disable_payment'] ) ? $shopping_cart_settings['disable_payment'] : '';
	$shopping_cart_settings['enable_recommendations']     = isset( $shopping_cart_settings['enable_recommendations'] ) ? $shopping_cart_settings['enable_recommendations'] : '';
	$shopping_cart_settings['disable_state']              = isset( $shopping_cart_settings['disable_state'] ) ? $shopping_cart_settings['disable_state'] : '';

	return apply_filters( 'ic_shopping_cart_settings', $shopping_cart_settings );
}

function shopping_cart_settings_content() {
	$submenu = isset( $_GET['submenu'] ) ? $_GET['submenu'] : '';
	if ( $submenu == 'shopping-cart' ) {
		?>
        <script>
            jQuery('.settings-submenu a').removeClass('current');
            jQuery('.settings-submenu a#shopping-cart-settings').addClass('current');
        </script>
        <div class="shopping-cart-settings setting-content submenu">
            <form method="post" action="options.php">
                <h2><?php _e( 'Cart Settings', 'ecommerce-product-catalog' ) ?></h2>
				<?php
				settings_fields( 'shopping_cart' );
				$shopping_cart_settings = get_shopping_cart_settings();
				ob_start();
				?>

                <table>
                    <tr>
                        <td>
							<?php _e( 'Choose Cart Page', 'ecommerce-product-catalog' ); ?>:
                        </td>
                        <td>
							<?php
							$default_content = ic_set_shortcode_content( 'shopping_cart' );
							ic_select_page( 'shopping_cart_settings[shopping_cart_page]', __( 'Select Cart Page', 'ecommerce-product-catalog' ), $shopping_cart_settings['shopping_cart_page'], true, false, 1, false, '', array(
								'title'      => __( 'Cart', 'ecommerce-product-catalog' ),
								'content'    => $default_content,
								'option'     => 'shopping_cart_settings',
								'option_sub' => 'shopping_cart_page'
							) );
							?>
                        </td>
                    </tr>
                    <tr>
                        <td>
							<?php _e( 'Choose Cart Submit Page', 'ecommerce-product-catalog' ); ?>:
                        </td>
                        <td>
							<?php
							$default_content = ic_set_shortcode_content( 'cart_submit_form' );
							ic_select_page( 'shopping_cart_settings[cart_submit_page]', __( 'Select Submit Page', 'ecommerce-product-catalog' ), $shopping_cart_settings['cart_submit_page'], true, false, 1, false, '', array(
								'title'      => __( 'Checkout', 'ecommerce-product-catalog' ),
								'content'    => $default_content,
								'option'     => 'shopping_cart_settings',
								'option_sub' => 'cart_submit_page'
							) );
							?>
                        </td>
                    </tr>
                    <tr>
                        <td>
							<?php _e( 'Choose Thank You Page', 'ecommerce-product-catalog' ); ?>:
                        </td>
                        <td>
							<?php
							$default_content = ic_set_shortcode_content( 'success_page' );
							ic_select_page( 'shopping_cart_settings[thank_you_page]', __( 'Select Thank You Page', 'ecommerce-product-catalog' ), $shopping_cart_settings['thank_you_page'], true, false, 1, false, '', array(
								'title'      => __( 'Thank You', 'ecommerce-product-catalog' ),
								'content'    => $default_content,
								'option'     => 'shopping_cart_settings',
								'option_sub' => 'thank_you_page'
							) );
							?>
                        </td>
                    </tr>
                </table><?php implecode_al_box( sprintf( __( 'Remember to use the %1$sShopping Cart widget%2$s to show the cart in the sidebar.', 'ecommerce-product-catalog' ), '<a href="' . admin_url( 'widgets.php' ) . '">', '</a>' ) ); ?>
                <h3><?php _e( 'Email settings', 'ecommerce-product-catalog' ); ?></h3>
                <style>
                    textarea[name="shopping_cart_settings[admin_email]"], textarea[name="shopping_cart_settings[user_email]"], textarea[name="shopping_cart_settings[packing_email]"] {
                        width: 450px;
                        height: 230px;
                    }

                    input[name="shopping_cart_settings[admin_email_subject]"], input[name="shopping_cart_settings[user_email_subject]"], input[name="shopping_cart_settings[packing_email_subject]"] {
                        width: 450px;
                    }

                </style>
				<?php
				$smtp_spam_message = sprintf( __( 'Free %1$sSMTP plugin%2$s is recommended to make sure that your notifications are not treated as SPAM.', 'ecommerce-product-catalog' ), '<a target="_blank" href="' . admin_url( 'plugin-install.php?s=smtp&tab=search&type=term' ) . '">', '</a>' );
				$smtp_spam_message .= ' ' . __( 'Alternately, try using the Email to send confirmations that matches your website domain.', 'ecommerce-product-catalog' );
				implecode_info( $smtp_spam_message );
				?>
                <table>
					<?php
					implecode_settings_text( __( 'Email to receive orders', 'ecommerce-product-catalog' ), 'shopping_cart_settings[receive_cart]', $shopping_cart_settings['receive_cart'] );
					implecode_settings_text( __( 'Email to send confirmations', 'ecommerce-product-catalog' ), 'shopping_cart_settings[send_cart]', $shopping_cart_settings['send_cart'] );
					implecode_settings_text( __( 'Your name in emails', 'ecommerce-product-catalog' ), 'shopping_cart_settings[cart_name]', $shopping_cart_settings['cart_name'] );
					implecode_settings_text( __( 'Admin Email Subject', 'ecommerce-product-catalog' ), 'shopping_cart_settings[admin_email_subject]', $shopping_cart_settings['admin_email_subject'] );
					implecode_settings_textarea( __( 'Admin Email template', 'ecommerce-product-catalog' ), 'shopping_cart_settings[admin_email]', $shopping_cart_settings['admin_email'] );
					implecode_settings_text( __( 'User Email Subject', 'ecommerce-product-catalog' ), 'shopping_cart_settings[user_email_subject]', $shopping_cart_settings['user_email_subject'] );
					implecode_settings_textarea( __( 'User Email template', 'ecommerce-product-catalog' ), 'shopping_cart_settings[user_email]', $shopping_cart_settings['user_email'] );
					?>
                </table>
                <h3><?php _e( 'Form Settings', 'ecommerce-product-catalog' ); ?></h3>
                <table><?php
					implecode_settings_radio( __( 'Remember fields by', 'ecommerce-product-catalog' ), 'shopping_cart_settings[form_registration]', $shopping_cart_settings['form_registration'], array(
						'user'   => __( 'User Account', 'ecommerce-product-catalog' ),
						'cookie' => __( 'Cookie Save', 'ecommerce-product-catalog' )
					), 1, __( 'If you select user account you will be able to select a page to become user account with the order history.', 'ecommerce-product-catalog' ) );
					//implecode_settings_radio(__('Country field', 'implecode-shopping-cart'), 'shopping_cart_settings[country_field]', $shopping_cart_settings['country_field'], $elements = array('dropdown' => __('Drop Down', 'implecode-shopping-cart').'<br>', 'text' => __('Text', 'implecode-shopping-cart')));
					implecode_settings_checkbox( __( 'Disable State Field', 'ecommerce-product-catalog' ), 'shopping_cart_settings[disable_state]', $shopping_cart_settings['disable_state'], 1, __( 'Check this checbox if you are not selling to USA.', 'ecommerce-product-catalog' ) );
					?>
                </table>
                <h3><?php _e( 'Orders', 'ecommerce-product-catalog' ); ?></h3>
                <table>
					<?php
					implecode_settings_checkbox( __( 'Disable Order Registration', 'ecommerce-product-catalog' ), 'shopping_cart_settings[order_registration_disable]', $shopping_cart_settings['order_registration_disable'], 1, __( 'If you check this the orders will not be saved into database. Only the notification email will be sent.', 'ecommerce-product-catalog' ) );
					?>
                </table>
                <h3><?php _e( 'Cart Widget', 'ecommerce-product-catalog' ); ?></h3>
                <table><?php implecode_settings_checkbox( __( 'Stick cart on top on scroll', 'ecommerce-product-catalog' ), 'shopping_cart_settings[stick_cart]', $shopping_cart_settings['stick_cart'] ); ?>
                </table>
				<?php
				do_action( 'shopping-cart-settings', $shopping_cart_settings );
				echo apply_filters( 'ic_cart_settings_html', ob_get_clean() );
				?>
                <p class="submit">
                    <input type="submit" class="button-primary"
                           value="<?php _e( 'Save changes', 'ecommerce-product-catalog' ); ?>"/>
                </p>

            </form>
        </div>
		<?php
	}
}

add_action( 'product-settings', 'shopping_cart_settings_content' );

function default_shopping_checkout_settings() {
	$shopping_cart_settings = get_shopping_cart_settings();
	//$supported_states       = implecode_supported_states();
	//$supported_countries    = implecode_supported_countries();
//$default = '{"fields":[{"label":"Name","field_type":"text","required":true,"field_options":{"size":"medium"},"cid":"name"},{"label":"Email","field_type":"email","required":true,"field_options":{"size":"medium"},"cid":"email"},{"label":"Subject","field_type":"text","required":true,"field_options":{"size":"medium"},"cid":"subject"},{"label":"Message","field_type":"paragraph","required":true,"field_options":{"size":"medium"},"cid":"message"}]}';
	$default = '{"fields":[';
	$default .= '{"label":"' . __( '<b>BILLING ADDRESS</b>', 'ecommerce-product-catalog' ) . '","field_type":"section_break","required":false,"cid":"inside_header_1"},{"label":"' . __( 'Company', 'ecommerce-product-catalog' ) . ':","field_type":"text","required":false,"field_options":{"size":"medium","paypal":"customer_name"},"cid":"company"},{"label":"' . __( 'Full Name', 'ecommerce-product-catalog' ) . ':","field_type":"text","required":true,"field_options":{"size":"medium","paypal":"customer_surname"},"cid":"name"},{"label":"' . __( 'Address', 'ecommerce-product-catalog' ) . ':","field_type":"text","required":true,"field_options":{"size":"medium","paypal":"customer_address"},"cid":"address"},{"label":"' . __( 'Postal Code', 'ecommerce-product-catalog' ) . ':","field_type":"text","required":true,"field_options":{"size":"medium","paypal":"customer_postcode"},"cid":"postal"},{"label":"' . __( 'City', 'ecommerce-product-catalog' ) . ':","field_type":"text","required":true,"field_options":{"size":"medium","paypal":"customer_city"},"cid":"city"}';
	$default .= ',{"label":"' . __( 'Country', 'ecommerce-product-catalog' ) . ':","field_type":"dropdown_country","required":true,"field_options":{"size":"medium","paypal":"customer_country"';
	/*
    if ( ! empty( $shopping_cart_settings['disable_state'] ) ) {
		asort( $supported_countries );
		$default .= ',{"label":"' . __( 'Country', 'ecommerce-product-catalog' ) . ':","field_type":"dropdown","required":true,"field_options":{"include_blank_option": "1","size":"medium","paypal":"customer_country"';
	} else {
		$default .= ',{"label":"' . __( 'Country', 'ecommerce-product-catalog' ) . ':","field_type":"dropdown","required":true,"field_options":{"size":"medium","paypal":"customer_country"';
	}
	$default .= ',"options":[';
	foreach ( $supported_countries as $code => $country ) {
		$default .= '{"label":"' . $country . '", "checked":false},';
	}
	$default .= ']';
	*/
	$default .= '},"cid":"country"}';
	if ( empty( $shopping_cart_settings['disable_state'] ) ) {
		$default .= ',{"label":"' . __( 'State', 'ecommerce-product-catalog' ) . ':","field_type":"dropdown_state","required":true,"field_options":{"size":"medium","paypal":"customer_state"';
		/*
        $default .= ',"options":[';
		foreach ( $supported_states as $code => $state ) {
			$default .= '{"label":"' . $state . '", "checked":false},';
		}
		$default .= ']';
		*/
		$default .= '},"cid":"state"}';
	}
	$default .= ',{"label":"' . __( 'Phone', 'ecommerce-product-catalog' ) . ':","field_type":"text","required":true,"field_options":{"size":"medium","paypal":"customer_phone"},"cid":"phone"},{"label":"' . __( 'Email', 'ecommerce-product-catalog' ) . ':","field_type":"email","required":true,"field_options":{"size":"medium","paypal":"1"},"cid":"email"},{"label":"' . __( 'Comment', 'ecommerce-product-catalog' ) . ':","field_type":"paragraph","required":false,"field_options":{"size":"medium"},"cid":"comment"}';
	$default .= ',{"label":"' . __( '<b>DELIVERY ADDRESS</b> (FILL ONLY IF DIFFERENT FROM THE BILLING ADDRESS)', 'ecommerce-product-catalog' ) . '","field_type":"section_break","required":false,"cid":"inside_header_2"},';
	$default .= '{"label":"' . __( 'Company', 'ecommerce-product-catalog' ) . ':","field_type":"text","required":false,"field_options":{"size":"medium"},"cid":"s_company"},{"label":"' . __( 'Full Name', 'ecommerce-product-catalog' ) . ':","field_type":"text","required":false,"field_options":{"size":"medium"},"cid":"s_name"},{"label":"' . __( 'Address', 'ecommerce-product-catalog' ) . ':","field_type":"text","required":false,"field_options":{"size":"medium"},"cid":"s_address"},{"label":"' . __( 'Postal Code', 'ecommerce-product-catalog' ) . ':","field_type":"text","required":false,"field_options":{"size":"medium"},"cid":"s_postal"},{"label":"' . __( 'City', 'ecommerce-product-catalog' ) . ':","field_type":"text","required":false,"field_options":{"size":"medium"},"cid":"s_city"}';
	$default .= ',{"label":"' . __( 'Country', 'ecommerce-product-catalog' ) . ':","field_type":"dropdown_country","required":false,"field_options":{"include_blank_option": "1","size":"medium"';
	/*
		if ( ! empty( $shopping_cart_settings['disable_state'] ) ) {
			$default .= ',{"label":"' . __( 'Country', 'ecommerce-product-catalog' ) . ':","field_type":"dropdown","required":false,"field_options":{"include_blank_option": "1","size":"medium"';
		} else {
			$default .= ',{"label":"' . __( 'Country', 'ecommerce-product-catalog' ) . ':","field_type":"dropdown","required":false,"field_options":{"size":"medium"';
		}
		$default .= ',"options":[';
		foreach ( $supported_countries as $code => $country ) {
			$default .= '{"label":"' . $country . '", "checked":false},';
		}
		$default .= ']';
	*/
	$default .= '},"cid":"s_country"}';
	if ( empty( $shopping_cart_settings['disable_state'] ) ) {
		$default .= ',{"label":"' . __( 'State', 'ecommerce-product-catalog' ) . ':","field_type":"dropdown_state","required":false,"field_options":{"size":"medium"';
		/*
        $default .= ',"options":[';
		foreach ( $supported_states as $code => $state ) {
			$default .= '{"label":"' . $state . '", "checked":false},';
		}
		$default .= ']';
		*/
		$default .= '},"cid":"s_state"}';
	}
	$default .= ',{"label":"' . __( 'Phone', 'ecommerce-product-catalog' ) . ':","field_type":"text","required":false,"field_options":{"size":"medium"},"cid":"s_phone"},{"label":"' . __( 'Email', 'ecommerce-product-catalog' ) . ':","field_type":"email","required":false,"field_options":{"size":"medium"},"cid":"s_email"},{"label":"' . __( 'Comment', 'ecommerce-product-catalog' ) . ':","field_type":"paragraph","required":false,"field_options":{"size":"medium"},"cid": "s_comment"}';
	if ( function_exists( 'get_privacy_policy_url' ) ) {
		$privacy_url = get_privacy_policy_url();
	}
	if ( ! empty( $privacy_url ) ) {
		$message = sprintf( __( 'I agree to let %1$s process my personal data to process my order and for other purposes described in %1$s %2$sPrivacy Notice%3$s.', 'ecommerce-product-catalog' ), get_bloginfo( 'name' ), "<a href='" . $privacy_url . "'>", '</a>' );
		$default .= ',{"label":"' . __( 'Privacy Notice', 'ecommerce-product-catalog' ) . ':","field_type":"checkboxes","required":true,"field_options":{"size":"medium","options":[{"label":"' . $message . '", "checked":false}]},"cid":"privacy_notice"}';
	}
	$default .= ']}';

	return str_replace( ',]', ']', apply_filters( 'ic_shopping_cart_checkout_fields', $default ) );
}

if ( ! function_exists( 'get_shopping_checkout_form_fields' ) ) {

	function get_shopping_checkout_form_fields() {
		$shopping_checkout_form = default_shopping_checkout_settings();

		return $shopping_checkout_form;
	}

}

function get_shopping_cart_site_name() {
	$shopping_cart_settings = get_shopping_cart_settings();
	if ( ! empty( $shopping_cart_settings['cart_name'] ) ) {
		$site_name = $shopping_cart_settings['cart_name'];
	} else {
		$site_name = get_bloginfo( 'name' );
	}

	return $site_name;
}

//add_filter( 'pre_update_option_shopping_checkout_form', 'validate_formsave', 10, 2 );
//add_filter( 'pre_add_option_shopping_checkout_form', 'validate_formsave', 10, 2 );

function validate_formsave( $old_value, $value ) {
	$updated = '{"fields":[';
	$updated .= $value;
	$updated .= ']}';

	return $updated;
}

/**
 * Returns cart checkout settings
 *
 * @return array
 */
function get_cart_form_editor_settings() {
	$form_settings = get_option( 'cart_form_editor_settings' );
	if ( ! is_array( $form_settings ) ) {
		$form_settings = array();
	}
	$form_settings['form_type']         = isset( $form_settings['form_type'] ) ? $form_settings['form_type'] : 'label-left';
	$form_settings['form_button_label'] = isset( $form_settings['form_button_label'] ) ? $form_settings['form_button_label'] : __( 'Place Order', 'ecommerce-product-catalog' );

	return apply_filters( 'ic_cart_form_editor_settings', $form_settings );
}

add_action( 'admin_init', 'ic_shopping_cart_options_validation_filters' );

/**
 * Initializes validation filters for general settings
 *
 */
function ic_shopping_cart_options_validation_filters() {
	add_filter( 'pre_update_option_shopping_cart_settings', 'ic_shopping_cart_settings_validation', 10, 2 );
}

function ic_shopping_cart_settings_validation( $new_value, $old_value ) {
	if ( ! empty( $old_value['shopping_cart_page'] ) && $new_value['shopping_cart_page'] !== $old_value['shopping_cart_page'] ) {
		ic_add_shopping_cart_page_shortcode( intval( $old_value['shopping_cart_page'] ) );
		ic_add_shopping_cart_page_shortcode( intval( $new_value['shopping_cart_page'] ), 'shopping_cart' );
	} else if ( empty( $old_value['shopping_cart_page'] ) && ! empty( $new_value['shopping_cart_page'] ) ) {
		ic_add_shopping_cart_page_shortcode( intval( $new_value['shopping_cart_page'] ), 'shopping_cart' );
	}
	if ( ! empty( $old_value['cart_submit_page'] ) && $new_value['cart_submit_page'] !== $old_value['cart_submit_page'] ) {
		ic_add_shopping_cart_page_shortcode( intval( $old_value['cart_submit_page'] ) );
		ic_add_shopping_cart_page_shortcode( intval( $new_value['cart_submit_page'] ), 'cart_submit_form' );
	} else if ( empty( $old_value['cart_submit_page'] ) && ! empty( $new_value['cart_submit_page'] ) ) {
		ic_add_shopping_cart_page_shortcode( intval( $new_value['cart_submit_page'] ), 'cart_submit_form' );
	}
	if ( ! empty( $old_value['thank_you_page'] ) && $new_value['thank_you_page'] !== $old_value['thank_you_page'] ) {
		ic_add_shopping_cart_page_shortcode( intval( $old_value['thank_you_page'] ) );
		ic_add_shopping_cart_page_shortcode( intval( $new_value['thank_you_page'] ), 'success_page' );
	} else if ( empty( $old_value['thank_you_page'] ) && ! empty( $new_value['thank_you_page'] ) ) {
		ic_add_shopping_cart_page_shortcode( intval( $new_value['thank_you_page'] ), 'success_page' );
	}

	return $new_value;
}

function ic_add_shopping_cart_page_shortcode( $page_id, $shortcode = '' ) {
	if ( empty( $page_id ) ) {
		return;
	}
	$post   = get_post( $page_id );
	$update = false;
	if ( empty( $shortcode ) ) {
		if ( has_shortcode( $post->post_content, 'shopping_cart' ) ) {
			$post->post_content = str_replace( '[shopping_cart]', '', $post->post_content );
			$update             = true;
		}
		if ( has_shortcode( $post->post_content, 'cart_submit_form' ) ) {
			$post->post_content = str_replace( '[cart_submit_form]', '', $post->post_content );
			$update             = true;
		}
		if ( has_shortcode( $post->post_content, 'success_page' ) ) {
			$post->post_content = str_replace( '[success_page]', '', $post->post_content );
			$update             = true;
		}
	} else if ( ! has_shortcode( $post->post_content, $shortcode ) && ! ic_string_contains( $post->post_content, '[' ) ) {
		if ( function_exists( 'use_block_editor_for_post_type' ) && use_block_editor_for_post_type( $post->post_type ) ) {
			$shortcode_html = '<!-- wp:shortcode -->[' . $shortcode . ']<!-- /wp:shortcode -->';
		} else {
			$shortcode_html = '[' . $shortcode . ']';
		}
		$post->post_content .= $shortcode_html;
		$update             = true;
	}
	if ( $update ) {
		wp_update_post( $post );
	}
}



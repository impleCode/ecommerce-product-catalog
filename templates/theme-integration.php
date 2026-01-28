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
 * @package        ecommerce-product-catalog/functions
 * @author        impleCode
 */
class ic_catalog_theme_integration {

	function __construct() {
		add_shortcode( 'theme_integration', array( __CLASS__, 'theme_integration_shortcode' ) );

		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
//add_action( 'wp_footer', 'theme_integration_wizard' );
		add_action( 'after_product_page', array( __CLASS__, 'theme_integration_wizard' ) );
		add_action( 'wp_ajax_save_wizard', array( __CLASS__, 'save_wizard' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_sample_product_scripts' ), 100 );
		add_action( 'switch_theme', array( __CLASS__, 'erase_integration_type_select' ) );
		add_action( 'admin_init', array( __CLASS__, 'create_sample_product_with_redirect' ) );
	}

	static function theme_integration_shortcode() {

	}

	/**
	 * Shows theme intagration wizard
	 *
	 * @param type $atts
	 */
	static function theme_integration_wizard( $atts ) {
		$current_mode = self::get_real_integration_mode();
		if ( is_ic_integration_wizard_page() ) {

			$args        = shortcode_atts( array(
				'class' => 'fixed-box',
			), $atts );
			$class       = esc_attr( $args['class'] );
			$box_content = '<h4>' . __( 'Catalog Configuration', 'ecommerce-product-catalog' ) . '</h4>';
			/* $box_content .= '<script>jQuery(window).scroll( function() { if (isScrolledIntoView(".relative-box")) {jQuery(".fixed-box").hide("slow");}
			  else {jQuery(".fixed-box").show("slow");}});
			  function isScrolledIntoView(elem)
			  {
			  var docViewTop = jQuery(window).scrollTop();
			  var docViewBottom = docViewTop + jQuery(window).height();
			  var elemTop = jQuery(elem).offset().top;
			  var elemBottom = elemTop + jQuery(elem).height();
			  return ((docViewTop < elemTop) && (docViewBottom > elemBottom));
			  //    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
			  }</script>'; */
			if ( ! isset( $_GET['test_advanced'] ) ) {
				if ( is_ic_shortcode_integration() ) {
					$box_content .= '<p>' . sprintf( __( '%s is currently running in Shortcode Mode.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) . '</p>';
					$box_content .= '<p>' . __( 'In Shortcode Mode all the catalog features work fine however your theme might display some unwanted elements on catalog pages.', 'ecommerce-product-catalog' ) . '</p>';
				} else if ( is_ic_simple_mode() ) {
					$box_content .= '<p>' . sprintf( __( '%s is currently running in Simple Mode.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) . '</p>';
					$box_content .= '<p>' . __( 'In Simple Mode the product listing, product search and category pages are disabled (please read this Sample Product Page to understand the difference fully).', 'ecommerce-product-catalog' ) . '</p>';
				} else {
					$box_content .= '<p>' . sprintf( __( '%s is currently running in Advanced Mode.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) . '</p>';
				}

				$box_content .= '<p>' . __( 'Please use the button below to check out how the product page looks in Automatic Advanced Mode.', 'ecommerce-product-catalog' ) . '</p>';
				$box_content .= '<p>' . __( 'The layout might look like broken at first; however, you will be able to adjust it.', 'ecommerce-product-catalog' ) . '</p>';
				$box_content .= '<p class="wp-core-ui"><a href="' . esc_url( add_query_arg( 'test_advanced', '1' ) ) . '" class="button-primary">' . __( 'Start Advanced Mode Test', 'ecommerce-product-catalog' ) . '</a>';
				if ( is_ic_shortcode_integration( null, false ) ) {
					$box_content .= '<a href="' . esc_url( add_query_arg( 'test_advanced', 'simple' ) ) . '" class="button-secondary">' . __( 'Use Shortcode Mode', 'ecommerce-product-catalog' ) . '</a></p>';
				} else if ( is_ic_simple_mode() ) {
					$box_content .= '<a href="' . esc_url( add_query_arg( 'test_advanced', 'simple' ) ) . '" class="button-secondary">' . __( 'Use Simple Mode', 'ecommerce-product-catalog' ) . '</a></p>';
				}
				if ( $current_mode == 'simple' ) {
					echo '<div id="integration_wizard" class="' . $class . '">' . implecode_info( $box_content, 0 ) . '</div>';
				}
			} else if ( isset( $_GET['test_advanced'] ) && $_GET['test_advanced'] == 1 ) {
				//$box_content .= '<style>#integration_wizard.fixed-box.opacity {opacity: 0.8;}#integration_wizard.fixed-box:hover {opacity: 1;}</style>';
				if ( is_ic_shortcode_integration( null, false ) ) {
					$box_content .= '<p class="initial-description">' . __( 'Would you like to remove the shortcode integration and try the configuration wizard?', 'ecommerce-product-catalog' ) . '</p><p><strong>' . sprintf( __( "If something goes wrong you will have to add %s to the product listing again.", 'ecommerce-product-catalog' ), '[' . ic_catalog_shortcode_name() . ']' ) . '</strong></p><p><strong>' . __( 'Do it only if the catalog layout doesn\'t match your theme or if some unwanted elements from the theme are displayed on catalog pages.', 'ecommerce-product-catalog' ) . '</strong></p>';
					$box_content .= '<p class="wp-core-ui" style="margin-top: 20px;"><a href="' . esc_url( add_query_arg( 'remove_shortcode_integration', '1' ) ) . '" class="button-primary">' . __( 'Remove the Shortcode and Start the Wizard', 'ecommerce-product-catalog' ) . '</a></p>';
				} else {
					$box_content .= '<p class="initial-description">' . __( 'Advanced Mode is temporary enabled for this page now.', 'ecommerce-product-catalog' ) . ' <strong>' . __( "Don't worry if it looks messy.", 'ecommerce-product-catalog' ) . '</strong> ' . __( 'Make some adjustments if necessary (you can change it at any time later).', 'ecommerce-product-catalog' ) . '</p>';
					$box_content .= '<p class="initial-description"><strong>' . __( 'Click start to proceed with layout adjustment.', 'ecommerce-product-catalog' ) . '</strong></p>';
//$box_content .= '<p>' . __( 'Please use the buttons below to let the script know if the Automatic Advanced Integration is done right.', 'ecommerce-product-catalog' ) . '</p>';
					$box_content          .= '<p class="wp-core-ui" style="margin-top: 20px;"><button class="button-primary start_section">' . __( 'Start', 'ecommerce-product-catalog' ) . '</button><a target="_blank" href="https://implecode.com/docs/ecommerce-product-catalog/theme-integration-wizard/#cam=default-mode&amp;key=top-message-video" class="button">' . __( 'Video Tutorial', 'ecommerce-product-catalog' ) . '</a></p>';
					$box_content          .= '<table class="styling-adjustments">';
					$integration_settings = self::settings();
					$box_content          .= '<tbody class="section_1 integration-section" style="display: none">';
					$box_content          .= '<tr><td colspan="2"><p style="margin-bottom: 5px">' . sprintf( __( '%1$sDecrease width%2$s to change the product page horizontal size, %1$sincrease padding%2$s to generate space around the content and %1$sset background & text colors%2$s to match your theme style', 'ecommerce-product-catalog' ), '<strong>', '</strong>' ) . ':</p></td></tr>';
					$box_content          .= implecode_settings_number( __( 'Width', 'ecommerce-product-catalog' ), 'container_width', $integration_settings['container_width'], '%', 0, null, __( 'In most cases you should decrease this number to match your template container size.', 'ecommerce-product-catalog' ), 0 );
					$box_content          .= implecode_settings_text_color( __( 'Background', 'ecommerce-product-catalog' ), 'container_bg', $integration_settings['container_bg'], null, 0, null, '{change: function(event, ui){ var hexcolor = jQuery( this ).wpColorPicker( "color" ); jQuery("#container").css("background", hexcolor); jQuery("#container").css("overflow", "hidden"); jQuery("#container").css("width", jQuery("input[name=\"container_width\"]").val()+"%");}}' );
					$box_content          .= implecode_settings_text_color( __( 'Text Color', 'ecommerce-product-catalog' ), 'container_text', $integration_settings['container_text'], null, 0, null, '{change: function(event, ui){ var hexcolor = jQuery( this ).wpColorPicker( "color" ); jQuery("#container *").css("color", hexcolor); }}' );
					$box_content          .= implecode_settings_number( __( 'Padding', 'ecommerce-product-catalog' ), 'container_padding', $integration_settings['container_padding'], 'px', 0, null, __( 'Increase this number to make space also on the top and bottom of the container. This is useful also if you are planning to enable the sidebar in next step.', 'ecommerce-product-catalog' ), 0 );
					$box_content          .= '</tbody>';

					if ( ! defined( 'AL_SIDEBAR_BASE_URL' ) ) {
						$box_content .= '<tbody class="section_2 integration-section" style="display: none">';
						$box_content .= '<tr><td colspan="2"><p style="margin-bottom: 5px">' . __( 'Select the sidebar side to enable it on your product pages.', 'ecommerce-product-catalog' ) . ':</p></td></tr>';

						$box_content .= implecode_settings_radio( __( 'Default Sidebar', 'ecommerce-product-catalog' ), 'default_sidebar', $integration_settings['default_sidebar'], array(
							'none'  => __( 'Disabled', 'ecommerce-product-catalog' ),
							'left'  => __( 'Left', 'ecommerce-product-catalog' ),
							'right' => __( 'Right', 'ecommerce-product-catalog' )
						), 0 );
						$box_content .= '<tr><td colspan="2"><p style="margin-top: 5px; margin-bottom: 5px; display: none;" class="integration-sidebar-info">' . __( 'If the sidebar is to close to the content, you can go back and adjust the padding.', 'ecommerce-product-catalog' ) . '</p></td></tr>';
						if ( $integration_settings['default_sidebar'] == 'none' ) {
							$box_content .= '<style>#catalog_sidebar {display: none;}</style>';
						}
						$box_content .= '</tbody>';
					}
					$box_content .= '<tbody class="section_3 integration-section" style="display: none">';
					$box_content .= '<tr><td colspan="2"><p style="margin-bottom: 5px">' . __( 'Disable unnecessary product page elements', 'ecommerce-product-catalog' ) . ':</p></td></tr>';

					$box_content .= implecode_settings_checkbox( __( 'Disable breadcrumbs', 'ecommerce-product-catalog' ), 'disable_breadcrumbs', $integration_settings['disable_breadcrumbs'], 0 );
					$box_content .= implecode_settings_checkbox( __( 'Disable Name', 'ecommerce-product-catalog' ), 'disable_name', $integration_settings['disable_name'], 0 );
					$box_content .= implecode_settings_checkbox( __( 'Disable Image', 'ecommerce-product-catalog' ), 'disable_image', $integration_settings['disable_image'], 0 );
					if ( function_exists( 'get_currency_settings' ) ) {
						$box_content .= implecode_settings_checkbox( __( 'Disable Price', 'ecommerce-product-catalog' ), 'disable_price', $integration_settings['disable_price'], 0 );
					}
					if ( function_exists( 'is_ic_sku_enabled' ) ) {
						$box_content .= implecode_settings_checkbox( __( 'Disable SKU', 'ecommerce-product-catalog' ), 'disable_sku', $integration_settings['disable_sku'], 0 );
					}
					if ( function_exists( 'is_ic_shipping_enabled' ) ) {
						$box_content .= implecode_settings_checkbox( __( 'Disable Shipping', 'ecommerce-product-catalog' ), 'disable_shipping', $integration_settings['disable_shipping'], 0 );
					}
					if ( function_exists( 'is_ic_attributes_enabled' ) ) {
						$box_content .= implecode_settings_checkbox( __( 'Disable Attributes', 'ecommerce-product-catalog' ), 'disable_attributes', $integration_settings['disable_attributes'], 0 );
					}
					$box_content .= '</tbody>';

					$box_content .= '</table>';

					$box_content .= '<style>#integration_wizard .al-box table tbody, #integration_wizard .al-box table tr, #integration_wizard .al-box table td {border: 0; background: transparent;} #integration_wizard .al-box table.styling-adjustments td, #integration_wizard .al-box table.styling-adjustments td label {vertical-align: middle;font-size: 14px; color: rgb(136, 136, 136) !important; text-align: left;} #integration_wizard .wp-picker-container {padding-top: 5px;}html #integration_wizard.fixed-box .al-box table input.wp-picker-clear {background: #ededed; transition: none; padding: 1px 6px; border: 1px solid #000; color: #000; margin: 0; margin-left: 6px;}#integration_wizard .wp-picker-holder{position: absolute;z-index: 99;}#integration_wizard p, #integration_wizard strong, #integration_wizard td, #integration_wizard h4, #integration_wizard span {color: #000 !important}</style>';

					$box_content .= '<div class="section_last integration-section" style="display: none">';
					$box_content .= '<p>' . __( 'Is everything looking fine now?', 'ecommerce-product-catalog' ) . '</p>';
					$box_content .= '<style>#integration_wizard .ic_spinner{background: url(' . admin_url() . '/images/spinner.gif) no-repeat;display: inline-block;
    opacity: 0.7;
    width: 20px;
    height: 20px;
    margin-left: 2px;
    vertical-align: middle;
    display: none;
	margin-right:3px;
	margin-left: -23px;}</style>';
					$box_content .= '<p class="wp-core-ui"><span class="ic_spinner"></span><a href="' . esc_url( add_query_arg( 'test_advanced', 'ok' ) ) . '" class="button-primary integration-ok">' . __( 'It\'s Fine', 'ecommerce-product-catalog' ) . '</a>';
					if ( is_ic_simple_mode() ) {
						$box_content .= '<a href="' . esc_url( add_query_arg( 'test_advanced', 'bad' ) ) . '" class="button-secondary">' . __( 'It\'s Broken', 'ecommerce-product-catalog' ) . '</a>';
					}
					$box_content .= '<button class="button-secondary show_third switch_section">' . __( 'Go Back', 'ecommerce-product-catalog' ) . '</button></p>';
					$box_content .= '</div>';
					$box_content .= '<p class="wp-core-ui" style="margin-top: 20px;"><button class="button-secondary show_prev_section switch_section" style="display: none">' . __( 'Go Back', 'ecommerce-product-catalog' ) . '</button><button class="button-primary show_next_section switch_section" style="display: none">' . __( 'Next', 'ecommerce-product-catalog' ) . '</button></p>';
				}
				echo '<div id="integration_wizard" class="integration_start ' . $class . '">' . implecode_info( $box_content, 0 ) . '</div>';
			} else if ( isset( $_GET['test_advanced'] ) && $_GET['test_advanced'] == 'bad' ) {
				$box_content .= '<p>' . __( 'It seems that Manual Theme Integration is needed in order to use Advanced Mode with your current theme.', 'ecommerce-product-catalog' ) . '</p>';
				$box_content .= '<h4>' . __( 'You Have 3 choices', 'ecommerce-product-catalog' ) . ':</h4>';
				$box_content .= '<ol>';
				$box_content .= '<li>' . __( 'Get the Manual Theme Integration done.', 'ecommerce-product-catalog' ) . '</li>';
				$box_content .= '<li>' . __( 'Keep using Simple Mode which is still functional.', 'ecommerce-product-catalog' ) . '</li>';
				$box_content .= '<li>' . sprintf( __( 'Use %s on your product listing page.', 'ecommerce-product-catalog' ), '[' . ic_catalog_shortcode_name() . ']' ) . '</li>';
				$box_content .= '<li>' . __( 'Switch the theme.', 'ecommerce-product-catalog' ) . '</li>';
				$box_content .= '</ol>';
				$box_content .= '<p>' . __( 'Please make your choice below or switch the theme.', 'ecommerce-product-catalog' ) . '</p>';
				$box_content .= '<p class="wp-core-ui"><a target="_blank" href="https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=integration-advanced-fail" class="button-primary">' . __( 'Free Theme Integration Guide', 'ecommerce-product-catalog' ) . '</a><a href="' . esc_url( add_query_arg( 'test_advanced', 'simple' ) ) . '" class="button-secondary">' . __( 'Use Simple Mode', 'ecommerce-product-catalog' ) . '</a></p>';
				self::enable_simple_mode();
				echo '<div id="integration_wizard" class="' . $class . '">' . implecode_warning( $box_content, 0 ) . '</div>';
			} else if ( isset( $_GET['test_advanced'] ) && $_GET['test_advanced'] == 'ok' ) {
				$box_content .= '<p>' . sprintf( __( 'Congratulations! %s is working on Advanced Mode now. You can go to admin and add the products to the catalog.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) . '</p>';
				$box_content .= '<p>' . sprintf( __( 'If you are a developer or would like to have full control on the product pages templates, make sure to see the %stemplates docs%s.', 'ecommerce-product-catalog' ), '<a href="https://implecode.com/docs/ecommerce-product-catalog/product-page-template/#cam=advanced-mode&key=integration-advanced-success-page-template">', '</a>' ) . '</p>';
				//$box_content .= '<p>' . __( 'You can switch between modes at any time in Product Settings.', 'ecommerce-product-catalog' ) . '</p>';
				$box_content .= '<p class="wp-core-ui"><a href="' . admin_url( 'edit.php?post_type=al_product' ) . '" class="button-primary">' . __( 'Go to Admin', 'ecommerce-product-catalog' ) . '</a>';
				//$box_content .= '<a target="_blank" href="https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=advanced-mode&key=integration-advanced-success" class="button-secondary">' . __( 'Free Theme Integration Guide', 'ecommerce-product-catalog' ) . '</a>';
				$box_content .= '</p>';
				self::enable_advanced_mode();
				echo '<div id="integration_wizard" class="' . $class . '">' . implecode_success( $box_content, 0 ) . '</div>';
			} else if ( isset( $_GET['test_advanced'] ) && $_GET['test_advanced'] == 'simple' ) {
				if ( is_ic_shortcode_integration() ) {
					$box_content .= '<p>' . __( 'You are using shortcode mode now.', 'ecommerce-product-catalog' ) . '</p>';
				} else {
					$box_content .= '<p>' . __( 'You are using simple mode now.', 'ecommerce-product-catalog' ) . '</p>';
				}
				$box_content .= '<p>' . __( 'You can switch between modes at any time in Product Settings.', 'ecommerce-product-catalog' ) . '</p>';
				$box_content .= '<p>' . __( 'Use the buttons below to try the advanced integration again or go to admin and start adding your products.', 'ecommerce-product-catalog' ) . '</p>';
				$box_content .= '<p class="wp-core-ui"><a href="' . admin_url( 'edit.php?post_type=al_product' ) . '" class="button-primary">' . __( 'Go to Admin', 'ecommerce-product-catalog' ) . '</a><a href="' . esc_url( add_query_arg( 'test_advanced', '1' ) ) . '" class="button-secondary">' . __( 'Restart Advanced Mode Test', 'ecommerce-product-catalog' ) . '</a></p>';
				self::enable_simple_mode();
				echo '<div id="integration_wizard" class="' . $class . '">' . implecode_success( $box_content, 0 ) . '</div>';
			}
		}
	}

	/**
	 * Returns wizard advanced mode settings
	 * @return array
	 */
	static function settings() {
		$archive_multiple_settings       = get_multiple_settings();
		$theme                           = get_option( 'template' );
		$settings['container_width']     = isset( $archive_multiple_settings['container_width'][ $theme ] ) ? $archive_multiple_settings['container_width'][ $theme ] : 100;
		$settings['container_bg']        = isset( $archive_multiple_settings['container_bg'][ $theme ] ) ? $archive_multiple_settings['container_bg'][ $theme ] : '';
		$settings['container_padding']   = isset( $archive_multiple_settings['container_padding'][ $theme ] ) ? $archive_multiple_settings['container_padding'][ $theme ] : 0;
		$settings['container_text']      = isset( $archive_multiple_settings['container_text'][ $theme ] ) ? $archive_multiple_settings['container_text'][ $theme ] : '';
		$settings['disable_breadcrumbs'] = isset( $archive_multiple_settings['enable_product_breadcrumbs'] ) && $archive_multiple_settings['enable_product_breadcrumbs'] == 1 ? 0 : 1;
		$settings['disable_name']        = isset( $archive_multiple_settings['disable_name'] ) ? $archive_multiple_settings['disable_name'] : 0;
		$settings['disable_image']       = is_ic_product_gallery_enabled() ? 0 : 1;
		$settings['disable_price']       = function_exists( 'is_ic_price_enabled' ) && is_ic_price_enabled() ? 0 : 1;
		$settings['disable_sku']         = function_exists( 'is_ic_sku_enabled' ) && is_ic_sku_enabled() ? 0 : 1;
		$settings['disable_shipping']    = function_exists( 'is_ic_shipping_enabled' ) && is_ic_shipping_enabled() ? 0 : 1;
		$settings['disable_attributes']  = function_exists( 'is_ic_attributes_enabled' ) && is_ic_attributes_enabled() ? 0 : 1;
		$settings['default_sidebar']     = isset( $archive_multiple_settings['default_sidebar'] ) ? $archive_multiple_settings['default_sidebar'] : 'none';

		return $settings;
	}

	/**
	 * Handles wizard avanced mode settings save
	 */
	static function save_wizard() {
		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ic-ajax-nonce' ) && current_user_can( 'manage_product_settings' ) ) {
			$archive_multiple_settings                                = get_multiple_settings();
			$product_page_settings                                    = get_product_page_settings();
			$theme                                                    = get_option( 'template' );
			$archive_multiple_settings['container_width'][ $theme ]   = intval( $_POST['container_width'] );
			$archive_multiple_settings['container_bg'][ $theme ]      = isset( $_POST['container_bg'] ) ? strval( $_POST['container_bg'] ) : '';
			$archive_multiple_settings['container_padding'][ $theme ] = intval( $_POST['container_padding'] );
			$archive_multiple_settings['container_text'][ $theme ]    = isset( $_POST['container_text'] ) ? strval( $_POST['container_text'] ) : '';
			$archive_multiple_settings['disable_name']                = intval( $_POST['disable_name'] );
			$archive_multiple_settings['disable_sku']                 = intval( $_POST['disable_sku'] );
			$archive_multiple_settings['default_sidebar']             = isset( $_POST['default_sidebar'] ) ? strval( $_POST['default_sidebar'] ) : '';
			$breadcrumbs                                              = intval( $_POST['disable_breadcrumbs'] );
			if ( $breadcrumbs == 1 ) {
				$archive_multiple_settings['enable_product_breadcrumbs'] = 0;
			} else {
				$archive_multiple_settings['enable_product_breadcrumbs'] = 1;
			}
			update_option( 'archive_multiple_settings', $archive_multiple_settings );

			if ( function_exists( 'get_currency_settings' ) ) {
				$price                     = intval( $_POST['disable_price'] );
				$product_currency_settings = get_currency_settings();
				if ( $price == 1 ) {
					$product_currency_settings['price_enable'] = 'off';
					update_option( 'product_currency_settings', $product_currency_settings );
				} else {
					$product_currency_settings['price_enable'] = 'on';
					update_option( 'product_currency_settings', $product_currency_settings );
				}
			}

			$image = intval( $_POST['disable_image'] );
			if ( $image == 1 ) {
				$product_page_settings['enable_product_gallery'] = 0;
				update_option( 'multi_single_options', $product_page_settings );
			} else {
				$product_page_settings['enable_product_gallery'] = 1;
				update_option( 'multi_single_options', $product_page_settings );
			}
			if ( function_exists( 'is_ic_shipping_enabled' ) ) {
				$shipping = intval( $_POST['disable_shipping'] );
				if ( $shipping == 1 ) {
					update_option( 'product_shipping_options_number', 0 );
				} else if ( ! is_ic_shipping_enabled() ) {
					update_option( 'product_shipping_options_number', 2 );
				}
			}
			if ( function_exists( 'is_ic_attributes_enabled' ) ) {
				$attributes = intval( $_POST['disable_attributes'] );
				if ( $attributes == 1 ) {
					update_option( 'product_attributes_number', 0 );
				} else if ( ! is_ic_attributes_enabled() ) {
					update_option( 'product_attributes_number', 3 );
				}
			}
		}

		echo 'done';

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	static function enqueue_sample_product_scripts() {
		if ( isset( $_GET['test_advanced'] ) ) {
			$product_id = sample_product_id();
			if ( intval( $product_id ) === get_the_ID() ) {
				wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array(
					'jquery-ui-draggable',
					'jquery-ui-slider',
					'jquery-touch-punch'
				), false, true );
				$picker_deps = array(
					'iris'
				);
				global $wp_version;
				if ( version_compare( $wp_version, 6.3, '>=' ) ) {
					$picker_deps[] = 'wp-blocks';
				}
				wp_enqueue_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), $picker_deps, false, true );
				$colorpicker_l10n = array(
					'clear'         => __( 'Clear' ),
					'defaultString' => __( 'Default' ),
					'pick'          => __( 'Select Color' ),
					'current'       => __( 'Current Color' )
				);
				wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );
				wp_enqueue_style( 'wp-color-picker' );
			}
		}
	}

	static function enable_advanced_mode( $hide_info = 0 ) {
		$archive_multiple_settings                               = get_multiple_settings();
		$theme                                                   = get_option( 'template' );
		$archive_multiple_settings['integration_type'][ $theme ] = 'advanced';
		update_option( 'archive_multiple_settings', $archive_multiple_settings );
		if ( $hide_info == 1 ) {
			$current_support_check           = ic_catalog_notices::theme_support_check();
			$current_support_check[ $theme ] = $theme;
			update_option( 'product_adder_theme_support_check', $current_support_check );
		}
	}

	static function enable_simple_mode() {
		$archive_multiple_settings                               = get_multiple_settings();
		$theme                                                   = get_option( 'template' );
		$archive_multiple_settings['integration_type'][ $theme ] = 'simple';
		update_option( 'archive_multiple_settings', $archive_multiple_settings );
		$current_support_check           = ic_catalog_notices::theme_support_check();
		$current_support_check[ $theme ] = '';
		update_option( 'product_adder_theme_support_check', $current_support_check );
	}

	static function get_real_integration_mode() {
		$archive_multiple_settings = get_option( 'archive_multiple_settings', get_default_multiple_settings() );
		if ( ! is_array( $archive_multiple_settings ) ) {
			$archive_multiple_settings = array();
		}
		$theme    = get_option( 'template' );
		$prev_int = ( isset( $archive_multiple_settings['integration_type'] ) && ! is_array( $archive_multiple_settings['integration_type'] ) ) ? $archive_multiple_settings['integration_type'] : 'simple';
		if ( isset( $archive_multiple_settings['integration_type'] ) && ! is_array( $archive_multiple_settings['integration_type'] ) ) {
			$archive_multiple_settings['integration_type'] = array();
		}
		$archive_multiple_settings['integration_type'][ $theme ] = isset( $archive_multiple_settings['integration_type'][ $theme ] ) ? $archive_multiple_settings['integration_type'][ $theme ] : $prev_int;

		return $archive_multiple_settings['integration_type'][ $theme ];
	}

	static function erase_integration_type_select() {
		$archive_multiple_settings = get_option( 'archive_multiple_settings', get_default_multiple_settings() );
		if ( isset( $archive_multiple_settings['integration_type'] ) && ! is_array( $archive_multiple_settings['integration_type'] ) ) {
			unset( $archive_multiple_settings['integration_type'] );
			unset( $archive_multiple_settings['container_width'] );
			unset( $archive_multiple_settings['container_bg'] );
			unset( $archive_multiple_settings['disable_name'] );
			unset( $archive_multiple_settings['container_padding'] );
			unset( $archive_multiple_settings['default_sidebar'] );
			update_option( 'archive_multiple_settings', $archive_multiple_settings );
			delete_option( 'product_adder_theme_support_check' );
			permalink_options_update();
		}
	}

	static function create_sample_product_with_redirect() {
		if ( isset( $_GET['create_sample_product_page'] ) ) {
			$sample_product_id = create_sample_product();
			$url               = get_permalink( $sample_product_id );
			$url               = esc_url_raw( add_query_arg( 'test_advanced', 1, $url ) );
			wp_redirect( $url );
			exit();
		}
	}

}

$ic_catalog_theme_integration = new ic_catalog_theme_integration;

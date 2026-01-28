<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product includes folder
 *
 * Here all plugin includes folder is defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
class ic_cat_activation_wizard extends ic_activation_wizard {

	private $display_notice = false, $notices = '';

	function __construct() {
		//add_action( 'in_admin_header', array( $this, 'notices' ) );
		add_action( 'in_admin_header', array( $this, 'disable_welcome_notices' ) );
		add_action( 'ic_epc_mode_selected', array( $this, 'activation_notices' ) );
		//add_action( 'admin_menu', array( $this, 'welcome_screen_page' ) );
		add_action( 'product_settings_menu', array( $this, 'welcome_screen_page' ) );
		add_action( 'admin_init', array( $this, 'handle_mode' ) );
		add_action( 'admin_init', array( $this, 'welcome_redirect' ) );
		add_action( 'ic_settings_top', array( $this, 'manage_general_tooltips' ) );
		add_action( 'shopping-cart-settings', array( $this, 'manage_cart_tooltips' ) );
		add_action( 'product-variations-settings', array( $this, 'manage_variations_tooltips' ) );
		add_action( 'product-attributes', array( $this, 'manage_attribute_tooltips' ) );
		add_action( 'front_end_labels_submenu', array( $this, 'manage_labels_tooltips' ) );
		add_action( 'product_listing_front_end_labels_settings', array( $this, 'manage_listing_labels_tooltips' ) );
		add_action( 'custom-design-submenu', array( $this, 'manage_design_tooltips' ) );
		add_action( 'single_product_design', array( $this, 'manage_single_design_tooltips' ) );
		add_action( 'ic_cat_extensions_page_start', array( $this, 'manage_extensions_tooltips' ) );
		add_action( 'ic_extensions_page_help_text', array( $this, 'manage_help_tooltips' ) );
		add_action( 'ic_products_edit_screen', array( $this, 'manage_products_tooltips' ) );
		add_filter( 'admin_product_details', array( $this, 'manage_product_tooltips' ) );
		add_action( 'ic_product_cat_fields', array( $this, 'manage_category_tooltips' ) );
		add_action( 'ic_affiliate_button_settings', array( $this, 'manage_affiliate_button_tooltips' ) );
	}

	function welcome_screen_page() {
		add_submenu_page( 'edit.php?post_type=al_product', __( "Getting Started", 'ecommerce-product-catalog' ), __( "Getting Started", 'ecommerce-product-catalog' ), apply_filters( 'see_product_settings_cap', 'manage_product_settings' ), 'implecode_welcome', array(
			$this,
			'welcome_screen_content'
		) );
	}

	function welcome_screen_content() {
		require_once( AL_BASE_PATH . '/includes/welcome/welcome-screen.php' );
	}

	function handle_mode() {
		if ( ! isset( $_GET['page'] ) ) {
			return;
		}
		if ( $_GET['page'] !== 'implecode_welcome' ) {
			return;
		}
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'ic_catalog_mode_selection' ) ) {
			return;
		}
		if ( ! empty( $_GET['mode'] ) ) {
			$mode = sanitize_text_field( $_GET['mode'] );
			$this->
			update_mode( $mode );
			$redirect_url = add_query_arg( 'selected_mode', $mode, admin_url( 'edit.php?post_type=al_product&page=implecode_welcome' ) );
			if ( $mode === 'store' ) {
				$redirect_url = add_query_arg( 'ic_catalog_activation_choice', 'price-on', $redirect_url );
			}
			$redirect_url = add_query_arg( '_wpnonce', wp_create_nonce( 'ic_catalog_activation_choice' ), $redirect_url );

			wp_safe_redirect( $redirect_url );
			exit;
		}
	}

	function disable_welcome_notices() {
		if ( is_ic_welcome_page() ) {
			remove_all_actions( 'admin_notices' );
		}
	}

	function update_mode( $mode ) {
		$settings = get_option( 'archive_multiple_settings', get_default_multiple_settings() );
		if ( ! is_array( $settings ) ) {
			$settings = array();
		}
		$settings['catalog_mode'] = $mode;
		update_option( 'archive_multiple_settings', $settings );
		$this->update_shipping( $mode );
		$this->update_price( $mode );
		$this->reset_wizard();
		if ( $mode === 'store' ) {
			update_option( 'IC_EPC_activation_message', 0, false );
		} elseif ( $mode === 'inquiry' ) {

		} elseif ( $mode === 'affiliate' ) {

		} elseif ( $mode === 'simple' ) {

		}
		$this->add_tooltips( $mode );
	}

	function add_tooltips( $mode ) {
		implecode_wp_tooltip_add( __( 'Catalog Configuration', 'ecommerce-product-catalog' ), __( 'Catalog settings are located here. Click on the settings link to finish the catalog configuration.', 'ecommerce-product-catalog' ), 'menu-posts-al_product ul li:nth-child(5)' );
		implecode_wp_tooltip_add( __( 'Important Settings', 'ecommerce-product-catalog' ), __( 'The most important general catalog settings are located here.', 'ecommerce-product-catalog' ), 'general-settings' );
		if ( $mode === 'store' || $mode === 'inquiry' ) {
			implecode_wp_tooltip_add( __( 'Cart Configuration', 'ecommerce-product-catalog' ), __( 'Click here to configure your cart settings, so it works correctly. Select the cart, checkout and thank you page. Set the email address for order notifications.', 'ecommerce-product-catalog' ), 'shopping-cart-settings' );
		}
		if ( $mode === 'affiliate' ) {
			implecode_wp_tooltip_add( __( 'Affiliate Button', 'ecommerce-product-catalog' ), __( 'Click here to configure your button settings, so it works as expected.', 'ecommerce-product-catalog' ), 'affiliate-button-settings' );
		}
	}

	function manage_general_tooltips() {
		implecode_wp_tooltip_hide( 'menu-posts-al_product ul li:nth-child(5)' );
		implecode_wp_tooltip_hide( 'general-settings' );
		implecode_wp_tooltip_add( __( 'Catalog Categories', 'ecommerce-product-catalog' ), __( 'Here you can manage categories and subcategories. You can add an unlimited number of categories and multiple levels of subcategories.', 'ecommerce-product-catalog' ), 'al_categories', true );
		implecode_wp_tooltip_add( __( 'Catalog Products', 'ecommerce-product-catalog' ), __( 'Here you can add, edit, and remove products. You can have an unlimited number of products.', 'ecommerce-product-catalog' ), 'al_products', true );
		implecode_wp_tooltip_add( __( 'Catalog Labels', 'ecommerce-product-catalog' ), __( 'Here you can change catalog text output.', 'ecommerce-product-catalog' ), 'names-settings', true );
		implecode_wp_tooltip_add( __( 'Catalog Design', 'ecommerce-product-catalog' ), __( 'Here you can configure the catalog design. Select the listing template and product page template. Change gallery options.', 'ecommerce-product-catalog' ), 'design-settings', true );
		implecode_wp_tooltip_add( __( 'Attributes Configuration', 'ecommerce-product-catalog' ), __( 'Here you can configure the attributes. Set the default values.', 'ecommerce-product-catalog' ), 'attributes-settings', true );
		implecode_wp_tooltip_add( __( 'Catalog Configuration', 'ecommerce-product-catalog' ), __( 'Set the currency custom symbol.', 'ecommerce-product-catalog' ), 'implecode_settings input[name="product_currency_settings[custom_symbol]"]', true );
		implecode_wp_tooltip_add( __( 'Catalog Listing Configuration', 'ecommerce-product-catalog' ), __( 'Use this option to select what should be shown on your main catalog page.', 'ecommerce-product-catalog' ), 'implecode_settings input[name="archive_multiple_settings[product_listing_cats]"]', true );
		implecode_wp_tooltip_add( __( 'Catalog Listing Configuration', 'ecommerce-product-catalog' ), __( 'Use the Show main catalog page content everywhere option if you are using a page builder to build your catalog design.', 'ecommerce-product-catalog' ), 'implecode_settings input[name="archive_multiple_settings[shortcode_mode][show_everywhere]"]', true );
		implecode_wp_tooltip_add( __( 'Catalog Listing Configuration', 'ecommerce-product-catalog' ), __( 'Select catalog template here.', 'ecommerce-product-catalog' ), 'implecode_settings select[name="archive_multiple_settings[shortcode_mode][template]"]', true );
		implecode_wp_tooltip_add( __( 'Catalog Listing Configuration', 'ecommerce-product-catalog' ), __( 'Select or view your main catalog page here', 'ecommerce-product-catalog' ), 'product_archive', true );

		implecode_wp_tooltip_add( __( 'Catalog Addons', 'ecommerce-product-catalog' ), __( 'Here you can find many useful add-ons and integrations.', 'ecommerce-product-catalog' ), 'extensions' );
		implecode_wp_tooltip_add( __( 'Help', 'ecommerce-product-catalog' ), __( 'Here you can can get some help.', 'ecommerce-product-catalog' ), 'help' );
	}

	function manage_attribute_tooltips() {
		implecode_wp_tooltip_hide( 'attributes-settings' );
	}

	function manage_design_tooltips() {
		implecode_wp_tooltip_hide( 'design-settings' );
		implecode_wp_tooltip_add( __( 'Catalog Design', 'ecommerce-product-catalog' ), __( 'Here you can configure the individual product page design and gallery settings.', 'ecommerce-product-catalog' ), 'single-design', true );
	}

	function manage_single_design_tooltips() {
		implecode_wp_tooltip_hide( 'single-design' );
	}

	function manage_labels_tooltips() {
		implecode_wp_tooltip_hide( 'names-settings' );
		implecode_wp_tooltip_add( __( 'Catalog Labels', 'ecommerce-product-catalog' ), __( 'Here you can change the labels that appear on the product listing.', 'ecommerce-product-catalog' ), 'archive-names', true );
	}

	function manage_listing_labels_tooltips() {
		implecode_wp_tooltip_hide( 'archive-names' );
	}

	function manage_cart_tooltips() {
		implecode_wp_tooltip_hide( 'shopping-cart-settings' );
		implecode_wp_tooltip_add( __( 'Variations Configuration', 'ecommerce-product-catalog' ), __( 'Here you can configure the variations.', 'ecommerce-product-catalog' ), 'product-variations-settings', true );
		implecode_wp_tooltip_add( __( 'Cart Configuration', 'ecommerce-product-catalog' ), __( 'Set the notification email address. The email to receive and send notifications can be the same.', 'ecommerce-product-catalog' ), 'implecode_settings input[name="shopping_cart_settings[receive_cart]"]', true );
		implecode_wp_tooltip_add( __( 'Cart Configuration', 'ecommerce-product-catalog' ), __( 'Select your cart pages. Each dropdown should point to a different page.', 'ecommerce-product-catalog' ), 'implecode_settings select[name="shopping_cart_settings[shopping_cart_page]"]', true );
		implecode_wp_tooltip_add( __( 'Product Variations', 'ecommerce-product-catalog' ), __( 'Here you can insert product variations so the user can choose additional product features when buying or asking for a quote.', 'ecommerce-product-catalog' ), 'al_cart_variations' );
	}

	function manage_variations_tooltips() {
		implecode_wp_tooltip_hide( 'product-variations-settings' );
	}

	function manage_extensions_tooltips() {
		implecode_wp_tooltip_hide( 'extensions' );
	}

	function manage_help_tooltips() {
		implecode_wp_tooltip_hide( 'help' );
	}

	function manage_products_tooltips() {
		implecode_wp_tooltip_hide( 'al_products' );
		implecode_wp_tooltip_add( __( 'Add new product', 'ecommerce-product-catalog' ), __( 'Click here to add new product.', 'ecommerce-product-catalog' ), 'add-new-product-page' );
		implecode_wp_tooltip_add( __( 'Add new product', 'ecommerce-product-catalog' ), __( 'Click here to add new product.', 'ecommerce-product-catalog' ), 'menu-posts-al_product ul li:nth-child(3)' );
		implecode_wp_tooltip_add( __( 'Products', 'ecommerce-product-catalog' ), __( 'Here all the products will show up. Use the Add new product button on the top to add a product.', 'ecommerce-product-catalog' ), 'posts-filter .wp-list-table #title' );
		implecode_wp_tooltip_add( __( 'Help Docs', 'ecommerce-product-catalog' ), __( 'Search for help here.', 'ecommerce-product-catalog' ), 'implecode_settings input[name="ic-settings-search"]' );
		implecode_wp_tooltip_add( __( 'Help from devs', 'ecommerce-product-catalog' ), __( 'Use the forum to get help on the integration, configuration, or any problem that you face.', 'ecommerce-product-catalog' ), 'implecode_settings .ic-settings-search .button-secondary' );
	}

	function manage_product_tooltips( $product_details ) {
		implecode_wp_tooltip_hide( 'add-new-product-page' );
		implecode_wp_tooltip_hide( 'menu-posts-al_product ul li:nth-child(3)' );
		implecode_wp_tooltip_add( __( 'Publish', 'ecommerce-product-catalog' ), __( 'Save the product so you can see how it shows up on the website.', 'ecommerce-product-catalog' ), 'publishing-action', true );
		implecode_wp_tooltip_add( __( 'Categories', 'ecommerce-product-catalog' ), __( 'Assign or add the product categories and subcategories here.', 'ecommerce-product-catalog' ), 'al_product-catdiv', true );
		implecode_wp_tooltip_add( __( 'Product Image', 'ecommerce-product-catalog' ), __( 'Here you can insert the product image.', 'ecommerce-product-catalog' ), 'postimagediv', true );
		implecode_wp_tooltip_add( __( 'Attributes', 'ecommerce-product-catalog' ), __( 'Here you can insert product attributes like color, size, or any other feature that the product has. With some add-ons, you will be able to sort, filter, and search products by these values.', 'ecommerce-product-catalog' ), 'al_product_attributes table.attributes', true );
		implecode_wp_tooltip_add( __( 'Media', 'ecommerce-product-catalog' ), __( 'Here you can add images, videos, or any other media to the description.', 'ecommerce-product-catalog' ), 'al_product_desc #wp-content-media-buttons button', true );
		implecode_wp_tooltip_add( __( 'Description', 'ecommerce-product-catalog' ), __( 'Insert the product description here. It will show up in a separate section or tab in the middle of the product page.', 'ecommerce-product-catalog' ), 'al_product_desc #content_ifr', true );
		implecode_wp_tooltip_add( __( 'Short Description', 'ecommerce-product-catalog' ), __( 'Insert the product short description here. It will show up on the top of the product page.', 'ecommerce-product-catalog' ), 'al_product_short_desc #excerpt_ifr', true );
		implecode_wp_tooltip_add( __( 'Product name', 'ecommerce-product-catalog' ), __( 'Insert the product name here.', 'ecommerce-product-catalog' ), 'post-body-content #titlewrap', true );

		return $product_details;
	}

	function manage_category_tooltips() {
		implecode_wp_tooltip_hide( 'al_categories' );
	}

	function manage_affiliate_button_tooltips() {
		implecode_wp_tooltip_hide( 'affiliate-button-settings' );
		implecode_wp_tooltip_add( __( 'Button URL', 'ecommerce-product-catalog' ), __( 'Check this checkbox if you want to define a separate button URL for each product.', 'ecommerce-product-catalog' ), 'implecode_settings input[name="ic_catalog_button[individual]"]', true );
	}

	function update_price( $mode ) {
		if ( ! function_exists( 'get_currency_settings' ) ) {
			$this->add_recommended_extension( 'price-field' );

			return;
		}
		$currency_settings                 = get_currency_settings();
		$currency_settings['price_enable'] = 'on';
		update_option( 'product_currency_settings', $currency_settings
		);
	}

	function update_shipping( $mode ) {
		if ( ! function_exists( 'is_ic_shipping_enabled' ) ) {
			return;
		}
		$enable_shipping = false;
		if ( $mode === 'store' ) {
			$enable_shipping = true;
		}
		if ( $enable_shipping && ! is_ic_shipping_enabled() ) {
			update_option( 'product_shipping_options_number', 2 );
		} else {
			update_option( 'product_shipping_options_number', 0 );
		}
	}

	function reset_wizard() {
		update_option( 'IC_EPC_activation_message', 1, false );
		delete_option( 'ic_cat_wizard_woo_choice' );
		delete_option( 'ic_hidden_notices' );
		delete_option( 'ic_hidden_boxes' );
		delete_option( 'ic_cat_recommended_extensions' );
		delete_option( 'implecode_wp_hidden_tooltips' );
		delete_option( 'implecode_wp_tooltips' );
	}

	function notices() {
		ob_start();
		$this->activation_notices();
		$this->notices = ob_get_clean();
		if ( $this->display_notice ) {
			remove_all_actions( 'ic_catalog_admin_notices' );
		}
		add_action( 'ic_catalog_admin_priority_notices', array( $this, 'activation_notices' ), - 1 );
	}

	function activation_notices() {
		if ( ! empty( $this->notices ) ) {
			echo $this->notices;

			return;
		}
		$this->display_notice = false;
		if ( is_ic_activation_notice() && $this->get_notice_status( 'notice-ic-catalog-activation' ) ) {
			delete_option( 'IC_EPC_activation_message' );
		}
		$response = array();
		if ( is_ic_activation_notice() && ! $this->get_notice_status( 'notice-ic-catalog-activation' ) ) {
			$this->display_notice = true;
			$not_complete         = true;
			$response             = $this->get_choice_response();
			$questions            = $this->response_to_question( $response );
			if ( ! empty( $questions ) ) {
				$header_name = IC_CATALOG_PLUGIN_NAME;
				if ( is_ic_welcome_page() && ! empty( $_GET['selected_mode'] ) ) {
					$header_name = '';
				}
				if ( ! empty( $header_name ) ) {
					$this->box_header( sprintf( __( '%s is active now!', 'ecommerce-product-catalog' ), $header_name ) );
				}
				if ( empty( $response['question'] ) ) {
					$this->box_paragraph( __( 'Make a choice below to continue with 1-minute catalog setup.', 'ecommerce-product-catalog' ) );
				}
			}
			if ( ! empty( $response['question'] ) ) {
				$this->box_paragraph( $response['question'] );
			}
			$form = false;
			if ( count( $questions ) === 1 ) {
				$form = $response['next_one'];
			}

			if ( empty( $questions ) ) {
				update_option( 'IC_EPC_activation_message_done', 1, false );
				$not_complete = false;

				$complete = apply_filters( 'ic_cat_activation_wizard_complete', true, $questions, $response );
				if ( $complete ) {
					if ( $this->any_recommended_extensions() && ! $this->get_notice_status( 'notice-ic-catalog-recommended' ) ) {
						if ( $this->show_woocommerce_notice() && $this->get_woo_choice() === 'woo-design' ) {
							remove_action( 'ic_cat_activation_wizard_bottom', array(
								'ic_catalog_notices',
								'getting_started_docs_info'
							) );
						}
						$this->recommended_extensions_box( false );
					} else {
						delete_option( 'IC_EPC_activation_message' );
						delete_option( 'IC_EPC_activation_message_done' );
						$catalog_names = get_catalog_names();
						$this->box_header( sprintf( __( "Congratulations! Your're ready to add %s.", 'ecommerce-product-catalog' ), $catalog_names['plural'] ) );

						$questions = array(
							admin_url( 'post-new.php?post_type=al_product' )                                                        => sprintf( __( 'Add First %s', 'ecommerce-product-catalog' ), $catalog_names['singular'] ),
							admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=csv' ) => sprintf( __( 'Import %s', 'ecommerce-product-catalog' ), $catalog_names['plural'] )
						);
					}
				}
			}
			if ( ! empty( $questions ) ) {
				$this->box_choice( $questions, $form );
			}
			if ( ! empty( $not_complete ) ) {
				$this->box_paragraph( __( 'You will be able to change your choice later in catalog settings or by reactivating the plugin.', 'ecommerce-product-catalog' ), true );
			}
			$this->wizard_box();
		} else if ( is_ic_catalog_admin_page() && $this->any_recommended_extensions() && ! $this->get_notice_status( 'notice-ic-catalog-recommended' ) ) {
			$this->display_notice = true;
			$this->recommended_extensions_box();
		} else if ( is_ic_new_product_screen() ) {
			$count         = ic_products_count();
			$sample_exists = ic_sample_page_exists();
			if ( ( $sample_exists && $count === 1 ) || ( ! $sample_exists && empty( $count ) ) ) {
				$this->display_notice = true;
				$catalog_names        = get_catalog_names();
				$this->box_header( sprintf( __( 'Add your first %s here.', 'ecommerce-product-catalog' ), $catalog_names['singular'] ) );
				$this->box_paragraph( __( 'By default you should see a two-column layout here:', 'ecommerce-product-catalog' ) );
				$optional   = '( ' . __( 'optional', 'ecommerce-product-catalog' ) . ' )';
				$left_side  = __( 'on the left side', 'ecommerce-product-catalog' );
				$right_side = __( 'on the right side', 'ecommerce-product-catalog' );
				$strong_op  = '<strong>';
				$strong_cl  = '</strong>';
				$list       = array(
					$strong_op . __( 'Name field', 'ecommerce-product-catalog' ) . $strong_cl . ' - ' . $left_side,
					$strong_op . __( 'Short description field', 'ecommerce-product-catalog' ) . $strong_cl . ' - ' . $left_side,
					$strong_op . __( 'Long Description field', 'ecommerce-product-catalog' ) . $strong_cl . ' - ' . $left_side,
					$strong_op . __( 'Attributes box', 'ecommerce-product-catalog' ) . $strong_cl . ' ' . $optional . ' - ' . $left_side . ' - ' . __( 'you can define their number in catalog settings.', 'ecommerce-product-catalog' ),
					$strong_op . __( 'Image box', 'ecommerce-product-catalog' ) . $strong_cl . ' - ' . $right_side,
					$strong_op . __( 'Publish box', 'ecommerce-product-catalog' ) . $strong_cl . ' - ' . $right_side,
					$strong_op . __( 'Categories box', 'ecommerce-product-catalog' ) . $strong_cl . ' - ' . $right_side,
					$strong_op . __( 'Price & SKU box', 'ecommerce-product-catalog' ) . $strong_cl . ' ' . $optional . ' - ' . $right_side
				);
				$this->box_list( $list );
				$this->box_paragraph( __( 'You can move all the boxes around to better suit your needs.', 'ecommerce-product-catalog' ) );
				$this->box_paragraph( __( 'This help box will disappear once you add your first product.', 'ecommerce-product-catalog' ), true );
				$this->wizard_box( '', 'style = "text-align: left;"' );
			}
		} else if ( is_ic_edit_product_screen() && ! $this->get_notice_status( 'notice-ic-catalog-activation' ) ) {
			$product_id = get_the_ID();
			if ( ! empty( $product_id ) && is_ic_product( $product_id ) && ic_product_exists( $product_id ) ) {
				$product_url          = get_permalink( $product_id );
				$url                  = admin_url( 'customize.php?autofocus[ control ] = ic_pc_integration_template  &url = ' . rawurldecode( $product_url ) . '&return = ' . rawurldecode( admin_url( 'edit.php?post_type = al_product' ) ) );
				$this->display_notice = true;
				$catalog_names        = get_catalog_names();
				$this->box_header( sprintf( __( "Let's customize your %s page layout.", 'ecommerce-product-catalog' ), $catalog_names['singular'] ) );
				$questions = array(
					$url => sprintf( __( 'Customize %s Page Layout', 'ecommerce-product-catalog' ), $catalog_names['singular'] )
				);
				$this->box_choice( $questions );
				$this->wizard_box( 'notice-ic-catalog-activation' );
			}
		} else if ( is_ic_product_list_admin_screen() && ! $this->get_notice_status( 'notice-ic-catalog-activation' ) ) {
			$this->display_notice = true;
			$listing_id           = intval( get_product_listing_id() );
			$catalog_names        = get_catalog_names();

			if ( ! empty( $listing_id ) ) {
				$listing_url        = get_permalink( $listing_id );
				$url                = admin_url( 'customize.php?autofocus[control]=ic_pc_archive_template&url=' . rawurlencode( $listing_url ) . '&return=' . rawurlencode( admin_url( 'edit.php?post_type=al_product&page=product-settings.php' ) ) );
				$message            = sprintf( __( 'Your main %s listing page is defined.', 'ecommerce-product-catalog' ), $catalog_names['singular'] );
				$message            .= ' ' . __( 'Template files will be used to display catalog pages.', 'ecommerce-product-catalog' );
				$button_label       = __( 'Customize Listing Layout', 'ecommerce-product-catalog' );
				$url_two            = admin_url( 'post.php?post=' . $listing_id . '&action=edit' );
				$button_label_two   = __( 'Edit Main Listing', 'ecommerce-product-catalog' );
				$url_three          = $listing_url;
				$button_label_three = __( 'Visit Main Listing', 'ecommerce-product-catalog' );
			} else {
				$message      = sprintf( __( 'Your main %s listing page is not selected. You can use shortcodes to display your products, but in most cases, it will be more convenient to use the templates.', 'ecommerce-product-catalog' ), $catalog_names['singular'] );
				$url          = admin_url( 'edit.php?post_type=al_product&page=product-settings.php' );
				$button_label = sprintf( __( 'Select Main %s Listing Page', 'ecommerce-product-catalog' ), $catalog_names['singular'] );
			}
			$this->box_paragraph( $message );
			$questions = array(
				$url => $button_label
			);
			if ( ! empty( $button_label_two ) && ! empty( $url_two ) ) {
				$questions[ $url_two ] = $button_label_two;
			}
			if ( ! empty( $button_label_three ) && ! empty( $url_three ) ) {
				$questions[ $url_three ] = $button_label_three;
			}
			$this->box_choice( $questions );
			$this->wizard_box( 'notice-ic-catalog-activation' );
		} else if ( is_ic_catalog_admin_page() && ! $this->get_notice_status( 'notice-ic-catalog-activation' ) ) {
			$this->display_notice = true;

			$this->box_header( __( 'Great! It looks like you are good to go with your catalog adventure.', 'ecommerce-product-catalog' ) );
			$this->box_paragraph( sprintf( __( 'If you have any questions or issues, feel free to post a %ssupport ticket%s.', 'ecommerce-product-catalog' ), '<a href=  "https://implecode.com/support/#cam=simple-mode&key=support-top">', '</a>' ) );
			$this->box_paragraph( sprintf( __( 'If you are looking for a customizable product theme, the free %sCatalog Me! theme%s is the way to go.', 'ecommerce-product-catalog' ), '<a href="' . admin_url( 'theme-install.php?search=Catalog+me%21' ) . '">', '</a>' ) );
			$this->box_paragraph( __( 'Make sure to visit the documentation for more tweaks and tricks.', 'ecommerce-product-catalog' ) );
			$questions  = array(
				'https://implecode.com/docs/ecommerce-product-catalog/getting-started/#cam=default-mode&key=getting-started' => __( 'Getting Started Guide', 'ecommerce-product-catalog' ),
				'https://implecode.com/docs/ecommerce-product-catalog/#cam=default-mode&key=docs'                            => __( 'Documentation', 'ecommerce-product-catalog' )
			);
			$listing_id = intval( get_product_listing_id() );
			if ( ! empty( $listing_id ) ) {
				$listing_url               = get_permalink( $listing_id );
				$questions[ $listing_url ] = __( 'Main Catalog Listing', 'ecommerce-product-catalog' );
			}
			$this->box_choice( apply_filters( 'ic_cat_activation_wizard_final_questions', $questions ) );

			$this->wizard_box( 'notice-ic-catalog-activation' );
		}
	}

	function activation_message_done() {
		$done = get_option( 'IC_EPC_activation_message_done', 0 );
		if ( ! empty( $done ) ) {
			return true;
		}

		return false;
	}

	function get_choice_response( $answer = null ) {
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'ic_catalog_activation_choice' ) ) {
			return 'Missing nonce';
		}
		$response = array();
		if ( $this->activation_message_done() ) {
			$answer = 'complete';
		} else if ( empty( $answer ) && ! empty( $_GET['ic_catalog_activation_choice'] ) ) {
			$answer = esc_attr( $_GET['ic_catalog_activation_choice'] );
		}
		switch ( $answer ) {
			case 'price-on':
				if ( function_exists( 'get_currency_settings' ) ) {
					$currency_settings = get_currency_settings();
				} else {
					$this->add_recommended_extension( 'price-field' );
				}
				if ( ! empty( $_GET['product_currency'] ) ) {
					if ( function_exists( 'get_currency_settings' ) ) {
						$currency_code = esc_attr( $_GET['product_currency'] );
						update_option( 'product_currency', $currency_code );
						$symbol = ic_cat_get_currency_symbol( $currency_code );
						if ( ! empty( $symbol ) ) {
							$currency_settings['custom_symbol'] = $symbol;
						}
					}
					if ( ! empty( $_GET['selected_mode'] ) ) {
						$response = $this->catalog_names_choice_response();
					} else {
						$response = $this->shipping_choice_response();
					}
				} else {
					if ( function_exists( 'get_currency_settings' ) ) {
						$currency_settings['price_enable'] = 'on';
						$response['one']                   = ic_cat_get_currency_switcher();
						$response['next_one']              = 'price-on';
						$response['question']              = __( 'Select your currency. You can also set a custom currency in catalog settings later.', 'ecommerce-product-catalog' );
					} else {
						if ( ! empty( $_GET['selected_mode'] ) ) {
							$response = $this->catalog_names_choice_response();
						} else {
							$response = $this->shipping_choice_response();
						}
					}
				}
				if ( function_exists( 'get_currency_settings' ) ) {
					update_option( 'product_currency_settings', $currency_settings );
				}
				break;
			case 'price-off':
				if ( function_exists( 'get_currency_settings' ) ) {
					$currency_settings                 = get_currency_settings();
					$currency_settings['price_enable'] = 'off';
					update_option( 'product_currency_settings', $currency_settings );
				}
				if ( ! empty( $_GET['selected_mode'] ) ) {
					$response = $this->catalog_names_choice_response();
				} else {
					$response = $this->shipping_choice_response();
				}
				break;
			case 'shipping-on':
				if ( function_exists( 'is_ic_shipping_enabled' ) ) {
					update_option( 'product_shipping_options_number', 1 );
				}
				$response = $this->catalog_names_choice_response();
				$this->add_recommended_extension( 'shipping-options' );

				break;
			case 'woo-design':
				//$response = $this->price_choice_response();
				$this->add_recommended_extension( 'catalog-booster-for-woocommerce' );
				update_option( 'ic_cat_wizard_woo_choice', 'woo-design' );

				break;
			case 'woo-separate':
				$response = $this->price_choice_response();
				update_option( 'ic_cat_wizard_woo_choice', 'woo-separate' );

				break;
			case 'shipping-off':
				if ( function_exists( 'is_ic_shipping_enabled' ) ) {
					update_option( 'product_shipping_options_number', 0 );
				}
				$response = $this->catalog_names_choice_response();
				break;
			case 'complete':
				if ( ! empty( $_GET['catalog_singular'] ) || ! empty( $_GET['catalog_plural'] ) ) {
					$archive_multiple_settings = get_multiple_settings();
					if ( ! empty( $_GET['catalog_singular'] ) ) {
						$archive_multiple_settings['catalog_singular'] = esc_attr( $_GET['catalog_singular'] );
					}
					if ( ! empty( $_GET['catalog_plural'] ) ) {
						$archive_multiple_settings['catalog_plural'] = esc_attr( $_GET['catalog_plural'] );
					}
					update_option( 'archive_multiple_settings', $archive_multiple_settings );
				}
				break;
			default:
				$response = apply_filters( 'ic_cat_activation_wizard_default_response', false );
				if ( ! $response ) {
					if ( $this->show_woocommerce_notice() ) {
						remove_action( 'ic_cat_activation_wizard_bottom', array(
							'ic_catalog_notices',
							'getting_started_docs_info'
						) );
						$response = $this->woo_choice_response();
					} else {
						$response = $this->price_choice_response();
					}
				}
		}

		return $response;
	}

	function shipping_choice_response() {
		$response['one']      = __( 'Shipping enabled for all or some products', 'ecommerce-product-catalog' );
		$response['next_one'] = 'shipping-on';
		$response['two']      = __( 'Shipping disabled completely', 'ecommerce-product-catalog' );
		$response['next_two'] = 'shipping-off';

		return $response;
	}

	function price_choice_response() {
		$response['one']      = __( 'Price enabled for all or some products', 'ecommerce-product-catalog' );
		$response['next_one'] = 'price-on';
		$response['two']      = __( 'Price disabled completely', 'ecommerce-product-catalog' );
		$response['next_two'] = 'price-off';

		return $response;
	}

	function woo_choice_response() {
		$response['one']      = __( 'Create Separate Catalog', 'ecommerce-product-catalog' );
		$response['next_one'] = 'woo-separate';
		$response['two']      = sprintf( __( 'Modify %s Design', 'ecommerce-product-catalog' ), 'WooCommerce' );
		$response['next_two'] = 'woo-design';
		$response['question'] = sprintf( __( 'It looks like you also have %s active. Make a choice below for correct setup.', 'ecommerce-product-catalog' ), 'WooCommerce' ) . '<br><br>' . sprintf( __( 'I would like to use %s to:', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME );

		return $response;
	}

	function catalog_names_choice_response() {
		$archive_multiple_settings = get_multiple_settings();
		$one                       = '<table style="margin:0 auto;"><tr>';
		$one                       .= implecode_settings_text( __( 'Catalog Singular Name', 'ecommerce-product-catalog' ), 'catalog_singular', $archive_multiple_settings['catalog_singular'], null, 0, null, __( 'Admin panel customisation setting. Change it to what you sell.', 'ecommerce-product-catalog' ) . ' ' . __( 'Examples: Service, Part, Flower, Photo', 'ecommerce-product-catalog' ) );
		$one                       .= implecode_settings_text( __( 'Catalog Plural Name', 'ecommerce-product-catalog' ), 'catalog_plural', $archive_multiple_settings['catalog_plural'], null, 0, null, __( 'Admin panel customisation setting. Change it to what you sell.', 'ecommerce-product-catalog' ) . ' ' . __( 'Examples: Services, Parts, Flowers, Photos', 'ecommerce-product-catalog' ) );
		$one                       .= '</tr></table>';
		$response['one']           = $one;
		$response['next_one']      = 'complete';
		$response['question']      = __( 'How would you like to name the products section in admin?', 'ecommerce-product-catalog' );
		$response['question']      .= '<br>' . __( 'This will personalize the admin experience. Leave the default values if you are not sure.', 'ecommerce-product-catalog' );

		return $response;
	}

	static function get_woo_choice() {
		$choice = get_option( 'ic_cat_wizard_woo_choice' );

		return $choice;
	}

	function welcome_redirect() {
		if ( ! get_transient( '_ic_welcome_screen_activation_redirect' ) ) {
			return;
		}

		delete_transient( '_ic_welcome_screen_activation_redirect' );

		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		wp_safe_redirect( admin_url( 'edit.php?post_type=al_product&page=implecode_welcome' ) );
		exit;
	}

}

$ic_cat_activation_wizard = new ic_cat_activation_wizard;

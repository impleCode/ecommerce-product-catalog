<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages general settings
 *
 * Here general settings are defined and managed.
 *
 * @version        1.1.4
 * @package        ecommerce-product-catalog/functions
 * @author        impleCode
 */
function general_menu() {
	if ( current_user_can( 'manage_product_settings' ) && function_exists( 'admin_url' ) ) {
		?>
        <a id="general-settings" class="nav-tab"
           href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings' ) ?>"><?php _e( 'General Settings', 'ecommerce-product-catalog' ); ?></a>
		<?php
	}
}

add_action( 'settings-menu', 'general_menu' );

function general_settings() {
	register_setting( 'product_settings', 'product_listing_url' );
	register_setting( 'product_settings', 'product_currency' );
	register_setting( 'product_settings', 'product_currency_settings' );
	register_setting( 'product_settings', 'product_archive' );
	register_setting( 'product_settings', 'enable_product_listing' );
	register_setting( 'product_settings', 'archive_multiple_settings' );
}

add_action( 'product-settings-list', 'general_settings' );

/**
 * Validates archive multiple settings
 *
 * @param array $new_value
 * @param array $old_value
 *
 * @return array
 */
function archive_multiple_settings_validation( $new_value ) {
	if ( function_exists( 'ic_force_clear_cache' ) ) {
		ic_force_clear_cache();
	}
	$product_slug = get_product_slug();
	if ( isset( $new_value['category_archive_url'] ) && $new_value['category_archive_url'] == $product_slug ) {
		$new_value['category_archive_url'] = $new_value['category_archive_url'] . '-1';
	}

	return apply_filters( 'ic_archive_multiple_settings_validation', $new_value );
}

/**
 * Validates product currency settings
 *
 * @param array $new_value
 *
 * @return array
 */
function product_currency_settings_validation( $new_value ) {
	if ( empty( $new_value ) ) {
		return $new_value;
	}
	if ( $new_value['th_sep'] == $new_value['dec_sep'] ) {
		if ( $new_value['th_sep'] == ',' ) {
			$new_value['th_sep'] = '.';
		} else {
			$new_value['th_sep'] = ',';
		}
	}

	return $new_value;
}

add_action( 'init', 'general_options_validation_filters' );

/**
 * Initializes validation filters for general settings
 *
 */
function general_options_validation_filters() {
	add_filter( 'pre_update_option_archive_multiple_settings', 'archive_multiple_settings_validation' );
	add_filter( 'pre_update_option_product_currency_settings', 'product_currency_settings_validation' );
}

function general_settings_content() {
	$submenu = isset( $_GET['submenu'] ) ? $_GET['submenu'] : '';
	?>
    <div class="overall-product-settings settings-wrapper" style="clear:both;">
        <div class="settings-submenu">
            <h3>
                <a id="general-settings" class="element current"
                   href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=general-settings' ) ?>"><?php _e( 'General Settings', 'ecommerce-product-catalog' ); ?></a>
				<?php do_action( 'general_submenu' ); ?>
            </h3>
        </div>

		<?php if ( $submenu == 'general-settings' or $submenu == '' ) { ?>
            <div class="setting-content submenu">
                <script>
                    jQuery('.settings-submenu a').removeClass('current');
                    jQuery('.settings-submenu a#general-settings').addClass('current');
                </script>
                <h2 style="display:inline-block;vertical-align:middle;"><?php _e( 'General Settings', 'ecommerce-product-catalog' ); ?></h2>
                <a style="display: inline-block;vertical-align: middle;margin-left: 10px;" class="button-secondary"
                   href="<?php echo esc_url( admin_url( 'edit.php?post_type=al_product&page=implecode_welcome' ) ) ?>"><?php _e( 'Configuration Wizard', 'ecommerce-product-catalog' ) ?></a>
                <a style="display: inline-block;vertical-align: middle;margin-left: 10px;" class="button-secondary"
                   href="<?php echo esc_url( admin_url( 'edit.php?post_type=al_product&page=system.php&reset_product_settings=1' ) ) ?>"><?php _e( 'Reset Catalog Settings', 'ecommerce-product-catalog' ) ?></a>
                <form method="post" action="options.php">
					<?php
					settings_fields( 'product_settings' );
					$create_new_button      = ic_create_new_listing_page();
					$enable_product_listing = get_option( 'enable_product_listing', 1 );
					//$product_listing_url		 = product_listing_url();
					$product_archive           = get_product_listing_id();
					$archive_multiple_settings = get_multiple_settings();
					$item_name                 = ic_catalog_item_name();
					$uc_item_name              = ic_ucfirst( $item_name );

					/*
					  $page_get					 = get_page_by_path( $product_listing_url );

					  if ( $product_archive != '' ) {
					  $new_product_listing_url = get_page_uri( $product_archive );
					  if ( $new_product_listing_url != '' ) {
					  update_option( 'product_listing_url', $new_product_listing_url );
					  } else {
					  update_option( 'product_listing_url', __( 'products', 'ecommerce-product-catalog' ) );
					  }
					  } else if ( !empty( $page_get->ID ) ) {
					  update_option( 'product_archive', $page_get->ID );
					  $product_archive = $page_get->ID;
					  } */
					$disabled = '';
					if ( ! is_advanced_mode_forced() || ic_is_woo_template_available() || is_ic_shortcode_integration() ) {
						$integration_panel_shown = 1;
						?>
                        <div class="ic-important-settings">
							<?php
							/*
							  implecode_info(
							  '<p>' . __( 'Select a page to display your products.', 'ecommerce-product-catalog' ) . '</p>' .
							  '<p>' . __( 'This is the most important setting. The selected page slug will be included in individual product urls.', 'ecommerce-product-catalog' ) . '</p>' .
							  '<p>' . sprintf( __( 'Place %s on this page.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() ) . '</p>'
							  );
							 *
							 */
							$tip = __( 'Select a page to display your products.', 'ecommerce-product-catalog' );
							$tip .= ' ' . __( 'The selected page will become a parent for each product.', 'ecommerce-product-catalog' );
							$tip .= ' ' . sprintf( __( 'Place %s on this page.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() );
							?>
                            <div></div>

                            <span><span style="vertical-align: middle;" title="<?php echo $tip ?>"
                                        class="dashicons dashicons-editor-help ic_tip"></span><?php _e( 'Main Catalog Page', 'ecommerce-product-catalog' ); ?>: </span>
							<?php
							if ( $enable_product_listing == 1 ) {
								$listing_url = product_listing_url();
								ic_select_page( 'product_archive', __( 'Default', 'ecommerce-product-catalog' ), $product_archive, true, $listing_url, 1, false, $create_new_button );
							} else {
								ic_select_page( 'product_archive', __( 'Default', 'ecommerce-product-catalog' ), $product_archive, true, false, 1, false, $create_new_button );
							}
							do_action( 'ic_after_main_catalog_page_setting_html' );
							?>

                            <h3><?php
								$theme = wp_get_theme();
								if ( $theme->exists() ) {
									echo __( 'Catalog Layout Integration with the theme', 'ecommerce-product-catalog' ) . ' (' . $theme->display( 'Name' ) . ')';
								}
								?>
                            </h3>
							<?php
							if ( ic_has_listing_shortcode() ) {
							if ( ! is_integraton_file_active() ) {
								$alert_message = sprintf( __( 'You have to remove the %s from the main catalog page to switch the integration method.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() );
								$alert_message .= '\r\n';
								$alert_message .= '\r\n';
								$alert_message .= __( 'How to do it?', 'ecommerce-product-catalog' );
								$alert_message .= '\r\n';
								$alert_message .= '1. ' . __( 'Click the Edit button near the Main Catalog Page option.', 'ecommerce-product-catalog' );
								$alert_message .= '\r\n';
								$alert_message .= '2. ' . sprintf( __( 'Remove the %s on the page edit screen.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() );
								$alert_message .= '\r\n';
								$alert_message .= '3. ' . sprintf( __( 'Save the page without the %s', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() );
								$alert_message .= '\r\n';
								$alert_message .= '4. ' . __( 'Get back here (refresh this page) and switch the layout mode again.', 'ecommerce-product-catalog' );
								?>
                                <script>
                                    jQuery(document).ready(function () {
                                        jQuery("input[name=ic-fake-empty-feature]").click(function () {
                                            alert("<?php echo $alert_message ?>");
                                            jQuery("input[name=ic-fake-empty-feature]").attr("checked", false);
                                            jQuery("input[name=ic-fake-empty-feature]:last-child").prop("checked", true);
                                        });
                                    });
                                </script>
                                <table>
									<?php
									$descriptions['simple']   = '<strong>' . __( 'Simple Mode', 'ecommerce-product-catalog' ) . '</strong> (' . __( 'recommended', 'ecommerce-product-catalog' ) . ') - ' . __( 'will use your theme page template with catalog custom styling applied.', 'ecommerce-product-catalog' );
									$descriptions['advanced'] = '<strong>' . __( 'Advanced Mode', 'ecommerce-product-catalog' ) . '</strong>' . ' - ' . __( 'fully customizable layout with the visual configuration wizard.', 'ecommerce-product-catalog' );
									$descriptions['theme']    = '<strong>' . __( 'Theme Mode', 'ecommerce-product-catalog' ) . '</strong>' . ' - ' . __( 'your theme default template files will be used to display catalog pages.', 'ecommerce-product-catalog' );
									implecode_settings_radio( __( 'Layout mode', 'ecommerce-product-catalog' ), 'ic-fake-empty-feature', 'simple', array(
										'advanced' => $descriptions['advanced'],
										'theme'    => $descriptions['theme'],
										'simple'   => $descriptions['simple']
									), 1, __( 'Simple mode is recommended. Choose Advanced Mode if you want more control over the layout or theme mode if your theme should control the layout. ', 'ecommerce-product-catalog' ), '<br>', "integration-mode-selection" );
									?>
                                </table>
							<?php
							}
							implecode_info( sprintf( __( 'You are currently using %s on your product listing to integrate the catalog with the theme.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() ) . '</p>' . '<p>' . sprintf( __( 'If you have any problems with catalog layout, you can remove the %s and use the theme integration wizard to fix it.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() ) . '</p>' );
							ic_shortcode_mode_settings();
							//echo sample_product_button( 'p', __( 'Remove the Shortcode and Start Visual Wizard', 'ecommerce-product-catalog' ), 'button' );
							} else if ( ic_is_woo_template_available() ) {
								echo '<p>' . __( 'If you have any problems with catalog layout, you can use the theme integration wizard to fix it.', 'ecommerce-product-catalog' ) . '</p>';
								echo sample_product_button( 'p', __( 'Advanced Mode Visual Wizard', 'ecommerce-product-catalog' ) );
							} else {
							if ( get_integration_type() == 'simple' ) {
								$disabled = 'disabled';
							}
							$theme = get_option( 'template' );
							//$selected	 = is_integration_mode_selected();
							/*
							  implecode_info(
							  '<p>' . sprintf( __( 'Place %s on the page where you would like the product list to be displayed.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() ) . '</p>' .
							  '<p>' . __( 'Simple mode is recommended.', 'ecommerce-product-catalog' ) . '</p>'
							  );
							 *
							 */
							?>
                                <table>
									<?php
									$descriptions['simple']   = '<strong>' . __( 'Simple Mode', 'ecommerce-product-catalog' ) . '</strong> (' . __( 'recommended', 'ecommerce-product-catalog' ) . ') - ' . __( 'will use your theme page template with catalog custom styling applied.', 'ecommerce-product-catalog' );
									$descriptions['advanced'] = '<strong>' . __( 'Advanced Mode', 'ecommerce-product-catalog' ) . '</strong>' . ' - ' . __( 'fully customizable layout with the visual configuration wizard.', 'ecommerce-product-catalog' );
									$descriptions['theme']    = '<strong>' . __( 'Theme Mode', 'ecommerce-product-catalog' ) . '</strong>' . ' - ' . __( 'your theme default template files will be used to display catalog pages.', 'ecommerce-product-catalog' );
									implecode_settings_radio( __( 'Layout mode', 'ecommerce-product-catalog' ), 'archive_multiple_settings[integration_type][' . $theme . ']', ic_catalog_theme_integration::get_real_integration_mode(), array(
										'advanced' => $descriptions['advanced'],
										'theme'    => $descriptions['theme'],
										'simple'   => $descriptions['simple']
									), 1, __( 'Simple mode is recommended. Choose Advanced Mode if you want more control over the layout or theme mode if your theme should control the layout. ', 'ecommerce-product-catalog' ), '<br>', "integration-mode-selection" );
									?>
                                </table>

                                <div class="simple_mode_settings">
									<?php
									implecode_info( __( 'Use the options below to adjust the output.', 'ecommerce-product-catalog' ), 1, 0, false );
									/*
									  implecode_info(
									  '<p>' . __( 'In simple mode you will have to use shortcodes to display category pages. The advanced mode will display them for you automatically.', 'ecommerce-product-catalog' ) . '</p>' .
									  '<p>' . __( 'Simple mode will use your theme layout so it can show some unwanted elements on product catalog pages.', 'ecommerce-product-catalog' ) . '</p>' .
									  '<p>' . sprintf( __( 'If you want to switch to advanced mode easily, please insert %s on your product listing page.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() ) . '</p>'
									  )
									 *
									 */
									?>
                                </div>
                                <div class="advanced_mode_settings" style="display: none">
									<?php
									implecode_info(
										'<p>' . sprintf( __( 'In Advanced Mode %s must figure out your theme markup to display products properly.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) . '</p>' .
										'<p>' . __( 'Use the button below to begin the easy auto adjustment.', 'ecommerce-product-catalog' ) . '</p>' .
										'<p>' . sprintf( __( 'If you have access to the server files, you can also use our %sTheme Integration Guide%s to achieve it quickly.', 'ecommerce-product-catalog' ), '<a href="https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=general-integration-info">', '</a>' ) . '</p>' .
										'<p>' . sprintf( __( 'If you have any problems with the integration, please insert %s on any page to display the catalog.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() ) . '</p>'
									);

									foreach ( $archive_multiple_settings['integration_type'] as $integration_theme => $value ) {
										if ( $integration_theme == $theme ) {
											continue;
										}
										echo '<input type="hidden" name="archive_multiple_settings[integration_type][' . $integration_theme . ']" value="' . $value . '">';
									}
									foreach ( $archive_multiple_settings['container_width'] as $container_width_theme => $value ) {
										if ( $container_width_theme == $theme ) {
											continue;
										}
										echo '<input type="hidden" name="archive_multiple_settings[container_width][' . $container_width_theme . ']" value="' . $value . '">';
									}
									foreach ( $archive_multiple_settings['container_bg'] as $container_bg_theme => $value ) {
										if ( $container_bg_theme == $theme ) {
											continue;
										}
										echo '<input type="hidden" name="archive_multiple_settings[container_bg][' . $container_bg_theme . ']" value="' . $value . '">';
									}
									foreach ( $archive_multiple_settings['container_padding'] as $container_width_padding => $value ) {
										if ( $container_width_padding == $theme ) {
											continue;
										}
										echo '<input type="hidden" name="archive_multiple_settings[container_padding][' . $container_width_padding . ']" value="' . $value . '">';
									}
									?>
                                    <table class="advanced_mode_settings_hidden">
                                        <tr>
                                            <td style="min-width: 250px"></td>
                                            <td></td>
                                        </tr>
										<?php
										implecode_settings_number( __( 'Catalog Container Width', 'ecommerce-product-catalog' ), 'archive_multiple_settings[container_width][' . $theme . ']', $archive_multiple_settings['container_width'][ $theme ], '%' );
										implecode_settings_text_color( __( 'Catalog Container Background', 'ecommerce-product-catalog' ), 'archive_multiple_settings[container_bg][' . $theme . ']', $archive_multiple_settings['container_bg'][ $theme ] );
										implecode_settings_number( __( 'Catalog Container Padding', 'ecommerce-product-catalog' ), 'archive_multiple_settings[container_padding][' . $theme . ']', $archive_multiple_settings['container_padding'][ $theme ], 'px' );
										if ( ! defined( 'AL_SIDEBAR_BASE_URL' ) ) {
											implecode_settings_radio( __( 'Default Sidebar', 'ecommerce-product-catalog' ), 'archive_multiple_settings[default_sidebar]', $archive_multiple_settings['default_sidebar'], array(
												'none'  => __( 'Disabled', 'ecommerce-product-catalog' ),
												'left'  => __( 'Left', 'ecommerce-product-catalog' ),
												'right' => __( 'Right', 'ecommerce-product-catalog' )
											) );
										}
										implecode_settings_checkbox( __( 'Disable Product Name', 'ecommerce-product-catalog' ), 'archive_multiple_settings[disable_name]', $archive_multiple_settings['disable_name'] );
										?>
                                    </table>
									<?php
									echo sample_product_button( 'p', __( 'Advanced Mode Visual Wizard', 'ecommerce-product-catalog' ) );
									/*
									  if ( get_integration_type() == 'advanced' ) {
									  echo sample_product_button( 'p', __( 'Restart Integration Wizard', 'ecommerce-product-catalog' ) );
									  }
									 *
									 */
									?>
                                </div>
                                <div class="theme_mode_settings" style="display: none">
									<?php
									implecode_info(
										'<p>' . __( 'In theme mode the catalog will use your theme layout for all the catalog pages.', 'ecommerce-product-catalog' ) . '</p>' .
										'<p>' . sprintf( __( 'If you have access to the server files, you can also use our %sTheme Integration Guide%s to have full control over the layout.', 'ecommerce-product-catalog' ), '<a href="https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=general-integration-info">', '</a>' ) . '</p>' .
										'<p>' . sprintf( __( 'If you have any problems with the integration, please insert %s on any page to display the catalog.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name() ) . '</p>'
									)
									?>
                                </div>
								<?php
								ic_shortcode_mode_settings();
							}
							?>
                        </div>
						<?php
					} else {
						if ( ! empty( $product_archive ) && $product_archive !== 'noid' ) {
							$url = get_edit_post_link( $product_archive );
						}
						if ( ! empty( $url ) ) {
							$message = sprintf( __( 'Add %1$s to the %2$smain catalog page%3$s to have more styling options.', 'ecommerce-product-catalog' ), ic_catalog_shortcode_name(), '<a href="' . $url . '">', '</a>' );
						} else {
							$url     = admin_url( 'post-new.php?post_type=page' );
							$message = sprintf( __( '%1$sCreate a main catalog listing page%2$s with %3$s to have more styling options.', 'ecommerce-product-catalog' ), '<a href="' . $url . '">', '</a>', ic_catalog_shortcode_name() );
						}
						implecode_info( $message );
					}
					do_action( 'ic_after_layout_integration_setting_html', $archive_multiple_settings );

					if ( file_exists( AL_BASE_PATH . '/modules/cart/index.php' ) ) {
						?>
                        <h3><?php _e( 'Catalog Mode', 'ecommerce-product-catalog' ); ?></h3>
                        <table><?php
							implecode_settings_radio( __( 'Catalog Mode', 'ecommerce-product-catalog' ), 'archive_multiple_settings[catalog_mode]', $archive_multiple_settings['catalog_mode'], array(
								'store'     => __( 'Web Store', 'ecommerce-product-catalog' ),
								'inquiry'   => __( 'Inquiry Catalog', 'ecommerce-product-catalog' ),
								'affiliate' => __( 'Affiliate Catalog', 'ecommerce-product-catalog' ),
								'simple'    => __( 'Simple Catalog', 'ecommerce-product-catalog' )
							), 1, __( 'Choose your usage scenario.', 'ecommerce-product-catalog' ) );
							?>
                        </table>
						<?php
					}
					?>
                    <h3><?php _e( 'Catalog Label', 'ecommerce-product-catalog' ); ?></h3>
                    <table><?php
						implecode_settings_text( __( 'Catalog Singular Name', 'ecommerce-product-catalog' ), 'archive_multiple_settings[catalog_singular]', $archive_multiple_settings['catalog_singular'], null, 1, null, __( 'Admin panel customisation setting. Change it to what you sell.', 'ecommerce-product-catalog' ) );
						implecode_settings_text( __( 'Catalog Plural Name', 'ecommerce-product-catalog' ), 'archive_multiple_settings[catalog_plural]', $archive_multiple_settings['catalog_plural'], null, 1, null, __( 'Admin panel customisation setting. Change it to what you sell.', 'ecommerce-product-catalog' ) );
						?>
                    </table>

                    <h3><?php _e( 'Main listing page', 'ecommerce-product-catalog' ); ?></h3><?php
					/* if ( $disabled == 'disabled' ) {
					  implecode_warning( sprintf( __( 'Product listing page is disabled with simple theme integration. See <a href="%s">Theme Integration Guide</a> to enable product listing page with pagination or use [show_products] shortcode on the page selected below.', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=product-listing' ) );
					  } */
					if ( ! ic_check_rewrite_compatibility() ) {
						implecode_warning( __( 'It seems that this page is already set to be a listing for different elements. Please change the product listing page to make sure that product pages work fine.<br><br>This is probably caused by other plugin being set to show items on the same page.', 'ecommerce-product-catalog' ) );
					}
					?>
                    <table>
                        <tr>
                            <td style="width: 180px;min-width:180px;">
                            </td>
                            <td></td>
                        </tr>
						<?php
						implecode_settings_checkbox( __( 'Enable Main Listing Page', 'ecommerce-product-catalog' ), 'enable_product_listing', $enable_product_listing, 1, sprintf( __( 'Disable and use %s shortcode to display the products.', 'ecommerce-product-catalog' ), '[show_products]' ) );
						if ( empty( $integration_panel_shown ) ) {
							?>
                            <tr>
                                <td>
                                    <span title="<?php _e( 'The page where the main product listing shows. Also this page slug will be included in product url.', 'ecommerce-product-catalog' ) ?>"
                                          class="dashicons dashicons-editor-help ic_tip"></span>
									<?php _e( 'Choose Main Listing Page', 'ecommerce-product-catalog' ); ?>:
                                </td>
                                <td><?php
									if ( $enable_product_listing == 1 ) {
										$listing_url = product_listing_url();
										ic_select_page( 'product_archive', __( 'Default', 'ecommerce-product-catalog' ), $product_archive, true, $listing_url );
									} else {
										ic_select_page( 'product_archive', __( 'Default', 'ecommerce-product-catalog' ), $product_archive, true );
									}
									?>
                                </td>
                            </tr> <?php
						}
						implecode_settings_number( __( 'Listing shows at most', 'ecommerce-product-catalog' ), 'archive_multiple_settings[archive_products_limit]', $archive_multiple_settings['archive_products_limit'], $item_name, 1, 1, __( 'You can also use shortcode with products_limit attribute to set this.', 'ecommerce-product-catalog' ), 1 );
						implecode_settings_radio( __( 'Main listing shows', 'ecommerce-product-catalog' ), 'archive_multiple_settings[product_listing_cats]', $archive_multiple_settings['product_listing_cats'], array(
							'off'              => $uc_item_name,
							'on'               => $uc_item_name . ' & ' . __( 'Main Categories', 'ecommerce-product-catalog' ),
							'cats_only'        => sprintf( __( 'Main Categories & Uncategorized %s', 'ecommerce-product-catalog' ), $uc_item_name ),
							'forced_cats_only' => __( 'Main Categories', 'ecommerce-product-catalog' )
						) );
						$sort_options = get_product_sort_options();
						implecode_settings_radio( __( 'Default order', 'ecommerce-product-catalog' ), 'archive_multiple_settings[product_order]', $archive_multiple_settings['product_order'], $sort_options, true, __( 'This is also the default setting for sorting drop-down.', 'ecommerce-product-catalog' ) );
						do_action( 'product_listing_page_settings' );
						?>
                    </table><?php
					//implecode_info(__('You can also use shortcode to show your products whenever you want on the website. Just paste on any page: [show_products] and you will display all products in place of the shortcode. <br><br>To show products from just one category, use: [show_products category="2"] where 2 is category ID (you can display several categories by inserting comma separated IDs). <br><br>To display products by IDs, use: [show_products product="5"], where 5 is product ID.', 'ecommerce-product-catalog'));
					?>
                    <div class="advanced_mode_settings_inline">
                        <h3><?php _e( 'Categories Settings', 'ecommerce-product-catalog' ); ?></h3><?php
						/*
						  if ( $disabled != '' ) {
						  implecode_warning( __( 'Automatic category pages are disabled with simple integration. Switch to Advanced Integration to enable category pages or use [show_products category="1"] (where "1" is category ID) on any page to show products from certain category.', 'ecommerce-product-catalog' ) );
						  }
						 *
						 */
						if ( ! ic_check_tax_rewrite_compatibility() ) {
							implecode_warning( __( 'It seems that this categories parent URL is already set to be a parent for different elements. Please change the Categories Parent URL to make sure that product category pages work fine.<br><br>This is probably caused by other plugin being set to show categories with the same parent.', 'ecommerce-product-catalog' ) );
						}
						?>
                        <table>
							<?php if ( is_ic_permalink_product_catalog() ) { ?>
                                <tr>
                                <td>
                                    <span title="<?php echo sprintf( __( 'By default, the category parent slug cannot be the same as for products. If you want them to be the same, please look for %s extension in the catalog extensions menu.', 'ecommerce-product-catalog' ), 'Smarter Product URLs' ) ?>"
                                          class="dashicons dashicons-editor-help ic_tip"></span>
									<?php _e( 'Categories Parent URL', 'ecommerce-product-catalog' ); ?>:
                                </td>
								<?php
								$site_url = site_url();
								$urllen   = strlen( $site_url );
								if ( $urllen > 25 ) {
									$site_url = ic_substr( $site_url, 0, 11 ) . '...' . ic_substr( $site_url, $urllen - 11, $urllen );
								}
								?>
                                <td class="longer">
									<?php echo $site_url ?>/<input type="text"
                                                                   name="archive_multiple_settings[category_archive_url]"
                                                                   title="<?php _e( 'Cannot be the same as product listing page slug', 'ecommerce-product-catalog' ) ?> (<?php echo get_product_slug() ?>)."
                                                                   id="category_archive_url"
                                                                   value="<?php echo urldecode( sanitize_title( $archive_multiple_settings['category_archive_url'] ) ); ?>"/>/<?php _e( 'category-name', 'ecommerce-product-catalog' ) ?>
                                    /
                                </td>
                                </tr><?php
							}
							implecode_settings_radio( __( 'Category Page shows', 'ecommerce-product-catalog' ), 'archive_multiple_settings[category_top_cats]', $archive_multiple_settings['category_top_cats'], array(
								'off'                => $uc_item_name,
								'on'                 => $uc_item_name . ' & ' . __( 'Subcategories', 'ecommerce-product-catalog' ),
								'only_subcategories' => __( 'Subcategories', 'ecommerce-product-catalog' )
							), 1, __( 'The main listing can show only products, top-level categories and products or only the categories. With the subcategories option selected the products will show up only if they are directly assigned to the category. If you want to display the products only on the bottom category level, please assign the products only to it (not to all categories in the tree).', 'ecommerce-product-catalog' ) );
							implecode_settings_radio( __( 'Categories Display', 'ecommerce-product-catalog' ), 'archive_multiple_settings[cat_template]', $archive_multiple_settings['cat_template'], array(
								'template' => __( 'Template', 'ecommerce-product-catalog' ),
								'link'     => __( 'URLs', 'ecommerce-product-catalog' )
							), true, __( 'Template option will display categories with the same listing theme as products. Link option will show categories as simple URLs without image.', 'ecommerce-product-catalog' ) );
							implecode_settings_checkbox( __( 'Disable Image on Category Page', 'ecommerce-product-catalog' ), 'archive_multiple_settings[cat_image_disabled]', $archive_multiple_settings['cat_image_disabled'], 1, __( 'If you disable the image, it will be only used for categories listing.', 'ecommerce-product-catalog' ) );
							implecode_settings_radio( __( 'Show Related', 'ecommerce-product-catalog' ), 'archive_multiple_settings[related]', $archive_multiple_settings['related'], array(
								'products'   => $uc_item_name,
								'categories' => __( 'Categories', 'ecommerce-product-catalog' ),
								'none'       => __( 'Nothing', 'ecommerce-product-catalog' )
							), 1, __( 'The related products or categories will be shown on the bottom of product pages.', 'ecommerce-product-catalog' ) );
							do_action( 'product_category_settings', $archive_multiple_settings );
							?>
                        </table>

                        <h3><?php _e( 'SEO Settings', 'ecommerce-product-catalog' ); ?></h3><?php
						/*
						  if ( $disabled != '' ) {
						  if ( $selected ) {
						  implecode_warning( sprintf( __( 'SEO settings are disabled with simple theme integration. See <a href="%s">Theme Integration Guide</a> to enable SEO settings.', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=seo-settings' ) );
						  } else {
						  implecode_warning( sprintf( __( 'SEO settings are disabled due to a lack of theme integration.%s', 'ecommerce-product-catalog' ), sample_product_button( 'p' ) ) );
						  }
						  }
						 *
						 */
						?>
                        <table>
							<?php
							implecode_settings_text( __( 'Archive SEO Title', 'ecommerce-product-catalog' ), 'archive_multiple_settings[seo_title]', $archive_multiple_settings['seo_title'], null, 1, null, __( 'The title tag for selected product listing page. If you are using separate SEO plugin you should set it there. E.g. in Yoast SEO look for it in Custom Post Types archive titles section.', 'ecommerce-product-catalog' ) );
							implecode_settings_checkbox( __( 'Enable SEO title separator', 'ecommerce-product-catalog' ), 'archive_multiple_settings[seo_title_sep]', $archive_multiple_settings['seo_title_sep'] );
							implecode_settings_checkbox( __( 'Enable Structured Data', 'ecommerce-product-catalog' ), 'archive_multiple_settings[enable_structured_data]', $archive_multiple_settings['enable_structured_data'], 1, __( 'Enable to show structured data on each single product page. Test it with Google’s Structured Data Testing Tool. You can modify the output with the structured-data.php template file.', 'ecommerce-product-catalog' ) )
							?>

                        </table>

                        <h3><?php _e( 'Breadcrumbs Settings', 'ecommerce-product-catalog' ); ?></h3><?php
						/*
						  if ( $disabled != '' ) {
						  if ( $selected ) {
						  implecode_warning( sprintf( __( 'Breadcrumbs are disabled with simple theme integration. See <a href="%s">Theme Integration Guide</a> to enable product breadcrumbs.', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=breadcrumbs-settings' ) );
						  } else {
						  implecode_warning( sprintf( __( 'Breadcrumbs are disabled due to a lack of theme integration.%s', 'ecommerce-product-catalog' ), sample_product_button( 'p' ) ) );
						  }
						  }
						 *
						 */
						?>
                        <table>
							<?php
							implecode_settings_checkbox( __( 'Enable Catalog Breadcrumbs', 'ecommerce-product-catalog' ), 'archive_multiple_settings[enable_product_breadcrumbs]', $archive_multiple_settings['enable_product_breadcrumbs'], 1, __( 'Shows a path to the currently displayed product catalog page with URLs to parent pages and correct schema markup for SEO.', 'ecommerce-product-catalog' ) );
							implecode_settings_text( __( 'Main listing breadcrumbs title', 'ecommerce-product-catalog' ), 'archive_multiple_settings[breadcrumbs_title]', $archive_multiple_settings['breadcrumbs_title'], null, 1, null, __( 'The title for main product listing in breadcrumbs.', 'ecommerce-product-catalog' ) );
							?>
                        </table>
                    </div>
					<?php do_action( 'general-settings', $archive_multiple_settings ); ?>
                    <p class="submit">
                        <input type="submit" class="button-primary"
                               value="<?php _e( 'Save changes', 'ecommerce-product-catalog' ); ?>"/>
                    </p>
                </form>
            </div>
            <div class="helpers">
                <div class="wrapper"><?php
					main_helper();
					doc_helper( __( 'shortcode', 'ecommerce-product-catalog' ), 'product-catalog-shortcodes' );
					doc_helper( __( 'sorting', 'ecommerce-product-catalog' ), 'product-order-settings' );
					//did_know_helper('support', __('You can get instant support by email','ecommerce-product-catalog'), 'https://implecode.com/wordpress/plugins/premium-support/')
					?>
                </div>
            </div>
			<?php
		}
		do_action( 'product-settings' );


		//permalink_options_update();
		?>
    </div>

	<?php
}

function ic_get_shortcode_mode_settings() {
	$settings                                          = get_multiple_settings();
	$settings['shortcode_mode']['show_everywhere']     = isset( $settings['shortcode_mode']['show_everywhere'] ) ? $settings['shortcode_mode']['show_everywhere'] : 0;
	$settings['shortcode_mode']['force_name']          = isset( $settings['shortcode_mode']['force_name'] ) ? $settings['shortcode_mode']['force_name'] : 0;
	$settings['shortcode_mode']['force_category_name'] = isset( $settings['shortcode_mode']['force_category_name'] ) ? $settings['shortcode_mode']['force_category_name'] : 0;
	$settings['shortcode_mode']['move_breadcrumbs']    = isset( $settings['shortcode_mode']['move_breadcrumbs'] ) ? $settings['shortcode_mode']['move_breadcrumbs'] : 0;
	$settings['shortcode_mode']['template']            = isset( $settings['shortcode_mode']['template'] ) ? $settings['shortcode_mode']['template'] : 0;

	return $settings['shortcode_mode'];
}

function ic_shortcode_mode_settings() {
	$settings = ic_get_shortcode_mode_settings();
	if ( is_ic_shortcode_integration() ) {
		echo '<table class="simple_mode_settings">';
		implecode_settings_checkbox( __( 'Show main catalog page content everywhere', 'ecommerce-product-catalog' ), 'archive_multiple_settings[shortcode_mode][show_everywhere]', $settings['show_everywhere'], 1, __( 'Check this if you want to display main catalog page content on every catalog page. For example, if you are using page builder on the main catalog page to design your catalog.', 'ecommerce-product-catalog' ) );
		implecode_settings_checkbox( __( 'Force product name display', 'ecommerce-product-catalog' ), 'archive_multiple_settings[shortcode_mode][force_name]', $settings['force_name'], 1, __( 'On some themes, the product name is missing on the product page, so you can use this to restore it. Uncheck this if you see duplicated product name on the product page.', 'ecommerce-product-catalog' ) );
		implecode_settings_checkbox( __( 'Force category name display', 'ecommerce-product-catalog' ), 'archive_multiple_settings[shortcode_mode][force_category_name]', $settings['force_category_name'], 1, __( 'On some themes, the category name is missing on the category page, so you can use this to restore it. Uncheck this if you see duplicated category name on the category page.', 'ecommerce-product-catalog' ) );
		if ( is_ic_breadcrumbs_enabled() ) {
			implecode_settings_checkbox( __( 'Move breadcrumbs to the top', 'ecommerce-product-catalog' ), 'archive_multiple_settings[shortcode_mode][move_breadcrumbs]', $settings['move_breadcrumbs'], 1, __( 'Breadcrumbs will be displayed before the page title. It may require some additional styling.', 'ecommerce-product-catalog' ) );
		}
		do_action( 'ic_shortcode_mode_settings_html', $settings );
		echo '</table>';
	} else {
		foreach ( $settings as $name => $value ) {
			echo '<input type="hidden" name="archive_multiple_settings[shortcode_mode][' . $name . ']" value="' . $value . '">';
		}
	}
}

function get_default_multiple_settings() {
	return array(
		'archive_products_limit'     => 12,
		'category_archive_url'       => 'products-category',
		'enable_product_breadcrumbs' => 0,
		'breadcrumbs_title'          => '',
		'seo_title'                  => '',
		'seo_title_sep'              => 1,
	);
}

function get_multiple_settings() {
	$archive_multiple_settings = get_option( 'archive_multiple_settings', get_default_multiple_settings() );
	if ( empty( $archive_multiple_settings ) || ! is_array( $archive_multiple_settings ) ) {
		$archive_multiple_settings = get_default_multiple_settings();
	}
	foreach ( $archive_multiple_settings as $settings_key => $settings_value ) {
		if ( ! is_array( $settings_value ) ) {
			$archive_multiple_settings[ $settings_key ] = sanitize_text_field( $settings_value );
		}
	}
	$theme    = get_option( 'template' );
	$prev_int = 'simple';
	if ( ! isset( $archive_multiple_settings['integration_type'] ) || ! is_array( $archive_multiple_settings['integration_type'] ) ) {
		if ( class_exists( 'ic_catalog_notices' ) ) {
			$support_check = ic_catalog_notices::theme_support_check();
		}
		if ( ! empty( $support_check[ $theme ] ) ) {
			$prev_int = isset( $archive_multiple_settings['integration_type'] ) ? $archive_multiple_settings['integration_type'] : 'simple';
		}
		$archive_multiple_settings['integration_type'] = array();
	}

	if ( is_advanced_mode_forced() || ( isset( $_GET['test_advanced'] ) && ( $_GET['test_advanced'] == 1 || $_GET['test_advanced'] == 'ok' ) ) ) {
		$archive_multiple_settings['integration_type'][ $theme ] = 'advanced';
	} else {
		$archive_multiple_settings['integration_type'][ $theme ] = isset( $archive_multiple_settings['integration_type'][ $theme ] ) ? $archive_multiple_settings['integration_type'][ $theme ] : $prev_int;
	}

	$archive_multiple_settings['catalog_mode']               = isset( $archive_multiple_settings['catalog_mode'] ) ? $archive_multiple_settings['catalog_mode'] : 'simple';
	$archive_multiple_settings['disable_sku']                = isset( $archive_multiple_settings['disable_sku'] ) ? $archive_multiple_settings['disable_sku'] : '';
	$archive_multiple_settings['disable_mpn']                = isset( $archive_multiple_settings['disable_mpn'] ) ? $archive_multiple_settings['disable_mpn'] : '';
	$archive_multiple_settings['seo_title_sep']              = isset( $archive_multiple_settings['seo_title_sep'] ) ? $archive_multiple_settings['seo_title_sep'] : '';
	$archive_multiple_settings['seo_title']                  = isset( $archive_multiple_settings['seo_title'] ) ? $archive_multiple_settings['seo_title'] : '';
	$archive_multiple_settings['category_archive_url']       = isset( $archive_multiple_settings['category_archive_url'] ) ? $archive_multiple_settings['category_archive_url'] : 'products-category';
	$archive_multiple_settings['category_archive_url']       = empty( $archive_multiple_settings['category_archive_url'] ) ? 'products-category' : $archive_multiple_settings['category_archive_url'];
	$archive_multiple_settings['product_listing_cats']       = isset( $archive_multiple_settings['product_listing_cats'] ) ? $archive_multiple_settings['product_listing_cats'] : 'on';
	$archive_multiple_settings['category_top_cats']          = isset( $archive_multiple_settings['category_top_cats'] ) ? $archive_multiple_settings['category_top_cats'] : 'on';
	$archive_multiple_settings['cat_template']               = isset( $archive_multiple_settings['cat_template'] ) ? $archive_multiple_settings['cat_template'] : 'template';
	$archive_multiple_settings['product_order']              = isset( $archive_multiple_settings['product_order'] ) ? $archive_multiple_settings['product_order'] : 'newest';
	$archive_multiple_settings['catalog_plural']             = ! empty( $archive_multiple_settings['catalog_plural'] ) ? $archive_multiple_settings['catalog_plural'] : DEF_CATALOG_PLURAL;
	$archive_multiple_settings['catalog_singular']           = ! empty( $archive_multiple_settings['catalog_singular'] ) ? $archive_multiple_settings['catalog_singular'] : DEF_CATALOG_SINGULAR;
	$archive_multiple_settings['cat_image_disabled']         = isset( $archive_multiple_settings['cat_image_disabled'] ) ? $archive_multiple_settings['cat_image_disabled'] : '';
	$archive_multiple_settings['container_width']            = isset( $archive_multiple_settings['container_width'] ) ? $archive_multiple_settings['container_width'] : 100;
	$archive_multiple_settings['container_bg']               = isset( $archive_multiple_settings['container_bg'] ) ? $archive_multiple_settings['container_bg'] : '';
	$archive_multiple_settings['container_padding']          = isset( $archive_multiple_settings['container_padding'] ) ? $archive_multiple_settings['container_padding'] : 0;
	$archive_multiple_settings['container_text']             = isset( $archive_multiple_settings['container_text'] ) ? $archive_multiple_settings['container_text'] : '';
	$archive_multiple_settings['disable_name']               = isset( $archive_multiple_settings['disable_name'] ) ? $archive_multiple_settings['disable_name'] : '';
	$archive_multiple_settings['default_sidebar']            = isset( $archive_multiple_settings['default_sidebar'] ) ? $archive_multiple_settings['default_sidebar'] : 'none';
	$archive_multiple_settings['related']                    = isset( $archive_multiple_settings['related'] ) ? $archive_multiple_settings['related'] : 'products';
	$archive_multiple_settings['breadcrumbs_title']          = isset( $archive_multiple_settings['breadcrumbs_title'] ) ? $archive_multiple_settings['breadcrumbs_title'] : $archive_multiple_settings['catalog_plural'];
	$archive_multiple_settings['enable_product_breadcrumbs'] = isset( $archive_multiple_settings['enable_product_breadcrumbs'] ) ? $archive_multiple_settings['enable_product_breadcrumbs'] : '';
	$archive_multiple_settings['enable_structured_data']     = isset( $archive_multiple_settings['enable_structured_data'] ) ? $archive_multiple_settings['enable_structured_data'] : '';


	$prev_container_width   = ! is_array( $archive_multiple_settings['container_width'] ) ? $archive_multiple_settings['container_width'] : 100;
	$prev_container_bg      = ! is_array( $archive_multiple_settings['container_bg'] ) ? $archive_multiple_settings['container_bg'] : '';
	$prev_container_padding = ! is_array( $archive_multiple_settings['container_padding'] ) ? $archive_multiple_settings['container_padding'] : 0;

	if ( ! is_array( $archive_multiple_settings['container_width'] ) ) {
		$archive_multiple_settings['container_width'] = array();
	}
	if ( ! is_array( $archive_multiple_settings['container_bg'] ) ) {
		$archive_multiple_settings['container_bg'] = array();
	}
	if ( ! is_array( $archive_multiple_settings['container_padding'] ) ) {
		$archive_multiple_settings['container_padding'] = array();
	}
	if ( ! is_array( $archive_multiple_settings['container_text'] ) ) {
		$archive_multiple_settings['container_text'] = array();
	}

	if ( ! isset( $archive_multiple_settings['container_width'][ $theme ] ) ) {
		$archive_multiple_settings['container_width'][ $theme ] = $prev_container_width;
	}
	if ( ! isset( $archive_multiple_settings['container_bg'][ $theme ] ) ) {
		$archive_multiple_settings['container_bg'][ $theme ] = $prev_container_bg;
	}
	if ( ! isset( $archive_multiple_settings['container_padding'][ $theme ] ) ) {
		$archive_multiple_settings['container_padding'][ $theme ] = $prev_container_padding;
	}
	if ( ! isset( $archive_multiple_settings['container_text'][ $theme ] ) ) {
		$archive_multiple_settings['container_text'][ $theme ] = $prev_container_padding;
	}

	return apply_filters( 'catalog_multiple_settings', $archive_multiple_settings );
}

function get_catalog_names( $which = null ) {
	$multiple_settings = get_multiple_settings();
	$names['singular'] = $multiple_settings['catalog_singular'];
	$names['plural']   = $multiple_settings['catalog_plural'];
	$names             = apply_filters( 'product_catalog_names', $names );
	if ( ! empty( $which ) && isset( $names[ $which ] ) ) {
		return $names[ $which ];
	}

	return $names;
}

function get_integration_type() {
	$type = ic_get_global( 'ic_integration_type' );
	if ( empty( $type ) ) {
		$settings = get_multiple_settings();
		$theme    = get_option( 'template' );
		$type     = apply_filters( 'ic_catalog_integration_type', $settings['integration_type'][ $theme ] );
		ic_save_global( 'ic_integration_type', $type );
	}

	return $type;
}

function get_product_sort_options() {
	return apply_filters( 'product_sort_options', array(
		'newest'       => __( 'Sort by Newest', 'ecommerce-product-catalog' ),
		'product-name' => __( 'Sort by Name', 'ecommerce-product-catalog' )
	) );
}

function get_product_listing_id() {
	if ( get_post_type() ) {
		$cache_key = 'listing_id';
	} else {
		$cache_key = 'pre_listing_id';
	}
	$cached = ic_get_global( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}
	$product_archive_created = get_option( 'product_archive_page_id', '0' );
	if ( ! empty( $product_archive_created ) && false === get_post_status( $product_archive_created ) ) {
		$product_archive_created = '0';
		update_option( 'product_archive_page_id', $product_archive_created );
	}
	$listing_id        = get_option( 'product_archive', $product_archive_created );
	$return_listing_id = apply_filters( 'product_listing_id', $listing_id );
	ic_save_global( $cache_key, $return_listing_id );

	return $return_listing_id;
}

/**
 * Returns product listing URL
 *
 * @return string
 */
function product_listing_url() {
	$listing_url = '';
	if ( /* is_ic_permalink_product_catalog() && */ 'noid' != ( $page_id = get_product_listing_id() ) ) {
		if ( ! empty( $page_id ) ) {
			$listing_url = ic_get_permalink( $page_id );
		}
	}
	if ( empty( $listing_url ) ) {
		$listing_url = get_post_type_archive_link( 'al_product' );
	}

	return apply_filters( 'product_listing_url', $listing_url );
}

function ic_get_product_listing_status() {
	if ( 'noid' != ( $page_id = get_product_listing_id() ) ) {
		if ( ! empty( $page_id ) ) {
			$status = get_post_status( $page_id );

			return $status;
		}
	}

	return 'publish';
}

function get_product_slug() {
	$page_id = get_product_listing_id();
	if ( $page_id !== 'noid' ) {
		$slug = urldecode( untrailingslashit( get_page_uri( $page_id ) ) );
	}
	if ( empty( $slug ) ) {
		$settings = get_multiple_settings();
		$slug     = ic_sanitize_title( $settings['catalog_plural'] );
	}

	return apply_filters( 'product_slug', $slug );
}

function ic_sanitize_title( $str, $keep_percent_sign = false, $force_lowercase = true, $sanitize_slugs = true ) {
	if ( ! ic_is_multibyte( $str ) ) {
		return sanitize_title( $str );
	}
	// Remove accents & entities
	$clean = remove_accents( $str );
	$clean = str_replace( array( '&lt', '&gt', '&amp' ), '', $clean );

	$percent_sign   = ( $keep_percent_sign ) ? "\%" : "";
	$sanitize_regex = "/[^\p{Xan}a-zA-Z0-9{$percent_sign}\/_\.|+, -]/ui";
	$clean          = preg_replace( $sanitize_regex, '', $clean );
	$clean          = ( $force_lowercase ) ? strtolower( $clean ) : $clean;

	// Remove amperand
	$clean = str_replace( array( '%26', '&' ), '', $clean );

	// Remove special characters
	if ( $sanitize_slugs !== false ) {
		$clean = preg_replace( "/[\s|+-]+/", "-", $clean );
		$clean = preg_replace( "/[,]+/", "", $clean );
		$clean = preg_replace( '/([\.]+)(?![a-z]{3,4}$)/i', '', $clean );
		$clean = preg_replace( '/([-\s+]\/[-\s+])/', '-', $clean );
	} else {
		$clean = preg_replace( "/[\s]+/", "-", $clean );
	}

	// Remove widow & duplicated slashes
	$clean = preg_replace( '/([-]*[\/]+[-]*)/', '/', $clean );
	$clean = preg_replace( '/([\/]+)/', '/', $clean );

	// Trim slashes, dashes and whitespaces
	$clean = trim( $clean, " /-" );

	return $clean;
}

add_action( 'updated_option', 'rewrite_permalinks_after_update', 10, 3 );

function rewrite_permalinks_after_update( $option, $old_value, $new_value ) {
	if ( $option == 'product_archive' || $option == 'archive_multiple_settings' ) {
		if ( $option == 'product_archive' ) {
			$old_id = intval( $old_value );
			$new_id = intval( $new_value );
			if ( ! empty( $new_id ) && $old_id !== $new_id ) {
				$auto_add = false;

				if ( ! empty( $old_id ) ) {
					$old_post = get_post( $old_id );
					if ( ! empty( $old_post->post_content ) && ic_has_page_catalog_shortcode( $old_post ) ) {
						$auto_add = true;
					}
				} else if ( ! empty( $new_id ) ) {
					$auto_add = true;
				}
				if ( $auto_add && ! empty( $new_id ) ) {
					$new_post = get_post( $new_id );
					if ( isset( $new_post->post_content ) && ! ic_has_page_catalog_shortcode( $new_post ) ) {
						$new_post->post_content = $new_post->post_content . ic_catalog_shortcode();
						wp_update_post( $new_post );
					}
				}
			}
		}
		permalink_options_update();
	}
}

function ic_catalog_shortcode_name() {
	return apply_filters( 'ic_catalog_shortcode_name', '[show_product_catalog]' );
}

function ic_catalog_shortcode() {
	return apply_filters( 'ic_catalog_default_listing_content', '[show_product_catalog]' );
}

function ic_create_new_listing_page() {
	$button = '';
	if ( ! empty( $_GET['create_new_listing_page'] ) ) {
		create_products_page();
	}
	$listing_id = get_product_listing_id();
	if ( empty( $listing_id ) || $listing_id === 'noid' ) {
		$button = ' <a class="button button-small" style="vertical-align: middle;" href="' . admin_url( 'edit.php?post_type=al_product&page=product-settings.php&create_new_listing_page=1' ) . '">' . __( 'Create New', 'ecommerce-product-catalog' ) . '</a>';
	}

	return $button;
}

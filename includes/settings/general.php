<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages general settings
 *
 * Here general settings are defined and managed.
 *
 * @version        1.1.4
 * @package        ecommerce-product-catalog/functions
 * @author        Norbert Dreszer
 */
function general_menu() {
	?>
	<a id="general-settings" class="nav-tab" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings' ) ?>"><?php _e( 'General Settings', 'ecommerce-product-catalog' ); ?></a><?php
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
 * @return array
 */
function archive_multiple_settings_validation( $new_value ) {
	$product_slug = get_product_slug();
	if ( $new_value[ 'category_archive_url' ] == $product_slug ) {
		$new_value[ 'category_archive_url' ] = $new_value[ 'category_archive_url' ] . '-1';
	}
	return $new_value;
}

/**
 * Validates product currency settings
 *
 * @param array $new_value
 * @return array
 */
function product_currency_settings_validation( $new_value ) {
	if ( $new_value[ 'th_sep' ] == $new_value[ 'dec_sep' ] ) {
		if ( $new_value[ 'th_sep' ] == ',' ) {
			$new_value[ 'th_sep' ] = '.';
		} else {
			$new_value[ 'th_sep' ] = ',';
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
	$submenu = isset( $_GET[ 'submenu' ] ) ? $_GET[ 'submenu' ] : '';
	?>
	<div class="overall-product-settings settings-wrapper" style="clear:both;">
		<div class="settings-submenu">
			<h3>
				<a id="general-settings" class="element current"
				   href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=general-settings' ) ?>"><?php _e( 'General Settings', 'ecommerce-product-catalog' ); ?></a>
				   <?php do_action( 'general_submenu' ); ?>
			</h3>
		</div>

		<?php if ( $submenu == 'general-settings' OR $submenu == '' ) { ?>
			<div class="setting-content submenu">
				<script>
		            jQuery( '.settings-submenu a' ).removeClass( 'current' );
		            jQuery( '.settings-submenu a#general-settings' ).addClass( 'current' );
				</script>
				<h2><?php _e( 'General Settings', 'ecommerce-product-catalog' ); ?></h2>

				<form method="post" action="options.php">
					<?php
					settings_fields( 'product_settings' );
					$enable_product_listing		 = get_option( 'enable_product_listing', 1 );
					//$product_listing_url		 = product_listing_url();
					$product_archive			 = get_product_listing_id();
					$archive_multiple_settings	 = get_multiple_settings();
					$item_name					 = ic_catalog_item_name();
					$uc_item_name				 = ic_ucfirst( $item_name );
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
					$disabled					 = '';
					if ( !is_advanced_mode_forced() || ic_is_woo_template_available() ) {
						?>
						<h3><?php _e( 'Catalog Mode', 'ecommerce-product-catalog' ); ?></h3><?php
						if ( ic_is_woo_template_available() ) {
							echo '<p>' . __( 'If you have any problems with catalog layout you can use the theme integration wizard to fix it.', 'ecommerce-product-catalog' ) . '</p>';
						}
						if ( get_integration_type() == 'simple' ) {
							$disabled = 'disabled';
						}
						$theme = get_option( 'template' );
						if ( is_integration_mode_selected() ) {
							$selected = true;
						} else {
							$selected = false;
						}
						?>
						<div class="simple_mode_settings">
							<?php
							if ( get_integration_type() == 'simple' ) {
								//implecode_warning( '<p>' . sprintf( __( 'The simple mode allows to use %s most features. You can build the product listing pages and category pages by using a [show_products] shortcode. Simple mode uses your theme page layout so it can show unwanted elements on product page. If it does please switch to Advanced Mode and see if it works out of the box.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) . '</p><p>' . __( 'Switching to Advanced Mode also gives additional features: automatic product listing, category pages, product search and category widget. Building a product catalog in Advanced Mode will be less time consuming as you don’t need to use a shortcode for everything.', 'ecommerce-product-catalog' ) . '</p>' . sample_product_button( 'p', __( 'Restart Integration Wizard', 'ecommerce-product-catalog' ) ) );
								implecode_info( __( 'In simple mode you will have to use shortcodes to display category pages. The advanced mode will build them for you automatically.', 'ecommerce-product-catalog' ) . '</p>' . '<p>' . __( 'Simple mode will use your theme layout so it can show some unwanted elements on product catalog pages.', 'ecommerce-product-catalog' ) . '</p>' );
							}
							?>
						</div>
						<table>
							<?php
							implecode_settings_radio( __( 'Choose catalog mode', 'ecommerce-product-catalog' ), 'archive_multiple_settings[integration_type][' . $theme . ']', $archive_multiple_settings[ 'integration_type' ][ $theme ], array( 'simple' => __( 'Simple Mode', 'ecommerce-product-catalog' ), 'advanced' => __( 'Advanced Mode', 'ecommerce-product-catalog' ) ), 1, __( 'Choose Advanced Mode if you want to use catalog categories and search with eCommerce Product Catalog layout.', 'ecommerce-product-catalog' ), '<br>', "integration-mode-selection" );
							?></table>
						<div class="advanced_mode_settings">
							<?php
							implecode_info(
							sprintf( __( 'In Advanced Mode %s must figure out your theme markup to display products properly.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) . '</p>' .
							'<p>' . __( 'Use the blue button below to begin the easy auto adjustment.', 'ecommerce-product-catalog' ) . '</p>' .
							'<p>' . sprintf( __( 'If you have access to the server files you can also use our %sTheme Integration Guide%s to achieve it quickly.', 'ecommerce-product-catalog' ), '<a href="https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=general-integration-info">', '</a>' ) . '</p>'
							);
							?>
							<table class="advanced_mode_settings_hidden" style="display:none"><?php
								foreach ( $archive_multiple_settings[ 'integration_type' ] as $integration_theme => $value ) {
									if ( $integration_theme == $theme ) {
										continue;
									}
									echo '<input type="hidden" name="archive_multiple_settings[integration_type][' . $integration_theme . ']" value="' . $value . '">';
								}
								foreach ( $archive_multiple_settings[ 'container_width' ] as $container_width_theme => $value ) {
									if ( $container_width_theme == $theme ) {
										continue;
									}
									echo '<input type="hidden" name="archive_multiple_settings[container_width][' . $container_width_theme . ']" value="' . $value . '">';
								}
								foreach ( $archive_multiple_settings[ 'container_bg' ] as $container_bg_theme => $value ) {
									if ( $container_bg_theme == $theme ) {
										continue;
									}
									echo '<input type="hidden" name="archive_multiple_settings[container_bg][' . $container_bg_theme . ']" value="' . $value . '">';
								}
								foreach ( $archive_multiple_settings[ 'container_padding' ] as $container_width_padding => $value ) {
									if ( $container_width_padding == $theme ) {
										continue;
									}
									echo '<input type="hidden" name="archive_multiple_settings[container_padding][' . $container_width_padding . ']" value="' . $value . '">';
								}
								implecode_settings_number( __( 'Catalog Container Width', 'ecommerce-product-catalog' ), 'archive_multiple_settings[container_width][' . $theme . ']', $archive_multiple_settings[ 'container_width' ][ $theme ], '%' );
								implecode_settings_text_color( __( 'Catalog Container Background', 'ecommerce-product-catalog' ), 'archive_multiple_settings[container_bg][' . $theme . ']', $archive_multiple_settings[ 'container_bg' ][ $theme ] );
								implecode_settings_number( __( 'Catalog Container Padding', 'ecommerce-product-catalog' ), 'archive_multiple_settings[container_padding][' . $theme . ']', $archive_multiple_settings[ 'container_padding' ][ $theme ], 'px' );
								if ( !defined( 'AL_SIDEBAR_BASE_URL' ) ) {
									implecode_settings_radio( __( 'Default Sidebar', 'ecommerce-product-catalog' ), 'archive_multiple_settings[default_sidebar]', $archive_multiple_settings[ 'default_sidebar' ], array( 'none' => __( 'Disabled', 'ecommerce-product-catalog' ), 'left' => __( 'Left', 'ecommerce-product-catalog' ), 'right' => __( 'Right', 'ecommerce-product-catalog' ) ) );
								}
								implecode_settings_checkbox( __( 'Disable Product Name', 'ecommerce-product-catalog' ), 'archive_multiple_settings[disable_name]', $archive_multiple_settings[ 'disable_name' ] );
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
						<?php
					}
					?>
					<h3><?php _e( 'Catalog', 'ecommerce-product-catalog' ); ?></h3>
					<table><?php
						implecode_settings_text( __( 'Catalog Singular Name', 'ecommerce-product-catalog' ), 'archive_multiple_settings[catalog_singular]', $archive_multiple_settings[ 'catalog_singular' ], null, 1, null, __( 'Admin panel customisation setting. Change it to what you sell.', 'ecommerce-product-catalog' ) );
						implecode_settings_text( __( 'Catalog Plural Name', 'ecommerce-product-catalog' ), 'archive_multiple_settings[catalog_plural]', $archive_multiple_settings[ 'catalog_plural' ], null, 1, null, __( 'Admin panel customisation setting. Change it to what you sell.', 'ecommerce-product-catalog' ) );
						?>
					</table>

					<h3><?php _e( 'Main listing page', 'ecommerce-product-catalog' ); ?></h3><?php
					/* if ( $disabled == 'disabled' ) {
					  implecode_warning( sprintf( __( 'Product listing page is disabled with simple theme integration. See <a href="%s">Theme Integration Guide</a> to enable product listing page with pagination or use [show_products] shortcode on the page selected below.', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=product-listing' ) );
					  } */
					if ( !ic_check_rewrite_compatibility() ) {
						implecode_warning( __( 'It seems that this page is already set to be a listing for different elements. Please change the product listing page to make sure that product pages work fine.<br><br>This is probably caused by other plugin being set to show items on the same page.', 'ecommerce-product-catalog' ) );
					}
					?>
					<table>
						<tr>
							<td style="width: 180px">
							</td>
							<td></td>
						</tr>
						<?php
						implecode_settings_checkbox( __( 'Enable Main Listing Page', 'ecommerce-product-catalog' ), 'enable_product_listing', $enable_product_listing, 1, __( 'Disable and use [show_products] shortcode to display the products.', 'ecommerce-product-catalog' ) );
						?>
						<tr>
							<td>
								<span title="<?php _e( 'The page where the main product listing shows. Also this page slug will be included in product url.', 'ecommerce-product-catalog' ) ?>" class="dashicons dashicons-editor-help ic_tip"></span>
								<?php _e( 'Choose Main Listing Page', 'ecommerce-product-catalog' ); ?>:
							</td>
							<td><?php
								if ( $enable_product_listing == 1 ) {
									$listing_url = product_listing_url();
									select_page( 'product_archive', __( 'Default', 'ecommerce-product-catalog' ), $product_archive, true, $listing_url );
								} else {
									select_page( 'product_archive', __( 'Default', 'ecommerce-product-catalog' ), $product_archive, true );
								}
								?>
							</td>
						</tr> <?php
						implecode_settings_number( __( 'Listing shows at most', 'ecommerce-product-catalog' ), 'archive_multiple_settings[archive_products_limit]', $archive_multiple_settings[ 'archive_products_limit' ], $item_name, 1, 1, __( 'You can also use shortcode with products_limit attribute to set this.', 'ecommerce-product-catalog' ) );
						implecode_settings_radio( __( 'Main listing shows', 'ecommerce-product-catalog' ), 'archive_multiple_settings[product_listing_cats]', $archive_multiple_settings[ 'product_listing_cats' ], array( 'off' => $uc_item_name, 'on' => $uc_item_name . ' & ' . __( 'Main Categories', 'ecommerce-product-catalog' ), 'cats_only' => __( 'Main Categories', 'ecommerce-product-catalog' ) ) );
						$sort_options = get_product_sort_options();
						implecode_settings_radio( __( 'Default order', 'ecommerce-product-catalog' ), 'archive_multiple_settings[product_order]', $archive_multiple_settings[ 'product_order' ], $sort_options, true, __( 'This is also the default setting for sorting drop-down.', 'ecommerce-product-catalog' ) );
						do_action( 'product_listing_page_settings' );
						?>
					</table><?php
					//implecode_info(__('You can also use shortcode to show your products whenever you want on the website. Just paste on any page: [show_products] and you will display all products in place of the shortcode. <br><br>To show products from just one category, use: [show_products category="2"] where 2 is category ID (you can display several categories by inserting comma separated IDs). <br><br>To display products by IDs, use: [show_products product="5"], where 5 is product ID.', 'ecommerce-product-catalog'));
					?>
					<div class="advanced_mode_settings">
						<h3><?php _e( 'Categories Settings', 'ecommerce-product-catalog' ); ?></h3><?php
						/*
						  if ( $disabled != '' ) {
						  implecode_warning( __( 'Automatic category pages are disabled with simple integration. Switch to Advanced Integration to enable category pages or use [show_products category="1"] (where "1" is category ID) on any page to show products from certain category.', 'ecommerce-product-catalog' ) );
						  }
						 *
						 */
						if ( !ic_check_tax_rewrite_compatibility() ) {
							implecode_warning( __( 'It seems that this categories parent URL is already set to be a parent for different elements. Please change the Categories Parent URL to make sure that product category pages work fine.<br><br>This is probably caused by other plugin being set to show categoires with the same parent.', 'ecommerce-product-catalog' ) );
						}
						?>
						<table>
							<?php if ( is_ic_permalink_product_catalog() ) { ?>
								<tr>
									<td><?php _e( 'Categories Parent URL', 'ecommerce-product-catalog' ); ?>:</td>
									<?php
									$site_url	 = site_url();
									$urllen		 = strlen( $site_url );
									if ( $urllen > 25 ) {
										$site_url = ic_substr( $site_url, 0, 11 ) . '...' . ic_substr( $site_url, $urllen - 11, $urllen );
									}
									?>
									<td class="longer">
										<?php echo $site_url ?>/<input  type="text" name="archive_multiple_settings[category_archive_url]" title="<?php _e( 'Cannot be the same as product listing page slug.', 'ecommerce-product-catalog' ) ?>" id="category_archive_url" value="<?php echo urldecode( sanitize_title( $archive_multiple_settings[ 'category_archive_url' ] ) ); ?>"/>/<?php _e( 'category-name', 'ecommerce-product-catalog' ) ?>/
									</td>
								</tr><?php
							}
							implecode_settings_radio( __( 'Category Page shows', 'ecommerce-product-catalog' ), 'archive_multiple_settings[category_top_cats]', $archive_multiple_settings[ 'category_top_cats' ], array( 'off' => $uc_item_name, 'on' => $uc_item_name . ' & ' . __( 'Subcategories', 'ecommerce-product-catalog' ), 'only_subcategories' => __( 'Subcategories', 'ecommerce-product-catalog' ) ), 1, __( 'The main listing can show only products, top level categories and products or only the categories. With the third option selected the products will show only on the bottom level category pages.', 'ecommerce-product-catalog' ) );
							implecode_settings_radio( __( 'Categories Display', 'ecommerce-product-catalog' ), 'archive_multiple_settings[cat_template]', $archive_multiple_settings[ 'cat_template' ], array( 'template' => __( 'Template', 'ecommerce-product-catalog' ), 'link' => __( 'URLs', 'ecommerce-product-catalog' ) ), true, __( 'Template option will display categories with the same listing theme as products. Link option will show categories as simple URLs without image.', 'ecommerce-product-catalog' ) );
							implecode_settings_checkbox( __( 'Disable Image on Category Page', 'ecommerce-product-catalog' ), 'archive_multiple_settings[cat_image_disabled]', $archive_multiple_settings[ 'cat_image_disabled' ], 1, __( 'If you disable the image it will be only used for categories listing.', 'ecommerce-product-catalog' ) );
							implecode_settings_radio( __( 'Show Related', 'ecommerce-product-catalog' ), 'archive_multiple_settings[related]', $archive_multiple_settings[ 'related' ], array( 'products' => $uc_item_name, 'categories' => __( 'Categories', 'ecommerce-product-catalog' ), 'none' => __( 'Nothing', 'ecommerce-product-catalog' ) ), 1, __( 'The related products or categories will be shown on the bottom of product pages.', 'ecommerce-product-catalog' ) );
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
							implecode_settings_text( __( 'Archive SEO Title', 'ecommerce-product-catalog' ), 'archive_multiple_settings[seo_title]', $archive_multiple_settings[ 'seo_title' ], null, 1, null, __( 'Title tag for selected product listing page. If you are using separate SEO plugin you should set it there. E.g. in Yoast SEO look for it in Custom Post Types archive titles section.', 'ecommerce-product-catalog' ) );
							implecode_settings_checkbox( __( 'Enable SEO title separator', 'ecommerce-product-catalog' ), 'archive_multiple_settings[seo_title_sep]', $archive_multiple_settings[ 'seo_title_sep' ] )
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
							implecode_settings_checkbox( __( 'Enable Catalog Breadcrumbs', 'ecommerce-product-catalog' ), 'archive_multiple_settings[enable_product_breadcrumbs]', $archive_multiple_settings[ 'enable_product_breadcrumbs' ], 1, __( 'Shows a path to the currently displayed product catalog page with URLs to parent pages and correct schema markup for SEO.', 'ecommerce-product-catalog' ) );
							implecode_settings_text( __( 'Main listing breadcrumbs title', 'ecommerce-product-catalog' ), 'archive_multiple_settings[breadcrumbs_title]', $archive_multiple_settings[ 'breadcrumbs_title' ], null, 1, null, __( 'The title for main product listing in breadcrumbs.', 'ecommerce-product-catalog' ) );
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


		permalink_options_update();
		?>
	</div>

	<?php
}

function get_default_multiple_settings() {
	return array(
		'archive_products_limit'	 => 10,
		'category_archive_url'		 => 'product-category',
		'enable_product_breadcrumbs' => 0,
		'breadcrumbs_title'			 => '',
		'seo_title'					 => '',
		'seo_title_sep'				 => 1,
	);
}

function get_multiple_settings() {
	$archive_multiple_settings	 = get_option( 'archive_multiple_settings', get_default_multiple_settings() );
	$theme						 = get_option( 'template' );
	$prev_int					 = 'simple';
	if ( !isset( $archive_multiple_settings[ 'integration_type' ] ) || !is_array( $archive_multiple_settings[ 'integration_type' ] ) ) {
		$support_check = ic_get_theme_support_check();
		if ( !empty( $support_check[ $theme ] ) ) {
			$prev_int = isset( $archive_multiple_settings[ 'integration_type' ] ) ? $archive_multiple_settings[ 'integration_type' ] : 'simple';
		}
		$archive_multiple_settings[ 'integration_type' ] = array();
	}
	if ( is_advanced_mode_forced() || (isset( $_GET[ 'test_advanced' ] ) && ($_GET[ 'test_advanced' ] == 1 || $_GET[ 'test_advanced' ] == 'ok')) ) {
		$archive_multiple_settings[ 'integration_type' ][ $theme ] = 'advanced';
	} else {
		$archive_multiple_settings[ 'integration_type' ][ $theme ] = isset( $archive_multiple_settings[ 'integration_type' ][ $theme ] ) ? $archive_multiple_settings[ 'integration_type' ][ $theme ] : $prev_int;
	}
	$archive_multiple_settings[ 'disable_sku' ]					 = isset( $archive_multiple_settings[ 'disable_sku' ] ) ? $archive_multiple_settings[ 'disable_sku' ] : '';
	$archive_multiple_settings[ 'seo_title_sep' ]				 = isset( $archive_multiple_settings[ 'seo_title_sep' ] ) ? $archive_multiple_settings[ 'seo_title_sep' ] : '';
	$archive_multiple_settings[ 'seo_title' ]					 = isset( $archive_multiple_settings[ 'seo_title' ] ) ? $archive_multiple_settings[ 'seo_title' ] : '';
	$archive_multiple_settings[ 'category_archive_url' ]		 = isset( $archive_multiple_settings[ 'category_archive_url' ] ) ? $archive_multiple_settings[ 'category_archive_url' ] : 'products-category';
	$archive_multiple_settings[ 'category_archive_url' ]		 = empty( $archive_multiple_settings[ 'category_archive_url' ] ) ? 'products-category' : $archive_multiple_settings[ 'category_archive_url' ];
	$archive_multiple_settings[ 'product_listing_cats' ]		 = isset( $archive_multiple_settings[ 'product_listing_cats' ] ) ? $archive_multiple_settings[ 'product_listing_cats' ] : 'on';
	$archive_multiple_settings[ 'category_top_cats' ]			 = isset( $archive_multiple_settings[ 'category_top_cats' ] ) ? $archive_multiple_settings[ 'category_top_cats' ] : 'on';
	$archive_multiple_settings[ 'cat_template' ]				 = isset( $archive_multiple_settings[ 'cat_template' ] ) ? $archive_multiple_settings[ 'cat_template' ] : 'template';
	$archive_multiple_settings[ 'product_order' ]				 = isset( $archive_multiple_settings[ 'product_order' ] ) ? $archive_multiple_settings[ 'product_order' ] : 'newest';
	$archive_multiple_settings[ 'catalog_plural' ]				 = !empty( $archive_multiple_settings[ 'catalog_plural' ] ) ? $archive_multiple_settings[ 'catalog_plural' ] : DEF_CATALOG_PLURAL;
	$archive_multiple_settings[ 'catalog_singular' ]			 = !empty( $archive_multiple_settings[ 'catalog_singular' ] ) ? $archive_multiple_settings[ 'catalog_singular' ] : DEF_CATALOG_SINGULAR;
	$archive_multiple_settings[ 'cat_image_disabled' ]			 = isset( $archive_multiple_settings[ 'cat_image_disabled' ] ) ? $archive_multiple_settings[ 'cat_image_disabled' ] : '';
	$archive_multiple_settings[ 'container_width' ]				 = isset( $archive_multiple_settings[ 'container_width' ] ) ? $archive_multiple_settings[ 'container_width' ] : 100;
	$archive_multiple_settings[ 'container_bg' ]				 = isset( $archive_multiple_settings[ 'container_bg' ] ) ? $archive_multiple_settings[ 'container_bg' ] : '';
	$archive_multiple_settings[ 'container_padding' ]			 = isset( $archive_multiple_settings[ 'container_padding' ] ) ? $archive_multiple_settings[ 'container_padding' ] : 0;
	$archive_multiple_settings[ 'container_text' ]				 = isset( $archive_multiple_settings[ 'container_text' ] ) ? $archive_multiple_settings[ 'container_text' ] : '';
	$archive_multiple_settings[ 'disable_name' ]				 = isset( $archive_multiple_settings[ 'disable_name' ] ) ? $archive_multiple_settings[ 'disable_name' ] : '';
	$archive_multiple_settings[ 'default_sidebar' ]				 = isset( $archive_multiple_settings[ 'default_sidebar' ] ) ? $archive_multiple_settings[ 'default_sidebar' ] : 'none';
	$archive_multiple_settings[ 'related' ]						 = isset( $archive_multiple_settings[ 'related' ] ) ? $archive_multiple_settings[ 'related' ] : 'products';
	$archive_multiple_settings[ 'breadcrumbs_title' ]			 = isset( $archive_multiple_settings[ 'breadcrumbs_title' ] ) ? $archive_multiple_settings[ 'breadcrumbs_title' ] : $archive_multiple_settings[ 'catalog_plural' ];
	$archive_multiple_settings[ 'enable_product_breadcrumbs' ]	 = isset( $archive_multiple_settings[ 'enable_product_breadcrumbs' ] ) ? $archive_multiple_settings[ 'enable_product_breadcrumbs' ] : '';


	$prev_container_width	 = !is_array( $archive_multiple_settings[ 'container_width' ] ) ? $archive_multiple_settings[ 'container_width' ] : 100;
	$prev_container_bg		 = !is_array( $archive_multiple_settings[ 'container_bg' ] ) ? $archive_multiple_settings[ 'container_bg' ] : '';
	$prev_container_padding	 = !is_array( $archive_multiple_settings[ 'container_padding' ] ) ? $archive_multiple_settings[ 'container_padding' ] : 0;

	if ( !is_array( $archive_multiple_settings[ 'container_width' ] ) ) {
		$archive_multiple_settings[ 'container_width' ] = array();
	}
	if ( !is_array( $archive_multiple_settings[ 'container_bg' ] ) ) {
		$archive_multiple_settings[ 'container_bg' ] = array();
	}
	if ( !is_array( $archive_multiple_settings[ 'container_padding' ] ) ) {
		$archive_multiple_settings[ 'container_padding' ] = array();
	}
	if ( !is_array( $archive_multiple_settings[ 'container_text' ] ) ) {
		$archive_multiple_settings[ 'container_text' ] = array();
	}

	if ( !isset( $archive_multiple_settings[ 'container_width' ][ $theme ] ) ) {
		$archive_multiple_settings[ 'container_width' ][ $theme ] = $prev_container_width;
	}
	if ( !isset( $archive_multiple_settings[ 'container_bg' ][ $theme ] ) ) {
		$archive_multiple_settings[ 'container_bg' ][ $theme ] = $prev_container_bg;
	}
	if ( !isset( $archive_multiple_settings[ 'container_padding' ][ $theme ] ) ) {
		$archive_multiple_settings[ 'container_padding' ][ $theme ] = $prev_container_padding;
	}
	if ( !isset( $archive_multiple_settings[ 'container_text' ][ $theme ] ) ) {
		$archive_multiple_settings[ 'container_text' ][ $theme ] = $prev_container_padding;
	}
	return apply_filters( 'catalog_multiple_settings', $archive_multiple_settings );
}

function get_catalog_names() {
	$multiple_settings	 = get_multiple_settings();
	$names[ 'singular' ] = $multiple_settings[ 'catalog_singular' ];
	$names[ 'plural' ]	 = $multiple_settings[ 'catalog_plural' ];
	return apply_filters( 'product_catalog_names', $names );
}

function get_integration_type() {
	$settings	 = get_multiple_settings();
	$theme		 = get_option( 'template' );
	return $settings[ 'integration_type' ][ $theme ];
}

function get_product_sort_options() {
	$sort_options = apply_filters( 'product_sort_options', array( 'newest' => __( 'Sort by Newest', 'ecommerce-product-catalog' ), 'product-name' => __( 'Sort by Name', 'ecommerce-product-catalog' ) ) );
	return $sort_options;
}

function get_product_listing_id() {
	$product_archive_created = get_option( 'product_archive_page_id', '0' );
	if ( FALSE === get_post_status( $product_archive_created ) ) {
		$product_archive_created = '0';
	}
	$listing_id = get_option( 'product_archive', $product_archive_created );
	return apply_filters( 'product_listing_id', $listing_id );
}

/**
 * Returns product listing URL
 *
 * @return string
 */
function product_listing_url() {
	$listing_url = '';
	if ( /* is_ic_permalink_product_catalog() && */'noid' != ($page_id	 = get_product_listing_id()) ) {
		if ( !empty( $page_id ) ) {
			$listing_url = get_permalink( $page_id );
		}
	}
	if ( empty( $listing_url ) ) {
		$listing_url = get_post_type_archive_link( 'al_product' );
	}
	return apply_filters( 'product_listing_url', $listing_url );
}

function get_product_slug() {
	$page_id = get_product_listing_id();
	$slug	 = urldecode( untrailingslashit( get_page_uri( $page_id ) ) );
	if ( empty( $slug ) ) {
		$settings	 = get_multiple_settings();
		$slug		 = ic_lcfirst( $settings[ 'catalog_plural' ] );
	}
	return apply_filters( 'product_slug', $slug );
}

add_action( 'updated_option', 'rewrite_permalinks_after_update' );

function rewrite_permalinks_after_update( $option ) {
	if ( $option == 'product_archive' || $option == 'archive_multiple_settings' ) {
		flush_rewrite_rules();
	}
}

<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product settings
 *
 * Here product settings are defined and managed.
 *
 * @version        1.1.4
 * @package        ecommerce-product-catalog/includes
 * @author        Norbert Dreszer
 */
function register_product_extensions() {
	add_submenu_page( 'edit.php?post_type=al_product', __( 'Extensions', 'al-ecommerce-product-catalog' ), '<span class="extensions">' . __( 'Extensions', 'al-ecommerce-product-catalog' ) . '</span>', 'read_private_products', basename( __FILE__ ), 'product_extensions' );
}

add_action( 'product_settings_menu', 'register_product_extensions' );

function product_extensions() {
	?>

	<div id="implecode_settings" class="wrap">
		<h2><?php _e( 'Extensions', 'al-ecommerce-product-catalog' ) ?> - impleCode eCommerce Product Catalog</h2>
		<h2 class="nav-tab-wrapper">
			<a id="extensions" class="nav-tab"
			   href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=product-extensions' ) ?>"><?php _e( 'Extensions', 'al-ecommerce-product-catalog' ); ?></a>
			<a id="help" class="nav-tab"
			   href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=help' ) ?>"><?php _e( 'Help', 'al-ecommerce-product-catalog' ); ?></a>
		</h2>
		<div class="table-wrapper">

			<?php
			$tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : '';

			/* GENERAL SETTINGS */

			if ( $tab == 'product-extensions' OR $tab == '' ) {
				?>
				<div class="extension-list">
					<script>
		                jQuery( '.nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );
		                jQuery( '.nav-tab-wrapper a#extensions' ).addClass( 'nav-tab-active' );
					</script><?php
					start_implecode_install();
					if ( false === ($extensions = get_transient( 'implecode_extensions_data' )) ) {
						$extensions = wp_remote_get( 'http://app.implecode.com/index.php?provide_extensions' );
						if ( !is_wp_error( $extensions ) || 200 != wp_remote_retrieve_response_code( $extensions ) ) {
							$extensions = json_decode( wp_remote_retrieve_body( $extensions ), true );
							if ( $extensions ) {
								set_transient( 'implecode_extensions_data', $extensions, 60 * 60 * 24 * 7 );
							}
						} else {
							$extensions = implecode_extensions();
						}
					}
					$all_ic_plugins = '';
					if ( function_exists( 'get_implecode_active_plugins' ) ) {
						$all_ic_plugins = get_implecode_active_plugins();
					}
					$not_active_ic_plugins = get_implecode_not_active_plugins();
					foreach ( $extensions as $extension ) {
						echo extension_box( $extension[ 'name' ], $extension[ 'url' ], $extension[ 'desc' ], $extension[ 'comp' ], $extension[ 'slug' ], $all_ic_plugins, $not_active_ic_plugins );
					}
					?> </div>
				<div class="helpers">
					<div class="wrapper"><h2><?php _e( 'Did you Know?', 'al-ecommerce-product-catalog' ) ?></h2><?php
						text_helper( '', __( 'All extensions are designed to work with each other smoothly.', 'al-ecommerce-product-catalog' ) );
						text_helper( '', __( 'Some extensions give even more features when combined with another one.', 'al-ecommerce-product-catalog' ) );
						text_helper( '', __( 'Click on the extension to see full features list.', 'al-ecommerce-product-catalog' ) );
						text_helper( '', __( 'Paste your license key and click install button to start the extension installation process.', 'al-ecommerce-product-catalog' ) );
						?>
					</div>
				</div>
				<?php
			} else if ( $tab == 'help' ) {
				?>
				<div class="help">
					<script>
		                jQuery( '.nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );
		                jQuery( '.nav-tab-wrapper a#help' ).addClass( 'nav-tab-active' );
					</script> <?php
					echo '<h3>How to Install the extension?</h3>';
					echo '<ol><li>Click the "Get your key" button on the extension that you want to install;</li>';
					echo '<li>You will be redirected to the impleCode website. Read the extension description, choose license type, click the Add to Cart button and fill the form;</li>';
					echo '<li>Your license key will be immediately sent to you by email provided in the previous step;</li>';
					echo '<li>Copy and Paste the license key to the license key field on the extension that you want to install;</li>';
					echo '<li>Click the install button and wait until the installation process is done. The installer will establish a secure connection with impleCode to get the installation files;</li>';
					echo '<li>Click the activation button;</li>';
					echo '<li>That\'s it. Enjoy!</li></ol>';
					echo '<p>In case you prefer to install the extension manually you will get also the installation files by email. See <a target="_blank" href="http://implecode.com/wordpress/product-catalog/plugin-installation-guide/?cam=extensions-help&key=manual-installation#manual">manual installation guide</a> for this.</p>';
					echo '<p>Please see the <a target="_blank" href="http://implecode.com/faq/?cam=extensions-help&key=faq">FAQ</a> for additional information</p>';
					echo '</div>';
					?>
					<div class="helpers">
						<div class="wrapper"><h2><?php _e( 'Did you Know?', 'al-ecommerce-product-catalog' ) ?></h2><?php
							text_helper( '', __( 'The installation process takes less than 10 seconds.', 'al-ecommerce-product-catalog' ) );
							text_helper( '', sprintf( __( 'You can take advantage of premium support and <a href="%s">send support tickets</a> to impleCode developers once you have your license key.', 'al-ecommerce-product-catalog' ), 'https://implecode.com/support/?support_type=support' ) );
							?>
						</div>
					</div><?php }
						?>

			</div>
			<div style="clear:both; height: 50px;"></div>
			<div class="plugin-logo">
				<a href="http://implecode.com/#cam=catalog-settings-link&key=logo-link"><img class="en"
																							 src="<?php echo AL_PLUGIN_BASE_PATH . 'img/implecode.png'; ?>"
																							 width="282px" alt="impleCode"/></a>
			</div>
		</div><?php
		/*
		  if ($tab == 'product-extensions' OR $tab == '') { ?>
		  <script>
		  jQuery(document).ready(function($) {
		  var $cache = $('.helpers .wrapper');
		  var top = $cache.offset().top - 30;
		  var h_height = $('.helpers .wrapper').height();
		  var height = $('.helpers').height() - h_height + 95;
		  function fixDiv() {
		  if ($(window).scrollTop() > top && $(window).scrollTop() < height)
		  $cache.css({'position': 'fixed', 'top': '32px'});
		  else if ($(window).scrollTop() > height)
		  $cache.css({'position': 'absolute', 'bottom': '10px', 'top': 'auto'});
		  else
		  $cache.css({'position': 'relative', 'top': 'auto', 'bottom': 'auto'});
		  }
		  $(window).scroll(fixDiv);
		  fixDiv();
		  });</script><?php } */
	}

	function implecode_extensions() {
		$extensions = array(
			array(
				'url'	 => 'premium-support',
				'name'	 => 'eCommerce Product Catalog Premium',
				'desc'	 => 'The premium version of eCommerce Product Catalog with more features & premium support.',
				'comp'	 => 'simple',
				'slug'	 => 'implecode-product-sidebar',
			),
			array(
				'url'	 => 'shopping-cart',
				'name'	 => 'Shopping Cart',
				'desc'	 => 'Full featured shopping cart with advanced customisation options. Transform your product catalog into a Web Store!',
				'comp'	 => 'simple',
				'slug'	 => 'implecode-shopping-cart',
			),
			array(
				'url'	 => 'quote-form',
				'name'	 => 'Quote Form',
				'desc'	 => 'Improve the conversion rate with quote/inquiry button which redirects to fully customizable product quote form.',
				'comp'	 => 'simple',
				'slug'	 => 'implecode-quote-form',
			),
			array(
				'url'	 => 'order-form',
				'name'	 => 'Order Form',
				'desc'	 => 'This powerful extension allows you to sell individual products with buy now button and fully customizable order form.',
				'comp'	 => 'simple',
				'slug'	 => 'implecode-order-form',
			),
			array(
				'url'	 => 'paypal-gateway',
				'name'	 => 'PayPal Gateway',
				'desc'	 => 'Boost the conversion rate with a robust PayPal shopping cart, buy now button or order form implementation.',
				'comp'	 => 'simple',
				'slug'	 => 'implecode-paypal-gateway',
			),
			array(
				'url'	 => 'product-page-customizer',
				'name'	 => 'Product Page Customizer',
				'desc'	 => 'Customize product page with simple settings. Change product page elements, their size, position and colors easily in a few seconds.',
				'comp'	 => 'simple',
				'slug'	 => 'product-page-customizer',
			),
			array(
				'url'	 => 'product-gallery-advanced',
				'name'	 => 'Product Gallery Advanced',
				'desc'	 => 'Add unlimited number of product images and show them in a robust product slider or beautiful light-box presentation.',
				'comp'	 => 'simple',
				'slug'	 => 'product-gallery-advanced',
			),
			array(
				'url'	 => 'custom-product-order',
				'name'	 => 'Custom Product Order',
				'desc'	 => 'Sort products by priority, lowest price, highest price or randomly. New options in sort drop-down. Assign featured products.',
				'comp'	 => 'simple',
				'slug'	 => 'custom-product-order',
			),
			array(
				'url'	 => 'upload-pdf',
				'name'	 => 'Upload PDF',
				'desc'	 => 'Easily attach unlimited PDF files to the products, upload to server and provide to clients on product pages.',
				'comp'	 => 'simple',
				'slug'	 => 'implecode-upload-pdf',
			),
			array(
				'url'	 => 'product-search-pro',
				'name'	 => 'Product Search PRO',
				'desc'	 => 'Improve WordPress default search engine to provide better product search results. Show product search form with a shortcode.',
				'comp'	 => 'adv',
				'slug'	 => 'implecode-product-search',
			),
			array(
				'url'	 => 'smart-multiple-catalogs',
				'name'	 => 'Smart Multiple Catalogs',
				'desc'	 => 'Create completely separate, multiple catalogs at one website. Assign separate categories, parent URLs, manage them from different...',
				'comp'	 => 'simple',
				'slug'	 => 'smart-multiple-catalogs',
			),
			array(
				'url'	 => 'smarter-product-urls',
				'name'	 => 'Smarter Product URLs',
				'desc'	 => 'Set up SEO and USER friendly product page URLs. Add product category in product page URLs.',
				'comp'	 => 'adv',
				'slug'	 => 'smarter-product-urls',
			),
			array(
				'url'	 => 'drop-attributes',
				'name'	 => 'Drop-down Attributes',
				'desc'	 => 'Select attributes values with a drop-down. Define default drop-down values for each attribute in product settings.',
				'comp'	 => 'simple',
				'slug'	 => 'drop-down-attributes',
			),
			array(
				'url'	 => 'product-csv',
				'name'	 => 'Product CSV',
				'desc'	 => 'Import, Export & Update products all fields and attributes with a simple CSV file.',
				'comp'	 => 'simple',
				'slug'	 => 'implecode-product-csv',
			),
			array(
				'url'	 => 'product-discounts',
				'name'	 => 'Product Discounts',
				'desc'	 => 'Apply percentage or value discounts for catalog products. Show the discount offers with a robust widget or shortcode and more!',
				'comp'	 => 'simple',
				'slug'	 => 'implecode-product-discounts',
			),
			array(
				'url'	 => 'classic-list-button',
				'name'	 => 'Classic List with Button',
				'desc'	 => 'Premium product listing theme for your eCommerce Product Catalog. Easily set image size, description name and button position.',
				'comp'	 => 'simple',
				'slug'	 => 'classic-list-button',
			),
			array(
				'url'	 => 'slim-grid',
				'name'	 => 'Slim Grid Theme',
				'desc'	 => 'Premium Grid Theme for product listing. Has additional settings for size, per row elements, description length and price.',
				'comp'	 => 'simple',
				'slug'	 => 'slim-classic-grid',
			),
		);
		return $extensions;
	}

	function extension_box( $name, $url, $desc, $comp = 'simple', $slug, $all_ic_plugins, $not_active_ic_plugins ) {
		if ( $comp == 'adv' && get_integration_type() == 'simple' ) {
			$comp_txt	 = __( 'Advanced Mode Required', 'al-ecommerce-product-catalog' );
			$comp_class	 = 'wrong';
		} else {
			$comp_txt	 = __( 'Ready to Install', 'al-ecommerce-product-catalog' );
			$comp_class	 = 'good';
		}

		$return		 = '<div class="extension ' . $url . '">
	<a class="extension-name" target="_blank" href="http://implecode.com/wordpress/plugins/' . $url . '/#cam=extensions&key=' . $url . '"><h3><span>' . $name . '</span></h3><span class="click-span">' . __( 'Click for more', 'al-ecommerce-product-catalog' ) . '</span></a>
	<p>' . $desc . '</p>';
		$disabled	 = '';
		$current_key = get_option( 'custom_license_code' );
		if ( !current_user_can( 'install_plugins' ) ) {
			$disabled	 = 'disabled';
			$current_key = '';
		}
		if ( !empty( $all_ic_plugins ) && is_ic_plugin_active( $slug, $all_ic_plugins ) ) {
			$return .= '<p><a target="_blank" href="https://implecode.com/support/" class="button-primary">Support</a> <a target="_blank" href="https://implecode.com/docs/" class="button-primary">Docs</a> <span class="comp installed">' . __( 'Active Extension', 'al-ecommerce-product-catalog' ) . '</span></p>';
		} else if ( !empty( $not_active_ic_plugins ) && is_ic_plugin_active( $slug, $not_active_ic_plugins ) ) {
			$return .= '<p><a ' . $disabled . ' href="' . wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $slug . '/' . $slug . '.php' ), 'activate-plugin_' . $slug . '/' . $slug . '.php' ) . '" class="button-primary">Activate Now</a><span class="comp info">' . __( 'Installed Extension', 'al-ecommerce-product-catalog' ) . '</span></p>';
		} else {
			if ( $comp_class == 'wrong' ) {
				$return .= '<p><a target="_blank" href="http://implecode.com/wordpress/plugins/' . $url . '/#cam=extensions&key=' . $url . '" class="button-primary">See the Extension</a><span class="comp ' . $comp_class . '">' . $comp_txt . '</span></p>';
			} else {
				$return .= '<form class="license_form" action=""><input type="hidden" name="implecode_install" value="1"><input type="hidden" name="url" value="' . $url . '"><input type="hidden" name="slug" value="' . $slug . '"><input type="hidden" name="post_type" value="al_product"><input type="hidden" name="page" value="extensions.php"><input type="text" name="license_key" ' . $disabled . ' class="wide" placeholder="License Key..." value="' . $current_key . '">';
				$return .= wp_nonce_field( 'install-implecode-plugin_' . $slug, '_wpnonce', 0, 0 );
				$return .= '<p class="submit"><input type="submit" ' . $disabled . ' value="Install" class="button-primary"><span class="comp ' . $comp_class . '">' . $comp_txt . '</span> <a target="_blank" href="http://implecode.com/wordpress/plugins/' . $url . '/#cam=extensions&key=' . $url . '" class="button-secondary right">Get your key</a></form></p>';
			}
		}
		$return .= '</div>';
		return $return;
	}

	function is_ic_plugin_active( $slug, $all_ic_plugins ) {
		foreach ( $all_ic_plugins as $key => $val ) {
			if ( $val[ 'slug' ] === $slug ) {
				return true;
			}
		}
		return false;
	}

	function start_implecode_install() {
		if ( isset( $_GET[ 'implecode_install' ] ) && !empty( $_GET[ 'slug' ] ) && !empty( $_GET[ 'license_key' ] ) && wp_verify_nonce( $_GET[ '_wpnonce' ], 'install-implecode-plugin_' . $_GET[ 'slug' ] ) == 1 && current_user_can( 'install_plugins' ) ) {
			$api = implecode_installation_url();
			if ( $api != 'error' ) {
				add_filter( 'install_plugin_complete_actions', 'implecode_install_actions', 10, 3 );
				echo '<div class="extension_installer">';
				include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
				$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
				$upgrader->install( $api->download_url );
				echo '</div>';
			} else if ( !is_license_key_prevalidated( $_GET[ 'license_key' ] ) ) {
				echo '<div id="message error" class="error product-adder-message messages-connect">
				<div class="squeezer">
					<h4><strong>' . sprintf( __( 'This is not a valid license key! Get it <a href="%s">here</a>.', 'al-ecommerce-product-catalog' ), 'http://implecode.com/wordpress/plugins/' . $_GET[ 'url' ] . '/#cam=extensions&key=' . $_GET[ 'url' ] ) . '</strong></h4>
				</div>
			</div>';
			} else {
				echo '<div id="message error" class="error product-adder-message messages-connect">
				<div class="squeezer">
					<h4><strong>' . sprintf( __( 'The supplied license key is not valid for this extension! Upgrade it <a href="%s">here</a>.', 'al-ecommerce-product-catalog' ), 'http://implecode.com/wordpress/plugins/' . $_GET[ 'url' ] . '/#cam=extensions&key=' . $_GET[ 'url' ] ) . '</strong></h4>
				</div>
			</div>';
			}
		} else if ( isset( $_GET[ 'implecode_install' ] ) && !empty( $_GET[ 'slug' ] ) && empty( $_GET[ 'license_key' ] ) && current_user_can( 'install_plugins' ) ) {
			echo '<div id="message error" class="error product-adder-message messages-connect">
				<div class="squeezer">
					<h4><strong>' . sprintf( __( 'You need to provide the license key to activate the extension. Get yours <a href="%s">here</a>.', 'al-ecommerce-product-catalog' ), 'http://implecode.com/wordpress/plugins/' . $_GET[ 'url' ] . '/#cam=extensions&key=' . $_GET[ 'url' ] ) . '</strong></h4>
				</div>
			</div>';
		} else if ( !current_user_can( 'install_plugins' ) ) {
			echo '<div id="message error" class="error product-adder-message messages-connect">
				<div class="squeezer">
					<h4><strong>' . __( 'You don\'t have permission to install and activate extensions.', 'al-ecommerce-product-catalog' ) . '</strong></h4>
				</div>
			</div>';
		}
	}

	function implecode_install_actions( $install_actions, $api, $plugin_file ) {
		$install_actions[ 'plugins_page' ] = '<a href="' . admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=product-extensions' ) . '">' . __( 'Reload the Page', 'al-ecommerce-product-catalog' ) . '</a>';
		return $install_actions;
	}

	function implecode_installation_url() {
		if ( is_license_key_prevalidated( $_GET[ 'license_key' ] ) ) {
			$options = array(
				'timeout' => 10, //seconds
			);
			$url	 = 'https://implecode.com/?action=get_metadata&slug=' . $_GET[ 'slug' ] . '&license_key=' . $_GET[ 'license_key' ];
			$connect = wp_remote_get(
			$url, $options
			);
			if ( !is_wp_error( $connect ) && !empty( $connect[ 'body' ] ) ) {
				$pluginInfo = json_decode( $connect[ 'body' ] );
				if ( isset( $pluginInfo->download_url ) && $pluginInfo->download_url != '' ) {
					update_option( 'custom_license_code', $_GET[ 'license_key' ] );
					$license_owner		 = url_to_array( $pluginInfo->license_owner );
					update_option( 'implecode_license_owner', array_to_url( $license_owner ) );
					update_option( 'no_implecode_license_error', 0 );
					$active_license		 = unserialize( get_option( 'license_active_plugins' ) );
					$active_license[]	 = $_GET[ 'slug' ];
					update_option( 'license_active_plugins', serialize( $active_license ) );
					return $pluginInfo;
				}
			}
		}
		return 'error';
	}

	function get_implecode_not_active_plugins() {
		$all_active = get_option( 'active_plugins' );
		if ( !function_exists( 'get_plugins' ) ) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}
		$all_plugins = get_plugins();
		$i			 = 0;
		$ic_plugins	 = array();
		foreach ( $all_active as $active_name ) {
			unset( $all_plugins[ $active_name ] );
		}
		foreach ( $all_plugins as $not_active_name => $not_active_plugin ) {
			if ( $not_active_plugin[ 'Author' ] == 'Norbert Dreszer' && $not_active_plugin[ 'Name' ] != 'eCommerce Product Catalog by impleCode' ) {
				$ic_plugins[ $i ][ 'dir_file' ]	 = $not_active_name;
				$not_active_name				 = explode( '/', $not_active_name );
				$ic_plugins[ $i ][ 'slug' ]		 = $not_active_name[ 0 ];
			}
			$i++;
		}
		return $ic_plugins;
	}

	function is_license_key_prevalidated( $license_key ) {
		$license_key = explode( '-', $license_key );
		if ( count( $license_key ) == 8 ) {
			return true;
		}
		return false;
	}

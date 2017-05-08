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
add_action( 'ic_epc_loaded', 'initialize_affiliate_scripts', 20 );

function initialize_affiliate_scripts() {
	if ( is_admin() ) {
		include(AL_BASE_PATH . '/partners/wpml/loader.php');
	}
	if ( is_admin() && isset( $_GET[ 'page' ] ) && $_GET[ 'page' ] == 'extensions.php' ) {
		if ( !defined( 'ICL_AFFILIATE_ID' ) ) {
			define( 'ICL_AFFILIATE_ID', '89119' );
		}
		if ( !defined( 'ICL_AFFILIATE_KEY' ) ) {
			define( 'ICL_AFFILIATE_KEY', 'x7MQob0JrgTA' );
		}

		//global $wp_installer_instances;
		//print_r( $wp_installer_instances );

		$wp_installer_instance = AL_BASE_PATH . '\partners\wpml/installer.php';
		//print_r( $wp_installer_instances[ $wp_installer_instance ] );
		WP_Installer_Setup( $wp_installer_instance, array(
			'plugins_install_tab'	 => 0, // optional, default value: 0
			'affiliate_id:wpml'		 => '89119', // optional, default value: empty
			'affiliate_key:wpml'	 => 'x7MQob0JrgTA', // optional, default value: empty
			'src_name'				 => IC_CATALOG_PLUGIN_NAME, // optional, default value: empty, needed for coupons
			'src_author'			 => 'impleCode', // optional, default value: empty, needed for coupons
			'repositories_include'	 => array( 'wpml' ) // optional, default to empty (show all)
		)
		);
	}
}

function ic_show_affiliate_content() {
	$config[ 'template' ]		 = 'compact'; //required
	$config[ 'product_name' ]	 = 'WPML';
	$config[ 'box_title' ]		 = 'Multilingual ' . IC_CATALOG_PLUGIN_NAME;
	$config[ 'name' ]			 = IC_CATALOG_PLUGIN_NAME; //name of theme/plugin
	$config[ 'box_description' ] = IC_CATALOG_PLUGIN_NAME . ' is fully compatible with WPML - the WordPress Multilingual plugin. WPML lets you add languages to your existing sites and includes advanced translation management.';
	$config[ 'repository' ]		 = 'wpml'; // required
	$config[ 'package' ]		 = 'multilingual-cms'; // required
	$config[ 'product' ]		 = 'multilingual-cms'; // required
	ob_start();
	WP_Installer_Show_Products( $config );
	$output						 = ob_get_clean();
	if ( $output != '<center>' . __( 'No repositories defined.', 'installer' ) . '</center>' ) {
		echo '<h2 class="partners-header">' . __( 'Fully compatible plugins from our partners', 'ecommerce-product-catalog' ) . '</h2>';
		echo '<p>You can also use third party plugins and themes fully compatible with ' . IC_CATALOG_PLUGIN_NAME . '. Please note that ' . IC_CATALOG_PLUGIN_NAME . ' developers get a small affiliate commision from every purchase made through the links below. This actually helps the devs to support the integration between plugins more effectively.</p>';
		echo '<div class="extension wpml">';
		echo $output;
		echo '</div>';
	}
}

function register_product_extensions() {
	add_submenu_page( 'edit.php?post_type=al_product', __( 'Extensions', 'ecommerce-product-catalog' ), '<span class="extensions">' . __( 'Extensions', 'ecommerce-product-catalog' ) . '</span>', 'manage_product_settings', basename( __FILE__ ), 'product_extensions' );
}

add_action( 'extensions-menu', 'ic_epc_extensions_menu_elements' );

/**
 * Generates eCommerce Product Catalog extensions menu
 *
 */
function ic_epc_extensions_menu_elements() {
	?>
	<a id="extensions" class="nav-tab" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=product-extensions' ) ?>"><?php _e( 'Installation', 'ecommerce-product-catalog' ); ?></a>
	<?php
	/*
	  <a id="new-extensions" class="nav-tab" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=new-product-extensions' ) ?>"><?php _e( 'New', 'ecommerce-product-catalog' ); ?></a>
	 */
}

add_action( 'product_settings_menu', 'register_product_extensions' );

function product_extensions() {
	?>
	<div id="implecode_settings" class="wrap">
		<h2><?php echo sprintf( __( 'Extensions for %s', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) ?></h2>
		<h3><?php _e( 'All premium extensions come with premium support provided by dev team.<br>Feel free to contact impleCode for configuration help, troubleshooting, installation assistance and any other plugin support at any time!', 'ecommerce-product-catalog' ) ?></h3>
		<h2 class="nav-tab-wrapper">
			<?php do_action( 'extensions-menu' ) ?>
			<a id="help" class="nav-tab"
			   href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=help' ) ?>"><?php _e( 'Help', 'ecommerce-product-catalog' ); ?></a>
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
					start_free_implecode_install();
					if ( false === ($extensions = get_site_transient( 'implecode_extensions_data' )) ) {
						$extensions_remote_url	 = apply_filters( 'ic_extensions_remote_url', 'provide_extensions' );
						$extensions				 = wp_remote_get( 'http://app.implecode.com/index.php?' . $extensions_remote_url );
						if ( !is_wp_error( $extensions ) && 200 == wp_remote_retrieve_response_code( $extensions ) ) {
							$extensions = json_decode( wp_remote_retrieve_body( $extensions ), true );
							if ( $extensions ) {
								set_site_transient( 'implecode_extensions_data', $extensions, 60 * 60 * 24 * 7 );
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
					do_action( 'ic_before_extensions_list', $tab );
					foreach ( $extensions as $extension ) {
						$extension[ 'type' ] = isset( $extension[ 'type' ] ) ? $extension[ 'type' ] : 'premium';
						echo extension_box( $extension[ 'name' ], $extension[ 'url' ], $extension[ 'desc' ], $extension[ 'comp' ], $extension[ 'slug' ], $all_ic_plugins, $not_active_ic_plugins, $extension[ 'type' ] );
					}
					ic_show_affiliate_content();
					?>
				</div>
				<div class="helpers">
					<div class="wrapper"><h2><?php _e( 'Did you Know?', 'ecommerce-product-catalog' ) ?></h2><?php
						text_helper( '', __( 'All extensions are designed to work with each other smoothly.', 'ecommerce-product-catalog' ) );
						text_helper( '', __( 'Some extensions give even more features when combined with another one.', 'ecommerce-product-catalog' ) );
						text_helper( '', __( 'Click on the extension to see full features list.', 'ecommerce-product-catalog' ) );
						text_helper( '', __( 'Paste your license key and click install button to start the extension installation process.', 'ecommerce-product-catalog' ) );
						?>
					</div>
				</div> <?php
			} else if ( $tab == 'new-product-extensions' ) {
				?>
				<div class="extension-list">
					<script>
		                jQuery( '.nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );
		                jQuery( '.nav-tab-wrapper a#new-extensions' ).addClass( 'nav-tab-active' );
					</script><?php
					start_implecode_install();
					if ( false === ($extensions = get_site_transient( 'implecode_new_extensions_data' )) ) {
						$extensions = wp_remote_get( 'http://app.implecode.com/index.php?provide_extensions&new=1' );
						if ( !is_wp_error( $extensions ) || 200 != wp_remote_retrieve_response_code( $extensions ) ) {
							$extensions = json_decode( wp_remote_retrieve_body( $extensions ), true );
							if ( $extensions ) {
								set_site_transient( 'implecode_new_extensions_data', $extensions, 60 * 60 * 24 * 7 );
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
					do_action( 'ic_before_extensions_list', $tab );
					foreach ( $extensions as $extension ) {
						echo extension_box( $extension[ 'name' ], $extension[ 'url' ], $extension[ 'desc' ], $extension[ 'comp' ], $extension[ 'slug' ], $all_ic_plugins, $not_active_ic_plugins );
					}
					?>
				</div>
				<div class="helpers">
					<div class="wrapper"><h2><?php _e( 'Did you Know?', 'ecommerce-product-catalog' ) ?></h2><?php
						text_helper( '', __( 'All extensions are designed to work with each other smoothly.', 'ecommerce-product-catalog' ) );
						text_helper( '', __( 'Some extensions give even more features when combined with another one.', 'ecommerce-product-catalog' ) );
						text_helper( '', __( 'Click on the extension to see full features list.', 'ecommerce-product-catalog' ) );
						text_helper( '', __( 'Paste your license key and click install button to start the extension installation process.', 'ecommerce-product-catalog' ) );
						?>
					</div>
				</div>
				<?php
			} else if ( $tab == 'all-product-extensions' ) {
				?>
				<div class="extension-list">
					<script>
		                jQuery( '.nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );
		                jQuery( '.nav-tab-wrapper a#extensions' ).addClass( 'nav-tab-active' );
					</script><?php
					start_implecode_install();
					if ( false === ($extensions = get_site_transient( 'implecode_extensions_data' )) ) {
						$extensions = wp_remote_get( 'http://app.implecode.com/index.php?provide_extensions' );
						if ( !is_wp_error( $extensions ) || 200 != wp_remote_retrieve_response_code( $extensions ) ) {
							$extensions = json_decode( wp_remote_retrieve_body( $extensions ), true );
							if ( $extensions ) {
								set_site_transient( 'implecode_extensions_data', $extensions, 60 * 60 * 24 * 7 );
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
					do_action( 'ic_before_extensions_list', $tab );
					foreach ( $extensions as $extension ) {
						echo extension_box( $extension[ 'name' ], $extension[ 'url' ], $extension[ 'desc' ], $extension[ 'comp' ], $extension[ 'slug' ], $all_ic_plugins, $not_active_ic_plugins );
					}
					?>
				</div>
				<div class="helpers">
					<div class="wrapper"><h2><?php _e( 'Did you Know?', 'ecommerce-product-catalog' ) ?></h2><?php
						text_helper( '', __( 'All extensions are designed to work with each other smoothly.', 'ecommerce-product-catalog' ) );
						text_helper( '', __( 'Some extensions give even more features when combined with another one.', 'ecommerce-product-catalog' ) );
						text_helper( '', __( 'Click on the extension to see full features list.', 'ecommerce-product-catalog' ) );
						text_helper( '', __( 'Paste your license key and click install button to start the extension installation process.', 'ecommerce-product-catalog' ) );
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
					echo '<p>In case you prefer to install the extension manually you will get also the installation files by email. See <a href="https://implecode.com/wordpress/product-catalog/plugin-installation-guide/?cam=extensions-help&key=manual-installation#manual">manual installation guide</a> for this.</p>';
					echo '<p>Please see the <a href="https://implecode.com/faq/?cam=extensions-help&key=faq">FAQ</a> for additional information</p>';
					?>
				</div>
				<div class="helpers">
					<div class="wrapper"><h2><?php _e( 'Did you Know?', 'ecommerce-product-catalog' ) ?></h2><?php
						text_helper( '', __( 'The installation process takes less than 10 seconds.', 'ecommerce-product-catalog' ) );
						text_helper( '', sprintf( __( 'You can take advantage of premium support and <a href="%s">send support tickets</a> to impleCode developers once you have your license key.', 'ecommerce-product-catalog' ), 'https://implecode.com/support/?support_type=support' ) );
						?>
					</div>
				</div><?php }
					?>
		</div>

		<div style="clear:both; height: 50px;"></div>
		<div class="plugin-logo">
			<a href="https://implecode.com/#cam=catalog-settings-link&key=logo-link"><img class="en" src="<?php echo AL_PLUGIN_BASE_PATH . 'img/implecode.png'; ?>" width="282px" alt="impleCode"/></a>
		</div>
	</div>
	<?php
}

function implecode_extensions() {
	$extensions = array(
		array(
			'url'	 => 'premium-support',
			'name'	 => 'Premium Toolset',
			'desc'	 => 'Product sidebar, product tags, enhanced category widget and premium email support.',
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
			'url'	 => 'quote-cart',
			'name'	 => 'Quote Cart',
			'desc'	 => 'Allow your users to send a quote for multiple products. Quote Cart adds a store like experience even for products without price!',
			'comp'	 => 'simple',
			'slug'	 => 'implecode-quote-cart',
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
			'url'	 => '2checkout-gateway',
			'name'	 => '2Checkout Gateway',
			'desc'	 => 'Take credit card payments with 2Checkout Gateway.',
			'comp'	 => 'simple',
			'slug'	 => '2checkout-gateway',
		),
		array(
			'url'	 => 'catalog-users-manager',
			'name'	 => 'Catalog Users Manager',
			'desc'	 => 'Manage catalog visibility options depending on logged in visitor.',
			'comp'	 => 'simple',
			'slug'	 => 'catalog-users-manager',
		),
		array(
			'url'	 => 'printable-coupons',
			'name'	 => 'Printable Coupons',
			'desc'	 => 'Sell printable coupons for your products or for certain value directly from the website. Generate customized coupons!',
			'comp'	 => 'simple',
			'slug'	 => 'implecode-printable-coupons',
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
			'url'	 => 'product-pdf',
			'name'	 => 'Product Print & PDF',
			'desc'	 => 'Print product pages with one click. Export product pages to PDF files with easy.',
			'comp'	 => 'simple',
			'slug'	 => 'product-pdf',
		),
		array(
			'url'	 => 'product-manufacturers',
			'name'	 => 'Product Manufacturers',
			'desc'	 => 'Manage product manufacturers & brands in separate screen and easily assign them to products. It has never been so simple!',
			'comp'	 => 'simple',
			'slug'	 => 'product-manufacturers',
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
			'url'	 => 'product-locations',
			'name'	 => 'Product Locations',
			'desc'	 => 'Easily manage product locations and get product quotes to multiple email addresses directly from product pages.',
			'comp'	 => 'simple',
			'slug'	 => 'implecode-product-locations',
		),
		array(
			'url'	 => 'product-attributes-pro',
			'name'	 => 'Product Attributes PRO',
			'desc'	 => 'Filter products by attributes. Select attributes values with a drop-down, checkbox or radio button.',
			'comp'	 => 'simple',
			'slug'	 => 'product-attributes-pro',
		),
		array(
			'url'	 => 'advanced-shipping-tables',
			'name'	 => 'Advanced Shipping Tables',
			'desc'	 => 'Calculates shipping based on Shopping Cart total and checkout fields values.',
			'comp'	 => 'simple',
			'slug'	 => 'advanced-shipping-table',
		),
		array(
			'url'	 => 'product-csv',
			'name'	 => 'Product CSV',
			'desc'	 => 'Import, Export & Update products all fields and attributes with a simple CSV file.',
			'comp'	 => 'simple',
			'slug'	 => 'implecode-product-csv',
		),
		array(
			'url'	 => 'multiple-prices',
			'name'	 => 'Multiple Pricing',
			'desc'	 => 'Set multiple, automatically calculated or manually inserted prices for each product.',
			'comp'	 => 'simple',
			'slug'	 => 'multiple-product-price',
		),
		array(
			'url'	 => 'product-discounts',
			'name'	 => 'Product Discounts',
			'desc'	 => 'Apply percentage or value discounts for catalog products. Show the discount offers with a robust widget or shortcode and more!',
			'comp'	 => 'simple',
			'slug'	 => 'implecode-product-discounts',
		),
		array(
			'url'	 => 'table-view',
			'name'	 => 'Table View',
			'desc'	 => 'Show products in nicely formatted table with customizable columns.',
			'comp'	 => 'simple',
			'slug'	 => 'table-view',
		),
		array(
			'url'	 => 'classic-list-button',
			'name'	 => 'Classic List with Button',
			'desc'	 => 'Premium product listing theme for your catalog. Easily set image size, description name and button position.',
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
		array(
			'url'	 => 'no-image-grid',
			'name'	 => 'No Image Grid Theme',
			'desc'	 => 'Premium Grid Theme for product listing. Best for products without image.',
			'comp'	 => 'simple',
			'slug'	 => 'no-image-grid',
		),
		array(
			'url'	 => 'side-grid',
			'name'	 => 'Side Grid',
			'desc'	 => 'Premium product listing grid with image on the left side.',
			'comp'	 => 'simple',
			'slug'	 => 'side-grid',
		),
	);
	return $extensions;
}

function extension_box( $name, $url, $desc, $comp = 'simple', $slug, $all_ic_plugins, $not_active_ic_plugins,
						$type = 'premium' ) {
	if ( $type == 'free' ) {
		return free_extension_box( $name, $url, $desc, $comp, $slug, $all_ic_plugins, $not_active_ic_plugins );
	}
	if ( $comp == 'adv' && get_integration_type() == 'simple' ) {
		$comp_txt	 = __( 'Advanced Mode Required', 'ecommerce-product-catalog' );
		$comp_class	 = 'wrong';
	} else {
		$comp_txt	 = __( 'Ready to Install', 'ecommerce-product-catalog' );
		$comp_class	 = 'good';
	}

	$return		 = '<div class="extension ' . $url . '">
	<a class="extension-name" href="https://implecode.com/wordpress/plugins/' . $url . '/#cam=extensions&key=' . $url . '"><h3><span>' . $name . '</span></h3><span class="click-span">' . __( 'Click for more', 'ecommerce-product-catalog' ) . '</span></a>
	<p>' . $desc . '</p>';
	$disabled	 = '';
	$current_key = get_option( 'custom_license_code' );
	if ( !current_user_can( 'install_plugins' ) ) {
		$disabled	 = 'disabled';
		$current_key = '';
	}
	if ( !empty( $all_ic_plugins ) && is_ic_plugin_active( $slug, $all_ic_plugins ) ) {
		$return .= '<p><a href="https://implecode.com/support/" class="button-primary">Support</a> <a href="https://implecode.com/docs/" class="button-primary">Docs</a> <span class="comp installed">' . __( 'Active Extension', 'ecommerce-product-catalog' ) . '</span></p>';
	} else if ( !empty( $not_active_ic_plugins ) && is_ic_plugin_active( $slug, $not_active_ic_plugins ) ) {
		$return .= '<p><a ' . $disabled . ' href="' . wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $slug . '/' . $slug . '.php' ), 'activate-plugin_' . $slug . '/' . $slug . '.php' ) . '" class="button-primary">Activate Now</a><span class="comp info">' . __( 'Installed Extension', 'ecommerce-product-catalog' ) . '</span></p>';
	} else {
		if ( $comp_class == 'wrong' ) {
			$return .= '<p><a href="https://implecode.com/wordpress/plugins/' . $url . '/#cam=extensions&key=' . $url . '" class="button-primary">See the Extension</a><span class="comp ' . $comp_class . '">' . $comp_txt . '</span></p>';
		} else {
			$return	 .= '<form class="license_form" action=""><input type="hidden" name="implecode_install" value="1"><input type="hidden" name="url" value="' . $url . '"><input type="hidden" name="slug" value="' . $slug . '"><input type="hidden" name="post_type" value="al_product"><input type="hidden" name="page" value="extensions.php"><input type="text" name="license_key" ' . $disabled . ' class="wide" placeholder="License Key..." value="' . $current_key . '">';
			$return	 .= wp_nonce_field( 'install-implecode-plugin_' . $slug, '_wpnonce', 0, 0 );
			$return	 .= '<p class="submit"><input type="submit" ' . $disabled . ' value="Install" class="button-primary"><span class="comp ' . $comp_class . '">' . $comp_txt . '</span> <a href="https://implecode.com/wordpress/plugins/' . $url . '/#cam=extensions&key=' . $url . '" class="button-secondary right">Get your key</a></form></p>';
		}
	}
	$return .= '</div>';
	return $return;
}

/**
 * Shows free extension box
 *
 * @param type $name
 * @param type $url
 * @param type $desc
 * @param type $comp
 * @param type $slug
 * @param type $all_ic_plugins
 * @param type $not_active_ic_plugins
 * @return string
 */
function free_extension_box( $name, $url, $desc, $comp = 'simple', $slug, $all_ic_plugins, $not_active_ic_plugins ) {
	if ( $comp == 'adv' && get_integration_type() == 'simple' ) {
		$comp_txt	 = __( 'Advanced Mode Required', 'post-type-x' );
		$comp_class	 = 'wrong';
	} else if ( $comp == 'price' && !function_exists( 'is_ic_price_enabled' ) ) {
		$comp_txt	 = __( 'Price Required', 'post-type-x' );
		$comp_class	 = 'wrong';
	} else {
		$comp_txt	 = __( 'Ready to Install', 'post-type-x' );
		$comp_class	 = 'good';
	}

	$return		 = '<div class="extension free ' . $url . '">
	<a class="extension-name" href="https://wordpress.org/plugins/' . $url . '"><h3><span>' . $name . '</span></h3><span class="click-span">' . __( 'Click for more', 'post-type-x' ) . '</span></a>
	<p>' . $desc . '</p>';
	$disabled	 = '';
	$current_key = get_option( 'custom_license_code' );
	if ( !current_user_can( 'install_plugins' ) ) {
		$disabled	 = 'disabled';
		$current_key = '';
	}
	if ( !empty( $all_ic_plugins ) && is_ic_plugin_active( $slug, $all_ic_plugins ) ) {
		$return .= '<p><a href="https://wordpress.org/support/plugin/' . $url . '" class="button-primary">Support</a> <a href="https://implecode.com/docs/" class="button-primary">Docs</a> <span class="comp installed">' . __( 'Active Extension', 'post-type-x' ) . '</span></p>';
	} else if ( !empty( $not_active_ic_plugins ) && is_ic_plugin_active( $slug, $not_active_ic_plugins ) ) {
		$return .= '<p><a ' . $disabled . ' href="' . wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode( $slug . '/' . $slug . '.php' ), 'activate-plugin_' . $slug . '/' . $slug . '.php' ) . '" class="button-primary">Activate Now</a><span class="comp info">' . __( 'Installed Extension', 'post-type-x' ) . '</span></p>';
	} else {
		if ( $comp_class == 'wrong' ) {
			$return .= '<p><a href="https://wordpress.org/plugins/' . $url . '" class="button-primary">See the Extension</a><span class="comp ' . $comp_class . '">' . $comp_txt . '</span></p>';
		} else {
			$return	 .= '<form class="license_form" action=""><input type="hidden" name="free_implecode_install" value="1"><input type="hidden" name="url" value="' . $url . '"><input type="hidden" name="slug" value="' . $slug . '"><input type="hidden" name="post_type" value="al_product"><input type="hidden" name="page" value="extensions.php"><input type="hidden" name="tab" value="product-extensions">';
			$return	 .= wp_nonce_field( 'install-implecode-plugin_' . $slug, '_wpnonce', 0, 0 );
			$return	 .= '<p class="submit"><input type="submit" ' . $disabled . ' value="Install" class="button-primary"><span class="comp ' . $comp_class . '">' . $comp_txt . '</span></form></p>';
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
					<h4><strong>' . sprintf( __( 'This is not a valid license key! Get it <a href="%s">here</a>.', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/plugins/' . $_GET[ 'url' ] . '/#cam=extensions&key=' . $_GET[ 'url' ] ) . '</strong></h4>
				</div>
			</div>';
		} else {
			echo '<div id="message error" class="error product-adder-message messages-connect">
				<div class="squeezer">
					<h4><strong>' . sprintf( __( 'The supplied license key is not valid for this extension! Upgrade it <a href="%s">here</a>.', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/plugins/' . $_GET[ 'url' ] . '/#cam=extensions&key=' . $_GET[ 'url' ] ) . '</strong></h4>
				</div>
			</div>';
		}
	} else if ( isset( $_GET[ 'implecode_install' ] ) && !empty( $_GET[ 'slug' ] ) && empty( $_GET[ 'license_key' ] ) && current_user_can( 'install_plugins' ) ) {
		echo '<div id="message error" class="error product-adder-message messages-connect">
				<div class="squeezer">
					<h4><strong>' . sprintf( __( 'You need to provide the license key to activate the extension. Get yours <a href="%s">here</a>.', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/plugins/' . $_GET[ 'url' ] . '/#cam=extensions&key=' . $_GET[ 'url' ] ) . '</strong></h4>
				</div>
			</div>';
	} else if ( !current_user_can( 'install_plugins' ) ) {
		echo '<div id="message error" class="error product-adder-message messages-connect">
				<div class="squeezer">
					<h4><strong>' . __( 'You don\'t have permission to install and activate extensions.', 'ecommerce-product-catalog' ) . '</strong></h4>
				</div>
			</div>';
	}
}

/**
 * Installs plugin available in WordPress repository
 *
 */
function start_free_implecode_install() {
	if ( isset( $_GET[ 'free_implecode_install' ] ) && !empty( $_GET[ 'slug' ] ) && wp_verify_nonce( $_GET[ '_wpnonce' ], 'install-implecode-plugin_' . $_GET[ 'slug' ] ) == 1 && current_user_can( 'install_plugins' ) ) {
		$slug	 = esc_html( $_GET[ 'slug' ] );
		$url	 = implecode_free_installation_url( $slug );
		if ( $url != 'error' ) {
			add_filter( 'install_plugin_complete_actions', 'implecode_install_actions', 10, 3 );
			echo '<div class="extension_installer">';
			include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
			$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
			$upgrader->install( $url );
			echo '</div>';
		} else {
			echo '<div id="message error" class="error product-adder-message messages-connect">
				<div class="squeezer">
					<h4><strong>' . __( 'This extension is not available at this time. Try again later.', 'post-type-x' ) . '</strong></h4>
				</div>
			</div>';
		}
	}

	/* else if ( !current_user_can( 'install_plugins' ) ) {
	  echo '<div id="message error" class="error product-adder-message messages-connect">
	  <div class="squeezer">
	  <h4><strong>' . __( 'You don\'t have permission to install and activate extensions.', 'post-type-x' ) . '</strong></h4>
	  </div>
	  </div>';
	  }
	 *
	 */
}

function implecode_install_actions( $install_actions, $api, $plugin_file ) {
	$install_actions[ 'plugins_page' ] = '<a href="' . admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=product-extensions' ) . '">' . __( 'Reload the Page', 'ecommerce-product-catalog' ) . '</a>';
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

/**
 * Returns installation URL from WordPress repository
 *
 * @param type $slug
 * @return string
 */
function implecode_free_installation_url( $slug ) {
	$url			 = 'https://downloads.wordpress.org/plugin/' . $slug . '.latest-stable.zip';
	$file_headers	 = @get_headers( $url );
	if ( $file_headers[ 0 ] == 'HTTP/1.1 404 Not Found' ) {
		return 'error';
	} else {
		return $url;
	}
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
		if ( $not_active_plugin[ 'Author' ] == 'Norbert Dreszer' && $not_active_plugin[ 'Name' ] != 'eCommerce Product Catalog by impleCode' && $not_active_plugin[ 'Name' ] != 'Post Type X' ) {
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

add_action( 'settings-menu', 'add_product_catalog_upgrade_url', 99 );

/**
 * Adds upgrade link in product settings menu
 *
 */
function add_product_catalog_upgrade_url() {
	if ( !function_exists( 'start_implecode_updater' ) ) {
		//echo '<a target="_blank" title="' . __( 'Now you can get multiple extensions at once with the lowest price ever.', 'ecommerce-product-catalog' ) . '" class="upgrade-now" href="https://implecode.com/choose-a-plan/#cam=bundles&key=settings-top-menu">' . __( 'Upgrade Now!', 'ecommerce-product-catalog' ) . '</a>';
	}
}

add_action( 'extensions-menu', 'add_product_catalog_bundle_url', 99 );

/**
 * Adds upgrade link in porudct extensions menu
 *
 */
function add_product_catalog_bundle_url() {
	if ( !function_exists( 'start_implecode_updater' ) ) {
		//echo '<a target="_blank" title="' . __( 'Now you can get multiple extensions at once with the lowest price ever.', 'ecommerce-product-catalog' ) . '" class="upgrade-now" href="https://implecode.com/choose-a-plan/#cam=bundles&key=extensions-top-menu">' . __( 'Now extensions bundles from $19.99!', 'ecommerce-product-catalog' ) . '</a>';
	}
}

add_action( 'ic_before_extensions_list', 'extensions_bundle_box', 5 );

/**
 * Shows bundle box before extensions list
 *
 */
function extensions_bundle_box() {
	if ( !function_exists( 'start_implecode_updater' ) ) {
		echo '<div class="bundle-box">' . __( 'Do you need multiple extensions?', 'ecommerce-product-catalog' ) . ' <a href="https://implecode.com/choose-a-plan/#cam=bundles&key=extensions-bundle-box">' . __( 'Check out extensions bundles', 'ecommerce-product-catalog' ) . '</a></div>';
	}
}

/**
 * Returns impleCode plugins available in WordPress repository that are active on the website
 *
 * @return type
 */
function get_free_implecode_active_plugins() {
	$all_active = get_option( 'active_plugins' );
	if ( !function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$all_plugins = get_plugins();
	$i			 = 0;
	$ic_plugins	 = array();
	foreach ( $all_active as $active_name ) {
		if ( $all_plugins[ $active_name ][ 'Author' ] == 'impleCode' && $all_plugins[ $active_name ][ 'Name' ] != 'eCommerce Product Catalog by impleCode' ) {
			$ic_plugins[ $i ][ 'dir_file' ]	 = $active_name;
			$active_name					 = explode( '/', $active_name );
			$ic_plugins[ $i ][ 'slug' ]		 = $active_name[ 0 ];
		}
		$i++;
	}
	return $ic_plugins;
}

/**
 * Returns impleCode plugins available in WordPress repository that are active on the website
 *
 * @return type
 */
function get_implecode_free_not_active_plugins() {
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
		if ( $not_active_plugin[ 'Author' ] == 'impleCode' && $not_active_plugin[ 'Name' ] != 'eCommerce Product Catalog by impleCode' ) {
			$ic_plugins[ $i ][ 'dir_file' ]	 = $not_active_name;
			$not_active_name				 = explode( '/', $not_active_name );
			$ic_plugins[ $i ][ 'slug' ]		 = $not_active_name[ 0 ];
		}
		$i++;
	}
	return $ic_plugins;
}

add_action( 'ic_before_extensions_list', 'ic_epc_free_extensions' );

/**
 * Shows Post Type X free extensions
 *
 */
function ic_epc_free_extensions() {
	if ( false === ($extensions = get_site_transient( 'implecode_epc_free_extensions_data' )) ) {
		$extensions = wp_remote_get( 'http://app.implecode.com/index.php?provide_extensions&free_epc=1' );
		if ( !is_wp_error( $extensions ) && 200 == wp_remote_retrieve_response_code( $extensions ) ) {
			$extensions = json_decode( wp_remote_retrieve_body( $extensions ), true );
			if ( $extensions ) {
				set_site_transient( 'implecode_epc_free_extensions_data', $extensions, 60 * 60 * 24 * 7 );
			}
		} else {
			$extensions = implecode_free_extensions();
		}
	}
	$all_ic_plugins = '';
	if ( function_exists( 'get_free_implecode_active_plugins' ) ) {
		$all_ic_plugins = get_free_implecode_active_plugins();
	}
	$not_active_ic_plugins = get_implecode_free_not_active_plugins();
	echo '<div class="free-extensions">';
	foreach ( $extensions as $extension ) {
		$extension[ 'type' ] = isset( $extension[ 'type' ] ) ? $extension[ 'type' ] : 'premium';
		echo extension_box( $extension[ 'name' ], $extension[ 'url' ], $extension[ 'desc' ], $extension[ 'comp' ], $extension[ 'slug' ], $all_ic_plugins, $not_active_ic_plugins, $extension[ 'type' ] );
	}
	echo '</div>';
}

//add_action( 'general_submenu', 'ic_epc_submenu_extensions_info', 99 );

function ic_epc_submenu_extensions_info() {
	if ( !function_exists( 'start_implecode_updater' ) ) {
		echo '<span class="extensions-promo-box">' . sprintf( __( 'Add free & premium features %shere%s.', 'ecommerce-product-catalog' ), '<a href="' . admin_url( 'edit.php?post_type=al_product&page=extensions.php' ) . '">', '</a>' ) . '</span>';
	}
}

function implecode_free_extensions() {
	$free_extensions = array(
		array(
			'url'	 => 'reviews-plus',
			'name'	 => 'Product Reviews',
			'desc'	 => 'Add reviews support for your catalog items. Use it for all or selected products.',
			'comp'	 => 'simple',
			'slug'	 => 'reviews-plus',
			'type'	 => 'free'
		),
	);
	return $free_extensions;
}

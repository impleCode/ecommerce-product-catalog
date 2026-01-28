<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Manages product settings
 *
 * Here product settings are defined and managed.
 *
 * @version        1.1.4
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
function register_product_extensions() {
    add_submenu_page( 'edit.php?post_type=al_product', __( 'Extensions', 'ecommerce-product-catalog' ), '<span class="extensions">' . __( 'Extensions', 'ecommerce-product-catalog' ) . '</span>', 'manage_product_settings', basename( __FILE__ ), 'product_extensions' );
}

//add_action( 'extensions-menu', 'ic_epc_extensions_menu_elements' );

/**
 * Generates eCommerce Product Catalog extensions menu
 *
 */
function ic_epc_extensions_menu_elements() {
    ?>
    <a id="extensions" class="nav-tab"
       href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=product-extensions' ) ?>"><?php _e( 'Installation', 'ecommerce-product-catalog' ); ?></a>
    <?php
    /*
      <a id="new-extensions" class="nav-tab" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=new-product-extensions' ) ?>"><?php _e( 'New', 'ecommerce-product-catalog' ); ?></a>
     */
}

add_action( 'product_settings_menu', 'register_product_extensions' );

function product_extensions() {
    ?>
    <div id="implecode_settings" class="wrap">
        <h1 class="wp-heading-inline"><?php echo sprintf( __( 'Extensions for %s', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) ?></h1>
        <?php do_action( 'ic_cat_extensions_page_start' ) ?>
        <hr class="wp-header-end">
        <h3><?php _e( 'All premium extensions come with premium support provided by the dev team.<br>Feel free to contact impleCode for configuration help, troubleshooting, installation assistance and any other plugin support at any time!', 'ecommerce-product-catalog' ) ?></h3>
        <?php ic_product_settings_html() ?>
        <?php /*
		  <h2 class="nav-tab-wrapper">
		  <?php do_action( 'extensions-menu' ) ?>
		  <a id="help" class="nav-tab"
		  href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=help' ) ?>"><?php _e( 'Help', 'ecommerce-product-catalog' ); ?></a>
		  </h2>
		 *
		 */
        ?>
        <div class="table-wrapper">
            <?php
            $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
            /* GENERAL SETTINGS */

            if ( $tab == 'product-extensions' or $tab == '' ) {
                ?>
                <div class="extension-list">
                    <script>
                        jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
                        jQuery('.nav-tab-wrapper a#extensions').addClass('nav-tab-active');
                    </script><?php
                    start_implecode_install();
                    start_free_implecode_install();
                    if ( false === ( $extensions = get_site_transient( 'implecode_extensions_data' ) ) ) {
                        $extensions_remote_url = apply_filters( 'ic_extensions_remote_url', 'provide_extensions' );
                        $extensions            = wp_remote_get( 'https://app.implecode.com/index.php?' . $extensions_remote_url );
                        if ( ! is_wp_error( $extensions ) && 200 == wp_remote_retrieve_response_code( $extensions ) ) {
                            $extensions = json_decode( wp_remote_retrieve_body( $extensions ), true );
                            if ( $extensions ) {
                                set_site_transient( 'implecode_extensions_data', $extensions, WEEK_IN_SECONDS );
                            }
                        } else {
                            $extensions = implecode_extensions();
                        }
                    }
                    $all_ic_plugins = array();
                    if ( function_exists( 'get_implecode_active_plugins' ) ) {
                        $all_ic_plugins = get_implecode_active_plugins();
                    }
                    $all_ic_plugins = array_merge( get_implecode_active_free_plugins(), $all_ic_plugins );

                    $not_active_ic_plugins = get_implecode_not_active_plugins();

                    do_action( 'ic_before_extensions_list', $tab );
                    $extensions         = apply_filters( 'ic_cat_extensions', $extensions );
                    $count              = 1;
                    $extensions_by_type = array();
                    $number             = 2;
                    foreach ( $extensions as $extension ) {
                        $extension['type'] = isset( $extension['type'] ) ? $extension['type'] : 'premium';
                        if ( $count % $number == 0 && ! empty( $extensions_by_type ) ) {
                            if ( ! empty( $extensions_by_type[0] ) ) {
                                echo extension_box( $extensions_by_type[0], $all_ic_plugins, $not_active_ic_plugins );
                                unset( $extensions_by_type[0] );
                                $number ++;
                            }
                            if ( ! empty( $extensions_by_type ) ) {
                                $extensions_by_type = array_values( $extensions_by_type );
                            }
                            $count ++;
                        } else if ( $extension['type'] == 'free' ) {
                            $extensions_by_type[] = $extension;
                            continue;
                        }
                        echo extension_box( $extension, $all_ic_plugins, $not_active_ic_plugins );
                        $count ++;
                    }
                    ic_show_affiliate_content();
                    ?>
                </div>
                <div class="helpers">
                    <div class="wrapper"><h2><?php _e( 'Did you Know?', 'ecommerce-product-catalog' ) ?></h2><?php
                        text_helper( '', __( 'All extensions are designed to work with each other smoothly.', 'ecommerce-product-catalog' ) );
                        text_helper( '', __( 'Some extensions give even more features when combined with another one.', 'ecommerce-product-catalog' ) );
                        text_helper( '', __( 'Click on the extension to see full features list.', 'ecommerce-product-catalog' ) );
                        text_helper( '', __( 'Paste your license key and click the install button to start the extension installation process.', 'ecommerce-product-catalog' ) );
                        ?>
                    </div>
                </div> <?php
            } else if ( $tab == 'new-product-extensions' ) {
                ?>
                <div class="extension-list">
                    <script>
                        jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
                        jQuery('.nav-tab-wrapper a#new-extensions').addClass('nav-tab-active');
                    </script><?php
                    start_implecode_install();
                    if ( false === ( $extensions = get_site_transient( 'implecode_new_extensions_data' ) ) ) {
                        $extensions = wp_remote_get( 'https://app.implecode.com/index.php?provide_extensions&new=1' );
                        if ( ! is_wp_error( $extensions ) || 200 != wp_remote_retrieve_response_code( $extensions ) ) {
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
                        echo extension_box( $extension, $all_ic_plugins, $not_active_ic_plugins );
                    }
                    ?>
                </div>
                <div class="helpers">
                    <div class="wrapper"><h2><?php _e( 'Did you Know?', 'ecommerce-product-catalog' ) ?></h2><?php
                        text_helper( '', __( 'All extensions are designed to work with each other smoothly.', 'ecommerce-product-catalog' ) );
                        text_helper( '', __( 'Some extensions give even more features when combined with another one.', 'ecommerce-product-catalog' ) );
                        text_helper( '', __( 'Click on the extension to see full features list.', 'ecommerce-product-catalog' ) );
                        text_helper( '', __( 'Paste your license key and click the install button to start the extension installation process.', 'ecommerce-product-catalog' ) );
                        ?>
                    </div>
                </div>
                <?php
            } else if ( $tab == 'all-product-extensions' ) {
                ?>
                <div class="extension-list">
                    <script>
                        jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
                        jQuery('.nav-tab-wrapper a#extensions').addClass('nav-tab-active');
                    </script><?php
                    start_implecode_install();
                    if ( false === ( $extensions = get_site_transient( 'implecode_extensions_data' ) ) ) {
                        $extensions = wp_remote_get( 'https://app.implecode.com/index.php?provide_extensions' );
                        if ( ! is_wp_error( $extensions ) || 200 != wp_remote_retrieve_response_code( $extensions ) ) {
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
                    $extensions = apply_filters( 'ic_cat_extensions', $extensions );

                    foreach ( $extensions as $extension ) {
                        echo extension_box( $extension, $all_ic_plugins, $not_active_ic_plugins );
                    }
                    ?>
                </div>
                <div class="helpers">
                    <div class="wrapper"><h2><?php _e( 'Did you Know?', 'ecommerce-product-catalog' ) ?></h2><?php
                        text_helper( '', __( 'All extensions are designed to work with each other smoothly.', 'ecommerce-product-catalog' ) );
                        text_helper( '', __( 'Some extensions give even more features when combined with another one.', 'ecommerce-product-catalog' ) );
                        text_helper( '', __( 'Click on the extension to see full features list.', 'ecommerce-product-catalog' ) );
                        text_helper( '', __( 'Paste your license key and click the install button to start the extension installation process.', 'ecommerce-product-catalog' ) );
                        ?>
                    </div>
                </div>
                <?php
            } else if ( $tab == 'help' ) {
                do_action( 'ic_extensions_page_help_top' );
                ?>
                <div class="help">
                    <script>
                        jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
                        jQuery('.nav-tab-wrapper a#help').addClass('nav-tab-active');
                    </script> <?php
                    do_action( 'ic_extensions_page_help_text' );
                    ?>
                    <h3><?php _e( 'Getting Started', 'ecommerce-product-catalog' ) ?></h3>
                    <ol>
                        <li>Go to the <a
                                    href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings' ) ?>">general
                                settings page</a></li>
                        <li>Select your main catalog page and the template</li>
                        <li><a href="<?php echo admin_url( 'post-new.php?post_type=al_product' ) ?>">Add your first
                                product</a> and check how it looks like by clicking the generated link after you publish
                            it
                        </li>
                        <li>Select what should be shown on main listing page (Main listing shows option)</li>
                        <li>Select what should be shown on category pages (Categories Settings)</li>
                        <li>Check your main listing page and click the categories and product pages</li>
                        <li>Make necessary adjustments in <a
                                    href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings' ) ?>">General
                                settings</a>, <a
                                    href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=attributes-settings&submenu=attributes' ) ?>">Attributes</a>,
                            <a href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=shipping-settings&submenu=shipping' ) ?>">Shipping</a>,
                            <a href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=design-settings&submenu=archive-design' ) ?>">Catalog
                                Design</a> and <a
                                    href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=names-settings&submenu=single-names' ) ?>">Front-end
                                Labels</a></li>
                        <li>Use the search box on the top of this page if you need more information</li>
                        <li>Check the <a
                                    href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php' ) ?>">Add-ons
                                and Integrations</a> for more free and premium features
                        </li>
                        <li>Use free <a target="_blank"
                                        href="https://wordpress.org/support/plugin/ecommerce-product-catalog/">support
                                forum</a> or <a target="_blank"
                                                href="https://implecode.com/support/#cam=help-tab&key=premium-support">premium
                                support service</a> to get professional help
                        </li>
                        <li>That's it. Enjoy!</li>
                    </ol>
                    <h3><?php _e( 'How to get help?', 'ecommerce-product-catalog' ) ?></h3>
                    <p>The developers provide both free and premium support:</p>
                    <a class="button-secondary" target="_blank"
                       href="https://wordpress.org/support/plugin/ecommerce-product-catalog/">Free Support Forum</a>
                    <a class="button-secondary" target="_blank"
                       href="https://implecode.com/support/#cam=help-tab&key=support">Premium Support Service</a>
                    <h3><?php _e( 'How to Install the extension?', 'ecommerce-product-catalog' ) ?></h3>
                    <ol>
                        <li>Go to the <a
                                    href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php' ) ?>">extensions
                                page</a></li>
                        <li>Click the "Get your key" button on the extension that you want to install;</li>
                        <li>You will be redirected to the impleCode website. Read the extension description, choose
                            license type, click the Add to Cart button and fill the form;
                        </li>
                        <li>Your license key will be immediately sent to you by email provided in the previous step;
                        </li>
                        <li>Copy and Paste the license key to the license key field on the extension that you want to
                            install;
                        </li>
                        <li>Click the install button and wait until the installation process is done. The installer will
                            establish a secure connection with impleCode to get the installation files;
                        </li>
                        <li>Click the activation button;</li>
                        <li>That's it. Enjoy!</li>
                    </ol>
                    <p>In case you prefer to install the extension manually you will get also the installation files in
                        the customer panel. See <a
                                href="https://implecode.com/wordpress/product-catalog/plugin-installation-guide/#cam=extensions-help&key=manual-installation#manual">manual
                            installation guide</a> for this.</p>
                    <p>Please see the <a href="https://implecode.com/faq/#cam=extensions-help&key=faq">FAQ</a> for
                        additional information</p>
                </div>
                <div class="helpers">
                    <div class="wrapper"><h2><?php _e( 'Did you Know?', 'ecommerce-product-catalog' ) ?></h2><?php
                        text_helper( '', __( 'The installation process takes less than 10 seconds.', 'ecommerce-product-catalog' ) );
                        text_helper( '', sprintf( __( 'You can take advantage of premium support and <a href="%s">send support tickets</a> to impleCode developers once you have your license key.', 'ecommerce-product-catalog' ), 'https://implecode.com/support/?support_type=support' ) );
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>

        <div style="clear:both; height: 50px;"></div>
        <div class="plugin-logo">
            <a href="https://implecode.com/#cam=catalog-settings-link&key=logo-link"><img class="en"
                                                                                          src="<?php echo AL_PLUGIN_BASE_PATH . 'img/implecode.png'; ?>"
                                                                                          width="282px"
                                                                                          alt="impleCode"/></a>
        </div>
    </div>
    <?php
}

function implecode_extensions() {
    $extensions = array(
            'implecode-product-sidebar'   => array(
                    'url'  => 'premium-support',
                    'name' => 'Premium Toolset',
                    'desc' => 'Product sidebar, product tags, enhanced category widget and premium email support.',
                    'comp' => 'simple',
                    'slug' => 'implecode-product-sidebar',
            ),
            'implecode-shopping-cart'     => array(
                    'url'  => 'shopping-cart',
                    'name' => 'Shopping Cart',
                    'desc' => 'Full featured shopping cart with advanced customisation options. Transform your product catalog into a Web Store!',
                    'comp' => 'simple',
                    'slug' => 'implecode-shopping-cart',
            ),
            'implecode-quote-cart'        => array(
                    'url'  => 'quote-cart',
                    'name' => 'Quote Cart',
                    'desc' => 'Allow your users to send a quote for multiple products. Quote Cart adds a store like experience even for products without price!',
                    'comp' => 'simple',
                    'slug' => 'implecode-quote-cart',
            ),
            'implecode-quote-form'        => array(
                    'url'  => 'quote-form',
                    'name' => 'Single Product Quote Form',
                    'desc' => 'Improve the conversion rate with quote/inquiry button which redirects to fully customizable product quote form.',
                    'comp' => 'simple',
                    'slug' => 'implecode-quote-form',
            ),
            'implecode-order-form'        => array(
                    'url'  => 'order-form',
                    'name' => 'Single Product Order Form',
                    'desc' => 'This powerful extension allows you to sell individual products with buy now button and fully customizable order form.',
                    'comp' => 'simple',
                    'slug' => 'implecode-order-form',
            ),
            'implecode-paypal-gateway'    => array(
                    'url'  => 'paypal-gateway',
                    'name' => 'PayPal Gateway',
                    'desc' => 'Boost the conversion rate with a robust PayPal shopping cart, buy now button or order form implementation.',
                    'comp' => 'simple',
                    'slug' => 'implecode-paypal-gateway',
            ),
            '2checkout-gateway'           => array(
                    'url'  => '2checkout-gateway',
                    'name' => '2Checkout Gateway',
                    'desc' => 'Take credit card payments with 2Checkout Gateway.',
                    'comp' => 'simple',
                    'slug' => '2checkout-gateway',
            ),
            'catalog-users-manager'       => array(
                    'url'  => 'catalog-users-manager',
                    'name' => 'Catalog Users Manager',
                    'desc' => 'Manage catalog visibility options depending on logged in visitor.',
                    'comp' => 'simple',
                    'slug' => 'catalog-users-manager',
            ),
            'implecode-printable-coupons' => array(
                    'url'  => 'printable-coupons',
                    'name' => 'Printable Coupons',
                    'desc' => 'Sell printable coupons for your products or for certain value directly from the website. Generate customized coupons!',
                    'comp' => 'simple',
                    'slug' => 'implecode-printable-coupons',
            ),
            'product-page-customizer'     => array(
                    'url'  => 'product-page-customizer',
                    'name' => 'Product Page Customizer',
                    'desc' => 'Customize product page with simple settings. Change product page elements, their size, position and colors easily in a few seconds.',
                    'comp' => 'simple',
                    'slug' => 'product-page-customizer',
            ),
            'product-gallery-advanced'    => array(
                    'url'  => 'product-gallery-advanced',
                    'name' => 'Product Gallery Advanced',
                    'desc' => 'Add unlimited number of product images and show them in a robust product slider or beautiful light-box presentation.',
                    'comp' => 'simple',
                    'slug' => 'product-gallery-advanced',
            ),
            'custom-product-order'        => array(
                    'url'  => 'custom-product-order',
                    'name' => 'Custom Product Order',
                    'desc' => 'Sort products by priority, lowest price, highest price or randomly. New options in sort drop-down. Assign featured products.',
                    'comp' => 'simple',
                    'slug' => 'custom-product-order',
            ),
            'implecode-upload-pdf'        => array(
                    'url'  => 'upload-pdf',
                    'name' => 'Upload PDF',
                    'desc' => 'Easily attach unlimited PDF files to the products, upload to server and provide to clients on product pages.',
                    'comp' => 'simple',
                    'slug' => 'implecode-upload-pdf',
            ),
            'product-pdf'                 => array(
                    'url'  => 'product-pdf',
                    'name' => 'Product Print & PDF',
                    'desc' => 'Print product pages with one click. Export product pages to PDF files with easy.',
                    'comp' => 'simple',
                    'slug' => 'product-pdf',
            ),
            'product-manufacturers'       => array(
                    'url'  => 'product-manufacturers',
                    'name' => 'Product Manufacturers',
                    'desc' => 'Manage product manufacturers & brands in separate screen and easily assign them to products. It has never been so simple!',
                    'comp' => 'simple',
                    'slug' => 'product-manufacturers',
            ),
            'implecode-product-search'    => array(
                    'url'  => 'product-search-pro',
                    'name' => 'Product Search PRO',
                    'desc' => 'Improve WordPress default search engine to provide better product search results. Show product search form with a shortcode.',
                    'comp' => 'adv',
                    'slug' => 'implecode-product-search',
            ),
            'smart-multiple-catalogs'     => array(
                    'url'  => 'smart-multiple-catalogs',
                    'name' => 'Smart Multiple Catalogs',
                    'desc' => 'Create completely separate, multiple catalogs at one website. Assign separate categories, parent URLs, manage them from different...',
                    'comp' => 'simple',
                    'slug' => 'smart-multiple-catalogs',
            ),
            'smarter-product-urls'        => array(
                    'url'  => 'smarter-product-urls',
                    'name' => 'Smarter Product URLs',
                    'desc' => 'Set up SEO and USER friendly product page URLs. Add product category in product page URLs.',
                    'comp' => 'adv',
                    'slug' => 'smarter-product-urls',
            ),
            'implecode-product-locations' => array(
                    'url'  => 'product-locations',
                    'name' => 'Product Locations',
                    'desc' => 'Easily manage product locations and get product quotes to multiple email addresses directly from product pages.',
                    'comp' => 'simple',
                    'slug' => 'implecode-product-locations',
            ),
            'product-attributes-pro'      => array(
                    'url'  => 'product-attributes-pro',
                    'name' => 'Product Attributes PRO',
                    'desc' => 'Filter products by attributes. Select attributes values with a drop-down, checkbox or radio button.',
                    'comp' => 'simple',
                    'slug' => 'product-attributes-pro',
            ),
            'advanced-shipping-table'     => array(
                    'url'  => 'advanced-shipping-tables',
                    'name' => 'Advanced Shipping Tables',
                    'desc' => 'Calculates shipping based on Shopping Cart total and checkout fields values.',
                    'comp' => 'simple',
                    'slug' => 'advanced-shipping-table',
            ),
            'implecode-product-csv'       => array(
                    'url'  => 'product-csv',
                    'name' => 'Product CSV',
                    'desc' => 'Import, Export & Update products all fields and attributes with a simple CSV file.',
                    'comp' => 'simple',
                    'slug' => 'implecode-product-csv',
            ),
            'multiple-product-price'      => array(
                    'url'  => 'multiple-prices',
                    'name' => 'Multiple Pricing',
                    'desc' => 'Set multiple, automatically calculated or manually inserted prices for each product.',
                    'comp' => 'simple',
                    'slug' => 'multiple-product-price',
            ),
            'implecode-product-discounts' => array(
                    'url'  => 'product-discounts',
                    'name' => 'Product Discounts',
                    'desc' => 'Apply percentage or value discounts for catalog products. Show the discount offers with a robust widget or shortcode and more!',
                    'comp' => 'simple',
                    'slug' => 'implecode-product-discounts',
            ),
            'table-view'                  => array(
                    'url'  => 'table-view',
                    'name' => 'Table View',
                    'desc' => 'Show products in nicely formatted table with customizable columns.',
                    'comp' => 'simple',
                    'slug' => 'table-view',
            ),
            'classic-list-button'         => array(
                    'url'  => 'classic-list-button',
                    'name' => 'Classic List with Button',
                    'desc' => 'Premium product listing theme for your catalog. Easily set image size, description name and button position.',
                    'comp' => 'simple',
                    'slug' => 'classic-list-button',
            ),
            'slim-classic-grid'           => array(
                    'url'  => 'slim-grid',
                    'name' => 'Slim Grid Theme',
                    'desc' => 'Premium Grid Theme for product listing. Has additional settings for size, per row elements, description length and price.',
                    'comp' => 'simple',
                    'slug' => 'slim-classic-grid',
            ),
            'no-image-grid'               => array(
                    'url'  => 'no-image-grid',
                    'name' => 'No Image Grid Theme',
                    'desc' => 'Premium Grid Theme for product listing. Best for products without image.',
                    'comp' => 'simple',
                    'slug' => 'no-image-grid',
            ),
            'side-grid'                   => array(
                    'url'  => 'side-grid',
                    'name' => 'Side Grid',
                    'desc' => 'Premium product listing grid with image on the left side.',
                    'comp' => 'simple',
                    'slug' => 'side-grid',
            ),
    );

    return $extensions;
}

function implecode_affiliate_extensions() {
    $extensions = array(
            array(
                    'url'  => 'https://wpml.org/purchase/?aid=89119&affiliate_key=x7MQob0JrgTA',
                    'name' => 'WPML - Multilingual Catalog',
                    'desc' => 'eCommerce Product Catalog is fully compatible with WPML - the WordPress Multilingual plugin. WPML lets you add languages to your existing sites and includes advanced translation management.',
                    'comp' => 'simple',
            )
    );

    return $extensions;
}

add_action( 'ic_epc_loaded', 'initialize_affiliate_scripts', 20 );

function initialize_affiliate_scripts() {
    if ( is_admin() ) {
        if ( ! defined( 'ICL_AFFILIATE_ID' ) ) {
            define( 'ICL_AFFILIATE_ID', '89119' );
        }
        if ( ! defined( 'ICL_AFFILIATE_KEY' ) ) {
            define( 'ICL_AFFILIATE_KEY', 'x7MQob0JrgTA' );
        }
    }
}

function ic_show_affiliate_content() {
    return;
    $affiliates = implecode_affiliate_extensions();
    $output     = '';
    foreach ( $affiliates as $affiliate ) {
        $output .= extension_affiliate_box( $affiliate['name'], $affiliate['url'], $affiliate['desc'], $affiliate['comp'] );
    }
    if ( ! empty( $output ) ) {
        echo '<h2 class="partners-header">' . __( 'Fully compatible plugins from our partners', 'ecommerce-product-catalog' ) . '</h2>';
        echo '<p>You can also use third party plugins and themes fully compatible with ' . IC_CATALOG_PLUGIN_NAME . '. Please note that ' . IC_CATALOG_PLUGIN_NAME . ' developers get a small affiliate commision from every purchase made through the links below. This actually helps the devs to support the integration between plugins more effectively.</p>';
        echo $output;
    }
}

function extension_box( $extension, $all_ic_plugins, $not_active_ic_plugins ) {
    $name = $extension['name'];
    $url  = $extension['url'];
    $desc = $extension['desc'];
    $comp = $extension['comp'];
    $slug = $extension['slug'];
    $type = ! empty( $extension['type'] ) ? $extension['type'] : 'premium';
    if ( $type == 'free' ) {
        return free_extension_box( $name, $url, $desc, $comp, $slug, $all_ic_plugins, $not_active_ic_plugins );
    }
    if ( $comp == 'adv' && get_integration_type() == 'simple' ) {
        $comp_txt   = __( 'Advanced Mode Required', 'ecommerce-product-catalog' );
        $comp_class = 'wrong';
    } else {
        $comp_txt   = __( 'Ready to Install', 'ecommerce-product-catalog' );
        $comp_class = 'good';
    }

    $return      = '<div class="extension ' . $slug . '">
	<a class="extension-name" href="https://implecode.com/wordpress/plugins/' . $url . '/#cam=extensions&key=' . $url . '"><h3><span>' . $name . '</span></h3><span class="click-span">' . __( 'Click for more', 'ecommerce-product-catalog' ) . '</span></a>
	<p>' . $desc . '</p>';
    $disabled    = '';
    $current_key = get_option( 'custom_license_code' );
    if ( ! current_user_can( 'install_plugins' ) ) {
        $disabled    = 'disabled';
        $current_key = '';
    }
    if ( ! empty( $all_ic_plugins ) && is_ic_plugin_active( $slug, $all_ic_plugins ) ) {
        $return .= '<p><a href="https://implecode.com/support/" class="button-primary">Support</a> <a href="https://implecode.com/docs/" class="button-primary">Docs</a> <span class="comp installed">' . __( 'Active Extension', 'ecommerce-product-catalog' ) . '</span></p>';
    } else if ( ! empty( $not_active_ic_plugins ) && is_ic_plugin_active( $slug, $not_active_ic_plugins ) ) {
        $plugin_file = ic_cat_get_plugin_file( $slug );
        $return      .= '<p><a ' . $disabled . ' href="' . wp_nonce_url( 'plugins.php?ic_cat_activation=1&action=activate&amp;plugin=' . urlencode( $plugin_file ), 'activate-plugin_' . $plugin_file ) . '" class="button-primary">Activate Now</a><span class="comp info">' . __( 'Installed Extension', 'ecommerce-product-catalog' ) . '</span></p>';
    } else {
        if ( $comp_class == 'wrong' ) {
            $return .= '<p><a href="https://implecode.com/wordpress/plugins/' . $url . '/#cam=extensions&key=' . $url . '" class="button-primary">See the Extension</a><span class="comp ' . $comp_class . '">' . $comp_txt . '</span></p>';
        } else {
            $return .= '<form class="license_form" action=""><input type="hidden" name="implecode_install" value="1"><input type="hidden" name="url" value="' . $url . '"><input type="hidden" name="slug" value="' . $slug . '"><input type="hidden" name="post_type" value="al_product"><input type="hidden" name="page" value="extensions.php"><input type="text" name="license_key" ' . $disabled . ' class="wide" placeholder="License Key..." value="' . $current_key . '">';
            $return .= wp_nonce_field( 'install-implecode-plugin_' . $slug, '_wpnonce', 0, 0 );
            $return .= '<p class="submit"><input type="submit" ' . $disabled . ' value="Install" class="button-primary"><span class="comp ' . $comp_class . '">' . $comp_txt . '</span> <a href="https://implecode.com/wordpress/plugins/' . $url . '/#cam=extensions&key=' . $url . '" class="button-secondary right">Get your key</a></form></p>';
        }
    }
    $return .= '</div>';

    return $return;
}

function ic_cat_get_plugin_file( $slug ) {
    if ( $slug == 'catalog-booster-for-woocommerce' ) {
        $file_name = 'woocommerce-catalog-booster';
    } else {
        $file_name = $slug;
    }
    $plugin_file = $slug . '/' . $file_name . '.php';

    return $plugin_file;
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
 *
 * @return string
 */
function free_extension_box( $name, $url, $desc, $comp, $slug, $all_ic_plugins, $not_active_ic_plugins ) {
    if ( $comp == 'adv' && get_integration_type() == 'simple' ) {
        $comp_txt   = __( 'Advanced Mode Required', 'ecommerce-product-catalog' );
        $comp_class = 'wrong';
    } else if ( $comp == 'price' && ! function_exists( 'is_ic_price_enabled' ) ) {
        $comp_txt   = __( 'Price Required', 'ecommerce-product-catalog' );
        $comp_class = 'wrong';
    } else {
        $comp_txt   = __( 'Ready to Install', 'ecommerce-product-catalog' );
        $comp_class = 'good';
    }

    $return      = '<div class="extension free ' . $url . '">
	<a class="extension-name" href="https://wordpress.org/plugins/' . $url . '"><h3><span>' . $name . '</span></h3><span class="click-span">' . __( 'Click for more', 'ecommerce-product-catalog' ) . '</span></a>
	<p>' . $desc . '</p>';
    $disabled    = '';
    $current_key = get_option( 'custom_license_code' );
    if ( ! current_user_can( 'install_plugins' ) ) {
        $disabled    = 'disabled';
        $current_key = '';
    }
    if ( ! empty( $all_ic_plugins ) && is_ic_plugin_active( $slug, $all_ic_plugins ) ) {
        $return .= '<p><a href="https://wordpress.org/support/plugin/' . $url . '" class="button-primary">Support</a> <a href="https://implecode.com/docs/" class="button-primary">Docs</a> <span class="comp installed">' . __( 'Active Extension', 'ecommerce-product-catalog' ) . '</span></p>';
    } else if ( ! empty( $not_active_ic_plugins ) && is_ic_plugin_active( $slug, $not_active_ic_plugins ) ) {
        $return .= '<p><a ' . $disabled . ' href="' . wp_nonce_url( 'plugins.php?ic_cat_activation=1&action=activate&amp;plugin=' . urlencode( ic_cat_get_plugin_file( $slug ) ), 'activate-plugin_' . ic_cat_get_plugin_file( $slug ) ) . '" class="button-primary">Activate Now</a><span class="comp info">' . __( 'Installed Extension', 'ecommerce-product-catalog' ) . '</span></p>';
    } else {
        if ( $comp_class == 'wrong' ) {
            $return .= '<p><a href="https://wordpress.org/plugins/' . $url . '" class="button-primary">See the Extension</a><span class="comp ' . $comp_class . '">' . $comp_txt . '</span></p>';
        } else {
            $return .= '<form class="license_form" action=""><input type="hidden" name="free_implecode_install" value="1"><input type="hidden" name="url" value="' . $url . '"><input type="hidden" name="slug" value="' . $slug . '"><input type="hidden" name="post_type" value="al_product"><input type="hidden" name="page" value="extensions.php"><input type="hidden" name="tab" value="product-extensions">';
            $return .= wp_nonce_field( 'install-implecode-plugin_' . $slug, '_wpnonce', 0, 0 );
            $return .= '<p class="submit"><input type="submit" ' . $disabled . ' value="Install" class="button-primary"><span class="comp ' . $comp_class . '">' . $comp_txt . '</span></form></p>';
        }
    }
    $return .= '</div>';

    return $return;
}

function extension_affiliate_box( $name, $url, $desc, $comp ) {

    if ( $comp == 'adv' && get_integration_type() == 'simple' ) {
        $comp_txt   = __( 'Advanced Mode Required', 'ecommerce-product-catalog' );
        $comp_class = 'wrong';
    } else {
        $comp_txt   = __( 'Ready to Install', 'ecommerce-product-catalog' );
        $comp_class = 'good';
    }

    $return = '<div class="extension affiliate">
	<a class="extension-name" href="' . $url . '"><h3><span>' . $name . '</span></h3><span class="click-span">' . __( 'Click for more', 'ecommerce-product-catalog' ) . '</span></a>
	<p>' . $desc . '</p>';
    if ( ! empty( $url ) ) {
        $return .= '<p><a href="' . $url . '" class="button-primary">Get ' . $name . '</a></p>';
    }
    $return .= '</div>';

    return $return;
}

function is_ic_plugin_active( $slug, $all_ic_plugins ) {
    foreach ( $all_ic_plugins as $key => $val ) {
        if ( $val['slug'] === $slug ) {
            return true;
        }
    }

    return false;
}

function start_implecode_install() {
    if ( isset( $_GET['implecode_install'] ) && ! empty( $_GET['slug'] ) && ! empty( $_GET['license_key'] ) && current_user_can( 'install_plugins' ) ) {
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'install-implecode-plugin_' . $_GET['slug'] ) ) {
            return;
        }
        $api = implecode_installation_url();
        if ( is_wp_error( $api ) ) {
            $error_messages = $api->get_error_messages();
            $wp_errors      = implode( "\n", $error_messages );
            echo '<div id="message error" class="error product-adder-message messages-connect">
				<div class="squeezer">
					<h4><strong>' . __( 'There is a problem with the connection. Please contact your server administrator with the following error:', 'ecommerce-product-catalog' ) . '</strong></h4>
						<p>' . $wp_errors . '</p>
				</div>
			</div>';
        } else if ( $api != 'error' ) {
            add_filter( 'install_plugin_complete_actions', 'implecode_install_actions', 10, 3 );
            echo '<div class="extension_installer">';
            include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
            $upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( array( 'api' => $api ) ) );
            $upgrader->install( $api->download_url );
            echo '</div>';
        } else if ( ! is_license_key_prevalidated( $_GET['license_key'] ) ) {
            echo '<div id="message error" class="error product-adder-message messages-connect">
				<div class="squeezer">
					<h4><strong>' . sprintf( __( 'This is not a valid license key! Get it <a href="%s">here</a>.', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/plugins/' . esc_url( $_GET['url'] ) . '/#cam=extensions&key=' . esc_url( $_GET['url'] ) ) . '</strong></h4>
				</div>
			</div>';
        } else {
            echo '<div id="message error" class="error product-adder-message messages-connect">
				<div class="squeezer">
					<h4><strong>' . sprintf( __( 'The supplied license key is not valid for this extension! Upgrade it <a href="%s">here</a>.', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/plugins/' . esc_url( $_GET['url'] ) . '/#cam=extensions&key=' . esc_url( $_GET['url'] ) ) . '</strong></h4>
				</div>
			</div>';
        }
    } else if ( isset( $_GET['implecode_install'] ) && ! empty( $_GET['slug'] ) && empty( $_GET['license_key'] ) && current_user_can( 'install_plugins' ) ) {
        echo '<div id="message error" class="error product-adder-message messages-connect">
				<div class="squeezer">
					<h4><strong>' . sprintf( __( 'You need to provide the license key to activate the extension. Get yours <a href="%s">here</a>.', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/plugins/' . esc_url( $_GET['url'] ) . '/#cam=extensions&key=' . esc_url( $_GET['url'] ) ) . '</strong></h4>
				</div>
			</div>';
    } else if ( ! current_user_can( 'install_plugins' ) ) {
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
    if ( isset( $_GET['free_implecode_install'] ) && ! empty( $_GET['slug'] ) && current_user_can( 'install_plugins' ) ) {
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'install-implecode-plugin_' . $_GET['slug'] ) ) {
            return;
        }
        $slug = esc_html( $_GET['slug'] );
        $url  = implecode_free_installation_url( $slug );
        if ( $url != 'error' ) {
            add_filter( 'install_plugin_complete_actions', 'implecode_install_actions', 10, 3 );
            echo '<div class="extension_installer">';
            include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
            $upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( array(
                    'url' => $url
            ) ) );
            $upgrader->install( $url );
            echo '</div>';
        } else {
            $url = 'https://downloads.wordpress.org/plugin/' . $slug . '.latest-stable.zip';
            echo '<div id="message error" class="error product-adder-message messages-connect">
				<div class="squeezer">
					<h4><strong>' . sprintf( __( 'Automatic installation is currently unavailable because your website cannot connect to WordPress.org. You can <a href="%s">download the extension manually</a> and install it via Plugins > Add New > Upload Plugin.', 'ecommerce-product-catalog' ), $url ) . '</strong></h4>
				</div>
			</div>';
        }
    }

    /* else if ( !current_user_can( 'install_plugins' ) ) {
      echo '<div id="message error" class="error product-adder-message messages-connect">
      <div class="squeezer">
      <h4><strong>' . __( 'You don\'t have permission to install and activate extensions.', 'ecommerce-product-catalog' ) . '</strong></h4>
      </div>
      </div>';
      }
     *
     */
}

function implecode_install_actions( $install_actions, $api, $plugin_file ) {
    if ( ! empty( $plugin_file ) ) {
        $disabled = '';
        if ( ! current_user_can( 'install_plugins' ) ) {
            $disabled = 'disabled';
        }
        $install_actions['activate_plugin'] = '<style>.extension_installer .button.button-primary:first-of-type{display: none;}</style><a ' . $disabled . ' href="' . wp_nonce_url( 'plugins.php?ic_cat_activation=1&action=activate&amp;plugin=' . urlencode( $plugin_file ), 'activate-plugin_' . $plugin_file ) . '" class="button-primary">Activate Now</a>';
    }
    $install_actions['plugins_page'] = '';

    return $install_actions;
}

function get_implecode_active_free_plugins() {
    $all_active = get_option( 'active_plugins' );
    if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $all_plugins = get_plugins();
    if ( empty( $all_plugins ) ) {
        return array();
    }
    $i          = 0;
    $ic_plugins = array();
    foreach ( $all_active as $active_name ) {
        if ( ! isset( $all_plugins[ $active_name ] ) ) {
            continue;
        }
        if ( $all_plugins[ $active_name ]['Author'] == 'impleCode' && $all_plugins[ $active_name ]['Name'] != 'eCommerce Product Catalog for WordPress' && $all_plugins[ $active_name ]['Name'] != 'Product Catalog Simple' ) {
            $ic_plugins[ $i ]['dir_file'] = $active_name;
            $active_name                  = explode( '/', $active_name );
            $ic_plugins[ $i ]['slug']     = $active_name[0];
        }
        $i ++;
    }

    return $ic_plugins;
}

function implecode_installation_url() {
    if ( is_license_key_prevalidated( $_GET['license_key'] ) ) {
        $options = array(
                'timeout' => 10, //seconds
        );
        $url     = 'https://implecode.com/?action=get_metadata&slug=' . $_GET['slug'] . '&license_key=' . $_GET['license_key'];
        $connect = wp_remote_get(
                $url, $options
        );
        if ( ! is_wp_error( $connect ) && ! empty( $connect['body'] ) ) {
            $pluginInfo = json_decode( $connect['body'] );
            if ( isset( $pluginInfo->download_url ) && $pluginInfo->download_url != '' ) {
                update_option( 'custom_license_code', $_GET['license_key'], false );
                $license_owner = url_to_array( $pluginInfo->license_owner );
                update_option( 'implecode_license_owner', array_to_url( $license_owner ), false );
                update_option( 'no_implecode_license_error', 0, false );
                $active_license = maybe_unserialize( get_option( 'license_active_plugins' ) );
                if ( empty( $active_license ) ) {
                    $active_license = array();
                }
                $active_license[] = $_GET['slug'];
                update_option( 'license_active_plugins', maybe_serialize( $active_license ), false );

                return $pluginInfo;
            }
        } else if ( is_wp_error( $connect ) ) {
            return $connect;
        }
    }

    return 'error';
}

/**
 * Returns installation URL from WordPress repository
 *
 * @param string $slug
 *
 * @return string
 */
function implecode_free_installation_url( $slug ) {
    $url          = 'https://downloads.wordpress.org/plugin/' . $slug . '.latest-stable.zip';
    $file_headers = @get_headers( $url );
    if ( empty( $file_headers[0] ) || $file_headers[0] == 'HTTP/1.1 404 Not Found' ) {
        return 'error';
    } else {
        return $url;
    }
}

function get_implecode_not_active_plugins() {
    $all_active = get_option( 'active_plugins' );
    if ( ! function_exists( 'get_plugins' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $all_plugins = get_plugins();
    $i           = 0;
    $ic_plugins  = array();
    foreach ( $all_active as $active_name ) {
        unset( $all_plugins[ $active_name ] );
    }
    foreach ( $all_plugins as $not_active_name => $not_active_plugin ) {
        if ( $not_active_plugin['Author'] == 'Norbert Dreszer' || $not_active_plugin['Author'] == 'impleCode' && $not_active_plugin['Name'] != 'eCommerce Product Catalog by impleCode' && $not_active_plugin['Name'] != 'Post Type X' ) {
            $ic_plugins[ $i ]['dir_file'] = $not_active_name;
            $not_active_name              = explode( '/', $not_active_name );
            $ic_plugins[ $i ]['slug']     = $not_active_name[0];
        }
        $i ++;
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

add_action( 'settings-menu', 'add_product_catalog_extensions_url', 55 );

function add_product_catalog_extensions_url() {
    if ( current_user_can( 'install_plugins' ) ) {
        ?>
        <a id="extensions" class="nav-tab"
           href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php' ) ?>"><?php _e( 'Add-ons & Integrations', 'ecommerce-product-catalog' ); ?></a>
        <?php
    }
    if ( current_user_can( 'manage_product_settings' ) ) {
        ?>
        <a id="help" class="nav-tab"
           href="<?php echo admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=help' ) ?>"><?php _e( 'Help', 'ecommerce-product-catalog' ); ?></a>

        <?php
    }
}

add_action( 'settings-menu', 'add_product_catalog_upgrade_url', 99 );

/**
 * Adds upgrade link in product settings menu
 *
 */
function add_product_catalog_upgrade_url() {
    if ( ! function_exists( 'start_implecode_updater' ) && false === get_site_transient( 'implecode_hide_extensions_box' ) ) {
        echo '<a target="_blank" title="' . __( 'Now you can get multiple extensions at once with the lowest price ever.', 'ecommerce-product-catalog' ) . '" class="upgrade-now" href="https://implecode.com/choose-a-plan/#cam=bundles&key=settings-top-menu">' . __( 'Upgrade Now!', 'ecommerce-product-catalog' ) . '</a>';
    }
}

add_action( 'extensions-menu', 'add_product_catalog_bundle_url', 99 );

/**
 * Adds upgrade link in porudct extensions menu
 *
 */
function add_product_catalog_bundle_url() {
    if ( ! function_exists( 'start_implecode_updater' ) ) {
        //echo '<a target="_blank" title="' . __( 'Now you can get multiple extensions at once with the lowest price ever.', 'ecommerce-product-catalog' ) . '" class="upgrade-now" href="https://implecode.com/choose-a-plan/#cam=bundles&key=extensions-top-menu">' . __( 'Now extensions bundles from $19.99!', 'ecommerce-product-catalog' ) . '</a>';
    }
}

add_action( 'ic_before_extensions_list', 'extensions_bundle_box', 5 );

/**
 * Shows bundle box before extensions list
 *
 */
function extensions_bundle_box() {
    if ( ! function_exists( 'start_implecode_updater' ) ) {
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
    if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $all_plugins = get_plugins();
    $i           = 0;
    $ic_plugins  = array();
    foreach ( $all_active as $active_name ) {
        if ( $all_plugins[ $active_name ]['Author'] == 'impleCode' && $all_plugins[ $active_name ]['Name'] != 'eCommerce Product Catalog by impleCode' ) {
            $ic_plugins[ $i ]['dir_file'] = $active_name;
            $active_name                  = explode( '/', $active_name );
            $ic_plugins[ $i ]['slug']     = $active_name[0];
        }
        $i ++;
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
    if ( ! function_exists( 'get_plugins' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    $all_plugins = get_plugins();
    $i           = 0;
    $ic_plugins  = array();
    foreach ( $all_active as $active_name ) {
        unset( $all_plugins[ $active_name ] );
    }
    foreach ( $all_plugins as $not_active_name => $not_active_plugin ) {
        if ( $not_active_plugin['Author'] == 'impleCode' && $not_active_plugin['Name'] != 'eCommerce Product Catalog by impleCode' ) {
            $ic_plugins[ $i ]['dir_file'] = $not_active_name;
            $not_active_name              = explode( '/', $not_active_name );
            $ic_plugins[ $i ]['slug']     = $not_active_name[0];
        }
        $i ++;
    }

    return $ic_plugins;
}

//add_action( 'ic_before_extensions_list', 'ic_epc_free_extensions' );
add_filter( 'ic_cat_extensions', 'ic_epc_free_extensions' );

/**
 * Shows Post Type X free extensions
 *
 */
function ic_epc_free_extensions( $existing_extensions = null ) {
    if ( false === ( $extensions = get_site_transient( 'implecode_epc_free_extensions_data' ) ) ) {
        $extensions = wp_remote_get( 'https://app.implecode.com/index.php?provide_extensions&free_epc=1' );
        if ( ! is_wp_error( $extensions ) && 200 == wp_remote_retrieve_response_code( $extensions ) ) {
            $extensions = json_decode( wp_remote_retrieve_body( $extensions ), true );
            if ( $extensions ) {
                set_site_transient( 'implecode_epc_free_extensions_data', $extensions, 60 * 60 * 24 * 7 );
            }
        } else {
            $extensions = implecode_free_extensions();
        }
    }
    //$all_ic_plugins = '';
    if ( function_exists( 'get_free_implecode_active_plugins' ) ) {
        //$all_ic_plugins = get_free_implecode_active_plugins();
    }
    //$not_active_ic_plugins = get_implecode_free_not_active_plugins();
    //echo '<div class="free-extensions">';
    foreach ( $extensions as $extension ) {
        $extension['type'] = isset( $extension['type'] ) ? $extension['type'] : 'premium';
        if ( $extension['slug'] !== 'catalog-booster-for-woocommerce' || class_exists( 'WooCommerce' ) ) {
            $new_extensions[] = $extension;
            //echo extension_box( $extension[ 'name' ], $extension[ 'url' ], $extension[ 'desc' ], $extension[ 'comp' ], $extension[ 'slug' ], $all_ic_plugins, $not_active_ic_plugins, $extension[ 'type' ] );
        }
    }

    //echo '</div>';
    return array_merge( $new_extensions, $existing_extensions );
}

function implecode_free_extensions() {
    $free_extensions = array(
            'reviews-plus'                    => array(
                    'url'  => 'reviews-plus',
                    'name' => 'Product Reviews',
                    'desc' => 'Add reviews support for your catalog items. Use it for all or selected products.',
                    'comp' => 'simple',
                    'slug' => 'reviews-plus',
                    'type' => 'free'
            ),
            'mailer-dragon'                   => array(
                    'url'  => 'mailer-dragon',
                    'name' => 'Catalog Newsletter',
                    'desc' => 'Let users subscribe for product-related newsletters.',
                    'comp' => 'simple',
                    'slug' => 'mailer-dragon',
                    'type' => 'free'
            ),
            'catalog-booster-for-woocommerce' => array(
                    'url'  => 'catalog-booster-for-woocommerce',
                    'name' => 'WooCommerce Catalog',
                    'desc' => 'Display WooCommerce products with ' . IC_CATALOG_PLUGIN_NAME . ' layout.',
                    'comp' => 'simple',
                    'slug' => 'catalog-booster-for-woocommerce',
                    'type' => 'free'
            )
    );

    return $free_extensions;
}

add_action( 'activated_plugin', 'ic_cat_extension_page_activation' );

function ic_cat_extension_page_activation() {
    if ( ! empty( $_GET['ic_cat_activation'] ) ) {
        wp_redirect( admin_url( 'edit.php?post_type=al_product&page=extensions.php&ic_cat_extension_activated=1' ) );
        exit;
    }
}

add_action( 'ic_cat_extensions_page_start', 'ic_cat_extension_activated_message' );

function ic_cat_extension_activated_message() {
    if ( ! empty( $_GET['ic_cat_extension_activated'] ) ) {
        ?>
        <div id="message" class="updated notice is-dismissible">
            <p><?php _e( 'Extension <strong>activated</strong>.', 'ecommerce-product-catalog' ) ?></p></div>
        <?php
    }
}

add_action( 'ic_cat_extensions_page_start', 'ic_show_extensions_page_renewal', 5 );

function ic_show_extensions_page_renewal() {
    ic_license_renewal_button( null, $button = 'page-title-action' );
}

if ( ! function_exists( 'ic_license_renewal_button' ) ) {

    function ic_license_renewal_button( $license_key = null, $button = 'button-primary', $label = null ) {
        if ( ! function_exists( 'get_implecode_active_plugins' ) || ! function_exists( 'array_to_url' ) ) {
            return;
        }
        if ( empty( $license_key ) ) {
            $license_key = get_option( 'custom_license_code', '' );
        }
        $license_products = get_implecode_active_plugins( true );
        if ( empty( $label ) ) {
            if ( ! defined( 'IC_FRAMEWORK_TEXTDOMAIN' ) ) {
                $framework_textdomain = '';
            } else {
                $framework_textdomain = IC_FRAMEWORK_TEXTDOMAIN;
            }
            $label = __( 'Renew License', $framework_textdomain );
        }
        echo '<a href="https://implecode.com/wordpress/plugins/?license_renewal=' . $license_key . '&license_products=' . array_to_url( $license_products ) . '#cam=dashboard&key=renewal" class="' . $button . ' ic-renewal-button" style="margin-top: 10px;">' . $label . '</a>';
    }

}

add_action( 'ic_catalog_admin_priority_notices', 'ic_license_renewal_notice' );

function ic_license_renewal_notice() {
    if ( ! function_exists( 'get_implecode_active_plugins' ) ) {
        return;
    }
    if ( ic_is_license_valid() ) {
        if ( ic_license_will_expire_soon() ) {
            $message = __( 'Your license will expire soon. Use the button below to prevent it, so the updates work without any interruptions.', 'ecommerce-product-catalog' );
        } else if ( ic_license_will_expire_soon( false ) ) {
            ic_license_reverify_schedule();
        }
    } else {
        $message = __( 'Your license is expired. The latest security and feature updates cannot be applied without an active license.', 'ecommerce-product-catalog' );
        $message .= '<br>' . __( 'Use the button below to fix it.', 'ecommerce-product-catalog' );
    }
    if ( empty( $message ) ) {
        return;
    }
    ?>
    <div class="error notice-updated is-dismissible ic-notice" data-ic_dismissible="notice-ic-catalog-renewal">
        <div class="squeezer">
            <?php
            echo '<p>' . $message . '</p>';
            echo '<p>';
            ic_license_renewal_button();
            echo '</p>';
            do_action( 'ic_license_expiration_message' );
            ?>
        </div>
    </div>
    <?php
    remove_action( 'admin_notices', 'license_key_expired' );
}

add_action( 'ic_license_expiration_message', 'ic_license_reverify_schedule' );
add_action( 'ic_license_reverify_schedule', 'ic_license_reverify_schedule' );

function ic_license_reverify_schedule() {
    if ( ! function_exists( 'check_all_implecode_license' ) ) {
        wp_unschedule_hook( 'ic_license_reverify_schedule' );

        return;
    }
    if ( current_filter() !== 'ic_license_reverify_schedule' ) {
        if ( ! wp_next_scheduled( 'ic_license_reverify_schedule' ) ) {
            wp_schedule_event( time() + MINUTE_IN_SECONDS, 'twicedaily', 'ic_license_reverify_schedule' );
        }

        return;
    }
    check_all_implecode_license();
    $unschedule = false;
    $reschedule = false;
    if ( ! function_exists( 'get_implecode_active_plugins' ) || ( ic_is_license_valid() && ! ic_license_will_expire_soon( false ) ) ) {
        $unschedule = true;
        if ( ic_license_is_subscription() ) {
            $reschedule = 'weekly';
        }
    } else if ( function_exists( 'get_implecode_active_plugins' ) && ! ic_is_license_valid() ) {
        $unschedule = true;
        $reschedule = 'weekly';
    } else if ( ic_license_will_expire_soon( false ) ) {
        $unschedule = true;
        $reschedule = 'twicedaily';
    }
    if ( $unschedule ) {
        wp_unschedule_hook( 'ic_license_reverify_schedule' );
    }
    if ( $reschedule ) {
        $schedules = wp_get_schedules();
        if ( ! empty( $schedules[ $reschedule ]['interval'] ) ) {
            if ( $reschedule === 'weekly' ) {
                $time = time() + HOUR_IN_SECONDS + DAY_IN_SECONDS;
            } else {
                $time = time() + $schedules[ $reschedule ]['interval'];
            }
            wp_schedule_event( $time, $reschedule, 'ic_license_reverify_schedule' );
        }
    }
}

function ic_license_valid_until() {
    $license_owner_param = get_option( 'implecode_license_owner' );
    $license_owner       = url_to_array( $license_owner_param );
    if ( ! empty( $license_owner['valid_until'] ) ) {
        return $license_owner['valid_until'];
    }

    return false;
}

function ic_is_license_valid() {
    $valid_until = ic_license_valid_until();
    if ( empty( $valid_until ) ) {
        return false;
    }
    $valid_until_time = strtotime( $valid_until );
    $current_time     = date( 'U' );
    if ( $valid_until_time + DAY_IN_SECONDS >= $current_time ) {
        return $valid_until_time;
    } else {
        return false;
    }
}

function ic_license_will_expire_soon( $check_is_sub = true ) {
    if ( $check_is_sub && ic_license_is_subscription() ) {
        return false;
    }
    if ( $valid_until_time = ic_is_license_valid() ) {
        $current_time = date( 'U' );
        $in_to_weeks  = $valid_until_time - ( WEEK_IN_SECONDS * 2 );
        if ( $current_time > $in_to_weeks ) {
            return true;
        }
    }

    return false;
}

function ic_license_is_subscription() {
    $license_owner_param = get_option( 'implecode_license_owner' );
    $license_owner       = url_to_array( $license_owner_param );
    if ( ! empty( $license_owner['subscription'] ) ) {
        return true;
    }

    return false;
}

add_action( 'ic_catalog_admin_priority_notices', 'ic_epc_newsletter_notice' );

/**
 * Displays a dismissible WordPress admin notice for a newsletter subscription with promotional content.
 *
 * @param ic_catalog_notices $ic_notices
 *
 * @return void
 */
function ic_epc_newsletter_notice( $ic_notices = null ) {
    if ( ( ! empty( $ic_notices ) && $ic_notices->get_notice_status( 'notice-ic-catalog-newsletter', 'temp' ) ) || function_exists( 'start_implecode_updater' ) ) {

        return;
    }
    ?>
    <div id="notice-ic-catalog-newsletter"
         class="updated notice notice-updated is-dismissible ic-notice notice-ic-catalog-newsletter"
         data-ic_dismissible="notice-ic-catalog-newsletter" data-ic_dismissible_type="temp" style="display: none;">
        <div class="squeezer"></div>
        <script>
            async function ic_epc_newsletter_notice() {
                try {
                    const response = await fetch('https://implecode.com/wp-json/ic-mailer/v1/forms?custom=epc_newsletter');
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    const data = await response.json();  // Change to .json()
                    if (data.length > 0) {
                        //jQuery('#ic-mailer-forms-container').append(data);
                        jQuery('#notice-ic-catalog-newsletter .squeezer').append(data);
                        jQuery('.notice-ic-catalog-newsletter').show();
                    }
                } catch (error) {
                    console.error('Error loading forms:', error);
                    document.getElementById('ic-mailer-forms-container').innerHTML =
                        '<div class="ic-notice-error"><?php _e( 'Error loading form. Please try again later.', 'ecommerce-product-catalog' )?></div>';
                }
            }

            ic_epc_newsletter_notice();
        </script>
    </div>

    <?php
}

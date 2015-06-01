<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WP Product template manager
 *
 * Here all plugin templates are defined.
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/
 * @author        Norbert Dreszer
 */
require_once(AL_BASE_PATH . '/templates/templates-functions.php');

add_action( 'after_setup_theme', 'initialize_product_adder_template', 11 );

/**
 * Includes correct template file
 *
 */
function initialize_product_adder_template() {

	$theme			 = get_option( 'template' );
	$woothemes		 = array( "canvas", "woo", "al" );
	$nosidebar		 = array( "twentyeleven" );
	$twentyten		 = array( "twentyten" );
	$twentythirteen	 = array( "twentythirteen" );
	$twentyfourteen	 = array( "twentyfourteen" );
	$twentyfifteen	 = array( "twentyfifteen" );
	$third_party	 = array( "storefront" );

	if ( is_integraton_file_active() ) {
		add_filter( 'template_include', 'al_product_adder_template' );
	} else if ( in_array( $theme, $woothemes ) ) {
		add_filter( 'template_include', 'al_product_adder_woo_template' );
	} else if ( in_array( $theme, $third_party ) ) {
		add_filter( 'template_include', 'al_product_adder_third_party_templates' );
	} else if ( in_array( $theme, $nosidebar ) ) {
		add_filter( 'template_include', 'al_product_adder_nosidebar_template' );
	} else if ( in_array( $theme, $twentyten ) ) {
		add_filter( 'template_include', 'al_product_adder_twentyten_template' );
	} else if ( in_array( $theme, $twentythirteen ) ) {
		add_filter( 'template_include', 'al_product_adder_twentythirteen_template' );
	} else if ( in_array( $theme, $twentyfourteen ) ) {
		add_filter( 'template_include', 'al_product_adder_twentyfourteen_template' );
	} else if ( in_array( $theme, $twentyfifteen ) ) {
		add_filter( 'template_include', 'al_product_adder_twentyfifteen_template' );
	} else if ( get_integration_type() == 'simple' && file_exists( get_page_php_path() ) ) {
		add_filter( 'template_include', 'al_product_adder_page_template' );
	} else {
		add_filter( 'template_include', 'al_product_adder_custom_template' );
	}
}

function al_product_adder_template( $template ) {
	if ( is_ic_catalog_page() ) {
		return get_product_adder_path();
	}
	return $template;
}

function al_product_adder_custom_template( $template ) {
	if ( is_ic_catalog_page() ) {
		return dirname( __FILE__ ) . '/templates/product-adder.php';
	}
	return $template;
}

// templates from woothemes
function al_product_adder_woo_template( $template ) {
	if ( is_ic_catalog_page() ) {
		return dirname( __FILE__ ) . '/templates/product-woo-adder.php';
	}
	return $template;
}

function al_product_adder_third_party_templates( $template ) {
	if ( is_ic_catalog_page() ) {
		$theme = get_option( 'template' );
		return dirname( __FILE__ ) . '/templates/third-party/' . $theme . '.php';
	}
	return $template;
}

// no sidebar on content page
function al_product_adder_nosidebar_template( $template ) {
	if ( is_ic_catalog_page() ) {
		return dirname( __FILE__ ) . '/templates/product-nosidebar-adder.php';
	}
	return $template;
}

// twentyten - primary replaced by container id
function al_product_adder_twentyten_template( $template ) {
	if ( is_ic_catalog_page() ) {
		return dirname( __FILE__ ) . '/templates/product-twentyten-adder.php';
	}
	return $template;
}

function al_product_adder_twentythirteen_template( $template ) {
	if ( is_ic_catalog_page() ) {
		return dirname( __FILE__ ) . '/templates/product-twentythirteen-adder.php';
	}
	return $template;
}

function al_product_adder_twentyfourteen_template( $template ) {
	if ( is_ic_catalog_page() ) {
		return dirname( __FILE__ ) . '/templates/product-twentyfourteen-adder.php';
	}
	return $template;
}

function al_product_adder_twentyfifteen_template( $template ) {
	if ( is_ic_catalog_page() ) {
		return dirname( __FILE__ ) . '/templates/product-twentyfifteen-adder.php';
	}
	return $template;
}

function al_product_adder_page_template( $template ) {
	if ( is_ic_catalog_page() ) {
		if ( is_archive() || is_search() || is_tax() ) {
			$product_archive = get_product_listing_id();
			wp_redirect( get_permalink( $product_archive ) );
		} else {
			return get_page_php_path();
		}
	}
	return $template;
}

function product_page_content( $content ) {
	if ( is_ic_catalog_page() && get_integration_type() == 'simple' ) {
		if ( is_single() ) {
			ob_start();
			content_product_adder();
			$content = ob_get_contents();
			ob_end_clean();
		}
	}
	return $content;
}

add_filter( "the_content", "product_page_content" );

function theme_integration_shortcode( $atts ) {
	$current_mode = get_real_integration_mode();
	if ( current_user_can( "delete_private_products" ) && !is_advanced_mode_forced() ) {
		$args		 = shortcode_atts( array(
			'class' => 'relative-box',
		), $atts );
		$class		 = esc_attr( $args[ 'class' ] );
		$box_content = '<h4>' . __( 'Advanced Mode Test', 'al-ecommerce-product-catalog' ) . '</h4>';
		$box_content .= '<script>jQuery(window).scroll( function() { if (isScrolledIntoView(".relative-box")) {jQuery(".fixed-box").hide("slow");}
else {jQuery(".fixed-box").show("slow");}});
function isScrolledIntoView(elem)
{
    var docViewTop = jQuery(window).scrollTop();
    var docViewBottom = docViewTop + jQuery(window).height();
    var elemTop = jQuery(elem).offset().top;
    var elemBottom = elemTop + jQuery(elem).height();
    return ((docViewTop < elemTop) && (docViewBottom > elemBottom));
//    return ((elemBottom <= docViewBottom) && (elemTop >= docViewTop));
}</script>';
		if ( !isset( $_GET[ 'test_advanced' ] ) ) {
			$box_content .= '<p>' . __( 'eCommerce Product Catalog is currently running in Simple Mode.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'In Simple Mode the product listing, product search and category pages are disabled (please read this Sample Product Page to fully understand the difference).', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'Please use the button below to check out how the product page looks in Automatic Advanced Mode.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p class="wp-core-ui"><a href="' . esc_url( add_query_arg( 'test_advanced', '1' ) ) . '" class="button-primary">' . __( 'Start Advanced Mode Test', 'al-ecommerce-product-catalog' ) . '</a><a href="' . esc_url( add_query_arg( 'test_advanced', 'simple' ) ) . '" class="button-secondary">' . __( 'Use Simple Mode', 'al-ecommerce-product-catalog' ) . '</a></p>';
			if ( $current_mode == 'simple' ) {
				return '<div class="' . $class . '">' . implecode_info( $box_content, 0 ) . '</div>';
			}
		} else if ( isset( $_GET[ 'test_advanced' ] ) && $_GET[ 'test_advanced' ] == 1 ) {
			$box_content .= '<p>' . __( 'Advanced Mode is temporary enabled for this page now.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'Please use the buttons below to let the script know if the Automatic Advanced Integration is done right.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'Is everything looking fine, without design break and correct sidebar position?', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p class="wp-core-ui"><a href="' . esc_url( add_query_arg( 'test_advanced', 'ok' ) ) . '" class="button-primary">' . __( 'It\'s Fine', 'al-ecommerce-product-catalog' ) . '</a><a href="' . esc_url( add_query_arg( 'test_advanced', 'bad' ) ) . '" class="button-secondary">' . __( 'It\'s Broken', 'al-ecommerce-product-catalog' ) . '</a></p>';
			return '<div class="' . $class . '">' . implecode_info( $box_content, 0 ) . '</div>';
		} else if ( isset( $_GET[ 'test_advanced' ] ) && $_GET[ 'test_advanced' ] == 'bad' ) {
			$box_content .= '<p>' . __( 'It seems that Manual Theme Integration is needed in order to use Advanced Mode with your current theme.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<h4>' . __( 'You Have 3 choices', 'al-ecommerce-product-catalog' ) . ':</h4>';
			$box_content .= '<ol>';
			$box_content .= '<li>' . __( 'Get the Manual Theme Integration done.', 'al-ecommerce-product-catalog' ) . '</li>';
			$box_content .= '<li>' . __( 'Keep using Simple Mode which is still functional.', 'al-ecommerce-product-catalog' ) . '</li>';
			$box_content .= '<li>' . __( 'Switch the theme.', 'al-ecommerce-product-catalog' ) . '</li>';
			$box_content .= '</ol>';
			$box_content .= '<p>' . __( 'Please make your choice below or switch the theme.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p class="wp-core-ui"><a target="_blank" href="http://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=integration-advanced-fail" class="button-primary">' . __( 'Free Theme Integration Guide', 'al-ecommerce-product-catalog' ) . '</a><a href="' . esc_url( add_query_arg( 'test_advanced', 'simple' ) ) . '" class="button-secondary">' . __( 'Use Simple Mode', 'al-ecommerce-product-catalog' ) . '</a></p>';
			enable_simple_mode();
			return '<div class="' . $class . '">' . implecode_warning( $box_content, 0 ) . '</div>';
		} else if ( isset( $_GET[ 'test_advanced' ] ) && $_GET[ 'test_advanced' ] == 'ok' ) {
			$box_content .= '<p>' . __( 'Congratulations! eCommerce Product Catalog is working on Advanced Mode now. You can go to admin and add the products to the catalog.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'If you are a developer or would like to have full control on the product pages templates we still recommend to proceed with manual integration.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'You can switch between modes at any time in Product Settings.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p class="wp-core-ui"><a href="' . admin_url( 'edit.php?post_type=al_product' ) . '" class="button-primary">' . __( 'Go to Admin', 'al-ecommerce-product-catalog' ) . '</a><a target="_blank" href="http://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=advanced-mode&key=integration-advanced-success" class="button-secondary">' . __( 'Free Theme Integration Guide', 'al-ecommerce-product-catalog' ) . '</a></p>';
			enable_advanced_mode();
			return '<div class="' . $class . '">' . implecode_success( $box_content, 0 ) . '</div>';
		} else if ( isset( $_GET[ 'test_advanced' ] ) && $_GET[ 'test_advanced' ] == 'simple' ) {
			$box_content .= '<p>' . __( 'You are using simple mode now.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'You can switch between modes at any time in Product Settings.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'Use the buttons below to try the advanced integration again or go to admin and start adding your products.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p class="wp-core-ui"><a href="' . admin_url( 'edit.php?post_type=al_product' ) . '" class="button-primary">' . __( 'Go to Admin', 'al-ecommerce-product-catalog' ) . '</a><a href="' . esc_url( add_query_arg( 'test_advanced', '1' ) ) . '" class="button-secondary">' . __( 'Restart Advanced Mode Test', 'al-ecommerce-product-catalog' ) . '</a></p>';
			enable_simple_mode();
			return '<div class="' . $class . '">' . implecode_success( $box_content, 0 ) . '</div>';
		}
	}
}

add_shortcode( 'theme_integration', 'theme_integration_shortcode' );

function enable_advanced_mode( $hide_info = 0 ) {
	$archive_multiple_settings						 = get_multiple_settings();
	$archive_multiple_settings[ 'integration_type' ] = 'advanced';
	update_option( 'archive_multiple_settings', $archive_multiple_settings );
	$template										 = get_option( 'template' );
	if ( $hide_info == 1 ) {
		update_option( 'product_adder_theme_support_check', $template );
	}
}

function enable_simple_mode() {
	$archive_multiple_settings						 = get_multiple_settings();
	$archive_multiple_settings[ 'integration_type' ] = 'simple';
	update_option( 'archive_multiple_settings', $archive_multiple_settings );
	update_option( 'product_adder_theme_support_check', '' );
}

function get_real_integration_mode() {
	$archive_multiple_settings						 = get_option( 'archive_multiple_settings', unserialize( DEFAULT_ARCHIVE_MULTIPLE_SETTINGS ) );
	$archive_multiple_settings[ 'integration_type' ] = isset( $archive_multiple_settings[ 'integration_type' ] ) ? $archive_multiple_settings[ 'integration_type' ] : 'simple';
	return $archive_multiple_settings[ 'integration_type' ];
}

function is_integration_mode_selected() {
	$return											 = false;
	$archive_multiple_settings						 = get_option( 'archive_multiple_settings', unserialize( DEFAULT_ARCHIVE_MULTIPLE_SETTINGS ) );
	$archive_multiple_settings[ 'integration_type' ] = isset( $archive_multiple_settings[ 'integration_type' ] ) ? $archive_multiple_settings[ 'integration_type' ] : '';
	if ( $archive_multiple_settings[ 'integration_type' ] != '' || is_integraton_file_active() ) {
		$return = true;
	}
	return $return;
}

function is_integraton_file_active() {
	if ( file_exists( get_product_adder_path() ) ) {
		return true;
	} else {
		return false;
	}
}

function erase_integration_type_select() {
	$archive_multiple_settings = get_option( 'archive_multiple_settings', unserialize( DEFAULT_ARCHIVE_MULTIPLE_SETTINGS ) );
	unset( $archive_multiple_settings[ 'integration_type' ] );
	update_option( 'archive_multiple_settings', $archive_multiple_settings );
	permalink_options_update();
}

add_action( 'switch_theme', 'erase_integration_type_select' );

function create_sample_product_with_redirect() {
	if ( isset( $_GET[ 'create_sample_product_page' ] ) ) {
		$sample_product_id = create_sample_product();
		wp_redirect( get_permalink( $sample_product_id ) );
		exit();
	}
}

add_action( 'admin_init', 'create_sample_product_with_redirect' );

function implecode_supported_themes() {
	return array( 'twentythirteen', 'twentyeleven', 'twentytwelve', 'twentyten', 'twentyfourteen', 'twentyfifteen', 'pub/minileven', 'storefront' );
}

function is_theme_implecode_supported() {
	$template	 = get_option( 'template' );
	$return		 = false;
	if ( in_array( $template, implecode_supported_themes() ) || current_theme_supports( 'ecommerce-product-catalog' ) ) {
		$return = true;
	}
	return $return;
}

function is_advanced_mode_forced() {
//    $template = get_option('template');
	$return = false;
	if ( is_theme_implecode_supported() || is_integraton_file_active() ) {
		$return = true;
	}
	return $return;
}

function get_product_adder_path() {
	return get_stylesheet_directory() . '/product-adder.php';
}

function get_page_php_path() {
	if ( file_exists( get_stylesheet_directory() . '/page.php' ) ) {
		$path = get_stylesheet_directory() . '/page.php';
	} else {
		$path = get_theme_root() . '/' . get_template() . '/page.php';
	}
	return $path;
}

function is_home_archive( $query = null ) {
	if ( !is_object( $query ) && is_front_page() && is_product_listing_home_set() ) {
		return true;
	} else if ( is_object( $query ) && $query->get( 'page_id' ) == get_option( 'page_on_front' ) && is_product_listing_home_set() ) {
		return true;
	}
	return false;
}

function is_product_listing_home_set() {
	$frontpage			 = get_option( 'page_on_front' );
	$product_listing_id	 = get_product_listing_id();
	if ( !empty( $frontpage ) && !empty( $product_listing_id ) && $frontpage == $product_listing_id ) {
		return true;
	}
	return false;
}

function home_product_listing_redirect( $template ) {
	if ( is_product_listing_home_set() && !is_front_page() && is_post_type_archive( 'al_product' ) && !is_search() ) {
		wp_redirect( get_site_url(), 301 );
		exit;
	}
	return $template;
}

add_filter( 'template_include', 'home_product_listing_redirect', 5 );
?>
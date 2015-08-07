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
add_shortcode( 'theme_integration', 'theme_integration_shortcode' );

function theme_integration_shortcode( $atts ) {

}

add_action( 'wp_footer', 'theme_integration_wizard' );

/**
 * Shows theme intagration wizard
 *
 * @param type $atts
 */
function theme_integration_wizard( $atts ) {
	$current_mode = get_real_integration_mode();
	if ( is_ic_integration_wizard_page() ) {
		$args		 = shortcode_atts( array(
			'class' => 'fixed-box',
		), $atts );
		$class		 = esc_attr( $args[ 'class' ] );
		$box_content = '<h4>' . __( 'Advanced Mode Test', 'al-ecommerce-product-catalog' ) . '</h4>';
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
		if ( !isset( $_GET[ 'test_advanced' ] ) ) {
			$box_content .= '<p>' . __( 'eCommerce Product Catalog is currently running in Simple Mode.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'In Simple Mode the product listing, product search and category pages are disabled (please read this Sample Product Page to fully understand the difference).', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'Please use the button below to check out how the product page looks in Automatic Advanced Mode.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p class="wp-core-ui"><a href="' . esc_url( add_query_arg( 'test_advanced', '1' ) ) . '" class="button-primary">' . __( 'Start Advanced Mode Test', 'al-ecommerce-product-catalog' ) . '</a><a href="' . esc_url( add_query_arg( 'test_advanced', 'simple' ) ) . '" class="button-secondary">' . __( 'Use Simple Mode', 'al-ecommerce-product-catalog' ) . '</a></p>';
			if ( $current_mode == 'simple' ) {
				echo '<div id="integration_wizard" class="' . $class . '">' . implecode_info( $box_content, 0 ) . '</div>';
			}
		} else if ( isset( $_GET[ 'test_advanced' ] ) && $_GET[ 'test_advanced' ] == 1 ) {
			$box_content .= '<style>#integration_wizard.fixed-box {opacity: 0.8;}#integration_wizard.fixed-box:hover {opacity: 1;}</style>';
			$box_content .= '<p>' . __( 'Advanced Mode is temporary enabled for this page now.', 'al-ecommerce-product-catalog' ) . '</p>';
			//$box_content .= '<p>' . __( 'Please use the buttons below to let the script know if the Automatic Advanced Integration is done right.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p style="margin-bottom: 0">' . __( 'Make some adjustments if necessary (you can change it at any time later)', 'al-ecommerce-product-catalog' ) . ':</p>';
			$box_content .= '<table class="styling-adjustments">';
			$integration_settings = get_integration_settings();
			$box_content .= implecode_settings_number( __( 'Width', 'al-ecommerce-product-catalog' ), 'container_width', $integration_settings[ 'container_width' ], '%', 0, null, null, 0 );
			$box_content .= implecode_settings_text_color( __( 'Background', 'al-ecommerce-product-catalog' ), 'container_bg', $integration_settings[ 'container_bg' ], null, 0, null, '{change: function(event, ui){ var hexcolor = jQuery( this ).wpColorPicker( "color" ); jQuery("#container").css("background", hexcolor); jQuery("#container").css("overflow", "hidden"); jQuery("#container").css("width", jQuery("input[name=\"container_width\"]").val()+"%");}}' );
			$box_content .= implecode_settings_number( __( 'Padding', 'al-ecommerce-product-catalog' ), 'container_padding', $integration_settings[ 'container_padding' ], 'px', 0, null, null, 0 );
			if ( !defined( 'AL_SIDEBAR_PLUGIN_BASE_PATH' ) ) {
				$box_content .= implecode_settings_radio( __( 'Default Sidebar', 'al-ecommerce-product-catalog' ), 'default_sidebar', $integration_settings[ 'default_sidebar' ], array( 'none' => __( 'Disabled', 'al-ecommerce-product-catalog' ), 'left' => __( 'Left', 'al-ecommerce-product-catalog' ), 'right' => __( 'Right', 'al-ecommerce-product-catalog' ) ), 0 );
				if ( $integration_settings[ 'default_sidebar' ] == 'none' ) {
					$box_content .= '<style>#catalog_sidebar {display: none;}</style>';
				}
			}
			$box_content .= implecode_settings_checkbox( __( 'Disable breadcrumbs', 'al-ecommerce-product-catalog' ), 'disable_breadcrumbs', $integration_settings[ 'disable_breadcrumbs' ], 0 );
			$box_content .= implecode_settings_checkbox( __( 'Disable Name', 'al-ecommerce-product-catalog' ), 'disable_name', $integration_settings[ 'disable_name' ], 0 );
			$box_content .= implecode_settings_checkbox( __( 'Disable Image', 'al-ecommerce-product-catalog' ), 'disable_image', $integration_settings[ 'disable_image' ], 0 );
			$box_content .= implecode_settings_checkbox( __( 'Disable Price', 'al-ecommerce-product-catalog' ), 'disable_price', $integration_settings[ 'disable_price' ], 0 );
			$box_content .= implecode_settings_checkbox( __( 'Disable Shipping', 'al-ecommerce-product-catalog' ), 'disable_shipping', $integration_settings[ 'disable_shipping' ], 0 );
			$box_content .= implecode_settings_checkbox( __( 'Disable Attributes', 'al-ecommerce-product-catalog' ), 'disable_attributes', $integration_settings[ 'disable_attributes' ], 0 );
			$box_content .= '</table>';
			$box_content .= '<style>#integration_wizard .al-box table tbody, #integration_wizard .al-box table tr, #integration_wizard .al-box table td {border: 0; background: transparent;} #integration_wizard .al-box table.styling-adjustments td {vertical-align: middle;font-size: 14px; color: rgb(136, 136, 136); text-align: left;} #integration_wizard .wp-picker-container {padding-top: 5px;}html #integration_wizard.fixed-box .al-box table input.wp-picker-clear {background: #ededed; transition: none; padding: 1px 6px; border: 1px solid #000; color: #000; margin: 0; margin-left: 6px;}#integration_wizard .wp-picker-holder{position: absolute;}</style>';
			$box_content .= '<script>jQuery("input[name=\"container_width\"]").change(function() { jQuery("#container").css("width", jQuery(this).val()+"%");jQuery("#container").css("margin", "0 auto");});';
			$box_content .= 'jQuery("input[name=\"container_padding\"]").change(function() { jQuery("#container #content").css("padding", jQuery(this).val()+"px");jQuery("#container").css("box-sizing", "border-box");jQuery("#container #catalog_sidebar").css("padding", jQuery(this).val()+"px");});';
			$box_content .= 'jQuery("input[name=\"disable_breadcrumbs\"]").change(function() { if (jQuery(this).is(":checked")) { jQuery("p#breadcrumbs").hide();} else {jQuery("p#breadcrumbs").show();}});';
			$box_content .= 'jQuery("input[name=\"disable_name\"]").change(function() { if (jQuery(this).is(":checked")) { jQuery("h1.product-name").hide();} else {jQuery("h1.product-name").show();}});';
			$box_content .= 'jQuery("input[name=\"disable_image\"]").change(function() { if (jQuery(this).is(":checked")) { jQuery("div.product-image").hide();jQuery("#product_details").addClass("no-image");} else {jQuery("div.product-image").show();jQuery("#product_details").removeClass("no-image");}});';
			$box_content .= 'jQuery("input[name=\"disable_price\"]").change(function() { if (jQuery(this).is(":checked")) { jQuery("table.price-table").hide();} else {jQuery("table.price-table").show();}});';
			$box_content .= 'jQuery("input[name=\"disable_shipping\"]").change(function() { if (jQuery(this).is(":checked")) { jQuery("table.shipping-table").hide();} else {jQuery("table.shipping-table").show();}});';
			$box_content .= 'jQuery("input[name=\"disable_attributes\"]").change(function() { if (jQuery(this).is(":checked")) { jQuery("#product_features").hide();} else {jQuery("#product_features").show();}});';
			$box_content .= 'jQuery("input[name=\"default_sidebar\"]").change(function() { if (jQuery(this).is(":checked")) { sidebar = jQuery(this).val();if (sidebar == "left") {jQuery("#catalog_sidebar").show();jQuery("#catalog_sidebar").css("float","left");jQuery(".product-catalog #content").css("width","70%");jQuery(".product-catalog #content").css("float","right");} else if (sidebar == "right") {jQuery("#catalog_sidebar").show();jQuery("#catalog_sidebar").css("float","right");jQuery(".product-catalog #content").css("width","70%");jQuery(".product-catalog #content").css("float","left");} else {jQuery("#catalog_sidebar").hide();jQuery(".product-catalog #content").css("width","100%");jQuery(".product-catalog #content").css("float","none");}}});';
			$box_content .= '</script>';
			//$box_content .= '<script>jQuery("input[name=\"container_bg\"]").change(function() { jQuery("#container").css("background", jQuery(this).val());});</script>';
			$box_content .= '<p>' . __( 'Is everything looking fine now?', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<script>jQuery(document).ready(function() {
    jQuery("a.integration-ok").click(function(e) {
	clicked = jQuery(this).attr("href");
	e.preventDefault();
	var breadcrumbs = 0;
	if (jQuery("input[name=\"disable_breadcrumbs\"]").is(":checked")) {
		breadcrumbs = 1;
	}
	var name = 0;
	if (jQuery("input[name=\"disable_name\"]").is(":checked")) {
		name = 1;
	}
	var image = 0;
	if (jQuery("input[name=\"disable_image\"]").is(":checked")) {
		image = 1;
	}
	var price = 0;
	if (jQuery("input[name=\"disable_price\"]").is(":checked")) {
		price = 1;
	}
	var shipping = 0;
	if (jQuery("input[name=\"disable_shipping\"]").is(":checked")) {
		shipping = 1;
	}
	var attributes = 0;
	if (jQuery("input[name=\"disable_attributes\"]").is(":checked")) {
		attributes = 1;
	}
	default_sidebar = jQuery("input[name=\"default_sidebar\"]:checked").val();
		var data = {
			"action": "save_wizard",
			"container_width": jQuery("input[name=\"container_width\"]").val(),
			"container_padding": jQuery("input[name=\"container_padding\"]").val(),
			"container_bg": jQuery("input[name=\"container_bg\"]").val(),
			"disable_breadcrumbs": breadcrumbs,
			"disable_name": name,
			"disable_image": image,
			"disable_price": price,
			"disable_shipping": shipping,
			"disable_attributes": attributes,
			"default_sidebar": default_sidebar
		};

		jQuery.post("' . admin_url( 'admin-ajax.php' ) . '", data, function(response) {
			window.location.href = clicked;
		});
	}); });
</script>';
			$box_content .= '<p class="wp-core-ui"><a href="' . esc_url( add_query_arg( 'test_advanced', 'ok' ) ) . '" class="button-primary integration-ok">' . __( 'It\'s Fine', 'al-ecommerce-product-catalog' ) . '</a><a href="' . esc_url( add_query_arg( 'test_advanced', 'bad' ) ) . '" class="button-secondary">' . __( 'It\'s Broken', 'al-ecommerce-product-catalog' ) . '</a></p>';

			echo '<div id="integration_wizard" class="' . $class . '">' . implecode_info( $box_content, 0 ) . '</div>';
		} else if ( isset( $_GET[ 'test_advanced' ] ) && $_GET[ 'test_advanced' ] == 'bad' ) {
			$box_content .= '<p>' . __( 'It seems that Manual Theme Integration is needed in order to use Advanced Mode with your current theme.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<h4>' . __( 'You Have 3 choices', 'al-ecommerce-product-catalog' ) . ':</h4>';
			$box_content .= '<ol>';
			$box_content .= '<li>' . __( 'Get the Manual Theme Integration done.', 'al-ecommerce-product-catalog' ) . '</li>';
			$box_content .= '<li>' . __( 'Keep using Simple Mode which is still functional.', 'al-ecommerce-product-catalog' ) . '</li>';
			$box_content .= '<li>' . __( 'Switch the theme.', 'al-ecommerce-product-catalog' ) . '</li>';
			$box_content .= '</ol>';
			$box_content .= '<p>' . __( 'Please make your choice below or switch the theme.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p class="wp-core-ui"><a target="_blank" href="https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=integration-advanced-fail" class="button-primary">' . __( 'Free Theme Integration Guide', 'al-ecommerce-product-catalog' ) . '</a><a href="' . esc_url( add_query_arg( 'test_advanced', 'simple' ) ) . '" class="button-secondary">' . __( 'Use Simple Mode', 'al-ecommerce-product-catalog' ) . '</a></p>';
			enable_simple_mode();
			echo '<div id="integration_wizard" class="' . $class . '">' . implecode_warning( $box_content, 0 ) . '</div>';
		} else if ( isset( $_GET[ 'test_advanced' ] ) && $_GET[ 'test_advanced' ] == 'ok' ) {
			$box_content .= '<p>' . __( 'Congratulations! eCommerce Product Catalog is working on Advanced Mode now. You can go to admin and add the products to the catalog.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'If you are a developer or would like to have full control on the product pages templates we still recommend to proceed with manual integration.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'You can switch between modes at any time in Product Settings.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p class="wp-core-ui"><a href="' . admin_url( 'edit.php?post_type=al_product' ) . '" class="button-primary">' . __( 'Go to Admin', 'al-ecommerce-product-catalog' ) . '</a><a target="_blank" href="https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=advanced-mode&key=integration-advanced-success" class="button-secondary">' . __( 'Free Theme Integration Guide', 'al-ecommerce-product-catalog' ) . '</a></p>';
			enable_advanced_mode();
			echo '<div id="integration_wizard" class="' . $class . '">' . implecode_success( $box_content, 0 ) . '</div>';
		} else if ( isset( $_GET[ 'test_advanced' ] ) && $_GET[ 'test_advanced' ] == 'simple' ) {
			$box_content .= '<p>' . __( 'You are using simple mode now.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'You can switch between modes at any time in Product Settings.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p>' . __( 'Use the buttons below to try the advanced integration again or go to admin and start adding your products.', 'al-ecommerce-product-catalog' ) . '</p>';
			$box_content .= '<p class="wp-core-ui"><a href="' . admin_url( 'edit.php?post_type=al_product' ) . '" class="button-primary">' . __( 'Go to Admin', 'al-ecommerce-product-catalog' ) . '</a><a href="' . esc_url( add_query_arg( 'test_advanced', '1' ) ) . '" class="button-secondary">' . __( 'Restart Advanced Mode Test', 'al-ecommerce-product-catalog' ) . '</a></p>';
			enable_simple_mode();
			echo '<div id="integration_wizard" class="' . $class . '">' . implecode_success( $box_content, 0 ) . '</div>';
		}
	}
}

/**
 * Returns wizard advanced mode settings
 * @return array
 */
function get_integration_settings() {
	$archive_multiple_settings			 = get_multiple_settings();
	$settings[ 'container_width' ]		 = isset( $archive_multiple_settings[ 'container_width' ] ) ? $archive_multiple_settings[ 'container_width' ] : 100;
	$settings[ 'container_bg' ]			 = isset( $archive_multiple_settings[ 'container_bg' ] ) ? $archive_multiple_settings[ 'container_bg' ] : '';
	$settings[ 'container_padding' ]	 = isset( $archive_multiple_settings[ 'container_padding' ] ) ? $archive_multiple_settings[ 'container_padding' ] : 0;
	$settings[ 'disable_breadcrumbs' ]	 = isset( $archive_multiple_settings[ 'enable_product_breadcrumbs' ] ) && $archive_multiple_settings[ 'enable_product_breadcrumbs' ] == 1 ? 0 : 1;
	$settings[ 'disable_name' ]			 = isset( $archive_multiple_settings[ 'disable_name' ] ) ? $archive_multiple_settings[ 'disable_name' ] : 0;
	$settings[ 'disable_image' ]		 = is_ic_product_gallery_enabled() ? 0 : 1;
	$settings[ 'disable_price' ]		 = is_ic_price_enabled() ? 0 : 1;
	$settings[ 'disable_shipping' ]		 = is_ic_shipping_enabled() ? 0 : 1;
	$settings[ 'disable_attributes' ]	 = is_ic_attributes_enabled() ? 0 : 1;
	$settings[ 'default_sidebar' ]		 = isset( $archive_multiple_settings[ 'default_sidebar' ] ) ? $archive_multiple_settings[ 'default_sidebar' ] : 'none';
	return $settings;
}

add_action( 'wp_ajax_save_wizard', 'save_wizard_advanced_mode_settings' );

/**
 * Handles wizard avanced mode settings save
 */
function save_wizard_advanced_mode_settings() {
	if ( current_user_can( 'manage_product_settings' ) ) {
		$archive_multiple_settings							 = get_multiple_settings();
		$product_currency_settings							 = get_currency_settings();
		$product_page_settings								 = get_product_page_settings();
		$archive_multiple_settings[ 'container_width' ]		 = intval( $_POST[ 'container_width' ] );
		$archive_multiple_settings[ 'container_bg' ]		 = isset( $_POST[ 'container_bg' ] ) ? strval( $_POST[ 'container_bg' ] ) : '';
		$archive_multiple_settings[ 'container_padding' ]	 = intval( $_POST[ 'container_padding' ] );
		$archive_multiple_settings[ 'disable_name' ]		 = intval( $_POST[ 'disable_name' ] );
		$archive_multiple_settings[ 'default_sidebar' ]		 = strval( $_POST[ 'default_sidebar' ] );
		$breadcrumbs										 = intval( $_POST[ 'disable_breadcrumbs' ] );
		if ( $breadcrumbs == 1 ) {
			$archive_multiple_settings[ 'enable_product_breadcrumbs' ] = 0;
		} else {
			$archive_multiple_settings[ 'enable_product_breadcrumbs' ] = 1;
		}
		update_option( 'archive_multiple_settings', $archive_multiple_settings );
		$price = intval( $_POST[ 'disable_price' ] );
		if ( $price == 1 ) {
			$product_currency_settings[ 'price_enable' ] = 'off';
			update_option( 'product_currency_settings', $product_currency_settings );
		} else {
			$product_currency_settings[ 'price_enable' ] = 'on';
			update_option( 'product_currency_settings', $product_currency_settings );
		}
		$image = intval( $_POST[ 'disable_image' ] );
		if ( $image == 1 ) {
			$product_page_settings[ 'enable_product_gallery' ] = 0;
			update_option( 'multi_single_options', $product_page_settings );
		} else {
			$product_page_settings[ 'enable_product_gallery' ] = 1;
			update_option( 'multi_single_options', $product_page_settings );
		}
		$shipping = intval( $_POST[ 'disable_shipping' ] );
		if ( $shipping == 1 ) {
			update_option( 'product_shipping_options_number', 0 );
		} else if ( !is_ic_shipping_enabled() ) {
			update_option( 'product_shipping_options_number', 2 );
		}
		$attributes = intval( $_POST[ 'disable_attributes' ] );
		if ( $attributes == 1 ) {
			update_option( 'product_attributes_number', 0 );
		} else if ( !is_ic_attributes_enabled() ) {
			update_option( 'product_attributes_number', 3 );
		}
	}

	echo 'done';

	wp_die(); // this is required to terminate immediately and return a proper response
}

add_action( 'wp_enqueue_scripts', 'enqueue_sample_product_scripts', 100 );

function enqueue_sample_product_scripts() {
	if ( isset( $_GET[ 'test_advanced' ] ) ) {
		$product_id = sample_product_id();
		if ( $product_id == get_the_ID() ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'iris', admin_url( 'js/iris.min.js' ), array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ) );
			wp_enqueue_script( 'wp-color-picker', admin_url( 'js/color-picker.min.js' ), array( 'iris' ) );
			$colorpicker_l10n = array(
				'clear'			 => __( 'Clear' ),
				'defaultString'	 => __( 'Default' ),
				'pick'			 => __( 'Select Color' )
			);
			wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );
		}
	}
}

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
	return $archive_multiple_settings[
	'integration_type' ];
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
	unset( $archive_multiple_settings[ 'container_width' ] );
	unset( $archive_multiple_settings[ 'container_bg' ] );
	unset( $archive_multiple_settings[ 'disable_name' ] );
	unset( $archive_multiple_settings[ 'container_padding' ] );
	unset( $archive_multiple_settings[ 'default_sidebar' ] );
	update_option( 'archive_multiple_settings', $archive_multiple_settings );
	permalink_options_update();
}

add_action( 'switch_theme', 'erase_integration_type_select' );

function create_sample_product_with_redirect() {
	if ( isset( $_GET[ 'create_sample_product_page' ] ) ) {
		$sample_product_id	 = create_sample_product();
		$url				 = get_permalink( $sample_product_id );
		$url				 = esc_url( add_query_arg( 'test_advanced', 1, $url ) );
		wp_redirect( $url );
		exit();
	}
}

add_action( 'admin_init', 'create_sample_product_with_redirect' );

function implecode_supported_themes() {
	return array( 'twentythirteen', 'twentyeleven', 'twentytwelve', 'twentyten', 'twentyfourteen', 'twentyfifteen', 'pub/minileven',
		'storefront' );
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

function get_custom_templates_folder() {
	return get_stylesheet_directory() . '/implecode/';
}

function get_custom_product_page_path() {
	$folder = get_custom_templates_folder();
	return $folder . 'product-page.php';
}

function get_custom_product_listing_path() {
	$folder = get_custom_templates_folder();
	return $folder . 'product-listing.php';
}

function get_page_php_path() {
	if ( file_exists( get_stylesheet_directory() . '/page.php' ) ) {
		$path = get_stylesheet_directory() . '/page.php';
	} else {
		$path = get_theme_root() . '/' . get_template() . '/page.php';
	}
	return $path;
}

add_filter( 'template_include', 'home_product_listing_redirect', 5 );

/**
 * Redirects the product listing page to homepage catalog if necessary
 *
 * @param type $template
 * @return type
 */
function home_product_listing_redirect( $template ) {
	if ( !is_paged() && !is_front_page() && is_ic_permalink_product_catalog() && is_product_listing_home_set() && is_post_type_archive( 'al_product' ) && !is_search() ) {
		wp_redirect( get_site_url(), 301 );
		exit;
	}
	return $template;
}

add_filter( 'redirect_canonical', 'ic_catalog_disable_redirect_canonical' );

/**
 * Fixes wrong pagination redirect on home page catalog listing
 *
 * @param boolean $redirect_url
 * @return boolean
 */
function ic_catalog_disable_redirect_canonical( $redirect_url ) {
	if ( is_paged() && is_front_page() && is_ic_permalink_product_catalog() && is_product_listing_home_set() ) {
		$redirect_url = false;
	}
	return $redirect_url;
}

<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages theme customizer
 *
 * @version		1.0.0
 * @package		catalog-me/framework/customizer
 * @author 		Norbert Dreszer
 */
add_action( 'customize_register', 'ecommerce_product_catalog_customizer' );

/**
 * Registers customizer settings
 *
 * @param object $wp_customize
 */
function ecommerce_product_catalog_customizer( $wp_customize ) {
	$wp_customize->add_panel( 'ic_product_catalog', array(
		'priority'		 => 10,
		'capability'	 => 'manage_product_settings',
		'theme_supports' => '',
		'title'			 => __( 'Product Catalog', 'ecommerce-product-catalog' ),
		'description'	 => __( 'Go to catalog page that you want to style so you can see the effect immediately.', 'ecommerce-product-catalog' ),
	) );

	$wp_customize->add_section( 'ic_product_catalog_integration', array(
		'title'			 => __( 'Product Page Style', 'ecommerce-product-catalog' ),
		'priority'		 => 30,
		'panel'			 => 'ic_product_catalog',
		'description'	 => __( 'Adjust these settings on a product page to see any effect.', 'ecommerce-product-catalog' )
	) );
	$wp_customize->add_section( 'ic_product_catalog_listing', array(
		'title'			 => __( 'Product Listing Style', 'ecommerce-product-catalog' ),
		'priority'		 => 30,
		'panel'			 => 'ic_product_catalog',
		'description'	 => __( 'Adjust these settings on a product listing to see any effect.', 'ecommerce-product-catalog' )
	) );
	$wp_customize->add_setting( 'multi_single_options[template]', array( 'type' => 'option', 'default' => '' ) );
	$wp_customize->add_control(
	'ic_pc_integration_template', array(
		'label'		 => __( 'Select template', 'ecommerce-product-catalog' ),
		'section'	 => 'ic_product_catalog_integration',
		'settings'	 => 'multi_single_options[template]',
		'type'		 => 'radio',
		'choices'	 => array( 'boxed' => __( 'Formatted', 'ecommerce-product-catalog' ), 'plain' => __( 'Plain', 'ecommerce-product-catalog' ) )
	)
	);
	$theme = get_option( 'template' );
	/*
	  $wp_customize->add_setting( 'archive_multiple_settings[container_width][' . $theme . ']', array( 'type' => 'option', 'default' => 100 ) );
	  $wp_customize->add_setting( 'archive_multiple_settings[container_padding][' . $theme . ']', array( 'type' => 'option', 'default' => 0 ) );
	 *
	 */
	$wp_customize->add_setting( 'archive_multiple_settings[container_bg][' . $theme . ']', array( 'type' => 'option', 'default' => '' ) );
	$wp_customize->add_setting( 'archive_multiple_settings[container_text][' . $theme . ']', array( 'type' => 'option', 'default' => '' ) );
	/*
	  $wp_customize->add_control(
	  'ic_pc_integration_width', array(
	  'label'			 => __( 'Width', 'ecommerce-product-catalog' ) . ' (%)',
	  'section'		 => 'ic_product_catalog_integration',
	  'settings'		 => 'archive_multiple_settings[container_width][' . $theme . ']',
	  'type'			 => 'number',
	  'description'	 => __( 'In most cases you should decrease this number to match your template container size.', 'ecommerce-product-catalog' )
	  )
	  );
	  $wp_customize->add_control(
	  'ic_pc_integration_padding', array(
	  'label'			 => __( 'Padding', 'ecommerce-product-catalog' ) . ' (px)',
	  'section'		 => 'ic_product_catalog_integration',
	  'settings'		 => 'archive_multiple_settings[container_padding][' . $theme . ']',
	  'type'			 => 'number',
	  'description'	 => __( 'Increase this number to make a space also on the top and bottom of the container. This is useful also if you are planning to enable the sidebar.', 'ecommerce-product-catalog' )
	  )
	  );
	 *
	 */
	$wp_customize->add_control(
	'ic_pc_integration_bg', array(
		'label'		 => __( 'Background', 'ecommerce-product-catalog' ),
		'section'	 => 'ic_product_catalog_integration',
		'settings'	 => 'archive_multiple_settings[container_bg][' . $theme . ']',
		'type'		 => 'color',
	)
	);
	$wp_customize->add_control(
	'ic_pc_integration_text', array(
		'label'		 => __( 'Text Color', 'ecommerce-product-catalog' ),
		'section'	 => 'ic_product_catalog_integration',
		'settings'	 => 'archive_multiple_settings[container_text][' . $theme . ']',
		'type'		 => 'color',
	)
	);
	$wp_customize->add_setting( 'archive_multiple_settings[default_sidebar]', array( 'type' => 'option', 'default' => '' ) );
	$wp_customize->add_control(
	'ic_pc_integration_sidebar', array(
		'label'		 => __( 'Sidebar', 'ecommerce-product-catalog' ),
		'section'	 => 'ic_product_catalog_integration',
		'settings'	 => 'archive_multiple_settings[default_sidebar]',
		'type'		 => 'radio',
		'choices'	 => array( 'none' => __( 'Disabled', 'ecommerce-product-catalog' ), 'left' => __( 'Left', 'ecommerce-product-catalog' ), 'right' => __( 'Right', 'ecommerce-product-catalog' ) )
	)
	);
	$wp_customize->add_setting( 'archive_multiple_settings[disable_name]', array( 'type' => 'option', 'default' => '' ) );
	$wp_customize->add_control(
	'ic_pc_integration_name', array(
		'label'		 => __( 'Disable Product Name', 'ecommerce-product-catalog' ),
		'section'	 => 'ic_product_catalog_integration',
		'settings'	 => 'archive_multiple_settings[disable_name]',
		'type'		 => 'checkbox',
	)
	);
	$wp_customize->add_setting( 'multi_single_options[enable_product_gallery]', array( 'type' => 'option', 'default' => '' ) );
	$wp_customize->add_control(
	'ic_pc_integration_gallery', array(
		'label'		 => __( 'Enable image', 'ecommerce-product-catalog' ),
		'section'	 => 'ic_product_catalog_integration',
		'settings'	 => 'multi_single_options[enable_product_gallery]',
		'type'		 => 'checkbox',
	)
	);
	$wp_customize->add_setting( 'catalog_lightbox', array( 'type' => 'option', 'default' => '' ) );
	$wp_customize->add_control(
	'ic_pc_integration_lightbox', array(
		'label'		 => __( 'Enable lightbox gallery', 'ecommerce-product-catalog' ),
		'section'	 => 'ic_product_catalog_integration',
		'settings'	 => 'catalog_lightbox',
		'type'		 => 'checkbox',
	)
	);
	$wp_customize->add_setting( 'multi_single_options[enable_product_gallery_only_when_exist]', array( 'type' => 'option', 'default' => '' ) );
	$wp_customize->add_control(
	'ic_pc_integration_disable_default_image', array(
		'label'		 => __( 'Enable image only when inserted', 'ecommerce-product-catalog' ),
		'section'	 => 'ic_product_catalog_integration',
		'settings'	 => 'multi_single_options[enable_product_gallery_only_when_exist]',
		'type'		 => 'checkbox',
	)
	);
	$wp_customize->add_setting( 'design_schemes[price-color]', array( 'type' => 'option', 'default' => '' ) );
	$wp_customize->add_control(
	'ic_pc_integration_price_color', array(
		'label'		 => __( 'Price Color', 'ecommerce-product-catalog' ),
		'section'	 => 'ic_product_catalog_integration',
		'settings'	 => 'design_schemes[price-color]',
		'type'		 => 'select',
		'choices'	 => array( 'red-price' => __( 'Red', 'ecommerce-product-catalog' ), 'orange-price' => __( 'Orange', 'ecommerce-product-catalog' ), 'green-price' => __( 'Green', 'ecommerce-product-catalog' ), 'blue-price' => __( 'Blue', 'ecommerce-product-catalog' ), 'grey-price' => __( 'Grey', 'ecommerce-product-catalog' ) )
	)
	);
	$wp_customize->add_setting( 'design_schemes[price-size]', array( 'type' => 'option', 'default' => '' ) );
	$wp_customize->add_control(
	'ic_pc_integration_price_size', array(
		'label'		 => __( 'Price Size', 'ecommerce-product-catalog' ),
		'section'	 => 'ic_product_catalog_integration',
		'settings'	 => 'design_schemes[price-size]',
		'type'		 => 'select',
		'choices'	 => array( 'big-price' => __( 'Big', 'ecommerce-product-catalog' ), 'small-price' => __( 'Small', 'ecommerce-product-catalog' ) )
	)
	);
}

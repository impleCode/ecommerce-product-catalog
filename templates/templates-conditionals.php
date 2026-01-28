<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WP Product template functions
 *
 * Here all plugin template functions are defined.
 *
 * @version        1.1.3
 * @package        ecommerce-product-catalog/
 * @author        impleCode
 */
function is_integration_mode_selected() {
	$return                    = false;
	$archive_multiple_settings = get_option( 'archive_multiple_settings', get_default_multiple_settings() );
	if ( ! is_array( $archive_multiple_settings ) ) {
		$archive_multiple_settings = array();
	}
	$theme     = get_option( 'template' );
	$prev_type = ( isset( $archive_multiple_settings['integration_type'] ) && ! is_array( $archive_multiple_settings['integration_type'] ) ) ? $archive_multiple_settings['integration_type'] : '';
	if ( ! isset( $archive_multiple_settings['integration_type'] ) || ( isset( $archive_multiple_settings['integration_type'] ) && ! is_array( $archive_multiple_settings['integration_type'] ) ) ) {
		$archive_multiple_settings['integration_type'] = array();
	}
	$archive_multiple_settings['integration_type'][ $theme ] = isset( $archive_multiple_settings['integration_type'][ $theme ] ) ? $archive_multiple_settings['integration_type'][ $theme ] : $prev_type;
	if ( $archive_multiple_settings['integration_type'][ $theme ] != '' || is_integraton_file_active() ) {
		$return = true;
	}

	return $return;
}

function is_integraton_file_active( $auto = false ) {
	if ( file_exists( get_product_adder_path( $auto ) ) ) {
		return true;
	} else {
		return false;
	}
}

function is_advanced_mode_forced( $true_when_shortcode = true ) {
//    $template = get_option('template');
	$return = false;
	if ( $true_when_shortcode ) {
		if ( function_exists( 'is_ic_shortcode_integration' ) && is_ic_shortcode_integration() ) {
			$return = true;
		}
	}
	if ( is_theme_implecode_supported() || is_integraton_file_active() || ic_is_woo_template_available() ) {
		$return = true;
	}

	return $return;
}

function is_theme_implecode_supported() {
	$template = get_option( 'template' );
	$return   = false;
	if ( in_array( $template, implecode_supported_themes() ) || current_theme_supports( 'ecommerce-product-catalog' ) /* || ic_is_woo_template_available() */ ) {
		$return = true;
	}

	return $return;
}

function implecode_supported_themes() {
	return array(
		'twentythirteen',
		'twentyeleven',
		'twentytwelve',
		'twentyten',
		'twentyfourteen',
		'twentyfifteen',
		'twentysixteen',
		'twentyseventeen',
		'twentynineteen',
		'pub/minileven',
		'storefront'
	);
}

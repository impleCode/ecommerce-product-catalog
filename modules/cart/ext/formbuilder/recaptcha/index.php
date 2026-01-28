<?php

if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/*
 *
 *
 *
 *
 *  @version       1.0.0
 *  @package       index
 *  @author        impleCode

 */

class ic_google_recaptcha {

	public $form_name	 = 'implecode_forms';
	public $form_label	 = "impleCode Forms";

	function __construct() {


		// by BestWebSoft
		if ( function_exists( 'gglcptch_admin_menu' ) ) {
			add_filter( 'gglcptch_add_custom_form', array( $this, 'BestWebSoft_settings' ) );
			add_filter( 'ic_formbuilder_before_button', array( $this, 'BestWebSoft_captcha' ) );
			add_filter( 'ic_formbuilder_error', array( $this, 'BestWebSoft_validation' ) );
			add_filter( 'ic_formbuilder_error_output', array( $this, 'BestWebSoft_validation_output' ) );
		}
	}

	function BestWebSoft_settings( $forms ) {
		$forms[ $this->form_name ] = array( "form_name" => $this->form_label );
		return $forms;
	}

	function BestWebSoft_captcha( $content ) {
		$content .= apply_filters( 'gglcptch_display_recaptcha', '', 'implecode_forms' );
		return $content;
	}

	function BestWebSoft_validation( $error ) {
		if ( !$error ) {
			return $error;
		}
		$captcha_output = apply_filters( 'gglcptch_verify_recaptcha', true, 'string', $this->form_name );
		if ( !$captcha_output ) {
			$error = true;
		}
		return $error;
	}

	function BestWebSoft_validation_output( $error_output ) {
		$captcha_output = apply_filters( 'gglcptch_verify_recaptcha', true, 'string', $this->form_name );
		if ( !$captcha_output ) {
			$error_output[ $this->form_name ] .= 'Robot test failed';
		}
		return $error_output;
	}

}

$ic_google_recaptcha = new ic_google_recaptcha;


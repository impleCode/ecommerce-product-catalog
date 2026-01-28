<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *
 *  @version       1.0.0
 *  @package
 *  @author        impleCode
 *
 */

class impleCode_Elementor_Widgets {

	function __construct() {
		add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_categories' ) );
	}

	function add_categories( $elements_manager ) {
		$elements_manager->add_category(
			'implecode', array(
			'title' => 'impleCode',
			'icon'  => 'fa fa-plug',
		) );
	}

	public function register_widgets() {
		require_once( AL_BASE_PATH . '/ext-comp/elementor/show_catalog_widget.php' );
		if ( method_exists( \Elementor\Plugin::instance()->widgets_manager, 'register' ) ) {
			\Elementor\Plugin::instance()->widgets_manager->register( new \Elementor_IC_Show_Catalog_Widget() );
		} else {
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_IC_Show_Catalog_Widget() );
		}
	}

}

$impleCode_Elementor_Widgets = new impleCode_Elementor_Widgets;



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

/**
 * Elementor Show Catalog Widget.
 *
 * Elementor widget that inserts catalog content into the page.
 *
 * @since 1.0.0
 */
class Elementor_IC_Show_Catalog_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * Retrieve Show Catalog widget name.
	 *
	 * @return string Widget name.
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function get_name() {
		return 'ic_show_catalog';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve Show Catalog widget title.
	 *
	 * @return string Widget title.
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function get_title() {
		return __( 'Show Catalog', 'ecommerce-product-catalog' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve Show Catalog widget icon.
	 *
	 * @return string Widget icon.
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function get_icon() {
		return 'dashicons dashicons-store';
	}

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the Show Catalog widget belongs to.
	 *
	 * @return array Widget categories.
	 * @since 1.0.0
	 * @access public
	 *
	 */
	public function get_categories() {
		return array( 'implecode' );
	}

	/**
	 * Register Show Catalog widget controls.
	 *
	 * Adds widget info.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function _register_controls() {


		$this->start_controls_section(
			'content_section',
			[
				'label' => __( 'Info', 'plugin-name' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'url',
			[
				'label' => __( 'Catalog Markup Element', 'ecommerce-product-catalog' ),
				'type'  => \Elementor\Controls_Manager::RAW_HTML,
				'raw'   => '<br>' . __( 'Use this to position your catalog container. The catalog container will show different content for product listing, category page, single product page and search results.', 'plugin-name' ) . '<br><br>' . __( 'By default it shows the main catalog page content.', 'ecommerce-product-catalog' ) . '<br><br>' . sprintf( __( 'Go to %scatalog settings%s to adjust what is visible here.', 'ecommerce-product-catalog' ), '<a target="_blank" href="' . admin_url( 'edit.php?post_type=al_product&page=product-settings.php' ) . '">', '</a>' ) . '<br><br>' . sprintf( __( '%sHere%s you can edit all text that is displayed inside the catalog container.', 'ecommerce-product-catalog' ), '<a target="_blank" href="' . admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=names-settings&submenu=archive-names' ) . '">', '</a>' )
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render Show Catalog widget output on the frontend.
	 *
	 * Written in PHP and used to generate the final HTML.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function render() {
		global $ic_shortcode_catalog;
		if ( ! empty( $ic_shortcode_catalog ) ) {
			if ( $ic_shortcode_catalog->is_page_builder_edit() ) {
				echo $ic_shortcode_catalog->catalog_shortcode();
			} else {
				echo '[show_product_catalog]';
			}
		}
	}

	protected function is_dynamic_content(): bool {
		return true;
	}

}

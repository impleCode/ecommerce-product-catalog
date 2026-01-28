<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *
 *  @version       1.0.0
 *  @author        impleCode
 *
 */

abstract class ic_catalog_widget extends WP_Widget {

	private $ic_name;
	private $ic_label;
	private $ic_description;

	/**
	 * @var string|null
	 */
	private $widget_filter_name;

	/**
	 * @var ic_html_util
	 */
	public $html;

	function __construct( $name, $label, $description, $filter_name = null ) {
		$this->ic_name            = $name;
		$this->ic_label           = $label;
		$this->ic_description     = $description;
		$this->widget_filter_name = $filter_name;
		if ( current_filter() === 'implecode_register_widgets' ) {
			$this->init();
		} else {
			add_action( 'implecode_register_widgets', array( $this, 'register' ) );
		}
	}

	/**
	 * The widget front-end display
	 *
	 * @return string
	 */
	function front( $instance, $args ) {

	}


	/**
	 * The widget default settings
	 *
	 * @return array
	 */
	abstract function default_settings();

	/**
	 * Settings rows array for the widget
	 *
	 * @return array where keys are names and values are attribute arrays with type, label, value and options
	 */
	abstract function settings_rows( $instance );

	function init() {
		if ( empty( $this->ic_name ) || empty( $this->ic_label ) || empty( $this->ic_description ) ) {
			return;
		}
		$this->html         = new ic_html_util;
		$this->html->fix_id = false;
		$widget_ops         = array( 'classname' => $this->ic_name, 'description' => $this->ic_description );
		parent::__construct( $this->ic_name, $this->ic_label, $widget_ops );
		if ( ! empty( $this->widget_filter_name ) ) {
			add_filter( 'ic_ajax_self_submit_return', array( $this, 'ajax' ) );
		}
		$this->additional();
	}


	function ajax( $return ) {
		if ( ! empty( $_POST['ajax_elements'][ $this->widget_filter_name ] ) ) {
			ob_start();
			$instance = isset( $_POST['ajax_elements'][ $this->widget_filter_name ]['instance'] ) ? $_POST['ajax_elements'][ $this->widget_filter_name ]['instance'] : array();
			$args     = isset( $_POST['ajax_elements'][ $this->widget_filter_name ]['args'] ) ? $_POST['ajax_elements'][ $this->widget_filter_name ]['args'] : array();
			the_widget( $this->ic_name, $instance, $args );
			$return[ $this->widget_filter_name ] = ob_get_clean();
		}

		return $return;
	}

	/**
	 * Does nothing unless extended
	 *
	 * @return void
	 */
	function additional() {

	}

	function register() {
		register_widget( $this->ic_name );
	}


	function widget( $args, $instance ) {
		ob_start();
		$this->front( $instance, $args );
		$front = ob_get_clean();

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		$before_widget = $this->before_widget( $args );
		if ( empty( $front ) && ! ic_string_contains( $before_widget, 'ic-empty-filter' ) ) {
			$before_widget = str_replace( 'class="', 'class="ic-empty-filter ', $before_widget );
		}
		echo $before_widget;
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo $front;
		echo $args['after_widget'];
	}

	function before_widget( $args ) {
		return $args['before_widget'];
	}

	function form_row( $label, $name, $value, $type = 'text', $options = array() ) {
		$id   = $this->get_field_id( $name );
		$name = $this->get_field_name( $name );
		echo $this->html->input( $type, $name, $value, $id, 0, $label, null, true, $options, array(), false );
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, $this->defaults() );
		$title    = $instance['title'];
		$this->form_row( __( 'Title:', 'ecommerce-product-catalog' ), 'title', $title );
		$settings = $this->settings_rows( $instance );
		foreach ( $settings as $name => $attr ) {
			$attr['type']    = empty( $attr['type'] ) ? 'text' : $attr['type'];
			$attr['label']   = isset( $attr['label'] ) ? $attr['label'] : '';
			$attr['value']   = isset( $attr['value'] ) ? $attr['value'] : '';
			$attr['options'] = isset( $attr['options'] ) ? $attr['options'] : array();
			$this->form_row( $attr['label'], $name, $attr['value'], $attr['type'], $attr['options'] );
		}
	}

	function update( $new_instance, $old_instance ) {

		return wp_parse_args( (array) $new_instance, $this->defaults() );
	}

	function defaults() {
		return array_merge( array( 'title' => '' ), $this->default_settings() );
	}
}


<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product search widget
 *
 * Here product search widget is defined.
 *
 * @version		1.4.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
class product_widget_search extends WP_Widget {

	function __construct() {
		if ( is_plural_form_active() ) {
			$names		 = get_catalog_names();
			$label		 = sprintf( __( '%s Search', 'ecommerce-product-catalog' ), ic_ucfirst( $names[ 'singular' ] ) );
			$sublabel	 = sprintf( __( 'A search form for your %s.', 'ecommerce-product-catalog' ), ic_lcfirst( $names[ 'plural' ] ) );
		} else {
			$label		 = __( 'Product Search', 'ecommerce-product-catalog' );
			$sublabel	 = __( 'A search form for your catalog items.', 'ecommerce-product-catalog' );
		}
		$widget_ops = array( 'classname' => 'product_search search widget_search', 'description' => $sublabel );
		parent::__construct( 'product_search', $label, $widget_ops );
	}

	function widget( $args, $instance ) {
		if ( get_integration_type() != 'simple' ) {

			$title = apply_filters( 'widget_title', empty( $instance[ 'title' ] ) ? '' : $instance[ 'title' ], $instance, $this->id_base );

			echo $args[ 'before_widget' ];
			if ( $title ) {
				echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];
			}
			ic_save_global( 'search_widget_instance', $instance );
			ic_show_search_widget_form();
			echo $args[ 'after_widget' ];
		}
	}

	function form( $instance ) {
		if ( get_integration_type() != 'simple' ) {
			$instance	 = wp_parse_args( (array) $instance, array( 'title' => '' ) );
			$title		 = $instance[ 'title' ];
			?>
			<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ecommerce-product-catalog' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p><?php
			do_action( 'product_search_widget_admin_form', $instance, $this );
		} else {
			if ( is_integration_mode_selected() ) {
				implecode_warning( sprintf( __( 'Search widget is disabled with simple theme integration. Please see <a href="%s">Theme Integration Guide</a> to enable product search widget.', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=search-widget' ) );
			} else {
				implecode_warning( sprintf( __( 'Search widget is disabled due to a lack of theme integration.%s', 'ecommerce-product-catalog' ), sample_product_button( 'p' ) ) );
			}
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance			 = $old_instance;
		$new_instance		 = wp_parse_args( (array) $new_instance, array( 'title' => '' ) );
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
		return apply_filters( 'product_search_widget_admin_save', $instance, $new_instance );
	}

}

/**
 * Returns search widget placeholder
 *
 * @return type
 */
function ic_get_search_widget_placeholder( $instance = null ) {
	if ( is_plural_form_active() ) {
		$names	 = get_catalog_names();
		$label	 = sprintf( __( '%s Search', 'ecommerce-product-catalog' ), ic_ucfirst( $names[ 'singular' ] ) );
	} else {
		$label = __( 'Product Search', 'ecommerce-product-catalog' );
	}
	return apply_filters( 'product_search_placeholder', $label, $instance );
}

/**
 * Returns search widget button text
 *
 * @return type
 */
function ic_get_search_widget_button_text() {
	return apply_filters( 'product_search_button_text', '' );
}

/**
 * Shows search widget form
 *
 */
function ic_show_search_widget_form() {
	ic_show_template_file( 'widgets/search-widget.php' );
}

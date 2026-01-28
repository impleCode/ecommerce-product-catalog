<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product search widget
 *
 * Here product search widget is defined.
 *
 * @version        1.4.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
class related_products_widget extends WP_Widget {

	function __construct() {
		if ( is_plural_form_active() ) {
			$names    = get_catalog_names();
			$label    = sprintf( __( 'Related %s', 'ecommerce-product-catalog' ), ic_ucfirst( $names['plural'] ) );
			$sublabel = sprintf( __( 'Shows related %s.', 'ecommerce-product-catalog' ), ic_lcfirst( $names['plural'] ) );
		} else {
			$label    = __( 'Related Catalog Items', 'ecommerce-product-catalog' );
			$sublabel = __( 'Shows related catalog items.', 'ecommerce-product-catalog' );
		}
		$widget_ops = array(
			'classname'             => 'related_products_widget',
			'description'           => $sublabel,
			'show_instance_in_rest' => true
		);
		parent::__construct( 'related_products_widget', $label, $widget_ops );
	}

	function widget( $args, $instance ) {
		$title      = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$product_id = isset( $instance['selectedProduct'] ) ? intval( $instance['selectedProduct'] ) : '';
		if ( empty( $product_id ) && ! is_ic_product_page() ) {
			return;
		}
		$related = get_related_products( null, false, $product_id );
		if ( ! empty( $related ) ) {
			echo $args['before_widget'];
			if ( $title ) {
				echo $args['before_title'] . $title . $args['after_title'];
			}

			// Use current theme search form if it exists

			echo $related;

			echo $args['after_widget'];
		}
	}

	function form( $instance ) {
		if ( get_integration_type() != 'simple' ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
			$title    = $instance['title'];
			?>
            <p><label
                    for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ecommerce-product-catalog' ); ?>
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                       value="<?php echo esc_attr( $title ); ?>"/></label></p><?php
		} else {
			//implecode_warning( sprintf( __( '%s is disabled due to a lack of main catalog listing.%s', 'ecommerce-product-catalog' ), __( 'Related products widget', 'ecommerce-product-catalog' ), ic_catalog_notices::create_listing_page_button() ) );
			ic_catalog_notices::simple_mode_notice();
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$new_instance      = wp_parse_args( (array) $new_instance, array( 'title' => '' ) );
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

}

add_action( 'implecode_register_widgets', 'register_related_products_widget' );

function register_related_products_widget() {
	register_widget( 'related_products_widget' );
}

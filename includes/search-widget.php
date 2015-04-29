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
		$widget_ops = array( 'classname' => 'product_search search widget_search', 'description' => __( 'A search form for your products.', 'al-ecommerce-product-catalog' ) );
		parent::__construct( 'product_search', __( 'Product Search', 'al-ecommerce-product-catalog' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		if ( get_integration_type() != 'simple' ) {
			$title = apply_filters( 'widget_title', empty( $instance[ 'title' ] ) ? '' : $instance[ 'title' ], $instance, $this->id_base );

			echo $args[ 'before_widget' ];
			if ( $title )
				echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];

			// Use current theme search form if it exists
			$search_button_text = apply_filters( 'product_search_button_text', __( 'Search', 'al-ecommerce-product-catalog' ) );
			echo '<form role="search" method="get" id="product_search_form" action="' . esc_url( home_url( '/' ) ) . '">
<input type="hidden" name="post_type" value="al_product" />
<input class="product-search-box" type="search" value="' . get_search_query() . '" id="s" name="s" placeholder="' . __( 'Product Search', 'al-ecommerce-product-catalog' ) . '" />
<input class="product-search-submit" type="submit" id="searchsubmit" value="' . $search_button_text . '" />
</form>';

			echo $args[ 'after_widget' ];
		}
	}

	function form( $instance ) {
		if ( get_integration_type() != 'simple' ) {
			$instance	 = wp_parse_args( (array) $instance, array( 'title' => '' ) );
			$title		 = $instance[ 'title' ];
			?>
			<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'al-ecommerce-product-catalog' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p><?php
		} else {
			implecode_warning( sprintf( __( 'Search widget is disabled with simple theme integration. Please see <a target="_blank" href="%s">Theme Integration Guide</a> to enable product search widget.', 'al-ecommerce-product-catalog' ), 'http://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=search-widget' ) );
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance			 = $old_instance;
		$new_instance		 = wp_parse_args( (array) $new_instance, array( 'title' => '' ) );
		$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
		return $instance;
	}

}

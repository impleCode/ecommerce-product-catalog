<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages shopping cart widget
 *
 * Here shopping cart widget is defined and managed.
 *
 * @version		1.0.0
 * @package		implecode-shopping-cart/includes
 * @author 		Norbert Dreszer
 */
add_action( 'implecode_register_widgets', 'ic_cart_register_cart_widget' );

function ic_cart_register_cart_widget() {
	register_widget( 'product_shopping_cart' );
}

class product_shopping_cart extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'product_shopping_cart', 'description' => __( 'Display Shopping Cart.', 'ecommerce-product-catalog' ) );
		parent::__construct( 'product_shopping_cart', __( 'Shopping Cart', 'ecommerce-product-catalog' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		//extract($args);
		$title						 = apply_filters( 'widget_title', empty( $instance[ 'title' ] ) ? '' : $instance[ 'title' ], $instance, $this->id_base );
		$instance[ 'show_search' ]	 = isset( $instance[ 'show_search' ] ) ? $instance[ 'show_search' ] : '';
		$instance[ 'hide_empty' ]	 = isset( $instance[ 'hide_empty' ] ) ? $instance[ 'hide_empty' ] : '';
		$instance[ 'text' ]			 = isset( $instance[ 'text' ] ) ? $instance[ 'text' ] : '';
		$cart_content				 = ic_shopping_cart_content( true );
		$shopping_cart_settings		 = get_shopping_cart_settings();
		//$content_array = array_filter(explode(',',$cart_content));
		$how_many					 = ic_get_cart_items_count( $cart_content );
		if ( $instance[ 'hide_empty' ] && empty( $how_many ) ) {
			echo '<div class="cart-hide-container ic_hidden">';
		}
		echo $args[ 'before_widget' ];
		if ( $title ) {
			echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];
		}
		//echo '<div id="shopping_cart_widget_container"><form id="shopping_cart_widget" method="post" action="'.ic_shopping_cart_page_url().'"><input hidden type="hidden" name="cart_content" value=\''.$cart_content.'\'><div class="product-shopping-cart"><input type="submit" value="'.$how_many.' '.__('selected', 'implecode-shopping-cart').'"></div></form>';
		$class = !empty( $instance[ 'hide_empty' ] ) ? 'check-if-empty' : '';
		if ( !empty( $class ) && empty( $how_many ) ) {
			$class .= ' ic_hidden';
		}
		echo '<div id="shopping_cart_widget_container" class="cart-widget-container ' . $class . '">';
		$show_empty = !empty( $instance[ 'hide_empty' ] ) ? false : true;
		echo ic_shopping_cart_button( $show_empty, $instance[ 'text' ] );
		if ( $instance[ 'show_search' ] == 1 ) {
			the_widget( 'product_widget_search' );
		}
		if ( $shopping_cart_settings[ 'stick_cart' ] == 1 ) {
			echo '<script>
			jQuery(window).load(function() {
			var widget = jQuery("#shopping_cart_widget_container"), offset = widget.offset();
			jQuery(window).scroll(function(){
			if (jQuery(this).scrollTop() > offset.top) {
				widget.addClass("fixed");
				widget.css("left", offset.left);
			} else {
				widget.removeClass("fixed");
				widget.css("left", "");
			}
			});
			});
			</script>';
		}
		echo '</div>';
		echo $args[ 'after_widget' ];
		if ( $instance[ 'hide_empty' ] && empty( $how_many ) ) {
			echo '</div>';
		}
	}

	function form( $instance ) {
		$instance					 = wp_parse_args( (array) $instance, array( 'title' => '', 'desc' => '', 'hide_empty' => '', 'text' => '' ) );
		$title						 = $instance[ 'title' ];
		$instance[ 'show_search' ]	 = isset( $instance[ 'show_search' ] ) ? $instance[ 'show_search' ] : '';
		$instance[ 'hide_empty' ]	 = isset( $instance[ 'hide_empty' ] ) ? $instance[ 'hide_empty' ] : '';
		$instance[ 'text' ]			 = isset( $instance[ 'text' ] ) ? $instance[ 'text' ] : '';
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ecommerce-product-catalog' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $new_instance;
		return $instance;
	}

}

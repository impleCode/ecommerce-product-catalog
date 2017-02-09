<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
class product_price_filter extends WP_Widget {

	function __construct() {
		if ( is_plural_form_active() ) {
			$names		 = get_catalog_names();
			$label		 = sprintf( __( '%s Price Filter', 'ecommerce-product-catalog' ), ic_ucfirst( $names[ 'singular' ] ) );
			$sublabel	 = sprintf( __( 'Filter %s by price.', 'ecommerce-product-catalog' ), ic_lcfirst( $names[ 'plural' ] ) );
		} else {
			$label		 = __( 'Catalog Price Filter', 'ecommerce-product-catalog' );
			$sublabel	 = __( 'Filter items by price.', 'ecommerce-product-catalog' );
		}
		$widget_ops = array( 'classname' => 'product_price_filter', 'description' => $sublabel );
		parent::__construct( 'product_price_filter', $label, $widget_ops );
	}

	function widget( $args, $instance ) {
		if ( get_integration_type() != 'simple' ) {
			if ( (!empty( $instance[ 'shortcode_support' ] ) && has_show_products_shortcode()) || (!is_ic_shortcode_query() && (is_ic_taxonomy_page() || is_ic_product_listing() || is_ic_product_search()) ) ) {

				$title = apply_filters( 'widget_title', empty( $instance[ 'title' ] ) ? '' : $instance[ 'title' ], $instance, $this->id_base );

				echo $args[ 'before_widget' ];
				if ( $title )
					echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];

				// Use current theme search form if it exists
				$min_price	 = isset( $_GET[ 'min-price' ] ) ? floatval( $_GET[ 'min-price' ] ) : '';
				$max_price	 = isset( $_GET[ 'max-price' ] ) ? floatval( $_GET[ 'max-price' ] ) : '';
				$currency	 = product_currency();
				$action		 = get_filter_widget_action( $instance );
				?>
				<div class="price-filter ic_ajax" data-ic_ajax="price-filter">
					<span class="filter-label"><?php _e( 'Price', 'ecommerce-product-catalog' ) ?>:</span>
					<form class="price-filter-form" action="<?php echo $action ?>">
						<?php
						foreach ( $_GET as $key => $value ) {
							if ( $key != 'min-price' && $key != 'max-price' ) {
								if ( is_array( $value ) ) {
									foreach ( $value as $a_value ) {
										if ( !empty( $a_value ) ) {
											echo '<input type="hidden" name="' . esc_attr( $key ) . '[]" value="' . esc_attr( $a_value ) . '" />';
										}
									}
								} else {
									echo '<input type="hidden" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '" />';
								}
							}
						}
						?>
						<input class="number-box" placeholder="<?php echo $currency ?>" type="number" min="0" step="0.01" name="min-price" value="<?php echo $min_price ?>"> - <input placeholder="<?php echo $currency ?>" min="0" step="0.01" type="number" class="number-box" name="max-price" value="<?php echo $max_price ?>">
						<input class="price-filter-submit" type="submit" value="OK">
					</form>
				</div>
				<?php
				echo $args[ 'after_widget' ];
			}
		}
	}

	function form( $instance ) {
		if ( get_integration_type() != 'simple' ) {
			$instance	 = wp_parse_args( (array) $instance, array( 'title' => '', 'shortcode_support' => 0 ) );
			$title		 = $instance[ 'title' ];
			?>
			<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ecommerce-product-catalog' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
			<p><input class="widefat" id="<?php echo $this->get_field_id( 'shortcode_support' ); ?>" name="<?php echo $this->get_field_name( 'shortcode_support' ); ?>" type="checkbox" value="1" <?php checked( 1, $instance[ 'shortcode_support' ] ) ?> /> <label for="<?php echo $this->get_field_id( 'shortcode_support' ); ?>"><?php _e( 'Support shortcode output filtering', 'ecommerce-product-catalog' ); ?></label></p><?php
		} else {
			if ( is_integration_mode_selected() ) {
				implecode_warning( sprintf( __( 'Sort widget is disabled with simple theme integration. Please see <a href="%s">Theme Integration Guide</a> to enable product sort widget.', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=search-widget' ) );
			} else {
				implecode_warning( sprintf( __( 'Sort widget is disabled due to a lack of theme integration.%s', 'ecommerce-product-catalog' ), sample_product_button( 'p' ) ) );
			}
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance						 = $old_instance;
		$new_instance					 = wp_parse_args( (array) $new_instance, array( 'title' => '', 'shortcode_support' => 0 ) );
		$instance[ 'title' ]			 = strip_tags( $new_instance[ 'title' ] );
		$instance[ 'shortcode_support' ] = intval( $new_instance[ 'shortcode_support' ] );
		return $instance;
	}

}

add_action( 'implecode_register_widgets', 'register_price_filter_widget' );

/**
 * Registers price filter widget
 *
 */
function register_price_filter_widget() {
	if ( is_ic_price_enabled() ) {
		register_widget( 'product_price_filter' );
	}
}

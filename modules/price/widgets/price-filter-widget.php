<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
class product_price_filter extends WP_Widget {

	function __construct() {
		if ( is_plural_form_active() ) {
			$names    = get_catalog_names();
			$label    = sprintf( __( '%s Price Filter', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) );
			$sublabel = sprintf( __( 'Filter %s by price.', 'ecommerce-product-catalog' ), ic_lcfirst( $names['plural'] ) );
		} else {
			$label    = __( 'Catalog Price Filter', 'ecommerce-product-catalog' );
			$sublabel = __( 'Filter items by price.', 'ecommerce-product-catalog' );
		}
		$widget_ops = array(
			'classname'             => 'product_price_filter',
			'description'           => $sublabel,
			'show_instance_in_rest' => true
		);
		parent::__construct( 'product_price_filter', $label, $widget_ops );
	}

	function widget( $args, $instance ) {
		if ( get_integration_type() != 'simple' ) {
			if ( ic_if_show_filter_widget( $instance, get_class( $this ) ) ) {
				$args  = apply_filters( 'ic_product_price_filter_widget_args', $args );
				$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

				echo $args['before_widget'];
				if ( $title ) {
					echo $args['before_title'] . $title . $args['after_title'];
				}

				$min_price = get_product_filter_value( 'min-price' );
				$max_price = get_product_filter_value( 'max-price' );
				$currency  = product_currency();
				$action    = get_filter_widget_action( $instance );
				$class     = '';
				if ( is_product_filter_active( 'min-price' ) || is_product_filter_active( 'max-price' ) ) {
					$class .= 'filter-active';
				}
				?>
                <div class="price-filter ic_ajax <?php design_schemes( 'box' ) ?> <?php echo $class ?>"
                     data-ic_ajax="price-filter"
                     data-ic_responsive_label="<?php _e( 'Price', 'ecommerce-product-catalog' ) ?>"
                     data-ic_ajax_data="<?php echo esc_attr( json_encode( array(
					     'instance' => $instance,
					     'args'     => $args
				     ) ) ) ?>">
                    <span class="filter-label"><?php _e( 'Price', 'ecommerce-product-catalog' ) ?>:</span>
                    <form class="price-filter-form" action="<?php echo $action ?>">
						<?php
						echo ic_get_to_hidden_field( $_GET, array( 'min-price', 'max-price', 'price-range' ) );
						?>
                        <input class="number-box" placeholder="<?php echo $currency ?>" type="number" min="0"
                               step="0.01" name="min-price" value="<?php echo $min_price ?>"> - <input
                                placeholder="<?php echo $currency ?>" min="0" step="0.01" type="number"
                                class="number-box" name="max-price" value="<?php echo $max_price ?>">
                        <input class="price-filter-submit" type="submit" value="OK">
						<?php
						echo ic_catalog_price_filter_reset();
						?>
                    </form>
					<?php do_action( 'ic_price_filter_end', $min_price, $max_price, $instance ) ?>
                </div>
				<?php
				echo $args['after_widget'];
			}
		}
	}

	function form( $instance ) {
		if ( get_integration_type() != 'simple' ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'shortcode_support' => 0 ) );
			$title    = $instance['title'];
			?>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ecommerce-product-catalog' ); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                           name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                           value="<?php echo esc_attr( $title ); ?>"/></label></p>
            <p><input class="widefat" id="<?php echo $this->get_field_id( 'shortcode_support' ); ?>"
                      name="<?php echo $this->get_field_name( 'shortcode_support' ); ?>" type="checkbox"
                      value="1" <?php checked( 1, $instance['shortcode_support'] ) ?> /> <label
                    for="<?php echo $this->get_field_id( 'shortcode_support' ); ?>"><?php _e( 'Enable also for shortcodes', 'ecommerce-product-catalog' ); ?></label>
            </p><?php
			do_action( 'ic_price_filter_widget_settings', $instance, $this );
		} else {
			//implecode_warning( sprintf( __( '%s is disabled due to a lack of main catalog listing.%s', 'ecommerce-product-catalog' ), __( 'Price filter widget', 'ecommerce-product-catalog' ), ic_catalog_notices::create_listing_page_button() ) );
			ic_catalog_notices::simple_mode_notice();
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance                      = $old_instance;
		$new_instance                  = wp_parse_args( (array) $new_instance, array(
			'title'             => '',
			'shortcode_support' => 0
		) );
		$instance['title']             = strip_tags( $new_instance['title'] );
		$instance['shortcode_support'] = intval( $new_instance['shortcode_support'] );

		return apply_filters( 'ic_price_filter_widget_save', $instance, $new_instance );
	}

}

add_action( 'implecode_register_widgets', 'ic_register_price_filter_widget' );

/**
 * Registers price filter widget
 *
 */
function ic_register_price_filter_widget() {
	if ( is_ic_price_enabled() ) {
		register_widget( 'product_price_filter' );
	}
}

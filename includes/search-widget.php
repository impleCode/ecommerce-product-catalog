<?php
/**
 * Manages product search widget
 *
 * Here product search widget is defined.
 *
 * @version		1.4.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class product_widget_search extends WP_Widget {

	function __construct() {
		$widget_ops = array('classname' => 'product_widget_search widget_search', 'description' => __( 'A search form for your products.', 'al-ecommerce-product-catalog') );
		parent::__construct('product_search', __('Product Search', 'al-ecommerce-product-catalog'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;

		// Use current theme search form if it exists
		echo '<form role="search" method="get" id="product_search_form" action="'.home_url( '/' ).'">
<input type="hidden" name="post_type" value="al_product" />
<input class="product-search-box" type="text" value="" id="s" name="s" placeholder="Search" />
<input class="product-search-submit" type="submit" name="submit" id="searchsubmit" value="Search" />
</form>';

		echo $after_widget;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
		$title = $instance['title'];
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'al-ecommerce-product-catalog'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label></p>
<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$new_instance = wp_parse_args((array) $new_instance, array( 'title' => ''));
		$instance['title'] = strip_tags($new_instance['title']);
		return $instance;
	}

}


<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product category widget
 *
 * Here product category widget is defined.
 *
 * @version        1.4.0
 * @package        ecommerce-product-catalog/includes
 * @author        Norbert Dreszer
 */
class product_cat_widget extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'classname' => 'widget_product_categories widget_categories', 'description' => __( 'A list or dropdown of product categories', 'al-ecommerce-product-catalog' ) );
		parent::__construct( 'product_categories', __( 'Product Categories', 'al-ecommerce-product-catalog' ), $widget_ops );
	}

	function widget( $args, $instance ) {
		if ( get_integration_type() != 'simple' ) {
			$title	 = apply_filters( 'widget_title', $instance[ 'title' ], $instance, $this->id_base );
			$c		 = !empty( $instance[ 'count' ] ) ? '1' : '0';
			$h		 = !empty( $instance[ 'hierarchical' ] ) ? '1' : '0';
			$d		 = !empty( $instance[ 'dropdown' ] ) ? '1' : '0';

			echo $args[ 'before_widget' ];
			if ( $title )
				echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];

			$cat_args = array( 'orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h, 'taxonomy' => 'al_product-cat' );

			if ( $d ) {
				$cat_args = array( 'orderby' => 'name', 'show_count' => $c, 'hierarchical' => $h, 'taxonomy' => 'al_product-cat', 'walker' => new my_Walker_CategoryDropdown, 'show_option_none' => __( 'Select Product Category', 'al-ecommerce-product-catalog' ) );
				wp_dropdown_categories( apply_filters( 'widget_product_categories_dropdown_args', $cat_args ) );
				?>

				<script type='text/javascript'>
					/* <![CDATA[ */
					var dropdown = document.getElementById( "cat" );
					function onCatChange() {
						if ( dropdown.options[dropdown.selectedIndex].value != '' ) {
							location.href = "<?php echo home_url(); ?>/?al_product-cat=" + dropdown.options[dropdown.selectedIndex].value;
						}
					}
					dropdown.onchange = onCatChange;
					/* ]]> */
				</script>

				<?php
			} else {
				?>
				<ul>
					<?php
					$cat_args[ 'title_li' ]	 = '';
					$cat_args				 = apply_filters( 'widget_product_categories_args', $cat_args, $instance );
					wp_list_categories( $cat_args );
					?>
				</ul>
				<?php
				do_action( 'after_product_category_widget', $cat_args, $instance );
			}

			echo $args[ 'after_widget' ];
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance					 = $old_instance;
		$instance[ 'title' ]		 = strip_tags( $new_instance[ 'title' ] );
		$instance[ 'count' ]		 = !empty( $new_instance[ 'count' ] ) ? 1 : 0;
		$instance[ 'hierarchical' ]	 = !empty( $new_instance[ 'hierarchical' ] ) ? 1 : 0;
		$instance[ 'dropdown' ]		 = !empty( $new_instance[ 'dropdown' ] ) ? 1 : 0;
		$instance					 = apply_filters( 'product_category_widget_save_instance', $instance, $new_instance, $old_instance );
		return $instance;
	}

	function form( $instance ) {
		//Defaults
		if ( get_integration_type() != 'simple' ) {
			$instance		 = wp_parse_args( (array) $instance, array( 'title' => '' ) );
			$title			 = esc_attr( $instance[ 'title' ] );
			$count			 = isset( $instance[ 'count' ] ) ? (bool) $instance[ 'count' ] : false;
			$hierarchical	 = isset( $instance[ 'hierarchical' ] ) ? (bool) $instance[ 'hierarchical' ] : false;
			$dropdown		 = isset( $instance[ 'dropdown' ] ) ? (bool) $instance[ 'dropdown' ] : false;
			?>
			<p><label
					for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'al-ecommerce-product-catalog' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
					   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
			</p>

			<p><input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'dropdown' ); ?>"
					  name="<?php echo $this->get_field_name( 'dropdown' ); ?>"<?php checked( $dropdown ); ?> />
				<label
					for="<?php echo $this->get_field_id( 'dropdown' ); ?>"><?php _e( 'Display as dropdown', 'al-ecommerce-product-catalog' ); ?></label><br/>

				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'count' ); ?>"
					   name="<?php echo $this->get_field_name( 'count' ); ?>"<?php checked( $count ); ?> />
				<label
					for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show product counts', 'al-ecommerce-product-catalog' ); ?></label><br/>

				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'hierarchical' ); ?>"
					   name="<?php echo $this->get_field_name( 'hierarchical' ); ?>"<?php checked( $hierarchical ); ?> />
				<label
					for="<?php echo $this->get_field_id( 'hierarchical' ); ?>"><?php _e( 'Show hierarchy', 'al-ecommerce-product-catalog' ); ?></label>
					<?php
					$object			 = $this;
					do_action( 'product_categories_widget_settings', $instance, $object );
					?> </p> <?php
			} else {
				implecode_warning( sprintf( __( 'Category widget is disabled with simple theme integration. Please see <a target="_blank" href="%s">Theme Integration Guide</a> to enable product category widget.', 'al-ecommerce-product-catalog' ), 'http://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=category-widget' ) );
			}
		}

	}

	class my_Walker_CategoryDropdown extends Walker_CategoryDropdown {

		function start_el( &$output, $category, $depth = 0, $args = Array(), $id = 0 ) {
			$pad = str_repeat( '&nbsp;', $depth * 3 );

			$cat_name = apply_filters( 'list_cats', $category->name, $category );
			$output .= "\t<option class=\"level-$depth\" value=\"" . $category->slug . "\"";
			if ( $category->term_id == isset( $args[ 'selected' ] ) ? $args[ 'selected' ] : '' )
				$output .= ' selected="selected"';
			$output .= '>';
			$output .= $pad . $cat_name;
			if ( isset( $args[ 'show_count' ] ) )
				$output .= '&nbsp;&nbsp;(' . $category->count . ')';
			if ( isset( $args[ 'show_last_update' ] ) ) {
				$format = 'Y-m-d';
				$output .= '&nbsp;&nbsp;' . gmdate( $format, $category->last_update_timestamp );
			}
			$output .= "</option>\n";
		}

	}

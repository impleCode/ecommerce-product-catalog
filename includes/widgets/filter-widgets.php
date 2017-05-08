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
add_action( 'widgets_init', 'register_product_filter_bar', 30 );

function register_product_filter_bar() {
	if ( is_plural_form_active() ) {
		$names		 = get_catalog_names();
		$label		 = sprintf( __( '%s Filters Bar', 'ecommerce-product-catalog' ), ic_ucfirst( $names[ 'singular' ] ) );
		$sublabel	 = sprintf( __( 'Appears above the product list. Recommended widgets: %1$s Search, %1$s Price Filter, %1$s Sort and %1$s Category Filter.', 'ecommerce-product-catalog' ), ic_ucfirst( $names[ 'singular' ] ) );
	} else {
		$label		 = __( 'Catalog Filters Bar', 'ecommerce-product-catalog' );
		$sublabel	 = __( 'Appears above the product list. Recommended widgets: Catalog Search, Catalog Price Filter, Catalog Sort and Catalog Category Filter.', 'ecommerce-product-catalog' );
	}
	$args = array(
		'name'			 => $label,
		'id'			 => 'product_sort_bar',
		'description'	 => $sublabel,
		'class'			 => '',
		'before_widget'	 => '<div id="%1$s" class="filter-widget %2$s">',
		'after_widget'	 => '</div>',
		'before_title'	 => '<h2 class="filter-widget-title">',
		'after_title'	 => '</h2>' );
	register_sidebar( $args );
}

class product_category_filter extends WP_Widget {

	function __construct() {
		if ( is_plural_form_active() ) {
			$names		 = get_catalog_names();
			$label		 = sprintf( __( '%s Category Filter', 'ecommerce-product-catalog' ), ic_ucfirst( $names[ 'singular' ] ) );
			$sublabel	 = sprintf( __( 'Filter %s by categories.', 'ecommerce-product-catalog' ), ic_lcfirst( $names[ 'plural' ] ) );
		} else {
			$label		 = __( 'Catalog Category Filter', 'ecommerce-product-catalog' );
			$sublabel	 = __( 'Filter items by categories.', 'ecommerce-product-catalog' );
		}
		$widget_ops = array( 'classname' => 'product_category_filter', 'description' => $sublabel );
		parent::__construct( 'product_category_filter', $label, $widget_ops );
	}

	function widget( $args, $instance ) {
		if ( get_integration_type() != 'simple' ) {
			if ( (!empty( $instance[ 'shortcode_support' ] ) && has_show_products_shortcode()) || (!is_ic_shortcode_query() && (is_ic_taxonomy_page() || is_ic_product_listing() || is_ic_product_search() )) ) {
				$title		 = apply_filters( 'widget_title', empty( $instance[ 'title' ] ) ? '' : $instance[ 'title' ], $instance, $this->id_base );
				$taxonomy	 = get_current_screen_tax();
				if ( is_ic_taxonomy_page() && !is_product_filter_active( 'product_category' ) ) {
					$categories = get_terms( $taxonomy, array( 'parent' => get_queried_object()->term_id ) );
				} else if ( !empty( $instance[ 'shortcode_support' ] ) && !is_ic_product_listing() && has_show_products_shortcode() && !is_product_filter_active( 'product_category' ) ) {
					global $shortcode_query;
					$parent		 = isset( $shortcode_query->query_vars[ 'term_id' ] ) ? $shortcode_query->query_vars[ 'term_id' ] : 0;
					$categories	 = get_terms( $taxonomy, array( 'parent' => $parent ) );
				} else {
					$categories = get_terms( $taxonomy, array( 'parent' => 0 ) );
				}
				$form		 = '';
				$child_form	 = '';
				foreach ( $categories as $category ) {
					$form .= get_product_category_filter_element( $category );
				}
				$class = 'product-category-filter-container';
				if ( is_product_filter_active( 'product_category' ) ) {
					$class			 .= ' filter-active';
					$filter_value	 = get_product_filter_value( 'product_category' );
					$children		 = get_terms( $taxonomy, array( 'parent' => $filter_value ) );
					//if ( !is_ic_taxonomy_page() ) {
					$parent_term	 = get_term_by( 'id', $filter_value, $taxonomy );
					if ( !empty( $parent_term->parent ) ) {
						$form .= get_product_category_filter_element( $parent_term );
					}
					//}
					if ( is_array( $children ) ) {
						foreach ( $children as $child ) {
							$child_form .= get_product_category_filter_element( $child );
						}
					}
				}
				if ( !empty( $form ) || !empty( $child_form ) ) {
					echo $args[ 'before_widget' ];
					if ( $title ) {
						echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];
					}
					echo '<div class="' . $class . ' ic_ajax" data-ic_ajax="product-category-filter-container">';
					echo $form;
					if ( !empty( $child_form ) ) {
						echo '<div class="child-category-filters">' . $child_form . '</div>';
					}
					echo '</div>';
					echo $args[ 'after_widget' ];
				}
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
				implecode_warning( sprintf( __( 'Category filter widget is disabled with simple theme integration. Please see <a href="%s">Theme Integration Guide</a> to enable product category filter widget.', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=search-widget' ) );
			} else {
				implecode_warning( sprintf( __( 'Category filter widget is disabled due to a lack of theme integration.%s', 'ecommerce-product-catalog' ), sample_product_button( 'p' ) ) );
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

class product_sort_filter extends WP_Widget {

	function __construct() {
		if ( is_plural_form_active() ) {
			$names		 = get_catalog_names();
			$label		 = sprintf( __( '%s Sort', 'ecommerce-product-catalog' ), ic_ucfirst( $names[ 'singular' ] ) );
			$sublabel	 = sprintf( __( 'Sort %s dropdown.', 'ecommerce-product-catalog' ), ic_lcfirst( $names[ 'plural' ] ) );
		} else {
			$label		 = __( 'Catalog Sort', 'ecommerce-product-catalog' );
			$sublabel	 = __( 'Sort catalog items dropdown.', 'ecommerce-product-catalog' );
		}
		$widget_ops = array( 'classname' => 'product_sort_filter', 'description' => $sublabel );
		parent::__construct( 'product_sort_filter', $label, $widget_ops );
	}

	function widget( $args, $instance ) {
		if ( get_integration_type() != 'simple' ) {
			if ( (!empty( $instance[ 'shortcode_support' ] ) && has_show_products_shortcode()) || (!is_ic_shortcode_query() && (is_ic_taxonomy_page() || is_ic_product_listing() || is_ic_product_search()) ) ) {

				$title = apply_filters( 'widget_title', empty( $instance[ 'title' ] ) ? '' : $instance[ 'title' ], $instance, $this->id_base );

				echo $args[ 'before_widget' ];
				if ( $title ) {
					echo $args[ 'before_title' ] . $title . $args[ 'after_title' ];
				}

				// Use current theme search form if it exists
				show_product_order_dropdown( null, null, $instance );
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
			<p><input class="widefat" id="<?php echo $this->get_field_id( 'shortcode_support' ); ?>" name="<?php echo $this->get_field_name( 'shortcode_support' ); ?>" type="checkbox" value="1" <?php checked( 1, $instance[ 'shortcode_support' ] ) ?> /> <label for="<?php echo $this->get_field_id( 'shortcode_support' ); ?>"><?php _e( 'Support shortcode output sorting', 'ecommerce-product-catalog' ); ?></label></p><?php
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

add_action( 'implecode_register_widgets', 'register_filter_widgets' );

function register_filter_widgets() {
	register_widget( 'product_category_filter' );
	register_widget( 'product_sort_filter' );
}

/**
 * Defines form action for filter widget
 *
 * @global type $post
 * @param type $instance
 * @return string
 */
function get_filter_widget_action( $instance ) {
	$action = product_listing_url();
	if ( (!empty( $instance[ 'shortcode_support' ] ) && has_show_products_shortcode()) || is_ic_taxonomy_page() ) {
		$action = '';
	}
	return $action;
}

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
add_action( 'widgets_init', 'register_product_filter_bar', 30 );

function register_product_filter_bar() {
	if ( is_plural_form_active() ) {
		$names    = get_catalog_names();
		$label    = sprintf( __( '%s Filters Bar', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) );
		$sublabel = sprintf( __( 'Appears above the product list. Recommended widgets: %1$s Search, %1$s Price Filter, %1$s Sort and %1$s Category Filter.', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) );
	} else {
		$label    = __( 'Catalog Filters Bar', 'ecommerce-product-catalog' );
		$sublabel = __( 'Appears above the product list. Recommended widgets: Catalog Search, Catalog Price Filter, Catalog Sort and Catalog Category Filter.', 'ecommerce-product-catalog' );
	}
	$args = array(
		'name'          => $label,
		'id'            => 'product_sort_bar',
		'description'   => $sublabel,
		'class'         => '',
		'before_widget' => '<div id="%1$s" class="filter-widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h2 class="filter-widget-title">',
		'after_title'   => '</h2>'
	);
	register_sidebar( $args );
}

function ic_set_filter_bar_default_widgets() {
	$sidebar_id     = 'product_sort_bar';
	$active_widgets = get_option( 'sidebars_widgets' );
	if ( empty( $active_widgets ) ) {
		$active_widgets = array();
	}
	if ( ! empty( $active_widgets[ $sidebar_id ] ) ) {

		return;
	}
	$widgets                       = ic_get_filter_bar_default_widgets();
	$active_widgets[ $sidebar_id ] = array();
	foreach ( $widgets as $widget ) {
		if ( ! empty( $widget ) ) {
			$active_widgets[ $sidebar_id ][] = $widget;
		}
	}

	update_option( 'sidebars_widgets', $active_widgets );
}

function ic_get_filter_bar_default_widgets() {
	$widgets = array();
	if ( class_exists( 'product_widget_search' ) ) {
		$widgets[] = ic_set_default_widget( 'product_search', array( 'title' => '' ) );
	}
	if ( class_exists( 'product_sort_filter' ) ) {
		$widgets[] = ic_set_default_widget( 'product_sort_filter', array( 'title' => '', 'shortcode_support' => 0 ) );
	}
	if ( class_exists( 'product_price_filter' ) ) {
		$widgets[] = ic_set_default_widget( 'product_price_filter', array( 'title' => '', 'shortcode_support' => 0 ) );
	}
	if ( class_exists( 'product_category_filter' ) ) {
		$widgets[] = ic_set_default_widget( 'product_category_filter', array(
			'title'             => '',
			'shortcode_support' => 0
		) );
	}

	return $widgets;
}

function ic_set_default_widget( $widget_name, $widget_content ) {
	if ( function_exists( 'register_block_type' ) ) {
		if ( $widget_name === 'product_search' ) {
			$widget_name = 'product-search-widget';
		}
		$widget_name = str_replace( '_', '-', $widget_name );
		$option_name = 'widget_block';
	} else {
		$option_name = 'widget_' . $widget_name;
	}
	$option = get_option( $option_name );
	if ( ! empty( $option ) && is_array( $option ) ) {
		$key = ic_array_key_last( $option );
		if ( is_numeric( $key ) ) {
			$key ++;
		} else {
			$key = 99;
		}
	} else {
		$option = array();
		$key    = 1;
	}
	if ( function_exists( 'register_block_type' ) ) {
		$registered_name = 'block-' . $key;
		$option[ $key ]  = array( 'content' => '<!-- wp:ic-epc/' . $widget_name . ' /-->' );
	} else {
		$registered_name = $widget_name . '-' . $key;
		$option[ $key ]  = $widget_content;
	}
	update_option( $option_name, $option );

	return $registered_name;
}

function ic_if_show_filter_widget( $instance = null, $filter_name = '' ) {
	if ( ic_is_rendering_block() || ic_is_rendering_catalog_block() || ( ( ic_get_global( 'inside_show_catalog_shortcode' ) || ! empty( $instance['shortcode_support'] ) ) && ( has_show_products_shortcode() || ic_is_rendering_products_block() ) ) || ( ! is_ic_shortcode_query() && ( is_ic_ajax() || ( ( is_ic_taxonomy_page() || is_ic_product_listing() || ( is_ic_product_search() && more_products() ) ) ) ) ) ) {

		return apply_filters( 'ic_if_show_filter_widget', true, $filter_name );
	}

	return false;
}

class product_category_filter extends WP_Widget {

	function __construct() {
		if ( is_plural_form_active() ) {
			$names    = get_catalog_names();
			$label    = sprintf( __( '%s Category Filter', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) );
			$sublabel = sprintf( __( 'Filter %s by categories.', 'ecommerce-product-catalog' ), ic_lcfirst( $names['plural'] ) );
		} else {
			$label    = __( 'Catalog Category Filter', 'ecommerce-product-catalog' );
			$sublabel = __( 'Filter items by categories.', 'ecommerce-product-catalog' );
		}
		$widget_ops = array(
			'classname'             => 'product_category_filter',
			'description'           => $sublabel,
			'show_instance_in_rest' => true
		);
		parent::__construct( 'product_category_filter', $label, $widget_ops );
	}

	function widget( $args, $instance ) {
		if ( get_integration_type() != 'simple' ) {
			if ( ic_if_show_filter_widget( $instance ) ) {
				$cache_key  = 'ic_catalog_category_filter' . md5( serialize( $instance ) );
				$form       = ic_get_global( $cache_key );
				$class      = apply_filters( 'ic_catalog_category_filter_class', 'product-category-filter-container', $instance );
				$title      = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
				$child_form = '';
				if ( empty( $form ) || is_product_filter_active( 'product_category' ) ) {
					global $shortcode_query;
					$taxonomy      = get_current_screen_tax();
					$post_ids      = null;
					$category_args = array();
					if ( ! empty( $shortcode_query ) && ! empty( $instance['shortcode_support'] ) && has_show_products_shortcode() ) {
						$excluded_terms = array();
						$shortcode_tax  = array();
						if ( is_product_filter_active( 'product_category' ) ) {
							$shortcode_terms = array();
							if ( ! empty( $shortcode_query->query['tax_query'] ) ) {
								foreach ( $shortcode_query->query['tax_query'] as $shortcode_tax_query ) {
									if ( isset( $shortcode_tax_query['terms'] ) && is_array( $shortcode_tax_query['terms'] ) ) {
										$shortcode_terms = array_merge( $shortcode_terms, $shortcode_tax_query['terms'] );
										if ( ! empty( $shortcode_tax_query['taxonomy'] ) && ! in_array( $shortcode_tax_query['taxonomy'], $shortcode_tax ) ) {
											$shortcode_tax[] = $shortcode_tax_query['taxonomy'];
										}
									}
								}
							}
							$current_filter_value = get_product_filter_value( 'product_category' );
							if ( ! empty( $current_filter_value ) ) {
								if ( is_array( $current_filter_value ) ) {
									$excluded_terms = array_merge( $excluded_terms, $current_filter_value );
								} else {
									$excluded_terms[] = $current_filter_value;
								}
							}

							if ( ! empty( $shortcode_terms ) ) {
								$excluded_terms = array_diff( $excluded_terms, $shortcode_terms );
							}
							if ( ! empty( $excluded_terms ) && is_array( $excluded_terms ) ) {
								foreach ( $excluded_terms as $excluded_term ) {
									$this_term = get_term( $excluded_term );
									if ( ! empty( $this_term->taxonomy ) && ! in_array( $this_term->taxonomy, $shortcode_tax ) ) {
										$shortcode_tax[] = $this_term->taxonomy;
									}
								}
							}
						}
						$post_ids             = ic_get_current_products( array(), $shortcode_tax, array(), $excluded_terms );
						$category_args['all'] = 1;

					}

					ic_save_global( 'taxonomy_terms_' . $taxonomy, ic_get_terms( array( 'taxonomy' => $taxonomy ) ) ); // get all and save them to cache
					$categories                = ic_catalog_get_current_categories( $taxonomy, apply_filters( 'ic_catalog_category_filter_cat_args', $category_args, $instance ) );
					$form                      = '';
					$category_ids              = wp_list_pluck( $categories, 'term_id' );
					$category_elements         = array();
					$dowhile                   = true;
					$i                         = 0;
					$parsed_current_categories = array();
					$show_count_by_default     = apply_filters( 'ic_catalog_category_filter_show_count', true, $instance );
					while ( $dowhile ) {
						if ( ! isset( $categories[ $i ] ) ) {
							$dowhile = false;
						} else {
							$category = $categories[ $i ];
							$i ++;
							if ( ! in_array( $category->term_id, $parsed_current_categories ) ) {
								$parsed_current_categories[] = $category->term_id;
							}
							if ( ! empty( $category->parent ) && is_numeric( $category->parent ) ) {
								if ( in_array( $category->parent, $category_ids ) ) {
									continue;
								} else {
									$parent_category = get_term( $category->parent );
									if ( ! empty( $parent_category ) && ! is_wp_error( $parent_category ) ) {
										$categories[]   = $parent_category;
										$category_ids[] = $category->parent;
									}
									continue;
								}
							}
							//$category_elements[ $category->name ] = apply_filters( 'ic_catalog_category_filter_parent', get_product_category_filter_element( $category, $post_ids, true, $show_count_by_default ), $category, $post_ids, $instance, $parsed_current_categories, $category_ids );
						}
					}
					foreach ( $categories as $category ) {
						if ( empty( $category->parent ) && ! empty( $category->name ) ) {
							$category_elements[ $category->name ] = apply_filters( 'ic_catalog_category_filter_parent', get_product_category_filter_element( $category, $post_ids, true, $show_count_by_default ), $category, $post_ids, $instance, $parsed_current_categories, $category_ids );
						}
					}
					if ( ! empty( $category_elements ) ) {
						if ( apply_filters( 'ic_catalog_category_filter_sort', true ) ) {
							ksort( $category_elements );
						}
						$form .= apply_filters( 'ic_catalog_category_filter_elements_ready', implode( '', $category_elements ) );
					}

					if ( is_product_filter_active( 'product_category' ) ) {
						$class        .= ' filter-active';
						$filter_value = get_product_filter_value( 'product_category' );
						if ( is_numeric( $filter_value ) ) {
							$children    = ic_catalog_get_categories( $filter_value );
							$parent_term = get_term_by( 'id', $filter_value, $taxonomy );
							if ( ! empty( $parent_term->parent ) ) {
								$form .= get_product_category_filter_element( $parent_term, $post_ids );
							}
							if ( is_array( $children ) ) {
								foreach ( $children as $child ) {
									$child_form .= get_product_category_filter_element( $child, $post_ids );
								}
							}
						}
					} else {
						ic_save_global( $cache_key, $form, true );
					}

				}
				if ( ! is_ic_ajax() && empty( $form ) && empty( $child_form ) ) {
					$args['before_widget'] = str_replace( 'class="', 'class="ic-empty-filter ', $args['before_widget'] );
				}
				if ( ! is_ic_ajax() || ( ! empty( $form ) || ! empty( $child_form ) ) ) {
					$child_form = apply_filters( 'ic_catalog_category_filter_child_form', $child_form, $instance );
					if ( isset( $args['before_widget'] ) ) {
						echo $args['before_widget'];
					}
					if ( $title ) {
						echo $args['before_title'] . $title . $args['after_title'];
					}
					echo '<div class="' . $class . ' ic_ajax" data-ic_responsive_label="' . __( 'Category', 'ecommerce-product-catalog' ) . '" data-ic_ajax="product-category-filter-container" data-ic_ajax_data="' . esc_attr( json_encode( array(
							'instance' => $instance,
							'args'     => $args
						) ) ) . '">';
					if ( ! empty( $form ) ) {
						echo apply_filters( 'ic_catalog_category_filter_form', $form, $instance );
					}
					if ( ! empty( $child_form ) ) {
						echo '<div class="child-category-filters">' . $child_form . '</div>';
					}
					echo '</div>';
					if ( isset( $args['after_widget'] ) ) {
						echo $args['after_widget'];
					}
				}
			}
		}
	}

	function form( $instance ) {
		if ( get_integration_type() != 'simple' ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'shortcode_support' => 0 ) );
			$title    = $instance['title'];
			?>
            <p>
                <label
                        for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ecommerce-product-catalog' ); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                           name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                           value="<?php echo esc_attr( $title ); ?>"/></label></p>
            <p><input class="widefat" id="<?php echo $this->get_field_id( 'shortcode_support' ); ?>"
                      name="<?php echo $this->get_field_name( 'shortcode_support' ); ?>" type="checkbox"
                      value="1" <?php checked( 1, $instance['shortcode_support'] ) ?> /> <label
                        for="<?php echo $this->get_field_id( 'shortcode_support' ); ?>"><?php _e( 'Enable also for shortcodes', 'ecommerce-product-catalog' ); ?></label>
            </p>
			<?php
			do_action( 'ic_catalog_category_filter_settings', $this, $instance );
		} else {
			//implecode_warning( sprintf( __( '%s is disabled due to a lack of main catalog listing.%s', 'ecommerce-product-catalog' ), __( 'Category filter', 'ecommerce-product-catalog' ), ic_catalog_notices::create_listing_page_button() ) );
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

		return apply_filters( 'ic_catalog_category_filter_settings_save', $instance, $new_instance );
	}

}

class product_sort_filter extends WP_Widget {

	function __construct() {
		if ( is_plural_form_active() ) {
			$names    = get_catalog_names();
			$label    = sprintf( __( '%s Sort', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) );
			$sublabel = sprintf( __( 'Sort %s dropdown.', 'ecommerce-product-catalog' ), ic_lcfirst( $names['plural'] ) );
		} else {
			$label    = __( 'Catalog Sort', 'ecommerce-product-catalog' );
			$sublabel = __( 'Sort catalog items dropdown.', 'ecommerce-product-catalog' );
		}
		$widget_ops = array(
			'classname'             => 'product_sort_filter',
			'description'           => $sublabel,
			'show_instance_in_rest' => true
		);
		parent::__construct( 'product_sort_filter', $label, $widget_ops );
	}

	function widget( $args, $instance ) {
		if ( get_integration_type() != 'simple' ) {
			if ( ic_if_show_filter_widget( $instance ) ) {

				$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

				echo $args['before_widget'];
				if ( $title ) {
					echo $args['before_title'] . $title . $args['after_title'];
				}

				// Use current theme search form if it exists
				show_product_order_dropdown( null, null, $instance );
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
                <label
                        for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ecommerce-product-catalog' ); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                           name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                           value="<?php echo esc_attr( $title ); ?>"/></label></p>
            <p><input class="widefat" id="<?php echo $this->get_field_id( 'shortcode_support' ); ?>"
                      name="<?php echo $this->get_field_name( 'shortcode_support' ); ?>" type="checkbox"
                      value="1" <?php checked( 1, $instance['shortcode_support'] ) ?> /> <label
                    for="<?php echo $this->get_field_id( 'shortcode_support' ); ?>"><?php _e( 'Enable also for shortcodes', 'ecommerce-product-catalog' ); ?></label>
            </p><?php
		} else {
			//implecode_warning( sprintf( __( '%s is disabled due to a lack of main catalog listing.%s', 'ecommerce-product-catalog' ), __( 'Sort widget', 'ecommerce-product-catalog' ), ic_catalog_notices::create_listing_page_button() ) );
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

		return $instance;
	}

}

class ic_product_size_filter extends WP_Widget {

	function __construct() {
		if ( is_plural_form_active() ) {
			$names    = get_catalog_names();
			$label    = sprintf( __( '%s Size Filter', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) );
			$sublabel = sprintf( __( 'Filter %s by size.', 'ecommerce-product-catalog' ), ic_lcfirst( $names['plural'] ) );
		} else {
			$label    = __( 'Catalog Size Filter', 'ecommerce-product-catalog' );
			$sublabel = __( 'Filter items by size.', 'ecommerce-product-catalog' );
		}
		$classname = 'product_size_filter';
		if ( is_product_filter_active( '_size_length' ) ) {
			$classname .= ' active';
		}
		$widget_ops = array( 'classname' => $classname, 'description' => $sublabel, 'show_instance_in_rest' => true );
		parent::__construct( 'ic_product_size_filter', $label, $widget_ops );
	}

	function widget( $args, $instance ) {
		if ( get_integration_type() != 'simple' ) {
			if ( ic_if_show_filter_widget( $instance ) && function_exists( 'ic_size_field_names' ) ) {
				ob_start();
				$this->size_filters();
				$size_filters = ob_get_clean();
				if ( empty( $size_filters ) ) {
					return;
				}
				$this->styles();

				$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
				echo $args['before_widget'];
				if ( $title ) {
					echo $args['before_title'] . $title . $args['after_title'];
				}
				do_action( 'ic_size_widget_before_sliders', $instance );
				echo '<form class="product-size-filter-container ic-slider-container toReload ic_ajax ' . design_schemes( 'box', 0 ) . '" data-ic_responsive_label="' . __( 'Size', 'ecommerce-product-catalog' ) . '" data-ic_ajax="product-size-filter-container" action="' . get_filter_widget_action( $instance ) . '">';
				$hidden_fields = array_keys( ic_size_field_names() );
				echo ic_get_to_hidden_field( $_GET, $hidden_fields );
				echo $size_filters;
				echo '</form>';
				echo $args['after_widget'];
			}
		}
	}

	function size_filters() {
		do_action( 'ic_size_filters' );
	}

	function form( $instance ) {
		if ( get_integration_type() != 'simple' ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'shortcode_support' => 0 ) );
			$title    = $instance['title'];
			?>
            <p>
                <label
                        for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ecommerce-product-catalog' ); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                           name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                           value="<?php echo esc_attr( $title ); ?>"/></label></p>
            <p><input class="widefat" id="<?php echo $this->get_field_id( 'shortcode_support' ); ?>"
                      name="<?php echo $this->get_field_name( 'shortcode_support' ); ?>" type="checkbox"
                      value="1" <?php checked( 1, $instance['shortcode_support'] ) ?> /> <label
                    for="<?php echo $this->get_field_id( 'shortcode_support' ); ?>"><?php _e( 'Enable also for shortcodes', 'ecommerce-product-catalog' ); ?></label>
            </p><?php
			do_action( 'ic_size_filter_widget_form', $instance, $this );
		} else {
			//implecode_warning( sprintf( __( '%s is disabled due to a lack of main catalog listing.%s', 'ecommerce-product-catalog' ), __( 'Size filter', 'ecommerce-product-catalog' ), ic_catalog_notices::create_listing_page_button() ) );
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

		return apply_filters( 'ic_size_widget_save', $instance, $new_instance );
	}

	function styles() {
		wp_enqueue_style( 'ic_range_slider' );
		wp_enqueue_script( 'ic_range_slider' );
	}

}

class ic_active_filters_widget extends WP_Widget {

	function __construct() {
		$label      = __( 'Catalog Active Filters', 'ecommerce-product-catalog' );
		$sublabel   = __( 'Displays active filters with an option to remove.', 'ecommerce-product-catalog' );
		$widget_ops = array(
			'classname'             => 'ic_active_filters_widget',
			'description'           => $sublabel,
			'show_instance_in_rest' => true
		);
		parent::__construct( 'ic_active_filters_widget', $label, $widget_ops );
	}

	function widget( $args, $instance ) {
		if ( get_integration_type() != 'simple' ) {
			if ( ic_if_show_filter_widget( $instance ) ) {
				$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
				echo $args['before_widget'];
				if ( $title ) {
					echo $args['before_title'] . $title . $args['after_title'];
				}
				echo ic_get_active_filters_html();
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
                <label
                        for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'ecommerce-product-catalog' ); ?>
                    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
                           name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
                           value="<?php echo esc_attr( $title ); ?>"/></label></p>
			<?php
		} else {
			//implecode_warning( sprintf( __( '%s is disabled due to a lack of main catalog listing.%s', 'ecommerce-product-catalog' ), __( 'Sort widget', 'ecommerce-product-catalog' ), ic_catalog_notices::create_listing_page_button() ) );
			ic_catalog_notices::simple_mode_notice();
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance          = $old_instance;
		$new_instance      = wp_parse_args( (array) $new_instance, array(
			'title' => '',
		) );
		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;
	}

}

add_action( 'implecode_register_widgets', 'register_filter_widgets' );

function register_filter_widgets() {
	register_widget( 'product_category_filter' );
	register_widget( 'product_sort_filter' );
	register_widget( 'ic_product_size_filter' );
	register_widget( 'ic_active_filters_widget' );
}

/**
 * Defines form action for filter widget
 *
 * @param type $instance
 *
 * @return string
 * @global type $post
 */
function get_filter_widget_action( $instance ) {
	if ( is_ic_inside_filters_bar() || ( ! empty( $instance['shortcode_support'] ) && has_show_products_shortcode() ) || is_ic_taxonomy_page() || is_ic_product_search() ) {
		$action = '';
	} else {
		if ( ! is_ic_catalog_page() && ic_get_global( 'inside_show_catalog_shortcode' ) ) {
			$action = '';
		} else {
			$action = apply_filters( 'ic_product_listing_widget_action', product_listing_url() );
		}
	}

	return $action;
}

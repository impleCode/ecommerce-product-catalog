<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages catalog classic grid theme
 *
 * Here classic grid theme is defined and managed.
 *
 * @version		1.2.0
 * @package		ecommerce-product-catalog/templates/themes
 * @author 		Norbert Dreszer
 */

/**
 * Shows classic grid example in product settings
 *
 */
function example_grid_archive_theme() {
	?>
	<div class="archive-listing classic-grid example">
		<a href="#grid-theme">
			<div style="background-image:url('<?php echo AL_PLUGIN_BASE_PATH . 'templates/themes/img/example-product.jpg'; ?>');" class="classic-grid-element"></div>
			<h3 class="product-name">White Lamp</h3>
			<div class="product-price">10 USD</div>
		</a>
	</div>
	<?php
}

/*
  function grid_archive_theme( $post ) {
  ?>
  <div class="archive-listing classic-grid">
  <a href="<?php the_permalink(); ?>">
  <div style="background-image:url('<?php
  if ( wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) ) ) {
  $url = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
  } else {
  $url = default_product_thumbnail_url();
  }
  echo $url;
  ?>');" class="classic-grid-element"></div>
  <div class="product-name"><?php the_title(); ?></div>
  <?php do_action( 'archive_price', $post ); ?>
  </a>
  </div>
  <?php
  }
 */

/**
 * Returns classic grid element for a given product
 *
 * @param object $post Product post object
 * @param string $archive_template
 * @return string
 */
function get_grid_archive_theme( $post, $archive_template = null ) {
	$archive_template	 = isset( $archive_template ) ? $archive_template : get_product_listing_template();
	$return				 = '';
	if ( $archive_template == 'grid' ) {
		$product_id			 = $post->ID;
		$image_id			 = get_post_thumbnail_id( $product_id );
		$thumbnail_product	 = wp_get_attachment_image_src( $image_id, 'classic-grid-listing' );
		$product_name		 = get_product_name();
		if ( $thumbnail_product ) {
			$img_class[ 'alt' ]		 = $product_name;
			$img_class[ 'class' ]	 = 'classic-grid-image';
			$image					 = wp_get_attachment_image( $image_id, 'classic-grid-listing', false, $img_class );
		} else {
			$url	 = default_product_thumbnail_url();
			$image	 = '<img src="' . $url . '" class="classic-grid-image default-image" alt="' . $product_name . '" >';
		}
		$archive_price			 = apply_filters( 'archive_price_filter', '', $post );
		$classic_grid_settings	 = get_classic_grid_settings();
		$row_class				 = get_row_class( $classic_grid_settings );
		$return					 = '<div class="archive-listing product-' . $product_id . ' classic-grid ' . $row_class . ' ' . product_class( $product_id ) . '">';
		$return					 .= '<a href="' . get_permalink() . '">';
		//$return .= '<div style="background-image:url(\'' . $url . '\');" class="classic-grid-element"></div>';
		$return					 .= '<div class="classic-grid-image-wrapper"><div class="pseudo"></div><div class="image">' . $image . '</div></div>';
		$return					 .= '<h3 class="product-name">' . $product_name . '</h3>' . $archive_price;
		if ( $classic_grid_settings[ 'attributes' ] == 1 && function_exists( 'product_attributes_number' ) ) {
			$attributes_number = product_attributes_number();
			if ( $attributes_number > 0 && has_product_any_attributes( $product_id ) ) {
				$max_listing_attributes	 = apply_filters( 'max_product_listing_attributes', $classic_grid_settings[ 'attributes_num' ] );
				$return					 .= '<div class="product-attributes">';
				$a						 = 0;
				for ( $i = 1; $i <= $attributes_number; $i++ ) {
					$attribute_value = get_attribute_value( $i, $product_id );
					if ( !empty( $attribute_value ) ) {
						$return .= '<div><span class="attribute-label-listing">' . get_attribute_label( $i, $product_id ) . ':</span> <span class="attribute-value-listing">' . get_attribute_value( $i, $product_id ) . '</span> <span class="attribute-unit-listing">' . get_attribute_unit( $i, $product_id ) . '</span></div>';
						$a++;
					}
					if ( $a == $max_listing_attributes ) {
						break;
					}
				}
				$return .= '</div>';
			}
		}
		$return	 .= '</a>';
		$return	 .= apply_filters( 'classic_grid_product_listing_element', '', $product_id );
		$return	 .= '</div>';
	}
	return $return;
}

/**
 * Returns classic grid element for given product category
 *
 * @param object $product_cat Product category object
 * @param string $archive_template
 * @return string
 */
function get_grid_category_theme( $product_cat, $archive_template ) {
	if ( $archive_template == 'grid' ) {
		$image_id		 = get_product_category_image_id( $product_cat->term_id );
		$category_image	 = wp_get_attachment_image_src( $image_id, 'classic-grid-listing' );
		if ( $category_image ) {
			$img_class[ 'alt' ]		 = $product_cat->name;
			$img_class[ 'class' ]	 = 'classic-grid-image';
			$image					 = wp_get_attachment_image( $image_id, 'classic-grid-listing', false, $img_class );
		} else {
			$url	 = default_product_thumbnail_url();
			$image	 = '<img src="' . $url . '" class="classic-grid-image default-image" alt="' . $product_cat->name . '" >';
		}
		$classic_grid_settings	 = get_classic_grid_settings();
		$row_class				 = get_row_class( $classic_grid_settings );
		$return					 = '<div class="archive-listing category-' . $product_cat->term_id . ' classic-grid ' . $row_class . ' ' . product_category_class( $product_cat->term_id ) . '">';
		$return					 .= '<a href="' . get_term_link( $product_cat ) . '">';
		//$return .= '<div style="background-image:url(\'' . $url . '\');" class="classic-grid-element"></div>';
		$return					 .= '<div class="classic-grid-image-wrapper"><div class="pseudo"></div><div class="image">' . $image . '</div></div>';
		$return					 .= '<h3 class="product-name">' . $product_cat->name . '</h3></a></div>';
		return $return;
	}
}

/**
 * Returns classic grid settings
 *
 * @return array
 *
  function get_classic_grid_settings() {
  $default_classic_grid_settings			 = array(
  'entries'	 => 3,
  'attributes' => 0
  );
  $classic_grid_settings					 = get_option( 'classic_grid_settings', $default_classic_grid_settings );
  $classic_grid_settings[ 'entries' ]		 = isset( $classic_grid_settings[ 'entries' ] ) ? $classic_grid_settings[ 'entries' ] : $default_classic_grid_settings[ 'entries' ];
  $classic_grid_settings[ 'attributes' ]	 = isset( $classic_grid_settings[ 'attributes' ] ) ? $classic_grid_settings[ 'attributes' ] : $default_classic_grid_settings[ 'attributes' ];
  return $classic_grid_settings;
  }
 *
 * @return array
 */
function get_classic_grid_settings() {
	$settings = wp_parse_args( get_option( 'classic_grid_settings' ), array( 'attributes' => 0, 'entries' => 3, 'per-row-categories' => 3, 'attributes_num' => 10 ) );
	return $settings;
}

add_filter( 'product_listing_additional_styles', 'classic_grid_additional_styling', 10, 2 );

/**
 * Adds classic grid inline styling for element width
 *
 * @param string $styles
 * @return string
 */
function classic_grid_additional_styling( $styles, $archive_template ) {
	if ( $archive_template == 'grid' ) {
		$grid_settings	 = get_classic_grid_settings();
		$margin			 = 1.5;
		if ( $grid_settings[ 'entries' ] != 3 ) {
			$margin	 = (($grid_settings[ 'entries' ] - 1) * 1.5) / $grid_settings[ 'entries' ];
			$width	 = number_format( 100 / $grid_settings[ 'entries' ], 2 ) - $margin - 0.01;
			$styles	 .= '.classic-grid.archive-listing{width:' . $width . '%;}';
		}
		if ( $grid_settings[ 'per-row-categories' ] != 3 ) {
			$margin	 = (($grid_settings[ 'per-row-categories' ] - 1) * 1.5) / $grid_settings[ 'per-row-categories' ];
			$width	 = number_format( 100 / $grid_settings[ 'per-row-categories' ], 2 ) - $margin - 0.01;
			$styles	 .= '.product-subcategories .classic-grid.archive-listing{width:' . $width . '%;}';
		}
	}
	return $styles;
}

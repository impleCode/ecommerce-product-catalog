<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages catalog default theme
 *
 * Here default theme is defined and managed.
 *
 * @version        1.1.4
 * @package        ecommerce-product-catalog/templates/themes
 * @author        Norbert Dreszer
 */

/**
 * Shows example modern grid in product settings
 *
 */
function example_default_archive_theme() {
	$modern_grid_settings = get_modern_grid_settings();
	?>
	<div id="content">
		<a href="#default-theme">
			<div class="al_archive"
				 style="background-image:url('<?php echo AL_PLUGIN_BASE_PATH . 'templates/themes/img/example-product.jpg'; ?>'); background-position:center; ">
				<div class="product-name">White Lamp</div>
				<?php if ( $modern_grid_settings[ 'attributes' ] == 1 ) { ?>
					<div class="product-attributes">
						<table class="attributes-table">
							<tbody>
								<tr>
									<td>Height</td>
									<td>20</td>
								</tr>
								<tr>
									<td>Color</td>
									<td>White</td>
								</tr>
							</tbody>
						</table>
					</div> <?php } ?>
				<div class="product-price">10 USD</div>
			</div>
		</a>
	</div>
	<?php
}

/*
  function default_archive_theme( $post ) {
  $modern_grid_settings	 = get_modern_grid_settings();
  ?>
  <a href="<?php the_permalink(); ?>">
  <div class="al_archive modern-grid-element" style='background-image:url(" <?php
  $thumbnail_product		 = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
  if ( $thumbnail_product[ 0 ] ) {
  $url = $thumbnail_product[ 0 ];
  } else {
  $url = default_product_thumbnail_url();
  }
  echo $url;
  ?>");'>

  <div class="product-name <?php design_schemes( 'box' ); ?>"><?php the_title(); ?></div>
  <?php
  $attributes_number	 = get_option( 'product_attributes_number', DEF_ATTRIBUTES_OPTIONS_NUMBER );
  $at_val				 = '';
  $any_attribute_value = '';
  for ( $i = 1; $i <= $attributes_number; $i++ ) {
  $at_val = get_post_meta( $post->ID, "_attribute" . $i, true );
  if ( !empty( $at_val ) ) {
  $any_attribute_value = $at_val . $i;
  }
  }
  if ( $attributes_number > 0 AND ! empty( $any_attribute_value ) AND $modern_grid_settings[ 'attributes' ] == 1 ) {
  ?>
  <div class="product-attributes">
  <table class="attributes-table">
  <?php
  for ( $i = 1; $i <= $attributes_number; $i++ ) {
  $attribute_value = get_post_meta( $post->ID, "_attribute" . $i, true );
  if ( !empty( $attribute_value ) ) {
  echo '<tr><td>' . get_post_meta( $post->ID, "_attribute-label" . $i, true ) . '</td><td>' . get_post_meta( $post->ID, "_attribute" . $i, true ) . ' ' . get_post_meta( $post->ID, "_attribute-unit" . $i, true ) . '</td></tr>';
  }
  }
  ?>
  </table>
  </div>
  <?php
  }
  do_action( 'archive_price', $post );
  ?>

  </div>
  </a>
  <?php
  }
 */

/**
 * Returns modern grid element for a given product
 *
 * @param object $post Product post object
 * @param string $archive_template
 * @return string
 */
function get_default_archive_theme( $post, $archive_template = null ) {
	$archive_template	 = isset( $archive_template ) ? $archive_template : get_product_listing_template();
	$return				 = '';
	if ( $archive_template == 'default' ) {
		$archive_price			 = apply_filters( 'archive_price_filter', '', $post );
		$modern_grid_settings	 = get_modern_grid_settings();
		$thumbnail_product		 = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'modern-grid-listing' );
		$img_class				 = '';
		if ( $thumbnail_product ) {
			$url = $thumbnail_product[ 0 ];
			if ( !empty( $thumbnail_product[ 2 ] ) ) {
				$ratio = $thumbnail_product[ 1 ] / $thumbnail_product[ 2 ];
				if ( $ratio <= 1.35 && $ratio > 1.20 ) {
					$img_class = ' class="higher"';
				} else if ( $ratio <= 1.15 ) {
					$img_class = ' class="higher rect"';
				} else if ( $ratio > 2 ) {
					$img_class = ' class="wider rect"';
				}
			}
		} else {
			$url = default_product_thumbnail_url();
		}
		$product_name		 = get_the_title();
		$return				 = '<div class="al_archive modern-grid-element ' . product_listing_size_class( $thumbnail_product ) . ' ' . product_class( $post->ID ) . '">';
		$return .= '<div class="pseudo"></div>';
		$return .= '<a href="' . get_permalink() . '"><img' . $img_class . ' src="' . $url . '" alt="' . $product_name . '">';
		$return .= '<h3 class="product-name ' . design_schemes( 'box', 0 ) . '">' . $product_name . '</h3>';
		$attributes_number	 = get_option( 'product_attributes_number', DEF_ATTRIBUTES_OPTIONS_NUMBER );
		$at_val				 = '';
		$any_attribute_value = '';
		for ( $i = 1; $i <= $attributes_number; $i++ ) {
			$at_val = get_post_meta( $post->ID, "_attribute" . $i, true );
			if ( !empty( $at_val ) ) {
				$any_attribute_value = $at_val . $i;
			}
		}
		if ( $attributes_number > 0 AND ! empty( $any_attribute_value ) AND $modern_grid_settings[ 'attributes' ] == 1 ) {
			$return .= '<div class="product-attributes"><table class="attributes-table">';
			for ( $i = 1; $i <= $attributes_number; $i++ ) {
				$attribute_value = get_post_meta( $post->ID, "_attribute" . $i, true );
				if ( !empty( $attribute_value ) ) {
					$return .= '<tr><td>' . get_post_meta( $post->ID, "_attribute-label" . $i, true ) . '</td><td>' . get_post_meta( $post->ID, "_attribute" . $i, true ) . ' ' . get_post_meta( $post->ID, "_attribute-unit" . $i, true ) . '</td></tr>';
				}
			}
			$return .= '</table></div>';
		}
		$return .= $archive_price . '</a></div>';
	}
	return $return;
}

/**
 * Returns modern grid element for a given product category
 *
 * @param object $product_cat Product category object
 * @param string $archive_template
 * @return string
 */
function get_default_category_theme( $product_cat, $archive_template ) {
	$thumbnail_product	 = wp_get_attachment_image_src( get_product_category_image_id( $product_cat->term_id ), 'modern-grid-listing' );
	$img_class			 = '';
	if ( $thumbnail_product ) {
		$url	 = $thumbnail_product[ 0 ];
		$ratio	 = $thumbnail_product[ 1 ] / $thumbnail_product[ 2 ];
		if ( $ratio <= 1.35 ) {
			$img_class = ' class="higher"';
		}
	} else {
		$url = default_product_thumbnail_url();
	}
	//$modern_grid_settings	 = get_modern_grid_settings();
	$return = '<div class="al_archive modern-grid-element ' . product_listing_size_class( $thumbnail_product ) . '">';
	//$return .= '<a class="pseudo-a" href="' . get_term_link($product_cat) . '"></a>';
	$return .= '<div class="pseudo"></div>';
	$return .= '<a href="' . get_term_link( $product_cat ) . '"><img' . $img_class . ' src="' . $url . '" alt="' . $product_cat->name . '">';
	$return .= '<h3 class="product-name ' . design_schemes( 'box', 0 ) . '">' . $product_cat->name . '</h3></a>';
	$return .= '</div>';
	return $return;
}

/**
 * Returns modern grid element class based on size ratio
 *
 * @param array $image
 * @return string
 */
function product_listing_size_class( $image ) {
	$class = '';
	if ( is_array( $image ) && $image[ 1 ] > 1.7 * $image[ 2 ] ) {
		$class = 'wider-bg';
	}
	return $class;
}

add_filter( 'product-list-class', 'add_modern_lising_class' );

/**
 * Adds per row class to modern grid product listing container
 *
 * @param string $class
 * @return string
 */
function add_modern_lising_class( $class ) {
	$archive_template = get_product_listing_template();
	if ( $archive_template == 'default' ) {
		$settings = get_modern_grid_settings();
		$class .= 'per-row-' . $settings[ 'per-row' ];
	}
	return $class;
}

add_action( 'after_setup_theme', 'default_product_listing_theme_setup' );

/**
 * Adds image size for modern grid product listing
 *
 */
function default_product_listing_theme_setup() {
	add_image_size( 'modern-grid-listing', 600, 384, true );
}

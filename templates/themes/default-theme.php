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
		$product_id				 = $post->ID;
		$archive_price			 = apply_filters( 'archive_price_filter', '', $post );
		$modern_grid_settings	 = get_modern_grid_settings();
		$image_id				 = get_post_thumbnail_id( $product_id );
		$thumbnail_product		 = wp_get_attachment_image_src( $image_id, 'modern-grid-listing' );
		$product_name			 = wp_strip_all_tags( get_the_title() );
		if ( $thumbnail_product ) {
			$url					 = $thumbnail_product[ 0 ];
			$img_class[ 'alt' ]		 = $product_name;
			$img_class[ 'class' ]	 = "modern-grid-image";
			if ( !empty( $thumbnail_product[ 2 ] ) ) {
				$ratio = $thumbnail_product[ 1 ] / $thumbnail_product[ 2 ];
				if ( $ratio <= 1.35 && $ratio > 1.20 ) {
					$img_class[ 'class' ] .= " higher";
				} else if ( $ratio <= 1.15 ) {
					$img_class[ 'class' ] .= " higher rect";
				} else if ( $ratio > 2 ) {
					$img_class[ 'class' ] .= " wider rect";
				}
			}
			$image = wp_get_attachment_image( $image_id, 'modern-grid-listing', false, $img_class );
		} else {
			$url	 = default_product_thumbnail_url();
			$image	 = '<img class="modern-grid-image" src="' . $url . '" alt="' . $product_name . '">';
		}
		$return = '<div class="al_archive product-' . $product_id . ' modern-grid-element ' . design_schemes( 'box', 0 ) . ' ' . product_listing_size_class( $thumbnail_product ) . ' ' . product_class( $product_id ) . '">';
		$return .= '<div class="pseudo"></div>';
		$return .= '<a href="' . get_permalink( $product_id ) . '">' . $image;
		$return .= '<h3 class="product-name ' . design_schemes( 'box', 0 ) . '">' . $product_name . '</h3>';
		if ( $modern_grid_settings[ 'attributes' ] == 1 && function_exists( 'product_attributes_number' ) ) {
			$attributes_number = product_attributes_number();
			if ( $attributes_number > 0 && has_product_any_attributes( $product_id ) ) {
				$max_listing_attributes	 = apply_filters( 'max_product_listing_attributes', $modern_grid_settings[ 'attributes_num' ] );
				$return .= '<div class="product-attributes"><table class="attributes-table">';
				$a						 = 0;
				for ( $i = 1; $i <= $attributes_number; $i++ ) {
					$attribute_value = get_attribute_value( $i, $product_id );
					if ( !empty( $attribute_value ) ) {
						$return .= '<tr><td class="attribute-label-listing">' . get_attribute_label( $i, $product_id ) . '</td><td><span class="attribute-value-listing">' . get_attribute_value( $i, $product_id ) . '</span> <span class="attribute-unit-listing">' . get_attribute_unit( $i, $product_id ) . '</span></td></tr>';
						$a++;
					}
					if ( $a == $max_listing_attributes ) {
						break;
					}
				}
				$return .= '</table></div>';
			}
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
	$image_id			 = get_product_category_image_id( $product_cat->term_id );
	$thumbnail_product	 = wp_get_attachment_image_src( $image_id, 'modern-grid-listing' );
	if ( $thumbnail_product ) {
		$img_class[ 'alt' ]		 = $product_cat->name;
		$img_class[ 'class' ]	 = 'modern-grid-image';
		$url					 = $thumbnail_product[ 0 ];
		if ( !empty( $thumbnail_product[ 2 ] ) ) {
			$ratio = $thumbnail_product[ 1 ] / $thumbnail_product[ 2 ];
			if ( $ratio <= 1.35 ) {
				$img_class[ 'class' ] .= ' higher';
			}
		}
		$image = wp_get_attachment_image( $image_id, 'modern-grid-listing', false, $img_class );
	} else {
		$url	 = default_product_thumbnail_url();
		$image	 = '<img class="modern-grid-image" src="' . $url . '" alt="' . $product_cat->name . '">';
	}
	//$modern_grid_settings	 = get_modern_grid_settings();
	$return = '<div class="al_archive category-' . $product_cat->term_id . ' modern-grid-element ' . design_schemes( 'box', 0 ) . ' ' . product_listing_size_class( $thumbnail_product ) . '">';
	//$return .= '<a class="pseudo-a" href="' . get_term_link($product_cat) . '"></a>';
	$return .= '<div class="pseudo"></div>';
	$return .= '<a href="' . get_term_link( $product_cat ) . '">' . $image;
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

add_filter( 'product-list-class', 'add_modern_lising_class', 10, 3 );

/**
 * Adds per row class to modern grid product listing container
 *
 * @param string $class
 * @return string
 */
function add_modern_lising_class( $class, $where = '', $archive_template = 'default' ) {
	if ( $archive_template == 'default' ) {
		$settings			 = get_modern_grid_settings();
		$shortcode_per_row	 = ic_get_global( 'shortcode_per_row' );
		if ( $shortcode_per_row ) {
			$settings[ 'per-row' ] = $shortcode_per_row;
		}
		$class .= ' per-row-' . $settings[ 'per-row' ];
	}
	ic_delete_global( 'shortcode_per_row' );
	return $class;
}

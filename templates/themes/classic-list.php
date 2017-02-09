<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages catalog classic list theme
 *
 * Here classic list theme is defined and managed.
 *
 * @version		1.2.0
 * @package		ecommerce-product-catalog/templates/themes
 * @author 		Norbert Dreszer
 */

/**
 * Shows example classic list in admin
 *
 */
function example_list_archive_theme() {
	?>
	<div class="archive-listing list example">
		<a href="#list-theme">
			<span class="div-link"></span>
		</a>
		<div class="product-image" style="background-image:url('<?php echo AL_PLUGIN_BASE_PATH . 'templates/themes/img/example-product.jpg'; ?>'); background-size: 150px; background-position: center;"></div>
		<div class="product-name">White Lamp</div>
		<div class="product-short-descr"><p>Fusce vestibulum augue ac quam tincidunt ullamcorper. Vestibulum scelerisque fermentum congue. Proin convallis dolor ac ipsum congue tincidunt. [...]</p>
		</div></div>
	<?php
}

/*
  function list_archive_theme( $post ) {
  ?>
  <div class="archive-listing list example">
  <a href="<?php the_permalink(); ?>"><span class="div-link"></span></a>
  <div class="product-image" style="background-image:url('<?php
  if ( wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) ) ) {
  $url = wp_get_attachment_url( get_post_thumbnail_id( $post->ID ) );
  } else {
  $url = default_product_thumbnail_url();
  }
  echo $url;
  ?>'); background-size: 150px; background-position: center; background-repeat: no-repeat;"></div>
  <div class="product-name"><?php the_title(); ?></div>
  <div class="product-short-descr"><p><?php echo c_list_desc( $post->ID ); ?></p></div>
  </div>
  <?php
  }
 */

function get_list_archive_theme( $post, $archive_template = null ) {
	$archive_template	 = isset( $archive_template ) ? $archive_template : get_product_listing_template();
	$return				 = '';
	if ( $archive_template == 'list' ) {
		$product_id			 = $post->ID;
		$product_name		 = get_product_name( $product_id );
		$image_id			 = get_post_thumbnail_id( $product_id );
		$thumbnail_product	 = wp_get_attachment_image_src( $image_id, 'classic-list-listing' );
		if ( $thumbnail_product ) {
			$img_class[ 'alt' ]		 = $product_name;
			$img_class[ 'class' ]	 = 'classic-list-image';
			$image					 = wp_get_attachment_image( $image_id, 'classic-list-listing', false, $img_class );
		} else {
			$url	 = default_product_thumbnail_url();
			$image	 = '<img src="' . $url . '" class="classic-list-image" alt="' . $product_name . '" >';
		}
		$return					 = '<div class="archive-listing product-' . $product_id . ' list ' . product_class( $post->ID ) . '">';
		$return					 .= '<a href="' . get_permalink() . '"><span class="div-link"></span></a>';
		$return					 .= '<div class="classic-list-image-wrapper"><div class="pseudo"></div>' . $image . '</div>';
		$return					 .= '<div class="product-name">' . $product_name . '</div>';
		$return					 .= '<div class="product-short-descr"><p>' . c_list_desc( $product_id ) . '</p></div>';
		$classic_list_settings	 = get_classic_list_settings();
		if ( $classic_list_settings[ 'attributes' ] == 1 && function_exists( 'product_attributes_number' ) ) {
			$attributes_number = product_attributes_number();
			if ( $attributes_number > 0 && has_product_any_attributes( $product_id ) ) {
				$max_listing_attributes	 = apply_filters( 'max_product_listing_attributes', $classic_list_settings[ 'attributes_num' ] );
				$return					 .= '<div class="product-attributes"><table class="attributes-table">';
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
		$return	 = apply_filters( 'classic_list_entry_bottom', $return );
		$return	 .= '</div>';
	}
	return $return;
}

function get_list_category_theme( $product_cat, $archive_template ) {
	if ( $archive_template == 'list' ) {
		$image_id	 = get_product_category_image_id( $product_cat->term_id );
		if ( $url		 = wp_get_attachment_url( $image_id, 'classic-list-listing' ) ) {
			$img_class[ 'alt' ]		 = $product_cat->name;
			$img_class[ 'class' ]	 = 'classic-list-image';
			$image					 = wp_get_attachment_image( $image_id, 'classic-list-listing', false, $img_class );
		} else {
			$url	 = default_product_thumbnail_url();
			$image	 = '<img src="' . $url . '" class="classic-list-image" alt="' . $product_cat->name . '" >';
		}
		if ( $product_cat->parent == 0 ) {
			$class = 'top-category';
		} else {
			$class = 'child-category';
		}
		$return	 = '<div class="archive-listing category-' . $product_cat->term_id . ' list ' . $class . '">';
		$return	 .= '<a href="' . get_term_link( $product_cat ) . '"><span class="div-link"></span></a>';
		$return	 .= '<div class="classic-list-image-wrapper"><div class="pseudo"></div>' . $image . '</div>';
		$return	 .= '<div class="product-name">' . $product_cat->name . '</div>';
		$return	 .= '<div class="product-short-descr"><p>' . c_list_desc( $post_id = null, $product_cat->description ) . '</p></div></div>';
		return $return;
	}
}

function get_classic_list_settings() {
	$settings = wp_parse_args( get_option( 'classic_list_settings' ), array( 'attributes' => 0, 'attributes_num' => 3 ) );
	return $settings;
}

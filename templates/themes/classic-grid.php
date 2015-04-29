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
		$thumbnail_product = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'classic-grid-listing' );
		if ( $thumbnail_product ) {
			$url = $thumbnail_product[ 0 ];
		} else {
			$url = default_product_thumbnail_url();
		}
		$archive_price			 = apply_filters( 'archive_price_filter', '', $post );
		$classic_grid_settings	 = get_classic_grid_settings();
		$row_class				 = get_row_class( $classic_grid_settings );
		$product_name			 = get_product_name();
		$return					 = '<div class="archive-listing classic-grid ' . $row_class . ' ' . product_class( $post->ID ) . '">';
		$return .= '<a href="' . get_permalink() . '">';
		//$return .= '<div style="background-image:url(\'' . $url . '\');" class="classic-grid-element"></div>';
		$return .= '<div class="classic-grid-image-wrapper"><div class="pseudo"></div><div class="image"><img src="' . $url . '" class="classic-grid-image" alt="' . $product_name . '" ></div></div>';
		$return .= '<h3 class="product-name">' . $product_name . '</h3>' . $archive_price . '</a></div>';
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
		$category_image = wp_get_attachment_image_src( get_product_category_image_id( $product_cat->term_id ), 'classic-grid-listing' );
		if ( $category_image ) {
			$url = $category_image[ 0 ];
		} else {
			$url = default_product_thumbnail_url();
		}
		$classic_grid_settings	 = get_classic_grid_settings();
		$row_class				 = get_row_class( $classic_grid_settings );
		$return					 = '<div class="archive-listing classic-grid ' . $row_class . '">';
		$return .= '<a href="' . get_term_link( $product_cat ) . '">';
		//$return .= '<div style="background-image:url(\'' . $url . '\');" class="classic-grid-element"></div>';
		$return .= '<div class="classic-grid-image-wrapper"><div class="pseudo"></div><div class="image"><img src="' . $url . '" class="classic-grid-image" alt="' . $product_cat->name . '" ></div></div>';
		$return .= '<h3 class="product-name">' . $product_cat->name . '</h3></a></div>';
		return $return;
	}
}

/**
 * Returns classic grid settings
 *
 * @return array
 */
function get_classic_grid_settings() {
	$default_classic_grid_settings	 = array(
		'entries' => 3,
	);
	$classic_grid_settings			 = get_option( 'classic_grid_settings', $default_classic_grid_settings );
	return $classic_grid_settings;
}

add_filter( 'product_listing_additional_styles', 'classic_grid_additional_styling' );

/**
 * Adds classic grid inline styling for element width
 *
 * @param string $styles
 * @return string
 */
function classic_grid_additional_styling( $styles ) {
	$archive_template = get_product_listing_template();
	if ( $archive_template == 'grid' ) {
		$grid_settings = get_classic_grid_settings();
		if ( $grid_settings[ 'entries' ] != 3 ) {
			$margin	 = (($grid_settings[ 'entries' ] - 1) * 1.5) / $grid_settings[ 'entries' ];
			$width	 = number_format( 100 / $grid_settings[ 'entries' ] ) - $margin;
			$styles .= '.classic-grid.archive-listing{width:' . $width . '%;}';
		}
	}
	return $styles;
}

add_action( 'after_setup_theme', 'classic_grid_product_listing_theme_setup' );

/**
 * Adds image size for classic grid product listing
 *
 */
function classic_grid_product_listing_theme_setup() {
	add_image_size( 'classic-grid-listing', 600, 600 );
}

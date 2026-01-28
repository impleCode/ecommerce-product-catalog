<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages catalog classic grid theme
 *
 * Here classic grid theme is defined and managed.
 *
 * @version        1.2.0
 * @package        ecommerce-product-catalog/templates/themes
 * @author        impleCode
 */

/**
 * Shows classic grid example in product settings
 *
 */
function example_grid_archive_theme() {
	?>
    <div class="archive-listing classic-grid example">
        <a href="#grid-theme">
            <div style="background-image:url('<?php echo AL_PLUGIN_BASE_PATH . 'templates/themes/img/example-product.jpg'; ?>');"
                 class="classic-grid-element"></div>
            <h3 class="product-name">White Lamp</h3>
            <div class="product-price">10 USD</div>
        </a>
    </div>
	<?php
}

/**
 * Returns classic grid element for a given product
 *
 * @param object $post Product post object
 * @param string $archive_template
 *
 * @return string
 */
function get_grid_archive_theme( $post, $archive_template = null ) {
	$archive_template = isset( $archive_template ) ? $archive_template : get_product_listing_template();
	$return           = '';
	if ( $archive_template == 'grid' ) {
		remove_all_filters( 'ic_listing_image_html' );
		add_filter( 'ic_listing_image_html', 'ic_set_classic_grid_image_html', 10, 3 );
		if ( ! has_filter( 'product-class', 'ic_classic_grid_add_row_class' ) ) {
			add_filter( 'product-class', 'ic_classic_grid_add_row_class' );
		}
		ob_start();
		ic_show_template_file( 'product-listing/classic-grid.php' );
		$return = ob_get_clean();
	}

	return $return;
}

/**
 * Returns classic grid element for given product category
 *
 * @param object $product_cat Product category object
 * @param string $archive_template
 *
 * @return string
 */
function get_grid_category_theme( $product_cat, $archive_template ) {
	if ( $archive_template == 'grid' ) {
		$product_cat = ic_set_classic_grid_category_image_html( $product_cat );
		ic_save_global( 'ic_current_product_cat', $product_cat );
		if ( ! has_filter( 'product-category-class', 'ic_classic_grid_add_row_class' ) ) {
			add_filter( 'product-category-class', 'ic_classic_grid_add_row_class' );
		}
		ob_start();
		ic_show_template_file( 'product-listing/classic-grid-category.php' );
		$return = ob_get_clean();

		return $return;
	}
}

function ic_classic_grid_add_row_class( $class ) {
	$classic_grid_settings = get_classic_grid_settings();
	if ( current_filter() === 'product-category-class' ) {
		$what = 'categories';
	} else {
		$what = 'products';
	}
	$row_class = get_row_class( $classic_grid_settings, $what );
	if ( ! empty( $row_class ) ) {
		$class .= ' ' . $row_class;
	}

	return $class;
}

function ic_set_classic_grid_image_html( $image_html, $product_id, $product ) {
	$image_id          = $product->image_id();
	$thumbnail_product = wp_get_attachment_image_src( $image_id, 'classic-grid-listing' );
	$product_name      = get_product_name( $product_id );
	if ( $thumbnail_product ) {
		$img_class['alt']   = $product_name;
		$img_class['class'] = 'classic-grid-image';
		$image_html         = wp_get_attachment_image( $image_id, 'classic-grid-listing', false, $img_class );
	} else {
		$url        = default_product_thumbnail_url();
		$image_html = '<img src="' . $url . '" class="classic-grid-image default-image" alt="' . $product_name . '" >';
	}

	return $image_html;
}

function ic_set_classic_grid_category_image_html( $product_cat ) {
	$image_id       = get_product_category_image_id( $product_cat->term_id );
	$category_image = wp_get_attachment_image_src( $image_id, 'classic-grid-listing' );
	if ( $category_image ) {
		$img_class['alt']   = $product_cat->name;
		$img_class['class'] = 'classic-grid-image';
		$image              = wp_get_attachment_image( $image_id, 'classic-grid-listing', false, $img_class );
	} else {
		$url   = default_product_thumbnail_url();
		$image = '<img src="' . $url . '" class="classic-grid-image default-image" alt="' . $product_cat->name . '" >';
	}
	//$product_cat->listing_image_html = $image;
	ic_save_global( 'ic_category_listing_image_html_' . $product_cat->term_id, $image );

	return $product_cat;
}

/**
 * Returns classic grid settings
 *
 * @return array
 */
function get_classic_grid_settings() {
	$settings = wp_parse_args( get_option( 'classic_grid_settings' ), array(
		'attributes'         => 0,
		'entries'            => 3,
		'per-row-categories' => 3,
		'attributes_num'     => 10
	) );
	$int_keys = array(
		'attributes',
		'entries',
		'per-row-categories',
		'attributes_num'
	);
	foreach ( $settings as $key => $value ) {
		if ( in_array( $key, $int_keys ) ) {
			$settings[ $key ] = intval( $value );
		}
	}

	return $settings;
}

add_filter( 'product_listing_additional_styles', 'classic_grid_additional_styling', 10, 2 );

/**
 * Adds classic grid inline styling for element width
 *
 * @param string $styles
 *
 * @return string
 */
function classic_grid_additional_styling( $styles, $archive_template ) {
	if ( $archive_template == 'grid' ) {
		$grid_settings = get_classic_grid_settings();
		$margin        = 1.5;
		if ( is_ic_shortcode_query() ) {
			$shortcode_per_row = ic_get_global( 'shortcode_per_row' );
			if ( $shortcode_per_row ) {
				$grid_settings['entries']            = intval( $shortcode_per_row );
				$grid_settings['per-row-categories'] = intval( $shortcode_per_row );
			}
		}
		if ( $grid_settings['entries'] != 3 && ! empty( $grid_settings['entries'] ) ) {
			$margin = ( ( $grid_settings['entries'] - 1 ) * 1.5 ) / $grid_settings['entries'];
			$width  = round( floatval( 100 / $grid_settings['entries'] ), 2 ) - $margin - 0.01;
			$styles .= '.product-list .classic-grid.archive-listing{width:' . $width . '%;}';
			if ( $grid_settings['entries'] > 3 ) {
				$styles .= '@media (max-width: 950px) and (min-width: 600px) {';
				$styles .= '.responsive .archive-listing.last, .responsive .archive-listing.first { clear: none;margin-right: 1.5%;}';
				$styles .= '.responsive .classic-grid.archive-listing, .responsive .classic-grid.archive-listing.last { width: 31%; }';
				$styles .= '.responsive .classic-grid.archive-listing:nth-child(3n + 1) { clear: left; }';
				$styles .= '}';
			}
		}
		if ( $grid_settings['per-row-categories'] != 3 && ! empty( $grid_settings['per-row-categories'] ) ) {
			$margin = ( ( $grid_settings['per-row-categories'] - 1 ) * 1.5 ) / $grid_settings['per-row-categories'];
			$width  = round( floatval( 100 / $grid_settings['per-row-categories'] ), 2 ) - $margin - 0.01;
			$styles .= '.product-subcategories .classic-grid.archive-listing{width:' . $width . '%;}';
			if ( $grid_settings['per-row-categories'] > 3 ) {
				$styles .= '@media (max-width: 950px) and (min-width: 600px) {';
				$styles .= '.responsive.product-subcategories .classic-grid.archive-listing { width: 31%;  }';
				$styles .= '}';
			}
		}
	}

	return $styles;
}

add_filter( 'ic_listing_template_file_paths', 'ic_add_classic_grid_path' );

function ic_add_classic_grid_path( $paths ) {
	$paths['grid'] = array( 'file' => 'product-listing/classic-grid.php', 'base' => '' );

	return $paths;
}

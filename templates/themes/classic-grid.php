<?php
/**
 * Manages catalog classic grid theme
 *
 * Here classic grid theme is defined and managed.
 *
 * @version		1.2.0
 * @package		ecommerce-product-catalog/templates/themes
 * @author 		Norbert Dreszer
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function example_grid_archive_theme() { ?>
<div class="archive-listing classic-grid example">
		<a href="#grid-theme">
		<div style="background-image:url('<?php echo AL_PLUGIN_BASE_PATH .'templates/themes/img/example-product.jpg'; ?>');" class="classic-grid-element"></div>
		<h3 class="product-name">White Lamp</h3>
		<div class="product-price">10 USD</div>
		</a>
</div>
<?php }

function grid_archive_theme($post) { ?>
<div class="archive-listing classic-grid">
		<a href="<?php the_permalink(); ?>">
		<div style="background-image:url('<?php 
			if (wp_get_attachment_url( get_post_thumbnail_id($post->ID) )) {
				$url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); 
			} 
			else {
				$url = default_product_thumbnail_url(); 
			}
			echo $url; ?>');" class="classic-grid-element"></div>
		<div class="product-name"><?php the_title(); ?></div>
		<?php do_action('archive_price', $post);  ?>
		</a>
</div>
<?php }

function get_grid_archive_theme($post, $archive_template = null) {
	$archive_template = isset($archive_template) ? $archive_template : get_product_listing_template();
	$return = '';
if ($archive_template == 'grid') {
	if (wp_get_attachment_url(get_post_thumbnail_id($post->ID))) {
		$url = wp_get_attachment_url(get_post_thumbnail_id($post->ID));
	} else {
		$url = default_product_thumbnail_url();
	}
	$archive_price = apply_filters('archive_price_filter', '', $post);
	$classic_grid_settings = get_classic_grid_settings();
	$row_class = get_row_class($classic_grid_settings);
	$return = '<div class="archive-listing classic-grid ' . $row_class . '">';
	$return .= '<a href="' . get_permalink() . '">';
	$return .= '<div style="background-image:url(\'' . $url . '\');" class="classic-grid-element"></div>';
	$return .= '<h3 class="product-name">' . get_the_title() . '</h3>' . $archive_price . '</a></div>';
}
return $return;
}

function get_grid_category_theme($product_cat, $archive_template) {
if ($archive_template == 'grid') {
if (! $url = wp_get_attachment_url( get_product_category_image_id($product_cat->term_id) )) {
	$url = default_product_thumbnail_url(); 
} 

$default_classic_grid_settings = array (
	'entries' => 3,
	);
$classic_grid_settings = get_option( 'classic_grid_settings', $default_classic_grid_settings);
$row_class = get_row_class($classic_grid_settings);
$return = '<div class="archive-listing classic-grid '.$row_class.'">';
$return .= '<a href="'.get_term_link($product_cat).'">';
$return .= '<div style="background-image:url(\''.$url.'\');" class="classic-grid-element"></div>';
$return .= '<h3 class="product-name">'.$product_cat->name.'</h3></a></div>';
return $return;
}
}

function get_classic_grid_settings() {
$default_classic_grid_settings = array (
	'entries' => 3,
);
$classic_grid_settings = get_option( 'classic_grid_settings', $default_classic_grid_settings);
return $classic_grid_settings;
}

function classic_grid_additional_styling($styles) {
$archive_template = get_product_listing_template();
if ($archive_template == 'grid') {
	$grid_settings = get_classic_grid_settings();
	if ($grid_settings['entries'] != 3) {
		$width = number_format(100 / $grid_settings['entries']) - 3;
		$styles .= '.classic-grid.archive-listing{width:'.$width.'%;}';
	}
}
return $styles;
}

add_filter('product_listing_additional_styles', 'classic_grid_additional_styling');
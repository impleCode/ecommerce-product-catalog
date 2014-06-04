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
		<div class="product-name">White Lamp</div>
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

function get_grid_archive_theme($post) {
if (wp_get_attachment_url( get_post_thumbnail_id($post->ID) )) {
	$url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); 
} 
else {
	$url = default_product_thumbnail_url(); 
}
$archive_price = apply_filters('archive_price_filter', '', $post);
$return = '<div class="archive-listing classic-grid">';
$return .= '<a href="'.get_permalink().'">';
$return .= '<div style="background-image:url(\''.$url.'\');" class="classic-grid-element"></div>';
$return .= '<div class="product-name">'.get_the_title().'</div>'.$archive_price.'</a></div>';
return $return;
}


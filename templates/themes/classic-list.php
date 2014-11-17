<?php
/**
 * Manages catalog classic list theme
 *
 * Here classic list theme is defined and managed.
 *
 * @version		1.2.0
 * @package		ecommerce-product-catalog/templates/themes
 * @author 		Norbert Dreszer
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function example_list_archive_theme() { ?>
<div class="archive-listing list example"><a href="#list-theme"><span class="div-link"></span></a><div class="product-image" style="background-image:url('<?php echo AL_PLUGIN_BASE_PATH .'templates/themes/img/example-product.jpg'; ?>'); background-size: 150px; background-position: center;"></div><div class="product-name">White Lamp</div><div class="product-short-descr"><p>Fusce vestibulum augue ac quam tincidunt ullamcorper. Vestibulum scelerisque fermentum congue. Proin convallis dolor ac ipsum congue tincidunt. [...]</p>
</div></div>
<?php }

function list_archive_theme($post) { ?>
<div class="archive-listing list example">
	<a href="<?php the_permalink(); ?>"><span class="div-link"></span></a>
	<div class="product-image" style="background-image:url('<?php 
			if (wp_get_attachment_url( get_post_thumbnail_id($post->ID) )) {
				$url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); 
			} 
			else {
				$url = default_product_thumbnail_url(); 
			}
			echo $url; ?>'); background-size: 150px; background-position: center; background-repeat: no-repeat;"></div>
	<div class="product-name"><?php the_title(); ?></div>
	<div class="product-short-descr"><p><?php 
		echo c_list_desc($post->ID); ?></p></div>
</div>
<?php }

function get_list_archive_theme($post) {
if (wp_get_attachment_url( get_post_thumbnail_id($post->ID) )) {
	$url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); 
} 
else {
	$url = default_product_thumbnail_url(); 
}
$return = '<div class="archive-listing list example">';
$return .= '<a href="'. get_permalink() .'"><span class="div-link"></span></a>';
$return .= '<div class="product-image" style="background-image:url(\''.$url.'\')"></div>';
$return .= '<div class="product-name">'. get_the_title().'</div>';
$return .= '<div class="product-short-descr"><p>'.c_list_desc($post->ID).'</p></div></div>';
return $return;
}

function get_list_category_theme($product_cat, $archive_template) {
if ($archive_template == 'list') {
if (! $url = wp_get_attachment_url( get_product_category_image_id($product_cat->term_id) )) {
	$url = default_product_thumbnail_url(); 
} 
$return = '<div class="archive-listing list example">';
$return .= '<a href="'. get_term_link($product_cat) .'"><span class="div-link"></span></a>';
$return .= '<div class="product-image" style="background-image:url(\''.$url.'\')"></div>';
$return .= '<div class="product-name">'. $product_cat->name.'</div>';
$return .= '<div class="product-short-descr"><p>'.c_list_desc($post_id = null, $product_cat->description).'</p></div></div>';
return $return;
}
}
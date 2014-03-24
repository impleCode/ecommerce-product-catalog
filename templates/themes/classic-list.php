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
<div class="archive-listing list example"><a href="#list-theme"><span class="div-link"></span></a><div class="product-image" style="background-image:url('<?php echo AL_PLUGIN_BASE_PATH .'templates/themes/img/example-product.jpg'; ?>'); background-size: 150px; background-position: center;"></div><div class="product-name">White Lamp</div><div class="product-short-descr"><p>Fusce vestibulum augue ac quam tincidunt ullamcorper. Vestibulum scelerisque fermentum congue. Proin convallis dolor ac ipsum congue tincidunt. Donec ullamcorper ipsum id risus feugiat volutpat. Curabitur cursus mattis dui sit amet scelerisque. [...]</p>
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
			echo $url; ?>'); background-size: 150px; background-position: center;"></div>
	<div class="product-name"><?php the_title(); ?></div>
	<div class="product-short-descr"><p><?php 
		$shortdesc = get_post_meta($post->ID, "_shortdesc", true);
		$shortdesc = substr($shortdesc, 0, 243) . ' [...]';
		echo $shortdesc; ?></p></div>
</div>
<?php }
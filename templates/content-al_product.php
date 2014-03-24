<?php
/**
 * The template for displaying products content.
 *
 * 
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates
 * @author 		Norbert Dreszer
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $post; 
$current_post_type = get_post_type();
$taxonomies = get_object_taxonomies($current_post_type);

$default_single_names = default_single_names();
$single_names = get_option( 'single_names', $default_single_names);
$enable_catalog_lightbox = get_option('catalog_lightbox', 1);
if ($enable_catalog_lightbox == 1) { ?>
<script>
jQuery(document).ready(function(){
				jQuery(".a-product-image").colorbox({transition: 'elastic', initialWidth: 200});
});
</script>
<?php 
do_action('single_product_begin');} 
$single_options = get_option('multi_single_options', unserialize(MULTI_SINGLE_OPTIONS));
echo product_breadcrumbs();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('al_product'); ?>>
	<header class="entry-header">
		<h1 class="entry-title product-name"><?php the_title(); ?></h1>
		<?php do_action('single_product_header', $post, $single_names); ?>
	</header>
	<div class="entry-content product-entry">
	<?php if ($single_options['enable_product_gallery'] == 1) {  ?>
		<div class="entry-thumbnail product-image">
			<?php if (has_post_thumbnail()) { 
				if ($enable_catalog_lightbox == 1) {
					$img_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'large'); ?>
					<a class="a-product-image" href="<?php echo $img_url[0];?>"><?php the_post_thumbnail('medium');?></a> <?php } 
				else {
				the_post_thumbnail('medium'); }
			} 
			else { echo default_product_thumbnail();}?>
		</div> <?php } else { $details_class = 'no-image'; } ?>
		<div class="product-details <?php echo $details_class; ?>">
			<?php do_action('product_details', $post, $single_names); ?>
		</div>
		<div class="entry-meta">
			<?php edit_post_link( __( 'Edit Product', 'al-ecommerce-product-catalog' ), '<span class="edit-link">', '</span>' ); ?>
		</div>
		<?php do_action("after_product_details", $post, $single_names); ?>
		<div class="after-product-description">
			<?php  do_action('single_product_end', $post, $single_names, $taxonomies[0]); ?>
		</div>
	
		<?php $enable_product_listing = get_option('enable_product_listing', 1);
		if ($enable_product_listing == 1) { ?>
			<a href="<?php echo product_listing_url(); ?>"><?php echo $single_names['return_to_archive']; ?></a>
		<?php } ?>
	</div>
</article>
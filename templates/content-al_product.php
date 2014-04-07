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
$details_class = '';
$default_single_names = default_single_names();
$single_names = get_option( 'single_names', $default_single_names);
$single_options = get_option('multi_single_options', unserialize(MULTI_SINGLE_OPTIONS));
do_action('single_product_begin');
echo product_breadcrumbs();
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('al_product'); ?>>
	<header class="entry-header">
		<h1 class="entry-title product-name"><?php the_title(); ?></h1>
		<?php do_action('single_product_header', $post, $single_names); ?>
	</header>
	<div class="entry-content product-entry">
	<?php 
	$enable = isset($single_options['enable_product_gallery']) ? $single_options['enable_product_gallery'] : '';
	$details_class = product_gallery_enabled($enable);
	show_product_gallery($post, $single_options);
	do_action('before_product_details'); ?>
	
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
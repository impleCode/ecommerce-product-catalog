<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template for displaying products content.
 *
 *
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates
 * @author 		Norbert Dreszer
 */
global $post;
$product_id						 = $post->ID;
$current_post_type				 = get_post_type();
$taxonomies						 = product_taxonomy_array();
$default_single_names			 = default_single_names();
$single_names					 = get_option( 'single_names', $default_single_names );
$single_names[ 'product_sku' ]	 = isset( $single_names[ 'product_sku' ] ) ? $single_names[ 'product_sku' ] : 'SKU:';
$single_options					 = get_product_page_settings();
do_action( 'single_product_begin' );
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'al_product responsive' ); ?>>
	<?php do_action( 'before_product_entry', $post, $single_names ); ?>
	<div class="entry-content product-entry"><?php
		//do_action( 'start_product_entry', $post, $single_names );
		do_action( 'before_product_details', $product_id, $single_options );
		$details_class					 = product_gallery_enabled( $single_options[ 'enable_product_gallery' ], $single_options[ 'enable_product_gallery_only_when_exist' ], $post );
		?>
		<div id="product_details" class="product-details <?php echo $details_class; ?>">
			<?php do_action( 'product_details', $post, $single_names ); ?>
		</div>
		<?php if ( current_user_can( 'edit_products' ) ) { ?>
			<div class="entry-meta">
				<?php edit_post_link( __( 'Edit Product', 'al-ecommerce-product-catalog' ), '<span class="edit-link">', '</span>' ); ?>
			</div><?php } ?>
		<div class="after-product-details">
			<?php do_action( "after_product_details", $post, $single_names ); ?>
		</div>
		<?php do_action( "after_after_product_details", $post, $single_names ); ?>
		<div class="after-product-description">
			<?php do_action( 'single_product_end', $post, $single_names, $taxonomies[ 0 ] ); ?>
		</div>
	</div>
</article>
<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product page
 *
 * Copy it to your theme implecode folder to edit the output: wp-content/themes/your-theme-folder-name/implecode/product-page.php
 *
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates
 * @author 		Norbert Dreszer
 */
global $post;
$product_id			 = $post->ID;
ic_save_global( 'product_id', $product_id );
$current_post_type	 = get_post_type();
$taxonomy			 = get_current_screen_tax();
do_action( 'single_product_begin', $product_id, $current_post_type, $taxonomy );
$single_names		 = get_single_names();
$single_options		 = get_product_page_settings();
?>

<article id="product-<?php the_ID(); ?>" <?php post_class( 'al_product responsive type-page product-' . $product_id . ' ' . $single_options[ 'template' ] ); ?> itemscope itemtype="http://schema.org/Product">
	<?php do_action( 'before_product_entry', $post, $single_names ); ?>
	<div class="entry-content product-entry">
		<div id="product_details_container">
			<?php
			do_action( 'before_product_details', $product_id, $single_options );
			$details_class		 = product_gallery_enabled( $single_options[ 'enable_product_gallery' ], $single_options[ 'enable_product_gallery_only_when_exist' ], $post );
			?>
			<div id="product_details" class="product-details <?php echo $details_class; ?>">
				<?php
				do_action( 'product_details', $post, $single_names );
				?>
			</div>
		</div>
		<?php if ( current_user_can( 'edit_products' ) ) { ?>
			<div class="entry-meta">
				<?php edit_post_link( __( 'Edit Product', 'ecommerce-product-catalog' ), '<span class="edit-link">', '</span>' ); ?>
			</div>
		<?php } ?>
		<div class="after-product-details">
			<?php do_action( "after_product_details", $post, $single_names ); ?>
		</div>
		<?php do_action( "after_after_product_details", $post, $single_names ); ?>
		<div class="after-product-description">
			<?php do_action( 'single_product_end', $post, $single_names, $taxonomy ); ?>
		</div>
	</div>
</article>
<?php
do_action( "single_product_very_end", $post, $single_names );

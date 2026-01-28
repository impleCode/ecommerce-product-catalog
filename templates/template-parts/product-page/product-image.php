<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product image on product page or with a shortcode
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/product-image.php
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates/template-parts/product-page
 * @author 		impleCode
 */
$product_id		 = ic_get_product_id();
$product_image	 = get_product_image( $product_id );
if ( !empty( $product_image ) ) {
	do_action( 'before_product_image', $product_id );
	?>
	<div class="entry-thumbnail product-image">
		<?php
		do_action( 'above_product_image', $product_id );
		if ( is_lightbox_enabled() && !is_ic_default_image( $product_id ) ) {
			?>
			<a class="a-product-image nofancybox nolightbox no-ajaxy" href="<?php echo get_product_image_url( $product_id ) ?>"><?php echo $product_image ?></a>
			<?php
		} else {
			echo $product_image;
		}
		do_action( 'below_product_image', $product_id );
		?>
	</div>
	<?php
	do_action( 'after_product_image', $product_id );
}

<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display related products on product page or with a shortcode
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/related-products.php
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates/template-parts/product-page
 * @author        impleCode
 */
$single_names = get_single_names();
$products     = ic_get_global( 'current_related_products' ); // Comma separated related products IDs
$post_type    = get_current_screen_post_type();
?>
<div class="related-products">
	<?php
	if ( ! empty( $single_names['other_categories'] ) ) {
		?>
        <h2 class="catalog-header"><?php echo $single_names['other_categories'] ?></h2>
		<?php
	}

	echo do_shortcode( '[show_products post_type="' . $post_type . '" product="' . $products . '" sort="0"]' );
	?>
</div>
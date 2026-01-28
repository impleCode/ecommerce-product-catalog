<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product description on product page or with a shortcode
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/product-description.php
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates/template-parts/product-page
 * @author        impleCode
 */
$single_names        = get_single_names();
$product_id          = ic_get_product_id();
$product_description = get_product_description( $product_id );
if ( ! empty( $product_description ) ) {
	?>
    <div id="product_description" class="product-description">
		<?php if ( ! empty( $single_names['product_description'] ) ) { ?>
            <h3 class="catalog-header"><?php echo $single_names['product_description'] ?></h3>
			<?php
		}
		if ( get_integration_type() == 'simple' && ! is_ic_shortcode_integration() ) {
			echo apply_filters( 'product_simple_description', $product_description );
		} else {
			echo apply_filters( 'the_content', $product_description );
		}
		?>
    </div>
	<?php
}
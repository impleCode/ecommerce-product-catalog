<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product price on product page or with a shortcode
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/product-price.php
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates/template-parts/product-page
 * @author 		Norbert Dreszer
 */
$product_id		 = ic_get_product_id();
$single_names	 = get_single_names();
$price			 = price_format( product_price( $product_id ) );
if ( !empty( $price ) ) {
	?>
	<div class="price-container">
		<table class="price-table">
			<tr>
				<td class="price-label"><?php echo $single_names[ 'product_price' ] ?></td>
				<td class="price-value <?php design_schemes() ?>"><?php echo $price ?></td>
			</tr>
			<?php do_action( 'price_table' ) ?>
		</table>
		<?php
		do_action( 'after_price_table' );
		?>
	</div>
	<?php
}
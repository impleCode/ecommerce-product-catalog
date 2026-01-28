<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product SKU on product page or with a shortcode
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/product-sku.php
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates/template-parts/product-page
 * @author 		impleCode
 */
$product_id		 = ic_get_product_id();
$single_names	 = get_single_names();
$sku_value		 = get_product_sku( $product_id );
if ( is_ic_sku_enabled() && !empty( $sku_value ) ) {
	?>

	<table class="sku-table">
		<tr>
			<td><?php echo $single_names[ 'product_sku' ] ?></td>
			<td class="sku-value"><?php echo $sku_value ?></td>
		</tr>
	</table>

	<?php
}
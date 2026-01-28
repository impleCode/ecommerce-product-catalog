<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product SKU on product page or with a shortcode
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/product-mpn.php
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates/template-parts/product-page
 * @author 		impleCode
 */
$product_id		 = ic_get_product_id();
$single_names	 = get_single_names();
$mpn_value		 = get_product_mpn( $product_id );
if ( is_ic_mpn_enabled() && !empty( $mpn_value ) ) {
	?>

	<table class="mpn-table">
		<tr>
			<td><?php echo $single_names[ 'product_mpn' ] ?></td>
			<td class="mpn-value"><?php echo $mpn_value ?></td>
		</tr>
	</table>

	<?php
}
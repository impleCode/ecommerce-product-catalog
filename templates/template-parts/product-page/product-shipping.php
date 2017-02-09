<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product shipping on product page or with a shortcode
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/product-shipping.php
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates/template-parts/product-page
 * @author 		Norbert Dreszer
 */
if ( function_exists( 'get_single_names' ) ) {
	$single_names = get_single_names();
}
$product_id		 = function_exists( 'ic_get_product_id' ) ? ic_get_product_id() : get_the_ID();
$shipping_values = get_shipping_options( $product_id );
if ( $shipping_values != 'none' ) {
	?>
	<div class="shipping-table-container">
		<table class="shipping-table">
			<?php if ( !empty( $single_names[ 'product_shipping' ] ) ) { ?>
				<tr>
					<td class="shipping-label"><?php echo $single_names[ 'product_shipping' ] ?></td>
				</tr>
			<?php } ?>
			<tr>
				<td>
					<ul><?php
						foreach ( $shipping_values as $i => $shipping_value ) {
							if ( $shipping_value === '' ) {
								continue;
							}
							$shipping_value = function_exists( 'price_format' ) ? price_format( $shipping_value ) : number_format( $shipping_value, 2 );
							if ( !empty( $shipping_value ) ) {
								?>
								<li><span class="shipping-label"><?php echo get_shipping_label( $i, $product_id ) ?>:</span> <span class="shipping-value"><?php echo $shipping_value ?></span></li><?php
								}
							}
							?>
					</ul>
				</td>
			</tr>
		</table>
		<?php do_action( 'after_shipping_table', $product_id ) ?>
	</div>
	<?php
}
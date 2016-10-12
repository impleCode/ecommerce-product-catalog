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
$product_id = function_exists( 'ic_get_product_id' ) ? ic_get_product_id() : get_the_ID();
if ( function_exists( 'get_single_names' ) ) {
	$single_names = get_single_names();
}
$raw_price	 = product_price( $product_id );
$price		 = price_format( $raw_price );
if ( !empty( $price ) ) {
	$class = 'price-value';
	if ( function_exists( 'design_schemes' ) ) {
		$class .= ' ' . design_schemes( null, 0 );
	}
	?>
	<div class="price-container" itemprop="offers" itemscope="" itemtype="http://schema.org/Offer">
		<meta itemprop="price" content="<?php echo $raw_price ?>">
		<meta itemprop="priceCurrency" content="<?php echo product_currency_letters() ?>">
		<link itemprop="availability" href="http://schema.org/InStock">
		<table class="price-table">
			<tr>
				<?php if ( !empty( $single_names[ 'product_price' ] ) ) { ?>
					<td class="price-label"><?php echo $single_names[ 'product_price' ] ?></td>
				<?php } ?>
				<td class="<?php echo $class ?>">
					<?php echo $price ?>
				</td>
			</tr>
			<?php do_action( 'price_table', $product_id ) ?>
		</table>
		<?php do_action( 'after_price_table', $product_id ) ?>
	</div>
	<?php
}
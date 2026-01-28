<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product price on product page or with a shortcode
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/product-price.php
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates/template-parts/product-page
 * @author        impleCode
 */
$product_id = function_exists( 'ic_get_product_id' ) ? ic_get_product_id() : get_the_ID();
do_action( 'begin_product-price.php', $product_id );
if ( function_exists( 'get_single_names' ) ) {
	$single_names = get_single_names();
}
$raw_price = product_price( $product_id );
$price     = price_format( $raw_price );
if ( ! empty( $price ) || $raw_price !== '' ) {
	$class = 'price-value price-value-' . $product_id;
	if ( function_exists( 'design_schemes' ) ) {
		$class .= ' ' . design_schemes( null, 0 );
	}
	$container_class = apply_filters( 'ic_price_container_class', 'price-container', $product_id );
	$container_attr  = apply_filters( 'ic_price_container_attr', '', $product_id );
	?>
    <div class="<?php echo $container_class ?>"<?php echo $container_attr ?>>
		<?php do_action( 'before_price_table', $product_id ) ?>
        <table class="price-table">
			<?php do_action( 'price_table_start', $product_id ) ?>
            <tr>
				<?php if ( ! empty( $single_names['product_price'] ) ) { ?>
                    <td class="price-label"><?php echo $single_names['product_price'] ?></td>
				<?php } ?>
                <td class="<?php echo $class ?>">
					<?php echo apply_filters( 'ic_product_page_price_display', $price, $product_id ) ?>
                </td>
            </tr>
			<?php do_action( 'price_table', $product_id ) ?>
        </table>
		<?php
		do_action( 'after_price_table', $product_id )
		?>
    </div>
	<?php
}
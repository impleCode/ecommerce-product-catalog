<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product listing attributes
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/listing-attributes.php
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates/template-parts/product-listing
 * @author        impleCode
 */
if ( ! function_exists( 'product_attributes_number' ) ) {
	return;
}
$product_id             = ic_get_product_id();
$attributes_number      = product_attributes_number();
$listing_attributes_num = ic_get_global( 'listing_attributes_num' );
if ( $attributes_number > 0 && has_product_any_attributes( $product_id ) ) {
	$max_listing_attributes = apply_filters( 'max_product_listing_attributes', $listing_attributes_num, $product_id );
	?>
    <div class="product-attributes">
		<?php
		$a = 0;
		for ( $i = 1; $i <= $attributes_number; $i ++ ) {
			$attribute_value = get_attribute_value( $i, $product_id );
			if ( ! empty( $attribute_value ) ) {
				?>
                <div><span class="attribute-label-listing"><?php echo get_attribute_label( $i, $product_id ) ?>:</span>
                    <span class="attribute-value-listing"><?php echo get_attribute_value( $i, $product_id ) ?></span>
                    <span class="attribute-unit-listing"><?php echo get_attribute_unit( $i, $product_id ) ?></span>
                </div>
				<?php
				$a ++;
			}
			if ( $a == $max_listing_attributes ) {
				break;
			}
		}
		?>
    </div>
	<?php
}




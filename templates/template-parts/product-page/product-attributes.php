<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product attributes on product page or with a shortcode
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/product-attributes.php
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates/template-parts/product-page
 * @author        impleCode
 */
if ( function_exists( 'get_single_names' ) ) {
	$single_names = get_single_names();
}
$product_id = function_exists( 'ic_get_product_id' ) ? ic_get_product_id() : get_the_ID();
if ( has_product_any_attributes( $product_id ) && function_exists( 'is_ic_attribute_table_visible' ) && is_ic_attribute_table_visible( $product_id ) ) {
	$attributes_number = product_attributes_number();
	if ( ! empty( $single_names['product_features'] ) ) {
		$container_id = sanitize_title( $single_names['product_features'] );
		if ( ic_string_contains( $container_id, '%' ) ) {
			$container_id = 'product_features';
		}
	} else {
		$container_id = 'product_features';
	}
	?>
    <div id="<?php echo $container_id ?>" class="product-features">
		<?php if ( ! empty( $single_names['product_features'] ) ) { ?>
            <h3 class="catalog-header"><?php echo $single_names['product_features'] ?></h3>
		<?php } ?>
        <table class="features-table">
			<?php
			for ( $i = 1; $i <= $attributes_number; $i ++ ) {
				$attribute_value = get_attribute_value( $i, $product_id );
				if ( ! empty( $attribute_value ) ) {
					$label         = get_attribute_label( $i, $product_id );
					$display_value = apply_filters( 'ic_catalog_attr_val_display', $attribute_value, $label, $i, $product_id );
					$unit          = get_attribute_unit( $i, $product_id );
					?>
                    <tr>
                        <td class="attribute-label-single <?php echo sanitize_title( $label . '-label' ) ?>"><?php echo $label ?></td>
                        <td class="attribute-value-unit-single"><span
                                    class="attribute-value-single <?php echo sanitize_title( $label . '-value' ) ?> <?php echo sanitize_title( $display_value ) ?>"><?php echo $display_value ?></span>
                            <span
                                    class="attribute-unit-single <?php echo sanitize_title( $label . '-unit' ) ?> <?php echo sanitize_title( $unit ) ?>"><?php echo $unit ?></span>
                        </td>
                    </tr>
					<?php
				}
			}
			do_action( 'ic_attributes_table', $product_id );
			?>
        </table>
    </div>
	<?php
}
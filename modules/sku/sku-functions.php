<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
add_action( 'product_details', 'show_sku', 8, 1 );

/**
 * Shows product SKU table
 *
 * @param object $post
 * @param array $single_names
 */
function show_sku( $product_id = false ) {
	if ( is_object( $product_id ) && isset( $product_id->ID ) ) {
		$product_id = $product_id->ID;
	}
	ic_show_template_file( 'product-page/product-sku.php', AL_BASE_TEMPLATES_PATH, $product_id );
}

/**
 * Returns sku table for product page
 *
 * @param int $product_id
 * @param array $single_names
 *
 * @return string
 */
function get_product_sku_table( $product_id ) {
	ob_start();
	show_sku( $product_id );

	return ob_get_clean();
}

/**
 * Returns SKU
 *
 * @param int $product_id
 *
 * @return string
 */
function get_product_sku( $product_id = null ) {
	if ( empty( $product_id ) ) {
		$product_id = ic_get_product_id();
	}
	if ( empty( $product_id ) ) {
		return;
	}
	$sku = get_post_meta( $product_id, '_sku', true );

	return apply_filters( 'ic_get_product_sku', $sku, $product_id );
}

add_action( 'ic_structured_data', 'ic_sku_structured_data' );

function ic_sku_structured_data( $product_id ) {
	if ( ! is_ic_sku_enabled() ) {
		$sku = $product_id;
	} else {
		$sku = get_product_sku( $product_id );
		if ( empty( $sku ) ) {
			$sku = $product_id;
		}
	}
	?>
    "sku": "<?php echo $sku ?>",
	<?php
}

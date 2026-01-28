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
add_action( 'product_details', 'show_mpn', 8, 1 );

/**
 * Shows product mpn table
 *
 * @param object $post
 * @param array $single_names
 */
function show_mpn( $product_id = false ) {
	if ( is_object( $product_id ) && isset( $product_id->ID ) ) {
		$product_id = $product_id->ID;
	}
	ic_show_template_file( 'product-page/product-mpn.php', AL_BASE_TEMPLATES_PATH, $product_id );
}

/**
 * Returns mpn table for product page
 *
 * @param int $product_id
 * @param array $single_names
 *
 * @return string
 */
function get_product_mpn_table( $product_id, $single_names ) {
	ob_start();
	show_mpn( $product_id );

	return ob_get_clean();
}

/**
 * Returns mpn
 *
 * @param int $product_id
 *
 * @return string
 */
function get_product_mpn( $product_id ) {
	$mpn = get_post_meta( $product_id, '_mpn', true );
	if ( empty( $mpn ) ) {
		$mpn = '';
	}

	return $mpn;
}

add_action( 'ic_structured_data', 'ic_mpn_structured_data' );

function ic_mpn_structured_data( $product_id ) {
	if ( ! is_ic_mpn_enabled() ) {
		$mpn = $product_id;
	} else {
		$mpn = get_product_mpn( $product_id );
		if ( empty( $mpn ) ) {
			$mpn = $product_id;
		}
	}
	?>
    "mpn": "<?php echo $mpn ?>",
	<?php
}

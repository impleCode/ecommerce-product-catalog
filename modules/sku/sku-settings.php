<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		impleCode
 */
add_action( 'single_names_table_start', 'ic_sku_single_names' );

/**
 * Shows sku product page labels settings
 *
 * @param type $single_names
 */
function ic_sku_single_names( $single_names ) {
	implecode_settings_text( __( 'SKU Label', 'ecommerce-product-catalog' ), 'single_names[product_sku]', $single_names[ 'product_sku' ] );
}

add_action( 'general-settings', 'ic_sku_settings' );

/**
 * Shows price settings
 *
 */
function ic_sku_settings( $archive_multiple_settings ) {
	?>
	<h3><?php _e( 'Additional Settings', 'ecommerce-product-catalog' ); ?></h3>
	<table>
		<?php
		implecode_settings_checkbox( __( 'Disable SKU', 'ecommerce-product-catalog' ), 'archive_multiple_settings[disable_sku]', $archive_multiple_settings[ 'disable_sku' ] );
		do_action( "ic_epc_additional_settings", $archive_multiple_settings );
		?>
	</table>
	<?php
}

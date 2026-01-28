<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *
 *  @version       1.0.0
 *  @author        impleCode
 *
 */

add_action( 'init', 'ic_price_field_init_blocks' );

function ic_price_field_init_blocks() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return;
	}
	$dirname = dirname( __FILE__ );
	require_once( $dirname . '/functions.php' );
	require_once( $dirname . '/price-table/index.php' );
	require_once( $dirname . '/price-filter/index.php' );
	do_action( 'ic_init_price_blocks' );
}

if ( ! class_exists( 'ic_epc_blocks' ) ) {
	add_action( 'admin_print_scripts', 'ic_blocks_js_global' );

	function ic_blocks_js_global() {
		$js_global = apply_filters( 'ic_epc_blocks_localize', array(
			'strings' => array(
				'select_shortcode_support' => __( 'Enable also for shortcodes', 'ecommerce-product-catalog' ),
				'select_title'             => __( 'Title', 'ecommerce-product-catalog' ),
				'settings'                 => __( 'Settings', 'ecommerce-product-catalog' )
			)
		) );
		?>
        <script>
            var ic_blocks_js_global = <?php echo wp_json_encode( $js_global ) ?>;
        </script>
		<?php
	}

}

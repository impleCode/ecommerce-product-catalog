<?php
/**
 * Plugin compatibility checker
 *
 * Here current theme is checked for compatibility with WP PRODUCT ADDER.
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
 
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function al_product_adder_admin_notices_styles() {

$template = get_option( 'template' );

	if ( ! current_theme_supports( 'wp_product_adder' ) && ! in_array( $template, array( 'twentythirteen', 'twentyeleven', 'twentytwelve', 'twentyten', 'twentyfourteen' ) ) ) {
	
	if ( ! empty( $_GET['hide_al_product_adder_support_check'] ) ) {
			update_option( 'product_adder_theme_support_check', $template );
			return;
		}
if ( get_option( 'product_adder_theme_support_check' ) !== $template && current_user_can('administrator')) {		
	add_action( 'admin_notices', 'product_adder_theme_check_notice' ); }
	
	}
	
}	
add_action( 'admin_print_styles', 'al_product_adder_admin_notices_styles' );	

function product_adder_theme_check_notice() { ?>
<div id="message" class="updated product-adder-message messages-connect">
	<div class="squeezer">
		<h4><?php _e( '<strong>Your theme does not declare eCommerce Product Catalog</strong> &#8211; if you encounter layout issues please read our integration guide or choose a recomended theme :)', 'al-ecommerce-product-catalog' ); ?></h4>
		<p class="submit"><a href="http://implecode.com/wordpress/product-catalog/theme-integration-guide/" target="_blank" class="button-primary"><?php _e( 'Theme Integration Guide', 'al-ecommerce-product-catalog' ); ?></a> <a class="skip button-primary" href="<?php echo add_query_arg( 'hide_al_product_adder_support_check', 'true' ); ?>"><?php _e( 'Hide this notice', 'al-ecommerce-product-catalog' ); ?></a></p>
	</div>
</div> 
<?php }


?>
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
if (! is_advanced_mode_forced() ) {
	$template = get_option( 'template' );
	$integration_type = get_integration_type();
	if ( ! empty( $_GET['hide_al_product_adder_support_check'] ) ) {
		update_option( 'product_adder_theme_support_check', $template );
		return;
	}
	if ( get_option( 'product_adder_theme_support_check' ) !== $template && current_user_can('administrator')) {		
		add_action( 'admin_notices', 'product_adder_theme_check_notice' ); 
	}
}
}	
add_action( 'admin_print_styles', 'al_product_adder_admin_notices_styles' );	

function product_adder_theme_check_notice() { 
if (is_integration_mode_selected() && get_integration_type() == 'simple') { ?>
<div id="message" class="updated product-adder-message messages-connect">
	<div class="squeezer">
		<h4><?php _e( '<strong>You are currently using eCommerce Product Catalog in Simple Mode</strong> &#8211; to switch to Advanced Mode you probably need Theme Integration.', 'al-ecommerce-product-catalog' ); ?></h4>
		<p class="submit"><a href="http://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=catalog-settings-link&key=integration-link-simple" target="_blank" class="button-primary"><?php _e( 'Theme Integration Guide', 'al-ecommerce-product-catalog' ); ?></a> <a class="skip button" href="edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=support"><?php _e( 'Plugin Support', 'al-ecommerce-product-catalog' ); ?></a><a class="skip button" href="<?php echo add_query_arg( 'hide_al_product_adder_support_check', 'true' ); ?>"><?php _e( 'I know, don\'t bug me', 'al-ecommerce-product-catalog' ); ?></a></p>
	</div>
</div><?php
}
else if (is_integration_mode_selected() && get_integration_type() == 'advanced') { ?>
<div id="message" class="updated product-adder-message messages-connect">
	<div class="squeezer">
		<h4><?php _e( '<strong>You are currently using eCommerce Product Catalog in Advanced Mode without the integration file</strong> &#8211; please see the guide for quick integration file creation.', 'al-ecommerce-product-catalog' ); ?></h4>
		<p class="submit"><a href="http://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=catalog-settings-link&key=integration-link-advanced" target="_blank" class="button-primary"><?php _e( 'Theme Integration Guide', 'al-ecommerce-product-catalog' ); ?></a> <a class="skip button" href="edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=support"><?php _e( 'Plugin Support', 'al-ecommerce-product-catalog' ); ?></a><a class="skip button" href="<?php echo add_query_arg( 'hide_al_product_adder_support_check', 'true' ); ?>"><?php _e( 'I know, don\'t bug me', 'al-ecommerce-product-catalog' ); ?></a></p>
	</div>
</div><?php
}
else { 
$product_id = sample_product_id();
$sample_product_url = get_permalink($product_id); 
if (! $sample_product_url || get_post_status( $product_id ) != 'publish') {
$sample_product_url = add_query_arg( 'create_sample_product_page', 'true' );
} ?>
<div id="message" class="error product-adder-message messages-connect">
	<div class="squeezer">
		<h4><?php _e( '<strong>Your theme does not declare eCommerce Product Catalog support</strong> &#8211; please proceed to sample product page where automatic layout adjustment can be done.', 'al-ecommerce-product-catalog' ); ?></h4>
		<p class="submit"><a href="<?php echo $sample_product_url ?>" class="button-primary"><?php _e( 'Sample Product Page', 'al-ecommerce-product-catalog' ); ?></a><a href="http://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=catalog-settings-link&key=integration-link" target="_blank" class="button"><?php _e( 'Theme Integration Guide', 'al-ecommerce-product-catalog' ); ?></a> <a class="skip button" href="edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=support"><?php _e( 'Plugin Support', 'al-ecommerce-product-catalog' ); ?></a><a class="skip button" href="<?php echo add_query_arg( 'hide_al_product_adder_support_check', 'true' ); ?>"><?php _e( 'Hide this notice', 'al-ecommerce-product-catalog' ); ?></a></p>
	</div>
</div><?php
}
}

function implecode_product_catalog_links( $links ) {
$links[] = '<a href="'. get_admin_url(null, 'edit.php?post_type=al_product&page=product-settings.php') .'">Settings</a>';
$links[] = '<a href="http://implecode.com/wordpress/plugins/premium-support/#cam=catalog-settings-link&key=support-link" target="_blank">Premium Support</a>';
return array_reverse ($links);
}

add_filter( 'plugin_action_links_'.plugin_basename(AL_PLUGIN_MAIN_FILE), 'implecode_product_catalog_links' );

?>
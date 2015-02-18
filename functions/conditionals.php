<?php 
/**
 * Manages product conditional functions
 *
 * Here all plugin conditional functions are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
function is_ic_product_page() {
if (is_singular(product_post_type_array())) {
return true;
}
return false;
}

function is_ic_admin_page() {
$screen = get_current_screen();
if (is_ic_catalog_admin_page() || isset($_GET['page']) && $_GET['page'] == 'implecode-settings') {
	return true;
}
return false;
}

function is_ic_catalog_admin_page() {
    $screen = get_current_screen();
    if ($screen->id == 'al_product_page_product-settings' || $screen->id == 'al_product' || $screen->id == 'edit-al_product' || $screen->id == 'edit-al_product-cat' || $screen->id == 'al_product_page_extensions') {
        return true;
    }
    return false;
}

function is_ic_price_enabled() {
    $product_currency = get_currency_settings();
    if ($product_currency['price_enable'] == 'on') {
        return true;
    }
    return false;
}
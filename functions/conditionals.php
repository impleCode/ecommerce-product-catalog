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

function is_ic_catalog_page() {
    if (is_ic_product_page() || is_ic_product_listing()) {
        return true;
    }
    return false;
}

function is_ic_product_listing() {
    if (is_post_type_archive(product_post_type_array())) {
        return true;
    }
    return false;
}

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
    if (ic_string_contains($screen->id, 'al_product')) {
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

function is_ic_sku_enabled() {
    $archive_multiple_settings = get_multiple_settings();
    if ($archive_multiple_settings['disable_sku'] != 1) {
        return true;
    }
    return false;
}

function ic_string_contains($string, $contains) {
    if (strpos($string,$contains) !== false) {
        return true;
    }
    return false;
}
<?php
/**
 * Manages functions necessary on plugin activation.
 *
 *
 * @version		1.1.3
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */

function create_products_page() {
$product_page = array(
	'post_title' => __('Products', 'al-ecommerce-product-catalog'),
	'post_type' => 'page',
	'post_content' => '',
	'post_status' => 'publish',
	'comment_status' => 'closed'
);

$plugin_data = get_plugin_data(AL_PLUGIN_MAIN_FILE); 
$plugin_version = $plugin_data["Version"];
$first_version = get_option('first_activation_version', '1.0');

if ($first_version == '1.0') {
add_option('first_activation_version', $plugin_version); 

$post_id = wp_insert_post($product_page);
add_option('product_archive_page_id', $post_id); }
}

?>
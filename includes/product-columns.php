<?php 
/**
 * Manages licenses columns
 *
 * Here all license columns defined and managed.
 *
 * @version		1.0.0
 * @package		digital-products-licenses/includes
 * @author 		Norbert Dreszer
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_filter('manage_edit-al_product_columns', 'add_product_columns');

function add_product_columns($product_columns) { 
    $new_columns['cb'] = $product_columns['cb'];
	$new_columns['id'] = __('ID', 'al-ecommerce-product-catalog');
	$new_columns['title'] = __('Product Name', 'al-ecommerce-product-catalog');
	$new_columns['shortcode'] = __('Shortcode', 'al-ecommerce-product-catalog');
	$new_columns['taxonomy-al_product-cat'] = __('Product Categories', 'al-ecommerce-product-catalog');
	$new_columns['date'] = __('Date', 'al-ecommerce-product-catalog');
    return $new_columns;
}

add_action('manage_al_product_posts_custom_column', 'manage_product_columns', 10, 2);
 
function manage_product_columns($column_name, $product_id) {
switch ($column_name) {
	case 'id':
		echo $product_id;
	break;
	case 'shortcode':
		echo '[show_products product="'.$product_id.'"]';
	break;
	
	default:
	break;
}
}
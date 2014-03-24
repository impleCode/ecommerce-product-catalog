<?php
/**
 * Manages plugin shortcodes
 *
 * Here all shortcodes are defined.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function parent_cat_list($atts) {
$output = wp_list_categories('title_li=&orderby=name&depth=1&taxonomy=al_product-cat&echo=0');
return $output;
}

add_shortcode('display_product_categories', 'parent_cat_list');


?>
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

function product_cat_shortcode($atts) {
global $cat_shortcode_query;
$cat_shortcode_query = array();
$cat_shortcode_query['current'] = 0;
extract(shortcode_atts(array( 
		'exclude' => array(),
		'include' => array(),
		'archive_template' => get_option( 'archive_template', 'default'),
		'parent' => '',
), $atts));
$inside = '';
$args = array(
'exclude' => $exclude,
'include' => $include,
'parent' => $parent,
); 
$cats = get_terms('al_product-cat', $args); 
$cat_shortcode_query['count'] = count($cats);
foreach ($cats as $cat) {
$inside .= get_product_category_template($archive_template, $cat);
$cat_shortcode_query['current']++;
}
reset_row_class();
return $inside;
}
add_shortcode('show_categories', 'product_cat_shortcode');

?>
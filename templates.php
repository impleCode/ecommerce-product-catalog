<?php 
/**
 * WP Product template manager
 *
 * Here all plugin templates are defined.
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/
 * @author 		Norbert Dreszer
 */
 
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
require_once(  AL_BASE_PATH . '/templates/templates-functions.php' );
$theme = get_option('template');
$woothemes = array("canvas", "woo", "al");
$nosidebar = array("twentyeleven");
$twentyten = array("twentyten");

if (file_exists(get_theme_root() . '/'. get_template() . '/product-adder.php')) {
	
	 add_filter( 'template_include', 'al_product_adder_template' ); 

	}
	
else if (in_array( $theme, $woothemes )) {
add_filter( 'template_include', 'al_product_adder_woo_template' ); }

else if (in_array( $theme, $nosidebar )) {
add_filter( 'template_include', 'al_product_adder_nosidebar_template' ); }

else if (in_array( $theme, $twentyten )) {
add_filter( 'template_include', 'al_product_adder_twentyten_template' ); }
	
else {
add_filter( 'template_include', 'al_product_adder_custom_template' );
}
	
	function al_product_adder_template($template) {
	if ( 'al_product' == get_quasi_post_type()) {
	    return get_theme_root() . '/'. get_template() . '/product-adder.php'; }

    return $template; }
	
	function al_product_adder_custom_template($template) {
	if ( 'al_product' == get_quasi_post_type()) {
	    return dirname( __FILE__ ) . '/templates/product-adder.php'; }

    return $template; }
	
	// templates from woothemes
	function al_product_adder_woo_template($template) {
	if ( 'al_product' == get_quasi_post_type()) {
	    return dirname( __FILE__ ) . '/templates/product-woo-adder.php'; }

    return $template; }
	
	// no sidebar on content page
	function al_product_adder_nosidebar_template($template) {
	if ( 'al_product' == get_quasi_post_type()) {
	    return dirname( __FILE__ ) . '/templates/product-nosidebar-adder.php'; }

    return $template; }
	
		// twentyten - primary replaced by container id
	function al_product_adder_twentyten_template($template) {
	if ( 'al_product' == get_quasi_post_type()) {
	    return dirname( __FILE__ ) . '/templates/product-twentyten-adder.php'; }

    return $template; }


?>
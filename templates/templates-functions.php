<?php
/**
 * WP Product template functions
 *
 * Here all plugin template functions are defined.
 *
 * @version		1.1.3
 * @package		ecommerce-product-catalog/
 * @author 		Norbert Dreszer
 */
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
function content_product_adder() {
if (is_archive() || is_search()) {
do_action('before_product_archive');
content_product_adder_archive();
}
else {
content_product_adder_single();
}
}

function content_product_adder_archive() {
include 'content-al_product_archive.php';
}

function content_product_adder_single() {
include 'content-al_product.php';
}

function content_product_adder_archive_before() {
$page_id = apply_filters('before_archive_post_id', get_option('product_archive'));
$page = get_post($page_id);
if ($page != '') {
	if (get_integration_type() != 'simple') { 
		$content = apply_filters ("the_content", $page->post_content);
	}
	else {
		$content = $page->post_content;
	}
}
else {
$content = '';
}
return '<div class="entry-summary">'.$content.'</div>';
}

function content_product_adder_archive_before_title() {
$def_page_id = get_option('product_archive');
$archive_names = get_option('archive_names');
$page_id = apply_filters('before_archive_post_id', $def_page_id);
$page = get_post($page_id);
if ($page == '') {
echo '<h1 class="entry-title">'.$archive_names['all_products'].'</h1>';
}
else {
echo '<h1 class="entry-title">'.$page->post_title.'</h1>';
}
}

function show_products_outside_loop($atts) {
global $shortcode_query, $product_sort;
extract(shortcode_atts(array( 
		'post_type' => 'al_product',
		'category' => '',
		'product' => '',
		'products_limit' => -1,
		'archive_template' => get_option( 'archive_template', 'default'),
		'design_scheme' => '',
		'sort' => 0,
    ), $atts));
$product_sort = $sort;
if ($product != 0) {
	$product_array = explode(',', $product);
	$query_param = array (
		'post_type' => 'al_product',
		'post__in' => $product_array,
		'posts_per_page' => $products_limit,
		);
}
else if ($category != 0) {
	$category_array = explode(',', $category);
	$query_param = array (
		'post_type' => 'al_product',
		'tax_query' => array(
			array(
				'taxonomy' => 'al_product-cat',
				'field' => 'term_id',
				'terms' => $category_array,
			),
		),
		'posts_per_page' => $products_limit,
		);
}
else {
	$product_array = explode(',', $product);
	$query_param = array (
		'post_type' => 'al_product',
		'posts_per_page' => $products_limit,
		);
}
$query_param = apply_filters('shortcode_query', $query_param);
$shortcode_query = new WP_Query($query_param);
$inside = '';
$i = 0;
do_action('before_product_list', $archive_template);
while ( $shortcode_query->have_posts() ) : $shortcode_query->the_post(); global $post; $i++;
	$inside .= get_catalog_template($archive_template, $post, $i, $design_scheme);
endwhile;
$inside = apply_filters('product_list_ready', $inside, $archive_template);
wp_reset_postdata();
return '<div class="product-list '.$archive_template.'-list">'.$inside.'<div style="clear:both"></div></div>';
}

add_shortcode('show_products', 'show_products_outside_loop');

function single_scripts(){
if (is_lightbox_enabled()) {
wp_enqueue_script('colorbox');
wp_enqueue_style('colorbox');
}}
add_action( 'wp_enqueue_scripts', 'single_scripts' );


add_action( 'pre_get_posts', 'set_products_limit' );
 
function set_products_limit( $query ) {
$archive_multiple_settings = get_option('archive_multiple_settings', unserialize (DEFAULT_ARCHIVE_MULTIPLE_SETTINGS));
if ( ! is_admin() && is_post_type_archive( 'al_product' ) || is_tax('al_product-cat') ) {
	$query->set( 'posts_per_page', $archive_multiple_settings['archive_products_limit'] );
}
}

function product_archive_pagination() {
if( is_singular() )
	return;
global $wp_query;
if( $wp_query->max_num_pages <= 1 )
	return;
$paged = get_query_var( 'paged' ) ? absint( get_query_var( 'paged' ) ) : 1;
$max   = intval( $wp_query->max_num_pages );
if ( $paged >= 1 )
	$links[] = $paged;
if ( $paged >= 3 ) {
	$links[] = $paged - 1;
	$links[] = $paged - 2;
}
if ( ( $paged + 2 ) <= $max ) {
	$links[] = $paged + 2;
	$links[] = $paged + 1;
}
echo '<div id="product_archive_nav" class="product-archive-nav '. design_schemes('box',0) .'"><ul>' . "\n";
if ( get_previous_posts_link() )
	printf( '<li>%s</li>' . "\n", get_previous_posts_link() );
if ( ! in_array( 1, $links ) ) {
	$class = 1 == $paged ? ' class="active"' : '';
printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ), '1' );
if ( ! in_array( 2, $links ) )
	echo '<li>…</li>';
}
sort( $links );
foreach ( (array) $links as $link ) {
	$class = $paged == $link ? ' class="active"' : '';
	printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ), $link );
}
if ( ! in_array( $max, $links ) ) {
	if ( ! in_array( $max - 1, $links ) )
		echo '<li>…</li>' . "\n";
		$class = $paged == $max ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ), $max );
}
if ( get_next_posts_link() ) {
	printf( '<li>%s</li>' . "\n", get_next_posts_link() ); }
	echo '</ul></div>' . "\n";
	
wp_reset_postdata();
}

function get_catalog_template($archive_template, $post, $i = null, $design_scheme = null) {
$themes_array = apply_filters('ecommerce_catalog_templates', array(
	'default' => get_default_archive_theme($post),
	'list' => get_list_archive_theme($post),
	'grid' => get_grid_archive_theme($post),
), $post, $i, $design_scheme);
$themes_array[$archive_template] = isset($themes_array[$archive_template]) ? $themes_array[$archive_template] : $themes_array['default'];
return $themes_array[$archive_template];
}

function get_product_category_template($archive_template, $product_cat, $i = null, $design_scheme = null) {
$themes_array = apply_filters('ecommerce_category_templates', array(
	'default' => get_default_category_theme($product_cat, $archive_template),
	'list' => get_list_category_theme($product_cat, $archive_template),
	'grid' => get_grid_category_theme($product_cat, $archive_template),
), $product_cat, $i, $design_scheme);
$themes_array[$archive_template] = isset($themes_array[$archive_template]) ? $themes_array[$archive_template] : $themes_array['default'];
return $themes_array[$archive_template];
}

function more_products() {
global $wp_query, $shortcode_query;
$post_type = apply_filters('current_product_post_type', 'al_product');
$taxonomy = apply_filters('current_product_catalog_taxonomy', 'al_product-cat');
if((isset($wp_query->query['post_type']) && $wp_query->query['post_type'] == $post_type) || isset($wp_query->query[$taxonomy])) {
	$y_query = $wp_query;
}
else {
	$y_query = $shortcode_query;
}
if (isset($y_query->current_post)) {
return $y_query->current_post + 1 < $y_query->post_count;
}
else {
return false;
}
}

function more_product_cats() {
global $cat_shortcode_query;
if (isset($cat_shortcode_query['current'])) {
$result = $cat_shortcode_query['current'] + 1 < $cat_shortcode_query['count'];
return $result;
}
else {
return false;
}
}


function get_row_class($grid_settings) {
$row_class = 'full';
if ($grid_settings['entries'] != '') {
global $row;
if ($row > $grid_settings['entries'] || !isset($row)) {$row = 1; }
$count = $row - $grid_settings['entries'];
if ($row == 1) {
$row_class = 'first';
}
else if ($count == 0) {
$row_class = 'last';
}
else {
$row_class = 'middle';
}
if (more_products() || more_product_cats()) {
$row++; }
else {
$row = 1;
}
}
return $row_class;
}

function reset_row_class() {
global $row;
$row = 1;
}

function product_class( $classes ) {
	global $post;
	if(($key = array_search('has-post-thumbnail', $classes)) !== false) {
    unset($classes[$key]);
	}
	return $classes;
}
add_filter( 'post_class', 'product_class' );
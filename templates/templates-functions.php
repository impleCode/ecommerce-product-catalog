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
$page_id = get_option('product_archive');
$page = get_post($page_id);
$content = apply_filters ("the_content", $page->post_content);
return '<div class="entry-summary">'.$content.'</div>';
}

function content_product_adder_archive_before_title() {
$page_id = get_option('product_archive');
$page = get_post($page_id);
echo '<h1 class="entry-title">'.$page->post_title.'</h1>';
}

function show_products_outside_loop($atts) {
extract(shortcode_atts(array( 
		'post_type' => 'al_product',
		'category' => '',
		'product' => '',
		'products_limit' => -1,
    ), $atts));

if ($product != 0) {
	$product_array = explode(',', $product);
	$query = new WP_Query( array (
		'post_type' => 'al_product',
		'post__in' => $product_array,
		'posts_per_page' => $products_limit,
		));
}
else if ($category != 0) {
	$category_array = explode(',', $category);
	$query = new WP_Query( array (
		'post_type' => 'al_product',
		'tax_query' => array(
			array(
				'taxonomy' => 'al_product-cat',
				'field' => 'term_id',
				'terms' => $category_array,
			),
		),
		'posts_per_page' => $products_limit,
		));
}
else {
	$product_array = explode(',', $product);
	$query = new WP_Query( array (
		'post_type' => 'al_product',
		'posts_per_page' => $products_limit,
		));
}
$inside = '';
$archive_template = get_option( 'archive_template', 'default');
ob_start();
if ($archive_template == 'default') {
	while ( $query->have_posts() ) : $query->the_post(); global $post;
		$inside .= default_archive_theme($post);
	endwhile;
	wp_reset_postdata();
}
else if ($archive_template == 'list') {
	while ( $query->have_posts() ) : $query->the_post(); global $post;
		$inside .= list_archive_theme($post);
	endwhile; wp_reset_postdata();
}
else {
	while ( $query->have_posts() ) : $query->the_post(); global $post;
		$inside .= grid_archive_theme($post);
	endwhile; wp_reset_postdata();
}
$output = ob_get_contents();
ob_end_clean();
return '<div class="product-list">'.$output.'</div>';
}

add_shortcode('show_products', 'show_products_outside_loop');

function single_scripts(){
$enable_catalog_lightbox = get_option('catalog_lightbox', 1);
if ($enable_catalog_lightbox == 1) {
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
printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( 1 ) ).'#product_archive_nav', '1' );
if ( ! in_array( 2, $links ) )
	echo '<li>…</li>';
}
sort( $links );
foreach ( (array) $links as $link ) {
	$class = $paged == $link ? ' class="active"' : '';
	printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $link ) ).'#product_archive_nav', $link );
}
if ( ! in_array( $max, $links ) ) {
	if ( ! in_array( $max - 1, $links ) )
		echo '<li>…</li>' . "\n";
		$class = $paged == $max ? ' class="active"' : '';
		printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( get_pagenum_link( $max ) ).'#product_archive_nav', $max );
}
if ( get_next_posts_link() )
	printf( '<li>%s</li>' . "\n", get_next_posts_link() );
	echo '</ul></div>' . "\n";
}

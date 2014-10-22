<?php 
/**
 * Manages product functions
 *
 * Here all plugin functions are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 
function default_product_thumbnail() {
 if (get_option('default_product_thumbnail')) {
 $url = get_option('default_product_thumbnail');
 }
 else {
 $product_id = get_the_ID();
 if ($product_id == sample_product_id()) {
	$url = AL_PLUGIN_BASE_PATH .'img/implecode.jpg';
 }
 else {
	$url = AL_PLUGIN_BASE_PATH .'img/no-default-thumbnail.png';
 }
 }

return '<img src="'.$url.'"  />';
 }

function default_product_thumbnail_url() {
 if (get_option('default_product_thumbnail')) {
 $url = get_option('default_product_thumbnail');
 }
 else {
 $url = AL_PLUGIN_BASE_PATH .'img/no-default-thumbnail.png';
 }

return $url;
 }
 
function product_listing_url() {
	if ( get_option('permalink_structure') ) { 
			$listing_url = site_url(). '/' .get_option('product_listing_url', __('products', 'al-ecommerce-product-catalog')). '/'; }
	else {
			$listing_url = get_post_type_archive_link( 'al_product' ); }
	return $listing_url;
}
function upload_product_image($name, $button_value, $option_name, $option_value = null, $default_image = null) { 
wp_enqueue_media(); 
if (empty($option_value)) { $option_value = get_option($option_name); }
if (empty($default_image)) {$default_image = AL_PLUGIN_BASE_PATH .'img/no-default-thumbnail.png';}
if ($option_value) {
$src = $option_value;}
else {$src = $default_image; } ?>
 <div class="custom-uploader">
 <input hidden="hidden" type="text" id="default" value="<?php echo $default_image; ?>" />
  <input hidden="hidden" type="text" name="<?php echo $option_name; ?>" id="<?php echo $name; ?>" value="<?php echo $option_value; ?>" />
  <div class="admin-media-image"><img class="media-image" src="<?php echo $src; ?>" width="100%" height="100%" /></div>
  <a href="#" class="button insert-media add_media" name="<?php echo $name; ?>_button" id="button_<?php echo $name; ?>"><span class="wp-media-buttons-icon"></span> <?php echo $button_value; ?></a>
  <a class="button" id="reset-image-button" href="#"><?php _e('Reset image', 'al-ecommerce-product-catalog'); ?></a>
</div>
<script>
jQuery(document).ready(function()
{
jQuery('#button_<?php echo $name; ?>').on('click', function()
{
wp.media.editor.send.attachment = function(props, attachment)
{
jQuery('#<?php echo $name; ?>').val(attachment.url);
jQuery('.media-image').attr("src", attachment.url);
}

wp.media.editor.open(this);

return false;
});
});

jQuery('#reset-image-button').on('click', function() {
jQuery('#<?php echo $name; ?>').val('');
src = jQuery('#default').val();
jQuery('.media-image').attr("src", src);
});
</script>
<?php

}

function select_page($option_name,$first_option,$selected_value) {
$args = array(
		'sort_order' => 'ASC',
		'sort_column' => 'post_title',
		'hierarchical' => 1,
		'exclude' => '',
		'include' => '',
		'meta_key' => '',
		'meta_value' => '',
		'authors' => '',
		'child_of' => 0,
		'parent' => -1,
		'exclude_tree' => '',
		'number' => '',
		'offset' => 0,
		'post_type' => 'page',
		'post_status' => 'publish'
		); 
$pages = get_pages($args); 
$select_box = '<select id="'.$option_name.'" name="'.$option_name.'"><option value="noid">'.$first_option.'</option>';
foreach ($pages as $page) { 
	$select_box .= '<option name="' .$option_name. '[' .$page->ID. ']" value="' .$page->ID. '" ' .selected($page->ID, $selected_value, 0). '>' .$page->post_title. '</option>';
	}  
$select_box .= '</select>';

echo $select_box;
}

function show_page_link($page_id) {
$page_url = post_permalink( $page_id );
$page_link = '<a target="_blank" href='.$page_url.'>'.$page_url.'</a>';
echo $page_link;
}

function verify_page_status($page_id) {
$page_status = get_post_status( $page_id );
if ($page_status != 'publish' AND $page_status != '') {
echo '<div class="al-box warning">This page has wrong status: '.$page_status.'.<br>Don\'t forget to publish it before going live!</div>';
}
}

function design_schemes($which = null, $echo = 1) {
$custom_design_schemes = unserialize (DEFAULT_DESIGN_SCHEMES);
$design_schemes = get_option('design_schemes', $custom_design_schemes);
if ($which == 'color') {
$output = $design_schemes['price-color'];
}
else if ($which == 'size') {
$output = $design_schemes['price-size'];
}
else if ($which == 'box') {
$output = $design_schemes['box-color'];
}
else if ($which == 'none') {
$output = '';
}
else {
$output = $design_schemes['price-color'].' '.$design_schemes['price-size'];
}
return echo_ic_setting(apply_filters('design_schemes_output', $output), $echo);
}

/* Single Product Functions */

function single_product_header($post, $single_names) { 
if (get_integration_type() != 'simple') { ?>
<header class="entry-header">
	<h1 class="entry-title product-name"><?php the_title(); ?></h1>
	<?php do_action('single_product_header', $post, $single_names); ?>
</header><?php
}
}
add_action('before_product_entry','single_product_header', 10, 2);

function example_price() {
echo '2500.00 EUR';
}
add_action('example_price','example_price');

function show_price($post, $single_names) {
$price_value = product_price($post->ID);
if (!empty($price_value)) { ?>
	<table class="price-table">
		<tr>
			<td class="price-label"><?php echo $single_names['product_price'] ?></td>
			<td class="price-value <?php design_schemes(); ?>"><?php echo price_format($price_value); ?></td>
		</tr>
		<?php do_action('price_table'); ?>
	</table>
<?php do_action('after_price_table'); }
}
add_action('product_details','show_price', 7, 2);

function show_sku($post, $single_names) {
$archive_multiple_settings = get_option('archive_multiple_settings', unserialize (DEFAULT_ARCHIVE_MULTIPLE_SETTINGS));
$archive_multiple_settings['disable_sku'] = isset($archive_multiple_settings['disable_sku']) ? $archive_multiple_settings['disable_sku'] : '';
$sku_value = get_post_meta($post->ID, '_sku', true);
if ($archive_multiple_settings['disable_sku'] != 1 && !empty($sku_value)) { ?>
	<table class="sku-table">
		<tr>
			<td><?php echo $single_names['product_sku'] ?></td>
			<td class="sku-value"><?php echo $sku_value; ?></td>
		</tr>
	</table>
<?php }
}
add_action('product_details','show_sku', 8, 2);

function product_price($product_id, $unfiltered = null) {
if (empty($unfiltered)) { 
$price_value = apply_filters('product_price', get_post_meta($product_id, "_price", true), $product_id); }
else {
$price_value = apply_filters('unfiltered_product_price', get_post_meta($product_id, "_price", true), $product_id);
}
return $price_value;
}

function product_currency() {
$product_currency = get_option('product_currency',DEF_CURRENCY);
$product_currency_settings = get_option('product_currency_settings', unserialize(DEF_CURRENCY_SETTINGS));
if (! empty($product_currency_settings['custom_symbol'])) {
$currency = $product_currency_settings['custom_symbol'];
}
else {$currency = $product_currency; }
return $currency;
}

function show_shipping_options($post, $single_names) {
$shipping_options = get_option('product_shipping_options_number',DEF_SHIPPING_OPTIONS_NUMBER);
$sh_val = '';
$any_shipping_value = '';
for ($i = 1; $i <= $shipping_options; $i++) {
	$sh_val = get_post_meta($post->ID, "_shipping".$i, true);
	if (! empty($sh_val)) {
		$any_shipping_value = $sh_val; }
}
if ($shipping_options > 0 AND ! empty($any_shipping_value)) { ?>
	<table class="shipping-table">
		<tr>
			<td>
				<?php echo $single_names['product_shipping'] ?>
			</td>
			<td>
				<ul>
					<?php for ($i = 1; $i <= $shipping_options; $i++) { 
						$shipping_value = get_post_meta($post->ID, "_shipping".$i, true);
						if (! empty($shipping_value)) {
							echo '<li>'. get_post_meta($post->ID, "_shipping-label".$i, true) . ' : ' . price_format($shipping_value) . '</li>'; 
						}
					}?>
				</ul>
			</td>
		</tr> 
	</table>
<?php }
}

add_action('product_details','show_shipping_options', 9, 2);

function show_short_desc($post, $single_names) { 
$shortdesc = get_post_meta($post->ID, "_shortdesc", true);  
$content = $shortdesc; ?>
<div class="shortdesc">
	<?php echo apply_filters('product_short_description' , $shortdesc);	?>
</div>
<?php }
add_action('product_details','show_short_desc', 5, 2);
add_filter('product_short_description', 'wptexturize');
add_filter('product_short_description', 'convert_smilies');
add_filter('product_short_description', 'convert_chars');
add_filter('product_short_description', 'wpautop');
add_filter('product_short_description', 'shortcode_unautop');
add_filter('product_short_description', 'do_shortcode', 11);

function show_product_attributes($post, $single_names) {
$attributes_number = get_option('product_attributes_number', DEF_ATTRIBUTES_OPTIONS_NUMBER);
$at_val = '';
$any_attribute_value = '';
for ($i = 1; $i <= $attributes_number; $i++) {
	$at_val = get_post_meta($post->ID, "_attribute".$i, true);
	if (! empty($at_val)) {
		$any_attribute_value = $at_val; 
	}
}
if ($attributes_number > 0 AND ! empty($any_attribute_value)) { ?>
	<div id="product_features" class="product-features">
		<h3><?php echo $single_names['product_features']; ?></h3>
		<table class="features-table">
			<?php for ($i = 1; $i <= $attributes_number; $i++) { 
				$attribute_value = get_post_meta($post->ID, "_attribute".$i, true);
				if (! empty($attribute_value)) {
					echo '<tr><td class="attribute-label-single">'. get_post_meta($post->ID, "_attribute-label".$i, true) . '</td><td>' . get_post_meta($post->ID, "_attribute".$i, true). ' '. get_post_meta($post->ID, "_attribute-unit".$i, true) .'</td></tr>'; 
				} 
			} ?>
		</table>
	</div>
<?php }
}

add_action('after_product_details','show_product_attributes', 10, 2);

function show_product_description($post, $single_names) {
$product_description = get_post_meta($post->ID, "_desc", true);
if (! empty($product_description)) { ?>
	<div class="product-description"><?php 
		if (get_integration_type() == 'simple') {
			echo apply_filters('product_simple_description', $product_description);
		}
		else {
			echo apply_filters( 'the_content',$product_description);
		} ?>
	</div>
<?php }
}

add_action('after_product_details','show_product_description', 10, 2);
add_filter('product_simple_description', 'wptexturize');
add_filter('product_simple_description', 'convert_smilies');
add_filter('product_simple_description', 'convert_chars');
add_filter('product_simple_description', 'wpautop');
add_filter('product_simple_description', 'shortcode_unautop');
//add_filter('product_simple_description', 'do_shortcode', 11);

function show_related_categories($post, $single_names, $taxonomy_name) { 
$terms = wp_get_post_terms($post->ID, $taxonomy_name, array("fields" => "ids"));
if (empty($terms)) {
return;
}
$term = $terms[0];
$categories = wp_list_categories('title_li=&taxonomy='.$taxonomy_name.'&include='.$term.'&echo=0&hierarchical=0'); 
if ($categories != '<li class="cat-item-none">No categories</li>') { ?>
	<div id="product_subcategories" class="product-subcategories">
		<table>
			<tr>
				<td>
					<?php echo $single_names['other_categories']; ?>
				</td>
				<td>
					<?php echo $categories; ?>
				</td>
			</tr>
		</table>
	</div> 
<?php }
}
add_action('single_product_end','show_related_categories', 10, 3);

/* Archive Functions */
function show_archive_price($post) {
$price_value = product_price($post->ID);
if (!empty($price_value)) { ?>
	<div class="product-price <?php design_schemes('color'); ?>">
		<?php echo price_format($price_value) ?>
	</div>
<?php }
}

add_action('archive_price', 'show_archive_price',10,1);

function set_archive_price($archive_price, $post) {
$price_value = product_price($post->ID);
if (!empty($price_value)) {
$archive_price = '<div class="product-price '. design_schemes('color', 0).'">';
$archive_price .= price_format($price_value);
$archive_price .= '</div>';
}
return $archive_price;
}

add_filter('archive_price_filter', 'set_archive_price', 10, 2);

function get_quasi_post_type($post_type = null) {
if (empty($post_type)) {
$post_type = get_post_type(); }
$quasi_post_type = substr($post_type,0,10);
return $quasi_post_type;
}

function product_breadcrumbs() {
if (get_integration_type() != 'simple') {
global $post;
$post_type = get_post_type();
$home_page = get_home_url();
if (function_exists('additional_product_listing_url') AND $post_type != 'al_product') {
$catalog_id = catalog_id($post_type);
$product_archives = additional_product_listing_url();
$product_archive = $product_archives[$catalog_id];
$archives_ids = get_option('additional_product_archive_id');
$breadcrumbs_options = get_option('product_breadcrumbs', unserialize (DEFAULT_PRODUCT_BREADCRUMBS));
if (empty($breadcrumbs_options['enable_product_breadcrumbs'][$catalog_id]) || !empty($breadcrumbs_options['enable_product_breadcrumbs'][$catalog_id]) && $breadcrumbs_options['enable_product_breadcrumbs'][$catalog_id] != 1) {
return;
}
$product_archive_title_options = $breadcrumbs_options['breadcrumbs_title'][$catalog_id];
if ($product_archive_title_options != '') {
$product_archive_title = $product_archive_title_options;
}
else {
$product_archive_title = get_the_title($archives_ids[$catalog_id]);
}}
else {
$archive_multiple_settings = get_option('archive_multiple_settings', unserialize (DEFAULT_ARCHIVE_MULTIPLE_SETTINGS));
if (empty($archive_multiple_settings['enable_product_breadcrumbs']) || !empty($archive_multiple_settings['enable_product_breadcrumbs']) && $archive_multiple_settings['enable_product_breadcrumbs'] != 1) {
return;
}

$product_archive = product_listing_url(); 
if ($archive_multiple_settings['breadcrumbs_title'] != '') {
$product_archive_title = $archive_multiple_settings['breadcrumbs_title'];
}
else {
$product_archive_title = get_the_title(get_option('product_archive', get_option('product_archive_page_id','0'))); } }
$current_product = get_the_title($post->ID);
if (is_single()) {
return '<p id="breadcrumbs">
<span xmlns:v="http://rdf.data-vocabulary.org/#">
	<span typeof="v:Breadcrumb">
		<a href="'.$home_page.'" rel="v:url" property="v:title">'.__('Home','al-ecommerce-product-catalog').'</a>
	</span> » 
	<span typeof="v:Breadcrumb">
		<a href="'.$product_archive.'" rel="v:url" property="v:title">'.$product_archive_title.'</a>
	</span> » 
	<span typeof="v:Breadcrumb">
		<span class="breadcrumb_last" property="v:title">'.$current_product.'</span>
	</span>
</span>
</p>'; }
else {
return '<p id="breadcrumbs">
<span xmlns:v="http://rdf.data-vocabulary.org/#">
	<span typeof="v:Breadcrumb">
		<a href="'.$home_page.'" rel="v:url" property="v:title">'.__('Home','al-ecommerce-product-catalog').'</a>
	</span> » 
	<span typeof="v:Breadcrumb">
		<span class="breadcrumb_last" property="v:title">'.$product_archive_title.'</span>
	</span>
</span>
</p>';
}
}
}

function al_product_register_widgets() {
	register_widget( 'product_cat_widget' );
	register_widget( 'product_widget_search' );
do_action('implecode_register_widgets');
}

add_action( 'widgets_init', 'al_product_register_widgets' );

function permalink_options_update() {
update_option('al_permalink_options_update', 1);
}

function check_permalink_options_update() {
$options_update = get_option('al_permalink_options_update', 'none');
if ($options_update != 'none') {
flush_rewrite_rules(false);
update_option('al_permalink_options_update', 'none');
}
}
function is_lightbox_enabled() {
$enable_catalog_lightbox = get_option('catalog_lightbox', 1);
$return = false;
if ($enable_catalog_lightbox == 1) {
$return = true;
}
return apply_filters('is_lightbox_enabled', $return);
}

function show_product_gallery($post, $single_options ) {
$single_options['enable_product_gallery'] = isset($single_options['enable_product_gallery']) ? $single_options['enable_product_gallery'] : '';
$single_options['enable_product_gallery_only_when_exist'] = isset($single_options['enable_product_gallery_only_when_exist']) ? $single_options['enable_product_gallery_only_when_exist'] : '';
if (is_lightbox_enabled() && $single_options['enable_product_gallery'] == 1) { 
	$colorbox_set = apply_filters('colorbox_set', '{transition: "elastic", initialWidth: 200, maxWidth: "90%", maxHeight: "90%", rel:"gal"}'); ?>
	<script>
		jQuery(document).ready(function(){
			jQuery(".a-product-image").colorbox(<?php echo $colorbox_set ?>);
		});
	</script> <?php 
}
if ($single_options['enable_product_gallery'] == 1) { 
	do_action('before_product_image'); ?>
	<div class="entry-thumbnail product-image"><?php 
	do_action('above_product_image');
	$image_size = apply_filters('product_image_size', 'medium');
	if (has_post_thumbnail()) { 
		if (is_lightbox_enabled()) {
			$img_url = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'large'); ?>
			<a class="a-product-image" href="<?php echo $img_url[0];?>"><?php the_post_thumbnail($image_size);?></a> <?php } 
		else {
			the_post_thumbnail($image_size); }
		} 
	else if ($single_options['enable_product_gallery_only_when_exist'] != 1) { 
		echo default_product_thumbnail(); 
	} 
	do_action('below_product_image', $post->ID);?>
	</div> <?php 
	do_action('after_product_image', $post->ID);
}
else { 
	return;
}
}
function product_gallery_enabled($enable, $enable_inserted, $post) {
$details_class = 'no-image'; 
if ($enable == 1) {
	if ($enable_inserted == 1 && ! has_post_thumbnail()) {
	return $details_class;
	}
	else {
	return;
	}
}
else { 
	return $details_class;
}
}

function product_post_type_array() {
$array = apply_filters('product_post_type_array', array('al_product'));
return $array;
}

function array_to_url($array) {
$url = urlencode(serialize($array));
return $url;
}

function url_to_array($url) {
$array = unserialize(stripslashes(urldecode($url)));
return $array;
}

function exclude_products_search( $search, &$wp_query ) {
global $wpdb;
	if ( empty( $search ))
		return $search;
$search .= " AND (($wpdb->posts.post_type NOT LIKE '%al_product%'))";
return $search;
}

function modify_product_search($query) {
if ( $query->is_search == 1 && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] != 'al_product' ) {
	add_filter('posts_search', 'exclude_products_search', 10, 2);
}
else if ($query->is_search == 1 && ! isset($query->query_vars['post_type'])) {
	add_filter('posts_search', 'exclude_products_search', 10, 2);
}
}

add_action( 'pre_get_posts', 'modify_product_search', 10, 1 ); 

function product_archive_title($title = null, $sep = null, $seplocation = null) {
global $post;
$settings = get_option('archive_multiple_settings', unserialize (DEFAULT_ARCHIVE_MULTIPLE_SETTINGS));
$settings['seo_title'] = isset($settings['seo_title']) ? $settings['seo_title'] : '';
$settings['seo_title_sep'] = isset($settings['seo_title_sep']) ? $settings['seo_title_sep'] : '';
if ($settings['seo_title_sep'] == 1) {
if ($sep != '') {
$sep = ' '.$sep.' ';
}
}
else {
$sep = '';
}
if ($settings['seo_title'] != '' && is_archive() && $post->post_type == 'al_product') {
	if ($seplocation == 'right') {
	$title = $settings['seo_title'].$sep; }
	else {
	$title = $sep.$settings['seo_title'];
	}
}
return $title;
}
add_filter('wp_title', 'product_archive_title', 99, 3);

function add_support_link($links, $file) {
	
	$plugin = plugin_basename(AL_PLUGIN_MAIN_FILE);

	// create link
	if ($file == $plugin) {
		return array_merge(
			$links,
			array( sprintf( '<a href="edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=support">%s</a>', __('Support') ) )
		);
	}

	return $links;
}

add_filter( 'plugin_row_meta', 'add_support_link', 10, 2 );

function implecode_al_box($text, $type = 'info') {
echo '<div class="al-box '.$type.'">';
echo $text;
echo '</div>';
}

function get_product_image_id( $attachment_url = '' ) {
global $wpdb;
$attachment_id = false;
if ( '' == $attachment_url )
	return;
$upload_dir_paths = wp_upload_dir();
if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
	$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
	$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );
	$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
}
return $attachment_id;
}

function get_all_catalog_products() {
$args = array(
		'post_type' => product_post_type_array(),
		'post_status' => 'publish',
		'posts_per_page' => -1,
		); 
$products = get_posts($args);
return $products;
}

function all_ctalog_products_dropdown($option_name, $first_option, $selected_value) {
$pages = get_all_catalog_products(); 
$select_box = '<select class="all_products_dropdown" id="'.$option_name.'" name="'.$option_name.'"><option value="noid">'.$first_option.'</option>';
foreach ($pages as $page) { 
	$select_box .= '<option class="id_'.$page->ID.'" name="' .$option_name. '[' .$page->ID. ']" value="' .$page->ID. '" ' .selected($page->ID, $selected_value, 0). '>' .$page->post_title. '</option>';
	}  
$select_box .= '</select>';
return $select_box;
}

function thumbnail_support_products() {
$support = get_theme_support('post-thumbnails');
$support_array = product_post_type_array(); 
if (is_array($support)) {
$support_array = array_merge($support[0], $support_array);
add_theme_support( 'post-thumbnails', $support_array );
}
else if (! $support) {
add_theme_support( 'post-thumbnails', $support_array );
}
else {
add_theme_support( 'post-thumbnails' );
}
}
add_action('after_setup_theme', 'thumbnail_support_products', 99);

function set_product_thumbnail_size_in_admin( $sizes ) { //print_r($sizes);
$post_type = get_post_type($_REQUEST['post_id']);
$product_types = product_post_type_array(); 
if (in_array($post_type, $product_types)) {
	$sizes['post-thumbnail'] = array( 'width' => 150, 'height' => 150, 'crop' => false);
}
return $sizes;
}
add_filter( 'intermediate_image_sizes_advanced', 'set_product_thumbnail_size_in_admin', 10);
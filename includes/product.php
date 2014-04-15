<?php 
/**
 * Manages product post type
 *
 * Here all product fields are defined.
 *
 * @version		1.1.1
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function frontend_scripts() {
	if (!is_admin()) {
		wp_enqueue_script('jquery');
		wp_register_script('colorbox', AL_PLUGIN_BASE_PATH.'js/colorbox/jquery.colorbox-min.js', array('jquery'));
		wp_register_style('colorbox', AL_PLUGIN_BASE_PATH.'js/colorbox/colorbox.css');
	}
}
add_action('init', 'frontend_scripts');

add_action( 'init', 'create_product' );
function create_product() {
global $wp_version;
$enable_product_listing = get_option('enable_product_listing', 1);
if ($enable_product_listing == 1) {$product_listing_t = true;} else {$product_listing_t = false;}
$slug = untrailingslashit(get_option('product_listing_url', __('products', 'al-ecommerce-product-catalog')));
if ( $wp_version < 3.8 ) {
	$reg_settings = array(
			'labels' => array(
				'name' => __('Products', 'al-ecommerce-product-catalog'),
				'singular_name' => __('Product', 'al-ecommerce-product-catalog'),
				'add_new'            => __( 'Add New Product','al-ecommerce-product-catalog'),
				'add_new_item'       => __( 'Add New Product','al-ecommerce-product-catalog'),
				'edit_item'          => __( 'Edit Product','al-ecommerce-product-catalog'),
				'new_item'           => __( 'Add New Product','al-ecommerce-product-catalog'),
				'view_item'          => __( 'View Product','al-ecommerce-product-catalog'),
				'search_items'       => __( 'Search Products','al-ecommerce-product-catalog'),
				'not_found'          => __( 'No Products found','al-ecommerce-product-catalog'),
				'not_found_in_trash' => __( 'No Products found in trash','al-ecommerce-product-catalog')
			),
		'public' => true,
		'has_archive' => $product_listing_t,
		'rewrite' => array('slug' => apply_filters ('product_slug_value_register', $slug), 'with_front' => false),
		'supports' => array( 'title', 'thumbnail'),
		'register_meta_box_cb' => 'add_product_metaboxes',
		'taxonomies' => array('al_product_cat'),
		'menu_icon' => plugins_url() . '/ecommerce-product-catalog/img/product.png',
		'capability_type' => 'product',
		'capabilities' => array(
				'publish_posts' => 'publish_products',
				'edit_posts' => 'edit_products',
				'edit_others_posts' => 'edit_others_products',
				'edit_published_posts' => 'edit_published_products',
				'edit_private_posts' => 'edit_private_products',
				'delete_posts' => 'delete_products',
				'delete_others_posts' => 'delete_others_products',
				'delete_private_posts' => 'delete_private_products',
				'delete_published_posts' => 'delete_published_products',
				'read_private_posts' => 'read_private_products',
				'edit_post' => 'edit_product',
				'delete_post' => 'delete_product',
				'read_post' => 'read_product',
			),
		'exclude_from_search' => false,
		); }
	else {
	$reg_settings = array(
			'labels' => array(
				'name' => __('Products', 'al-ecommerce-product-catalog'),
				'singular_name' => __('Product', 'al-ecommerce-product-catalog'),
				'add_new'            => __( 'Add New Product','al-ecommerce-product-catalog'),
				'add_new_item'       => __( 'Add New Product','al-ecommerce-product-catalog'),
				'edit_item'          => __( 'Edit Product','al-ecommerce-product-catalog'),
				'new_item'           => __( 'Add New Product','al-ecommerce-product-catalog'),
				'view_item'          => __( 'View Product','al-ecommerce-product-catalog'),
				'search_items'       => __( 'Search Products','al-ecommerce-product-catalog'),
				'not_found'          => __( 'No Products found','al-ecommerce-product-catalog'),
				'not_found_in_trash' => __( 'No Products found in trash','al-ecommerce-product-catalog')
			),
		'public' => true,
		'has_archive' => $product_listing_t,
		'rewrite' => array('slug' => apply_filters ('product_slug_value_register', $slug), 'with_front' => false),
		'supports' => array( 'title', 'thumbnail'),
		'register_meta_box_cb' => 'add_product_metaboxes',
		'taxonomies' => array('al_product-cat'),
		'capability_type' => 'product',
		'capabilities' => array(
				'publish_posts' => 'publish_products',
				'edit_posts' => 'edit_products',
				'edit_others_posts' => 'edit_others_products',
				'edit_published_posts' => 'edit_published_products',
				'edit_private_posts' => 'edit_private_products',
				'delete_posts' => 'delete_products',
				'delete_others_posts' => 'delete_others_products',
				'delete_private_posts' => 'delete_private_products',
				'delete_published_posts' => 'delete_published_products',
				'read_private_posts' => 'read_private_products',
				'edit_post' => 'edit_product',
				'delete_post' => 'delete_product',
				'read_post' => 'read_product',
			),
		'exclude_from_search' => false,
		);
	}

	register_post_type( 'al_product', $reg_settings);
	// flush_rewrite_rules(false);
	
}

add_action('admin_head', 'product_icons');
function product_icons() {
        global $post_type;
    ?>
    <style>
    <?php if (isset($_GET['post_type']) == 'al_product') : ?>
    #icon-edit { background:transparent url('<?php echo plugins_url() . '/ecommerce-product-catalog/img/product-32.png';?>') no-repeat; }     
    <?php endif; ?>
        </style>
        <?php
}

// Add the Events Meta Boxes
function add_product_metaboxes() {
	add_meta_box('al_product_short_desc', __('Product short description', 'al-ecommerce-product-catalog'), 'al_product_short_desc', 'al_product', apply_filters('short_desc_box_column','normal'), apply_filters('short_desc_box_priority','default'));
	add_meta_box('al_product_desc', __('Product description', 'al-ecommerce-product-catalog'), 'al_product_desc', 'al_product', apply_filters('desc_box_column','normal'), apply_filters('desc_box_priority','default'));
	add_meta_box('al_product_price', __('Product Price', 'al-ecommerce-product-catalog'), 'al_product_price', 'al_product', apply_filters('product_price_box_column','side'), apply_filters('product_price_box_priority','default'));
	if (get_option('product_shipping_options_number',DEF_SHIPPING_OPTIONS_NUMBER) > 0) {
	add_meta_box('al_product_shipping', __('Product Shipping', 'al-ecommerce-product-catalog'), 'al_product_shipping', 'al_product', apply_filters('product_shipping_box_column','side'), apply_filters('product_shipping_box_priority','default')); }
	if (get_option('product_attributes_number',DEF_ATTRIBUTES_OPTIONS_NUMBER) > 0) {
	add_meta_box('al_product_attributes', __('Product attributes', 'al-ecommerce-product-catalog'), 'al_product_attributes', 'al_product', apply_filters('product_attributes_box_column','normal'), apply_filters('product_attributes_box_priority','default')); }
	do_action('add_product_metaboxes');
}

// The Product Price Metabox
function al_product_price() {
	global $post;
	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="pricemeta_noncename" id="pricemeta_noncename" value="' .
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	// Get the price data if its already been entered
	$price = get_post_meta($post->ID, '_price', true);
	// Echo out the field
	echo '<table><tr><td class="price-column"><input type="number" min="0" step="0.01" name="_price" value="' . $price  . '" class="widefat" /></td><td>'. product_currency() .'</td></tr></table>';
}

// The Product Shipping Metabox
function al_product_shipping() {
	global $post;
	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="shippingmeta_noncename" id="shippingmeta_noncename" value="' .
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	$currency = get_option('product_currency', DEF_CURRENCY);
	echo '<table class="sort-settings shipping"><tbody>';
	for ($i = 1; $i <= get_option('product_shipping_options_number', DEF_SHIPPING_OPTIONS_NUMBER); $i++) {
	// Get the shipping data if its already been entered
	$shipping_option = get_option('product_shipping_cost');
	$shipping_label_option = get_option('product_shipping_label');
	$shipping_option_field = get_post_meta($post->ID, '_shipping'.$i, true);
	$shipping_label_field = get_post_meta($post->ID, '_shipping-label'.$i, true);
	if (! empty($shipping_option_field)) {
	$shipping = $shipping_option_field; }
	else { $shipping = $shipping_option[$i]; }
	if (! empty($shipping_label_field)) {
	$shipping_label = $shipping_label_field; }
	else { $shipping_label = $shipping_label_option[$i]; }
	// Echo out the fields
	echo '<tr><td class="shipping-label-column"><input class="shipping-label" type="text" name="_shipping-label'.$i.'" value="' . $shipping_label  . '" /></td><td><input class="shipping-value" type="number" min="0" name="_shipping'.$i.'" value="' . $shipping  . '" />'. $currency .'</td></tr>'; }
	echo '</tbody></table>';
}

// The Product attributes Metabox
function al_product_attributes() {
	global $post;
	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="attributesmeta_noncename" id="attributesmeta_noncename" value="' .
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	echo '<div class="al-box info">'. __('Only attributes with values set will be shown on product page.', 'al-ecommerce-product-catalog') .'</div>';
	do_action('before_product_attributes_edit_single');
	echo '<table class="sort-settings attributes">
	<thead><tr>
	<th class="title"><b>Name</b></th>
	<th></th>
	<th class="title"><b>Value</b></th>
	<th class="title"><b>Unit</b></th>
	</tr>
	</thead>
	<tbody>';
	do_action('inside_attributes_edit_table');
	$attributes_option = get_option('product_attribute');
	$attributes_label_option = get_option('product_attribute_label');
	$attributes_unit_option = get_option('product_attribute_unit');
	for ($i = 1; $i <= get_option('product_attributes_number', DEF_ATTRIBUTES_OPTIONS_NUMBER); $i++) {
		$attributes_option_field = get_post_meta($post->ID, '_attribute'.$i, false);
		$attributes_label_option_field = get_post_meta($post->ID, '_attribute-label'.$i, true);
		$attributes_unit_option_field = get_post_meta($post->ID, '_attribute-unit'.$i, true);
		if (! empty($attributes_option_field)) {
			$attributes = $attributes_option_field[0]; }
		else { $attributes = $attributes_option[$i]; }
		if (! empty($attributes_label_option_field)) {
			$attributes_label = $attributes_label_option_field; }
		else { $attributes_label = $attributes_label_option[$i]; }
		if (! empty($attributes_unit_option_field)) {
			$attributes_unit = $attributes_unit_option_field; }
		else { $attributes_unit = $attributes_unit_option[$i]; } 
		$attribute_value_field = '<input class="attribute-value" type="text" name="_attribute'. $i .'" value="'. $attributes .'" />'; ?>
		<tr>
			<td class="attributes-label-column"><input class="attribute-label" type="text" name="_attribute-label<?php echo $i ?>" value="<?php echo $attributes_label ?>" /></td>
			<td class="break-column">:</td>
			<td class="value-column"><?php echo apply_filters ('product_attribute_value_edit', $attribute_value_field, $i, $attributes) ?></td>
			<td class="unit-column"><input class="attribute-unit admin-number-field" type="text" name="_attribute-unit<?php echo $i ?>" value="<?php echo $attributes_unit ?>" /></td>
		</tr>
<?php } ?>
	</tbody>
	</table><?php
	do_action('product_attributes_edit_single', $post);
}

// The Product Short Description Metabox
function al_product_short_desc() {
	global $post;
	// Noncename needed to verify where the data originated
	echo '<input type="hidden" name="shortdescmeta_noncename" id="shortdescmeta_noncename" value="' .
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	// Get short description data if its already been entered
	$shortdesc = get_post_meta($post->ID, '_shortdesc', true);
	// Echo out the field
	// echo '<textarea name="_shortdesc" value="' . $shortdesc  . '" class="widefat" ></textarea>';
	$short_desc_settings = array('media_buttons' => false, 'textarea_rows' => 5, 'teeny' => true);
	wp_editor($shortdesc,'_shortdesc', $short_desc_settings);
}
function al_product_desc() {
	global $post;
	echo '<input type="hidden" name="descmeta_noncename" id="descmeta_noncename" value="' .
	wp_create_nonce( plugin_basename(__FILE__) ) . '" />';
	$desc = get_post_meta($post->ID, '_desc', true);
	$desc_settings = array('textarea_rows' => 30);
	wp_editor($desc,'_desc', $desc_settings);
}

// Save the Metabox Data
function implecode_save_products_meta($post_id, $post) {
	$post_type_now = substr($post->post_type,0,10);
	if($post_type_now == 'al_product' ) {
	$pricemeta_noncename = isset($_POST['pricemeta_noncename']) ? $_POST['pricemeta_noncename'] : '';
	if ( !empty($pricemeta_noncename) && !wp_verify_nonce( $pricemeta_noncename, plugin_basename(__FILE__) )) {
	return $post->ID;
	}
	if ( !current_user_can( 'edit_post', $post->ID ))
		return $post->ID;
	$product_meta['_price'] = !empty($_POST['_price']) ? $_POST['_price'] : '';
	$product_meta['_shortdesc'] = !empty($_POST['_shortdesc']) ? $_POST['_shortdesc'] : '';
	$product_meta['_desc'] = !empty($_POST['_desc']) ? $_POST['_desc'] : '';
	for ($i = 1; $i <= get_option('product_shipping_options_number',DEF_SHIPPING_OPTIONS_NUMBER); $i++) {
	$product_meta['_shipping'.$i] = !empty($_POST['_shipping'.$i]) ? $_POST['_shipping'.$i] : '';
	$product_meta['_shipping-label'.$i] = !empty($_POST['_shipping-label'.$i]) ? $_POST['_shipping-label'.$i] : '';
	}
	for ($i = 1; $i <= get_option('product_attributes_number',DEF_ATTRIBUTES_OPTIONS_NUMBER); $i++) {
	$product_meta['_attribute'.$i] = !empty($_POST['_attribute'.$i]) ? $_POST['_attribute'.$i] : '';
	$product_meta['_attribute-label'.$i] = !empty($_POST['_attribute-label'.$i]) ? $_POST['_attribute-label'.$i] : '';
	$product_meta['_attribute-unit'.$i] = !empty($_POST['_attribute-unit'.$i]) ? $_POST['_attribute-unit'.$i] : '';
	}
	foreach ($product_meta as $key => $value) { 
		if(! $post_type_now == 'al_product' ) return; 
		$value = implode(',', (array)$value); 
		$current_value = get_post_meta( $post->ID, $key, true );
		if (isset($value) && ! isset($current_value)) {
			add_post_meta( $post->ID, $key, $value, true );
		}
		else if(isset($value) && $value != $current_value) { 
			update_post_meta($post->ID, $key, $value);
		} 
		else if (! isset($value) && $current_value) { 
			delete_post_meta($post->ID, $key);
		}
	} }
}
add_action('save_post', 'implecode_save_products_meta', 1, 2);

add_action('do_meta_boxes', 'change_image_box');
function change_image_box()
{
    remove_meta_box( 'postimagediv', 'al_product', 'side' );
   add_meta_box('postimagediv', __('Product Image','al-ecommerce-product-catalog'), 'post_thumbnail_meta_box', 'al_product', apply_filters('product_image_box_column','side'), apply_filters('product_image_box_priority','high'));
}	

add_action('admin_head-post-new.php', 'change_thumbnail_html');
add_action('admin_head-post.php', 'change_thumbnail_html');
function change_thumbnail_html( $content ) {
    if ('al_product' == get_quasi_post_type($GLOBALS['post_type']))
      add_filter('admin_post_thumbnail_html', 'do_thumb');
	  add_filter('admin_post_thumbnail_html', 'do_thumb_1');
}
function do_thumb($content){
	 return str_replace(__('Set featured image'), __('Set product image', 'al-ecommerce-product-catalog'),$content);
}

function do_thumb_1($content){
	 return str_replace(__('Remove featured image'), __('Remove product image', 'al-ecommerce-product-catalog'),$content);
}

function set_product_messages($messages) {
global $post, $post_ID;
$quasi_post_type = get_quasi_post_type();
$post_type = get_post_type( $post_ID );
if ($quasi_post_type == 'al_product') {
$obj = get_post_type_object($post_type);
$singular = $obj->labels->singular_name;

$messages[$post_type] = array(
0 => '', // Unused. Messages start at index 1.
1 => sprintf( __($singular.' updated. <a href="%s">View '.strtolower($singular).'</a>'), esc_url( get_permalink($post_ID) ) ),
2 => __('Custom field updated.'),
3 => __('Custom field deleted.'),
4 => __($singular.' updated.'),
5 => isset($_GET['revision']) ? sprintf( __($singular.' restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
6 => sprintf( __($singular.' published. <a href="%s">View '.strtolower($singular).'</a>'), esc_url( get_permalink($post_ID) ) ),
7 => __('Page saved.'),
8 => sprintf( __($singular.' submitted. <a target="_blank" href="%s">Preview '.strtolower($singular).'</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
9 => sprintf( __($singular.' scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview '.strtolower($singular).'</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
10 => sprintf( __($singular.' draft updated. <a target="_blank" href="%s">Preview '.strtolower($singular).'</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
); }
return $messages; 
}

add_filter('post_updated_messages', 'set_product_messages' );

require_once('product-categories.php');
require_once('search-widget.php');
// require_once('product-types.php');

?>
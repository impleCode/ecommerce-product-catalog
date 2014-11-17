<?php 
/**
 * Manages product category columns
 *
 * Here all product category columns defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function add_product_category_helper() {
doc_helper(__('category', 'al-ecommerce-product-catalog'), 'product-categories', 'left');
}

add_action( 'al_product-cat_add_form', 'add_product_category_helper' );

add_action('al_product-cat_edit_form_fields','product_category_edit_form_fields');
add_action('al_product-cat_edit_form', 'product_category_edit_form');
add_action('al_product-cat_add_form_fields','product_category_edit_form_fields');
add_action('al_product-cat_add_form','product_category_edit_form');


function product_category_edit_form() {
?>
<script type="text/javascript">
jQuery(document).ready(function(){
jQuery('#edittag').attr( "enctype", "multipart/form-data" ).attr( "encoding", "multipart/form-data" );
        });
</script>
<?php 
}

add_action('edit_al_product-cat','save_product_cat_image');
add_action('create_al_product-cat','save_product_cat_image');
function save_product_cat_image($term_id) {
if(isset($_POST['product_cat_image']))
	update_option('al_product_cat_image_'.$term_id, $_POST['product_cat_image']);
}

add_action('delete_al_product-cat','delete_product_cat_image');
function delete_product_cat_image($term_id) {
delete_option('al_product_cat_image_'.$term_id);
}

function product_category_edit_form_fields ($field) {
if (isset($field->term_id)) {
$cat_img_src = get_product_category_image_id($field->term_id);
}
else {
$cat_img_src = '';
}
$default_image = get_option('default_product_thumbnail');
implecode_upload_image('Select Category Image', 'product_cat_image', $cat_img_src, $default_image, 'id');
}
	
function product_cat_columns($product_columns) { 
    $product_columns = array_reverse($product_columns);
	$temp = $product_columns['cb'];
	unset($product_columns['cb']);
	unset($product_columns['slug']);
	$product_columns['img'] = __('Image', 'al-ecommerce-product-catalog');
	$product_columns['id'] = __('ID', 'al-ecommerce-product-catalog');
	$product_columns['cb'] = $temp;
	$product_columns = array_reverse($product_columns);
	$product_columns['shortcode'] = __('Shortcode', 'al-ecommerce-product-catalog');
    return $product_columns;
}
	
add_filter('manage_edit-al_product-cat_columns', 'product_cat_columns');
 
function manage_product_category_columns($depr, $column_name, $term_id) {
switch ($column_name) {
	case 'img':
		$attachment_id = get_product_category_image_id($term_id);
		echo wp_get_attachment_image( $attachment_id, array(40,40));
	break;
	case 'id':
		echo $term_id;
	break;
	
	case 'shortcode':
		echo '<code>[show_products category="'.$term_id.'"]</code><code>[show_categories include="'.$term_id.'"]</code>';
	break;
	
	default:
	break;
}
}

add_action('manage_al_product-cat_custom_column', 'manage_product_category_columns', 10, 3);

function get_product_category_image_id($cat_id) {
return get_option('al_product_cat_image_'.$cat_id);
}
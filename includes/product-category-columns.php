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

//add_action('al_product-cat_edit_form_fields','product_category_edit_form_fields');
//add_action('al_product-cat_edit_form', 'product_category_edit_form');
//add_action('al_product-cat_add_form_fields','product_category_edit_form_fields');
//add_action('al_product-cat_add_form','product_category_edit_form');


function product_category_edit_form() {
?>
<script type="text/javascript">
jQuery(document).ready(function(){
jQuery('#edittag').attr( "enctype", "multipart/form-data" ).attr( "encoding", "multipart/form-data" );
        });
</script>
<?php 
}

function product_category_edit_form_fields () {
?>
    <tr class="form-field">
            <th valign="top" scope="row">
                <label for="catpic"><?php _e('Category Picture', 'al-ecommerce-product-catalog'); ?></label>
            </th>
            <td>
                <input type="file" id="catpic" name="catpic"/>
            </td>
        </tr>
        <?php 
    }
	
function product_cat_columns($product_columns) { 
    $product_columns = array_reverse($product_columns);
	$temp = $product_columns['cb'];
	unset($product_columns['cb']);
	unset($product_columns['slug']);
	$product_columns['id'] = __('ID', 'al-ecommerce-product-catalog');
	$product_columns['cb'] = $temp;
	$product_columns = array_reverse($product_columns);
	$product_columns['shortcode'] = __('Shortcode', 'al-ecommerce-product-catalog');
    return $product_columns;
}
	
add_filter('manage_edit-al_product-cat_columns', 'product_cat_columns');
 
function manage_product_category_columns($depr, $column_name, $term_id) {
switch ($column_name) {
	case 'id':
		echo $term_id;
	break;
	
	case 'shortcode':
		echo '[show_products category="'.$term_id.'"]';
	break;
	
	default:
	break;
}
}

add_action('manage_al_product-cat_custom_column', 'manage_product_category_columns', 10, 3);
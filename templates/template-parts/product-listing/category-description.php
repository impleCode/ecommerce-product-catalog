<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product category page description
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/category-description.php
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates/template-parts/product-listing
 * @author 		impleCode
 */
$term_description = term_description();

if ( !empty( $term_description ) ) {
	?>

	<div class="taxonomy-description"><?php echo apply_filters( 'ic_product_cat_desc', $term_description ) ?></div>


	<?php
}



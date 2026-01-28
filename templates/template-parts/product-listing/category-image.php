<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display category page image
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/category-image.php
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates/template-parts/product-listing
 * @author 		impleCode
 */
$category_id	 = ic_get_global( 'current_product_category_id' );
$category_img	 = get_product_category_image_id( $category_id );
?>

<div class="taxonomy-image"><?php echo wp_get_attachment_image( $category_img, apply_filters( 'product_cat_image_size', 'product-category-page-image' ), false, array( 'class' => 'product-category-image' ) ); ?></div>

<?php


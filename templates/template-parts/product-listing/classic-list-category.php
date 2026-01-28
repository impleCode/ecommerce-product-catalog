<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product categories as classic list
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/classic-list-category.php
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates/template-parts/product-listing
 * @author        impleCode
 */
$product_cat = ic_get_global( 'ic_current_product_cat' );
if ( empty( $product_cat ) ) {
	return;
}

if ( $product_cat->parent == 0 ) {
	$class = 'top-category';
} else {
	$class = 'child-category';
}
?>


    <div class="archive-listing category-<?php echo $product_cat->term_id ?> list <?php echo $class ?>">
        <a href="<?php echo ic_get_category_url( $product_cat->term_id ) ?>"><span class="div-link"></span></a>
        <div class="classic-list-image-wrapper">
            <div class="pseudo"></div><?php echo ic_get_category_listing_image_html( $product_cat->term_id ) ?></div>
        <div class="product-name"><?php echo $product_cat->name ?></div>
        <div class="product-short-descr">
            <p><?php echo c_list_desc( null, $product_cat->description ) ?></p>
        </div>
    </div>

<?php

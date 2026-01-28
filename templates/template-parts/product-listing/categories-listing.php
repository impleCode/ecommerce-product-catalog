<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product categories on main product listing or subcategories on product category page
 *
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/categories-listing.php
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates/template-parts/product-listing
 * @author        impleCode
 */
$product_categories = ic_get_global( 'current_product_categories' );
$archive_template   = ic_get_global( 'current_product_archive_template' );
?>
    <div class="product-subcategories responsive <?php echo $archive_template . ' ' . product_list_class( $archive_template, 'category-list' ) ?>">
		<?php
		echo $product_categories;
		?>
    </div>
<?php


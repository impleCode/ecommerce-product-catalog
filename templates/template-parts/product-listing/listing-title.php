<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product listing or category page title
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/product-title.php
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates/template-parts/product-listing
 * @author 		impleCode
 */
?>

<h1 class="entry-title product-listing-name"><?php echo get_product_catalog_page_title() ?></h1>

<?php

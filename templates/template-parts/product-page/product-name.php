<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product name on product page
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/product-name.php
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates/template-parts/product-page
 * @author        impleCode
 */
if ( ! function_exists( 'get_product_name' ) ) {
	return;
}
?>

    <h1 class="entry-title product-name"><?php echo get_product_name() ?></h1>

<?php

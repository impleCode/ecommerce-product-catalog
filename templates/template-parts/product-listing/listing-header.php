<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product listing or category page header
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/listing-header.php
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates/template-parts/product-listing
 * @author 		impleCode
 */
global $post;
$archive_names = get_archive_names();
?>

<header class="entry-header product-listing-header">
	<?php do_action( 'product_listing_header', $post, $archive_names ); ?>
</header>

<?php

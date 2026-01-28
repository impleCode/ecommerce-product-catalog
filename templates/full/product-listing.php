<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * The template to display main product listing or category page in Advanced Mode
 *
 * Copy it to your theme implecode folder to edit the output: wp-content/themes/your-theme-folder-name/implecode/product-listing.php
 *
 * @version        1.1.3
 * @package        ecommerce-product-catalog/templates/full
 * @author        impleCode
 */
global $post;
$default_archive_names = default_archive_names();
$multiple_settings     = get_multiple_settings();
do_action( 'product_listing_begin', $multiple_settings );
$archive_names = get_archive_names();
$listing_class = apply_filters( 'product_listing_classes', 'al_product responsive type-page' );
?>
    <article id="product_listing" <?php post_class( $listing_class ); ?>>
		<?php do_action( 'before_product_listing_entry', $post, $archive_names ); ?>
        <div class="entry-content">
			<?php
			$archive_template = get_product_listing_template();
			do_action( 'product_listing_entry_inside', $archive_template, $multiple_settings );
			?>
        </div>
    </article>
<?php
do_action( 'product_listing_end', $archive_template, $multiple_settings );


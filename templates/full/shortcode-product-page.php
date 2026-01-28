<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product page when the [show_product_catalog] shortcode does exist on your main product listing page
 *
 * Copy it to your theme implecode folder to edit the output: wp-content/themes/your-theme-folder-name/implecode/shortcode-product-page.php
 *
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates
 * @author        impleCode
 */
$product_id        = ic_get_product_id();
$current_post_type = get_post_type( $product_id );
$taxonomy          = get_current_screen_tax();
$single_names      = get_single_names();
$product           = get_post( $product_id );
do_action( 'single_product_begin', $product_id, $current_post_type, $taxonomy );
do_action( 'before_product_entry', $product, $single_names );
?>
    <div class="product-entry">

		<?php
		if ( post_password_required() ) {
			the_content();

			return;
		} else {
			do_action( 'product_page_inside', $product_id, $single_names, $taxonomy );
		}
		?>
    </div>
<?php
do_action( "single_product_very_end", $product, $single_names );


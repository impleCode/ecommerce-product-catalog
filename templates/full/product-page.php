<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product page when the [show_product_catalog] shortcode does not exist on your main product listing page
 *
 * Copy it to your theme implecode folder to edit the output: wp-content/themes/your-theme-folder-name/implecode/product-page.php
 *
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/templates
 * @author        impleCode
 */
global $post;
$product         = $post;
$product_id      = isset( $product->ID ) ? $product->ID : '';
$this_product_id = ic_get_product_id();
if ( $this_product_id && $this_product_id !== $product_id ) {
	$product_id = $this_product_id;
	$product    = get_post( $product_id );
	setup_postdata( $product );
	//the_post();
}

$current_post_type = get_post_type( $product_id );
$taxonomy          = get_current_screen_tax();
do_action( 'single_product_begin', $product_id, $current_post_type, $taxonomy );
$single_names   = get_single_names();
$single_options = get_product_page_settings();
?>

    <article
            id="product_page" <?php post_class( 'al_product responsive type-page product-' . $product_id . ' ' . $single_options['template'] . ' ' . apply_filters( 'product-class', '', $product_id ) ); ?>>
		<?php
		do_action( 'before_product_entry', $product, $single_names );
		?>
        <div class="entry-content product-entry entry">
			<?php
			if ( post_password_required() ) {
				the_content();

				return;
			} else {
				do_action( 'product_page_inside', $product, $single_names, $taxonomy );
			}
			?>
        </div>
		<?php
		do_action( 'after_product_entry', $product, $single_names );
		?>
    </article>
<?php
do_action( "single_product_very_end", $product, $single_names );

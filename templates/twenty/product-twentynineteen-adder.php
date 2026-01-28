<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Template Name:  Product Template [NO SIDEBAR]
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/templates
 * @author 		impleCode
 */
get_header();
?>

<section id="primary" class="content-area">
	<main id="main" class="site-main">

		<?php
		content_product_adder();
		?>

	</main><!-- #main -->
</section><!-- #primary -->

<?php
get_footer();

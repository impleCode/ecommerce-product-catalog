<?php
/**
 * The template for displaying pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other "pages" on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */
get_header();
?>
<style>article#product_listing {width: auto; margin: 0 8.3333%}</style>
<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">

		<?php
		remove_action( 'single_product_begin', 'add_product_breadcrumbs' );
		remove_action( 'product_listing_begin', 'add_product_breadcrumbs' );
		add_action( 'single_product_header', 'add_product_breadcrumbs', 5 );
		add_action( 'product_listing_header', 'add_product_breadcrumbs', 5 );
		content_product_adder();
		?>

	</main><!-- .site-main -->
</div><!-- .content-area -->

<?php
get_footer();

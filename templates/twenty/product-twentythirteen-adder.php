<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that other
 * 'pages' on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */
get_header();
?>

<div id="primary" class="content-area">
	<div id="content" class="site-content" role="main">
		<style> .post-type-archive-al_product header.al_product {
				margin: 0 auto;
			}
			p#breadcrumbs {
				max-width: 1080px;
				margin: 0 auto;
			}
			.single .product-entry {margin: 0 auto;} .post-type-archive .entry-summary {padding: 0; } .post-type-archive-al_product .site-content article, .tax-al_product-cat .site-content article, .post-type-archive-al_product .al_product.article {padding: 40px 0px;}</style>
			<?php /* The loop */ ?>
			<?php content_product_adder(); ?>

	</div><!-- #content -->
</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
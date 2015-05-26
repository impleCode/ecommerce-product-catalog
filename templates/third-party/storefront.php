<?php
/**
 * eCommerce Product Catalog template for Storefront
 *
 * @package storefront
 */

get_header(); ?>
	<style>.right-sidebar .content-area {width: 100%;float: none;margin-right: 0;}</style>
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php content_product_adder() ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>

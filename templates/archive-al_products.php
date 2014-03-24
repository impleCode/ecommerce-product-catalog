<?php
/**
 * The template for displaying products archive pages.
 *
 * 
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/templates
 * @author 		Norbert Dreszer
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
get_header(); ?>
	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">
		<?php 	content_product_adder_archive();	?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
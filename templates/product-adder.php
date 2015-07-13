<?php
/**
 * Template Name:  Product Template
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates
 * @author 		Norbert Dreszer
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();
do_action( 'advanced_mode_layout_start' );
?>
<div id="container" class="content-area">
	<div id="content" class="site-content" role="main">

		<?php content_product_adder(); ?>


	</div><!-- #content -->
</div>

<?php
do_action( 'advanced_mode_layout_end' );

get_footer();

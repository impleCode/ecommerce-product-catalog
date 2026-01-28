<?php
/**
 * Template Name:  Product Template
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates
 * @author 		impleCode
 */
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();
do_action( 'advanced_mode_layout_start' );
?>
<div id="container" class="content-area container product-catalog">
	<?php do_action( 'advanced_mode_layout_before_content' ); ?>
	<div id="content" class="site-content" role="main">
		<?php
		content_product_adder();
		?>
	</div><!-- #content -->
	<?php do_action( 'advanced_mode_layout_after_content' ); ?>
</div>

<?php
do_action( 'advanced_mode_layout_end' );

get_footer();

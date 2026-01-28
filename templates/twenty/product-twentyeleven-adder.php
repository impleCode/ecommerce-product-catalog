<?php
/** 
* Template Name:  Product Template [NO SIDEBAR]
*
 * @version		1.0.0
 * @package		ecommerce-product-catalog/templates
 * @author 		impleCode
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header(); ?>

	
		
		<div id="primary">
			<div id="content" role="main"> 
			<style>.classic-grid.archive-listing {max-width: 176px;}</style>
			<?php content_product_adder(); ?>
			</div>
		</div><!-- #content -->
	

<?php if (is_archive()) {get_sidebar(); } ?>
<?php get_footer(); ?>

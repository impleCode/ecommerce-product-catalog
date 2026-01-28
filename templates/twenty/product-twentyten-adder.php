<?php
/** 
* Template Name:  Product Template
*
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates
 * @author 		impleCode
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header(); ?>

	<div id="container">
		<div id="content" class="site-content">
			 
			<?php content_product_adder(); ?>
				

		</div><!-- #content -->
	</div>
 
<?php get_sidebar(); ?>

<?php get_footer(); ?>

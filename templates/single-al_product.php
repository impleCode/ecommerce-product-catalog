<?php
/** 
* Template Name:  Ad Template
*
 * @version		1.0.0
 * @package		wp-ad-adder/templates
 * @author 		Norbert Dreszer
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

get_header(); ?>

	
		<div id="content" class="site-content" role="main">

			<?php content_product_adder_single(); ?>

		</div><!-- #content -->
	

<?php get_sidebar(); ?>
<?php get_footer(); ?>

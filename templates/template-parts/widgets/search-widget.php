<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * The template to display product search widget output
 *
 * Copy it to your theme implecode folder to edit the output: your-theme-folder-name/implecode/search-widget.php
 *
 * @version		1.1.2
 * @package		ecommerce-product-catalog/templates/template-parts/widgets
 * @author 		Norbert Dreszer
 */
$instance = ic_get_global( 'search_widget_instance' );
if ( $instance ) {
	$label				 = ic_get_search_widget_placeholder( $instance );
	$search_button_text	 = ic_get_search_widget_button_text();
	?>
	<form role="search" class="<?php design_schemes( 'box' ) ?>" method="get" id="product_search_form" action="<?php echo esc_url( home_url( '/' ) ) ?>">
		<?php echo apply_filters( 'ic_product_search_form_post_type', '<input type="hidden" name="post_type" value="' . get_current_screen_post_type() . '" />', $instance ); ?>
		<div class="search-container">
			<input class="product-search-box" type="search" value="<?php echo get_search_query() ?>" id="s" name="s" placeholder="<?php echo $label ?>" />
			<input class="product-search-submit" type="submit" id="searchsubmit" value="<?php echo $search_button_text ?>" />
		</div>
	</form>
	<?php
	do_action( 'ic_after_product_search_form', $instance );
}
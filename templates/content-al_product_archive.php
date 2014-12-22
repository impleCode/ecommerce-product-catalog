<?php
/**
 * The template for displaying products archive content.
 *
 * 
 *
 * @version		1.1.3
 * @package		ecommerce-product-catalog/templates
 * @author 		Norbert Dreszer
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $post; 
$default_archive_names = default_archive_names();
$multiple_settings = get_multiple_settings();
$archive_names = get_option( 'archive_names', $default_archive_names);

if (is_tax()) { $the_tax = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) ); 
$page_title = $archive_names['all_prefix'] .' '.$the_tax->name; }
else if (is_search()) {
$page_title = __('Search Results for:','al-ecommerce-product-catalog').' '.$_GET['s'];
}
else {$page_title = $archive_names['all_products']; }
echo product_breadcrumbs(); ?>
				

			
<article id="product_listing" <?php post_class('al_product'); ?>>
<header <?php post_class('entry-header'); ?>>
	<?php if (! is_tax() && ! is_search()) { content_product_adder_archive_before_title(); }
		else {
		echo '<h1 class="entry-title">'.$page_title.'</h1>';
		}	?>
</header> 
	<div class="entry-content">
		<?php $before_archive = content_product_adder_archive_before();
		$archive_template = get_option( 'archive_template', DEFAULT_ARCHIVE_TEMPLATE);
		$taxonomy_name = apply_filters('current_product_catalog_taxonomy', 'al_product-cat');
		if (! is_tax() && !is_search()) {			
			if ( $before_archive != '<div class="entry-summary"></div>') {
				echo $before_archive; 
			} 
			if ($multiple_settings['product_listing_cats'] == 'on') {
				if ($multiple_settings['cat_template'] != 'template') {
					$product_subcategories = wp_list_categories('show_option_none=No_cat&echo=0&title_li=&taxonomy='.$taxonomy_name.'&parent=0'); 
					if (!strpos($product_subcategories,'No_cat') ){
						echo '<div class="product-subcategories">'.$product_subcategories.'</div>';
					}
				}
				else {
					$show_categories = do_shortcode('[show_categories]');
					if (!empty($show_categories)) {
						echo '<div class="product-subcategories '.$archive_template.'">'.$show_categories;
						if ($archive_template != 'list') {
							echo '<hr>';
						}
						echo '</div>';
					}
				}
			}
		} 
		if (is_tax()) {
			$term = get_queried_object()->term_id; 
			$term_img = get_product_category_image_id($term);
			echo wp_get_attachment_image( $term_img, apply_filters('product_cat_image_size', 'large'));
			$term_description = term_description();
			if (! empty($term_description)) {
			echo '<div class="taxonomy-description">'.$term_description.'</div>'; }
			if ($multiple_settings['category_top_cats'] == 'on') {
				if ($multiple_settings['cat_template'] != 'template') {
				$product_subcategories = wp_list_categories('show_option_none=No_cat&echo=0&title_li=&taxonomy='.$taxonomy_name.'&child_of='.$term); 
				if (!strpos($product_subcategories,'No_cat') ){ ?>
					<div class="product-subcategories">
						<?php  echo $product_subcategories; ?> 
					</div>
				<?php }
				}
				else {
					$show_categories = do_shortcode('[show_categories parent='.get_queried_object_id().']');
					if (!empty($show_categories)) {
						echo '<div class="product-subcategories '.$archive_template.'">'.$show_categories;
						if ($archive_template != 'list') {
							echo '<hr>';
						}
						echo '</div>';
					}
				}
			}
		} 
		do_action('before_product_list', $archive_template, $multiple_settings);
		$product_list = '<div class="product-list">';
		while ( have_posts() ) : the_post(); 
			$product_list .= get_catalog_template($archive_template, $post);
		endwhile;
		$product_list .= '</div>';
		$product_list = apply_filters('product_list_ready', $product_list, $archive_template);
		echo $product_list;
		?><span class="clear"></span>
	</div>
	
</article>
	<?php product_archive_pagination(); ?>
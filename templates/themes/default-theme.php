<?php
/**
 * Manages catalog default theme
 *
 * Here default theme is defined and managed.
 *
 * @version		1.1.4
 * @package		ecommerce-product-catalog/templates/themes
 * @author 		Norbert Dreszer
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

 function example_default_archive_theme() { 
 $default_modern_grid_settings = array (
	'attributes' => 1,
	);
$modern_grid_settings = get_option( 'modern_grid_settings', $default_modern_grid_settings); 
$modern_grid_settings['attributes'] = isset($modern_grid_settings['attributes']) ? $modern_grid_settings['attributes'] : '' ?>
 <div id="content">
 <a href="#default-theme"><div class="al_archive" style="background-image:url('<?php echo AL_PLUGIN_BASE_PATH .'templates/themes/img/example-product.jpg'; ?>'); background-position:center; ">
				<div class="product-name">White Lamp</div>
				<?php if ($modern_grid_settings['attributes'] == 1) {?>
				<div class="product-attributes">
				<table class="attributes-table">
				<tbody><tr><td>Height</td><td>20 </td></tr><tr><td>Color</td><td>White </td></tr>		
				</tbody></table>
				</div> <?php } ?>
				<div class="product-price">10 USD</div>
				</div></a> 
</div>
<?php }

function default_archive_theme($post) { 
$default_modern_grid_settings = array (
	'attributes' => 1,
	);
$modern_grid_settings = get_option( 'modern_grid_settings', $default_modern_grid_settings); 
$modern_grid_settings['attributes'] = isset($modern_grid_settings['attributes']) ? $modern_grid_settings['attributes'] : ''; ?>
			<a href="<?php the_permalink(); ?>"><div class="al_archive modern-grid-element" style='background-image:url(" <?php 
			$thumbnail_product = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
			if ($thumbnail_product[0]) {
				$url = $thumbnail_product[0]; 
			} 
			else {
				$url = default_product_thumbnail_url(); 
			}
			echo $url; ?>");'>
		
			<div class="product-name <?php design_schemes('box'); ?>"><?php the_title(); ?></div>
			<?php $attributes_number = get_option('product_attributes_number', DEF_ATTRIBUTES_OPTIONS_NUMBER);
			$at_val = '';
			$any_attribute_value = '';
			for ($i = 1; $i <= $attributes_number; $i++) {
				$at_val = get_post_meta($post->ID, "_attribute".$i, true);
				if (! empty($at_val)) {
					$any_attribute_value = $at_val.$i; }
			}
			if ($attributes_number > 0 AND ! empty($any_attribute_value) AND $modern_grid_settings['attributes'] == 1) { ?>
				<div class="product-attributes">
					<table class="attributes-table">
					<?php for ($i = 1; $i <= $attributes_number; $i++) { 
						$attribute_value = get_post_meta($post->ID, "_attribute".$i, true);
						if (! empty($attribute_value)) {
							echo '<tr><td>'. get_post_meta($post->ID, "_attribute-label".$i, true) . '</td><td>' . get_post_meta($post->ID, "_attribute".$i, true). ' '. get_post_meta($post->ID, "_attribute-unit".$i, true) .'</td></tr>'; } } ?>
					</table>
				</div> 
			<?php } 
			do_action('archive_price', $post); ?>
			
			</div></a>		
<?php } 

function get_default_archive_theme($post, $archive_template = null) {
	$archive_template = isset($archive_template) ? $archive_template : get_product_listing_template();
	$return = '';
	if ($archive_template == 'default') {
		$archive_price = apply_filters('archive_price_filter', '', $post);
		$default_modern_grid_settings = array(
			'attributes' => 1,
		);
		$modern_grid_settings = get_option('modern_grid_settings', $default_modern_grid_settings);
		$modern_grid_settings['attributes'] = isset($modern_grid_settings['attributes']) ? $modern_grid_settings['attributes'] : '';
		$thumbnail_product = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'full');
		if ($thumbnail_product[0]) {
			$url = $thumbnail_product[0];
            $img_class = '';
            $ratio = $thumbnail_product[1] / $thumbnail_product[2];
            if ($ratio <= 1.35 && $ratio > 1.20) {
                $img_class = ' class="higher"';
            }
            else if ($ratio <= 1.20) {
                $img_class = ' class="higher rect"';
            }
            else if ($ratio > 2) {
                $img_class = ' class="wider rect"';
            }
		} else {
			$url = default_product_thumbnail_url();
		}
        $product_name = get_the_title();
		$return = '<div class="al_archive modern-grid-element ' . product_listing_size_class($thumbnail_product) . '">';
        $return .= '<div class="pseudo"></div>';
        $return .= '<a href="' . get_permalink() . '"><img'.$img_class.' src="'. $url.'" alt="'.$product_name.'"">';
        $return .= '<h3 class="product-name ' . design_schemes('box', 0) . '">' . $product_name . '</h3>';
		$attributes_number = get_option('product_attributes_number', DEF_ATTRIBUTES_OPTIONS_NUMBER);
		$at_val = '';
		$any_attribute_value = '';
		for ($i = 1; $i <= $attributes_number; $i++) {
			$at_val = get_post_meta($post->ID, "_attribute" . $i, true);
			if (!empty($at_val)) {
				$any_attribute_value = $at_val . $i;
			}
		}
		if ($attributes_number > 0 AND !empty($any_attribute_value) AND $modern_grid_settings['attributes'] == 1) {
			$return .= '<div class="product-attributes"><table class="attributes-table">';
			for ($i = 1; $i <= $attributes_number; $i++) {
				$attribute_value = get_post_meta($post->ID, "_attribute" . $i, true);
				if (!empty($attribute_value)) {
					$return .= '<tr><td>' . get_post_meta($post->ID, "_attribute-label" . $i, true) . '</td><td>' . get_post_meta($post->ID, "_attribute" . $i, true) . ' ' . get_post_meta($post->ID, "_attribute-unit" . $i, true) . '</td></tr>';
				}
			}
			$return .= '</table></div>';
		}
		$return .= $archive_price . '</a></div>';
	}
return $return;
} 

function get_default_category_theme($product_cat, $archive_template) {
$thumbnail_product = wp_get_attachment_image_src(get_product_category_image_id($product_cat->term_id), 'large');
if ($thumbnail_product[0]) {
	$url = $thumbnail_product[0];
    $img_class = '';
    $ratio = $thumbnail_product[1] / $thumbnail_product[2];
    if ($ratio <= 1.35) {
        $img_class = ' class="higher"';
    }
} 
else {
	$url = default_product_thumbnail_url(); 
}
$return = '<div class="al_archive modern-grid-element '.product_listing_size_class($thumbnail_product).'">';
    //$return .= '<a class="pseudo-a" href="' . get_term_link($product_cat) . '"></a>';
    $return .= '<div class="pseudo"></div>';
    $return .= '<a href="' . get_term_link($product_cat) . '"><img'.$img_class.' src="'. $url.'" alt="'.$product_cat->name.'">';
    $return .= '<h3 class="product-name '. design_schemes('box', 0).'">'. $product_cat->name.'</h3></a>';
$return .= '</div>';
return $return;
}

function product_listing_size_class($image) {
	$class = '';
	if (is_array($image) && $image[1] > 1.7*$image[2]) {
		$class = 'wider-bg';
	}
	return $class;
}
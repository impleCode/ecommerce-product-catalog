<?php
/**
 * Manages custom names settings
 *
 * Here custom names settings are defined and managed.
 *
 * @version		1.1.4
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
 
function default_single_names() {
$single_names = array (
'product_price' => __('Product Price:', 'al-ecommerce-product-catalog'),
'product_sku' => __('SKU:', 'al-ecommerce-product-catalog'),
'product_shipping' => __('Product Shipping:', 'al-ecommerce-product-catalog'),
'product_features' => __('Product Features', 'al-ecommerce-product-catalog'),
'other_categories' => __('See also different:', 'al-ecommerce-product-catalog'),
'return_to_archive' => __('<< return to products', 'al-ecommerce-product-catalog'),
);

return $single_names;
}

function default_archive_names() {
$archive_names = array (
'all_products' => __('All Products', 'al-ecommerce-product-catalog'),
'all_prefix' => __('All', 'al-ecommerce-product-catalog'),
);

return $archive_names;
}
 
function custom_names_menu() { ?>
<a id="names-settings" class="nav-tab" href="<?php echo admin_url('edit.php?post_type=al_product&page=product-settings.php&tab=names-settings&submenu=single-names')?>	"><?php _e('Front-end Labels', 'al-ecommerce-product-catalog'); ?></a>
<?php }

add_action('settings-menu','custom_names_menu');

function custom_names_settings() {
register_setting('product_names_archive', 'archive_names');
register_setting('product_names_single', 'single_names');
}

add_action('product-settings-list','custom_names_settings');

function custom_names_content() { ?>
<div class="names-product-settings settings-wrapper"> <?php
	$tab = $_GET['tab'];
	$submenu = $_GET['submenu']; ?>
	<div class="settings-submenu">
		<h3>
		<a id="single-names" class="element current" href="<?php echo admin_url('edit.php?post_type=al_product&page=product-settings.php&tab=names-settings&submenu=single-names')?>"><?php _e('Single Product', 'al-ecommerce-product-catalog'); ?></a>
		<a id="archive-names" class="element" href="<?php echo admin_url('edit.php?post_type=al_product&page=product-settings.php&tab=names-settings&submenu=archive-names')?>"><?php _e('Product Listings', 'al-ecommerce-product-catalog'); ?></a>	
	</h3>
	</div><?php 
	if ($submenu == 'single-names') { ?>
		<div id="single_names" class="setting-content submenu">
			<script>
				jQuery('.settings-submenu a').removeClass('current');
				jQuery('.settings-submenu a#single-names').addClass('current');
			</script>
			<form method="post" action="options.php">
				<?php settings_fields('product_names_single'); 
				$default_single_names = default_single_names();
				$single_names = get_option( 'single_names', $default_single_names);
				$single_names['product_sku'] = isset($single_names['product_sku']) ? $single_names['product_sku'] : 'SKU:';		?>
				<h2><?php _e('Front-end Labels', 'al-ecommerce-product-catalog'); ?></h2>
				<h3><?php _e('Single Product Labels', 'al-ecommerce-product-catalog'); ?></h3>
				<table class="wp-list-table widefat product-settings-table" style="clear:right; text-align: left;">
					<thead><th><strong><?php _e('Front-end Element', 'al-ecommerce-product-catalog'); ?></strong></th><th><strong><?php _e('Front-end Text', 'al-ecommerce-product-catalog'); ?></strong></th></thead>
					<tbody>
						<tr><td><?php _e('Price Label', 'al-ecommerce-product-catalog'); ?></td><td><input type="text" name="single_names[product_price]" value="<?php echo $single_names['product_price']; ?>" /></td></tr>
						<tr><td><?php _e('SKU Label', 'al-ecommerce-product-catalog'); ?></td><td><input type="text" name="single_names[product_sku]" value="<?php echo $single_names['product_sku']; ?>" /></td></tr>
						<tr><td><?php _e('Shipping Label', 'al-ecommerce-product-catalog'); ?></td><td><input type="text" name="single_names[product_shipping]" value="<?php echo $single_names['product_shipping']; ?>" /></td></tr>
						<tr><td><?php _e('Features Label', 'al-ecommerce-product-catalog'); ?></td><td><input type="text" name="single_names[product_features]" value="<?php echo $single_names['product_features']; ?>" /></td></tr>
						<tr><td><?php _e('Another Categories', 'al-ecommerce-product-catalog'); ?></td><td><input type="text" name="single_names[other_categories]" value="<?php echo $single_names['other_categories']; ?>" /></td></tr>
						<tr><td><?php _e('Return to Products', 'al-ecommerce-product-catalog'); ?></td><td><input type="text" name="single_names[return_to_archive]" value="<?php echo $single_names['return_to_archive']; ?>" /></td></tr>
						<?php do_action('single_names_table', $single_names) ?>
					</tbody>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save changes', 'al-ecommerce-product-catalog'); ?>" />
				</p>	
			</form>
		</div>
		<div class="helpers"><div class="wrapper"><?php 
		main_helper(); ?>
		</div></div><?php 
	} 
	else if ($submenu == 'archive-names') { ?>
		<div id="archive_names" class="setting-content submenu">
		<script>
		jQuery('.settings-submenu a').removeClass('current');
		jQuery('.settings-submenu a#archive-names').addClass('current');
		</script>
		<form method="post" action="options.php"><?php 
		settings_fields('product_names_archive'); 
		$default_archive_names = default_archive_names();
		$archive_names = get_option( 'archive_names', $default_archive_names); ?>
		<h2><?php _e('Front-end Labels', 'al-ecommerce-product-catalog'); ?></h2><?php
		$disabled = '';
		if (get_integration_type() == 'simple') { 
			$disabled = 'disabled'; 
			implecode_warning(sprintf(__('Product listing pages are disabled with simple theme integration. See <a href="%s">Theme Integration Guide</a> to enable product listing pages.', 'al-ecommerce-product-catalog'), 'http://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=simple-mode&key=front-labels'));
		} ?>
		<h3><?php _e('Product Listing Labels', 'al-ecommerce-product-catalog'); ?></h3>
		<table class="wp-list-table widefat product-settings-table" style="clear:right; text-align: left;">
			<thead><th><strong><?php _e('Front-end Element', 'al-ecommerce-product-catalog'); ?></strong></th><th><strong><?php _e('Front-end Text', 'al-ecommerce-product-catalog'); ?></strong></th></thead>
			<tbody>
				<tr><td><?php _e('Product Archive Title', 'al-ecommerce-product-catalog'); ?></td><td><input type="text" <?php echo $disabled ?> name="archive_names[all_products]" value="<?php echo $archive_names['all_products']; ?>" /></td></tr>
				<tr><td><?php _e('Category Prefix', 'al-ecommerce-product-catalog'); ?></td><td><input type="text" <?php echo $disabled ?> name="archive_names[all_prefix]" value="<?php echo $archive_names['all_prefix']; ?>" /></td></tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" <?php echo $disabled ?> class="button-primary" value="<?php _e('Save changes', 'al-ecommerce-product-catalog'); ?>" />
		</p>	
		</form>
		</div>
		<div class="helpers"><div class="wrapper"><?php 
		main_helper(); ?>
		</div></div><?php 
	} do_action('names-settings'); ?>
</div><?php 
}
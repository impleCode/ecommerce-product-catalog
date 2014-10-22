<?php
/**
 * Manages product settings
 *
 * Here product settings are defined and managed.
 *
 * @version		1.1.4
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
 


function register_product_extensions() {
    add_submenu_page('edit.php?post_type=al_product', __('Extensions', 'al-ecommerce-product-catalog'), '<span class="extensions">'.__('Extensions', 'al-ecommerce-product-catalog').'</span>', 'read_private_products', basename(__FILE__), 'product_extensions');
}

add_action('product_settings_menu' , 'register_product_extensions'); 


function product_extensions() { ?>

    <div id="implecode_settings" class="wrap">
	<h2><?php _e('Product Settings', 'al-ecommerce-product-catalog') ?> - impleCode eCommerce Product Catalog</h2>
		<h2 class="nav-tab-wrapper">
			<a id="extensions" class="nav-tab" href="<?php echo admin_url('edit.php?post_type=al_product&page=extensions.php&tab=product-extensions') ?>"><?php _e('Extensions', 'al-ecommerce-product-catalog'); ?></a>
		</h2>
		<?php $tab = isset($_GET['tab']) ? $_GET['tab'] : ''; 
		
		/*GENERAL SETTINGS*/
		
		if ($tab == 'product-extensions' OR $tab == '') { ?>
			<script>
				jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
				jQuery('.nav-tab-wrapper a#extensions').addClass('nav-tab-active');
			</script><?php
			if ( false === ( $extensions = get_transient( 'implecode_extensions_data' ) ) ) {
				$extensions = wp_remote_get('http://app.implecode.com/index.php?provide_extensions');
				if ( ! is_wp_error( $extensions ) || 200 != wp_remote_retrieve_response_code( $extensions )) {
					$extensions = json_decode( wp_remote_retrieve_body( $extensions ), true );
					if ( $extensions ) {
						set_transient( 'implecode_extensions_data', $extensions, 60*60*24*7 );
					}
				}
				else {
					$extensions = implecode_extensions();
				}
			}
			foreach ($extensions as $extension) {
				echo extension_box($extension['name'], $extension['url'], $extension['desc']);
			}
		} ?>
	<div style="clear:both; height: 50px;"></div>
	<div class="plugin-logo">
		<a href="http://implecode.com/#cam=catalog-settings-link&key=logo-link"><img class="en" src="<?php echo AL_PLUGIN_BASE_PATH .'img/implecode.png'; ?>" width="282px" alt="impleCode" /></a>
		</div>
    </div><?php
}

function implecode_extensions() {
$extensions = array(
array(
	'url' => 'premium-support',
	'name' => 'eCommerce Product Catalog Premium',
	'desc' => 'The premium version of eCommerce Product Catalog with more features & premium support.',
),
array(
	'url' => 'product-discounts',
	'name' => 'Product Discounts',
	'desc' => 'Apply percentage or value discounts for catalog products. Show the discount offers with a robust widget or shortcode and more!',
),
array(
	'url' => 'quote-form',
	'name' => 'Quote Form',
	'desc' => 'Improve the conversion rate with quote button which redirects to professional product quote form.',
),
array(
	'url' => 'order-form',
	'name' => 'Order Form',
	'desc' => 'This powerful extension allows you to sell individual products by adding order button into each product page.',
),
array(
	'url' => 'custom-product-order',
	'name' => 'Custom Product Order',
	'desc' => 'Set custom product order with new priority feature and sort products by price. Assign featured products.',
),
array(
	'url' => 'upload-pdf',
	'name' => 'Upload PDF',
	'desc' => 'Easily attach PDF files to the products, upload to server and provide to clients on product pages.',
),
array(
	'url' => 'product-search-pro',
	'name' => 'Product Search PRO',
	'desc' => 'Improve WordPress default search engine to provide better product search results. Show product search form with a shortcode.',
),
array(
	'url' => 'smarter-product-urls',
	'name' => 'Smarter Product URLs',
	'desc' => 'Set up SEO and USER friendly product page URLs. Add product category in product page URLs.',
),
array(
	'url' => 'product-gallery-advanced',
	'name' => 'Product Gallery Advanced',
	'desc' => 'Add unlimited number of product images and show them in a robust product slider or beautiful light-box presentation.',
),
array(
	'url' => 'smart-multiple-catalogs',
	'name' => 'Smart Multiple Catalogs',
	'desc' => 'Create completely separate, multiple catalogs at one website. Assign separate categories, parent URLs, manage them from different...',
),
array(
	'url' => 'drop-attributes',
	'name' => 'Drop-down Attributes',
	'desc' => 'Select attributes values with a drop-down. Define default drop-down values for each attribute in product settings.',
),

);
return $extensions;
}

function extension_box($name, $url, $desc) {
$return = '<div class="extension '.$url.'">
	<a target="_blank" href="http://implecode.com/wordpress/plugins/'.$url.'/#cam=extensions&key='.$url.'"><h3><span>'.$name.'</span></h3></a>
	<p>'.$desc.'</p>
	<p><a target="_blank" href="http://implecode.com/wordpress/plugins/'.$url.'/#cam=extensions&key='.$url.'" class="button-primary">Get this extension</a></p>
</div>';
return $return;
}
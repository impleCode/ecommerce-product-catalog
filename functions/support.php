<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages support settings
 *
 * Here support settings are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
if ( !function_exists( 'implecode_support_menu' ) ):

	function implecode_custom_support_menu() {
		?>
		<a id="support-settings" class="element" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=support' ) ?>"><?php _e( 'Support', 'ecommerce-product-catalog' ); ?></a>
		<?php
	}

	add_action( 'general_submenu', 'implecode_custom_support_menu', 20 );

	function implecode_custom_support_settings_content() {
		?>
		<?php $submenu = isset( $_GET[ 'submenu' ] ) ? $_GET[ 'submenu' ] : ''; ?>
		<?php if ( $submenu == 'support' ) { ?>
			<div class="setting-content submenu support-tab">
				<script>
			        jQuery( '.settings-submenu a' ).removeClass( 'current' );
			        jQuery( '.settings-submenu a#support-settings' ).addClass( 'current' );
				</script>
				<h2><?php _e( 'impleCode Support', 'ecommerce-product-catalog' ); ?></h2>
				<p><?php echo sprintf( __( '<b>%s is free to use</b>. That\'s great! It\'s a pleasure to serve it to you. Let\'s keep it free forever!', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ); ?></p><?php /*
			  <p><?php _e('If you found eCommerce Product Catalog useful or it saved you some amount of time please consider to support its development by buying premium support or an extension. This is better than a donation, because you get even more value with it. All the income from premium support and extensions goes for eCommerce Product Catalog and its extensions development. Everybody wins.','ecommerce-product-catalog'); ?></p> */ ?>
				<p><?php _e( 'This awesome plugin is developed under impleCode brand which is a legally operating company. It means that <b>you can be assured that the high quality development will be continuous</b>.', 'ecommerce-product-catalog' ) ?></p>
				<div style="clear: both; height: 10px;"></div>
				<div class="extension premium-support">
					<a href="https://implecode.com/wordpress/plugins/premium-support/#cam=catalog-support-tab&key=support-link"><h3><span><?php echo IC_CATALOG_PLUGIN_NAME ?> Premium</span></h3></a>
					<p><?php _e( '<b>One year</b> of high quality and speedy email', 'ecommerce-product-catalog' ) ?> <a href="https://implecode.com/wordpress/plugins/premium-support/#cam=catalog-support-tab&key=support-link">Premium Support</a> <?php _e( 'from impleCode support team for just', 'ecommerce-product-catalog' ) ?> $19.99.</p>
					<form style="text-align: center; position: relative; top: 10px;" action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="hosted_button_id" value="LCRGR95EST66S">
						<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<img alt="" border="0" src="https://www.paypalobjects.com/pl_PL/i/scr/pixel.gif" width="1" height="1">
					</form>
				</div>
				<div class="extension premium-support">
					<a href="https://implecode.com/wordpress/plugins/?cam=catalog-support-tab&key=extensions-link#extensions"><h3><span><?php echo IC_CATALOG_PLUGIN_NAME ?> Extensions</span></h3></a>
					<p><?php echo sprintf( __( '<b>Extensions</b> provide additional useful features. They improve %s in a field of <a href="%1$s">SEO</a>, <a href="%3$s">Productivity</a>, <a href="%2$s">Usability</a> and <a href="%4$s">Conversion</a>.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME, esc_url( 'https://implecode.com/wordpress/plugins/?cam=catalog-support-tab&key=extensions-link-seo#seo_usability_boosters' ), esc_url( 'https://implecode.com/wordpress/plugins/?cam=catalog-support-tab&key=extensions-link-usability#seo_usability_boosters' ), esc_url( 'https://implecode.com/wordpress/plugins/?cam=catalog-support-tab&key=extensions-link-productivity#productivity_boosters' ), esc_url( 'https://implecode.com/wordpress/plugins/?cam=catalog-support-tab&key=extensions-link-conversion#conversion_boosters' ) ) ?></p>
					<p><a href="https://implecode.com/wordpress/plugins/?cam=catalog-support-tab&key=extensions-link#extensions"><input style="cursor:pointer;" class="button-primary" type="button" value="Check out the extensions &raquo;"></a></p>
				</div>
				<div style="clear: both; height: 10px;"></div>
				<h2><?php _e( 'Premium Support Free Additions', 'ecommerce-product-catalog' ) ?></h2>
				<p><?php echo sprintf( __( 'Apart of fast, confidential email support <b>every premium support member will receive some advanced features</b> for %s as a free welcome gift.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ); ?></p>
				<h4><?php _e( 'Premium Features:', 'ecommerce-product-catalog' ) ?></h4>
				<ol>
					<li><?php _e( '<b>Product tags</b> - which is considered as SEO booster if used properly', 'ecommerce-product-catalog' ) ?>;</li>
					<li><?php _e( '<b>Product sidebar</b> on product page under the image', 'ecommerce-product-catalog' ) ?>;</li>
					<li><?php _e( '<b>Enable/disable sidebar</b> on product listing pages', 'ecommerce-product-catalog' ) ?>;</li>
					<li><?php _e( '<b>More styling</b> - set product sidebar and product description width', 'ecommerce-product-catalog' ) ?>;</li>
					<li><?php _e( '<b>Category widget enhancement</b> - Show child categories only on parent category pages', 'ecommerce-product-catalog' ) ?>.</li>
				</ol>
				<p><?php _e( 'Please just go ahead and use the Buy Now button above to receive the premium support service and the free additions immediately. You will receive the premium extension on your PayPal email address immediately after the payment is confirmed.', 'ecommerce-product-catalog' ); ?></p>
				<p><?php echo sprintf( __( 'If you need to get it on different email address please use the <a href="%s">impleCode website to order the premium support</a>. It will let you set different email address than the one for PayPal.', 'ecommerce-product-catalog' ), esc_url( 'https://implecode.com/wordpress/plugins/premium-support/#cam=catalog-support-tab&key=support-link-1' ) ); ?></p>
				<h2><?php _e( 'Theme Integration', 'ecommerce-product-catalog' ) ?></h2>
				<p><?php echo sprintf( __( 'As you may already know some themes may need Theme Integration to fully support %s. I wrote this <a href="%s">theme integrations guide</a>, however to make it even easier you will get <a href="%s">Advanced Theme Integration</a> service for free if you choose <a href="%s">Premium Support</a> service.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME, esc_url( 'https://implecode.com/wordpress/product-catalog/theme-integration-guide/#cam=catalog-support-tab&key=integration-link' ), esc_url( 'https://implecode.com/wordpress/plugins/advanced-theme-integration/#cam=catalog-support-tab&key=integration-service-link' ), esc_url( 'https://implecode.com/wordpress/plugins/premium-support/#cam=catalog-support-tab&key=support-link-2' ) ) ?></p>
				<h2><?php echo sprintf( __( '%s documentation', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME ) ?></h2>
				<p><?php echo sprintf( __( '<b>%4$s</b> documentation is being developed <a href="%1$s">here</a>. For questions about %4$s please use <a href="%2$s">support forum</a> or <a href="%3$s">Premium Support service</a>.', 'ecommerce-product-catalog' ), esc_url( 'https://implecode.com/wordpress/product-catalog/#cam=catalog-support-tab&key=docs-link' ), esc_url( 'http://wordpress.org/support/plugin/ecommerce-product-catalog' ), esc_url( 'https://implecode.com/wordpress/plugins/premium-support/#cam=catalog-support-tab&key=support-link-3' ), IC_CATALOG_PLUGIN_NAME ) ?></p>
				<?php /*
				  <h2><?php _e('Plugin Extensions', 'ecommerce-product-catalog') ?></h2>
				  <p><?php _e('For many users eCommerce Product Catalog standard features is more than enough. However for more specialized needs there are some extensions available. eCommerce Product Catalog extensions are divided into:', 'ecommerce-product-catalog') ?></p>
				  <table class="wp-list-table widefat">
				  <thead>
				  <th>
				  <b>SEO & USABILITY EXTENSIONS</b>
				  </th>
				  <th>
				  <b>PRODUCTIVITY EXTENSIONS</b>
				  </th>
				  <th>
				  <b>CONVERSION EXTENSIONS</b>
				  </th>
				  <thead>
				  <tbody>
				  <tr>
				  <td>
				  <ul class="support-ul">
				  <li><a target="_blank" href="https://implecode.com/wordpress/plugins/custom-product-order/#cam=catalog-support-tab&key=custom-product-order-link">Custom Product Order</a> - <?php _e('show products in custom order, show featured products always on top', 'ecommerce-product-catalog') ?></li>
				  <li><a target="_blank" href="https://implecode.com/wordpress/plugins/upload-pdf/#cam=catalog-support-tab&key=upload-pdf-link">Upload PDF</a> - <?php _e('a downloadable PDF attached to the product', 'ecommerce-product-catalog') ?></li>
				  <li><a target="_blank" href="https://implecode.com/wordpress/plugins/product-search-pro/#cam=catalog-support-tab&key=product-search-pro-link">Product Search PRO</a> - <?php _e('better product search, completely rewritten search engine', 'ecommerce-product-catalog') ?></li>
				  <li><a target="_blank" href="https://implecode.com/wordpress/plugins/smarter-product-urls/#cam=catalog-support-tab&key=smarter-product-urls-link">Smarter Product URLs</a> - <?php _e('URL structure improved for SEO and Usability purpose', 'ecommerce-product-catalog') ?></li>
				  <li><a target="_blank" href="https://implecode.com/wordpress/plugins/product-gallery-advanced/#cam=catalog-support-tab&key=gallery-advanced-link">Product Gallery Advanced</a> - <?php _e('add more images and show them in lightbox', 'ecommerce-product-catalog') ?></li>
				  <li><a target="_blank" href="https://implecode.com/wordpress/plugins/smart-multiple-catalogs/#cam=catalog-support-tab&key=smart-multiple-catalogs-link">Smart Multiple Catalogs</a> - <?php _e('create completely separate product catalogs, with separate categories and URL structure, even with separate management in WP Admin', 'ecommerce-product-catalog') ?></li>
				  </ul>
				  </td>
				  <td>
				  <ul class="support-ul">
				  <li><a target="_blank" href="https://implecode.com/wordpress/plugins/drop-attributes/#cam=catalog-support-tab&key=drop-down-attributes-link">Drop-down Attributes</a> - <?php _e('limit possible values for certain attributes', 'ecommerce-product-catalog') ?></li>
				  </ul>
				  </td>
				  <td>
				  <ul class="support-ul">
				  <li><a target="_blank" href="https://implecode.com/wordpress/plugins/quote-form/#cam=catalog-support-tab&key=quote-form-link">Quote Form</a> - <?php _e('allow users to ask for a quote for individual products', 'ecommerce-product-catalog') ?></li>
				  <li><a target="_blank" href="https://implecode.com/wordpress/plugins/paypal-gateway/#cam=catalog-support-tab&key=paypal-gateway-link">PayPal Gateway</a> - <?php _e('allow fast PayPal payments through order form', 'ecommerce-product-catalog') ?></li>
				  <li><a target="_blank" href="https://implecode.com/wordpress/plugins/order-form/#cam=catalog-support-tab&key=order-form-link">Order Form</a> - <?php _e('allow users to order individual products (not a shopping cart), good when users buy only one product at a time', 'ecommerce-product-catalog') ?></li>
				  </ul>
				  </td>
				  </tr>
				  </tbody>
				  </table> */ ?>
				<p style="border: 1px solid; padding: 10px 10px 26px 10px;">
					<a style="float: left; margin-right: 10px;" href="https://implecode.com/wordpress/plugins/premium-support/#cam=catalog-support-tab&key=donation-support-link"><img height="60px" src="<?php echo AL_PLUGIN_BASE_PATH . 'img/do-not-donate.png' ?>" /></a><?php echo sprintf( __( '<b>Please do not donate</b> (we finance the %3$s development from <a href="%1$s">premium support</a> and <a href="%2$s">extensions</a>)', 'ecommerce-product-catalog' ), esc_url( 'https://implecode.com/wordpress/plugins/premium-support/#cam=catalog-support-tab&key=donation-support-link' ), esc_url( 'https://implecode.com/wordpress/plugins/?cam=catalog-support-tab&key=donation-extensions-link#extensions' ), IC_CATALOG_PLUGIN_NAME ) ?>.
				</p>
			</div>
			<div class="helpers"><div class="wrapper"><?php
					main_helper();
					did_know_helper( 'support', __( 'You can get instant premium support from plugin developers', 'ecommerce-product-catalog' ), 'https://implecode.com/wordpress/plugins/premium-support/' );
					ic_bug_report();
					review_helper();
					?>
				</div></div><?php
		}
	}

	add_action( 'product-settings', 'implecode_custom_support_settings_content' );

	add_action( 'admin_init', 'ic_disable_ic_updater', 4 );

	/**
	 * Disables premium updates and support on demand
	 *
	 */
	function ic_disable_ic_updater() {
		if ( get_option( 'ic_disable_license_message' ) == 1 ) {
			add_action( 'admin_init', 'ic_disable_license_message', 6 );
		}
		if ( get_option( 'ic_disable_ic_updater' ) == 1 ) {
			if ( !function_exists( 'start_implecode_updater' ) ) {

				function start_implecode_updater() {

				}

			}
			if ( !function_exists( 'implecode_support_menu' ) ) {

				function implecode_support_menu() {

				}

			}
		}
	}

	/**
	 * Disables premium license check message
	 *
	 */
	function ic_disable_license_message() {
		remove_action( 'admin_init', 'check_if_license_exists', 99 );
	}























endif;
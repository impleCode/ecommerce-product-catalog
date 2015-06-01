<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages shipping settings
 *
 * Here shipping settings are defined and managed.
 *
 * @version		1.1.4
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
function shipping_menu() {
	?>
	<a id="shipping-settings" class="nav-tab" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=shipping-settings&submenu=shipping' ) ?>"><?php _e( 'Product shipping', 'al-ecommerce-product-catalog' ); ?></a>
	<?php
}

// add_action('general_submenu','shipping_menu'); // UNCOMMENT TO INSERT IN FIRST TAB and change url above
add_action( 'settings-menu', 'shipping_menu' );

function shipping_settings() {
	register_setting( 'product_shipping', 'product_shipping_options_number' );
	register_setting( 'product_shipping', 'display_shipping' );
	register_setting( 'product_shipping', 'product_shipping_cost' );
	register_setting( 'product_shipping', 'product_shipping_label' );
}

add_action( 'product-settings-list', 'shipping_settings' );

function shipping_settings_content() {
	$submenu = $_GET[ 'submenu' ];
	?>
	<div class="shipping-product-settings settings-wrapper" style="clear:both;">
		<div class="settings-submenu">
			<h3>
				<a id="shipping-settings" class="element current" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=shipping-settings&submenu=shipping' ) ?>"><?php _e( 'Shipping Settings', 'al-ecommerce-product-catalog' ); ?></a>
				<?php do_action( 'shipping_submenu' ); ?>
			</h3>
		</div><?php if ( $submenu == 'shipping' ) { ?>
			<div class="setting-content submenu">
				<script>
		            jQuery( '.settings-submenu a' ).removeClass( 'current' );
		            jQuery( '.settings-submenu a#shipping-settings' ).addClass( 'current' );
				</script>
				<h2><?php _e( 'Shipping Settings', 'al-ecommerce-product-catalog' ); ?></h2>
				<form method="post" action="options.php">
					<?php settings_fields( 'product_shipping' ); ?>
					<h3><?php _e( 'Product shipping options', 'al-ecommerce-product-catalog' ); ?></h3>
					<table>
						<tr>
							<td colspan="2"><?php _e( 'Number of shipping options', 'al-ecommerce-product-catalog' ); ?> <input size="30" type="number" step="1" min="0" name="product_shipping_options_number" id="admin-number-field" value="<?php echo get_option( 'product_shipping_options_number', DEF_SHIPPING_OPTIONS_NUMBER ); ?>" /><input type="submit" class="button" value="<?php _e( 'Update', 'al-ecommerce-product-catalog' ); ?>" /></td>
						</tr>
					</table>
					<?php
					$shipping_count = get_option( 'product_shipping_options_number', DEF_SHIPPING_OPTIONS_NUMBER );
					if ( $shipping_count > 0 ) {
						?>
						<div class="al-box info"><p><?php _e( "If you fill out the fields below, system will automatically pre-fill the fields on product pages so you doesn't have to fill them every time you add product.</p><p>When every product in your catalogue has different shipping options you can leave all or just a part of these fields empty.", 'al-ecommerce-product-catalog' ); ?></p><p><?php _e( 'You can change these default values on every product page.', 'al-ecommerce-product-catalog' ); ?></p></div>

						<table class="wp-list-table widefat product-settings-table dragable">
							<thead><tr><th></th><th class="title"><b><?php _e( 'Shipping default name', 'al-ecommerce-product-catalog' ); ?></b></th><th></th><th class="title"><b><?php _e( 'Shipping default cost', 'al-ecommerce-product-catalog' ); ?></b></th><th class="dragger"></th></tr></thead><tbody>
								<?php
								$shipping_cost	 = get_option( 'product_shipping_cost', DEF_VALUE );
								$shipping_label	 = get_option( 'product_shipping_label' );
								for ( $i = 1; $i <= $shipping_count; $i++ ) {
									$shipping_label[ $i ]	 = isset( $shipping_label[ $i ] ) ? $shipping_label[ $i ] : '';
									$shipping_cost[ $i ]	 = isset( $shipping_cost[ $i ] ) ? $shipping_cost[ $i ] : '';
									// Echo out the field
									echo '<tr><td class="lp-column">' . $i . '.</td><td class="product-shipping-label-column"><input class="product-shipping-label" type="text" name="product_shipping_label[' . $i . ']" value="' . $shipping_label[ $i ] . '" /></td><td class="lp-column">:</td><td><input id="admin-number-field" class="product-shipping-cost" type="number" min="0" name="product_shipping_cost[' . $i . ']" value="' . $shipping_cost[ $i ] . '" /> ' . product_currency() . '</td><td class="dragger"></td></tr>';
								}
								?>
							</tbody></table>
			<?php //do_action('product-attributes');   ?>
						<p class="submit">
							<input type="submit" class="button-primary" value="<?php _e( 'Save changes', 'al-ecommerce-product-catalog' ); ?>" />
						</p>
		<?php } else { ?>
						<tr><td colspan="2">
								<div class="al-box warning"><?php _e( 'Shipping disabled. To enable set minimum 1 shipping option.', 'al-ecommerce-product-catalog' ); ?></div>
							</td></tr>
						</table>
		<?php } ?>

				</form>
			</div>
			<div class="helpers"><div class="wrapper"><?php main_helper(); ?>
				</div></div>

	<?php } do_action( 'product-shipping' ); ?>
	</div><?php
}

add_action( 'general_settings', 'shipping_settings_content' );

<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages attributes settings
 *
 * Here attributes settings are defined and managed.
 *
 * @version		1.1.4
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
function attributes_menu() {
	?>
	<a id="attributes-settings" class="nav-tab"  href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=attributes-settings&submenu=attributes' ) ?>"><?php _e( 'Product attributes', 'al-ecommerce-product-catalog' ); ?></a>
	<?php
}

// add_action('general_submenu','attributes_menu'); // UNCOMMENT TO INSERT IN FIRST TAB and change url above
add_action( 'settings-menu', 'attributes_menu' );

function attributes_settings() {
	register_setting( 'product_attributes', 'product_attributes_number' );
	register_setting( 'product_attributes', 'al_display_attributes' );
	register_setting( 'product_attributes', 'product_attribute' );
	register_setting( 'product_attributes', 'product_attribute_label' );
	register_setting( 'product_attributes', 'product_attribute_unit' );
}

add_action( 'product-settings-list', 'attributes_settings' );

function attributes_settings_content() {
	$submenu = $_GET[ 'submenu' ];
	?>
	<div class="attributes-product-settings settings-wrapper" style="clear:both;">
		<div class="settings-submenu">
			<h3>
				<a id="attributes-settings" class="element current" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=attributes-settings&submenu=attributes' ) ?>"><?php _e( 'Attributes Settings', 'al-ecommerce-product-catalog' ); ?></a>
	<?php do_action( 'attributes_submenu' ); ?>
			</h3>
		</div>
	<?php if ( $submenu == 'attributes' ) { ?>
			<div class="setting-content submenu">
				<script>
					jQuery( '.settings-submenu a' ).removeClass( 'current' );
					jQuery( '.settings-submenu a#attributes-settings' ).addClass( 'current' );
				</script>
				<h2><?php _e( 'Attributes Settings', 'al-ecommerce-product-catalog' ); ?></h2>
				<form method="post" action="options.php">
		<?php settings_fields( 'product_attributes' ); ?>
					<h3><?php _e( 'Product attributes options', 'al-ecommerce-product-catalog' ); ?></h3>
					<table>
						<tr>
							<td colspan="2"><?php _e( 'Number of product attributes', 'al-ecommerce-product-catalog' ); ?> <input size="30" type="number" step="1" min="0" name="product_attributes_number" id="admin-number-field" value="<?php echo get_option( 'product_attributes_number', DEF_ATTRIBUTES_OPTIONS_NUMBER ); ?>" /><input type="submit" class="button" value="<?php _e( 'Update', 'al-ecommerce-product-catalog' ); ?>" />
							</td>
						</tr>
					</table>
					<?php
					$attributes_count = get_option( 'product_attributes_number', DEF_ATTRIBUTES_OPTIONS_NUMBER );
					if ( $attributes_count > 0 ) {
						?>
						<div class="al-box info">
							<p><?php _e( "If you fill out the fields below, system will automatically pre-fill the fields on product pages so you doesn't have to fill them every time you add product.</p><p>When every product in your catalogue is different you can leave all or a part of these field empty.", 'al-ecommerce-product-catalog' ); ?></p><p><?php _e( 'You can change these default values on every product page.', 'al-ecommerce-product-catalog' ); ?></p>
						</div>
						<table  class="wp-list-table widefat product-settings-table dragable">
							<thead>
								<tr>
									<th class="title"></th><th class="title"><b><?php _e( 'Attribute default name', 'al-ecommerce-product-catalog' ); ?></b></th>
									<th></th>
									<th class="title"><b><?php _e( 'Attribute default value', 'al-ecommerce-product-catalog' ); ?></b></th>
									<th class="title"><b><?php _e( 'Attribute default unit', 'al-ecommerce-product-catalog' ); ?></b></th>
			<?php do_action( 'product_attributes_settings_table_th' ); ?>
									<th class="dragger"></th>
								</tr>
							</thead>
							<tbody><?php
								$attribute		 = get_option( 'product_attribute' );
								$attribute_label = get_option( 'product_attribute_label' );
								$attribute_unit	 = get_option( 'product_attribute_unit' );
								for ( $i = 1; $i <= get_option( 'product_attributes_number', '3' ); $i++ ) {
									$attribute_label[ $i ]	 = isset( $attribute_label[ $i ] ) ? $attribute_label[ $i ] : '';
									$attribute[ $i ]		 = isset( $attribute[ $i ] ) ? $attribute[ $i ] : '';
									$attribute_unit[ $i ]	 = isset( $attribute_unit[ $i ] ) ? $attribute_unit[ $i ] : '';
									?>
									<tr>
										<td class="lp-column lp'.$i.'"><?php echo $i ?>.</td>
										<td class="product-attribute-label-column"><input class="product-attribute-label" type="text" name="product_attribute_label[<?php echo $i ?>]" value="<?php echo $attribute_label[ $i ] ?>" /></td><td class="lp-column">:</td>
										<td><input id="admin-number-field" class="product-attribute" type="text" name="product_attribute[<?php echo $i ?>]" value="<?php echo $attribute[ $i ] ?>" /></td>
										<td><input id="admin-number-field" class="product-attribute-unit" type="text" name="product_attribute_unit[<?php echo $i ?>]" value="<?php echo $attribute_unit[ $i ] ?>" /></td>
									<?php do_action( 'product_attributes_settings_table_td', $i ); ?>
										<td class="dragger"></td>
									</tr> <?php }
								?>
							</tbody>
						</table>
			<?php do_action( 'attributes-settings' ); ?>
						<p class="submit">
							<input type="submit" class="button-primary" value="<?php _e( 'Save changes', 'al-ecommerce-product-catalog' ); ?>" />
						</p><?php
		} else {
			?>
						<table>
							<tr>
								<td colspan="2">
									<div class="al-box warning"><?php _e( 'Attributes disabled. To enable set minimum 1 attribute.', 'al-ecommerce-product-catalog' ); ?></div>
								</td>
							</tr>
						</table><?php }
		?>
				</form>
			</div>
			<div class="helpers"><div class="wrapper"><?php
					main_helper();
					doc_helper( __( 'attributes', 'al-ecommerce-product-catalog' ), 'product-attributes' )
					?>
				</div></div>
	<?php } do_action( 'product-attributes' ); ?>
	</div><?php
}

add_action( 'general_settings', 'attributes_settings_content' );

/**
 * Returns the number of defined product attributes
 *
 * @return int
 */
function product_attributes_number() {
	return get_option( 'product_attributes_number', DEF_ATTRIBUTES_OPTIONS_NUMBER );
}

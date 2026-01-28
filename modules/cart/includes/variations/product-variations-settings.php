<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product variations
 *
 * Here product variations functions are defined and managed.
 *
 * @version        1.0.0
 * @package        implecode-quote-cart/includes
 * @author        Norbert Dreszer
 */
function product_variations_menu() {
	?>
	<a id="product-variations-settings" class="element"
	   href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=attributes-settings&submenu=product-variations' ) ?>"><?php _e( 'Product Variations', 'ecommerce-product-catalog' ); ?> </a><?php
}

add_action( 'attributes_submenu', 'product_variations_menu' );

function product_variations_settings() {
	register_setting( 'product_variations', 'product_variations_settings' );
	//do_action( 'product_variations_settings' );
}

add_action( 'product-settings-list', 'product_variations_settings' );

function get_product_variations_settings() {
	$product_variations_settings = get_option( 'product_variations_settings', array() );
	if ( ! is_array( $product_variations_settings ) ) {
		$product_variations_settings = array();
	}
	$product_variations_settings['count']  = isset( $product_variations_settings['count'] ) ? $product_variations_settings['count'] : 2;
	$product_variations_settings['labels'] = isset( $product_variations_settings['labels'] ) ? $product_variations_settings['labels'] : array();
	$product_variations_settings['values'] = isset( $product_variations_settings['values'] ) ? $product_variations_settings['values'] : array();
	$product_variations_settings['prices'] = isset( $product_variations_settings['prices'] ) ? $product_variations_settings['prices'] : array();
	$product_variations_settings['mod']    = isset( $product_variations_settings['mod'] ) ? $product_variations_settings['mod'] : array();
	$product_variations_settings['mode']   = isset( $product_variations_settings['mode'] ) ? $product_variations_settings['mode'] : 'normal';
	$product_variations_settings['info']   = isset( $product_variations_settings['info'] ) ? $product_variations_settings['info'] : 'full-price';

	return apply_filters( 'product_variations_settings', $product_variations_settings );
}

function product_variations_settings_content() {
	$submenu = isset( $_GET['submenu'] ) ? $_GET['submenu'] : '';
	if ( $submenu == 'product-variations' ) {
		?>
		<script>
            jQuery('.settings-submenu a').removeClass('current');
            jQuery('.settings-submenu a#product-variations-settings').addClass('current');
		</script>
		<div class="product-variations-settings setting-content submenu">
			<form method="post" action="options.php">
				<h2><?php _e( 'Product Variations Settings', 'ecommerce-product-catalog' ); ?></h2>
				<?php
				settings_fields( 'product_variations' );
				$product_variations_settings = get_product_variations_settings();
				?>
				<table>
					<tr>
						<td colspan="2"><?php _e( 'Number of product variations', 'ecommerce-product-catalog' ); ?>
							<input size="30" type="number" step="1" min="0" name="product_variations_settings[count]"
							       id="admin-number-field"
							       value="<?php echo $product_variations_settings['count']; ?>"/><input type="submit"
							                                                                            class="button"
							                                                                            value="<?php _e( 'Update', 'ecommerce-product-catalog' ); ?>"/>
						</td>
					</tr>
				</table><?php
				if ( $product_variations_settings['count'] > 0 ) {
					echo '<table>';
					if ( ic_is_variations_price_effect_active() ) {
						implecode_settings_radio( __( 'Variation price info', 'ecommerce-product-catalog' ), 'product_variations_settings[info]', $product_variations_settings['info'], array(
							'full-price'   => __( 'Show full price if possible to calculate', 'ecommerce-product-catalog' ),
							'price-effect' => __( 'Show price effect only', 'ecommerce-product-catalog' ),
							'no-info'      => __( 'Disable variation price info', 'ecommerce-product-catalog' )
						) );
					}
					echo '</table>';
					?>
					<style>.product-variations-settings table td textarea {
                            min-height: 100px;
                        }</style>
					<div class="al-box info">
						<p><?php _e( "If you fill out the fields below, the system will automatically pre-fill the fields on product pages so you doesn't have to fill them every time you add product.", "ecommerce-product-catalog" ) ?></p>
						<p><?php _e( "When every product in your catalogue is different you can leave all or a part of these fields empty.", 'ecommerce-product-catalog' ); ?></p>
						<p><?php _e( 'You can change these default values on every product page.', 'ecommerce-product-catalog' ); ?></p>
					</div>
					<table class="wp-list-table widefat product-settings-table">
					<thead>
					<tr>
						<th class="title"></th>
						<th class="title"><b><?php _e( 'Variation default name', 'ecommerce-product-catalog' ); ?></b>
						</th>
						<th></th>
						<th class="title"><b><?php _e( 'Variation default values', 'ecommerce-product-catalog' ); ?></b>
						</th>
						<?php
						$mod_drop = false;
						if ( ic_is_variations_price_effect_active() ) {
							$mod_drop = true;
							?>
							<th class="title">
								<b><?php _e( 'Variation default price effects', 'ecommerce-product-catalog' ); ?></b>
							</th>
						<?php } ?>
						<?php
						if ( ic_is_variations_shipping_effect_active() ) {
							$mod_drop = true;
							?>
							<th class="title">
								<b><?php _e( 'Variation default shipping effects', 'ecommerce-product-catalog' ); ?></b>
							</th>
							<?php
						}
						if ( $mod_drop ) {
							?>
							<th></th>
						<?php }
						?>

					</tr>
					</thead>
					<tbody><?php
					for ( $i = 1; $i <= $product_variations_settings['count']; $i ++ ) {
						$product_variations_settings['labels'][ $i ]   = isset( $product_variations_settings['labels'][ $i ] ) ? $product_variations_settings['labels'][ $i ] : '';
						$product_variations_settings['values'][ $i ]   = isset( $product_variations_settings['values'][ $i ] ) ? $product_variations_settings['values'][ $i ] : '';
						$product_variations_settings['prices'][ $i ]   = isset( $product_variations_settings['prices'][ $i ] ) ? $product_variations_settings['prices'][ $i ] : '';
						$product_variations_settings['shipping'][ $i ] = isset( $product_variations_settings['shipping'][ $i ] ) ? $product_variations_settings['shipping'][ $i ] : '';
						$product_variations_settings['mod'][ $i ]      = isset( $product_variations_settings['mod'][ $i ] ) ? $product_variations_settings['mod'][ $i ] : '+';
						?>
						<tr>
							<td class="lp-column lp'.$i.'"><?php echo $i ?>.</td>
							<td class="product-variation-label-column"><input class="product-variation-label"
							                                                  type="text"
							                                                  name="product_variations_settings[labels][<?php echo $i ?>]"
							                                                  value="<?php echo $product_variations_settings['labels'][ $i ] ?>"/>
							</td>
							<td class="lp-column">:</td>
							<td><textarea
									name="product_variations_settings[values][<?php echo $i ?>]"><?php echo $product_variations_settings['values'][ $i ] ?></textarea>
							</td>
							<?php
							if ( ic_is_variations_price_effect_active() ) {
								?>
								<td><textarea
										name="product_variations_settings[prices][<?php echo $i ?>]"><?php echo $product_variations_settings['prices'][ $i ] ?></textarea>
								</td>
							<?php } ?>
							<?php
							if ( ic_is_variations_shipping_effect_active() ) {
								?>
								<td><textarea
										name="product_variations_settings[shipping][<?php echo $i ?>]"><?php echo $product_variations_settings['shipping'][ $i ] ?></textarea>
								</td>
								<?php
							}
							if ( $mod_drop ) {
								?>
								<td><?php echo ic_cart_variations_meta::get_price_modificator_dropdown( 'product_variations_settings[mod][' . $i . ']', $product_variations_settings['mod'][ $i ] ) ?></td>
							<?php } ?>
						</tr> <?php }
					?>
					</tbody>
					</table><?php
				} else {
					?>
					<table>
						<tr>
							<td colspan="2">
								<div
									class="al-box warning"><?php _e( 'Product Variations disabled. To enable set minimum 1 variation.', 'ecommerce-product-catalog' ); ?></div>
							</td>
						</tr>
					</table> <?php
				}
				do_action( 'product-variations-settings' );
				?>
				<p class="submit">
					<input type="submit" class="button-primary"
					       value="<?php _e( 'Save changes', 'ecommerce-product-catalog' ); ?>"/>
				</p>

			</form>
		</div>
		<?php
	}
}

add_action( 'product-attributes', 'product_variations_settings_content' );

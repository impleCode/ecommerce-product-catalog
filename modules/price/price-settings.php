<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
add_action( 'inside_color_schemes_settings_table', 'ic_price_design_schemes' );

/**
 * Shows price design schemes
 *
 * @param type $design_schemes
 */
function ic_price_design_schemes( $design_schemes ) {
	?>
	<tr>
		<td><label for="design_schemes[price-size]"><?php _e( 'Price Size', 'ecommerce-product-catalog' ); ?></label></td>
		<td><select id="single_price" name="design_schemes[price-size]">
				<option name="design_schemes[big-price]" value="big-price"<?php selected( 'big-price', $design_schemes[ 'price-size' ] ); ?>><?php _e( 'Big', 'ecommerce-product-catalog' ); ?></option>
				<option name="design_schemes[small-price]" value="small-price"<?php selected( 'small-price', $design_schemes[ 'price-size' ] ); ?>><?php _e( 'Small', 'ecommerce-product-catalog' ); ?></option>
			</select></td>
		<td rowspan=2 class="price-value example <?php design_schemes(); ?>"><?php do_action( 'example_price' ); ?></td>
		<td><?php _e( 'single product', 'ecommerce-product-catalog' ); ?></td>
	</tr>
	<tr>
		<td><?php _e( 'Price Color', 'ecommerce-product-catalog' ); ?></td>
		<td>
			<select id="single_price" name="design_schemes[price-color]">
				<option name="design_schemes[red-price]" value="red-price"<?php selected( 'red-price', $design_schemes[ 'price-color' ] ); ?>><?php _e( 'Red', 'ecommerce-product-catalog' ); ?></option>
				<option name="design_schemes[orange-price]" value="orange-price"<?php selected( 'orange-price', $design_schemes[ 'price-color' ] ); ?>><?php _e( 'Orange', 'ecommerce-product-catalog' ); ?></option>
				<option name="design_schemes[green-price]" value="green-price"<?php selected( 'green-price', $design_schemes[ 'price-color' ] ); ?>><?php _e( 'Green', 'ecommerce-product-catalog' ); ?></option>
				<option name="design_schemes[blue-price]" value="blue-price"<?php selected( 'blue-price', $design_schemes[ 'price-color' ] ); ?>><?php _e( 'Blue', 'ecommerce-product-catalog' ); ?></option>
				<option name="design_schemes[grey-price]" value="grey-price"<?php selected( 'grey-price', $design_schemes[ 'price-color' ] ); ?>><?php _e( 'Grey', 'ecommerce-product-catalog' ); ?></option>
			</select>
		</td>
		<td><?php _e( 'single product', 'ecommerce-product-catalog' ); ?>, <?php _e( 'product archive', 'ecommerce-product-catalog' ); ?></td>
	</tr>
	<?php
}

add_action( 'single_names_table_start', 'ic_price_single_names' );

/**
 * Shows price product page labels settings
 *
 * @param type $single_names
 */
function ic_price_single_names( $single_names ) {
	?>
	<tr><td><?php _e( 'Price Label', 'ecommerce-product-catalog' ); ?></td><td><input type="text" name="single_names[product_price]" value="<?php echo esc_html( $single_names[ 'product_price' ] ); ?>" /></td></tr>
	<tr><td><?php _e( 'Free Product Text', 'ecommerce-product-catalog' ); ?></td><td><input type="text" name="single_names[free]" value="<?php echo esc_html( $single_names[ 'free' ] ); ?>" /></td></tr>
	<tr><td><?php _e( 'After Price Text', 'ecommerce-product-catalog' ); ?></td><td><input type="text" name="single_names[after_price]" value="<?php echo esc_html( $single_names[ 'after_price' ] ); ?>" /></td></tr>
	<?php
}

add_action( 'general-settings', 'ic_price_settings' );

/**
 * Shows price settings
 *
 */
function ic_price_settings() {
	$product_currency			 = get_product_currency_code();
	$product_currency_settings	 = get_currency_settings();
	?>
	<h3><?php _e( 'Payment and currency', 'ecommerce-product-catalog' ); ?></h3>
	<table id="payment_table">
		<thead>
			<?php implecode_settings_radio( __( 'Price', 'ecommerce-product-catalog' ), 'product_currency_settings[price_enable]', $product_currency_settings[ 'price_enable' ], array( 'on' => __( 'On', 'ecommerce-product-catalog' ), 'off' => __( 'Off', 'ecommerce-product-catalog' ) ), 1, __( 'Whether to enable or disable price functionality for the catalog.', 'ecommerce-product-catalog' ) ); ?>
		</thead>
		<tbody><?php do_action( 'payment_settings_table_start' ) ?>
			<tr>
				<td><span title="<?php _e( 'Select a currency from the list. If your currency is not available in the list, please use the Custom Currency Symbol option below.', 'ecommerce-product-catalog' ) ?>" class="dashicons dashicons-editor-help ic_tip"></span><?php _e( 'Your currency', 'ecommerce-product-catalog' ); ?>:</td>
				<td><select id="product_currency" name="product_currency">
						<?php
						$currencies					 = available_currencies();
						foreach ( $currencies as $currency ) :
							?>
							<option name="product_currency[<?php echo $currency; ?>]"
									value="<?php echo $currency; ?>"<?php selected( $currency, $product_currency ); ?>><?php echo $currency; ?></option>
								<?php endforeach; ?>
					</select></td>
			</tr>
			<?php
			implecode_settings_text( __( 'Custom Currency Symbol', 'ecommerce-product-catalog' ), 'product_currency_settings[custom_symbol]', $product_currency_settings[ 'custom_symbol' ], null, 1, 'small_text_box', __( 'If you choose custom currency symbol, it will override Your Currency setting and let you use any currency.', 'ecommerce-product-catalog' ) );
			implecode_settings_radio( __( 'Currency position', 'ecommerce-product-catalog' ), 'product_currency_settings[price_format]', $product_currency_settings[ 'price_format' ], array(
				'before' => __( 'Before Price', 'ecommerce-product-catalog' ),
				'after'	 => __( 'After Price', 'ecommerce-product-catalog' )
			)
			);
			implecode_settings_radio( __( 'Space between currency & price', 'ecommerce-product-catalog' ), 'product_currency_settings[price_space]', $product_currency_settings[ 'price_space' ], array( 'on' => __( 'On', 'ecommerce-product-catalog' ), 'off' => __( 'Off', 'ecommerce-product-catalog' ) ) );
			implecode_settings_text( __( 'Thousands Separator', 'ecommerce-product-catalog' ), 'product_currency_settings[th_sep]', $product_currency_settings[ 'th_sep' ], null, 1, 'small_text_box' );
			implecode_settings_text( __( 'Decimal Separator', 'ecommerce-product-catalog' ), 'product_currency_settings[dec_sep]', $product_currency_settings[ 'dec_sep' ], null, 1, 'small_text_box' );
			?>
		</tbody>
	</table>
	<script>jQuery( document ).ready( function () {
	        jQuery( "input[name=\"product_currency_settings[price_enable]\"]" ).change( function () {
	            if ( jQuery( this ).val() == 'off' && jQuery( this ).is( ':checked' ) ) {
	                jQuery( "#payment_table tbody" ).hide( "slow" );
	            } else {
	                jQuery( "#payment_table tbody" ).show( "slow" );
	            }
	        } );
	        jQuery( "input[name=\"product_currency_settings[price_enable]\"]" ).trigger( "change" );
	    } );</script>
	<?php
}

/**
 * Returns currency settings array(th_sep, dec_sep, price_enable)
 * @return array
 */
function get_currency_settings() {
	if ( $product_currency_settings = ic_get_global( 'product_currency_settings' ) ) {
		return $product_currency_settings;
	}
	$product_currency_settings						 = get_option( 'product_currency_settings', array(
		'custom_symbol'	 => '$',
		'price_format'	 => 'before',
		'price_space'	 => 'off',
		'price_enable'	 => 'on',
	) );
	global $wp_locale;
	$local[ 'mon_thousands_sep' ]					 = isset( $wp_locale->number_format[ 'thousands_sep' ] ) ? $wp_locale->number_format[ 'thousands_sep' ] : ',';
	$local[ 'decimal_point' ]						 = isset( $wp_locale->number_format[ 'decimal_point' ] ) ? $wp_locale->number_format[ 'decimal_point' ] : '.';
	$product_currency_settings[ 'th_sep' ]			 = isset( $product_currency_settings[ 'th_sep' ] ) ? $product_currency_settings[ 'th_sep' ] : $local[ 'mon_thousands_sep' ];
	$product_currency_settings[ 'dec_sep' ]			 = isset( $product_currency_settings[ 'dec_sep' ] ) ? $product_currency_settings[ 'dec_sep' ] : $local[ 'decimal_point' ];
	$product_currency_settings[ 'price_enable' ]	 = isset( $product_currency_settings[ 'price_enable' ] ) ? $product_currency_settings[ 'price_enable' ] : 'on';
	$product_currency_settings[ 'custom_symbol' ]	 = isset( $product_currency_settings[ 'custom_symbol' ] ) ? $product_currency_settings[ 'custom_symbol' ] : '$';
	$product_currency_settings[ 'price_format' ]	 = isset( $product_currency_settings[ 'price_format' ] ) ? $product_currency_settings[ 'price_format' ] : 'before';
	$product_currency_settings[ 'price_space' ]		 = isset( $product_currency_settings[ 'price_space' ] ) ? $product_currency_settings[ 'price_space' ] : 'off';
	$product_currency_settings						 = apply_filters( 'product_currency_settings', $product_currency_settings );
	ic_save_global( 'product_currency_settings', $product_currency_settings );
	return $product_currency_settings;
}

/**
 * Returns product currency code even if the currency symbol is set
 *
 * @return string
 */
function get_product_currency_code() {
	return get_option( 'product_currency', 'USD' );
}

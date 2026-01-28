<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product attributes
 *
 * Here all product attributes are defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */
add_action( 'settings-menu', 'shipping_menu', 30 );

/**
 * Shows shipping tab
 *
 */
function shipping_menu() {
	if ( current_user_can( 'manage_product_settings' ) ) {
		?>
        <a id="shipping-settings" class="nav-tab"
           href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=shipping-settings&submenu=shipping' ) ?>"><?php _e( 'Shipping', 'ecommerce-product-catalog' ); ?></a>
		<?php
	}
}

add_action( 'product-settings-list', 'register_shipping_settings' );

/**
 * Registers shipping settings
 *
 */
function register_shipping_settings() {
	register_setting( 'product_shipping', 'product_shipping_options_number' );
	register_setting( 'product_shipping', 'display_shipping' );
	register_setting( 'product_shipping', 'product_shipping_cost' );
	register_setting( 'product_shipping', 'product_shipping_label' );
	register_setting( 'product_shipping', 'general_shipping_settings' );
}

/**
 * Shows shipping settings
 *
 */
function shipping_settings_content() {
	$submenu = $_GET['submenu'];
	?>
    <div class="shipping-product-settings settings-wrapper" style="clear:both;">
    <div class="settings-submenu">
    <h3>
        <a id="shipping-settings" class="element current"
           href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=shipping-settings&submenu=shipping' ) ?>"><?php _e( 'Shipping Settings', 'ecommerce-product-catalog' ); ?></a>
		<?php do_action( 'shipping_submenu' ); ?>
    </h3>
    </div><?php if ( $submenu == 'shipping' ) { ?>
        <div class="setting-content submenu">
            <script>
                jQuery('.settings-submenu a').removeClass('current');
                jQuery('.settings-submenu a#shipping-settings').addClass('current');
            </script>
            <h2><?php _e( 'Shipping Settings', 'ecommerce-product-catalog' ); ?></h2>
            <form method="post" action="options.php">
				<?php
				settings_fields( 'product_shipping' );
				$shipping_count = get_shipping_options_number();
				?>
                <h3><?php _e( 'Shipping options', 'ecommerce-product-catalog' ); ?></h3>
                <table>
                    <tr>
                        <td colspan="2"><?php _e( 'Number of shipping options', 'ecommerce-product-catalog' ); ?> <input
                                    size="30" type="number" step="1" min="0" name="product_shipping_options_number"
                                    id="admin-number-field" value="<?php echo $shipping_count; ?>"/><input type="submit"
                                                                                                           class="button"
                                                                                                           value="<?php _e( 'Update', 'ecommerce-product-catalog' ); ?>"/>
                        </td>
                    </tr>
                </table>
				<?php
				ic_register_setting( __( 'Number of shipping options', 'ecommerce-product-catalog' ), 'product_shipping_options_number' );
				if ( $shipping_count > 0 ) {
					?>
                    <div class="al-box info">
                        <p><?php _e( "If you fill out the fields below, the system will automatically pre-fill the fields when adding a new item, so you don't have to fill them every time.", "ecommerce-product-catalog" ) ?></p>
                        <p><?php _e( "When every item in your catalogue has different shipping options you can leave all or just a part of these fields empty.", 'ecommerce-product-catalog' ) ?></p>
                        <p><?php _e( 'You can change these default values for every new item in the catalog.', 'ecommerce-product-catalog' ); ?></p>
                    </div>
                    <div class="settings-table-container" style="overflow-x: scroll;">
                        <table class="wp-list-table widefat product-settings-table dragable">
                            <thead>
                            <tr>
                                <th></th>
                                <th class="title">
                                    <b><?php _e( 'Shipping default name', 'ecommerce-product-catalog' ); ?></b></th>
                                <th></th>
                                <th class="title">
                                    <b><?php _e( 'Shipping default cost', 'ecommerce-product-catalog' ); ?></b></th>
                                <th class="dragger"></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
							$shipping_cost  = get_default_shipping_costs();
							$shipping_label = get_default_shipping_labels();
							$currency       = '';
							if ( function_exists( 'product_currency' ) ) {
								$currency = product_currency();
							}
							for ( $i = 1; $i <= $shipping_count; $i ++ ) {
								$shipping_label[ $i ] = isset( $shipping_label[ $i ] ) ? $shipping_label[ $i ] : '';
								$shipping_cost[ $i ]  = isset( $shipping_cost[ $i ] ) ? $shipping_cost[ $i ] : 0;
								// Echo out the field
								echo '<tr><td class="lp-column">' . $i . '.</td><td class="product-shipping-label-column"><input class="product-shipping-label" type="text" name="product_shipping_label[' . $i . ']" value="' . esc_html( $shipping_label[ $i ] ) . '" /></td><td class="lp-column">:</td><td><input id="admin-number-field" class="product-shipping-cost" type="number" min="0" step="0.01" name="product_shipping_cost[' . $i . ']" value="' . floatval( $shipping_cost[ $i ] ) . '" /> ' . $currency . '</td><td class="dragger"></td></tr>';
							}
							?>
                            </tbody>
                        </table>
                    </div>
					<?php
					$shipping_settings = get_general_shipping_settings();
					do_action( 'product-shipping-settings', $shipping_settings );
					?>
                    <p class="submit">
                        <input type="submit" class="button-primary"
                               value="<?php _e( 'Save changes', 'ecommerce-product-catalog' ); ?>"/>
                    </p>
				<?php } else { ?>
                    <tr>
                        <td colspan="2">
                            <div class="al-box warning"><?php _e( 'Shipping disabled. To enable set minimum 1 shipping option.', 'ecommerce-product-catalog' ); ?></div>
                        </td>
                    </tr>
                    </table>
				<?php } ?>

            </form>
        </div>
        <div class="helpers">
            <div class="wrapper"><?php main_helper(); ?>
            </div>
        </div>

		<?php
	}
	do_action( 'product-shipping' );
	?>
    </div><?php
}

//add_action( 'general_settings', 'shipping_settings_content' );

/**
 * Returns general shipping settings
 * @return type
 */
function get_general_shipping_settings() {
	$shiping_settings = get_option( 'general_shipping_settings' );
	if ( ! is_array( $shiping_settings ) ) {
		$shiping_settings = array();
	}

	return $shiping_settings;
}

/**
 * Returns default shipping labels
 *
 * @return type
 */
function get_default_shipping_labels() {
	$shipping_labels = get_option( 'product_shipping_label' );
	if ( empty( $shipping_labels ) ) {
		$shipping_labels = array();
	}

	return $shipping_labels;
}

/**
 * Returns default shipping costs
 *
 * @return type
 */
function get_default_shipping_costs() {
	$shipping_costs = get_option( 'product_shipping_cost' );
	if ( empty( $shipping_costs ) ) {
		$shipping_costs = array();
	}

	return $shipping_costs;
}

add_action( 'single_names_table_start', 'ic_shipping_single_names' );

/**
 * Shows price product page labels settings
 *
 * @param type $single_names
 */
function ic_shipping_single_names( $single_names ) {
	implecode_settings_text( __( 'Shipping Label', 'ecommerce-product-catalog' ), 'single_names[product_shipping]', $single_names['product_shipping'] );
	implecode_settings_text( __( 'Free Shipping Text', 'ecommerce-product-catalog' ), 'single_names[free_shipping]', $single_names['free_shipping'] );
}

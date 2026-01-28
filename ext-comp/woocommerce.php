<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *
 *  @version       1.0.0
 *  @author        impleCode
 *
 */

class ic_catalog_woocommerce {
	function __construct() {
		if ( class_exists( 'WooCommerce' ) ) {
			add_action( 'settings-menu', array( $this, 'woocommerce_menu' ), 51 );
			add_action( 'settings-content', array( $this, 'tab' ) );
			add_action( 'ic-woocommerce-settings', array( $this, 'content' ) );
		}
	}

	function woocommerce_menu() {
		if ( ! function_exists( 'start_ic_woocat' ) ) {
			$url = admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=woocommerce&submenu=woocommerce' );
		} else {
			$url = admin_url( 'admin.php?page=ic-catalog-mode' );;
		}
		if ( current_user_can( 'manage_product_settings' ) ) {
			?>
            <a id="woocommerce-settings" class="nav-tab"
               href="<?php echo esc_url( $url ) ?>">WooCommerce</a>
			<?php
		}
	}

	function tab() {
		$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : '';
		if ( $tab !== 'woocommerce' ) {
			return;
		}
		?>
        <script>
            jQuery('.nav-tab-wrapper a').removeClass('nav-tab-active');
            jQuery('.nav-tab-wrapper a#woocommerce-settings').addClass('nav-tab-active');
        </script>
        <div class="woocommerce-settings settings-wrapper">
            <div class="settings-submenu">
                <h3>

                </h3>
            </div>
            <div class="setting-content submenu">
				<?php do_action( 'ic-woocommerce-settings' ); ?>
            </div>
            <div class="helpers">
                <div class="wrapper"><?php
					main_helper();
					?>
                </div>
            </div>
        </div>
		<?php
	}

	function content() {
		$tab     = $_GET['tab'];
		$submenu = $_GET['submenu'];
		if ( $submenu == 'woocommerce' ) {
			?>
            <script>
                jQuery('.settings-submenu a').removeClass('current');
                jQuery('.settings-submenu a#woocommerce-settings').addClass('current');
            </script>
			<?php
			$message = __( 'Missing Module!', 'ecommerce-product-catalog' );
			$message .= '<br>';
			$message .= '<br>';
			$message .= sprintf( __( 'Please install free %1$s extension from %2$scatalog extensions menu%3$s.', 'ecommerce-product-catalog' ), 'WooCommerce Catalog', '<a href="' . admin_url( 'edit.php?post_type=al_product&page=extensions.php&tab=product-extensions' ) . '">', '</a>' );
			$message .= '<br>';
			$message .= '<br>';
			$message .= __( 'You will be able to customize the WooCommerce catalog design, disable cart, enable inquiry and many more.', 'ecommerce-product-catalog' );
			$message .= '<br>';
			$message .= '<br>';
			$message .= sprintf( __( 'You can also %1$simport WooCommerce products into separate catalog%2$s.', 'ecommerce-product-catalog' ), '<a href="' . admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=csv' ) . '">', '</a>' );
			implecode_info( $message );
			?>
			<?php
		}

	}

}

new ic_catalog_woocommerce;
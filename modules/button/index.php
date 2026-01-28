<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *
 *  @version       1.0.0
 *  @package
 *  @author        impleCode
 *
 */

class ic_catalog_button {

	private $settings;
	private $meta_name = '_ic_button_url';
	private $option_name = 'ic_catalog_button';
	private $menu_element = 'affiliate_button';

	function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	function init() {
		$this->settings = $this->settings();
		add_action( 'wp', array( $this, 'enable' ) );
		add_filter( 'product_meta_save', array( $this, 'save' ) );
		add_action( 'add_product_metaboxes', array( $this, 'metabox' ) );
		add_action( 'product-settings-list', array( $this, 'register_settings' ) );
		add_action( 'general_submenu', array( $this, 'settings_menu' ) );
		add_action( 'product-settings', array( $this, 'settings_html' ) );
	}

	function enable() {
		//add_action( 'after_price_table', array( $this, 'show' ), 10, 0 );
		add_action( 'product_details', array( $this, 'show' ), 10, 0 );
	}

	function show() {
		$url = $this->button_url();
		if ( ! empty( $url ) ) {
			?>
            <div class="ic-affiliate-button-container"><a href="<?php echo esc_url( $url ) ?>"
                                                          class="button"><?php echo $this->settings['label'] ?></a>
            </div>
			<?php
		}
	}

	function button_url( $product_id = null ) {
		if ( empty( $product_id ) ) {
			$product_id = ic_get_product_id();
		}
		$url = $this->settings['url'];
		if ( ! empty( $this->settings['individual'] ) ) {
			$url = get_post_meta( $product_id, $this->meta_name, true );
			if ( empty( $url ) && ! empty( $this->settings['use_default'] ) ) {
				$url = $this->settings['url'];
			}
		}
		if ( is_string( $url ) ) {

			return apply_filters( 'ic_affiliate_button_url', $url, $product_id );
		}

		return '';
	}

	function metabox( $post ) {
		if ( ! empty( $this->settings['individual'] ) ) {
			add_meta_box( 'ic_catalog_button', __( 'Button', 'ecommerce-product-catalog' ), array(
				$this,
				'metabox_content'
			), 'al_product', 'side', 'default' );
		}
	}

	function metabox_content() {
		echo '<table>';
		$url = $this->button_url();
		implecode_settings_text( __( 'Button URL', 'ecommerce-product-catalog' ), $this->meta_name, $url );
		echo '</table>';
	}

	function save( $product_meta ) {
		$product_meta[ $this->meta_name ] = isset( $_POST[ $this->meta_name ] ) ? esc_url( $_POST[ $this->meta_name ] ) : '';

		return $product_meta;
	}

	function settings_html() {
		$submenu = isset( $_GET['submenu'] ) ? $_GET['submenu'] : '';
		if ( $submenu == $this->menu_element ) {
			?>
            <script>
                jQuery('.settings-submenu a').removeClass('current');
                jQuery('.settings-submenu a#affiliate-button-settings').addClass('current');
            </script>
            <div class="affiliate-button-settings setting-content submenu">
                <form method="post" action="options.php">
					<?php settings_fields( $this->option_name ); ?>
                    <h2><?php _e( 'Product Button Options', 'ecommerce-product-catalog' ) ?></h2>
					<?php
					echo '<table>';
					implecode_settings_text( __( 'Button Label', 'ecommerce-product-catalog' ), $this->option_name . '[label]', $this->settings['label'] );
					implecode_settings_text( __( 'Default button URL', 'ecommerce-product-catalog' ), $this->option_name . '[url]', $this->settings['url'] );
					implecode_settings_checkbox( __( 'Unique URL for each Product', 'ecommerce-product-catalog' ), $this->option_name . '[individual]', $this->settings['individual'] );
					implecode_settings_checkbox( __( 'Use default if empty', 'ecommerce-product-catalog' ), $this->option_name . '[use_default]', $this->settings['use_default'] );
					do_action( 'ic_affiliate_button_settings', $this->settings, $this->option_name );
					echo '</table>';
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

	function settings_menu() {
		?>
        <a id="affiliate-button-settings" class="element"
           href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=product-settings&submenu=' . $this->menu_element ) ?>"><?php _e( 'Affiliate Button', 'ecommerce-product-catalog' ); ?> </a><?php
	}

	function register_settings() {
		register_setting( $this->option_name, $this->option_name );
	}

	function settings() {
		if ( ! empty( $this->settings ) ) {
			return $this->settings;
		}
		$this->settings = get_option( $this->option_name );
		if ( ! is_array( $this->settings ) ) {
			$this->settings = array();
		}
		$this->settings['label']       = ! empty( $this->settings['label'] ) ? $this->settings['label'] : __( 'Read More', 'ecommerce-product-catalog' );
		$this->settings['url']         = isset( $this->settings['url'] ) ? $this->settings['url'] : '';
		$this->settings['individual']  = isset( $this->settings['individual'] ) ? $this->settings['individual'] : '';
		$this->settings['use_default'] = isset( $this->settings['use_default'] ) ? $this->settings['use_default'] : '';

		return apply_filters( 'ic_affiliate_settings', $this->settings );
	}

}

global $ic_catalog_button;
$ic_catalog_button = new ic_catalog_button;

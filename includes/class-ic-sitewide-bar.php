<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *
 *
 *
 *
 *  @version       1.0.0
 *  @package       responsive-bar
 *  @author        impleCode

 */

class ic_sitewide_bar {

	private $display = '', $search = '', $catalog = '', $search_type = '';

	function __construct() {
		add_action( 'wp_footer', array( $this, 'show' ) );
		add_filter( 'wp_nav_menu_items', array( $this, 'show' ), 99, 2 );

		add_action( 'ic_catalog_design_schemes_top', array( $this, 'settings' ) );
		add_filter( 'ic_catalog_design_schemes', array( $this, 'settings_default' ) );
		add_action( 'ic_catalog_bar_content', array( $this, 'listing' ) );
		add_action( 'ic_catalog_bar_content', array( $this, 'search' ) );

		add_action( 'ic_catalog_customizer_sections', array( $this, 'customizer_sections' ) );
		add_filter( 'ic_customizer_settings', array( $this, 'customizer' ), 10, 2 );

		add_action( 'ic_register_blocks', array( $this, 'register_block' ) );

		add_action( 'enqueue_block_assets', array( $this, 'enqueue' ) );

		add_action( 'wp', array( $this, 'init' ) );
	}

	function enqueue() {
		wp_enqueue_style( 'dashicons' );
		wp_enqueue_style( 'al_product_styles' );
	}

	function init() {
		$design_schemes    = ic_get_design_schemes();
		$this->display     = $design_schemes['icons_display'];
		$this->search      = $design_schemes['icons_display_search'];
		$this->catalog     = $design_schemes['icons_display_catalog'];
		$this->search_type = $design_schemes['icons_search'];
	}

	function show( $nav_menu = null, $args = null ) {
		if ( ! $this->is_displayed() ) {
			return $nav_menu;
		}
		if ( ! empty( $nav_menu ) && ! empty( $args ) ) {

			if ( ( ! ic_string_contains( $args->theme_location, 'primary' ) && ! ic_string_contains( $args->theme_location, 'main' ) ) || $this->display !== 'all' ) {
				return $nav_menu;
			}

			$nav_menu .= $this->icons();
		}

		return $nav_menu;
	}

	function icons() {
		ob_start();
		echo '<div id="ic-catalog-menu-bar">';
		ic_show_template_file( 'sitewide-icons/icon-bar.php' );
		echo '</div>';

		return ob_get_clean();
	}

	function icon_container( $content, $class = '' ) {
		if ( empty( $content ) ) {
			return;
		}
		if ( is_custom_product_listing_page() ) {
			if ( ! empty( $class ) ) {
				$class .= ' ';
			}
			$class .= 'current-menu-item';
		}
		?>
        <div class="ic-bar-icon <?php echo $class ?>">
			<?php
			echo $content;
			?>
        </div>
		<?php
	}

	function icon( $url, $icon, $content = null, $container_class = '' ) {
		if ( empty( $url ) || empty( $icon ) ) {
			return;
		}
		if ( ! $this->is_url( $url ) ) {
			$content = $url;
			$url     = '';
		}
		$design_schemes = ic_get_design_schemes();
		$class          = ' button ' . implode( ' ', array_filter( $design_schemes ) );
		if ( ! empty( $content ) ) {
			$class .= ' ic-show-content';
		}
		$icon_content = '<a class="ic-icon-url' . $class . '" href="' . $url . '">';
		$icon_content .= '<span class="' . $this->icons_type() . $icon . '"></span>';
		$icon_content .= '</a>';
		if ( ! empty( $content ) ) {
			$icon_content .= '<div class="ic-icon-hidden-content"><div class="ic-icon-hidden-content-inside"><span class="ic-popup-close dashicons dashicons-no-alt"></span>' . $content . '</div></div>';
		}
		$this->icon_container( $icon_content, $container_class );
	}

	function text( $text, $class = '' ) {
		if ( empty( $text ) ) {
			return;
		}
		if ( ! empty( $class ) ) {
			$class = 'ic-bar-text-' . $class;
		}
		?>
        <div class="ic-bar-text <?php echo $class ?>">
			<?php
			echo $text;
			?>
        </div>
		<?php
	}

	function icons_type() {
		return apply_filters( 'ic_catalog_bar_icons_type', 'dashicons dashicons-' );
	}

	function listing() {
		if ( ! is_ic_product_listing_enabled() ) {
			return;
		}

		if ( ! empty( $this->catalog ) ) {
			return;
		}
		$listing_page = product_listing_url();
		if ( ! empty( $listing_page ) ) {
			$this->icon( $listing_page, 'store' );
		}
	}

	function search() {
		if ( ! empty( $this->search ) ) {
			return;
		}
		ob_start();
		ic_save_global( 'search_widget_instance', array( 'title' => '' ) );
		add_filter( 'ic_search_box_class', array( __CLASS__, 'box_class' ) );
		ic_show_search_widget_form();
		$search = ob_get_clean();
		if ( ! empty( $search ) ) {
			$this->icon( $search, 'search' );
		}
	}

	static function box_class( $class ) {
		if ( ! empty( $class ) ) {
			$class .= ' ';
		}
		$class .= design_schemes( 'box', 0 );

		return $class;
	}

	function register_block() {
		register_block_type( __DIR__ . '/blocks/sitewide-icons/',
			array(
				'render_callback' => array( $this, 'icons' ),
			)
		);
	}

	function settings( $design_schemes ) {
		?>
        <h3><?php _e( 'Sitewide Icons', 'ecommerce-product-catalog' ); ?></h3>
        <table>
			<?php
			implecode_settings_radio( __( 'Icons Display', 'ecommerce-product-catalog' ), 'design_schemes[icons_display]', $design_schemes['icons_display'], $this->icons_display_options() );
			implecode_settings_checkbox( __( 'Hide Catalog Icon', 'ecommerce-product-catalog' ), 'design_schemes[icons_display_catalog]', $design_schemes['icons_display_catalog'] );
			implecode_settings_checkbox( __( 'Hide Search Icon', 'ecommerce-product-catalog' ), 'design_schemes[icons_display_search]', $design_schemes['icons_display_search'] );
			implecode_settings_radio( __( 'Search Icon', 'ecommerce-product-catalog' ), 'design_schemes[icons_search]', $design_schemes['icons_search'], $this->icons_search_options() );
			do_action( 'ic_catalog_sitewide_icons_settings_html', $design_schemes );
			?>
        </table>
		<?php
	}

	function icons_display_options() {
		return array(
			'all'   => __( 'All devices', 'ecommerce-product-catalog' ),
			'small' => __( 'Small screens only', 'ecommerce-product-catalog' ),
			'none'  => __( 'Disabled', 'ecommerce-product-catalog' ),
		);
	}

	function icons_search_options() {
		return array(
			'field'    => __( 'Simple Field', 'ecommerce-product-catalog' ),
			'ic_popup' => __( 'Popup', 'ecommerce-product-catalog' ),
		);
	}

	function settings_default( $settings ) {
		$settings['icons_display']         = isset( $settings['icons_display'] ) ? $settings['icons_display'] : 'none';
		$settings['icons_display_catalog'] = isset( $settings['icons_display_catalog'] ) ? $settings['icons_display_catalog'] : '';
		$settings['icons_display_search']  = isset( $settings['icons_display_search'] ) ? $settings['icons_display_search'] : '';
		$settings['icons_search']          = isset( $settings['icons_search'] ) ? $settings['icons_search'] : 'ic_popup';

		return apply_filters( 'ic_catalog_sitewide_icons_settings', $settings );
	}

	function is_displayed() {
		if ( empty( $this->display ) || ( ! empty( $this->display ) && $this->display === 'none' ) ) {
			return false;
		}
		if ( empty( $this->catalog ) || empty( $this->search ) ) {
			return true;
		}

		return apply_filters( 'ic_catalog_sitewide_icons_displayed', false );
	}

	function is_url( $url ) {
		if ( $url === esc_url_raw( $url ) ) {
			return true;
		}

		return false;
	}

	static function container_class() {
		$design_schemes = ic_get_design_schemes();
		$class          = 'ic-catalog-bar device-' . $design_schemes['icons_display'] . ' ' . $design_schemes['icons_search'];

		return $class;
	}

	function customizer_sections( $wp_customize ) {
		$message         = __( 'The icons will appear in the main menu.', 'ecommerce-product-catalog' );
		$site_editor_url = admin_url( 'site-editor.php' );
		if ( ! empty( $site_editor_url ) ) {
			$message .= ' ';
			$message .= sprintf( __( 'You can also %sadd the Catalog Icons block to the menu%s if your theme supports site editing with blocks.', 'ecommerce-product-catalog' ), '<a href="' . esc_url( $site_editor_url ) . '">', '</a>' );
		}

		$wp_customize->add_section( 'ic_product_catalog_icons', array(
			'title'       => __( 'Sitewide Icons', 'ecommerce-product-catalog' ),
			'priority'    => 30,
			'panel'       => 'ic_product_catalog',
			'description' => $message
		) );
	}

	function customizer( $settings, $customizer ) {
		$settings[] = array(
			'name'    => 'design_schemes[icons_display]',
			'args'    => array( 'type' => 'option', 'default' => 'none' ),
			'control' => array(
				'name' => 'ic_pc_integration_icons_display',
				'args' => array(
					'label'    => __( 'Icons Display', 'ecommerce-product-catalog' ),
					'section'  => 'ic_product_catalog_icons',
					'settings' => 'design_schemes[icons_display]',
					'type'     => 'radio',
					'choices'  => $this->icons_display_options()
				)
			)
		);
		$settings[] = array(
			'name'    => 'design_schemes[icons_display_catalog]',
			'args'    => array(
				'type'              => 'option',
				'default'           => '',
				'sanitize_callback' => array( $customizer, 'sanitize_checkbox' )
			),
			'control' => array(
				'name' => 'ic_pc_integration_icons_display_catalog',
				'args' => array(
					'label'    => __( 'Hide Catalog Icon', 'ecommerce-product-catalog' ),
					'section'  => 'ic_product_catalog_icons',
					'settings' => 'design_schemes[icons_display_catalog]',
					'type'     => 'checkbox',
				)
			)
		);
		$settings[] = array(
			'name'    => 'design_schemes[icons_display_search]',
			'args'    => array(
				'type'              => 'option',
				'default'           => '',
				'sanitize_callback' => array( $customizer, 'sanitize_checkbox' )
			),
			'control' => array(
				'name' => 'ic_pc_integration_icons_display_search',
				'args' => array(
					'label'    => __( 'Hide Search Icon', 'ecommerce-product-catalog' ),
					'section'  => 'ic_product_catalog_icons',
					'settings' => 'design_schemes[icons_display_search]',
					'type'     => 'checkbox',
				)
			)
		);
		$settings[] = array(
			'name'    => 'design_schemes[icons_search]',
			'args'    => array( 'type' => 'option', 'default' => 'ic_popup' ),
			'control' => array(
				'name' => 'ic_pc_integration_icons_search',
				'args' => array(
					'label'    => __( 'Search Icon', 'ecommerce-product-catalog' ),
					'section'  => 'ic_product_catalog_icons',
					'settings' => 'design_schemes[icons_search]',
					'type'     => 'radio',
					'choices'  => $this->icons_search_options()
				)
			)
		);

		return $settings;
	}

}

global $ic_sitewide_bar;
$ic_sitewide_bar = new ic_sitewide_bar;


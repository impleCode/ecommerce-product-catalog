<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product settings
 *
 * Here product settings are defined and managed.
 *
 * @version		1.1.4
 * @package		ecommerce-product-catalog/includes
 * @author 		Norbert Dreszer
 */
function register_product_settings_menu() {
	add_submenu_page( 'edit.php?post_type=al_product', __( 'Settings', 'al-ecommerce-product-catalog' ), __( 'Settings', 'al-ecommerce-product-catalog' ), apply_filters( 'see_product_settings_cap', 'manage_product_settings' ), basename( __FILE__ ), 'product_settings' );
	do_action( 'product_settings_menu' );
}

add_action( 'admin_menu', 'register_product_settings_menu' );

add_filter( 'option_page_capability_product_settings', 'map_product_settings_capability' );
add_filter( 'option_page_capability_product_attributes', 'map_product_settings_capability' );
add_filter( 'option_page_capability_product_shipping', 'map_product_settings_capability' );
add_filter( 'option_page_capability_product_names_archive', 'map_product_settings_capability' );
add_filter( 'option_page_capability_product_names_single', 'map_product_settings_capability' );
add_filter( 'option_page_capability_product_design', 'map_product_settings_capability' );
add_filter( 'option_page_capability_single_design', 'map_product_settings_capability' );
add_filter( 'option_page_capability_design_schemes', 'map_product_settings_capability' );

function map_product_settings_capability( $cap ) {
	return apply_filters( 'change_product_settings_cap', 'manage_product_settings' );
}

function product_settings_list() {
	do_action( 'product-settings-list' );
}

add_action( 'admin_init', 'product_settings_list', 20 );

require_once( AL_BASE_PATH . '/config/currencies.php' );
require_once( AL_BASE_PATH . '/templates/themes/default-theme.php' );
require_once( AL_BASE_PATH . '/templates/themes/classic-list.php' );
require_once( AL_BASE_PATH . '/templates/themes/classic-grid.php' );

function product_settings() {
	?>

	<div id="implecode_settings" class="wrap">
		<h2><?php _e( 'Settings', 'al-ecommerce-product-catalog' ) ?> - impleCode eCommerce Product Catalog</h2>
		<h2 class="nav-tab-wrapper">
			<?php do_action( 'settings-menu' ); ?>
		</h2>
		<?php
		$tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : '';

		/* GENERAL SETTINGS */

		if ( $tab == 'product-settings' OR $tab == '' ) {
			?>
			<script>
				jQuery( '.nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );
				jQuery( '.nav-tab-wrapper a#general-settings' ).addClass( 'nav-tab-active' );
			</script>
			<?php
			general_settings_content();
		}

		/* ATTRIBUTES TAB */ else if ( $tab == 'attributes-settings' ) {
			?>
			<script>
				jQuery( '.nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );
				jQuery( '.nav-tab-wrapper a#attributes-settings' ).addClass( 'nav-tab-active' );
			</script>
			<?php
			attributes_settings_content();
		}

		/* SHIPPING TAB */ else if ( $tab == 'shipping-settings' ) {
			?>
			<script>
				jQuery( '.nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );
				jQuery( '.nav-tab-wrapper a#shipping-settings' ).addClass( 'nav-tab-active' );
			</script>
			<?php
			shipping_settings_content();
		}

		/* DESIGN TAB */ else if ( $tab == 'design-settings' ) {
			?>
			<script>
				jQuery( '.nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );
				jQuery( '.nav-tab-wrapper a#design-settings' ).addClass( 'nav-tab-active' );
			</script>
			<?php
			custom_design_content();
		} else if ( $tab == 'names-settings' ) {
			?>
			<script>
				jQuery( '.nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );
				jQuery( '.nav-tab-wrapper a#names-settings' ).addClass( 'nav-tab-active' );
			</script>
			<?php
			custom_names_content();
		}
		do_action( 'settings-content' );
		?>
		<div class="plugin-logo">
			<a href="http://implecode.com/#cam=catalog-settings-link&key=logo-link"><img class="en" src="<?php echo AL_PLUGIN_BASE_PATH . 'img/implecode.png'; ?>" width="282px" alt="impleCode" /></a>
		</div>
	</div>


	<script>
		var fixHelper = function ( e, ui ) {
			ui.children().each( function () {
				jQuery( this ).width( jQuery( this ).width() );
			} );
			return ui;
		};

		jQuery( function ( $ ) {
			var $cache = $( '.helpers .wrapper, #implecode_settings .settings-submenu h3' );
			var top = $cache.offset().top;
			var height = $( '.settings-submenu' ).height();
			function fixDiv() {
				if ( $( window ).scrollTop() > top && $( window ).scrollTop() < height )
					$cache.css( { 'position': 'fixed', 'top': '32px' } );
				else if ( $( window ).scrollTop() > height )
					$cache.css( { 'position': 'absolute', 'bottom': '0px', 'top': 'auto' } );
				else
					$cache.css( { 'position': 'relative', 'top': 'auto', 'bottom': 'auto' } );
			}
			$( window ).scroll( fixDiv );
			fixDiv();
		} );

		jQuery( '.product-settings-table.dragable tbody' ).sortable( {
			update: function ( event, ui ) {
				jQuery( '.product-settings-table.dragable tbody tr' ).each( function () {
					var r = jQuery( this ).index() + 1;
					jQuery( this ).children( 'td:first-child' ).html( r );
					jQuery( this ).children( 'td:first-child' ).removeClass();
					jQuery( this ).children( 'td:first-child' ).addClass( 'lp-column lp' + r );
					jQuery( this ).find( '.product-attribute-label-column .product-attribute-label' ).attr( 'name', 'product_attribute_label[' + r + ']' );
					jQuery( this ).find( 'td .product-attribute' ).attr( 'name', 'product_attribute[' + r + ']' );
					jQuery( this ).find( 'td .product-attribute-unit' ).attr( 'name', 'product_attribute_unit[' + r + ']' );

					jQuery( this ).find( '.product-shipping-label-column .product-shipping-label' ).attr( 'name', 'product_shipping_label[' + r + ']' );
					jQuery( this ).find( 'td .product-shipping-cost' ).attr( 'name', 'product_shipping_cost[' + r + ']' );
				} )
			},
			helper: fixHelper,
			placeholder: 'sort-placeholder',
		} );
		//jQuery('.ui-sortable').height(jQuery('.ui-sortable').height());

	</script>
	<?php
}

function doc_helper( $title, $url, $class = null ) {
	$helper = '<div class="doc-helper ' . $class . '"><div class="doc-item">
		<div class="doc-name green-box">' . sprintf(
	__( '%s Settings in Docs', 'al-ecommerce-product-catalog' ), ucfirst( $title ) ) . '</div>
		<div class="doc-description">' . sprintf(
	__( 'See %s configuration tips in the impleCode documentation', 'al-ecommerce-product-catalog' ), $title ) . '.</div>
		<div class="doc-button"><a target="_blank" href="http://implecode.com/docs/ecommerce-product-catalog/' . $url . '/?cam=catalog-docs-box&key=' . $url . '"><input class="doc_button classic-button" type="button" value="' . __( 'See in Docs', 'al-ecommerce-product-catalog' ) . '"></a></div>
		<a title="' . __( 'Click the button to visit impleCode documentation', 'al-ecommerce-product-catalog' ) . '" target="_blank" href="http://implecode.com/docs/ecommerce-product-catalog/' . $url . '/?cam=catalog-docs-box&key=' . $url . '" class="background-url"></a>
		</div></div>';
	echo $helper;
}

function did_know_helper( $name, $desc, $url, $class = null ) {
	$helper = '<div class="doc-helper ' . $class . '"><div class="doc-item">
		<div class="doc-name green-box">' .
	__( 'Did you know?', 'al-ecommerce-product-catalog' ) . '</div>
		<div class="doc-description">' . $desc . '.</div>
		<div class="doc-button"><a target="_blank" href="' . $url . '?cam=catalog-know-box&key=' . $name . '"><input class="doc_button classic-button" type="button" value="' . __( 'See Now', 'al-ecommerce-product-catalog' ) . '"></a></div>
		<a title="' . __( 'Click the button to visit impleCode website', 'al-ecommerce-product-catalog' ) . '" target="_blank" href="' . $url . '?cam=catalog-docs-box&key=' . $name . '" class="background-url"></a>
		</div></div>';
	echo $helper;
}

function text_helper( $title, $desc, $class = null ) {
	$helper = '<div class="doc-helper text ' . $class . '"><div class="doc-item">
		<div class="doc-name green-box">' . $title . '</div>
		<div class="doc-description">' . $desc . '</div>
		</div></div>';
	echo $helper;
}

function review_helper() {
	$helper = '<div class="doc-helper review"><div class="doc-item">
		<div class="doc-name green-box">' . __( 'Rate this Plugin!', 'al-ecommerce-product-catalog' ) . '</div>
		<div class="doc-description">' . sprintf( __( 'Please <a target="_blank" href="%s">rate</a> this plugin and tell me if it works for you or not. It really helps development.', 'al-ecommerce-product-catalog' ), 'https://wordpress.org/support/view/plugin-reviews/ecommerce-product-catalog#postform' ) . '</div>
		</div></div>';
	echo $helper;
}

function main_helper() {
	$helper = '<div class="doc-helper main"><div class="doc-item">
		<div class="doc-name green-box">' . __( 'Need Help?', 'al-ecommerce-product-catalog' ) . '</div>
		<div class="doc-description">
			<form role="search" method="get" class="search-form" action="https://implecode.com/docs/">
				<label>
					<span class="screen-reader-text">Search for:</span>
					<input type="hidden" value="al_doc" name="post_type">
					<input type="search" class="search-field" placeholder="Search Docs …" value="" name="s" title="Search for:">
				</label>
				<input type="submit" class="button-primary" value="Search">
			</form>
		</div>
		</div></div>';
	echo $helper;
}

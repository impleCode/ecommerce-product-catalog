<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages custom design settings
 *
 * Here custom design settings are defined and managed.
 *
 * @version		1.1.4
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
function design_menu() {
	?>
	<a id="design-settings" class="nav-tab" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=design-settings&submenu=archive-design' ) ?>"><?php _e( 'Catalog Design', 'al-ecommerce-product-catalog' ); ?></a>
	<?php
}

add_action( 'settings-menu', 'design_menu' );

function design_settings() {
	register_setting( 'product_design', 'archive_template' );
	register_setting( 'product_design', 'modern_grid_settings' );
	register_setting( 'product_design', 'classic_grid_settings' );
	register_setting( 'single_design', 'catalog_lightbox' );
	register_setting( 'single_design', 'multi_single_options' );
	register_setting( 'single_design', 'default_product_thumbnail' );
	register_setting( 'design_schemes', 'design_schemes' );
}

add_action( 'product-settings-list', 'design_settings' );

function custom_design_content() {
	?>
	<div class="design-product-settings settings-wrapper">
		<div class="settings-submenu">
			<h3>
				<a id="archive-design" class="element current" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=design-settings&submenu=archive-design' ) ?>"><?php _e( 'Product Listing', 'al-ecommerce-product-catalog' ); ?></a>
				<a id="single-design" class="element" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=design-settings&submenu=single-design' ) ?>"><?php _e( 'Product Page', 'al-ecommerce-product-catalog' ); ?></a>
				<a id="design-schemes" class="element" href="<?php echo admin_url( 'edit.php?post_type=al_product&page=product-settings.php&tab=design-settings&submenu=design-schemes' ) ?>"><?php _e( 'Design Schemes', 'al-ecommerce-product-catalog' ); ?></a>
				<?php do_action( 'custom-design-submenu' ); ?>
			</h3>
		</div>
		<div class="setting-content submenu"><?php do_action( 'custom-design-settings' ); ?>
		</div>
		<div class="helpers"><div class="wrapper"><?php
				main_helper();
				$submenu = $_GET[ 'submenu' ];
				if ( $submenu == 'single-design' ) {
					doc_helper( __( 'gallery', 'al-ecommerce-product-catalog' ), 'product-gallery' );
				}
				?>
			</div></div>
	</div>
	<?php
}

function archive_custom_design() {
	$tab	 = $_GET[ 'tab' ];
	$submenu = $_GET[ 'submenu' ];
	if ( $submenu == 'archive-design' ) {
		?>
		<script>
			jQuery( '.settings-submenu a' ).removeClass( 'current' );
			jQuery( '.settings-submenu a#archive-design' ).addClass( 'current' );
		</script>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'product_design' );
			$archive_template				 = get_product_listing_template();
			$modern_grid_settings			 = get_modern_grid_settings();
			$default_classic_grid_settings	 = array(
				'entries' => 3,
			);
			$classic_grid_settings			 = get_option( 'classic_grid_settings', $default_classic_grid_settings );
			?>
			<h2><?php _e( 'Design Settings', 'al-ecommerce-product-catalog' ); ?></h2>
			<h3><?php _e( 'Product Listing', 'al-ecommerce-product-catalog' ); ?></h3>
			<table class="design-table">
				<thead></thead>
				<tbody>
					<tr id="default-theme">
						<td class="with-additional-styling theme-name">
							<input type="radio" name="archive_template" value="default"<?php checked( 'default' == $archive_template ); ?>><?php _e( 'Modern Grid', 'al-ecommerce-product-catalog' ); ?></td>
						<td rowspan="2" class="theme-example"><?php example_default_archive_theme(); ?></td>
					</tr>
					<tr><td class="additional-styling"><strong><?php _e( 'Additional Settings', 'al-ecommerce-product-catalog' ); ?></strong><br><input title="<?php _e( 'Use this only with short attributes labels and values e.g. Color: Red', 'al-ecommerce-product-catalog' ) ?>" type="checkbox" name="modern_grid_settings[attributes]" value="1"<?php checked( 1, isset( $modern_grid_settings[ 'attributes' ] ) ? $modern_grid_settings[ 'attributes' ] : ''  ); ?>><?php _e( 'Show Attributes', 'al-ecommerce-product-catalog' ); ?><br><?php _e( 'Per row', 'al-ecommerce-product-catalog' ) ?>: <input type="number" min="1" max="5" step="1" class="number_box" name="modern_grid_settings[per-row]" value="<?php echo $modern_grid_settings[ 'per-row' ] ?>"><?php _e( 'products', 'al-ecommerce-product-catalog' ) ?></td></tr>
					<tr><td colspan="2" class="separator"></td></tr>
					<tr id="list-theme">
						<td class="with-additional-styling theme-name"><input type="radio" name="archive_template" value="list"<?php checked( 'list' == $archive_template ); ?>><?php _e( 'Classic List', 'al-ecommerce-product-catalog' ); ?></td>
						<td class="theme-example"><?php example_list_archive_theme(); ?></td>
					</tr>
					<tr><td colspan="2" class="separator"></td></tr>
					<tr id="grid-theme">
						<td class="with-additional-styling theme-name">
							<input type="radio" name="archive_template" value="grid"<?php checked( 'grid' == $archive_template ); ?>><?php _e( 'Classic Grid', 'al-ecommerce-product-catalog' ); ?></td>
						<td rowspan="2" class="theme-example"><?php example_grid_archive_theme(); ?></td>
					</tr>
					<tr>
						<td class="additional-styling"><strong><?php _e( 'Additional Settings', 'al-ecommerce-product-catalog' ); ?></strong><br><?php _e( 'Per row', 'al-ecommerce-product-catalog' ) ?>: <input type="number" min="1" step="1" class="number_box" title="<?php _e( 'The product listing element width will adjust accordingly to your theme content width.', 'al-ecommerce-product-catalog' ) ?>" name="classic_grid_settings[entries]" value="<?php echo $classic_grid_settings[ 'entries' ] ?>"><?php _e( 'products', 'al-ecommerce-product-catalog' ) ?></td>
					</tr>
					<tr><td colspan="2" class="separator"></td></tr>
					<?php do_action( 'product_listing_theme_settings', $archive_template ) ?>
				</tbody>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save changes', 'al-ecommerce-product-catalog' ); ?>" />
			</p>
		</form>
		<?php
	}
}

add_action( 'custom-design-settings', 'archive_custom_design' );

function single_custom_design() {
	$tab	 = $_GET[ 'tab' ];
	$submenu = $_GET[ 'submenu' ];
	if ( $submenu == 'single-design' ) {
		?>
		<script>
			jQuery( '.settings-submenu a' ).removeClass( 'current' );
			jQuery( '.settings-submenu a#single-design' ).addClass( 'current' );
		</script>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'single_design' );
			$enable_catalog_lightbox = get_option( 'catalog_lightbox', ENABLE_CATALOG_LIGHTBOX );
			$single_options			 = get_option( 'multi_single_options', unserialize( MULTI_SINGLE_OPTIONS ) );
			?>
			<h2><?php _e( 'Design Settings', 'al-ecommerce-product-catalog' ); ?></h2>
			<h3><?php _e( 'Default Product Image', 'al-ecommerce-product-catalog' ); ?></h3><?php
			//$name = 'default_product_thumbnail';
			//$button_value = __('Change Default Thumbnail', 'al-ecommerce-product-catalog');
			//$option_name = 'default_product_thumbnail';
			//upload_product_image($name, $button_value, $option_name);
			implecode_upload_image( __( 'Upload Default Image', 'al-ecommerce-product-catalog' ), 'default_product_thumbnail', get_default_product_image_src() )
			?>
			<h3><?php _e( 'Product Gallery', 'al-ecommerce-product-catalog' ); ?></h3>
			<input type="checkbox" title="<?php _e( 'The image will be used only for product listing when unchecked.', 'al-ecommerce-product-catalog' ) ?>" name="multi_single_options[enable_product_gallery]" value="1"<?php checked( 1, isset( $single_options[ 'enable_product_gallery' ] ) ? $single_options[ 'enable_product_gallery' ] : ''  ); ?>><?php _e( 'Enable product image', 'al-ecommerce-product-catalog' ); ?></br>
			<input type="checkbox" title="<?php _e( 'The image on product page will not be linked when unchecked.', 'al-ecommerce-product-catalog' ) ?>" name="catalog_lightbox" value="1"<?php checked( 1, $enable_catalog_lightbox ); ?> ><?php _e( 'Enable lightbox on product image', 'al-ecommerce-product-catalog' ); ?></br>
			<input type="checkbox" title="<?php _e( 'The default image will be used on product listing only when unchecked.', 'al-ecommerce-product-catalog' ) ?>" name="multi_single_options[enable_product_gallery_only_when_exist]" value="1"<?php checked( 1, isset( $single_options[ 'enable_product_gallery_only_when_exist' ] ) ? $single_options[ 'enable_product_gallery_only_when_exist' ] : ''  ); ?> /><?php
			_e( 'Enable product image only when inserted', 'al-ecommerce-product-catalog' );

			do_action( 'single_product_design' );
			?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save changes', 'al-ecommerce-product-catalog' ); ?>" />
			</p>
		</form>
		<?php
	}
}

add_action( 'custom-design-settings', 'single_custom_design' );

function color_schemes() {
	$tab	 = $_GET[ 'tab' ];
	$submenu = $_GET[ 'submenu' ];
	if ( $submenu == 'design-schemes' ) {
		?>
		<script>
			jQuery( '.settings-submenu a' ).removeClass( 'current' );
			jQuery( '.settings-submenu a#design-schemes' ).addClass( 'current' );
		</script>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'design_schemes' );
			$custom_single_styles	 = unserialize( DEFAULT_DESIGN_SCHEMES );
			$design_schemes			 = get_option( 'design_schemes', $custom_single_styles );
			?>
			<h2><?php _e( 'Design Settings', 'al-ecommerce-product-catalog' ); ?></h2>
			<h3><?php _e( 'Design Schemes', 'al-ecommerce-product-catalog' ); ?></h3>
			<div class="al-box info"><p><?php _e( "Changing design schemes has almost always impact on various elements. For example changing price color has impact on single product page and archive page price color.", 'al-ecommerce-product-catalog' ); ?></p><p><?php _e( 'You can figure it out by checking "impact" column.', 'al-ecommerce-product-catalog' ); ?></p></div>
			<table style="clear:right" class="wp-list-table widefat product-settings-table">
				<thead><tr>
						<th><strong><?php _e( 'Setting', 'al-ecommerce-product-catalog' ); ?></strong></th>
						<th><strong><?php _e( 'Value', 'al-ecommerce-product-catalog' ); ?></strong></th>
						<th><strong><?php _e( 'Example Effect', 'al-ecommerce-product-catalog' ); ?></strong></th>
						<th><strong><?php _e( 'Impact', 'al-ecommerce-product-catalog' ); ?></strong></th>
					</tr></thead>
				<tbody>
					<tr>
						<td><label for="design_schemes[price-size]"><?php _e( 'Price Size', 'al-ecommerce-product-catalog' ); ?></label></td>
						<td><select id="single_price" name="design_schemes[price-size]">
								<option name="design_schemes[big-price]" value="big-price"<?php selected( 'big-price', $design_schemes[ 'price-size' ] ); ?>><?php _e( 'Big', 'al-ecommerce-product-catalog' ); ?></option>
								<option name="design_schemes[small-price]" value="small-price"<?php selected( 'small-price', $design_schemes[ 'price-size' ] ); ?>><?php _e( 'Small', 'al-ecommerce-product-catalog' ); ?></option>
							</select></td>
						<td rowspan=2 class="price-value example <?php design_schemes(); ?>"><?php do_action( 'example_price' ); ?></td>
						<td><?php _e( 'single product', 'al-ecommerce-product-catalog' ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'Price Color', 'al-ecommerce-product-catalog' ); ?></td>
						<td>
							<select id="single_price" name="design_schemes[price-color]">
								<option name="design_schemes[red-price]" value="red-price"<?php selected( 'red-price', $design_schemes[ 'price-color' ] ); ?>><?php _e( 'Red', 'al-ecommerce-product-catalog' ); ?></option>
								<option name="design_schemes[orange-price]" value="orange-price"<?php selected( 'orange-price', $design_schemes[ 'price-color' ] ); ?>><?php _e( 'Orange', 'al-ecommerce-product-catalog' ); ?></option>
								<option name="design_schemes[green-price]" value="green-price"<?php selected( 'green-price', $design_schemes[ 'price-color' ] ); ?>><?php _e( 'Green', 'al-ecommerce-product-catalog' ); ?></option>
							</select>
						</td>
						<td><?php _e( 'single product', 'al-ecommerce-product-catalog' ); ?>, <?php _e( 'product archive', 'al-ecommerce-product-catalog' ); ?></td>
					</tr>
					<tr>
						<td><?php _e( 'Boxes Color', 'al-ecommerce-product-catalog' ); ?></td>
						<td>
							<select id="box_schemes" name="design_schemes[box-color]">
								<option name="design_schemes[red-box]" value="red-box"<?php selected( 'red-box', $design_schemes[ 'box-color' ] ); ?>><?php _e( 'Red', 'al-ecommerce-product-catalog' ); ?></option>
								<option name="design_schemes[orange-box]" value="orange-box"<?php selected( 'orange-box', $design_schemes[ 'box-color' ] ); ?>><?php _e( 'Orange', 'al-ecommerce-product-catalog' ); ?></option>
								<option name="design_schemes[green-box]" value="green-box"<?php selected( 'green-box', $design_schemes[ 'box-color' ] ); ?>><?php _e( 'Green', 'al-ecommerce-product-catalog' ); ?></option>
							</select>
						</td>
						<td><div class="product-name example <?php design_schemes( 'box' ); ?>">Exclusive Red Lamp</div></td>
						<td><?php _e( 'product archive title', 'al-ecommerce-product-catalog' ); ?>, <?php _e( 'archive pagination', 'al-ecommerce-product-catalog' ); ?></td>
					</tr>
				</tbody>
			</table>
			<?php do_action( 'color_schemes_settings' ); ?>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e( 'Save changes', 'al-ecommerce-product-catalog' ); ?>" />
			</p>
		</form>
		<?php
	}
}

add_action( 'custom-design-settings', 'color_schemes' );

function get_default_product_image_src() {
	$default_image	 = AL_PLUGIN_BASE_PATH . 'img/no-default-thumbnail.png';
	$defined_image	 = get_option( 'default_product_thumbnail' );
	$defined_image	 = empty( $defined_image ) ? $default_image : $defined_image;
	return $defined_image;
}

function get_modern_grid_settings() {
	$settings = wp_parse_args( get_option( 'modern_grid_settings' ), array( 'attributes' => 0, 'per-row' => 2 ) );
	return $settings;
}

function get_product_page_settings() {
	$single_options												 = get_option( 'multi_single_options', unserialize( MULTI_SINGLE_OPTIONS ) );
	$single_options[ 'enable_product_gallery' ]					 = isset( $single_options[ 'enable_product_gallery' ] ) ? $single_options[ 'enable_product_gallery' ] : '';
	$single_options[ 'enable_product_gallery_only_when_exist' ]	 = isset( $single_options[ 'enable_product_gallery_only_when_exist' ] ) ? $single_options[ 'enable_product_gallery_only_when_exist' ] : '';
	return $single_options;
}

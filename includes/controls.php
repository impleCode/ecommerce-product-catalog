<?php
if ( !defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 * Defines customizer controls
 *
 * @created Apr 9, 2015
 * @package catalog-me/framework/customizer
 */

if ( class_exists( 'WP_Customize_Control' ) ) {

	/**
	 * Control to display info
	 */
	class More_Catalog_impleCode_Control extends WP_Customize_Control {

		public function render_content() {
			?>
			<label style="overflow: hidden; zoom: 1;">
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<p>
					<?php
					printf( __( 'Check the %1$scatalog settings%2$s for more advanced configuration options.', 'ecommerce-product-catalog' ), '<a href="' . esc_url( admin_url() . 'edit.php?post_type=al_product&page=product-settings.php' ) . '">', '</a>' );
					?>
				</p>
				<p>
					<?php
					printf( __( 'There\'s also a range of %1$sCatalog add-ons%2$s available to put additional power in your hands. Check out the %1$sextensions page%2$s in your dashboard for more information.', 'ecommerce-product-catalog' ), '<a href="' . esc_url( admin_url() . 'edit.php?post_type=al_product&page=extensions.php' ) . '">', '</a>' );
					?>
				</p>
			</label>
			<?php
		}

	}

}

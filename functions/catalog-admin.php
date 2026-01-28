<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages admin only functions
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        impleCode
 */
class ic_catalog_frontend_admin {

	public function __construct() {
		add_action( 'admin_bar_menu', array( $this, 'edit_product_listing' ), 999 );
		add_action( 'ic_product_admin_actions', array( $this, 'admin_actions_container' ), 10, 1 );
		add_action( 'ic_product_meta', array( $this, 'customize_product_page' ) );
		add_action( 'ic_product_meta', array( $this, 'customize_product_listing' ) );
		add_action( 'ic_listing_meta', array( $this, 'customize_product_listing' ) );
		add_action( 'ic_listing_meta', array( $this, 'customize_filters_bar' ) );
		add_action( 'ic_listing_meta', array( $this, 'customize_icons' ) );
		add_action( 'before_product_listing_entry', array( $this, 'listing_options' ) );
		add_action( 'ic_listing_admin_actions', array( $this, 'admin_listing_actions_container' ) );
	}

	public function listing_options() {
		if ( current_user_can( 'edit_products' ) ) {
			do_action( 'ic_listing_admin_actions' );
		}
	}

	public function edit_product_listing( $wp_admin_bar ) {
		$listing_id = get_product_listing_id();
		$query      = ic_get_global( 'pre_shortcode_query' );
		if ( ! empty( $listing_id ) && is_ic_product_listing( $query ) && $listing_id != 'noid' && current_user_can( 'edit_pages' ) ) {
			if ( is_plural_form_active() ) {
				$names = get_catalog_names();
				$label = sprintf( __( 'Edit %s Listing', 'ecommerce-product-catalog' ), ic_ucfirst( $names['singular'] ) );
			} else {
				$label = __( 'Edit Product Listing', 'ecommerce-product-catalog' );
			}
			$args = array(
				'id'    => 'edit',
				'title' => $label,
				'href'  => admin_url( 'post.php?post=' . $listing_id . '&action=edit' ),
				'meta'  => array( 'class' => 'edit-products-page' ),
			);
			$wp_admin_bar->add_node( $args );
		}
	}

	public function admin_actions_container( $product ) {
		if ( get_edit_post_link( $product->ID ) && empty( $_GET['test_advanced'] ) ) {
			?>
            <div class="product-meta">
                <span><?php _e( 'Admin Options', 'ecommerce-product-catalog' ) ?>: </span>
				<?php
				edit_post_link( __( 'Edit Product', 'ecommerce-product-catalog' ), '<span class="edit-link">', '</span>', $product->ID );
				if ( current_user_can( 'administrator' ) ) {
					do_action( 'ic_product_meta', $product );
				}
				?>
            </div>
			<?php
		}
	}

	public function admin_listing_actions_container() {
		if ( empty( $_GET['test_advanced'] ) && current_user_can( 'administrator' ) ) {
			?>
            <div class="product-meta">
				<?php
				echo '<span>' . __( 'Admin Options', 'ecommerce-product-catalog' ) . ': </span>';
				do_action( 'ic_listing_meta' );
				?>
            </div>
			<?php
		}
	}

	public function customize_product_page() {
		if ( function_exists( 'is_customize_preview' ) ) {
			$current_page = get_permalink();
			$url          = admin_url( 'customize.php?autofocus[control]=ic_pc_integration_template&url=' . $current_page );
			echo '<span><a href="' . $url . '">' . __( 'Customize Page Design', 'ecommerce-product-catalog' ) . '</a></span>';
		}
	}

	public function customize_product_listing() {
		if ( function_exists( 'is_customize_preview' ) ) {
			$listing_page = product_listing_url();
			if ( ! empty( $listing_page ) ) {
				$url = admin_url( 'customize.php?autofocus[control]=ic_pc_archive_template&url=' . $listing_page );
				echo '<span><a href="' . $url . '">' . __( 'Customize Listing Design', 'ecommerce-product-catalog' ) . '</a></span>';
			}
		}
	}

	public function customize_icons() {
		if ( function_exists( 'is_customize_preview' ) ) {
			$listing_page = product_listing_url();
			if ( ! empty( $listing_page ) ) {
				$url = admin_url( 'customize.php?autofocus[control]=ic_pc_integration_icons_display&url=' . $listing_page );
				echo '<span><a href="' . $url . '">' . __( 'Customize Sitewide Icons', 'ecommerce-product-catalog' ) . '</a></span>';
			}
		}
	}

	public function customize_filters_bar() {
		$url = admin_url( 'widgets.php' );
		echo '<span><a href="' . $url . '">' . __( 'Customize Filters Bar', 'ecommerce-product-catalog' ) . '</a></span>';
	}

}

$ic_catalog_frontend_admin = new ic_catalog_frontend_admin;

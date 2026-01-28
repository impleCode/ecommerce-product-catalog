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

class ic_epc_block_edit {

	function __construct() {
		add_action( 'ic_after_layout_integration_setting_html', array( $this, 'edit_settings' ) );
		add_filter( 'catalog_multiple_settings', array( $this, 'default_edit' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'init', array( $this, 'init' ), 1 );
	}

	function init() {
		if ( is_admin() || ! $this->enabled() ) {
			return;
		}
		add_filter( 'ic_epc_allow_gutenberg', array( $this, 'ret_true' ) );
	}

	function admin_init() {
		if ( ! $this->enabled() ) {
			remove_filter( 'ic_epc_allow_gutenberg', array( $this, 'ret_true' ) );

			return;
		}
		add_filter( 'ic_epc_allow_gutenberg', array( $this, 'ret_true' ) );

		global $ic_register_product;
		if ( ! empty( $ic_register_product ) ) {
			remove_action( 'current_screen', array( $ic_register_product, 'edit_screen' ) );
			add_action( 'do_meta_boxes', array( $ic_register_product, 'change_image_box' ) );
			add_action( 'add_product_metaboxes', array( $this, 'modify_boxes' ) );
			add_action( 'add_meta_boxes', array( $this, 'modify_boxes' ) );
			add_action( 'enqueue_block_editor_assets', array( $this, 'modify_editor' ) );
			global $wp_version;
			if ( ! class_exists( 'jQuery_Migrate_Helper' ) && ( version_compare( $wp_version, 6.0 ) < 0 ) ) {
				add_filter( 'ic_product_short_desc_input', array( $this, 'excerpt_textarea' ) );
			}
		}
	}

	function excerpt_textarea() {
		global $post;
		ob_start();
		post_excerpt_meta_box( $post );

		return ob_get_clean();
	}

	function modify_boxes() {
		remove_meta_box( 'al_product_desc', 'al_product', 'normal' );
	}

	function modify_editor() {
		if ( is_ic_edit_product_screen() || is_ic_new_product_screen() ) {
			global $wp_version;
			if ( version_compare( $wp_version, 6.5 ) < 0 ) {
				wp_enqueue_script( 'ic_epc_modify_editor', AL_PLUGIN_BASE_PATH . 'includes/blocks/js/modify-editor.js' . ic_filemtime( AL_BASE_PATH . '/includes/blocks/js/modify-editor.js' ), array( 'wp-edit-post' ), IC_EPC_VERSION, true );
			} else {
				wp_enqueue_script( 'ic_epc_modify_editor', AL_PLUGIN_BASE_PATH . 'includes/blocks/js/modify-editor-65.js' . ic_filemtime( AL_BASE_PATH . '/includes/blocks/js/modify-editor-65.js' ), array( 'wp-edit-post' ), IC_EPC_VERSION, true );
			}
		}
	}

	function edit_settings( $settings ) {
		if ( ! $this->enable_switcher() ) {
			return;
		}
		?>
        <h3><?php _e( 'Edit Mode', 'ecommerce-product-catalog' ); ?></h3>
        <table><?php
			implecode_settings_radio( __( 'Product Edit Mode', 'ecommerce-product-catalog' ), 'archive_multiple_settings[edit_mode]', $settings['edit_mode'], array(
				'classic' => __( 'Classic Editor', 'ecommerce-product-catalog' ),
				'blocks'  => __( 'Blocks', 'ecommerce-product-catalog' ) . ' (Gutenberg)',
				//'full_blocks' => __( 'Blocks for entire page', 'ecommerce-product-catalog' ) . ' (Gutenberg)',
			), 1, __( 'Choose how would you like to edit the products.', 'ecommerce-product-catalog' ) );
			?>
        </table>
		<?php
		if ( $settings['edit_mode'] !== 'blocks' && $this->is_forced() ) {
			?>
            <script>
                jQuery('[name="archive_multiple_settings[edit_mode]"][value="classic"]').prop('disabled', true);
                jQuery('[name="archive_multiple_settings[edit_mode]"][value="classic"]').prop('checked', false);
                jQuery('[name="archive_multiple_settings[edit_mode]"][value="blocks"]').prop('checked', true);
            </script>
			<?php
		}
	}

	function default_edit( $settings ) {
		$settings['edit_mode'] = ! empty( $settings['edit_mode'] ) ? $settings['edit_mode'] : $this->default_mode();

		return $settings;
	}

	function ret_true() {
		return true;
	}

	function enabled() {
		if ( ! $this->enable_switcher() ) {
			$mode = $this->default_mode();
		} else {
			$archive_multiple_settings = get_multiple_settings();
			$mode                      = $archive_multiple_settings['edit_mode'];
		}
		if ( $mode === 'blocks' ) {
			return true;
		}
		if ( $this->is_forced() ) {
			return true;
		}

		return false;
	}

	function is_forced() {
		$post_type         = 'al_product';
		$current_post_type = get_post_type();
		if ( ! empty( $current_post_type ) ) {
			if ( get_quasi_post_type( $current_post_type ) === $post_type ) {
				$post_type = $current_post_type;
			} else {
				$post_type = '';
			}
		}
		if ( ! empty( $post_type ) ) {
			global $ic_register_product;
			$removed = remove_filter( 'use_block_editor_for_post_type', array(
				$ic_register_product,
				'can_gutenberg'
			), 999, 2 );
			$forced  = apply_filters( 'use_block_editor_for_post_type', false, $post_type );
			if ( $removed ) {
				add_filter( 'use_block_editor_for_post_type', array( $ic_register_product, 'can_gutenberg' ), 999, 2 );
			}
			if ( $forced ) {

				return true;
			}
		}

		return false;
	}

	function default_mode() {
		$forced = $this->use_block_editor();
		if ( $forced ) {
			return 'blocks';
		} else {
			return 'classic';
		}
	}

	function enable_switcher() {
		if ( $this->is_managed_elsewhere() || $this->is_changed() ) {
			return false;
		} else {
			return true;
		}
	}

	function is_changed() {
		$forced = $this->use_block_editor();
		if ( $forced === 'not_changed' ) {
			return false;
		} else {
			return true;
		}
	}

	function use_block_editor() {
		global $ic_register_product;
		$removed_first = remove_filter( 'use_block_editor_for_post_type', array(
			$ic_register_product,
			'can_gutenberg'
		), 999, 2 );
		$forced        = apply_filters( 'use_block_editor_for_post_type', 'not_changed', 'al_product' );
		if ( 'post.php' === $GLOBALS['pagenow'] && isset( $_GET['action'], $_GET['post'] ) && 'edit' === $_GET['action'] && empty( $_GET['meta-box-loader'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$post_id        = intval( $_GET['post'] );
			$post           = get_post( $post_id );
			$removed_second = remove_filter( 'use_block_editor_for_post', array(
				$ic_register_product,
				'can_gutenberg'
			), 999, 2 );
			$forced         = apply_filters( 'use_block_editor_for_post', $forced, $post );
		}
		if ( ! empty( $removed_first ) ) {
			add_filter( 'use_block_editor_for_post_type', array( $ic_register_product, 'can_gutenberg' ), 999, 2 );
		}
		if ( ! empty( $removed_second ) ) {
			add_filter( 'use_block_editor_for_post', array( $ic_register_product, 'can_gutenberg' ), 999, 2 );
		}

		return $forced;
	}

	function is_managed_elsewhere() {
		if ( class_exists( 'Classic_Editor' ) ) {
			return true;
		} else {
			return false;
		}
	}

}

$ic_epc_block_edit = new ic_epc_block_edit;

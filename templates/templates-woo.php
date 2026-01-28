<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WP Product template functions
 *
 * Here all plugin template functions are defined.
 *
 * @version        1.1.3
 * @package        ecommerce-product-catalog/
 * @author        impleCode
 */
class ic_woo_templates {

	public $theme;
	public $block = false;
	public $allow = false;
	public $processed = false;

	function __construct() {
		$this->setup();
		$this->hooks();
	}

	function hooks() {
		add_action( 'init', array( $this, 'apply_woo_templates' ) );
		add_action( 'wp_ajax_ic_is_woo_template_available', array( $this, 'ajax' ) );
		add_filter( 'admin_head', array( $this, 'ajax_script' ) );
	}

	function setup() {
		$this->theme = get_option( 'template' );
		//delete_option( 'ic_block_woo_template_file' );
		//delete_option( 'ic_allow_woo_template_file' );
		if ( get_option( 'ic_block_woo_template_file', 0 ) === $this->theme ) {
			$this->block     = true;
			$this->allow     = false;
			$this->processed = true;
		} else if ( get_option( 'ic_allow_woo_template_file', 0 ) === $this->theme ) {
			$this->block     = false;
			$this->allow     = true;
			$this->processed = true;
		}
	}

	function ajax() {
		$this->is_woo_template_available();
		wp_die();
	}

	function ajax_script() {
		if ( $this->processed || ! $this->let_template_error_check() ) {
			return;
		}
		?>
        <script>
            jQuery(document).ready(function () {
                var data = {
                    'action': 'ic_is_woo_template_available'
                };
                jQuery.post(ajaxurl, data);
            });
        </script>
		<?php

	}

	function apply_woo_templates() {
		if ( $this->let_template_error_check() && $this->is_woo_template_available() ) {
			add_action( 'before_product_archive', array( $this, 'woo_before_templates' ) );
			add_action( 'before_product_page', array( $this, 'woo_before_templates' ) );
			add_action( 'after_product_archive', array( $this, 'woo_after_templates' ) );
			add_action( 'after_product_page', array( $this, 'woo_after_templates' ) );
		}
	}

	function block() {
		update_option( 'ic_block_woo_template_file', $this->theme );
		delete_option( 'ic_allow_woo_template_file' );
		$this->block = true;
		$this->allow = false;


		return;
	}

	function allow() {
		update_option( 'ic_allow_woo_template_file', $this->theme );
		delete_option( 'ic_block_woo_template_file' );
		$this->block = false;
		$this->allow = true;

		return;
	}

	function let_template_error_check() {
		/*
		  if ( PHP_MAJOR_VERSION < 7  && !class_exists( 'WooCommerce' ) && version_compare( IC_EPC_FIRST_VERSION, '2.8.6', '<' ) ) {
		  return false;
		  }
		 *
		 */
		if ( is_integration_mode_selected() || is_theme_implecode_supported() || is_ic_shortcode_integration( null, false ) || isset( $_GET['test_advanced'] ) ) {
			return false;
		}
		if ( is_ic_activation_hook() ) {
			return false;
		}

		return true;
	}

	function woo_before_templates() {
		global $wp_filter;
		if ( isset( $wp_filter['woocommerce_before_main_content'] ) ) {
			if ( isset( $wp_filter['woocommerce_before_main_content']->callbacks ) && is_array( $wp_filter['woocommerce_before_main_content']->callbacks ) ) {
				$callbacks = $wp_filter['woocommerce_before_main_content']->callbacks;
				foreach ( $callbacks as $priority => $call ) {
					if ( $priority != 10 ) {
						remove_all_actions( 'woocommerce_before_main_content', $priority );
					}
				}
			}
			remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
			do_action( 'woocommerce_before_main_content' );
		}
	}

	function woo_after_templates() {
		global $wp_filter;
		if ( isset( $wp_filter['woocommerce_after_main_content'] ) ) {
			if ( isset( $wp_filter['woocommerce_before_main_content']->callbacks ) && is_array( $wp_filter['woocommerce_before_main_content']->callbacks ) ) {
				$callbacks = $wp_filter['woocommerce_after_main_content']->callbacks;
				foreach ( $callbacks as $priority => $call ) {
					if ( $priority != 10 ) {
						remove_all_actions( 'woocommerce_after_main_content', $priority );
					}
				}
			}
			remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
			do_action( 'woocommerce_after_main_content' );
		}
	}

	function is_woo_template_available() {

		if ( $this->let_template_error_check() ) {

			if ( ! $this->processed && ! is_ic_ajax( 'ic_is_woo_template_available' ) ) {

				return false;
			}
			if ( $this->block ) {

				return false;
			}
			if ( $this->allow && ! is_ic_ajax( 'ic_is_woo_template_available' ) ) {
				return true;
			}
			ic_catalog_template::woo_functions();

			if ( $this->handle_error( 'woo_before_templates' ) ) {
				return true;
			} else {

				return $this->try_woo_template_file();
			}
		}

		return false;
	}

	function try_woo_template_file() {
		if ( ! $this->let_template_error_check() ) {
			return false;
		}
		if ( $this->block ) {
			return false;
		}
		$path = '';
		if ( is_readable( get_stylesheet_directory() . '/woocommerce.php' ) ) {
			$path = get_stylesheet_directory() . '/woocommerce.php';
		} else if ( is_readable( get_template_directory() . '/woocommerce.php' ) ) {
			$path = get_template_directory() . '/woocommerce.php';
		}
		if ( ! empty( $path ) ) {
			$product_adder_path = get_product_adder_path( true, true );
			if ( ! file_exists( $product_adder_path ) ) {
				$main_file_contents = file_get_contents( $path );
				if ( ic_string_contains( $main_file_contents, 'woocommerce_content' ) && copy( $path, $product_adder_path ) ) {
					$file_contents = file_get_contents( $product_adder_path );
					if ( ic_string_contains( $file_contents, 'woocommerce_content' ) ) {
						$new_file_contents = str_replace( "woocommerce_content", "content_product_adder", $file_contents );
						file_put_contents( $product_adder_path, $new_file_contents );
						$this->handle_error( $product_adder_path );

						return true;
					}
				}
			} else {
				$this->handle_error( $product_adder_path );

				return true;
			}
		} else {
			$this->block();
		}

		return false;
	}

	function handle_error( $product_adder_path ) {
		if ( $this->allow ) {
			return true;
		}

		if ( $this->check_for_errors( $product_adder_path ) ) {
			if ( is_file( $product_adder_path ) ) {
				unlink( $product_adder_path );
			}
			if ( ! method_exists( $this, $product_adder_path ) ) {
				$this->block();
			} else {
				return false;
			}
		} else {
			$this->allow();
		}

		if ( ! method_exists( $this, $product_adder_path ) ) {
			ic_redirect_to_same();
		}

		return true;
	}

	function check_for_errors( $path ) {
		if ( is_ic_activation_hook() ) {
			return false;
		}
		set_error_handler( array( $this, 'error_handler' ) );
		ic_save_global( 'check_for_errors_path', $path );
		register_shutdown_function( array( $this, 'shutdown_get_errors' ) );
		try {
			ob_start();

			if ( is_file( $path ) ) {

				include( $path );
			} else if ( method_exists( $this, $path ) ) {
				$this->$path();
			} else {

				throw new Error( 'No Catalog Content' );
			}
			$content = ob_get_clean();
			if ( empty( $content ) ) {

				throw new Error( 'No Catalog Content' );
			}
		} catch ( Throwable $e ) {
			restore_error_handler();

			return true;
		} catch ( Exception $e ) {
			restore_error_handler();

			return true;
		} catch ( Error $e ) {
			restore_error_handler();

			return true;
		}
		restore_error_handler();

		return false;
	}

	function error_handler( $errno, $errstr, $errfile, $errline ) {
		if ( 0 === error_reporting() ) {
			return false;
		}

		throw new ErrorException( $errstr, 0, $errno, $errfile, $errline );
	}

	function shutdown_get_errors() {
		$error = error_get_last();
		if ( isset( $error['type'] ) && ( $error['type'] === E_ERROR || $error['type'] === E_WARNING ) ) {
			$path = ic_get_global( 'check_for_errors_path' );
			if ( ! empty( $path ) ) {
				if ( is_file( $path ) ) {
					unlink( $path );
				}
				$this->block();
				exit( 1 );
			}
		}
	}

}

global $ic_woo_templates;
$ic_woo_templates = new ic_woo_templates;

function ic_is_woo_template_available() {
	global $ic_woo_templates;
	if ( ! empty( $ic_woo_templates ) ) {
		return $ic_woo_templates->is_woo_template_available();
	}

	return false;
}

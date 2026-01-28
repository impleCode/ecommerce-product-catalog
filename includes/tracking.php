<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Tracking functions for reporting plugin usage to the implecode site for users that have opted in
 *
 * @version        1.1.1
 * @package        ecommerce-product-catalog/includes
 * @author        impleCode
 */

/**
 * Usage tracking
 *
 * @access public
 * @return void
 * @since  2.7.2
 */
class IC_EPC_Tracking {

	/**
	 * The data to send to the IC site
	 *
	 * @access private
	 */
	private $data;

	/**
	 * Get things going
	 *
	 * @access public
	 */
	public function __construct() {

		add_action( 'init', array( $this, 'schedule_send' ) );
		add_action( 'admin_init', array( $this, 'init' ) );
		$this->init_errors();
	}

	function init() {
		add_filter( 'pre_update_option_ic_epc_allow_tracking', array( $this, 'check_for_settings_optin' ) );
		add_action( 'ic_epc_opt_into_tracking', array( $this, 'check_for_optin' ) );
		add_action( 'ic_epc_opt_out_of_tracking', array( $this, 'check_for_optout' ) );
		add_action( 'ic_catalog_admin_priority_notices', array( $this, 'admin_notice' ), - 2 );
		add_action( 'ic_system_tools', array( $this, 'settings_optin' ) );
		add_filter( 'ic_epc_links', array( $this, 'confirm_deactivation' ) );
		add_action( 'wp_ajax_ic_submit_deactivation_reason', array( $this, 'submit_deactivation_reason' ) );
	}

	function init_errors() {
		add_filter( 'recovery_mode_email', array( $this, 'paused_plugin_report_email' ), 10, 2 );
		register_shutdown_function( array( $this, 'fatal' ) );
	}

	/**
	 * Check if the user has opted into tracking
	 *
	 * @access private
	 * @return bool
	 */
	private function tracking_allowed() {
		return (bool) get_option( 'ic_epc_allow_tracking', false );
	}

	/**
	 * Setup the data that is going to be tracked
	 *
	 * @access private
	 * @return void
	 */
	private function setup_data() {

		$data = array();

		$data['php_version']    = phpversion();
		$data['plugin_name']    = defined( 'IC_CATALOG_PLUGIN_NAME' ) ? IC_CATALOG_PLUGIN_NAME : 'not set';
		$data['plugin_version'] = defined( 'IC_EPC_VERSION' ) ? IC_EPC_VERSION : 'not set';
		$data['wp_version']     = get_bloginfo( 'version' );
		$data['server']         = isset( $_SERVER['SERVER_SOFTWARE'] ) ? $_SERVER['SERVER_SOFTWARE'] : '';

		$data['install_ver'] = (string) get_option( 'first_activation_version', 'not set' );

		$data['multisite'] = is_multisite();
		$data['url']       = home_url();

// Retrieve current theme info
		$theme_data = wp_get_theme();
		if ( $theme_data->exists() ) {
			$theme              = $theme_data->display( 'Name' ) . ' ' . $theme_data->display( 'Version' );
			$data['theme']      = $theme;
			$data['theme_name'] = $theme_data->display( 'Name' );
		} else {
			$data['theme']      = 'Incorrect theme data';
			$data['theme_name'] = 'Incorrect theme data';
		}
		$data['integration'] = $this->get_integration_type();
// Retrieve current plugin information
		if ( ! function_exists( 'get_plugins' ) ) {
			include ABSPATH . '/wp-admin/includes/plugin.php';
		}

		$plugins        = array_keys( get_plugins() );
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $key => $plugin ) {
			if ( in_array( $plugin, $active_plugins ) ) {
// Remove active plugins from list so we can show active and inactive separately
				unset( $plugins[ $key ] );
			}
		}

		$data['active_plugins']   = implode( ',', $active_plugins );
		$data['inactive_plugins'] = implode( ',', $plugins );
		if ( function_exists( 'ic_products_count' ) ) {
			$data['products'] = ic_products_count();
		} else {
			$data['products'] = 'not set';
		}
		if ( function_exists( 'get_catalog_names' ) ) {
			$names                 = get_catalog_names();
			$data['product_label'] = $names['singular'];
		} else {
			$data['product_label'] = 'not set';
		}

		$data['locale'] = ( $data['wp_version'] >= 4.7 ) ? get_user_locale() : get_locale();

		$this->data = $data;
	}

	public function get_integration_type() {
		if ( ! function_exists( 'get_integration_type' ) ) {
			return 'not set';
		}
		$integration_type = get_integration_type();
		if ( is_advanced_mode_forced() ) {
			$integration_type .= ' - forced';
			if ( ic_is_woo_template_available() ) {
				$integration_type .= ' - woo';
			} else if ( is_theme_implecode_supported() ) {
				$integration_type .= ' - supported';
			} else if ( is_integraton_file_active() ) {
				$integration_type .= ' - file';
			}
			if ( is_ic_shortcode_integration() ) {
				$integration_type .= ' - shortcode';
			}
		} else if ( ! is_integration_mode_selected() ) {
			$integration_type .= ' - not selected';
		}

		return $integration_type;
	}

	/**
	 * Send the data to the impleCode server
	 *
	 * @access private
	 * @return void
	 */
	public function send_checkin( $override = false, $ignore_last_checkin = false ) {

		$home_url = trailingslashit( home_url() );
// Allows us to stop our own site from checking in, and a filter for our additional sites
		if ( $home_url === 'https://implecode.com/' || apply_filters( 'ic_epc_disable_tracking_checkin', false ) ) {
			return false;
		}

		if ( ! $this->tracking_allowed() && ! $override ) {
			return false;
		}

// Send a maximum of once per week
		$last_send = $this->get_last_send();
		if ( is_numeric( $last_send ) && $last_send > strtotime( '-1 week' ) && ! $ignore_last_checkin ) {
			return false;
		}

		$this->setup_data();
		$action_name = 'checkin';
		if ( empty( $last_send ) ) {
			$action_name .= '-first';
		}
		$request = wp_remote_post( 'https://check.implecode.com/?ic_epc_action=' . $action_name, array(
			'method'      => 'POST',
			'timeout'     => 20,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'body'        => $this->data,
			'sslverify'   => false,
			'user-agent'  => 'IC_EPC/' . IC_EPC_VERSION . '; ' . get_bloginfo( 'url' )
		) );

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		update_option( 'ic_epc_tracking_last_send', time(), false );

		return true;
	}

	/**
	 * Check for a new opt-in on settings save
	 *
	 *
	 * @access public
	 * @return array
	 */
	public function check_for_settings_optin( $input ) {
// Send an intial check in on settings save

		if ( ! empty( $input ) ) {
			$this->send_checkin( true );
		}

		return $input;
	}

	/**
	 * Show system tools checkbox
	 *
	 *
	 * @access public
	 * @return array
	 */
	public function settings_optin() {
		$checked = $this->tracking_allowed() ? 1 : 0;
		implecode_settings_checkbox( __( 'Send anonymous statistics', 'ecommerce-product-catalog' ), 'epc_allow_tracking', $checked );
	}

	/**
	 * Check for a new opt-in via the admin notice
	 *
	 * @access public
	 * @return void
	 */
	public function check_for_optin( $data ) {

		update_option( 'ic_epc_allow_tracking', 1, false );

		$this->send_checkin( true );

		update_option( 'ic_epc_tracking_notice', '1', false );
	}

	/**
	 * Check for a new opt-in via the admin notice
	 *
	 * @access public
	 * @return void
	 */
	public function check_for_optout( $data ) {
		delete_option( 'ic_epc_allow_tracking' );
		update_option( 'ic_epc_tracking_notice', '1', false );
		wp_redirect( remove_query_arg( 'ic_epc_action' ) );
		exit;
	}

	/**
	 * Get the last time a checkin was sent
	 *
	 * @access private
	 * @return false|string
	 */
	private function get_last_send() {
		return get_option( 'ic_epc_tracking_last_send' );
	}

	/**
	 * Schedule a weekly checkin
	 *
	 * @access public
	 * @return void
	 */
	public function schedule_send() {
// We send once a week (while tracking is allowed) to check in, which can be used to determine active sites
		add_action( 'ic_epc_weekly_scheduled_events', array( $this, 'send_checkin' ) );
	}

	/**
	 * Display the admin notice to users that have not opted-in or out
	 *
	 * @access public
	 * @return void
	 */
	public function admin_notice() {
		$hide_notice = get_option( 'ic_epc_tracking_notice' );

		if ( $hide_notice ) {
			return;
		}

		if ( get_option( 'ic_epc_allow_tracking', false ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if (
			stristr( network_site_url( '/' ), 'dev' ) !== false ||
			stristr( network_site_url( '/' ), 'localhost' ) !== false ||
			stristr( network_site_url( '/' ), ':8888' ) !== false // This is common with MAMP on OS X
		) {
			update_option( 'ic_epc_tracking_notice', '1', false );
		} else {
			$optin_url  = add_query_arg( 'ic_epc_action', 'opt_into_tracking' );
			$optout_url = add_query_arg( 'ic_epc_action', 'opt_out_of_tracking' );

			echo '<div class="updated"><p>';
			echo sprintf( __( 'Allow %s to track plugin usage? Opt-in to tracking to help in plugin development. No sensitive data is tracked.', 'ecommerce-product-catalog' ), IC_CATALOG_PLUGIN_NAME );
			echo '&nbsp;<a href="' . esc_url( $optin_url ) . '" class="button-primary">' . __( 'Allow', 'ecommerce-product-catalog' ) . '</a>';
			echo '&nbsp;<a href="' . esc_url( $optout_url ) . '" class="button-secondary">' . __( 'Do not allow', 'ecommerce-product-catalog' ) . '</a>';
			echo '</p></div>';
		}
	}

	/**
	 * Adds deactivation box script to plugin links
	 *
	 * @param type $links
	 */
	function confirm_deactivation( $links ) {
		if ( isset( $links['settings'] ) ) {
			$links['settings'] .= '<script>jQuery(document).ready(function() {
				var deactivate_link = jQuery("tr[data-slug=' . IC_CATALOG_PLUGIN_SLUG . '] span.deactivate a");
deactivate_link.click(function(e) {
	if (jQuery(this).data("prevented") === true) {
	jQuery(this).data("prevented", false);
        return;
}
	e.preventDefault();
	jQuery("div.ic_deactivate_confirm").show();
	jQuery(this).data("prevented", true);
});
jQuery(".ic_deactivate_bg, div.ic_deactivate_confirm a.button-secondary ").click(function(e) {
e.preventDefault();
jQuery("div.ic_deactivate_confirm").hide();
deactivate_link.data("prevented", false);
});
jQuery("div.ic_deactivate_confirm a.deactivate").click(function(e) {
e.preventDefault();
jQuery("div.ic_deactivate_confirm").hide();
window.location = deactivate_link.attr("href");
});
jQuery("div.ic_deactivate_confirm .button-primary").click(function(e) {
e.preventDefault();
var selected_reason = jQuery("input[name=deactivation-reason]:checked");
if (selected_reason.length) {
jQuery(".ic_deactivate_box .warning").hide();
jQuery(this).attr("disabled", true);
jQuery(this).text("' . __( 'Processing...', 'ecommerce-product-catalog' ) . '");
var reason = selected_reason.val();
var reason_desc = selected_reason.parent("p").next("p").find("textarea").val();
 var data = {
            "action": "ic_submit_deactivation_reason",
			"reason": reason,
			"reason_desc": reason_desc,
			"nonce": "' . wp_create_nonce( 'ic-ajax-nonce' ) . '"
        };
        jQuery.post( ajaxurl, data, function ( response ) {
            window.location = deactivate_link.attr("href");
        } );

} else {
jQuery(".ic_deactivate_box .warning").show();
}
});
jQuery("input[name=deactivation-reason]").click(function() {
jQuery(".ic_deactivate_box textarea").hide();
jQuery(this).parent("p").next("p").find("textarea").show();
});
});</script>';
			$links['settings'] .= $this->confirm_deactivation_box();
		}

		return $links;
	}

	function confirm_deactivation_box() {
		$box = '<div class="ic_deactivate_confirm">';
		$box .= '<div class="ic_deactivate_bg"></div>';
		$box .= '<div class="ic_deactivate_box">';
		$box .= '<div class="ic_deactivate_question">';
		$box .= '<h3>' . __( 'If you have a moment, please let us know why you are deactivating', 'ecommerce-product-catalog' ) . ':</h3>';
		foreach ( $this->confirm_deactivation_options() as $key => $field ) {
			$box .= '<p><input type="radio" id="' . $key . '" name="deactivation-reason" value="' . $key . '"> <label for="' . $key . '">' . $field['label'] . '</label></p>';
			$box .= '<p><textarea name="deactivation-reason-desc" placeholder="' . $field['placeholder'] . '"></textarea></p>';
		}
		$box .= '</div>';
		$box .= '<div class="ic_deactivate_buttons">';
		$box .= implecode_warning( __( 'Please choose a reason...', 'ecommerce-product-catalog' ), 0 );
		$box .= '&nbsp;<a href="" class="submit_deactivate button-primary">' . __( 'Submit & Deactivate', 'ecommerce-product-catalog' ) . '</a>';
		$box .= '&nbsp;&nbsp;&nbsp;<a href="" class="button-secondary">' . __( 'Cancel', 'ecommerce-product-catalog' ) . '</a>';
		$box .= '&nbsp;&nbsp;&nbsp;<a href="" class="deactivate">' . __( 'Skip', 'ecommerce-product-catalog' ) . '</a>';
		$box .= '</div>';
		$box .= '</div>';
		$box .= '</div>';

		return $box;
	}

	function confirm_deactivation_options() {
		$options = array(
			'dont-understand'        => array(
				'label'       => __( "I couldn't understand how to make it work", 'ecommerce-product-catalog' ),
				'placeholder' => __( "What could we do better?", 'ecommerce-product-catalog' )
			),
			'better-plugin'          => array(
				'label'       => __( "I found a better plugin", 'ecommerce-product-catalog' ),
				'placeholder' => __( "What's the plugin's name?", 'ecommerce-product-catalog' )
			),
			'missing-feature'        => array(
				'label'       => __( "The plugin is excellent, but I need a specific feature that you don't support", 'ecommerce-product-catalog' ),
				'placeholder' => __( "What feature?", 'ecommerce-product-catalog' )
			),
			'not-working'            => array(
				'label'       => __( "The plugin is not working", 'ecommerce-product-catalog' ),
				'placeholder' => __( "Kindly share what didn't work so we can fix it for future users...", 'ecommerce-product-catalog' )
			),
			'looking-something-else' => array(
				'label'       => __( "It's not what I was looking for", 'ecommerce-product-catalog' ),
				'placeholder' => __( "What you've been looking for?", 'ecommerce-product-catalog' )
			),
			'didnt-work'             => array(
				'label'       => __( "The plugin didn't work as expected", 'ecommerce-product-catalog' ),
				'placeholder' => __( "What did you expect?", 'ecommerce-product-catalog' )
			),
			'other'                  => array(
				'label'       => __( "Other", 'ecommerce-product-catalog' ),
				'placeholder' => __( "What is the reason?", 'ecommerce-product-catalog' )
			),
		);

		return $options;
	}

	function submit_deactivation_reason() {
		if ( ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'ic-ajax-nonce' ) && ! empty( $_POST['reason'] ) ) {
			$this->setup_data();
			$data                        = $this->data;
			$data['deactivation_reason'] = $_POST['reason'];
			if ( ! empty( $_POST['reason_desc'] ) ) {
				$data['deactivation_reason_desc'] = $_POST['reason_desc'];
			}
			wp_remote_post( 'https://check.implecode.com/?ic_epc_action=deactivation', array(
				'method'      => 'POST',
				'timeout'     => 20,
				'redirection' => 5,
				'httpversion' => '1.1',
				'blocking'    => true,
				'body'        => $data,
				'sslverify'   => false,
				'user-agent'  => 'IC_EPC/' . IC_EPC_VERSION . '; ' . get_bloginfo( 'url' )
			) );
		}
		wp_die();
	}

	function paused_plugin_report_email( $email, $url ) {
		if ( ! empty( $email['message'] ) && ( ic_string_contains( $email['message'], 'eCommerce Product Catalog' ) || ic_string_contains( $email['message'], 'ecommerce-product-catalog' ) ) ) {
			$this->send_paused_checkin( array( $email['message'] ) );
		}

		return $email;
	}

	function fatal() {
		$error = error_get_last();
		if ( ! empty( $error ) && ! empty( $error['file'] ) ) {
			if ( $this->supported_slug( $error['file'] ) ) {
				$message = '';
				if ( ! empty( $error['type'] ) ) {
					$message .= '[' . $error['type'] . '] ';
				}
				if ( ! empty( $error['message'] ) ) {
					$message .= str_replace( untrailingslashit( plugin_dir_path( AL_BASE_PATH ) ), '', $error['message'] ) . ' ';
				}
				$message .= 'in file ' . str_replace( untrailingslashit( plugin_dir_path( AL_BASE_PATH ) ), '', $error['file'] ) . ' ';

				if ( ! empty( $error['line'] ) ) {
					$message .= 'on line ' . $error['line'] . ' ';
				}
				if ( defined( 'IC_CATALOG_VERSION' ) ) {
					$message .= '[' . IC_CATALOG_VERSION . ']';
				}
				/** Fully anonymized error report */
				$this->send_paused_checkin( array( $message ), true );
			}

		}
	}

	function supported_slug( $file ) {
		if ( empty( $file ) || ! is_string( $file ) ) {
			return false;
		}
		if ( defined( 'AL_BASE_PATH' ) && strpos( $file, AL_BASE_PATH ) !== false ) {
			return true;
		} else {
			$slugs = $this->slugs();
			foreach ( $slugs as $slug ) {
				if ( strpos( $file, $slug ) !== false ) {
					return true;
				}
			}
		}

		return false;
	}

	function slugs() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$all_plugins = get_plugins();
		if ( empty( $all_plugins ) ) {
			return array();
		}
		$slugs = array();
		foreach ( $all_plugins as $name => $plugin ) {
			if ( $plugin['Author'] !== 'impleCode' ) {
				continue;
			}
			$name = explode( '/', $name );
			if ( ! in_array( $name[0], $slugs ) ) {
				$slugs[] = $name[0];
			}
		}

		return $slugs;
	}

	/**
	 * Send the data to the impleCode server
	 *
	 * @access private
	 * @return void
	 */
	public function send_paused_checkin( $errors, $override = false, $ignore_last_checkin = false ) {

		$home_url = trailingslashit( home_url() );
// Allows us to stop our own site from checking in, and a filter for our additional sites
		if ( $home_url === 'https://implecode.com/' || apply_filters( 'ic_epc_disable_tracking_checkin', false ) ) {
			return false;
		}

		if ( ! $this->tracking_allowed() && ! $override ) {
			return false;
		}

// Send a maximum of once per day
		$last_send = $this->get_last_paused_send();
		if ( is_numeric( $last_send ) && $last_send > strtotime( '-1 day' ) && ! $ignore_last_checkin ) {
			return false;
		}

		$this->setup_data();
		$anonymize            = array( WP_PLUGIN_DIR, untrailingslashit( plugin_dir_path( AL_BASE_PATH ) ) );
		$this->data['errors'] = str_replace( $anonymize, '', json_encode( $errors ) );
		if ( ! $this->tracking_allowed() ) {
			/** Anonymize if forced checkin */
			$this->data['url'] = 'not_provided';
		}
		$action_name = 'error';
		if ( empty( $last_send ) ) {
			$action_name .= '-first';
		}
		$request = wp_remote_post( 'https://errors.implecode.com/?ic_epc_action=' . $action_name, array(
			'method'      => 'POST',
			'timeout'     => 20,
			'redirection' => 5,
			'httpversion' => '1.1',
			'blocking'    => true,
			'body'        => $this->data,
			'sslverify'   => false,
			'user-agent'  => 'IC_EPC/' . IC_EPC_VERSION . '; '
		) );

		if ( is_wp_error( $request ) ) {
			return $request;
		}

		update_option( 'ic_epc_tracking_last_paused_send', time(), false );

		return true;
	}

	/**
	 * Get the last time a checkin was sent
	 *
	 * @access private
	 * @return false|string
	 */
	private function get_last_paused_send() {
		return get_option( 'ic_epc_tracking_last_paused_send' );
	}

}

add_action( 'admin_init', 'ic_epc_get_actions', 99 );

function ic_epc_get_actions() {
	if ( isset( $_GET['ic_epc_action'] ) ) {
		do_action( 'ic_epc_' . $_GET['ic_epc_action'], $_GET );
	}
}

$ic_epc_tracking = new IC_EPC_Tracking;

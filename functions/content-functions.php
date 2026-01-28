<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Manages product content functions
 *
 * Here all plugin content functions are defined and managed.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        impleCode
 */
/* Classic List */

function c_list_desc( $post_id = null, $shortdesc = null ) {
	if ( $shortdesc == '' && ! empty( $post_id ) ) {
		$shortdesc = clean_short_description( $post_id );
	} else if ( ! empty( $shortdesc ) ) {
		$shortdesc = strip_tags( $shortdesc );
		$shortdesc = trim( strip_shortcodes( $shortdesc ) );
		$shortdesc = str_replace( array( "\r\n" ), ' ', $shortdesc );
	} else {
		return '';
	}
	$desclenght = strlen( $shortdesc );
	$more       = '';
	$limit      = apply_filters( 'c_list_desc_limit', 243 );
	if ( $desclenght > $limit ) {
		$more = ' [...]';
	}

	return apply_filters( 'c_list_desc_content', ic_substr( $shortdesc, 0, $limit ) . $more, $post_id );
}

/**
 * Returns short description text without HTML
 *
 * @param int $product_id
 *
 * @return string
 */
function clean_short_description( $product_id, $new_line = ' ' ) {
	$shortdesc = get_product_short_description( $product_id );
	$shortdesc = strip_tags( $shortdesc );
	$shortdesc = trim( strip_shortcodes( $shortdesc ) );
	$shortdesc = str_replace( array( "\r\n", "\r", "\n" ), $new_line, $shortdesc );

	return trim( preg_replace( '/\s\s+/', ' ', $shortdesc ) );
}

/* Single Product */
add_action( 'single_product_end', 'add_back_to_products_url', 99, 2 );

/**
 *
 * @param object $post
 * @param array $single_names
 * @param string $taxonomies
 */
function add_back_to_products_url( $post, $single_names ) {
	$force_back_url = apply_filters( 'force_back_to_products_url', false );
	if ( is_ic_product_listing_enabled() || $force_back_url ) {
		echo get_back_to_products_url( $single_names );
	}
}

/**
 * Returns back to products URL
 *
 * @param array $v_single_names
 *
 * @return string
 */
function get_back_to_products_url( $v_single_names = null ) {
	if ( is_ic_product_listing_enabled() ) {
		$single_names = isset( $v_single_names ) ? $v_single_names : get_single_names();
		$listing_url  = product_listing_url();
		if ( ! empty( $listing_url ) ) {
			$url = '<a class="back-to-products" href="' . product_listing_url() . '">' . $single_names['return_to_archive'] . '</a>';

			return $url;
		}
	}

	return;
}

/**
 * Shows product search form
 */
function product_search_form() {
	$search_button_text = __( 'Search', 'ecommerce-product-catalog' );
	$search_placeholder = ic_get_search_widget_placeholder();
	echo '<form role="search" method="get" class="search-form product_search_form" action="' . esc_url( home_url( '/' ) ) . '">
<input type="hidden" name="post_type" value="' . get_current_screen_post_type() . '" />
<input class="product-search-box" type="search" value="' . get_search_query() . '" id="s" name="s" placeholder="' . $search_placeholder . '" />
<input class="search-submit product-search-submit" type="submit" value="' . esc_attr( $search_button_text ) . '" />
</form>';
}

function ic_enqueue_main_catalog_js_css() {
	$enqueued = ic_get_global( 'enqueued_main_js_css' );
	if ( $enqueued ) {
		return;
	}
	if ( ! did_action( 'ic_catalog_localize_scripts' ) ) {
		do_action( 'ic_catalog_localize_scripts' );
	}
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'al_product_styles' );
	wp_enqueue_script( 'al_product_scripts' );
	do_action( 'enqueue_main_catalog_scripts' );
	ic_save_global( 'enqueued_main_js_css', 1, false, false, true );
}

add_action( 'ic_catalog_localize_scripts', 'ic_localize_main_catalog_js' );

function ic_localize_main_catalog_js() {
	if ( ! function_exists( 'admin_url' ) ) {
		return;
	}
	$colorbox_set = json_decode( apply_filters( 'colorbox_set', '{"transition": "elastic", "initialWidth": 200, "maxWidth": "90%", "maxHeight": "90%", "rel":"gal"}', ic_get_product_id() ) );
	$localize     = apply_filters( 'ic_catalog_product_object_js', array(
		'ajaxurl'             => admin_url( 'admin-ajax.php' ),
		'post_id'             => ic_get_product_id(),
		'lightbox_settings'   => $colorbox_set,
		'filter_button_label' => __( 'Filter', 'ecommerce-product-catalog' ),
		'design_schemes'      => design_schemes( 'box', 0 ),
		'loading'             => get_site_url() . '/wp-includes/js/thickbox/loadingAnimation.gif',
	) );
	//if ( is_user_logged_in() ) {
	$localize['nonce'] = wp_create_nonce( 'ic-ajax-nonce' );
	//}
	wp_localize_script( 'al_product_scripts', 'product_object', $localize );
}

if ( ! function_exists( 'ic_popup' ) ) {
	/**
	 * Renders a popup with customizable content, buttons, and classes.
	 *
	 * @param string $content The content to display in the popup.
	 * @param string $class Optional. Additional CSS classes for the popup. Default is an empty string.
	 * @param string|array|null $ok_url Optional. URL or array of buttons for the "OK" action. Default is null.
	 * @param string $ok_label Optional. Label for the "OK" button. Default is an empty string.
	 * @param string $cancel_label Optional. Label for the "Cancel" button. Default is an empty string.
	 * @param string $ok_class Optional. CSS classes for the "OK" button. Default is 'ic-popup-ok'.
	 * @param string $cancel_class Optional. CSS classes for the "Cancel" button. Default is 'ic-popup-cancel'.
	 * @param array $additional_buttons Optional. Additional buttons to display in the popup. Default is an empty array.
	 *
	 * @return void
	 */
	function ic_popup( $content, $class = '', $ok_url = null, $ok_label = '', $cancel_label = '', $ok_class = 'ic-popup-ok', $cancel_class = 'ic-popup-cancel', $additional_buttons = array(), $show_by_default = false ) {
		$html = new ic_html_util;
		if ( ! $show_by_default ) {
			$class .= ' ic-hidden';
		}
		if ( ! empty( $ok_url ) && ! is_array( $ok_url ) ) {
			if ( empty( $ok_label ) ) {
				$ok_label = __( 'OK', 'ecommerce-product-catalog' );
			}
			if ( empty( $cancel_label ) ) {
				$cancel_label = __( 'Cancel', 'implecode-quote-cart' );
			}
			$buttons   = array_merge( array(
				array(
					'label' => $ok_label,
					'class' => $ok_class,
					'url'   => $ok_url
				),
			), $additional_buttons );
			$buttons[] = array(
				'label' => $cancel_label,
				'class' => 'ic-secondary-button ' . $cancel_class,
			);
			if ( $show_by_default && is_user_logged_in() ) {
				$buttons[] = array(
					'label' => __( 'Never show again', 'implecode-quote-cart' ),
					'class' => 'ic-secondary-button ic-popup-never-show',
				);
			}
		} else if ( is_array( $ok_url ) ) {
			$buttons = $ok_url;
		} else {
			$buttons = '';
		}
		echo $html->popup( $content, $buttons, $class );
	}
}
if ( ! function_exists( 'create_ic_overlay' ) ) {

	function create_ic_overlay() {
		echo '<div id="ic_overlay" class="ic-overlay" style="display:none"></div>';
	}

}

add_action( 'wp_ajax_ic_user_hide_content', 'ic_hide_content_for_user' );
add_action( 'wp_ajax_nopriv_ic_user_hide_content', 'ic_hide_content_for_user' );

/**
 * Hides specific content for the user based on the provided hash from a POST request.
 * Retrieves the 'hash' parameter from the POST request, and if it's not empty,
 * calls the function to hide the content for the user.
 *
 * @return void Terminates script execution after processing the request.
 */
function ic_hide_content_for_user() {
	$popup_hash = isset( $_POST['hash'] ) ? $_POST['hash'] : '';
	if ( ! empty( $popup_hash ) ) {
		ic_user_hide_content( $popup_hash );
	}

	wp_die();
}

/**
 * Hides specific content for a user by adding the content hash to the user's hidden content metadata.
 *
 * @param string $content_hash The hash of the content to be hidden.
 * @param int|string $user_id Optional. The ID of the user. Defaults to the current logged-in user's ID.
 *
 * @return void
 */
function ic_user_hide_content( $content_hash, $user_id = '' ) {
	if ( empty( $content_hash ) ) {
		return;
	}
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}
	if ( empty( $user_id ) || ! is_numeric( $user_id ) ) {
		return;
	}
	if ( ! ic_is_user_hidden_content( $content_hash, $user_id ) ) {
		$hidden_content   = ic_user_hidden_content( $user_id );
		$hidden_content[] = $content_hash;
		update_user_meta( $user_id, '_ic_hidden_content', $hidden_content );
	}
}

/**
 * Retrieves the hidden content for a specific user.
 *
 * @param int $user_id The ID of the user. If empty, the current logged-in user's ID is used.
 *
 * @return array An array of hidden content for the specified user.
 */
function ic_user_hidden_content( $user_id ) {
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}
	if ( empty( $user_id ) || ! is_numeric( $user_id ) ) {
		return array();
	}
	$hidden = get_user_meta( $user_id, '_ic_hidden_content', true );
	if ( empty( $hidden ) ) {
		$hidden = array();
	}

	return $hidden;
}

/**
 * Checks if the specified content is hidden for a given user.
 *
 * @param string $content_hash The unique identifier for the content being checked.
 * @param int|string $user_id Optional. The user ID. Defaults to the current user's ID.
 *
 * @return bool Returns true if the content is hidden for the user, false otherwise.
 */
function ic_is_user_hidden_content( $content_hash, $user_id = '' ) {
	if ( empty( $content_hash ) ) {
		return false;
	}
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}
	if ( empty( $user_id ) || ! is_numeric( $user_id ) ) {
		return false;
	}
	$hidden_content = ic_user_hidden_content( $user_id );
	if ( in_array( $content_hash, $hidden_content ) ) {
		return true;
	}

	return false;
}

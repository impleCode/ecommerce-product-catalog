<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WP Product template manager
 *
 * Here all plugin templates are defined.
 *
 * @version        1.1.2
 * @package        ecommerce-product-catalog/
 * @author        impleCode
 */
class ic_catalog_template {

	private $template = 'file';

	function __construct() {

		$this->files();
		//add_action( 'ic_epc_loaded', array( $this, 'files' ) );
		//add_action( 'after_setup_theme', array( $this, 'init' ) );
		add_action( 'ic_epc_loaded', array( $this, 'init' ) );
	}

	function init() {
		//$this->load_templates();
		add_action( 'ic_catalog_wp', array( __CLASS__, 'setup_postdata' ) );
		add_action( 'ic_catalog_wp', array( $this, 'load_templates' ) );

		/**
		 * Home product listing filters
		 */
		add_filter( 'template_include', array( __CLASS__, 'home_product_listing_redirect' ), 5 );
		add_filter( 'redirect_canonical', array( __CLASS__, 'disable_redirect_canonical' ) );
	}

	function files() {
		require_once( AL_BASE_PATH . '/templates/templates-conditionals.php' );
		require_once( AL_BASE_PATH . '/templates/theme-integration.php' );
		require_once( AL_BASE_PATH . '/templates/templates-files.php' );
		require_once( AL_BASE_PATH . '/templates/templates-functions.php' );
		require_once( AL_BASE_PATH . '/templates/templates-woo.php' );
		require_once( AL_BASE_PATH . '/templates/shortcode-catalog.php' );
		require_once( AL_BASE_PATH . '/templates/block-catalog.php' );
	}

	function load_templates() {
		if ( ! is_ic_shortcode_integration() ) {
			//add_action( 'after_setup_theme', array( $this, 'initialize_product_adder_template' ), 99 );
			add_action( 'template_redirect', array( $this, 'initialize_product_adder_template' ), 99 );
		}

		$theme = get_option( 'template' );
		if ( $theme === 'twentyseventeen' ) {
			require_once( AL_BASE_PATH . '/templates/templates-twenty-functions.php' );
		}
	}

	/**
	 * Includes correct template file
	 *
	 */
	function initialize_product_adder_template() {
		$theme           = get_option( 'template' );
		$woothemes       = array( "canvas", "woo", "al" );
		$twentyeleven    = array( "twentyeleven" );
		$twentyten       = array( "twentyten" );
		$twentythirteen  = array( "twentythirteen" );
		$twentyfourteen  = array( "twentyfourteen" );
		$twentyfifteen   = array( "twentyfifteen" );
		$twentysixteen   = array( "twentysixteen" );
		$twentyseventeen = array( "twentyseventeen" );
		$twentynineteen  = array( "twentynineteen" );
		$third_party     = array( "storefront" );
		$all_themes      = array_merge( $woothemes, $twentyeleven, $twentyten, $twentythirteen, $twentyfourteen, $twentyfifteen, $twentysixteen, $twentyseventeen, $twentynineteen, $third_party );
		if ( is_integraton_file_active() ) {
			$this->template = 'file';
		} else if ( in_array( $theme, $all_themes ) ) {

			if ( in_array( $theme, $woothemes ) ) {
				$this->template = 'third-party/product-woo-adder.php';
			} else if ( in_array( $theme, $third_party ) ) {
				$this->template = 'third-party/' . $theme . '.php';
			} else if ( ic_string_contains( $theme, 'twenty' ) ) {
				$this->template = 'twenty/product-' . $theme . '-adder.php';
			}
		} else if ( is_integraton_file_active( true ) && ic_is_woo_template_available() ) {
			$this->template = 'auto';
			add_action( 'wp', array( __CLASS__, 'woo_functions' ) );
		} else if ( ic_is_woo_template_available() ) {
			$this->template = 'product-woo-adder.php';
			add_action( 'wp', array( __CLASS__, 'woo_functions' ) );
		} else if ( get_integration_type() == 'simple' ) {
			$this->template = 'page';
			add_filter( "the_content", array( __CLASS__, "product_page_content" ) );
			add_action( 'wp', array( __CLASS__, 'remove_product_comments_rss' ) );
		} else if ( get_integration_type() == 'theme' ) {
			$this->template = '';
			add_filter( "the_content", array( __CLASS__, "product_page_content" ) );
			add_action( 'wp', array( __CLASS__, 'remove_product_comments_rss' ) );
		} else {
			$this->template = 'product-adder.php';
		}
		if ( ! empty( $this->template ) ) {
			add_filter( 'template_include', array( $this, 'template_path' ), 99 );
		}
	}

	static function woo_functions() {
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
			require_once( AL_BASE_PATH . '/templates/templates-woo-functions.php' );
		}
	}

	function template_path( $template ) {
		if ( is_ic_catalog_page() && ! is_ic_shortcode_integration() ) {
			$type = $this->template;
			if ( empty( $type ) ) {
				return $template;
			}
			switch ( $type ) {
				case 'file':
					return get_product_adder_path();
				case 'auto':
					return get_product_adder_path( true );
				case 'page':
					return $this->theme_page_template( $template );
				default:
					return dirname( __FILE__ ) . '/templates/' . $type;
			}
		}

		return $template;
	}

	function theme_page_template( $template ) {
		if ( is_archive() || is_search() || is_tax() ) {
			$product_archive = get_product_listing_id();
			if ( ! empty( $product_archive ) && get_integration_type() != 'simple' ) {
				wp_redirect( get_permalink( $product_archive ) );
				exit;
			}
		}
		if ( file_exists( get_page_php_path() ) ) {
			return get_page_php_path();
		} else if ( file_exists( get_index_php_path() ) ) {
			return get_index_php_path();
		}

		return $template;
	}

	/**
	 * Postdata setup for product pages
	 *
	 * @global type $post
	 */
	static function setup_postdata() {
		if ( is_ic_catalog_page() ) {
			ic_set_product_id( get_the_ID() );
			global $post;
			if ( isset( $post->post_content ) && empty( $post->post_content ) && ( get_integration_type() == 'simple' || is_ic_shortcode_integration() ) ) {
				$post->post_content = ' ';
			}
			setup_postdata( $post );
		}
	}

	static function product_page_content( $content ) {
		$integration_type = get_integration_type();
		if ( is_main_query() && in_the_loop() && is_ic_product_page() && ! is_ic_shortcode_integration() && ( $integration_type == 'simple' || $integration_type === 'theme' ) ) {
			remove_filter( "the_content", array( __CLASS__, "product_page_content" ) );
			ob_start();
			content_product_adder();
			$content = ob_get_contents();
			ob_end_clean();
		}

		return $content;
	}

	/**
	 * Redirects the product listing page to homepage catalog if necessary
	 *
	 * @param type $template
	 *
	 * @return type
	 */
	static function home_product_listing_redirect( $template ) {
		if ( ! is_paged() && ! is_front_page() && is_ic_permalink_product_catalog() && is_product_listing_home_set() && is_post_type_archive( 'al_product' ) && ! is_search() ) {
			wp_redirect( get_site_url(), 301 );
			exit;
		}

		return $template;
	}

	/**
	 * Fixes wrong pagination redirect on home page catalog listing
	 *
	 * @param boolean $redirect_url
	 *
	 * @return boolean
	 */
	static function disable_redirect_canonical( $redirect_url ) {
		if ( is_paged() && is_front_page() && is_ic_permalink_product_catalog() && is_product_listing_home_set() ) {
			$redirect_url = false;
		}

		return $redirect_url;
	}

	static function remove_product_comments_rss( $url ) {
		if ( get_integration_type() == 'simple' && is_ic_product_page() ) {
			remove_action( 'wp_head', 'feed_links_extra', 3 );
		}
	}

}

global $ic_catalog_template;
$ic_catalog_template = new ic_catalog_template;


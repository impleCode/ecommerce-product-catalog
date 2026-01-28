<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 *  Session handler class.
 *  Uses a custom table for session storage. Based on https://github.com/kloon/woocommerce-large-sessions.
 *  @version       1.0.0
 *  @author        impleCode
 *
 */

class ic_session {
	/**
	 * Cache prefix.
	 *
	 * @var string $_cache_prefix Cache prefix.
	 */
	protected $_cache_prefix = 'ic_cache';
	/**
	 * Cache group.
	 *
	 * @var string $cache_group Cache group.
	 */
	protected $_cache_group = 'implecode';
	/**
	 * Customer ID.
	 *
	 * @var int $_customer_id Customer ID.
	 */
	protected $_customer_id;

	/**
	 * Guest customer ID prefix.
	 *
	 * @var string $_random_customer_id_prefix Prefix.
	 */
	protected $_random_customer_id_prefix = 'ic_';

	/**
	 * Session Data.
	 *
	 * @var array $_data Data array.
	 */
	protected $_data = array();

	/**
	 * To save when the session needs saving.
	 *
	 * @var bool $_to_save When something changes
	 */
	protected $_to_save = false;

	/**
	 * Cookie name used for the session.
	 *
	 * @var string cookie name
	 */
	protected $_cookie;

	/**
	 * Stores session expiry.
	 *
	 * @var string session due to expire timestamp
	 */
	protected $_session_expiring;

	/**
	 * Stores session due to expire timestamp.
	 *
	 * @var string session expiration timestamp
	 */
	protected $_session_expiration;

	/**
	 * True when the cookie exists.
	 *
	 * @var bool Based on whether a cookie exists.
	 */
	protected $_has_cookie = false;

	/**
	 * Table name for session data.
	 *
	 * @var string Custom session table name
	 */
	protected $_table;

	/**
	 * Array containing all data including to save
	 *
	 * @var array All stored data
	 */
	public $all_data = array();

	/**
	 * Constructor for the session class.
	 */
	public function __construct() {
		$this->_cookie = 'wordpress_ic_session_' . COOKIEHASH;
		$this->_table  = $GLOBALS['wpdb']->prefix . 'ic_sessions';
	}

	/**
	 * Init hooks and session data.
	 *
	 */
	public function init() {
		if ( ! $this->create_table() ) {
			return false;
		}
		$this->init_session_cookie();

		add_action( 'shutdown', array( $this, 'save_data' ), 20 );
		add_filter( 'wp_die_ajax_handler', array( $this, 'save_data_filter' ) );
		add_filter( 'wp_redirect', array( $this, 'save_data_filter' ) );
		add_action( 'wp_logout', array( $this, 'destroy_session' ) );
		if ( ! wp_next_scheduled( 'ic_cleanup_sessions' ) ) {
			wp_schedule_event( time() + ( 6 * HOUR_IN_SECONDS ), 'twicedaily', 'ic_cleanup_sessions' );
		} else {
			add_action( 'ic_cleanup_sessions', array( $this, 'cleanup_sessions' ) );
		}

		return true;
	}

	/**
	 * Setup cookie and customer ID.
	 *
	 */
	public function init_session_cookie() {
		$cookie = $this->get_session_cookie();
		if ( $cookie ) {
			// Customer ID will be an MD5 hash id this is a guest session.
			$this->_customer_id        = $cookie[0];
			$this->_session_expiration = $cookie[1];
			$this->_session_expiring   = $cookie[2];
			$this->_has_cookie         = true;
			$this->_data               = $this->get_session_data();

			if ( ! $this->is_session_cookie_valid() ) {
				$this->destroy_session();
				$this->set_session_expiration();
			}

			// If the user logs in, update session.
			if ( is_user_logged_in() && strval( get_current_user_id() ) !== $this->_customer_id ) {
				$guest_session_id   = $this->_customer_id;
				$this->_customer_id = strval( get_current_user_id() );
				$this->_to_save     = true;
				$this->save_data( $guest_session_id );
				$this->set_customer_session_cookie();
			}

			// Update session if it's close to expiring.
			if ( time() > $this->_session_expiring ) {
				$this->set_session_expiration();
				$this->update_session_timestamp( $this->_customer_id, $this->_session_expiration );
			}
		} else {
			$this->set_session_expiration();
			$this->_customer_id = $this->generate_customer_id();
			$this->_data        = $this->get_session_data();
		}
	}

	/**
	 * Checks if session cookie is expired, or belongs to a logged-out user.
	 *
	 * @return bool Whether session cookie is valid.
	 */
	private function is_session_cookie_valid() {
		// If session is expired, session cookie is invalid.
		if ( time() > $this->_session_expiration ) {
			return false;
		}

		// If user has logged out, session cookie is invalid.
		if ( ! is_user_logged_in() && ! $this->is_customer_guest( $this->_customer_id ) ) {

			return false;
		}

		// Session from a different user is not valid. (Although from a guest user will be valid)
		if ( is_user_logged_in() && ! $this->is_customer_guest( $this->_customer_id ) && strval( get_current_user_id() ) !== strval( $this->_customer_id ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Sets the session cookie on-demand.
	 *
	 */
	public function set_customer_session_cookie() {
		$to_hash           = $this->_customer_id . '|' . $this->_session_expiration;
		$cookie_hash       = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
		$cookie_value      = $this->_customer_id . '||' . $this->_session_expiration . '||' . $this->_session_expiring . '||' . $cookie_hash;
		$this->_has_cookie = true;
		if ( ! isset( $_COOKIE[ $this->_cookie ] ) || $_COOKIE[ $this->_cookie ] !== $cookie_value ) {
			ic_setcookie( $this->_cookie, $cookie_value, $this->_session_expiration, $this->use_secure_cookie(), true );
		}
	}

	/**
	 * Should the session cookie be secure?
	 *
	 * @return bool
	 */
	protected function use_secure_cookie() {
		return ic_site_is_https() && is_ssl();
	}

	/**
	 * Return true if the current user has an active session, i.e. a cookie to retrieve values.
	 *
	 * @return bool
	 */
	public function has_session() {
		return isset( $_COOKIE[ $this->_cookie ] ) || $this->_has_cookie || is_user_logged_in(); // @codingStandardsIgnoreLine.
	}

	/**
	 * Set session expiration.
	 */
	public function set_session_expiration() {
		$this->_session_expiring   = time() + intval( apply_filters( 'ic_session_expiration', 3 * DAY_IN_SECONDS ) );
		$this->_session_expiration = $this->_session_expiring + HOUR_IN_SECONDS;
	}

	/**
	 * Generate a unique customer ID for guests, or return user ID if logged in.
	 *
	 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
	 *
	 * @return string
	 */
	public function generate_customer_id() {
		$customer_id = '';

		if ( is_user_logged_in() ) {
			$customer_id = strval( get_current_user_id() );
		}

		if ( empty( $customer_id ) ) {
			require_once ABSPATH . 'wp-includes/class-phpass.php';
			$hash        = new PasswordHash( 8, false );
			$customer_id = $this->_random_customer_id_prefix . substr( md5( $hash->get_random_bytes( 32 ) ), strlen( $this->_random_customer_id_prefix ) );
		}

		return $customer_id;
	}

	/**
	 * Checks if this is an auto-generated customer ID.
	 *
	 * @param string|int $customer_id Customer ID to check.
	 *
	 * @return bool Whether customer ID is randomly generated.
	 */
	private function is_customer_guest( $customer_id ) {
		$customer_id = strval( $customer_id );
		if ( empty( $customer_id ) ) {
			return true;
		}
		if ( $this->_random_customer_id_prefix === substr( $customer_id, 0, strlen( $this->_random_customer_id_prefix ) ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get the session cookie, if set. Otherwise, return false.
	 *
	 * Session cookies without a customer ID are invalid.
	 *
	 * @return bool|array
	 */
	public function get_session_cookie() {
		$cookie_value = isset( $_COOKIE[ $this->_cookie ] ) ? wp_unslash( $_COOKIE[ $this->_cookie ] ) : false; // @codingStandardsIgnoreLine.
		if ( empty( $cookie_value ) || ! is_string( $cookie_value ) ) {
			return false;
		}

		if ( substr_count( $cookie_value, '||' ) < 3 ) {
			return false;
		}

		list( $customer_id, $session_expiration, $session_expiring, $cookie_hash ) = explode( '||', $cookie_value );

		if ( empty( $customer_id ) ) {
			return false;
		}

		// Validate hash.
		$to_hash = $customer_id . '|' . $session_expiration;
		$hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );

		if ( empty( $cookie_hash ) || ! hash_equals( $hash, $cookie_hash ) ) {
			return false;
		}

		return array( $customer_id, $session_expiration, $session_expiring, $cookie_hash );
	}

	/**
	 * Get session data.
	 *
	 * @return array
	 */
	public function get_session_data() {
		return $this->has_session() ? (array) $this->get_session( $this->_customer_id, array() ) : array();
	}

	/**
	 * Gets a cache prefix. This is used in session names so the entire cache can be invalidated with 1 function call.
	 *
	 * @return string
	 */
	private function get_cache_prefix() {
		$time = wp_cache_get( $this->_cache_prefix . '_cache_prefix', $this->_cache_group );

		if ( false === $time ) {
			$time = microtime();
			wp_cache_set( $this->_cache_prefix . '_cache_prefix', $time, $this->_cache_group );
		}

		return $this->_cache_prefix . '_' . $time;
	}

	/**
	 * Save data and delete guest session.
	 *
	 * @param int $old_session_key session ID before user logs in.
	 */
	public function save_data( $old_session_key = 0 ) {
		// Dirty if something changed - prevents saving nothing new.
		if ( $this->_to_save && $this->has_session() ) {
			global $wpdb;
			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO $this->_table (`session_key`, `session_value`, `session_expiry`) VALUES (%s, %s, %d)
 					ON DUPLICATE KEY UPDATE `session_value` = VALUES(`session_value`), `session_expiry` = VALUES(`session_expiry`)",
					$this->_customer_id,
					maybe_serialize( $this->_data ),
					$this->_session_expiration
				)
			);
			wp_cache_set( $this->get_cache_prefix() . $this->_customer_id, $this->_data, $this->_cache_group, $this->_session_expiration - time() );
			/*
			if ( is_ic_ajax() ) {
				set_transient( 'ic_clear_session_cache_' . $this->_customer_id, 1, MINUTE_IN_SECONDS );
			}
			*/
			$this->_to_save = false;
			if ( ! empty( $old_session_key ) && get_current_user_id() != $old_session_key && ! is_object( get_user_by( 'id', $old_session_key ) ) ) {
				$this->delete_session( $old_session_key );
			}
		}
	}

	public function save_data_filter( $value ) {
		$this->save_data();

		return $value;
	}

	/**
	 * Destroy all session data.
	 */
	public function destroy_session() {
		$this->delete_session( $this->_customer_id );
		$this->forget_session();
	}

	/**
	 * Forget all session data without destroying it.
	 */
	public function forget_session() {
		ic_setcookie( $this->_cookie, '', time() - YEAR_IN_SECONDS, $this->use_secure_cookie(), true );

		$this->_data        = array();
		$this->_to_save     = false;
		$this->_customer_id = $this->generate_customer_id();
	}

	/**
	 * Cleanup session data from the database and clear caches.
	 */
	public function cleanup_sessions() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM $this->_table WHERE session_expiry < %d", time() ) ); // @codingStandardsIgnoreLine.

		wp_cache_set( $this->_cache_prefix . '_cache_prefix', microtime(), $this->_cache_group );
	}

	/**
	 * Returns the session.
	 *
	 * @param string $customer_id Customer ID.
	 * @param mixed $default Default session value.
	 *
	 * @return string|array
	 */
	public function get_session( $customer_id, $default = false ) {
		global $wpdb;

		if ( defined( 'WP_SETUP_CONFIG' ) ) {
			return false;
		}

		$value = wp_cache_get( $this->get_cache_prefix() . $customer_id, $this->_cache_group );
		/*
				if ( $value !== false && ! is_ic_ajax() && get_transient( 'ic_clear_session_cache_' . $this->_customer_id ) ) {
					delete_transient( 'ic_clear_session_cache_' . $this->_customer_id );
					$value = false;
				}
		*/
		if ( false === $value ) {
			$value = $wpdb->get_var( $wpdb->prepare( "SELECT session_value FROM $this->_table WHERE session_key = %s", $customer_id ) ); // @codingStandardsIgnoreLine.

			if ( is_null( $value ) ) {
				$value = $default;
			}

			$cache_duration = $this->_session_expiration - time();
			if ( 0 < $cache_duration ) {
				wp_cache_add( $this->get_cache_prefix() . $customer_id, $value, $this->_cache_group, $cache_duration );
			}
		}

		return maybe_unserialize( $value );
	}

	/**
	 * Delete the session from the cache and database.
	 *
	 * @param int $customer_id Customer ID.
	 */
	public function delete_session( $customer_id ) {
		global $wpdb;

		wp_cache_delete( $this->get_cache_prefix() . $customer_id, $this->_cache_group );
		$wpdb->delete(
			$this->_table,
			array(
				'session_key' => $customer_id,
			)
		);
	}

	/**
	 * Update the session expiry timestamp.
	 *
	 * @param string $customer_id Customer ID.
	 * @param int $timestamp Timestamp to expire the cookie.
	 */
	public function update_session_timestamp( $customer_id, $timestamp ) {
		global $wpdb;

		$wpdb->update(
			$this->_table,
			array(
				'session_expiry' => $timestamp,
			),
			array(
				'session_key' => $customer_id,
			),
			array(
				'%d',
			)
		);
	}

	/**
	 * Magic get method.
	 *
	 * @param mixed $key Key to get.
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->get( $key );
	}

	/**
	 * Magic set method.
	 *
	 * @param mixed $key Key to set.
	 * @param mixed $value Value to set.
	 */
	public function __set( $key, $value ) {
		$this->set( $key, $value );
	}

	/**
	 * Magic isset method.
	 *
	 * @param mixed $key Key to check.
	 *
	 * @return bool
	 */
	public function __isset( $key ) {
		return isset( $this->_data[ sanitize_title( $key ) ] );
	}

	/**
	 * Magic unset method.
	 *
	 * @param mixed $key Key to unset.
	 */
	public function __unset( $key ) {
		if ( isset( $this->_data[ $key ] ) ) {
			unset( $this->_data[ $key ] );
			$this->_to_save = true;
		}
	}

	/**
	 * Get a session variable.
	 *
	 * @param string $key Key to get.
	 * @param mixed $default used if the session variable isn't set.
	 *
	 * @return array|string value of session variable
	 */
	public function get( $key = '', $default = null ) {
		if ( empty( $key ) ) {

			if ( ! empty( $this->all_data ) ) {
				return $this->all_data;
			}
			$all_data = $this->get_session_data();

			if ( ! empty( $this->_data ) && $this->_to_save ) {
				$all_data = array_merge( $all_data, $this->_data );
			}

			$return_data = array();
			foreach ( $all_data as $key => $value ) {
				$return_data[ $key ] = $this->get( $key );
			}
			$this->all_data = $return_data;

			return $this->all_data;
		}
		$key = sanitize_key( $key );

		return isset( $this->_data[ $key ] ) ? maybe_unserialize( $this->_data[ $key ] ) : $default;
	}

	/**
	 * Set a session variable.
	 *
	 * @param string $key Key to set.
	 * @param mixed $value Value to set.
	 */
	public function set( $key, $value ) {
		if ( $value !== $this->get( $key ) ) {
			$sanitized_key                 = sanitize_key( $key );
			$this->_data[ $sanitized_key ] = maybe_serialize( $value );
			if ( ! empty( $this->all_data ) ) {
				$this->all_data[ $sanitized_key ] = $value;
			}
			$this->_to_save = true;
		}
	}

	/**
	 * Replace multiple session variables
	 *
	 * @param array $new_data data to be replaced.
	 */
	public function replace( $new_data, $clear_old = true ) {
		if ( $clear_old ) {
			//$this->_data    = array();
			//$this->all_data = array();
			foreach ( $this->all_data as $key => $value ) {
				if ( ! isset( $new_data[ $key ] ) ) {
					unset( $this->all_data[ $key ] );
					$this->_to_save = true;
				}
			}
			foreach ( $this->_data as $key => $value ) {
				if ( ! isset( $new_data[ $key ] ) ) {
					unset( $this->_data[ $key ] );
					$this->_to_save = true;
				}
			}
		}
		foreach ( $new_data as $key => $data ) {
			$this->set( $key, $data );
		}
	}

	/**
	 * Get customer ID.
	 *
	 * @return int
	 */
	public function get_customer_id() {
		return $this->_customer_id;
	}

	private function table_exists() {
		$return = ic_get_global( 'session_table_exists' );
		if ( $return === 1 ) {
			return true;
		} else if ( $return === 2 ) {
			return false;
		}
		global $wpdb;
		$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $this->_table ) );
		if ( $wpdb->get_var( $query ) == $this->_table ) {
			ic_save_global( 'session_table_exists', 1 );

			return true;
		}

		ic_save_global( 'session_table_exists', 2 );

		return false;

	}

	private function create_table() {
		if ( $this->table_exists() ) {
			return true;
		}
		global $wpdb;

		$collate = '';

		if ( $wpdb->has_cap( 'collation' ) ) {
			$collate = $wpdb->get_charset_collate();
		}
		$create = "CREATE TABLE $this->_table (
  session_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  session_key char(32) NOT NULL,
  session_value longtext NOT NULL,
  session_expiry BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY  (session_id),
  UNIQUE KEY session_key (session_key)
) $collate;";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $create );
		if ( $this->table_exists() ) {
			return true;
		} else {
			return false;
		}
	}
}


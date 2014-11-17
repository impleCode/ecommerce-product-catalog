<?php 
/**
 * Manages product functions folder
 *
 * Here all plugin functions folder is defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/includes/settings
 * @author 		Norbert Dreszer
 */
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//if (! function_exists('implecode_settings_radio')) {
require_once('settings-functions.php');// }

require_once('general.php' );
require_once('attributes.php' );
require_once('shipping.php' );
require_once('custom-design.php' );
require_once('custom-names.php' );
require_once('csv.php' );
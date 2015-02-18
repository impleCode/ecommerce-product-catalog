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
require_once(AL_BASE_PATH. '/includes/settings/settings-functions.php');// }

require_once(AL_BASE_PATH. '/includes/settings/general.php' );
require_once(AL_BASE_PATH. '/includes/settings/attributes.php' );
require_once(AL_BASE_PATH. '/includes/settings/shipping.php' );
require_once(AL_BASE_PATH. '/includes/settings/custom-design.php' );
require_once(AL_BASE_PATH. '/includes/settings/custom-names.php' );
require_once(AL_BASE_PATH. '/includes/settings/csv.php' );
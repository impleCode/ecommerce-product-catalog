<?php
/**
 * Manages wordpress core fields
 *
 * Here all wordpress fields are redefined.
 *
 * @version        1.0.0
 * @package        ecommerce-product-catalog/functions
 * @author        Norbert Dreszer
 */
if (!defined('ABSPATH')) exit; // Exit if accessed directly

function al_enter_title_here($message)
{
    $screen = get_current_screen();
    if (ic_string_contains($screen->id, 'al_product')) {
        $names = get_catalog_names();
        $message = sprintf(__('Enter %s name here', 'al-ecommerce-product-catalog'), strtolower($names['singular']));
        return $message;
    }
}
add_filter('enter_title_here', 'al_enter_title_here');

?>
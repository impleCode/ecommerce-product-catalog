<?php 
/**
 * Manages plugin currencies
 *
 * Here plugin currencies are defined and managed.
 *
 * @version		1.0.0
 * @package		ecommerce-product-catalog/functions
 * @author 		Norbert Dreszer
 */
 
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function available_currencies() {
$currencies = array(
'USD',
'EUR',
'AUD',
'CAD',
'GBP',
'JPY',
'NZD',
'CHF',
'HKD',
'SGD',
'SEK',
'DKK',
'PLN',
'NOK',
'HUF',
'CZK',
'ILS',
'MXN',
'BRL',
'MYR',
'PHP',
'TWD',
'THB',
'TRY',
'RUB'
);
return $currencies;
}
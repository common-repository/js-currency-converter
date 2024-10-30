<?php
/**
 * @package JS Currency Converter
 * @version 1.2
 */
/*
 * Plugin Name:	JS Currency Converter
 * Description: Adding Valuta Conversion to a WordPress site using JavScript
 * Author: 		Dragonet
 * Version: 	1.2
 * Author URI: 	http://www.dragonet.nl/
 * Text Domain: js_currency_converter
*/

require_once(__DIR__.'/js_currency_converter-front-functions.php');

if (is_admin()) {
    require_once(__DIR__.'/js_currency_converter-admin-functions.php');
    $jcc_admin = new JsCurrencyConverterAdmin();
}

$jcc_front = new JsCurrencyConverter();
$jcc_front->setActions();

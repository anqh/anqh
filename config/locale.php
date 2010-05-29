<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Locale config
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
return array(

	/**
	 * Available languages
	 */
	'languages' => array(
		'en' => array('en_US', 'English_United States', 'English'),
		'fi' => array('fi_FI', 'Finnish_Finnish', 'Suomi'),
	),

	/**
	 * Default language locale name(s).
	 * First item must be a valid i18n directory name, subsequent items are alternative locales
	 * for OS's that don't support the first (e.g. Windows). The first valid locale in the array will be used.
	 * @see http://php.net/setlocale
	 */
	'default_language' => 'en',
	'language' => array('en_US', 'English_United States', 'English'),

	/**
	 * Available countries
	 *
	 * shortcode => locale, name, currency
	 */
	'countries' => array(
		'fi' => array('fi_FI', 'Finland', 'EUR'),
	),

	/**
	 * Default country locale.
	 */
	'default_country' => 'fi',
	'country' => array('fi_FI', 'Finland', 'EUR'),

	/**
	 * Available currencies
	 *
	 * code => symbol, short, long
	 */
	'currencies' => array(
		'EUR' => array('&euro;', 'Eur', 'Euro'),
	),

	/**
	 * Default country locale.
	 */
	'currency' => array('&euro;', 'Eur', 'Euro'),

	/**
	 * Locale timezone. Defaults to use the server timezone.
	 * @see http://php.net/timezones
	 */
	'timezone' => ini_get('date.timezone'),

	/**
	 * First day of the week
	 */
	'start_monday' => true,
	
);

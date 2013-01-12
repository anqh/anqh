<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Ads config
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
return array(

	// Network code
	'network_code' => false,

	/**
	 * Ad slots.
	 *
	 * SLOT => array(
	 *   'ad unit' => array(width, height)
	 * )
	 */
	'slots' => array(
		Ads::MAINMENU => array(),
		Ads::SIDE     => array(),
	),

);

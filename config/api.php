<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * API config
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
return array(

	// How many requests allowed per time span, 0 to disable
	'rate_limit' => 1000,

	// Time span for limit in seconds
	'rate_span'  => 60 * 60,

	// API target URL
	'url'        => '/api',

);

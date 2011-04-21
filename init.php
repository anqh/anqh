<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Init for Venues
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

Route::set('api_venues', 'api/<version>/venues/<action>(.<format>)', array('version' => 'v[0-9\.]+', 'action' => 'foursquare', 'format' => 'xml|json'))
	->defaults(array(
		'controller' => 'venues_api',
		'version'    => 'v1',
	));
Route::set('venue_add', 'venues/(<id>/)add')
	->defaults(array(
		'controller' => 'venues',
		'action'     => 'add',
	));
Route::set('venue', 'venue/<id>(/<action>(/<param>))', array('action' => 'venue|edit|delete|image|combine|foursquare'))
	->defaults(array(
		'controller' => 'venues',
		'action'     => 'venue',
	));
Route::set('venues', 'venues')
	->defaults(array(
		'controller' => 'venues',
	));

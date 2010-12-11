<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Init for Events
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

Route::set('api_events', 'api/<version>/events/<action>(.<format>)', array('version' => 'v[0-9\.]+', 'action' => 'search|event', 'format' => 'xml|json'))
	->defaults(array(
		'controller' => 'events_api',
		'version'    => 'v1',
	));
Route::set('event', 'event/<id>(/<action>(/<param>))', array('action' => 'edit|delete|favorite|unfavorite|hover|image'))
	->defaults(array(
		'controller' => 'events',
		'action'     => 'event',
	));
Route::set('events_ymd', 'events/<year>/week/<week>', array('year' => '\d{4}', 'week' => '\d{2}'))
	->defaults(array(
		'controller' => 'events',
		'action'     => 'browse',
	));
Route::set('events_ymd', 'events/<year>(/<month>(/<day>))', array('year' => '\d{4}', 'month' => '\d{1,2}', 'day' => '\d{1,2}'))
	->defaults(array(
		'controller' => 'events',
		'action'     => 'browse',
	));
Route::set('events', 'events(/<action>)', array('action' => 'add|upcoming|past|browser|flyers'))
	->defaults(array(
		'controller' => 'events',
	));

<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Init for Venues
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

Route::set('venue_category_add', 'venues/add')
	->defaults(array(
		'controller' => 'venues',
		'action'     => 'addcategory',
	));
Route::set('venue_add', 'venues/<id>/add')
	->defaults(array(
		'controller' => 'venues',
		'action'     => 'add',
	));
Route::set('venue_category', 'venues/<id>(/<action>)', array('action' => 'edit|delete'))
	->defaults(array(
		'controller' => 'venues',
		'action'     => 'category',
	));
Route::set('venue', 'venue/<id>(/<action>)', array('action' => 'venue|edit|delete'))
	->defaults(array(
		'controller' => 'venues',
		'action'     => 'venue',
	));
Route::set('venues', 'venues')
	->defaults(array(
		'controller' => 'venues',
	));

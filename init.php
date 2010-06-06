<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Init for Forum
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

Route::set('forum_group_add', 'forum/areas/add')
	->defaults(array(
		'controller' => 'forum_group',
		'action'     => 'edit',
	));
Route::set('forum_area_add', 'forum/areas/<group_id>/<action>', array('action' => 'add'))
	->defaults(array(
		'controller' => 'forum_area',
		'action'     => 'edit',
	));
Route::set('forum_group', 'forum/areas(/<id>(/<action>))', array('action' => 'index|edit'))
	->defaults(array(
		'controller' => 'forum_group',
		'action'     => 'index',
	));
Route::set('forum_area', 'forum/<id>(/<action>)', array('action' => 'index|edit'))
	->defaults(array(
		'controller' => 'forum_area',
		'action'     => 'index',
	));
Route::set('forum_topic', 'topic/<id>(/<action>)', array('action' => 'index|add|edit'))
	->defaults(array(
		'controller' => 'forum_topic',
		'action'     => 'index',
	));
Route::set('forum', 'forum(/<params>)', array('params' => '.*'))
	->defaults(array(
		'controller' => 'forum',
		'action'     => 'index'
	));

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
Route::set('forum_topic_add', 'forum/<id>/<action>', array('action' => 'post'))
	->defaults(array(
		'controller' => 'forum_topic',
	));
Route::set('forum_group', 'forum/areas(/<id>(/<action>))', array('action' => 'edit|delete'))
	->defaults(array(
		'controller' => 'forum_group',
	));
Route::set('forum_area', 'forum/<id>(/<action>)', array('action' => 'edit|delete'))
	->defaults(array(
		'controller' => 'forum_area',
	));
Route::set('forum_event', 'topic/event/<id>(/<time>)', array('time' => 'before|after'))
	->defaults(array(
		'controller' => 'forum_topic',
		'action'     => 'event'
	));
Route::set('forum_topic', 'topic/<id>(/<action>)', array('action' => 'add|edit|reply|delete'))
	->defaults(array(
		'controller' => 'forum_topic',
	));
Route::set('forum_post', 'topic/<topic_id>/<id>(/<action>)', array('action' => 'edit|quote|delete'))
	->defaults(array(
		'controller' => 'forum_topic'
	));
Route::set('forum', 'forum(/)')
	->defaults(array(
		'controller' => 'forum',
	));

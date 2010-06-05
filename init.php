<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Init for Forum
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

Route::set('forum_group', 'forums/<id>(/<params>)', array('params' => '.*'))
	->defaults(array(
		'controller' => 'forum',
		'action'     => 'group',
	));
Route::set('forum_area', 'forum/<id>(/<params>)', array('params' => '.*'))
	->defaults(array(
		'controller' => 'forum',
		'action'     => 'area',
	));
Route::set('forum_topic', 'topic/<id>(/<params>)', array('params' => '.*'))
	->defaults(array(
		'controller' => 'forum',
		'action'     => 'topic',
	));
Route::set('forum', 'forum(/<params>)', array('params' => '.*'))
	->defaults(array(
		'controller' => 'forum',
		'action'     => 'index'
	));

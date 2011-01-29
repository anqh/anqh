<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Init for Anqh
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

Route::set('404', '<file>.<ext>', array('ext' => 'ico|png|jpg|gif|txt|avi|flv|sql|js|css'))
	->defaults(array(
		'controller' => 'static',
		'action'     => '404'
	));
Route::set('api_user', 'api/<version>/user/<action>(.<format>)', array('version' => 'v[0-9\.]+', 'action' => 'search', 'format' => 'xml|json'))
	->defaults(array(
		'controller' => 'user_api',
		'version'    => 'v1',
	));
Route::set('user', 'member(/<username>(/<action>(/<param>)))', array(
	'action'   => 'hover|settings|friends?|unfriend|favorites|image|ignores?|unignore',
	'username' => '[^/]+')
)->defaults(array(
		'controller' => 'user',
	));
Route::set('user_comment', 'member/comment/<id>/<commentaction>', array('commentaction' => 'delete|private'))
	->defaults(array(
		'controller' => 'user',
		'action'     => 'comment',
	));
/*
Route::set('users', 'members(/<action>)')
	->defaults(array(
		'controller' => 'users',
	));
*/
Route::set('sign', 'sign/<action>', array('action' => 'up|in|out'))
	->defaults(array(
		'controller' => 'sign',
		'action'     => 'up'
	));
Route::set('setting', 'set/<action>/<value>')
	->defaults(array(
		'controller' => 'set'
	));
Route::set('shouts', 'shouts(/<action>)', array('action' => 'index|shout'))
	->defaults(array(
		'controller' => 'shouts',
	));
Route::set('roles', 'roles')
	->defaults(array(
		'controller' => 'roles',
	));
Route::set('role', 'role(/<id>(/<action>))', array('action' => 'delete|edit'))
	->defaults(array(
		'controller' => 'roles',
		'action'     => 'edit',
	));
Route::set('tag_group_add', 'tags/addgroup')
	->defaults(array(
		'controller' => 'tags',
		'action'     => 'addgroup',
	));
Route::set('tag_group', 'tags/<id>(/<action>)', array('action' => 'group|add|deletegroup|editgroup'))
	->defaults(array(
		'controller' => 'tags',
		'action'     => 'group',
	));
Route::set('tag', 'tag/<id>(/<action>)', array('action' => 'tag|edit|delete'))
	->defaults(array(
		'controller' => 'tags',
		'action'     => 'tag',
	));
Route::set('tags', 'tags')
	->defaults(array(
		'controller' => 'tags',
		'action'     => 'index',
	));
/*
Route::set('index', '')
	->defaults(array(
		'controller' => 'index',
		'action'     => 'index',
	));
Route::set('catch_all', '(<path>)', array('path' => '.+'))
	->defaults(array(
		'controller' => 'error',
		'action' => '404'
	));
*/

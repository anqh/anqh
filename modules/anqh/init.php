<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Init for Anqh.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

// Our own exception handler
if (Kohana::$errors === true) {
	set_exception_handler(array('Anqh_Exception', 'handler'));
}

Model_User::default_setting(array(
	'ui.newsfeed' => View_Newsfeed::TYPE_ALL,
	'ui.theme'    => 'mixed'
));

Route::set('error', 'error/<action>(/<message>)', array('action' => '[0-9]++', 'message' => '.+'))
	->defaults(array(
		'controller' => 'error'
	));
Route::set('404', '<file>.<ext>', array('ext' => 'ico|png|jpg|gif|txt|avi|flv|sql|js|css'))
	->defaults(array(
		'controller' => 'static',
		'action'     => '404'
	));
Route::set('api_user', 'api/<version>/<api>/<action>(.<format>)', array(
		'version' => 'v[0-9\.]+',
		'api'     => 'users?',
		'action'  => 'search',
		'format'  => 'xml|json'))
	->defaults(array(
		'controller' => 'users_api',
		'version'    => 'v1',
	));
Route::set('ical_favorites', 'member/<username>/favorites.ics', array('username' => '[^/]+'))
	->defaults(array(
		'controller' => 'user',
		'action'     => 'favorites_ical',
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
Route::set('users', 'members(/<action>)')
	->defaults(array(
		'controller' => 'users',
	));
Route::set('sign', 'sign/<action>', array('action' => 'up|in|out'))
	->defaults(array(
		'controller' => 'sign',
		'action'     => 'up'
	));
Route::set('password', 'password')
	->defaults(array(
		'controller' => 'sign',
		'action'     => 'password'
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
Route::set('tag_group', 'tags/<id>(/<action>)', array('action' => 'add|group|deletegroup'))
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
Route::set('notifications', 'notifications')
	->defaults(array(
		'controller' => 'notifications',
		'action'     => 'index',
	));
Route::set('developers', 'developers')
	->defaults(array(
		'controller' => 'developers',
		'action'     => 'index',
	));
Route::set('contact', 'contact')
	->defaults(array(
		'controller' => 'contact',
		'action'     => 'index',
	));

Route::set('oauth', 'oauth(/<provider>)/<action>', array('provider' => 'facebook|foursquare'))
	->defaults(array(
		'controller' => 'oauth',
		'action'     => 'redirect',
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


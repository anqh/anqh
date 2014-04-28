<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Init for Music charts.
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

Route::set('music_add', '<music>/add', array('music' => 'mixtape|track'))
	->defaults(array(
		'controller' => 'music',
		'action'     => 'add',
	));
Route::set('music_browse', '<music>(/<genre>)', array('music' => 'mixtapes|tracks'))
	->defaults(array(
		'controller' => 'music',
		'action'     => 'browse',
	));
Route::set('music_track', 'music/<id>(/<action>)', array('action' => 'edit|delete|listen'))
	->defaults(array(
		'controller' => 'music',
		'action'     => 'track',
	));
Route::set('charts', 'charts(/<action>)', array('action' => 'add'))
	->defaults(array(
		'controller' => 'music',
	));
Route::set('profile_music', 'member/<username>/music', array(
	'username' => '[^/]+')
)->defaults(array(
		'action'     => 'profile',
		'controller' => 'music',
	));

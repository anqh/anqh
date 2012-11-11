<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Init for Music charts.
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

Route::set('track_comment', 'track/comment/<id>/<commentaction>', array('commentaction' => 'delete|private'))
	->defaults(array(
		'controller' => 'music',
		'action'     => 'comment',
	));
Route::set('music_add', '<music>/add', array('music' => 'mixtape|track'))
	->defaults(array(
		'controller' => 'music',
		'action'     => 'add',
	));
Route::set('music_track', 'track/<id>(/<action>)', array('action' => 'edit|delete|listen'))
	->defaults(array(
		'controller' => 'music',
		'action'     => 'track',
	));
Route::set('charts', 'charts(/<action>)', array('action' => 'add'))
	->defaults(array(
		'controller' => 'music',
	));


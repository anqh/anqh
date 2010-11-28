<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Init for Galleries
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

Route::set('gallery_image_comment', 'gallery/comment/<id>/<commentaction>', array('commentaction' => 'delete|private'))
	->defaults(array(
		'controller' => 'galleries',
		'action'     => 'comment',
	));
Route::set('gallery_event', 'event/<id>/gallery')
	->defaults(array(
		'controller' => 'galleries',
		'action'     => 'event'
	));
Route::set('flyer_image', 'flyer/<id>')
	->defaults(array(
		'controller' => 'galleries',
		'action'     => 'flyer'
	));
Route::set('gallery_image', 'gallery/<gallery_id>/<id>(/<action>)', array('action' => 'approve|delete|default|hover', 'id' => '\d+'))
	->defaults(array(
		'controller' => 'galleries',
		'action'     => 'image',
	));
Route::set('gallery', 'gallery/<id>(/<action>)', array('action' => 'update|upload|pending'))
	->defaults(array(
		'controller' => 'galleries',
		'action'     => 'gallery',
	));
Route::set('galleries', 'galleries(/<action>(/<year>(/<month>)))', array('action' => 'browse|upload|approval', 'year' => '\d{4}', 'month' => '\d{1,2}'))
	->defaults(array(
		'controller' => 'galleries',
	));

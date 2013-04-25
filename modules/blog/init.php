<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Init for Blog
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

Route::set('blog_comment', 'blog/comment/<id>/<commentaction>', array('commentaction' => 'delete|private'))
	->defaults(array(
		'controller' => 'blog',
		'action'     => 'comment',
	));
Route::set('blog_entry', 'blog/<id>(/<action>)', array('action' => 'edit|delete'))
	->defaults(array(
		'controller' => 'blog',
		'action'     => 'entry',
	));
Route::set('blog_user', 'member/<username>/blog(/<year>(/<month>))', array(
	'year'     => '\d{4}',
	'month'    => '\d{1,2}',
	'username' => '[^/]+')
)->defaults(array(
		'action'     => 'user',
		'controller' => 'blog',
	));
Route::set('blogs', 'blogs(/<action>)', array('action' => 'add'))
	->defaults(array(
		'controller' => 'blog',
	));

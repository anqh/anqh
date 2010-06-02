<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Init for Forum
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

Route::set('forum', 'forum(/<action>(/<id>(/<params>)))', array('action' => 'index|group|areas|area|topic', 'params' => '.*'))
	->defaults(array(
		'controller' => 'forum',
		'action'     => 'index'
	));

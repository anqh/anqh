<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Comment view for Guests
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

$comments = isset($comments) ? $comments : 0;
if ($comments > 1):
	echo __('There are :comments comments. Please login to read them and write your own.', array(':comments' => $comments));
elseif ($comments > 0):
	echo __('There is :comments comment. Please login to read it and write your own.', array(':comments' => $comments));
else:
	echo __('Please login to read and write comments.');
endif;

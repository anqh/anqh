<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Hover card
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

if ($user['title']) echo HTML::chars(trim($user['title'])) . '<br />';
if ($user['thumb']) echo HTML::image($user['thumb'], array('width' => 160)) . '<br />';
echo __('Last login: :login', array(':login' => HTML::time(Date::fuzzy_span($user['last_login']), $user['last_login']))) . '<br />';

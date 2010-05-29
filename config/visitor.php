<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Visitor config
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
return array(
	'hash_method'  => 'sha1',
	'salt_pattern' => array(1, 3, 5, 9, 14, 15, 20, 21, 28, 30),
	'lifetime'     => 1209600,
	'session_key'  => 'user',
	'cookie_name'  => 'autologin',

	'username'     => array(
		'chars'      => 'a-zA-Z0-9_\-\^\. ',
		'length_min' => 3,
		'length_max' => 20,
	),
	
);

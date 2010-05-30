<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Security helper
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Security extends Kohana_Security {

	/**
	 * @var  integer  Token time to live in seconds, 30 minutes
	 */
	public static $csrf_ttl = 1800;


	/**
	 * Get CSRF token
	 *
	 * @param   string   $id      Custom token id, e.g. uid
	 * @param   string   $action  Optional action
	 * @param   integer  $time    Used only internally
	 * @return  string
	 */
	public static function csrf($id = '', $action = '', $time = 0) {

		// Get id string for token, could be uid or ip etc
		if (!$id) $id = Request::$client_ip;

		// Get time to live
		if (!$time) $time = ceil(time() / self::$csrf_ttl);

		return md5($time . self::token() . $id . $action);
	}


	/**
	 * Validate CSRF token
	 *
	 * @param   string   $token
	 * @param   string   $id      Custom token id, e.g. uid
	 * @param   string   $action  Optional action
	 * @return  boolean
	 */
	public static function csrf_valid($token = false, $id = '', $action = '') {
		if (!$token) $token = Arr::get($_REQUEST, 'token');

		// Get time to live
		$time = ceil(time() / self::$csrf_ttl);

		// Check token validity
		return ($token === self::csrf($id, $action, $time) || $token === self::csrf($id, $action, $time - 1));

	}

}

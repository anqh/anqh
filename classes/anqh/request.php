<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Request
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Request extends Kohana_Request {

	/**
	 * Redirect back to history
	 *
	 * @static
	 * @param   string   $default  if no history found
	 * @param   boolean  $return   return url, don't redirect
	 * @return  string
	 */
	public static function back($default = '/', $return = false) {
		$url = Session::instance()->get('history', $default);

		if ($return) {
			return $url;
		}

		Request::current()->redirect($url);
	}


	/**
	 * Return current unrouted URI, otherwise default action would be added
	 *
	 * @static
	 * @return  string
	 */
	public static function current_uri() {
		return $_REQUEST['URI'];
	}


	/**
	 * Get client host name
	 *
	 * @static
	 * @return  string
	 */
	public static function host_name() {
		static $host_name;

		if (!is_string($host_name)) {
			try {
				$host_name = (self::$client_ip == '0.0.0.0') ? self::$client_ip : gethostbyaddr(self::$client_ip);
			} catch (ErrorException $e) {
				$host_name = self::$client_ip;
			}
		}

		return $host_name;
	}

}

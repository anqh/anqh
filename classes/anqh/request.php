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
	 * Get client host name
	 *
	 * @static
	 * @return  string
	 */
	public static function host_name() {
		static $host_name;

		if (!is_string($host_name)) {
			$host_name = (self::$client_ip == '0.0.0.0') ? self::$client_ip : gethostbyaddr(self::$client_ip);
		}

		return $host_name;
	}

}

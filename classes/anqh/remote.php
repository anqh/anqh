<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Remote
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Remote extends Kohana_Remote {

	// Default cURL options
	public static $default_options = array(
		CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; Kohana v3.0 +http://kohanaphp.com/)',
		CURLOPT_CONNECTTIMEOUT => 5,
		CURLOPT_TIMEOUT        => 5,
		CURLOPT_FOLLOWLOCATION => true,
	);


	/**
	 * Do a GET request
	 *
	 * @static
	 * @param   string  $url
	 * @param   array   $params
	 * @param   array   $options
	 * @return  string
	 *
	 * @throws  Kohana_Exception
	 */
	public static function get($url, array $params = null, array $options = null) {
		if (!empty($params)) {
			$url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params, '', '&');
		}

		return parent::get($url, $options);
	}


	/**
	 * Do a POST request
	 *
	 * @static
	 * @param   string  $url
	 * @param   array   $params
	 * @param   array   $options
	 * @return  string
	 *
	 * @throws  Kohana_Exception
	 */
	public static function post($url, array $params = null, array $options = null) {
		$options = array(CURLOPT_POST => true) + (array)$options;

		if (!empty($params)) {
			$options[CURLOPT_POSTFIELDS] = http_build_query($params);
		}

		return parent::get($url, $options);
	}

}

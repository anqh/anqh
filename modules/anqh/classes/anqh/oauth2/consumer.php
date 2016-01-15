<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * OAuth2_Consumer
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_OAuth2_Consumer extends Kohana_OAuth2_Consumer {

	/**
	 * Make an API call.
	 *
	 * @param   string  $call
	 * @param   array   $params
	 * @return  array
	 */
	public function api_call($call, array $params = null) {
		if (!$params) {
			$params = array('access_token' => $this->_token['access_token']);
		} else {
			$params['access_token'] = $this->_token['access_token'];
		}

		$api_call = $this->base_uri() . $call . URL::query($params, false);

		if (substr_count($api_call, '?') > 1) {
			$api_call = str_replace('?access_token', '&access_token', $api_call);
		}

		$request = Request::factory($api_call)->method(Request::GET);

		Kohana::$log->add(Log::DEBUG, 'OAuth2: Making an API call: :call', array(':call' => $api_call));

		$response = $this->execute($request);

		return (array)json_decode($response->body());
	}


	/**
	 * Get provider.
	 *
	 * @return  string
	 */
	public function get_provider() {
		return $this->_provider;
	}

}

<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * OAuth2_Consumer_GrantType_Authorization_Code
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_OAuth2_Consumer_GrantType_Authorization_Code extends Kohana_OAuth2_Consumer_GrantType_Authorization_Code {

	public function request_token($grant_type_options = array()) {
		$request = Request::factory($this->_config[$this->_provider]['token_uri'])
				->method(Request::POST)
				->post(array(
					'grant_type'    => 'authorization_code',
					'code'          => $grant_type_options['code'],
					'redirect_uri'  => URL::site($this->_config[$this->_provider]['redirect_uri'], TRUE),
					'client_id'     => $this->_config[$this->_provider]['client_id'],
					'client_secret' => $this->_config[$this->_provider]['client_secret'],
				));

		/** @var  HTTP_Response  $response */
		$response = $request->execute();

		if ($response->status() != 200) {
			throw new OAuth2_Exception_InvalidGrant('Error! .. '.$response->body());
		}

		$content_type = stripslashes(Arr::get(explode(';', $response->headers('content-type')), 0));
		switch ($content_type) {

			case 'application/json':
				$x = (array) json_decode($response->body());
				break;

			case 'text/plain':
			case 'application/x-www-form-urlencoded': # Stupid github -_- and Facebook
				parse_str($response->body(), $x);
				break;

			default:
				throw new OAuth2_Exception_InvalidGrant('Unknown Content-Type: :content_type', array(
					':content_type' => $response->headers('content-type'),
				));

		}

		return $x;
	}


	public function get_redirect_uri($state = NULL, $response_type = OAuth2::RESPONSE_TYPE_CODE) {
		return parent::get_redirect_uri($state, $response_type) . '&scope=' . $this->_config[$this->_provider]['scope'];
	}

}

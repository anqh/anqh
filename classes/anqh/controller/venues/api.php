<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Venues API controller
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Venues_API extends Controller_API {

	/**
	 * Action: foursquare proxy
	 */
	public function action_foursquare() {
		$foursquare = Arr::get_once($_REQUEST, 'method');
		$url        = 'http://api.foursquare.com/v1';
		$method     = 'GET';
		$required = $optional = array();

		switch ($foursquare) {

			// Venue info
			case 'venue':
				$url .= '/venue.json';
				$required = array('vid');
				break;

			// Venue search
			case 'venues':
				$url   .= '/venues.json';
				$required = array('geolat', 'geolong');
				$optional = array('q', 'l');
		    break;

			default:
		    return;

		}

		$params = array_filter(Arr::intersect($_REQUEST, $required));
		if (!empty($params)) {
			$params += array_filter(Arr::intersect($_REQUEST, $optional));
			try {
				if ($method == 'GET') {

					// Send GET request
					if (!empty($params)) {
						$url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params, '', '&');
					}
					$options = array();

				} else {

					// Send POST request
					$options = array(
						CURLOPT_POST           => true,
						CURLOPT_FOLLOWLOCATION => true,
					);
					if (!empty($params)) {
						$options[CURLOPT_POSTFIELDS] = http_build_query($params);
					}

				}
				$request = Request::factory($url);
				$request->get_client()->options($options);
				$response = $request->execute();
				if ($response->status() == 200) {
					$this->data[$foursquare] = json_decode($response->body());
				}
			} catch (Kohana_Exception $e) {
			}
		}
	}


}

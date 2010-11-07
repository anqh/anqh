<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Venues API controller
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
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

		$params = array_filter(Arr::extract($_REQUEST, $required));
		if (!empty($params)) {
			$params += array_filter(Arr::extract($_REQUEST, $optional));
			try {
				$data = ($method == 'GET') ? Remote::get($url, $params) : Remote::post($url, $params);
				$this->data[$foursquare] = json_decode($data);
			} catch (Kohana_Exception $e) {
			}
		}
	}


}

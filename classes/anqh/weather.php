<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Weather
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Weather {

	/**
	 * Get current weather conditions
	 *
	 * @static
	 * @param   string  $location
	 * @return  array
	 */
	public static function get_weather($location = 'Helsinki') {
		$cache = Cache::instance();

		// Default to English and SI units
		$language = 'en-gb';

		// Failed weather calls are blocked
		$block_key = 'weather_blocked_' . date('Ymd');
		$blocked = $cache->get_($block_key, array());
		if (isset($blocked[mb_strtolower($location)])) {
			return array();
		}

		$cache_key = 'weather_' . Text::clean($location);
		$weather = $cache->get_($cache_key);
		if (!is_array($weather)) {

			// Load weather data from the (unsupported, unofficial) Google Weather API
			$xml = new SimpleXMLElement('http://www.google.com/ig/api?weather=' . urlencode(mb_strtolower($location)) . '&hl=' . $language, null, true);

			// Check for errors
			if (!$xml || $xml->xpath('/xml_api_reply/problem_cause')) {
				$weather = array();

				// Block location for today
				$blocked[mb_strtolower($location)] = true;
				$cache->set_($block_key, $blocked, Date::DAY);

			} else {

				$information   = $xml->xpath('/xml_api_reply/weather/forecast_information');
				$current       = $xml->xpath('/xml_api_reply/weather/current_conditions');
				$forecast_list = $xml->xpath('/xml_api_reply/weather/forecast_conditions');

				$forecast = array();
				foreach ($forecast_list as $forecast_day) {
					$forecast[(string)$forecast_day->day_of_week['data']] = array(
						'low'       => (int)$forecast_day->low['data'],
						'high'      => (int)$forecast_day->high['data'],
						'condition' => (string)$forecast_day->condition['data'],
					);
				}

				$weather = array(
					'city'        => (string)$information[0]->city['data'],
					'postal_code' => (string)$information[0]->postal_code['data'],
					'time'        => (string)strtotime($information[0]->current_date_time['data']),

					'condition'   => (string)$current[0]->condition['data'],
					'temperature' => (int)$current[0]->temp_c['data'],
					'humidity'    => (string)$current[0]->humidity['data'],
					'wind'        => (string)$current[0]->wind_condition['data'],

					'forecast'    => $forecast,
				);
			}

			// Cache for 30 minutes
			$cache->set_($cache_key, $weather, 60 * 15);
		}

		return $weather;
	}

}

<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Geo library to handle GeoNames etc geographical
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Geo {

	/**
	 * @var  array  City cache
	 */
	protected static $_cities = array();

	/**
	 * @var  array  Country cache
	 */
	protected static $_countries = array();


	/**
	 * Get country info from GeoNames by country code
	 *
	 * @static
	 * @param   string  $id
	 * @return  array
	 */
	private static function _country_info($id, $lang = 'en') {
		$url = Kohana::config('geo.base_url') . '/countryInfo?country=' . $id . '&lang=' . $lang;
		try {
			$xml = new SimpleXMLElement($url, null, true);
			Kohana::$log->add(Log::DEBUG, 'GeoNames OK: ' . $url);
			return $xml;
		} catch (Exception $e) {
			Kohana::$log->add(Log::ERROR, 'GeoNames failed: ' . $url . ' - ' . Kohana_Exception::text($e));
			return false;
		}
	}


	/**
	 * Get from GeoNames by geonameId
	 *
	 * @static
	 * @param   integer  $id
	 * @return  array
	 */
	private static function _get($id, $lang = 'en') {
		$url = Kohana::config('geo.base_url') . '/get?geonameId=' . (int)$id . '&lang=' . $lang . '&style=full';
		try {
			$xml = new SimpleXMLElement($url, null, true);
			Kohana::$log->add(Log::DEBUG, 'GeoNames OK: ' . $url);
			return $xml;
		} catch (Exception $e) {
			Kohana::$log->add(Log::ERROR, 'GeoNames failed: ' . $url . ' - ' . Kohana_Exception::text($e));
			return false;
		}
	}


	/**
	 * Get city by geonameId
	 *
	 * @static
	 * @param   integer  $id
	 * @return  Model_Geo_City
	 */
	public static function find_city($id, $lang = 'en') {
		$id = (int)$id;
		if (!$id) {
			return false;
		}

		// Try local cache first
		if (!isset(self::$_cities[$id])) {

			// Not found from cache, load from db if preferred
			$city = Model_Geo_City::factory($id);
			if (!$city->loaded()) {

				// Still not loaded, load from GeoNames
				$city = Model_Geo_City::factory();
				if ($page = self::_get($id, $lang)) {
					if ($country = self::find_country((string)$page->countryCode, $lang)) {
						$city->id              = (int)$page->geonameId;
						$city->name            = (string)$page->toponymName;
						$city->latitude        = (float)$page->lat;
						$city->longitude       = (float)$page->lng;
						$city->population      = (int)$page->population;
						$city->geo_country_id  = $country->id;
						$city->geo_timezone_id = Model_Geo_Timezone::factory((string)$page->timezone)->id;
						try {
							$city->save();
						} catch (Validation_Exception $e) {
							return false;
						}
					}
				}

			}

			self::$_cities[$city->id] = $city;
		} else {
			$city = self::$_cities[$id];
		}

		// Localization
		if (!isset($city->i18n[$lang])) {
			$languages = Kohana::config('geo.languages');
			$i18n = (array)$city->i18n;
			foreach ($languages as $language) {
				if (!isset($i18n[$language]) && $page = self::_get($city->id, $language)) {
					$i18n[$language] = (string)$page->name;
				}
			}
			$city->i18n = $i18n;
			$city->save();
		}

		return $city;
	}


	/**
	 * Get country by id or country code
	 *
	 * @static
	 * @param   string|integer  $code  country geonameId or country code
	 * @return  Model_Geo_Country
	 */
	public static function find_country($code, $lang = 'en') {
		$id = is_numeric($code) ? (int)$code : strtoupper($code);
		if (!$id) {
			return false;
		}

		// Try local cache first
		if (!isset(self::$_countries[$id])) {

			// Not found from cache, load from db if preferred
			$country = Model_Geo_Country::factory($id);
			if (!$country->loaded()) {

				// Still not loaded, load from GeoNames
				$country = Model_Geo_Country::factory();
				if (is_int($id)) {

					// geonameId given
					if ($details = self::_get((int)$id, $lang)) {
						$info = self::_country_info((string)$details->countryCode, $lang);
					}

				} else {

					// Country code given
					if ($info = self::_country_info($id, $lang)) {
						$details = self::_get((int)$info->country->geonameId, $lang);
					}

				}

				if (!empty($info) && !empty($details)) {
					$country->id         = (int)$info->country->geonameId;
					$country->name       = (string)$details->toponymName;
					$country->code       = strtoupper((string)$details->countryCode);
					$country->currency   = (string)$info->country->currencyCode;
					$country->population = (int)$details->population;
					try {
						$country->save();
					} catch (Validation_Exception $e) {
						return false;
					}

				}
			}

			self::$_countries[$country->id] = self::$_countries[$country->code] = $country;
		} else {
			$country = self::$_countries[$id];
		}

		// Localization
		if (!isset($country->i18n[$lang])) {
			$languages = Kohana::config('geo.languages');
			$i18n = (array)$country->i18n;
			foreach ($languages as $language) {
				if (!isset($i18n[$language]) && $info = self::_country_info($country->code, $language)) {
					$i18n[$language] = (string)$info->country->countryName;
				}
			}
			$country->i18n = $i18n;
			$country->save();
		}

		return $country;
	}

}

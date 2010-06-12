<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Geo library to handle GeoNames etc geographical
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
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
	 * Get country info from GeoNames by geonameId
	 *
	 * @static
	 * @param   integer  $id
	 * @return  array
	 */
	private static function _country_info($id, $lang = 'en') {
		try {
			return new SimpleXMLElement(Kohana::config('geo.base_url') . '/countryInfo?country=' . (int)$id . '&lang=' . $lang, null, true);
		} catch (Exception $e) { }

		return false;
	}


	/**
	 * Get from GeoNames by geonameId
	 *
	 * @static
	 * @param   integer  $id
	 * @return  array
	 */
	private static function _get($id, $lang = 'en') {
		try {
			return new SimpleXMLElement(Kohana::config('geo.base_url') . '/get?geonameId=' . (int)$id . '&lang=' . $lang . '&style=full', null, true);
		} catch (Exception $e) { }

		return false;
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

		$languages = Kohana::config('geo.languages');

		// Try local cache first
		if (!isset(self::$_cities[$id])) {

			// Not found from cache, load from db if preferred
			$city = Jelly::select('geo_city')->load($id);
			if (!$city->loaded()) {

				// Still not loaded, load from GeoNames
				$city = Jelly::factory('geo_city');
				if ($page = self::_get($id, $lang)) {
					if ($country = self::find_country((string)$page->countryCode, $lang)) {
						$city->id         = (int)$page->geonameId;
						$city->name       = (string)$page->toponymName;
						$city->latitude   = (float)$page->lat;
						$city->longitude  = (float)$page->lng;
						$city->population = (int)$page->population;
						$city->country    = $country;
						$city->timezone   = Jelly::select('geo_timezone')->load((string)$page->timezone);
						try {
							$city->save();
						} catch (Validate_Exception $e) {
							return false;
						}
					}
				}

			}

			self::$_cities[$city->id] = $city;
		}

		// Localization
		if (!isset($city->i18n[$lang])) {
			$page = self::_get($city->id, $lang);
			$city->i18n[$lang] = (string)$page->name;
			$city->save();
		}

		return self::$_cities[$id];
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

		$languages = Kohana::config('geo.languages');

		// Try local cache first
		if (!isset(self::$_countries[$id])) {

			// Not found from cache, load from db if preferred
			$country = Jelly::select('geo_country')->load($id);
			if (!$country->loaded()) {

				// Still not loaded, load from GeoNames
				$country = Jelly::factory('geo_country');
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
					$country->i18n       = array($lang => (string)$info->country->countryName);

					try {
						$country->save();
					} catch (Validate_Exception $e) {
						return false;
					}

				}
			}

			self::$_countries[$country->id] = self::$_countries[$country->code] = $country;
		}

		// Localization
		if (!isset($country->i18n[$lang])) {
			$info = self::_country_info($country->code, $lang);
			$country->i18n[$lang] = (string)$info->country->countryName;
			$country->save();
		}

		return self::$_countries[$id];
	}

}

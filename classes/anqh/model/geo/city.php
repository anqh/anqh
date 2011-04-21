<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Geo City model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Geo_City extends AutoModeler {

	protected $_table_name = 'geo_cities';

	protected $_data = array(
		'id'              => null,
		'name'            => null,
		'latitude'        => null,
		'longitude'       => null,
		'population'      => null,
		'created'         => null,
		'modified'        => null,
		'i18n'            => null,
		'geo_country_id'  => null,
		'geo_timezone_id' => null,
	);

	protected $_rules = array(
		'name'            => array('not_empty', 'max_length' => array(':value', 200)),
		'latitude'        => array('numeric'),
		'longitude'       => array('numeric'),
		'population'      => array('digit'),

		'geo_country_id'  => array('digit'),
		'geo_timezone_id' => array('digit'),
	);


	/**
	 * Override __get() to handle JSON in i18n, returned as array.
	 *
	 * @param   string  $key
	 * @return  mixed
	 */
	public function __get($key) {
		if ($key == 'i18n' && $this->_data['i18n']) {
			return json_decode($this->_data['i18n'], true);
		}

		return parent::__get($key);
	}


	/**
	 * Override __set() to handle JSON.
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 */
	public function __set($key, $value) {
		if ($key == 'i18n' && is_array($value)) {
			$value = @json_encode($value);
		}

		parent::__set($key, $value);
	}

}

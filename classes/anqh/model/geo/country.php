<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Geo Country model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Geo_Country extends AutoModeler {

	protected $_table_name = 'geo_countries';

	protected $_data = array(
		'id'         => null,
		'name'       => null,
		'code'       => null,
		'currency'   => null,
		'population' => null,
		'created'    => null,
		'modified'   => null,
		'i18n'       => null,
	);

	protected $_rules = array(
		'name'            => array('not_empty', 'max_length' => array(':value', 200)),
		'code'            => array('not_empty', 'exact_length' => array(':value', 2)),
		'currency'        => array('exact_length' => array(':value', 3)),
		'population'      => array('digit'),
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

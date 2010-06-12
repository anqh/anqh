<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Geo Country model builder
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Builder_Geo_Country extends Jelly_Builder {

	/**
	 * Load by geonameId or country code
	 *
	 * @param   mixed  $value
	 * @return  string
	 */
	public function unique_key($value) {
		return is_numeric($value) ? $this->_meta->primary_key() : 'code';
	}

}

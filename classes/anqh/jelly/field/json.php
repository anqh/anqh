<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * JSON field for Jelly
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Jelly_Field_JSON extends Jelly_Field_Text {

	/**
	 * @var  boolan  Return as array or object
	 */
	public $array = true;

	/**
	 * @var  boolean  Don't trim JSON
	 */
	public $trim = false;

	/**
	 * Decode data back to original form
	 *
	 * @param   mixed  $value
	 * @return  mixed
	 */
	public function set($value) {
		if ($value === null || ($this->null && empty($value))) {
			return null;
		}

		if ($return = @json_decode($value, $this->array)) {
			return $return;
		}

		return $value;
	}


	/**
	 * Encode data on save
	 *
	 * @param   mixed  $value
	 * @return  string
	 */
	public function save($model, $value, $loaded) {
		return $value === null && $this->null ? null : json_encode($value);
	}

}

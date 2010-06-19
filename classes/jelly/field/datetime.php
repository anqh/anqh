<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Date and Time field for Jelly
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Jelly_Field_DateTime extends Field_Timestamp {

	/**
	 * Adds a date validation rule if it doesn't already exist.
	 *
	 * @param   string  $model
	 * @param   string  $column
	 */
	public function initialize($model, $column) {
		parent::initialize($model, $column);

		$this->rules += array(
			//'datetime'   => null,
		);
	}


	/**
	 * Support array(date, time) value
	 *
	 * @param   mixed  $value
	 * @return  mixed
	 */
	public function set($value) {
		if (is_array($value)) {
			$date = Arr::get($value, 'date');
			$time = Arr::get($value, 'time');

			$value = strtotime($date . ' ' . $time);
		}

		return parent::set($value);
	}

}

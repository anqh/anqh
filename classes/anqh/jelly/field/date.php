<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Date field for Jelly
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Jelly_Field_Date extends Jelly_Field_Timestamp {

	/**
	 * @var  string  A date formula representing the time in the database
	 */
	public $format = 'Y-m-d';

	/**
	 * @var  string  A pretty format used for representing the date to users
	 */
	public $pretty_format = 'j.n.Y';


	/**
	 * Adds a date validation rule if it doesn't already exist.
	 *
	 * @param   string  $model
	 * @param   string  $column
	 */
	public function initialize($model, $column) {
		parent::initialize($model, $column);

		$this->rules += array(
			'date'       => null,
			'max_length' => array(10),
		);
	}

}

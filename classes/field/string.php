<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Jelly String Field
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Field_String extends Jelly_Field_String {

	/**
	 * @var  boolean  Empty should be null
	 */
	public $null = true;


	/**
	 * Adds a trim filter if it doesn't already exist.
	 *
	 * @param   string  $model
	 * @param   string  $column
	 * @return  void
	 **/
	public function initialize($model, $column) {
		parent::initialize($model, $column);

		$this->filters += array($this->null ? 'Field_String::trim' : 'trim' => null);
	}


	/**
	 * Trim string, return empty() strings as null
	 *
	 * @static
	 * @param   string  $value
	 * @return  string
	 */
	public static function trim($value) {
		$value = trim($value);

		return empty($value) ? null : $value;
	}

}

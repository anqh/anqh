<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * URL field for Jelly
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Jelly_Field_URL extends Field_String {

	/**
	 * Adds an URL validation rule if it doesn't already exist.
	 *
	 * @param   string  $model
	 * @param   string  $column
	 * @return  void
	 **/
	public function initialize($model, $column) {
		parent::initialize($model, $column);

		$this->rules += array('url' => null);
	}

}

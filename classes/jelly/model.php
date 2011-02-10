<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Jelly Model model validation fix
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Jelly_Model extends Jelly_Model_Core {

	/**
	 * Create a Jelly model
	 *
	 * @static
	 * @return  Jelly_Model
	 */
	public static function factory() {
	  return Jelly::factory(Jelly::model_name(get_called_class()));
	}


	/**
	 * Load a Jelly model
	 *
	 * @static
	 * @param   integer|string  $id  Primary key
	 * @return  Jelly_Model
	 */
	public static function find($id) {
		return Jelly::select(get_called_class())->load($id);
	}


	/**
	 * Find all models
	 *
	 * @static
	 * @return  Jelly_Collection
	 */
	public static function find_all() {
		return Jelly::select(get_called_class())->execute();
	}


	/**
	 * Get model original value, e.g., foreign model id
	 *
	 * @param   string  $name
	 * @return  mixed
	 */
	public function original($name = null) {
		return Arr::get($this->_original, $name);
	}


	/**
	 * Get model URL slug
	 *
	 * @return  string
	 */
	public function slug() {
		return $this->name();
	}


	/**
	 * Validates the current state of the model.
	 *
	 * Only changed data is validated, unless $data is passed.
	 *
	 * @param   array  $data
	 * @throws  Validate_Exception
	 * @return  array
	 */
	public function validate($data = null) {
		if ($data === null) {
			$data = $this->_loaded ? $this->_changed : $this->_changed + $this->_original;
		}

		return parent::validate($data);
	}

}

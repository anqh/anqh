<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Jelly Model model validation fix
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Jelly_Model extends Jelly_Model_Core {

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

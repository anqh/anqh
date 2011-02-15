<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Jelly Field model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Jelly_Field extends Jelly_Core_Field {

	/**
	 * Sets all options
	 */
	public function __construct($options = array())	{

		// Assume it's the column name
		if (is_string($options)) {
			$this->column = $options;
		} elseif (is_array($options)) {

			// Just throw them into the class as public variables
			foreach ($options as $name => $value) {
				$this->$name = $value;
			}

		}

		// Check as to whether we need to add some callbacks for shortcut properties
		if ($this->unique === true) {
			$this->rules[] = array($this, '_is_unique');
		}
	}

}

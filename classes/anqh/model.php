<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model extends Kohana_Model {

	/**
	 * Extract model name from model.
	 *
	 *  Model::model_name('Model_User') => 'user'
	 *  Model_User::model_name() => 'user'
	 *
	 * @static
	 * @param   string|Model  $model
	 * @return  string
	 *
	 * @throws  Kohana_Exception
	 */
	public static function model_name($model = null) {
		if (is_null($model)) {

			// Get current model
			$model = get_called_class();

		} else if (is_object($model)) {

			// Model is a model
			$model = get_class($model);

		} else if (!is_string($model)) {

			// Invalid
			throw new Kohana_Exception(':model is not a proper model.', array(':model' => $model));

		}
		$model = UTF8::strtolower($model ? $model : get_called_class());

		return (strpos($model, $prefix = 'model_') === 0) ? substr($model, strlen($prefix)) : $model;
	}

}

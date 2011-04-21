<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Model Exception
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Exception extends Kohana_Exception {

	const ERROR_LEVEL = 'WARNING';


	/**
	 * Model not found or no access
	 *
	 * @param  Model    $model
	 * @param  integer  $id
	 */
	public function __construct(Model $model, $id = 0) {
		parent::__construct('Model not found: :model #:id', array(
			':id'    => $id,
			':model' => Model::model_name($model),
		));
	}

}

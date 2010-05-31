<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Model Exception
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Exception extends Kohana_Exception {

	const NOT_FOUND   = 0;
	const PERMISSION  = 1;
	const ERROR_LEVEL = 'WARNING';


	/**
	 * Model not found or no access
	 *
	 * @param  Jelly_Model $model
	 * @param  integer     $id
	 * @param  integer     $code
	 * @param  string      $permission
	 * @param  integer     $code
	 */
	public function __construct(Jelly_Model $model, $id = 0, $code = self::NOT_FOUND, $permission = null) {
		$values = array(
			':id'    => $id,
			':model' => Jelly::model_name($model),
		);

		switch ($code) {
			case self::NOT_FOUND: $message = 'Model not found: :id @ :model'; break;
			case self::PERMISSION: $message = "Permission ':permission' denied: :id @ :model"; $values[':permission'] = $permission; break;
			default: $message = 'Model Exception: :id @ :model'; break;
		}

		parent::__construct($message, $values, $code);
	}

}

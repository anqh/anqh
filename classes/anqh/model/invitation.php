<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Invitation model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Invitation extends Jelly_Model implements Permission_Interface {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id' => new Jelly_Field_Primary,
			'code' => new Jelly_Field_String(array(
				'unique' => true,
				'rules' => array(
					'not_empty'    => null,
					'exact_length' => array(16),
				),
			)),
			'email' => new Jelly_Field_Email(array(
				'rules' => array(
					'not_empty'  => null,
					'min_length' => array(6),
					'max_length' => array(127),
				),
				'callbacks' => array(
					'unique' => array('Model_Invitation', '_unique')
				),
			)),
			'created' => new Jelly_Field_Timestamp(array(
				'auto_now_create' => true,
			)),
		));
	}


	/**
	 * Create invitation code
	 *
	 * @return  string
	 */
	public function code() {
		return text::random('alnum', 16);
	}


	/**
	 * Find invitation by code
	 *
	 * @static
	 * @param   string  $code
	 * @return  Model_Invitation
	 */
	public static function find_by_code($code) {
		return Jelly::query('invitation')
			->where('code', '=', $code)
			->limit(1)
			->select();
	}


	/**
	 * Check permission
	 *
	 * @param   string      $permission
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function has_permission($permission, $user) {
		switch ($permission) {
			case self::PERMISSION_CREATE:
			case self::PERMISSION_DELETE:
			case self::PERMISSION_READ:
			case self::PERMISSION_UPDATE:
		    return true;
		}

		return false;
	}


	/**
	 * Validate callback wrapper for checking email uniqueness
	 *
	 * @static
	 * @param  Validate  $array
	 * @param  string    $field
	 */
	public static function _unique(Validate $array, $field) {
		if (Model_User::find_user($array[$field])) {
			$array->error($field, 'unique', array('param1' => $field));
		}
	}

}

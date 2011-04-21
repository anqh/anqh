<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Invitation model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Invitation extends AutoModeler implements Permission_Interface {

	protected $_table_name = 'invitations';

	protected $_data = array(
		'id'      => null,
		'code'    => null,
		'email'   => null,
		'created' => null,
	);

	protected $_rules = array(
		'code'    => array('not_empty', 'exact_length' => array(':value', 16), 'AutoModeler::unique' => array(':model', ':value', ':field')),
		'email'   => array('not_empty', 'email', 'length' => array(':value', 6, 127), 'Model_Invitation::_unique' => array(':validation', ':field')),
	);


	/**
	 * Load country
	 *
	 * @param  integer|string  $id
	 */
	public function __construct($id = null) {
		parent::__construct();

		if ($id !== null) {
			$this->load(DB::select()->where(is_numeric($id) ? 'id' : 'code', '=', $id));
		}
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
	public static function _unique(Validation $array, $field) {
		if (Model_User::find_user($array[$field])) {
			$array->error($field, 'unique', array('param1' => $field));
		}
	}

}

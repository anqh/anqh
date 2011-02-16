<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Role model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Role extends Jelly_Model implements Permission_Interface {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id' => new Jelly_Field_Primary,
			'name' => new Jelly_Field_String(array(
				'label'  => __('Name'),
				'unique' => true,
				'rules'  => array(
					'max_length' => array(32),
					'not_empty'  => null,
				),
			)),
			'description' => new Jelly_Field_Text(array(
				'label' => __('Description'),
			)),
			'users' => new Jelly_Field_ManyToMany,
		));
	}


	/**
	 * Find a role by id or name
	 *
	 * @static
	 * @param   string|integer  $role
	 * @return  Model_Role
	 */
	public static function find($role) {
		$model = is_numeric($role)
			? Model_Role::find($role)
			: Jelly::query('role')
				->where('name', '=', $role)
				->limit(1)
				->select();

		return $model->loaded() ? $model : null;
	}


	/**
	 * Check permission
	 *
	 * @param   string      $permission
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function has_permission($permission, $user) {

		// Don't allow to delete critical roles
		$status = ($permission !== self::PERMISSION_DELETE || !in_array($this->name, array('login', 'admin')));

		return $status;
	}

}

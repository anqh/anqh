<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Role model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Role extends Jelly_Model implements Interface_Permission {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->name_key('name')
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String(array(
					'label'  => __('Name'),
					'unique' => true,
					'rules'  => array(
						'max_length' => array(32),
						'not_empty' => array(true),
					)
				)),
				'description' => new Field_Text(array(
					'label' => __('Description'),
				)),
				'users' => new Field_ManyToMany,
		));
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

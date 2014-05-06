<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Roles controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Roles extends Controller_Page {

	/**
	 * Construct controller.
	 */
	public function before() {
		parent::before();

		Permission::required(new Model_Role, Model_Role::PERMISSION_UPDATE);
	}


	/**
	 * Controller default action.
	 */
	public function action_index() {
		$this->view = View_Page::factory(__('Roles'));

		$this->page_actions[] = array(
			'link'  =>  Route::url('role'),
			'text'  => '<i class="icon-plus-sign icon-white"></i> ' . __('Add new role'),
			'class' => 'btn btn-primary role-add'
		);

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_roles());
	}


	/**
	 * Action: delete
	 */
	public function action_delete() {
		$this->history = false;

		$role_id = (int)$this->request->param('id');
		$role = Model_Role::factory($role_id);
		if (!$role->loaded() || !Security::csrf_valid()) {
			throw new Model_Exception($role, $role_id);
		}
		Permission::required($role, Model_Role::PERMISSION_DELETE);

		$role->delete();

		Request::back(Route::url('roles'));
	}


	/**
	 * Action: edit
	 */
	public function action_edit() {
		$this->history = false;

		// Load role
		$role_id = (int)$this->request->param('id', 0);
		if ($role_id) {
			$role = Model_Role::factory($role_id);
			if (!$role->loaded()) {
				throw new Model_Exception($role, $role_id);
			}
			Permission::required($role, Model_Role::PERMISSION_UPDATE);
		} else {
			$role = Model_Role::factory();
			Permission::required($role, Model_Role::PERMISSION_CREATE);
		}

		// Handle post
		$errors = array();
		if ($_POST) {
			$role->name        = Arr::get($_POST, 'name');
			$role->description = Arr::get($_POST, 'description');
			try {
				$role->save();
				$this->request->redirect(Route::url('roles'));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}

		// Set title
		$this->view = View_Page::factory(__('Role') . ($role->name ? ': ' . $role->name : ''));

		// Set actions
		if ($role->loaded() && Permission::has($role, Model_Role::PERMISSION_DELETE)) {
			$this->page_actions[] = array(
				'link'  =>  Route::model($role, 'delete') . '?token=' . Security::csrf(),
				'text'  =>  '<i class="icon-trash icon-white"></i> ' . __('Delete role'),
				'class' => 'btn btn-danger role-delete'
			);
		}

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_role($role, $errors));
	}


	/**
	 * Get role editor.
	 *
	 * @param   Model_Role  $role
	 * @param   array       $errors
	 * @return  View_Admin_Role
	 */
	public function section_role(Model_Role $role = null, array $errors = null) {
		$section = new View_Admin_Role($role);
		$section->errors = $errors;

		return $section;
	}


	/**
	 * Get roles list.
	 *
	 * @return  View_Admin_Roles
	 */
	public function section_roles() {
		return new View_Admin_Roles();
	}

}

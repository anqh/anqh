<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Roles controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Roles extends Controller_Template {

	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		if (!Visitor::instance()->logged_in('admin')) {
			throw new Kohana_Exception('Unauthorized access');
		}

	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$this->page_title = __('Roles');
		$this->page_actions[] = array('link' => Route::get('role')->uri(array('action' => 'create')), 'text' => __('Add new role'), 'class' => 'role-add');

		Widget::add('main', View_Module::Factory('roles/roles', array('roles' => Jelly::select('role')->execute())));
	}


	/**
	 * Action: delete
	 */
	public function action_delete($role_id) {
		$this->history = false;

		$role = Jelly::select('role', (int)$role_id);
		if (!$role->loaded()) {
			throw new Model_Exception($role, (int)$role_id, Model_Exception::NOT_FOUND);
		}
		if (!Permission::has($role, Model_Role::PERMISSION_DELETE, $this->user)) {
			throw new Model_Exception($role, (int)$role_id, Model_Exception::PERMISSION, Model_Role::PERMISSION_DELETE);
		}
		$role->delete();

		Request::back(Route::get('roles')->uri());
	}


	/**
	 * Action: edit
	 */
	public function action_edit($role_id = 0) {
		$this->history = false;

		// Load role
		if ($role_id) {
			$role = Jelly::select('role', (int)$role_id);
			if (!$role->loaded()) {
				throw new Model_Exception($role, (int)$role_id, Model_Exception::NOT_FOUND);
			}
		} else {
			$role = Jelly::factory('role');
		}

		// Handle post
		$errors = array();
		if ($_POST) {
			$role->set($_POST);
			try {
				$role->save();
				$this->request->redirect(Route::get('roles')->uri());
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}

		// Set title
		$this->page_title = __('Role') . ($role->name ? ': ' . HTML::chars($role->name) : '');

		// Set actions
		if ($role->loaded()) {
			$this->page_actions[] = array('link' => URL::model($role) . '/delete', 'text' => __('Delete role'), 'class' => 'role-delete');
		}

		// Build form
		$form = array(
			'values' => $role,
			'errors' => $errors,
			'cancel' => Request::back(Route::get('roles')->uri(), true),
			'groups' => array(
				array(
					'fields' => array(
						'name'        => array(),
						'description' => array(),
					)
				)
			)
		);
		//Widget::add('main', View_Module::factory('roles/edit', array('role' => $role, 'errors' => $errors)));
		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}

}

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
			$this->request->redirect('/');
		}

	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$this->page_title = __('Roles');
		$this->page_actions[] = array('link' => 'role/add', 'text' => __('Add new role'), 'class' => 'role-add');

		Widget::add('main', View_Module::Factory('roles/roles', array('roles' => Jelly::select('role')->execute())));
	}


	/**
	 * Action: delete
	 */
	public function action_delete($role_id) {
		Widget::add('main', 'delete');
	}


	/**
	 * Action: role
	 */
	public function action_edit($role_id) {
		$this->history = false;
		
		$role = Jelly::select('role', (int)$role_id);

		$form_values = $form_errors = array();

		if ($role->loaded()) {
			$this->page_title = HTML::chars($role->name);
			$this->page_actions[] = array('link' => URL::model($role) . '/delete', 'text' => __('Delete role'), 'class' => 'role-delete');
			Widget::add('main', View_Module::factory('roles/edit', array('values' => $form_values, 'errors' => $form_errors)));
		} else {
			throw new Model_Exception('Failed to load :model: :id', array(':id' => (int)$role_id, ':model' => Jelly::model_name($role)));
		}
	}

}

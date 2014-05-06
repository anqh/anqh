<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Forum Group controller
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Forum_Group extends Controller_Forum {

	/**
	 * Action: delete
	 */
	public function action_delete() {
		$this->history = false;

		$group_id = (int)$this->request->param('id');
		$group = Model_Forum_Group::factory($group_id);
		if (!$group->loaded()) {
			throw new Model_Exception($group, $group_id);
		}
		Permission::required($group, Model_Forum_Group::PERMISSION_DELETE, Visitor::$user);

		$group->delete();
		$this->request->redirect(Route::get('forum')->uri());
	}


	/**
	 * Action: edit
	 */
	public function action_edit() {
		$this->history = false;
		$this->tabs = null;

		$group_id = (int)$this->request->param('id', null);

		// Load group
		if ($group_id) {
			$group = Model_Forum_Group::factory((int)$group_id);
			if (!$group->loaded()) {
				throw new Model_Exception($group, (int)$group_id);
			}
		} else {
			$group = Model_Forum_Group::factory();
			$group->created   = time();
			$group->author_id = Visitor::$user->id;
		}
		Permission::required($group, $group->loaded() ? Model_Forum_Group::PERMISSION_UPDATE : Model_Forum_Group::PERMISSION_CREATE, Visitor::$user);

		// Handle post
		$errors = array();
		if ($_POST) {
			$group->set_fields(Arr::intersect($_POST, array('name', 'description', 'sort')));
			try {
				$group->save();
				$this->request->redirect(Route::model($group));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}


		// Build page
		$this->view      = new View_Page(__('Forum group') . ($group->name ? ': ' . HTML::chars($group->name) : ''));
		$this->view->tab = 'areas';

		// Set actions
		if ($group->loaded() && Permission::has($group, Model_Forum_Group::PERMISSION_DELETE, Visitor::$user)) {
			$this->view->actions[] = array(
				'link'  => Route::model($group, 'delete'),
				'text'  => '<i class="icon-trash icon-white"></i> ' . __('Delete group'),
				'class' => 'btn btn-danger group-delete',
			);
		}

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_edit($group, $errors));
	}


	/**
	 * Action: index
	 */
	public function action_index() {

		// Load groups
		$groups = Model_Forum_Group::factory()->find_all();

		// Build page
		$this->view      = new View_Page(__('Forum'));
		$this->view->tab = 'areas';

		// Set actions
		if (Permission::has(new Model_Forum_Group, Model_Forum_Group::PERMISSION_CREATE, Visitor::$user)) {
			$this->view->actions[] = array(
				'link' => Route::url('forum_group_add'),
				'text' => '<i class="icon-plus-sign icon-white"></i> ' . __('New group'),
			);
		}

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_groups($groups));

		$this->_side_views();
	}


	/**
	 * Get group edit form.
	 *
	 * @param  Model_Forum_Group  $group
	 * @param  array              $errors
	 */
	public function section_edit($group, $errors = null) {
		$section = new View_Forum_GroupEdit($group);
		$section->errors = $errors;

		return $section;
	}


	/**
	 * Get forum groups view.
	 *
	 * @param  Model_Forum_Group[]  $groups
	 */
	public function section_groups($groups) {
		return new View_Forum_Group($groups);
	}

}

<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Forum Group controller
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
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
		Permission::required($group, Model_Forum_Group::PERMISSION_DELETE, self::$user);

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
			$group->author_id = self::$user->id;
		}
		Permission::required($group, $group->loaded() ? Model_Forum_Group::PERMISSION_UPDATE : Model_Forum_Group::PERMISSION_CREATE, self::$user);

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

		// Set title
		$this->page_title = __('Forum group') . ($group->name ? ': ' . HTML::chars($group->name) : '');

		// Set actions
		if ($group->loaded() && Permission::has($group, Model_Forum_Group::PERMISSION_DELETE, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($group, 'delete'), 'text' => __('Delete group'), 'class' => 'group-delete');
		}

		Widget::add('main', View_Module::factory('forum/group_edit', array(
			'errors' => $errors,
			'group'  => $group,
		)));
	}


	/**
	 * Action: index
	 */
	public function action_index() {
		$this->tab_id = 'areas';

		// Load group(s)
		$group_id = (int)$this->request->param('id');
		if (!$group_id) {

			// All groups
			$groups = Model_Forum_Group::factory()->find_all();
			if (Permission::has(new Model_Forum_Group, Model_Forum_Group::PERMISSION_CREATE, self::$user)) {
				$this->page_actions[] = array('link' => Route::get('forum_group_add')->uri(), 'text' => __('New group'), 'class' => 'group-add');
			}

		} else {

			// One group
			$group = Model_Forum_Group::factory($group_id);
			if (!$group->loaded()) {
				throw new Model_Exception($group, $group_id);
			}
			Permission::required($group, Model_Forum_Group::PERMISSION_READ, self::$user);

			if (Permission::has($group, Model_Forum_Group::PERMISSION_UPDATE, self::$user)) {
				$this->page_actions[] = array('link' => Route::model($group, 'edit'), 'text' => __('Edit group'), 'class' => 'group-edit');
			}
			if (Permission::has($group, Model_Forum_Group::PERMISSION_CREATE_AREA, self::$user)) {
				$this->page_actions[] = array('link' => Route::model($group, 'add'), 'text' => __('New area'), 'class' => 'area-add');
			}
			$groups = array($group);
		}

		$this->page_title = count($groups) > 1 ? __('Forum areas') : $groups[0]->name;
		foreach ($groups as $group) {
			Widget::add('main', View_Module::factory('forum/group', array('group' => $group, 'user' => self::$user)));
		}

		$this->side_views();
	}


}

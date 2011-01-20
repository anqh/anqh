<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Tags controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Tags extends Controller_Template {

	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		if (!Visitor::instance()->logged_in('admin')) {
			throw new Permission_Exception(new Model_Tag);
		}

		$this->page_title = __('Tags');
	}


	/**
	 * Action: add tag
	 */
	public function action_add() {
		return $this->_edit_tag((int)$this->request->param('id'));
	}


	/**
	 * Action: add group
	 */
	public function action_addgroup() {
		return $this->_edit_group();
	}


	/**
	 * Action: delete tag
	 */
	public function action_delete() {
		$this->history = false;

		$tag_id = (int)$this->request->param('id');
		$tag = Model_Tag::factory($tag_id);
		if (!$tag->loaded()) {
			throw new Model_Exception($tag, $tag_id);
		}

		$group = $tag->group;
		$tag->delete();

		$this->request->redirect(Route::model($group));
	}


	/**
	 * Action: delete group
	 */
	public function action_deletegroup() {
		$this->history = false;

		$group_id = (int)$this->request->param('id');
		$group = Model_Tag_Group::factory($group_id);
		if (!$group->loaded()) {
			throw new Model_Exception($group, $group_id);
		}

		$group->delete();

		$this->request->redirect(Route::get('tags')->uri());
	}


	/**
	 * Action: edit tag
	 */
	public function action_edit() {
		return $this->_edit_tag(null, (int)$this->request->param('id'));
	}


	/**
	 * Action: edit group
	 */
	public function action_editgroup() {
		return $this->_edit_group((int)$this->request->param('id'));
	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$this->page_actions[] = array('link' => Route::get('tag_group_add')->uri(), 'text' => __('Add group'), 'class' => 'group-add');

		Widget::add('main', View_Module::factory('tags/groups', array(
			'groups' => Model_Tag_Group::find_all(),
		)));
	}


	/**
	 * Action: group
	 */
	public function action_group() {
		$group_id = (int)$this->request->param('id');
		$group = Model_Tag_Group::factory($group_id);
		if (!$group->loaded()) {
			throw new Model_Exception($group, $group_id);
		}

		$this->page_title = HTML::chars($group->name);
		$this->page_subtitle = HTML::chars($group->description);

		$this->page_actions[] = array('link' => Route::model($group, 'editgroup'), 'text' => __('Edit group'), 'class' => 'group-edit');
		$this->page_actions[] = array('link' => Route::model($group, 'add'),  'text' => __('Add tag'),    'class' => 'tag-add');

		Widget::add('main', View_Module::factory('tags/tags', array(
			'tags' => $group->tags,
		)));
	}


	/**
	 * Action: tag
	 */
	public function action_tag() {
		$tag_id = (int)$this->request->param('id');
		$tag = Model_Tag::factory($tag_id);
		if (!$tag->loaded()) {
			throw new Model_Exception($tag, $tag_id);
		}
		$this->page_title = HTML::chars($tag->name);
		$this->page_subtitle = HTML::chars($tag->description);

		$this->page_actions[] = array('link' => Route::model($tag, 'edit'),   'text' => __('Edit tag'),  'class' => 'tag-edit');
		$this->page_actions[] = array('link' => Route::model($tag, 'delete'), 'text' => __('Delete tag'),'class' => 'tag-delete');
	}


	/**
	 * Edit tag group
	 *
	 * @param  integer  $group_id
	 */
	protected function _edit_group($group_id = null) {
		$this->history = false;

		if ($group_id) {

			// Edit group
			$group = Model_Tag_Group::factory($group_id);
			if (!$group->loaded()) {
				throw new Model_Exception($group, $group_id);
			}
			$this->page_title = HTML::chars($group->name);
			$this->page_subtitle = HTML::chars($group->description);

			$this->page_actions[] = array('link' => Route::model($group, 'deletegroup'), 'text' => __('Delete group'), 'class' => 'group-delete');
			$cancel = Route::model($group);

		} else {

			// Create new group
			$group = Model_Tag_Group::factory();
			$this->page_title = __('Tag group');
			$cancel = Route::get('tags')->uri();

		}

		$errors = array();
		if ($_POST) {
			$group->set($_POST);
			try {
				$group->save();
				$this->request->redirect(Route::model($group));
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}

		// Build form
		$form = array(
			'values' => $group,
			'errors' => $errors,
			'cancel' => $cancel,
			'groups' => array(
				array(
					'fields' => array(
						'name'        => array(),
						'description' => array(),
					)
				)
			)
		);

		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}


	/**
	 * Edit tag
	 *
	 * @param  integer  $group_id
	 * @param  integer  $tag_id
	 */
	protected function _edit_tag($group_id = null, $tag_id = null) {
		$this->history = false;

		if ($group_id) {

			// Add new tag
			$group = Model_Tag_Group::factory($group_id);
			if (!$group->loaded()) {
				throw new Model_Exception($group, $group_id);
			}
			$this->page_title = HTML::chars($group->name);
			$this->page_subtitle = HTML::chars($group->description);
			$tag = Model_Tag::factory()->set(array('group' => $group));
			$cancel = Route::model($group);

		} else if ($tag_id) {

			// Edit old tag
			$tag = Model_Tag::factory($tag_id);
			if (!$tag->loaded()) {
				throw new Model_Exception($tag, $tag_id);
			}
			$this->page_title = HTML::chars($tag->name);
			$this->page_subtitle = HTML::chars($tag->description);
			$cancel = Route::model($tag);

		} else {
			Request::back(Route::get('tags')->uri());
		}

		$errors = array();
		if ($_POST) {
			$tag->set($_POST);
			try {
				$tag->save();
				$this->request->redirect(Route::model($tag));
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}

		// Build form
		$form = array(
			'values' => $tag,
			'errors' => $errors,
			'cancel' => $cancel,
			'groups' => array(
				array(
					'fields' => array(
						'name'        => array(),
						'description' => array(),
					)
				)
			)
		);

		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}

}

<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Tags controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Tags extends Controller_Page {

	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		if (!Visitor::instance()->logged_in('admin')) {
			throw new Permission_Exception(new Model_Tag);
		}

		$this->view = View_Page::factory(__('Tags'));
	}


	/**
	 * Action: add tag
	 */
	public function action_add() {
		$this->action_tag((int)$this->request->param('id'));
	}


	/**
	 * Action: add group
	 */
	public function action_addgroup() {
		$this->action_group();
	}


	/**
	 * Action: delete tag
	 */
	public function action_delete() {
		$this->history = false;

		$tag_id = (int)$this->request->param('id');
		$tag = Model_Tag::factory($tag_id);
		if (!$tag->loaded() || !Security::csrf_valid()) {
			throw new Model_Exception($tag, $tag_id);
		}

		$group = $tag->tag_group;
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
		if (!$group->loaded() || !Security::csrf_valid()) {
			throw new Model_Exception($group, $group_id);
		}

		$group->delete();

		$this->request->redirect(Route::url('tags'));
	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$this->page_actions[] = array(
			'link'  => Route::url('tag_group', array('id' => 'add')),
			'text'  => '<i class="icon-plus-sign icon-white"></i> ' .__('Add new group'),
			'class' => 'btn btn-primary group-add'
		);

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_groups());
	}


	/**
	 * Action: group
	 */
	public function action_group() {
		$this->history = false;

		$group_id = (int)$this->request->param('id');
		if ($group_id) {

			// Edit group
			$group = Model_Tag_Group::factory($group_id);
			if (!$group->loaded()) {
				throw new Model_Exception($group, $group_id);
			}

			$this->view = View_Page::factory($group->name);
			$this->view->subtitle = HTML::chars($group->description);

			$this->page_actions[] = array(
				'link'  => Route::model($group, 'deletegroup') . '?' . Security::csrf_query(),
				'text'  => '<i class="icon-trash icon-white"></i> ' . __('Delete group'),
				'class' => 'btn btn-danger group-delete'
			);
			$this->page_actions[] = array(
				'link'  => Route::model($group, 'add'),
				'text'  => '<i class="icon-plus-sign icon-white"></i> ' . __('Add new tag'),
				'class' => 'btn btn-primary tag-add'
			);

		} else {

			// Create new group
			$group = Model_Tag_Group::factory();
			$group->author_id = self::$user->id;
			$group->created   = time();

			$this->view = View_Page::factory(__('Tag group'));

		}

		$errors = array();
		if ($_POST) {
			$group->name        = Arr::get($_POST, 'name');
			$group->description = Arr::get($_POST, 'description');
			try {
				$group->save();
				$this->request->redirect(Route::url('tags'));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_group($group, $errors));
	}


	/**
	 * Action: tag
	 *
	 * @param  integer  $group_id
	 */
	public function action_tag($group_id = null) {
		$this->history = false;

		if ($group_id && $this->request->action() !== 'tag') {

			// Add new tag
			$group = Model_Tag_Group::factory($group_id);
			if (!$group->loaded()) {
				throw new Model_Exception($group, $group_id);
			}
			$tag = Model_Tag::factory();
			$tag->tag_group_id = $group_id;
			$tag->author_id    = self::$user->id;
			$tag->created      = time();

			$this->view           = View_Page::factory($group->name);
			$this->view->subtitle = HTML::chars($group->description);

		} else if ($tag_id = (int)$this->request->param('id')) {

			// Edit old tag
			$tag = Model_Tag::factory($tag_id);
			if (!$tag->loaded()) {
				throw new Model_Exception($tag, $tag_id);
			}

			$this->view           = View_Page::factory($tag->name);
			$this->view->subtitle = HTML::chars($tag->description);
			$this->page_actions[] = array(
				'link'  => Route::model($tag, 'delete') . '?' . Security::csrf_query(),
				'text'  => '<i class="icon-trash icon-white"></i> ' . __('Delete tag'),
				'class' => 'btn btn-danger tag-delete'
			);

		} else {
			Request::back(Route::url('tags'));
		}

		$errors = array();
		if ($_POST) {
			$tag->name        = Arr::get($_POST, 'name');
			$tag->description = Arr::get($_POST, 'description');
			try {
				$tag->save();
				$this->request->redirect(Route::model($tag));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_tag($tag, $errors));
	}


	/**
	 * Get tag group.
	 *
	 * @param   Model_Tag_Group  $group
	 * @param   array            $errors
	 * @return  View_Admin_TagGroup
	 */
	public function section_group(Model_Tag_Group $group, array $errors = null) {
		$section = new View_Admin_TagGroup($group);
		$section->errors = $errors;

		return $section;
	}


	/**
	 * Get tag groups.
	 *
	 * @return  View_Admin_TagGroups
	 */
	public function section_groups() {
		return new View_Admin_TagGroups();
	}


	/**
	 * Get tag.
	 *
	 * @param   Model_Tag  $tag
	 * @param   array      $errors
	 * @return  View_Admin_Tag
	 */
	public function section_tag(Model_Tag $tag, array $errors = null) {
		$section = new View_Admin_Tag($tag);
		$section->errors = $errors;

		return $section;
	}

}

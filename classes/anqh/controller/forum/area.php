<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Forum Area controller
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Forum_Area extends Controller_Forum {

	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->tab_id = 'areas';
	}


	/**
	 * Action: add
	 */
	public function action_add() {
		return $this->action_edit();
	}


	/**
	 * Action: delete
	 */
	public function action_delete() {
		$this->history = false;

		$area_id = (int)$this->request->param('id');
		$area = Model_Forum_Area::find($area_id);
		if (!$area->loaded()) {
			throw new Model_Exception($area, $area_id);
		}
		Permission::required($area, Model_Forum_Area::PERMISSION_DELETE, self::$user);

		$group = $area->group;
		$area->delete();
		$this->request->redirect(Route::model($group));
	}


	/**
	 * Action: edit
	 */
	public function action_edit() {
		$this->history = false;

		// Load area
		$area_id = (int)$this->request->param('id');
		if ($area_id) {
			$area = Model_Forum_Area::find($area_id);
			if (!$area->loaded()) {
				throw new Model_Exception($area, $area_id);
			}
			Permission::required($area, Model_Forum_Area::PERMISSION_UPDATE, self::$user);
		} else {
			$area = Model_Forum_Area::factory();
		}

		// Load group
		if ($area->loaded()) {
			$group = $area->group;
		} else if ($group_id = (int)$this->request->param('group_id')) {
			$group = Model_Forum_Group::find($group_id);
			$area->group = $group;
			if (!$group->loaded()) {
				throw new Model_Exception($group, $group_id);
			}
			Permission::required($group, Model_Forum_Group::PERMISSION_CREATE_AREA, self::$user);
		}

		// Handle post
		$errors = array();
		if ($_POST) {
			$area->set($_POST);
			try {
				$area->save();
				$this->request->redirect(Route::model($area));
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}

		// Set title
		$this->page_title = __('Forum area') . ($area->name ? ': ' . HTML::chars($area->name) : '');

		// Set actions
		if ($area->loaded() && Permission::has($area, Model_Forum_Area::PERMISSION_DELETE, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($area, 'delete'), 'text' => __('Delete area'), 'class' => 'area-delete');
		}

		// Build form
		$form = array(
			'values' => $area,
			'errors' => $errors,
			'cancel' => Request::back(Route::get('forum_group')->uri(), true),
			'groups' => array(
				array(
					'fields' => array(
						'group'       => array(),
						'name'        => array(),
						'description' => array(),
						'sort'        => array(),
					),
				),
				array(
					'header' => __('Settings'),
					'fields' => array(
						'access_read'  => array(),
						'access_write' => array(),
						'type'         => array(),
						'bind'         => array(),
						'status'       => array(),
					),
				),
			),
		);

		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}


	/**
	 * Action: index
	 */
	public function action_index() {

		// Load area
		$area_id = (int)$this->request->param('id');
		$area = Model_Forum_Area::find((int)$area_id);
		if (!$area->loaded()) {
			throw new Model_Exception($area, (int)$area_id);
		}
		Permission::required($area, Model_Forum_Area::PERMISSION_READ, self::$user);

		// Set title
		$this->page_title = HTML::chars($area->name);
		$this->page_subtitle = $area->description;

		// Set actions
		if (Permission::has($area, Model_Forum_Area::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($area, 'edit', false), 'text' => __('Edit area'), 'class' => 'area-edit');
		}
		if (Permission::has($area, Model_Forum_Area::PERMISSION_POST, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($area, 'post'), 'text' => __('New topic'), 'class' => 'topic-add');
		}

		// Pagination
		$pagination = Pagination::factory(array(
			'items_per_page' => Kohana::config('forum.topics_per_page'),
			'total_items'    => $area->topic_count,
		));

		// Posts
		Widget::add('main', View_Module::factory('forum/topics', array(
			'mod_class'  => 'topics articles',
			'topics'     => $area->get('topics')->active()->pagination($pagination)->execute(),
			'pagination' => $pagination
		)));

		$this->side_views();
	}
}

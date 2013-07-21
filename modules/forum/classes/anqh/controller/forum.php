<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Forum controller
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Forum extends Controller_Page {
	public $page_id = 'forum';

	protected $_config;

	/**
	 * @var  boolean  Private topic/area hack
	 */
	protected $private = false;


	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->page_title = __('Forum');


		// Forum areas dropdown
		$groups = Model_Forum_Group::factory()->find_all();
		$areas   = array();
		foreach ($groups as $group) {
			$divider = false;
			foreach ($group->areas() as $area) {
				if (Permission::has($area, Model_Forum_Area::PERMISSION_READ, self::$user)) {
					$divider = true;
					$areas[] = array(
						'link'  => Route::model($area),
						'text'  => HTML::entities($area->name),
						'class' => 'hoverable'
					);
				}
			}
			if ($divider) {
				$areas[] = array('divider' => true);
			}
		}
		array_pop($areas);
		$this->page_actions['areas'] = array(
			'link'     => Route::url('forum'),
			'text'     => '<i class="icon-folder-open icon-white"></i> ' . __('Forum areas'),
		);
		$this->page_actions['area'] = array(
			'link'     => Route::url('forum'),
			'text'     => '',
			'dropdown' => $areas,
		);

		if (self::$user) {
			$this->page_actions['private-messages'] = array(
				'link' => Forum::private_messages_url(),
				'text' => '<i class="icon-envelope icon-white"></i> ' . __('Private messages'),
			);
		}
	}


	/**
	 * Action: latest posts
	 */
	public function action_index() {
		$this->view        = new View_Page(__('New posts'));
		$this->view->tab   = 'areas';
		$this->view->spans = View_Page::SPANS_66;

		// Actions
		if (Permission::has(new Model_Forum_Group, Model_Forum_Group::PERMISSION_CREATE, self::$user)) {
			$this->view->actions[] = array(
				'link' => Route::url('forum_group_add'),
				'text' => '<i class="icon-plus-sign icon-white"></i> ' . __('New group'),
			);
		}

		// New posts
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_topics(Model_Forum_Topic::factory()->find_active(40)));

		// Areas
		$groups = Model_Forum_Group::factory()->find_all();
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_groups($groups));

//		$this->_side_views();
	}


	/**
	 * Get forum groups view.
	 *
	 * @param  Model_Forum_Group[]  $groups
	 */
	public function section_groups($groups) {
		$section         = new View_Forum_Group($groups);
		$section->class .= ' full';

		return $section;
	}


	/**
	 * Get topic list view.
	 *
	 * @param   Model_Forum_Topic[]  $topics
	 * @return  View_Topics_List
	 */
	public function section_topic_list($topics) {
		return new View_Topics_List($topics);
	}


	/**
	 * Get bigger topic list view.
	 *
	 * @param   Model_Forum_Topic[]  $topics
	 * @return  View_Topics_Index
	 */
	public function section_topics($topics) {
		return new View_Topics_Index($topics);
	}


	/**
	 * Side views.
	 */
	public function _side_views() {

		// New posts
		$section = $this->section_topic_list(Model_Forum_Topic::factory()->find_active(20));
		$section->title = __('New posts');
		$this->view->add(View_Page::COLUMN_SIDE, $section);

		// New topics
//		$section = $this->section_topic_list(Model_Forum_Topic::factory()->find_new(20));
//		$section->title = __('New topics');
//		$this->view->add(View_Page::COLUMN_SIDE, $section);

	}

}

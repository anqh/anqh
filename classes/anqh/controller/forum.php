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

		// Generic page actions
		$this->page_actions[] = array(
			'link'  => Route::url('forum'),
			'text'  => '<i class="icon-comment icon-white"></i> ' . __('New posts'),
		);
		$this->page_actions[] = array(
			'link'  => Route::url('forum_group'),
			'text'  => '<i class="icon-folder-open icon-white"></i> ' . __('Areas'),
		);
		$this->page_id = 'forum';
		$this->page_title = __('Forum');
		$this->tabs = array(
			'index' => array('url' => Route::get('forum')->uri(),       'text' => __('New posts')),
			'areas' => array('url' => Route::get('forum_group')->uri(), 'text' => __('Areas'))
		);

		if (self::$user) {
			$this->page_actions[] = array(
				'link'  => Forum::private_messages_url(),
				'text'  => '<i class="icon-envelope icon-white"></i> ' . __('Private messages'),
			);
			$this->tabs['private'] = array('url' => Forum::private_messages_url(), 'text' => __('Private messages'));
		}
	}


	/**
	 * Action: latest posts
	 */
	public function action_index() {
		$this->view = new View_Page(__('Forum'));

		$this->view->add(View_Page::COLUMN_MAIN, $this->section_topics(Model_Forum_Topic::factory()->find_active(20)));

		$this->_side_views();
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
	 * Side tabs
	 */
	public function side_views() {
		$tabs = array(
			'active' => array('href' => '#topics-active', 'title' => __('New posts'), 'tab' => View_Module::factory('forum/topiclist', array(
				'mod_id'    => 'topics-active',
				'mod_class' => 'cut tab topics',
				'title'     => __('New posts'),
				'topics'    => Model_Forum_Topic::factory()->find_active(20),
			))),
			'latest' => array('href' => '#topics-new', 'title' => __('New topics'), 'tab' => View_Module::factory('forum/topiclist', array(
				'mod_id'    => 'topics-new',
				'mod_class' => 'cut tab topics',
				'title'     => __('New topics'),
				'topics'    => Model_Forum_Topic::factory()->find_new(10),
			))),
			'areas' => array('href' => '#forum-areas', 'title' => __('Areas'), 'selected' => $this->tab_id == 'index', 'tab' => View_Module::factory('forum/grouplist', array(
				'mod_id'    => 'forum-areas',
				'mod_class' => 'cut tab areas',
				'title'     => __('Forum areas'),
				'groups'    => Model_Forum_Group::factory()->find_all(),
				'user'      => self::$user,
			))),
		);

		Widget::add('side', View::factory('generic/tabs_side', array('id' => 'topics-tab', 'tabs' => $tabs)));
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
		$section = $this->section_topic_list(Model_Forum_Topic::factory()->find_new(20));
		$section->title = __('New topics');
		$this->view->add(View_Page::COLUMN_SIDE, $section);

		// Areas
	}

}

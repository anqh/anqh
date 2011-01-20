<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Forum controller
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Forum extends Controller_Template {

	protected $_config;


	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->page_id = 'forum';
		$this->page_title = __('Forum');
		$this->tabs = array(
			'index' => array('url' => Route::get('forum')->uri(), 'text' => __('New posts')),
			'areas' => array('url' => Route::get('forum_group')->uri(), 'text' => __('Areas'))
		);
	}


	/**
	 * Action: latest posts
	 */
	public function action_index() {
		$this->tab_id = 'index';

		Widget::add('main', View_Module::factory('forum/topics', array(
			'mod_class' => 'topics articles',
			'topics'    => Model_Forum_Topic::find_active(20)
		)));

		$this->side_views();
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
				'topics'    => Model_Forum_Topic::find_active(20),
			))),
			'latest' => array('href' => '#topics-new', 'title' => __('New topics'), 'tab' => View_Module::factory('forum/topiclist', array(
				'mod_id'    => 'topics-new',
				'mod_class' => 'cut tab topics',
				'title'     => __('New topics'),
				'topics'    => Model_Forum_Topic::find_new(20),
			))),
			'areas' => array('href' => '#forum-areas', 'title' => __('Areas'), 'selected' => $this->tab_id == 'index', 'tab' => View_Module::factory('forum/grouplist', array(
				'mod_id'    => 'forum-areas',
				'mod_class' => 'cut tab areas',
				'title'     => __('Forum areas'),
				'groups'    => Model_Forum_Group::find_all(),
				'user'      => self::$user,
			))),
		);

		Widget::add('side', View::factory('generic/tabs_side', array('id' => 'topics-tab', 'tabs' => $tabs)));
	}

}

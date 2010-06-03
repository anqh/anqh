<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Forum controller
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Forum extends Controller_Template {

	protected $_config;


	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->page_title = __('Forum');
		$this->tabs = array(
			'index' => array('link' => Route::get('forum')->uri(), 'text' => __('New posts')),
			'areas' => array('link' => Route::get('forum')->uri(array('action' => 'areas')), 'text' => __('Areas'))
		);
	}


	/**
	 * Controller default action, latest posts
	 */
	public function action_index() {
		$this->tab_id = 'index';

		Widget::add('main', View_Module::factory('forum/topics', array(
			'mod_class' => 'topics articles',
			'topics'    => Jelly::select('forum_topic')->active()->execute()
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
				'topics'    =>  Jelly::select('forum_topic')->active()->execute(),
			))),
			'latest' => array('href' => '#topics-new', 'title' => __('New topics'), 'tab' => View_Module::factory('forum/topiclist', array(
				'mod_id'    => 'topics-new',
				'mod_class' => 'cut tab topics',
				'title'     => __('New topics'),
				'topics'    =>  Jelly::select('forum_topic')->latest()->execute(),
			))),
			'areas' => array('href' => '#forum-areas', 'title' => __('Areas'), 'selected' => in_array($this->tab_id, array('active', 'latest')), 'tab' => View_Module::factory('forum/grouplist', array(
				'mod_id'    => 'forum-areas',
				'mod_class' => 'cut tab areas',
				'title'     => __('Forum areas'),
				'groups'    => Jelly::select('forum_group')->execute(),
				'user'      => $this->user,
			))),
		);

		Widget::add('side', View::factory('generic/tabs_side', array('id' => 'topics-tab', 'tabs' => $tabs)));
	}

}

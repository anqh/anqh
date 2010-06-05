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
	 * Action: area
	 *
	 * @param  integer|string  $area_id
	 */
	public function action_area($area_id) {
		$this->tabs = null;

		// Load area
		$area = Jelly::select('forum_area', (int)$area_id);
		if (!$area->loaded()) {
			throw new Model_Exception($area, (int)$area_id, Model_Exception::NOT_FOUND);
		}
		if (!Permission::has($area, Model_Forum_Area::PERMISSION_READ, $this->user)) {
			throw new Model_Exception($area, (int)$area_id, Model_Exception::PERMISSION, Model_Forum_Area::PERMISSION_READ);
		}

		// Set title
		$this->page_title = HTML::chars($area->name);
		$this->page_subtitle = $area->description;

		// Set actions
		if (Permission::has($area, Model_Forum_Area::PERMISSION_UPDATE, $this->user)) {
			$this->page_actions[] = array('link' => Route::model($area, 'edit'), 'text' => __('Edit area'), 'class' => 'area-edit');
		}
		if (Permission::has($area, Model_Forum_Area::PERMISSION_POST, $this->user)) {
			$this->page_actions[] = array('link' => Route::model($area, 'post'), 'text' => __('New topic'), 'class' => 'topic-add');
		}

		// Pagination
		$per_page = 20;
		$pagination = Pagination::factory(array(
			'items_per_page' => $per_page,
			'total_items'    => $area->num_topics,
		));

		// Posts
		Widget::add('main', View_Module::factory('forum/topics', array(
			'mod_class'  => 'topics articles',
			'topics'     => $area->get('topics')->active()->pagination($pagination)->execute(),
			'pagination' => $pagination
		)));

		$this->side_views();
	}


	/**
	 * Controller default action, latest posts
	 */
	public function action_index() {
		$this->tab_id = 'index';

		Widget::add('main', View_Module::factory('forum/topics', array(
			'mod_class' => 'topics articles',
			'topics'    => Jelly::select('forum_topic')->active()->limit(20)->execute()
		)));

		$this->side_views();
	}


	/**
	 * Action: topic
	 *
	 * @param  integer|string  $topic_id
	 */
	public function action_topic($topic_id, $params = null) {
		$this->tabs = null;

		// Load topic
		$topic = Jelly::select('forum_topic', (int)$topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, (int)$topic_id, Model_Exception::NOT_FOUND);
		}
		if (!Permission::has($topic, Model_Forum_Topic::PERMISSION_READ, $this->user)) {
			throw new Model_Exception($topic, (int)$topic_id, Model_Exception::PERMISSION, Model_Forum_Topic::PERMISSION_READ);
		}

		// Set title
		$this->page_title = ($topic->read_only ? '<span class="locked">' . __('[Locked]') . '</span> ' : '') . HTML::chars($topic->name());
		$this->page_subtitle = __('Area :area. ', array(
			':area' => HTML::anchor(Route::model($topic->area), HTML::chars($topic->area->name), array('title' => strip_tags($topic->area->description)))
		));
		$this->page_subtitle .= HTML::icon_value(array(':views' => $topic->num_reads), ':views view', ':views views', 'views');
		$this->page_subtitle .= HTML::icon_value(array(':replies' => $topic->num_posts - 1), ':replies reply', ':replies replies', 'posts');

		// Set actions
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_UPDATE, $this->user)) {
			$this->page_actions[] = array('link' => Route::model($topic, 'edit'), 'text' => __('Edit topic'), 'class' => 'topic-edit');
		}
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_POST, $this->user)) {
			$this->page_actions[] = array('link' => Route::model($topic, '#reply'), 'text' => __('Reply to topic'), 'class' => 'topic-post');
		}

		// Pagination
		$per_page = 20;
		$pagination = Pagination::factory(array(
			'items_per_page' => $per_page,
			'total_items'    => $topic->num_posts,
		));
		if (Arr::get($_GET, 'page') == 'last') {
			$pagination->last();
		}

		// Posts
		Widget::add('main', View_Module::factory('forum/topic', array(
			'mod_class'  => 'topic articles topic-' . $topic->id,
			'user'       => $this->user,
			'topic'      => $topic,
			'posts'      => $topic->get('posts')->pagination($pagination)->execute(),
			'pagination' => $pagination
		)));

		// Reply
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_POST, $this->user)) {
			Widget::add('main', View_Module::factory('forum/post_edit', array(
				'mod_id'    => 'reply',
				'mod_title' => __('Reply'),
				'form_post' => Route::model($topic, 'post'),
				'post'      => array('post_id' => 0),
				'errors'    => array(),
				'parent_id' => 0,
			)));
		}

		$this->side_views();
		/*
				if ($action == 'page' && $extra == 'last') {
					$pagination->to_last_page();
				}

				$posts = $forum_topic->forum_posts->find_all($per_page, $pagination->sql_offset);

				// Update read counter if not owner
				if (!$forum_topic->is_author($this->user)) {
					$forum_topic->reads++;
					$forum_topic->save();
				}

				*/
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
				'topics'    =>  Jelly::select('forum_topic')->active()->limit(20)->execute(),
			))),
			'latest' => array('href' => '#topics-new', 'title' => __('New topics'), 'tab' => View_Module::factory('forum/topiclist', array(
				'mod_id'    => 'topics-new',
				'mod_class' => 'cut tab topics',
				'title'     => __('New topics'),
				'topics'    =>  Jelly::select('forum_topic')->latest()->limit(20)->execute(),
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

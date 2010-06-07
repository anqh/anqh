<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Forum Topic controller
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Forum_Topic extends Controller_Forum {

	/**
	 * Action: index
	 */
	public function action_index() {
		$this->tabs = null;

		// Load topic
		$topic_id = (int)$this->request->param('id');
		$topic = Jelly::select('forum_topic')->load($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}
		Permission::required($topic, Model_Forum_Topic::PERMISSION_READ, $this->user);

		// Update read counter if not owner
		if (!$this->user || $topic->author != $this->user) {
			$topic->num_reads++;
			$topic->save();
		}

		// Set title
		$this->page_title = ($topic->status == Model_Forum_Topic::STATUS_LOCKED ? '<span class="locked">' . __('[Locked]') . '</span> ' : '') . HTML::chars($topic->name());
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
			$this->page_actions[] = array('link' => Route::model($topic, 'reply'), 'text' => __('Reply to topic'), 'class' => 'topic-post');
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
		/*
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
		*/

		$this->side_views();
	}

}

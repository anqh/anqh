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
	 * Construct controller
	 */
	public function before() {
		parent::before();

		Widget::add('head', HTML::script('js/jquery.markitup.pack.js'));
		Widget::add('head', HTML::script('js/markitup.bbcode.js'));
	}


	/**
	 * Action: delete
	 */
	public function action_delete() {
		$id = (int)$this->request->param('id');

		// Are we deleting a post, if so we have a topic_id
		$topic_id = (int)$this->request->param('topic_id');
		if ($topic_id) {
			return $this->_delete_post($topic_id, $id);
		}

		return $this->_delete_topic($id);

	}


	/**
	 * Action: edit
	 */
	public function action_edit() {
		$id = (int)$this->request->param('id');

		// Are we editing a post, if so we have a topic_id
		$topic_id = (int)$this->request->param('topic_id');
		if ($topic_id) {
			return $this->_edit_post($topic_id, $id);
		}

		return $this->_edit_topic(null, $id);
	}


	/**
	 * Action: event
	 */
	public function action_event() {
		$event_id = (int)$this->request->param('id');

		$event = Jelly::select('event')->load($event_id);
		if (!$event->loaded()) {
			throw new Model_Exception($event, $event_id);
		}

		// Go to before or after event discussion?
		$time = $this->request->param('time');
		if (!$time) {
			$time = $event->stamp_begin > time() ? 'before' : 'after';
		}
		$bind = $time == 'before' ? 'events_upcoming' : 'events_past';

		// Redirect
		if ($topic = Model_Forum_Topic::find_by_bind($event, $bind)) {
			$this->request->redirect(Route::model($topic));
		} else {
			$this->request->redirect(Route::get('forum')->uri());
		}
	}


	/**
	 * Action: index
	 */
	public function action_index() {

		// Go to post?
		$topic_id = (int)$this->request->param('topic_id');
		if ($topic_id) {
			$post_id = (int)$this->request->param('id');
		} else {
			$topic_id = (int)$this->request->param('id');
		}

		// Load topic
		/** @var  Model_Forum_Topic  $topic */
		$topic = Jelly::select('forum_topic')->load($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}
		Permission::required($topic, Model_Forum_Topic::PERMISSION_READ, self::$user);

		// Did we request single post with ajax?
		if ($this->ajax && isset($post_id)) {
			$this->history = false;
			$post = Jelly::select('forum_post')->load($post_id);
			if (!$post->loaded()) {
				throw new Model_Exception($topic, $topic_id);
			}

			// Permission is already checked by the topic, no need to check for post

			echo View::factory('forum/post', array(
				'topic'  => $topic,
				'post'   => $post,
				'number' => $topic->get_post_number($post->id) + 1,
				'user'   => self::$user));
			return;
		}

		// Add new tab
		$this->tab_id = 'area';
		$this->tabs['area'] = array('url' => Route::model($topic->area), 'text' => __('Area'));

		// Update counts
		if (!self::$user || $topic->author != self::$user) {
			$topic->read_count++;
			$topic->save();
		}
		if (self::$user) {
			$quotes = Model_Forum_Quote::find_by_user(self::$user);
			if (count($quotes)) {
				foreach ($quotes as $quote) {
					if ($topic->id == $quote->topic->id) {
						$quote->delete();
						break;
					}
				}
			}
		}

		// Facebook
		if (Kohana::config('site.facebook')) {
			Anqh::open_graph('title', $topic->name);
			Anqh::open_graph('url', URL::site(Route::get('forum_topic')->uri(array('id' => $topic->id, 'action' => '')), true));
		}
		Anqh::share(true);

		// Set title
		$this->_set_title($topic);

		// Model binding
		if ($topic->area->type == Model_Forum_Area::TYPE_BIND && $topic->bind_id) {
			if ($bind = Model_Forum_Area::get_binds($topic->area->bind)) {
				$model = Jelly::select($bind['model'])->load($topic->bind_id);
				if ($model->loaded()) {

					// Set actions
					$this->page_actions[] = array('link' => Route::model($model), 'text' => $bind['link']);

					// Set views
					foreach ((array)$bind['view'] as $view) {
						Widget::add('side', View_Module::factory($view, array(
							$bind['model'] => $model,
						)), Widget::TOP);
					}

				}
			}
		}

		// Set actions
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_POST, self::$user)) {
			$this->page_actions[] = array('link' => '#reply', 'text' => __('Reply to topic'), 'class' => 'topic-post');
		}
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($topic, 'edit'), 'text' => __('Edit topic'), 'class' => 'topic-edit');
		}

		// Pagination
		$pagination = Pagination::factory(array(
			'url'            => Route::get('forum_topic')->uri(array('id' => Route::model_id($topic))),
			'items_per_page' => Kohana::config('forum.posts_per_page'),
			'total_items'    => max(1, $topic->post_count),
		));
		if (Arr::get($_GET, 'page') == 'last') {

			// Go to last page
			$pagination->last();

		} else if (isset($post_id)) {

			// Go to post
			$pagination->item($topic->get_post_number($post_id));
			/*
			Widget::add('foot', HTML::script_source('
$(function() {
	var post = $("#post-' . $post_id . '");
	if (post) {
	 var position = post.offset();
		window.scrollTo(0, position.top);
	}
});
'));
			 */

		}

		// Posts
		Widget::add('main', View_Module::factory('forum/topic', array(
			'mod_class'  => 'topic articles topic-' . $topic->id,
			'user'       => self::$user,
			'topic'      => $topic,
			'posts'      => $topic->get('posts')->pagination($pagination)->execute(),
			'first'      => $pagination->current_first_item,
			'pagination' => $pagination
		)));

		// Reply
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_POST, self::$user)) {
			$form = array(
				'action' => Route::model($topic, 'reply'),
				'values' => Jelly::factory('forum_post'),
				'save'   => array('label' => __('Reply')),
				'groups' => array(
					array(
						'fields' => array(
							'post' => array('label' => null),
						),
					),
				)
			);

			Widget::add('main', View_Module::factory('forum/reply', array(
				'mod_id' => 'reply',
				'user'   => self::$user,
				'form'   => $form
			)));
		}

		//$this->side_views();
	}


	/**
	 * Action: post
	 */
	public function action_post() {
		return $this->_edit_topic((int)$this->request->param('id'));
	}


	/**
	 * Action: quote
	 */
	public function action_quote() {
		return $this->_edit_post((int)$this->request->param('topic_id'), null, (int)$this->request->param('id'));
	}


	/**
	 * Action: reply
	 */
	public function action_reply() {
		return $this->_edit_post((int)$this->request->param('id'));
	}


	/**
	 * Delete forum post
	 *
	 * @param  integer  $topic_id
	 * @param  integer  $post_id
	 */
	protected function _delete_post($topic_id, $post_id) {
		$this->history = false;

		// Topic is always loaded, avoid haxing attempts to edit posts from wrong topics
		$topic = Jelly::select('forum_topic')->load($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}

		// Editing a post
		$post = Jelly::select('forum_post')->load($post_id);
		if (!$post->loaded() || $post->topic->id != $topic->id || !Security::csrf_valid()) {
			throw new Model_Exception($post, $post_id);
		}
		Permission::required($post, Model_Forum_Post::PERMISSION_DELETE, self::$user);

		$post->delete();
		$topic->refresh();
		$topic->area->post_count--;
		$topic->area->save();

		if ($this->ajax) {
			return;
		}

		$this->request->redirect(Route::model($topic));
	}


	/**
	 * Delete forum topic
	 *
	 * @param  integer  $topic_id
	 */
	protected function _delete_topic($topic_id) {
		$this->history = false;

		// Topic is always loaded, avoid haxing attempts to edit posts from wrong topics
		$topic = Jelly::select('forum_topic')->load($topic_id);
		if (!$topic->loaded() || !Security::csrf_valid()) {
			throw new Model_Exception($topic, $topic_id);
		}

		Permission::required($topic, Model_Forum_Topic::PERMISSION_DELETE, self::$user);

		$area  = $topic->area;
		$posts = $topic->post_count;
		$topic->delete();
		$area->post_count -= $posts;
		$area->topic_count--;
		$area->save();

		$this->request->redirect(Route::model($area));
	}


	/**
	 * Edit forum post
	 *
	 * @param  integer  $topic_id   When replying to a topic
	 * @param  integer  $post_id    When editing a post
	 * @param  integer  $quote_id   When quoting a post
	 */
	protected function _edit_post($topic_id, $post_id = null, $quote_id = null) {
		$this->history = false;

		// Topic is always loaded, avoid haxing attempts to edit posts from wrong topics
		$topic = Jelly::select('forum_topic')->load($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}
		Permission::required($topic, Model_Forum_Topic::PERMISSION_POST, self::$user);

		if ($post_id) {

			// Editing a post
			$post = Jelly::select('forum_post')->load($post_id);
			if (!$post->loaded() || $post->topic->id != $topic->id) {
				throw new Model_Exception($post, $post_id);
			}
			Permission::required($post, Model_Forum_Post::PERMISSION_UPDATE, self::$user);

		} else {

			// New reply
			$post = Jelly::factory('forum_post');

		}

		// Quoting a post
		if ($quote_id) {
			$quote = Jelly::select('forum_post')->load($quote_id);
			if (!$quote->loaded() || $quote->topic->id != $topic->id) {
				throw new Model_Exception($quote, $quote_id);
			}
			Permission::required($quote, Model_Forum_Post::PERMISSION_READ, self::$user);

			if (!$post->loaded()) {
				$post->post = '[quote author="' . $quote->author_name . '" post="' . $quote->id . '"]' . $quote->post . "[/quote]\n\n";
			}
		}

		$this->_set_title($topic);

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {

			$post->post        = $_POST['post'];
			$post->author_ip   = Request::$client_ip;
			$post->author_host = Request::host_name();
			if (!$post->loaded()) {

				// New post
				$post->topic       = $topic;
				$post->area        = $topic->area;
				$post->author      = self::$user;
				$post->author_name = self::$user->username;
				if ($quote_id) {
					$post->parent = $quote;
				}
				$increase = true;

			} else {

				// Old post
				$post->modifies++;
				$post->modified = time();
				$increase = false;

			}

			try {
				$post->save();
				if ($increase) {

					// Quote
					if ($quote_id && $quote->author->id) {
						Jelly::factory('forum_quote')
							->set(array(
								'user'   => $quote->author,
								'author' => self::$user,
								'topic'  => $topic,
								'post'   => $post))
							->save();
					}

					// Topic
					$topic->post_count++;
					$topic->last_post   = $post;
					$topic->last_posted = $post->created;
					$topic->last_poster = $post->author_name;
					$topic->save();

					// Area
					$area = $topic->area;
					$area->post_count++;
					$area->last_topic = $topic;
					$area->save();

					// User
					self::$user->post_count++;
					self::$user->save();

					// News feed
					NewsfeedItem_Forum::reply(self::$user, $post);

				}

				if ($this->ajax) {
					return Request::factory(Route::get('forum_post')->uri(array('topic_id' => Route::model_id($topic), 'id' => $post->id)))->execute();
				}

				$this->request->redirect(Route::model($topic, '?page=last#last'));
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}

		// Build form
		$form = array(
			'values' => $post,
			'errors' => $errors,
			'save'   => array('label' => $quote_id ? __('Reply') : __('Save')),
			'cancel' => $this->ajax
				? Route::get('forum_post')->uri(array('topic_id' => Route::model_id($topic), 'id' => $quote_id ? $quote->id : $post->id))
				: Request::back(Route::model($topic), true),
			'groups' => array(
				array(
					'fields' => array(
						'post' => array('label' => null),
					),
				),
			)
		);

		if ($this->ajax) {
			if ($quote_id) {

				// Quote
				$form['attributes'] = array('id' => 'quote');

				echo View_Module::factory('forum/reply', array(
					'mod_id'    => 'quote',
					'mod_class' => 'quote first',
					'form'      => $form,
					'user'      => self::$user,
				));

			} else {

				// Edit post
				echo View::factory('form/anqh', array('form' => $form));

			}
			return;
		}

		Widget::add('main', View_Module::factory('forum/reply', array(
			'mod_id' => 'reply',
			'form'   => $form,
			'user'   => self::$user,
		)));

		//Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}


	/**
	 * Edit forum topic
	 *
	 * @param  integer  $area_id
	 * @param  integer  $topic_id
	 */
	protected function _edit_topic($area_id = null, $topic_id = null) {
		$this->history = false;

		if ($area_id) {

			// Start new topic
			$area = Jelly::select('forum_area')->load($area_id);
			if (!$area->loaded()) {
				throw new Model_Exception($area, $area_id);
			}
			Permission::required($area, Model_Forum_Area::PERMISSION_POST, self::$user);
			$this->page_title = HTML::chars($area->name);
			$topic = Jelly::factory('forum_topic');
			$post  = Jelly::factory('forum_post');
			$cancel = Route::model($area);

			// Build form
			$form = array(
				'values' => $topic,
				'save'   => array(
					'attributes' => array('tabindex' => 3)
				),
				'cancel' => $cancel,
				'groups' => array(
					array(
						'fields' => array(
							'name' => array(
								'attributes' => array('tabindex' => 1)
							),
							'post' => array(
								'attributes' => array('tabindex' => 2),
								'model' => $post
							),
						),
					),
				)
			);

		} else {

			// Edit old topic
			$topic = Jelly::select('forum_topic')->load($topic_id);
			if (!$topic->loaded()) {
				throw new Model_Exception($topic, $topic_id);
			}
			Permission::required($topic, Model_Forum_Topic::PERMISSION_UPDATE, self::$user);
			$this->_set_title($topic);
			$cancel = Route::model($topic);

			// Set actions
			if (Permission::has($topic, Model_Forum_Topic::PERMISSION_DELETE, self::$user)) {
				$this->page_actions[] = array('link' => Route::model($topic, 'delete'), 'text' => __('Delete topic'), 'class' => 'topic-delete');
			}

			// Build form
			$form = array(
				'values' => $topic,
				'cancel' => $cancel,
				'groups' => array(
					array(
						'fields' => array(
							'name'   => array(),
							'status' => array(),
						),
					),
				),
			);
		}

		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			if (isset($post)) {

				// New topic
				$post->post        = $_POST['post'];
				$post->area        = $area;
				$post->author      = self::$user;
				$post->author_name = self::$user->username;
				$post->author_ip   = Request::$client_ip;
				$post->author_host = Request::host_name();
				try {
					$post->validate();
				} catch (Validate_Exception $e) {
					$errors += $e->array->errors('validate');
				}

				$topic->name = $_POST['name'];
				$topic->area = $area;
				try {
					$topic->validate();
				} catch (Validate_Exception $e) {
					$errors += $e->array->errors('validate');
				}

				// If no errors found, save models
				if (empty($errors)) {
					$topic->save();

					// Post
					$post->topic = $topic;
					$post->save();

					// Topic
					$topic->first_post  = $topic->last_post   = $post;
					$topic->last_poster = self::$user->username;
					$topic->last_posted = time();
					$topic->post_count   = 1;
					$topic->save();

					// Area
					$area->last_topic = $topic;
					$area->post_count++;
					$area->topic_count++;
					$area->save();

					// User
					self::$user->post_count++;
					self::$user->save();

					// News feed
					NewsfeedItem_Forum::topic(self::$user, $topic);

					$this->request->redirect(Route::model($topic));
				}

			} else {

				// Old topic
				$topic->set(Arr::intersect($_POST, array('name', 'status')));
				try {
					$topic->save();
					$this->request->redirect(Route::model($topic));
				} catch (Validate_Exception $e) {
					$errors = $e->array->errors('validate');
				}

			}
		}
		$form['errors'] = $errors;

		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}


	/**
	 * Set page title
	 *
	 * @param  Model_Forum_Topic  $topic
	 */
	protected function _set_title(Model_Forum_Topic $topic = null) {
		switch ($topic->status) {
			case Model_Forum_Topic::STATUS_LOCKED: $prefix = '<span class="locked">' . __('[Locked]') . '</span> '; break;
			case Model_Forum_Topic::STATUS_SINK:   $prefix = '<span class="sink">' . __('[Sink]') . '</span> '; break;
			default: $prefix = '';
		}
		$this->page_title = $prefix . HTML::chars($topic->name());
		$this->page_subtitle  = HTML::icon_value(array(':views' => $topic->read_count), ':views view', ':views views', 'views');
		$this->page_subtitle .= HTML::icon_value(array(':replies' => $topic->post_count - 1), ':replies reply', ':replies replies', 'posts');
		$this->page_subtitle .= ' | ' . HTML::anchor(
			Route::model($topic->area),
			__('Back to :area ', array(':area' => HTML::chars($topic->area->name))),
			array('title' => strip_tags($topic->area->description))
		);
	}

}

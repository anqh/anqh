<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Forum Topic controller
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
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

		$event = Model_Event::find($event_id);
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
		$topic = $this->private ? Model_Forum_Private_Topic::find($topic_id) : Model_Forum_Topic::find($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}
		Permission::required($topic, Model_Forum_Topic::PERMISSION_READ, self::$user);

		// Did we request single post with ajax?
		if ($this->ajax && isset($post_id)) {
			$this->history = false;
			$post = $this->private ? Model_Forum_Private_Post::find($post_id) : Model_Forum_Post::find($post_id);
			if (!$post->loaded()) {
				throw new Model_Exception($topic, $topic_id);
			}

			// Permission is already checked by the topic, no need to check for post

			echo View::factory('forum/post', array(
				'topic'   => $topic,
				'post'    => $post,
				'number'  => $topic->get_post_number($post->id) + 1,
				'user'    => self::$user,
				'private' => $this->private
			));
			return;
		}

		// Add new tab
		if ($this->private) {
			$this->tab_id = 'private';
		} else {
			$this->tab_id = 'area';
			$this->tabs['area'] = array('url' => Route::model($topic->area), 'text' => __('Area'));
		}

		// Set title
		$this->_set_title($topic);

		// Update counts
		if (!self::$user || $topic->author->id != self::$user->id) {
			$topic->read_count++;
			$topic->save();
		}

		// Public topic extras
		if (!$this->private) {

			// Quotes are supported only in public forum as we get notifications anyway in private
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

		} // Public topic extras

		// Set actions
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_POST, self::$user)) {
			$this->page_actions[] = array('link' => '#reply', 'text' => __('Reply to topic'), 'class' => 'topic-post');
		}
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($topic, 'edit'), 'text' => __('Edit topic'), 'class' => 'topic-edit');
		}

		// Pagination
		$pagination = Pagination::factory(array(
			'url'            => Route::model($topic),
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

		// Recipients
		if ($this->private) {
			Widget::add('main', View_Module::factory('generic/users', array(
				'mod_title' => __('Recipients'),
				'users'     => $topic->find_recipients(),
				'viewer'    => self::$user
			)));
		}


		// Posts
		Widget::add('main', View_Module::factory('forum/topic', array(
			'mod_class'  => 'topic articles topic-' . $topic->id,
			'user'       => self::$user,
			'topic'      => $topic,
			'posts'      => $topic->find_posts($pagination),
			'first'      => $pagination->current_first_item,
			'pagination' => $pagination,
			'private'    => $this->private,
		)));

		// Reply
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_POST, self::$user)) {
			$form = array(
				'action' => Route::model($topic, 'reply'),
				'values' => $this->private ? Model_Forum_Private_Post::factory() : Model_Forum_Post::factory(),
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
				'mod_id'  => 'reply',
				'user'    => self::$user,
				'form'    => $form,
				'private' => $this->private
			)));
		}

		//$this->side_views();
	}


	/**
	 * Action: post new topic
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
		$topic = $this->private ? Model_Forum_Private_Topic::find($topic_id) : Model_Forum_Topic::find($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}

		// Editing a post
		$post = $this->private ? Model_Forum_Private_Post::find($post_id) : Model_Forum_Post::find($post_id);
		if (!$post->loaded() || $post->topic->id != $topic->id || !Security::csrf_valid()) {
			throw new Model_Exception($post, $post_id);
		}
		Permission::required($post, Model_Forum_Post::PERMISSION_DELETE, self::$user);

		$post->delete();
		$topic->refresh();
		if (!$this->private) {
			$topic->area->post_count--;
			$topic->area->save();
		}

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
		$topic = $this->private ? Model_Forum_Private_Topic::find($topic_id) : Model_Forum_Topic::find($topic_id);
		if (!$topic->loaded() || !Security::csrf_valid()) {
			throw new Model_Exception($topic, $topic_id);
		}

		Permission::required($topic, Model_Forum_Topic::PERMISSION_DELETE, self::$user);

		$posts = $topic->post_count;
		$area  = $topic->area;

		// Update area only for public forum
		if (!$this->private) {
			$area->post_count -= $posts;
			$area->topic_count--;
			$area->save();
		}

		$topic->delete();

		$this->request->redirect($this->private ? Route::get('forum_area')->uri(array('id' => 'private', 'action' => '')) : Route::model($area));
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
		$topic = $this->private ? Model_Forum_Private_Topic::find($topic_id) : Model_Forum_Topic::find($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}
		Permission::required($topic, Model_Forum_Topic::PERMISSION_POST, self::$user);

		if ($post_id) {

			// Editing a post
			$post = $this->private ? Model_Forum_Private_Post::find($post_id) : Model_Forum_Post::find($post_id);
			if (!$post->loaded() || $post->topic->id != $topic->id) {
				throw new Model_Exception($post, $post_id);
			}
			Permission::required($post, Model_Forum_Post::PERMISSION_UPDATE, self::$user);

		} else {

			// New reply
			$post = $this->private ? Model_Forum_Private_Post::factory() : Model_Forum_Post::factory();

		}

		// Quoting a post
		if ($quote_id) {
			$quote = $this->private ? Model_Forum_Private_Post::factory() : Model_Forum_Post::find($quote_id);
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

				// Notify recipients
				if ($this->private) {
					$topic->notify_recipients(self::$user);
				}

			} else {

				// Old post
				$post->modifies++;
				$post->modified = time();
				$increase = false;

			}

			try {
				$post->save();
				if ($increase) {

					// Quote, only for public topics
					if (!$this->private && $quote_id && $quote->author->id) {
						Model_Forum_Quote::factory()
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

					// Area, only for public topics
					if (!$this->private) {
						$area = $topic->area;
						$area->post_count++;
						$area->last_topic = $topic;
						$area->save();
					}

					// User
					self::$user->post_count++;
					self::$user->save();

					// News feed
					if (!$this->private) {
						NewsfeedItem_Forum::reply(self::$user, $post);
					}

				}

				if ($this->ajax) {
					return Request::factory(
						Route::get($this->private ? 'forum_private_post' : 'forum_post')
							->uri(array('topic_id' => Route::model_id($topic), 'id' => $post->id))
					)->execute();
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
				? Route::get($this->private ? 'forum_private_post' : 'forum_post')
						->uri(array('topic_id' => Route::model_id($topic), 'id' => $quote_id ? $quote->id : $post->id))
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
					'private'   => $this->private,
				));

			} else {

				// Edit post
				echo View::factory('form/anqh', array('form' => $form));

			}
			return;
		}

		Widget::add('main', View_Module::factory('forum/reply', array(
			'mod_id'    => 'reply',
			'form'      => $form,
			'user'      => self::$user,
			'private'   => $this->private,
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

		if ($area_id && !$topic_id) {

			// Start new topic
			/** @var  Model_Forum_Private_Area|Model_Forum_Area  $area */
			$area = $this->private ? Model_Forum_Private_Area::find($area_id) : Model_Forum_Area::find($area_id);
			if (!$area->loaded()) {
				throw new Model_Exception($area, $area_id);
			}
			Permission::required($area, Model_Forum_Area::PERMISSION_POST, self::$user);

			$this->page_title = HTML::chars($area->name);
			if ($this->private) {

				/** @var  Model_Forum_Private_Topic  $topic */
				$topic  = Model_Forum_Private_Topic::factory();
				/** @var  Model_Forum_Private_Post  $post */
				$post   = Model_Forum_Private_Post::factory();
				$recipients = array();
				$cancel = Route::get('forum_area')->uri(array('id' => 'private', 'action' => ''));

			} else {

				/** @var  Model_Forum_Topic  $topic */
				$topic  = Model_Forum_Topic::factory();
				/** @var  Model_Forum_Post  $topic */
				$post   = Model_Forum_Post::factory();
				$cancel = Route::model($area);

			}

		} else if ($topic_id) {

			// Edit old topic
			/** @var  Model_Forum_Private_Topic|Model_Forum_Topic  $topic */
			$topic = $this->private ? Model_Forum_Private_Topic::find($topic_id) : Model_Forum_Topic::find($topic_id);
			if (!$topic->loaded()) {
				throw new Model_Exception($topic, $topic_id);
			}
			Permission::required($topic, Model_Forum_Topic::PERMISSION_UPDATE, self::$user);

			// Build recipients list
			if ($this->private) {
				$recipients = $topic->find_recipient_names();
			}

			$this->_set_title($topic);
			$cancel = Route::model($topic);

			// Set actions
			if (Permission::has($topic, Model_Forum_Topic::PERMISSION_DELETE, self::$user)) {
				$this->page_actions[] = array('link' => Route::model($topic, 'delete') . '?token=' .Security::csrf(), 'text' => __('Delete topic'), 'class' => 'topic-delete');
			}

		} else {

			throw new InvalidArgumentException('Topic and area missing');

		}

		$errors = array();
		if ($_POST && Security::csrf_valid()) {

			// Get recipients
			if ($this->private) {
				$post_recipients = array();
				foreach (explode(',', Arr::get_once($_POST, 'recipients')) as $recipient) {
					if ($user = Model_User::find_user_light(trim($recipient))) {
						$post_recipients[$user['id']] = $user['username'];
					}
				}

				// Make sure author is included
				$post_recipients[self::$user->id] = self::$user->username;
			}

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

				$topic->author      = self::$user;
				$topic->author_name = self::$user->username;
				$topic->name        = $_POST['name'];
				$topic->area        = $area;
				try {
					$topic->validate();
				} catch (Validate_Exception $e) {
					$errors += $e->array->errors('validate');
				}

				// If no errors found, save models
				if (empty($errors)) {
					$topic->save();

					// Recipients
					if ($this->private) {
						$topic->set_recipients($post_recipients);
					}

					// Post
					$post->topic = $topic;
					$post->save();

					// Topic
					$topic->first_post  = $topic->last_post   = $post;
					$topic->last_poster = self::$user->username;
					$topic->last_posted = time();
					$topic->post_count   = 1;
					$topic->save();

					// Area, only public forums
					if (!$this->private) {
						$area->last_topic = $topic;
						$area->post_count++;
						$area->topic_count++;
						$area->save();
					}

					// User
					self::$user->post_count++;
					self::$user->save();

					// News feed
					if (!$this->private) {
						NewsfeedItem_Forum::topic(self::$user, $topic);
					}

					$this->request->redirect(Route::model($topic));
				}

				isset($post_recipients) and $recipients = $post_recipients;

			} else {

				// Old topic
				$topic->set(Arr::intersect($_POST, array('name', 'status')));
				try {
					$topic->save();

					// Recipients
					if ($this->private) {
						$topic->set_recipients($post_recipients);
					}

					$this->request->redirect(Route::model($topic));
				} catch (Validate_Exception $e) {
					$errors = $e->array->errors('validate');
				}

			}
		}
		$form['errors'] = $errors;

		Widget::add('main', View_Module::factory('forum/edit_topic', array(
			'private'    => $this->private,
			'topic'      => $topic,
			'recipients' => isset($recipients) ? implode(', ', $recipients) : null,
			'post'       => isset($post) ? $post : null,
			'errors'     => $errors,
			'cancel'     => $cancel,
			'user'       => self::$user,
		)));

		//Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
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

		if ($this->private) {
			$this->page_subtitle .= ' | ' . HTML::anchor(
				Forum::private_messages_url(),
				__('Back to :area', array(':area' => __('Private messages'))),
				array('title' => strip_tags($topic->area->description))
			);
		} else {
			$this->page_subtitle .= ' | ' . HTML::anchor(
				Route::model($topic->area),
				__('Back to :area', array(':area' => HTML::chars($topic->area->name))),
				array('title' => strip_tags($topic->area->description))
			);
		}
	}

}

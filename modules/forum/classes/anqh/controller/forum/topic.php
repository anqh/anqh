<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Forum Topic controller
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Forum_Topic extends Controller_Forum {

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

		$this->_delete_topic($id);
	}


	/**
	 * Action: edit
	 */
	public function action_edit() {
		$id = (int)$this->request->param('id');

		// Are we editing a post, if so we have a topic_id
		$topic_id = (int)$this->request->param('topic_id');
		if ($topic_id) {
			$this->_edit_post($topic_id, $id);

			return;
		}

		$this->_edit_topic(null, $id);
	}


	/**
	 * Action: event
	 */
	public function action_event() {
		$event_id = (int)$this->request->param('id');

		$event = Model_Event::factory($event_id);
		if (!$event->loaded()) {
			throw new Model_Exception($event, $event_id);
		}

		// Go to before or after event discussion?
		$time = $this->request->param('time');
		if (!$time) {
			$time = $event->stamp_begin > time() ? 'before' : 'after';
		}
		$bind = ($time == 'before') ? 'events_upcoming' : 'events_past';

		// Redirect
		if ($topic = Model_Forum_Topic::factory()->find_by_bind($event, $bind)) {

			// Topic existing
			$this->request->redirect(Route::model($topic));

		} else {

			// @todo Create new topic
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
		/** @var  Model_Forum_Private_Topic|Model_Forum_Topic  $topic */
		$topic = $this->private ? Model_Forum_Private_Topic::factory($topic_id) : Model_Forum_Topic::factory($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}
		Permission::required($topic, Model_Forum_Topic::PERMISSION_READ, self::$user);

		// Did we request single post with ajax?
		if (($this->ajax || $this->internal) && isset($post_id)) {
			$this->history = false;
			$post = $this->private ? Model_Forum_Private_Post::factory($post_id) : Model_Forum_Post::factory($post_id);
			if (!$post->loaded()) {
				throw new Model_Exception($topic, $topic_id);
			}

			// Permission is already checked by the topic, no need to check for post

			$this->response->body($this->section_post($topic, $post));

			return;
		}

		// Update counts
		if ($this->private) {
			$topic->mark_as_read(self::$user);
		}
		if (!self::$user || $topic->author_id != self::$user->id) {
			$topic->read_count++;
			$topic->save();
		}


		// Build page
		$this->view             = new View_Page();
		$this->view->title_html = Forum::topic($topic);
		$this->view->subtitle   = __($topic->post_count == 1 ? ':posts post' : ':posts posts', array(':posts' => Num::format($topic->post_count, 0)));
		$this->view->tab        = 'topic';

		$this->page_actions['topic'] = array(
			'link' => Route::model($topic),
			'text' => '<i class="icon-comment icon-white"></i> ' . __('Topic'),
		);

		// Public topic extras
		if (!$this->private) {

			// Quotes are supported only in public forum as we get notifications anyway in private
			if (self::$user) {
				$quotes = Model_Forum_Quote::factory()->find_by_user(self::$user);
				if (count($quotes)) {
					foreach ($quotes as $quote) {
						if ($topic->id == $quote->forum_topic_id) {
							$quote->delete();
							break;
						}
					}
				}
			}

			// Facebook
			Anqh::page_meta('title', $topic->name);
			Anqh::page_meta('url', URL::site(Route::url('forum_topic', array('id' => $topic->id, 'action' => '')), true));
			Anqh::share(true);

			// Model binding
			$area = $topic->area();
			if ($area->type == Model_Forum_Area::TYPE_BIND && $topic->bind_id) {
				if ($bind = Model_Forum_Area::get_binds($area->bind)) {
					$model = AutoModeler::factory($bind['model'], $topic->bind_id);
					if ($model->loaded()) {

						// Set actions
						$this->page_actions[] = array('link' => Route::model($model), 'text' => $bind['link']);

						// Set views
						foreach ((array)$bind['view'] as $view) {
							$this->view->add(View_Page::COLUMN_SIDE, View_Module::factory($view, array(
								$bind['model'] => $model,
							)), Widget::TOP);
						}

					}
				}
			}

		} // Public topic extras

		// Set actions
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_POST, self::$user)) {
			$this->view->actions[] = array(
				'link'  => Request::current_uri() . '#reply',
				'text'  => '<i class="icon-comment icon-white"></i> ' . __('Reply to topic'),
				'class' => 'btn btn-primary topic-post'
			);
		}
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_UPDATE, self::$user)) {
			$this->view->actions[] = array(
				'link'  => Route::model($topic, 'edit'),
				'text'  => '<i class="icon-edit icon-white"></i> ' . __('Edit topic'),
			);
		}

		// Breadcrumbs
		$this->page_breadcrumbs[] = HTML::anchor(Route::url('forum_group'), __('Forum'));
		$this->page_breadcrumbs[] = HTML::anchor(Route::model($topic->area()), $topic->area()->name);


		// Pagination
		$this->view->add(View_Page::COLUMN_MAIN, $pagination = $this->section_pagination($topic));
		$this->view->subtitle .= ', ' . __($pagination->total_pages == 1 ? ':pages page' : ':pages pages', array(':pages' => Num::format($pagination->total_pages, 0)));
		$this->view->subtitle .= ', ' . __($topic->read_count == 1 ? ':views view' : ':views views', array(':views' => Num::format($topic->read_count, 0)));

		// Go to post?
		if (isset($post_id)) {
			$pagination->item($topic->get_post_number($post_id) + 1);

			// We need to set pagination urls manually if jumped to a post
			$pagination->base_url = Route::model($topic);

		}

		// Recipients
		if ($this->private) {
			$this->view->add(View_Page::COLUMN_MAIN, $this->section_recipients($topic));
			$this->view->add(View_Page::COLUMN_MAIN, '<hr />');
		}

		// Posts
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_topic($topic, $pagination));

		// Reply
		if (Permission::has($topic, Model_Forum_Topic::PERMISSION_POST, self::$user)) {

			// Old post warning
			if ($topic->last_posted && time() - $topic->last_posted > Date::YEAR) {
				$this->view->add(View_Page::COLUMN_MAIN, $this->section_ancient_warning($topic->last_posted));
			}

			$section = $this->section_post_edit(View_Forum_PostEdit::REPLY, $this->private ? Model_Forum_Private_Post::factory() : Model_Forum_Post::factory());
			$section->forum_topic = $topic;

			$this->view->add(View_Page::COLUMN_MAIN, $section);
		}

		// Pagination
		$this->view->add(View_Page::COLUMN_MAIN, $pagination);

		$this->_side_views();
	}


	/**
	 * Action: post new topic
	 */
	public function action_post() {
		$this->_edit_topic((int)$this->request->param('id'));
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
	 *
	 * @throws  Model_Exception
	 */
	protected function _delete_post($topic_id, $post_id) {
		$this->history = false;

		// Topic is always loaded, avoid haxing attempts to edit posts from wrong topics
		$topic = $this->private ? Model_Forum_Private_Topic::factory($topic_id) : Model_Forum_Topic::factory($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}

		// Editing a post
		$post = $this->private ? Model_Forum_Private_Post::factory($post_id) : Model_Forum_Post::factory($post_id);
		if (!$post->loaded() || $post->forum_topic_id != $topic->id || !Security::csrf_valid()) {
			throw new Model_Exception($post, $post_id);
		}
		Permission::required($post, Model_Forum_Post::PERMISSION_DELETE, self::$user);

		$post->delete();
		$topic->refresh();
		if (!$this->private) {
			$area = $topic->area();
			$area->post_count--;
			$area->save();
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
		$topic = $this->private ? Model_Forum_Private_Topic::factory($topic_id) : Model_Forum_Topic::factory($topic_id);
		if (!$topic->loaded() || !Security::csrf_valid()) {
			throw new Model_Exception($topic, $topic_id);
		}

		Permission::required($topic, Model_Forum_Topic::PERMISSION_DELETE, self::$user);

		$area  = $topic->area();

		// Update area only for public forum
		if (!$this->private) {
			$area->post_count -= $topic->post_count;
			$area->topic_count--;
			$area->save();
		}

		$topic->delete();

		$this->request->redirect($this->private ? Route::get('forum_area')->uri(array('id' => 'private', 'action' => '')) : Route::model($area));
	}


	/**
	 * Edit forum post
	 *
	 * @param  integer  $topic_id  When replying to a topic
	 * @param  integer  $post_id   When editing a post
	 * @param  integer  $quote_id  When quoting a post
	 *
	 * @throws  Model_Exception  missing topic, missing post
	 */
	protected function _edit_post($topic_id, $post_id = null, $quote_id = null) {
		$this->history = false;

		// Topic is always loaded, avoid haxing attempts to edit posts from wrong topics
		$topic = $this->private ? Model_Forum_Private_Topic::factory($topic_id) : Model_Forum_Topic::factory($topic_id);
		if (!$topic->loaded()) {
			throw new Model_Exception($topic, $topic_id);
		}
		Permission::required($topic, Model_Forum_Topic::PERMISSION_POST, self::$user);

		if ($post_id) {

			// Editing a post
			$post = $this->private ? Model_Forum_Private_Post::factory($post_id) : Model_Forum_Post::factory($post_id);
			if (!$post->loaded() || $post->forum_topic_id != $topic->id) {
				throw new Model_Exception($post, $post_id);
			}
			Permission::required($post, Model_Forum_Post::PERMISSION_UPDATE, self::$user);

		} else {

			// New reply
			$post = $this->private ? Model_Forum_Private_Post::factory() : Model_Forum_Post::factory();

		}

		// Quoting a post
		if ($quote_id) {
			$quote = $this->private ? Model_Forum_Private_Post::factory() : Model_Forum_Post::factory($quote_id);
			if (!$quote->loaded() || $quote->forum_topic_id != $topic->id) {
				throw new Model_Exception($quote, $quote_id);
			}
			Permission::required($quote, Model_Forum_Post::PERMISSION_READ, self::$user);

			if (!$post->loaded()) {
				$post->post = '[quote author="' . $quote->author_name . '" post="' . $quote->id . '"]' . $quote->post . "[/quote]\n\n";
			}
			$post->parent_id = $quote_id;
		}

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {

			$post->post        = Arr::get($_POST, 'post');
			$post->author_ip   = Request::$client_ip;
			$post->author_host = Request::host_name();
			if (!$post->loaded()) {

				// New post
				$post->forum_topic_id = $topic->id;
				$post->forum_area_id  = $topic->forum_area_id;
				$post->author_id      = self::$user->id;
				$post->author_name    = self::$user->username;
				$post->created        = time();
				$increase = true;

			} else {

				// Old post
				$post->modify_count++;
				$post->modified = time();
				$increase = false;

			}

			// Preview
			if (isset($_POST['preview'])) {
				if ($this->ajax) {
					$this->response->body($this->section_post($topic, $post));
				}

				return;
			}

			// Save
			try {
				$post->save();

				if ($increase) {

					// Quote, only for public topics
					if (!$this->private && $quote_id && $quote->author_id) {
						$quoted = $quote->author_id;
						$quote = new Model_Forum_Quote();
						$quote->user_id        = $quoted;
						$quote->author_id      = self::$user->id;
						$quote->forum_topic_id = $topic->id;
						$quote->forum_post_id  = $post->id;
						$quote->created        = time();
						$quote->save();
					}

					// Notify recipients
					if ($this->private) {
						$topic->notify_recipients(self::$user);
					}

					// Topic
					$topic->post_count++;
					$topic->last_post_id = $post->id;
					$topic->last_poster  = $post->author_name;

					// If current topic is set to sink, don't update last posted date
					if ($topic->status != Model_Forum_Topic::STATUS_SINK) {
						$topic->last_posted = $post->created;
					}

					$topic->save();

					// Area, only for public topics
					if (!$this->private) {
						$area = $topic->area();
						$area->post_count++;
						$area->last_topic_id = $topic->id;
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
					$post_route = Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
						'topic_id' => Route::model_id($topic),
						'id'       => $post->id,
					));
					$post_response = Request::factory($post_route)->execute();
					$this->response->body($post_response->body());

					return;
				}

				$this->request->redirect(Route::model($topic, '?page=last#last'));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}

		// Common attributes
		if ($quote_id) {
			$mode = View_Forum_PostEdit::QUOTE;
		} else if ($post_id) {
			$mode = View_Forum_PostEdit::EDIT_POST;
		} else {
			$mode = View_Forum_PostEdit::REPLY;
		}
		$section = $this->section_post_edit($mode, $post);
		$section->forum_topic = $topic;
		$section->errors      = $errors;
		$section->cancel      = $this->ajax
			? Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
					'topic_id' => Route::model_id($topic),
					'id'       => $quote_id ? $quote_id : $post->id,
				))
			: Request::back(Route::model($topic), true);

		if ($this->ajax) {
			$this->response->body($mode == View_Forum_PostEdit::EDIT_POST ? $section->content() : $section);

			return;
		}


		// Build page
		$this->view = new View_Page();
		$this->view->title_html = Forum::topic($topic);

		$this->view->add(View_Page::COLUMN_MAIN, $section);

	}


	/**
	 * Edit forum topic
	 *
	 * @param  integer  $area_id
	 * @param  integer  $topic_id
	 *
	 * @throws  Model_Exception           invalid area, invalid topic
	 * @throws  InvalidArgumentException  missing area and topic
	 */
	protected function _edit_topic($area_id = null, $topic_id = null) {
		$this->history = false;

		$this->view = new View_Page();

		if ($area_id && !$topic_id) {

			// Start new topic
			$mode = View_Forum_PostEdit::NEW_TOPIC;

			/** @var  Model_Forum_Private_Area|Model_Forum_Area  $area */
			$area = $this->private ? Model_Forum_Private_Area::factory($area_id) : Model_Forum_Area::factory($area_id);
			if (!$area->loaded()) {
				throw new Model_Exception($area, $area_id);
			}
			Permission::required($area, Model_Forum_Area::PERMISSION_POST, self::$user);

			$this->view->title = HTML::chars($area->name);
			if ($this->private) {

				// Private topic
				$topic  = new Model_Forum_Private_Topic();
				$post   = new Model_Forum_Private_Post();
				$cancel = Route::url('forum_area', array('id' => 'private', 'action' => ''));
				$recipients = array();

			} else {

				// Public topic
				$topic  = new Model_Forum_Topic();
				$post   = new Model_Forum_Post();
				$cancel = Route::model($area);

			}
			$topic->forum_area_id = $area->id;
			$post->forum_area_id  = $area->id;

		} else if ($topic_id) {

			// Edit old topic
			$mode = View_Forum_PostEdit::EDIT_TOPIC;

			/** @var  Model_Forum_Private_Topic|Model_Forum_Topic  $topic */
			$topic = $this->private ? Model_Forum_Private_Topic::factory($topic_id) : Model_Forum_Topic::factory($topic_id);
			if (!$topic->loaded()) {
				throw new Model_Exception($topic, $topic_id);
			}
			Permission::required($topic, Model_Forum_Topic::PERMISSION_UPDATE, self::$user);

			// Build recipients list
			if ($this->private) {
				$recipients = $topic->find_recipient_names();
			}

			$this->view->title_html = Forum::topic($topic);
			$cancel = Route::model($topic);

			// Set actions
			if (Permission::has($topic, Model_Forum_Topic::PERMISSION_DELETE, self::$user)) {
				$this->view->actions[] = array(
					'link'  => Route::model($topic, 'delete') . '?' . Security::csrf_query(),
					'text'  => '<i class="icon-trash icon-white"></i> ' . __('Delete topic'),
					'class' => 'btn btn-danger topic-delete');
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
				$post->post          = $_POST['post'];
				$post->author_id     = self::$user->id;
				$post->author_name   = self::$user->username;
				$post->author_ip     = Request::$client_ip;
				$post->author_host   = Request::host_name();
				$post->created       = time();
				try {
					$post->is_valid();
				} catch (Validation_Exception $e) {
					$errors += $e->array->errors('validate');
				}

				$topic->author_id     = self::$user->id;
				$topic->author_name   = self::$user->username;
				$topic->name          = $_POST['name'];
				$topic->created       = time();
				try {
					$topic->is_valid();
				} catch (Validation_Exception $e) {
					$errors += $e->array->errors('validate');
				}

				// Preview
				if (isset($_POST['preview'])) {
					if ($this->ajax) {
						$this->response->body($this->section_post($topic, $post));
					}

					return;
				}

				// If no errors found, save models
				if (empty($errors)) {
					$topic->save();

					// Recipients
					if ($this->private) {
						$topic->set_recipients($post_recipients);
					}

					// Post
					$post->forum_topic_id = $topic->id;
					$post->save();

					// Topic
					$topic->first_post_id = $topic->last_post_id = $post->id;
					$topic->last_poster   = self::$user->username;
					$topic->last_posted   = time();
					$topic->post_count    = 1;
					$topic->save();

					// Area, only public forums
					if (!$this->private) {
						$area->last_topic_id = $topic->id;
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
				$new_area_id = false;
				$topic->name = Arr::get($_POST, 'name');
				if (self::$user->has_role(array('admin', 'moderator', 'forum moderator'))) {
					$topic->set_fields(Arr::intersect($_POST, array('status', 'sticky')));

					// Change area?
					$new_area_id = Arr::get($_POST, 'forum_area_id');
					if ($new_area_id && $new_area_id != $topic->forum_area_id) {
						$old_area_id          = $topic->forum_area_id;
						$topic->forum_area_id = $new_area_id;
					}

				}
				try {
					$topic->save();

					// Recipients
					if ($this->private) {
						$topic->set_recipients($post_recipients);
					}

					// Area change
					if (isset($old_area_id)) {
						if ($this->private) {
							Model_Forum_Private_Area::factory($old_area_id)->refresh();
							Model_Forum_Private_Area::factory($new_area_id)->refresh();
						} else {
							Model_Forum_Area::factory($old_area_id)->refresh();
							Model_Forum_Area::factory($new_area_id)->refresh();
						}
					}

					$this->request->redirect(Route::model($topic));
				} catch (Validation_Exception $e) {
					$errors = $e->array->errors('validate');
				}

			}
		}
		$form['errors'] = $errors;

		$section = $this->section_post_edit($mode, isset($post) ? $post : null);
		$section->forum_topic = $topic;
		$section->errors      = $errors;
		$section->cancel      = $cancel;
		$section->recipients  = isset($recipients) ? implode(', ', $recipients) : null;

		$this->view->add(View_Page::COLUMN_MAIN, $section);
	}


	/**
	 * Get replying to old post warning.
	 *
	 * @param   integer  $previous
	 * @return  string
	 */
	public function section_ancient_warning($previous) {
		ob_start();

?>

<div class="offset1 post-old">
	<span class="label label-warning">&iexcl; <?= __('You are replying to a dead topic.') ?> <?= __('Previous post :ago', array(':ago' => Date::fuzzy_span($previous))) ?> !</span>
</div>

<?php

		return ob_get_clean();
	}


	/**
	 * Get pagination view.
	 *
	 * @param   Model_Forum_Topic  $topic
	 * @return  View_Generic_Pagination
	 */
	public function section_pagination(Model_Forum_Topic $topic) {
		return new View_Generic_Pagination(array(
			'base_url'       => Route::model($topic),
			'items_per_page' => Kohana::$config->load('forum.posts_per_page'),
			'total_items'    => max(1, $topic->post_count),
		));
	}


	/**
	 * Get post view.
	 *
	 * @param  Model_Forum_Topic  $topic
	 * @param  Model_Forum_Post   $post
	 */
	public function section_post(Model_Forum_Topic $topic, Model_Forum_Post $post) {
		$section = new View_Forum_Post($post, $topic);
		$section->nth     = $topic->get_post_number($post->id) + 1;
		$section->private = $this->private;

		return $section;
	}


	/**
	 * Get post edit view.
	 *
	 * @param   string            $mode
	 * @param   Model_Forum_Post  $forum_post
	 * @return  View_Forum_PostEdit
	 */
	public function section_post_edit($mode, Model_Forum_Post $forum_post = null) {
		$section = new View_Forum_PostEdit($mode, $forum_post);
		$section->private = $this->private;
		if ($mode === View_Forum_PostEdit::REPLY) {
			$section->id = 'reply';
		}

		return $section;
	}


	/**
	 * Get private topic recipients.
	 *
	 * @param   Model_Forum_Private_Topic  $topic
	 * @return  View_Users_List
	 */
	public function section_recipients(Model_Forum_Private_Topic $topic) {
		$section = new View_Users_List($recipients = $topic->recipients());
		$section->title = __('Recipients') . ' <small><i class="icon-user"></i> ' . count($recipients) . '</small>';

		return $section;
	}


 	/**
	 * Get topic view.
	 *
	 * @param  Model_Forum_Topic        $topic
	 * @param  View_Generic_Pagination  $pagination
	 */
	public function section_topic(Model_Forum_Topic $topic, View_Generic_Pagination $pagination = null) {
		return new View_Forum_Topic($topic, $pagination, $this->private);
	}


	/**
	 * Set page title
	 *
	 * @param  Model_Forum_Topic  $topic
	 */
	protected function _set_title(Model_Forum_Topic $topic = null) {
		$this->page_title     = Forum::topic($topic);
		$this->page_subtitle  = HTML::icon_value(array(':views' => (int)$topic->read_count), ':views view', ':views views', 'views');
		$this->page_subtitle .= HTML::icon_value(array(':replies' => $topic->post_count - 1), ':replies reply', ':replies replies', 'posts');
/*
		$area = $topic->area();
		if ($this->private) {
			$this->page_subtitle .= ' | ' . HTML::anchor(
				Forum::private_messages_url(),
				__('Back to :area', array(':area' => __('Private messages'))),
				array('title' => strip_tags($area->description))
			);
		} else {
			$this->page_subtitle .= ' | ' . HTML::anchor(
				Route::model($area),
				__('Back to :area', array(':area' => HTML::chars($area->name))),
				array('title' => strip_tags($area->description))
			);
		}*/
	}

}

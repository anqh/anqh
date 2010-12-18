<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Galleries controller
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Galleries extends Controller_Template {

	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->page_title = __('Galleries');
		$this->tabs = array(
			'latest' => array('url' => Route::get('galleries')->uri(), 'text' => __('Latest updates')),
			'browse' => array('url' => Route::get('galleries')->uri(array('action' => 'browse')), 'text' => __('Browse galleries')),
			'flyers' => array('url' => Route::get('flyers')->uri(array('action' => '')), 'text' => __('Browse flyers')),
		);
	}


	/**
	 * Action: approve single image
	 */
	public function action_approve() {
		$this->history = false;

		return $this->action_image();
	}


	/**
	 * Action: approval
	 */
	public function action_approval() {
		$this->history = false;

		// Can we see galleries with un-approved images?
		Permission::required(new Model_Gallery, Model_Gallery::PERMISSION_APPROVE_WAITING, self::$user);

		// Can we see all of them and approve?
		$approve = Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_APPROVE, self::$user);

		// Load galleries we have access to
		$galleries = Model_Gallery::find_pending($approve ? null : self::$user);

		if (count($galleries)) {
			Widget::add('wide', View_Module::factory('galleries/galleries', array(
				'galleries' => $galleries,
				'approval'  => $approve,
				'user'      => self::$user,
			)));
		}
	}


	/**
	 * Action: browse
	 */
	public function action_browse() {
		$this->tab_id = 'browse';

		$months = Model_Gallery::find_months();

		// Default to last month
		$year  = (int)$this->request->param('year');
		$month = (int)$this->request->param('month');
		if (!$year) {
			$year  = max(array_keys($months));
			$month = max(array_keys($months[$year]));
		} else if (!$month) {
			$month = isset($months[$year]) ? min(array_keys($months[$year])) : 1;
		}

		$year  = min($year, date('Y'));
		$month = min(12, max(1, $month));

		$this->page_title .= ' - ' . HTML::chars(date('F Y', mktime(null, null, null, $month, 1, $year)));

		// Month browser
		Widget::add('side', View_Module::factory('galleries/month_browser', array(
			'route'  => 'galleries',
			'action' => 'browse',
			'year'   => $year,
			'month'  => $month,
			'months' => $months
		)));

		// Galleries
		$galleries = Jelly::select('gallery')->year_month($year, $month)->execute();
		if (count($galleries)) {
			Widget::add('main', View_Module::factory('galleries/galleries', array(
				'galleries' => $galleries
			)));
		}

	}


	/**
	 * Action: comment
	 */
	public function action_comment() {
		$this->history = false;
		$comment_id = (int)$this->request->param('id');
		$action     = $this->request->param('commentaction');

		// Load blog_comment
		$comment = Jelly::select('image_comment')->load($comment_id);
		if (($action == 'delete' || $action == 'private') && Security::csrf_valid() && $comment->loaded()) {
			$image = $comment->image;
			$gallery = Model_Gallery::find_by_image($image->id);
			switch ($action) {

				// Delete comment
				case 'delete':
			    if (Permission::has($comment, Model_Image_Comment::PERMISSION_DELETE, self::$user)) {
				    $comment->delete();
				    $image->comment_count--;
				    $image->save();
				    $gallery->comment_count--;
				    $gallery->save();
			    }
			    break;

				// Set comment as private
			  case 'private':
				  if (Permission::has($comment, Model_Image_Comment::PERMISSION_UPDATE, self::$user)) {
					  $comment->private = true;
					  $comment->save();
				  }
			    break;

			}
			if (!$this->ajax) {
				$this->request->redirect(Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => '')));
			}
		}

		if (!$this->ajax) {
			Request::back('galleries');
		}
	}


	/**
	 * Action: comment flyer
	 */
	public function action_comment_flyer() {
		$this->history = false;
		$comment_id = (int)$this->request->param('id');
		$action     = $this->request->param('commentaction');

		// Load blog_comment
		$comment = Jelly::select('image_comment')->load($comment_id);
		if (($action == 'delete' || $action == 'private') && Security::csrf_valid() && $comment->loaded()) {
			$image = $comment->image;
			$flyer = Model_Flyer::find_by_image($image->id);
			switch ($action) {

				// Delete comment
				case 'delete':
			    if (Permission::has($comment, Model_Image_Comment::PERMISSION_DELETE, self::$user)) {
				    $comment->delete();
				    $image->comment_count--;
				    $image->save();
			    }
			    break;

				// Set comment as private
			  case 'private':
				  if (Permission::has($comment, Model_Image_Comment::PERMISSION_UPDATE, self::$user)) {
					  $comment->private = true;
					  $comment->save();
				  }
			    break;

			}
			if (!$this->ajax) {
				$this->request->redirect(Route::get('flyer')->uri(array('id' => $flyer->id, 'action' => '')));
			}
		}

		if (!$this->ajax) {
			Request::back('galleries');
		}
	}


	/**
	 * Action: default
	 */
	public function action_default() {
		$this->history = false;

		$gallery_id = (int)$this->request->param('gallery_id');
		$image_id   = $this->request->param('id');

		/** @var  Model_Gallery  $gallery */
		$gallery = Jelly::select('gallery')->load($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}

		Permission::required($gallery, Model_Gallery::PERMISSION_UPDATE, self::$user);

		if (Security::csrf_valid()) {
			foreach ($gallery->images as $image) {
				if ($image->id == $image_id) {
					$gallery->default_image = $image_id;
					$gallery->save();
					break;
				}
			}
		}

		Request::back(Route::model($gallery));
	}


	/**
	 * Action: delete
	 */
	public function action_delete() {
		$this->history = false;

		$gallery_id = (int)$this->request->param('gallery_id');
		$image_id   = $this->request->param('id');

		/** @var  Model_Gallery  $gallery */
		$gallery = Jelly::select('gallery')->load($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}

		/** @var  Model_Image  $image */
		$image = Jelly::select('image')->load($image_id);
		if (!$image->loaded()) {
			throw new Model_Exception($image, $image_id);
		}

		Permission::required($image, Model_Image::PERMISSION_DELETE, self::$user);

		if (Security::csrf_valid()) {
			foreach ($gallery->images as $image) {
				if ($image->id == $image_id) {
					$gallery->remove('images', $image);
					$gallery->image_count--;
					$gallery->save();
					$image->delete();
					break;
				}
			}
		}

		$this->request->redirect(Route::model($gallery));
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

		// Redirect
		$gallery = Model_Gallery::find_by_event($event->id);
		if ($gallery->loaded()) {
			$this->request->redirect(Route::model($gallery));
		} else {
			$this->request->redirect(Route::get('galleries')->uri(array('action' => 'upload')) . '?event=' . $event->id);
		}
	}


	/**
	 * Action: flyer
	 */
	public function action_flyer() {
		$flyer_id = (int)$this->request->param('id');

		/** @var  Model_Flyer  $flyer */
		$flyer = Jelly::select('flyer')->load($flyer_id);
		if (!$flyer->loaded()) {
			throw new Model_Exception($flyer, $flyer_id);
		}

		/** @var  Model_Image  $image */
		$image = $flyer->image;

		/** @var  Model_Event  $event */
		$event = $flyer->event;

		// Set title
		if ($event->loaded()) {
			$this->page_title = HTML::chars($event->name);
			$this->page_subtitle = HTML::time(date('l ', $event->stamp_begin) . Date::format(Date::DMY_SHORT, $event->stamp_begin), $event->stamp_begin, true);
			$this->page_subtitle .= ' | ' . HTML::anchor(Route::model($event),  __('Go to event'));

			// Facebook
			if (Kohana::config('site.facebook')) {
				Anqh::open_graph('title', __('Flyer') . ': ' . $event->name);
				Anqh::open_graph('url', URL::site(Route::get('flyer')->uri(array('id' => $flyer->id, 'action' => '')), true));
				Anqh::open_graph('description', date('l ', $event->stamp_begin) . Date::format(Date::DMY_SHORT, $event->stamp_begin) . ' @ ' . $event->venue_name);
				Anqh::open_graph('image', URL::site($image->get_url('thumbnail'), true));
			}

		} else {
			$this->page_title = HTML::chars($flyer->name);
			$this->page_subtitle = $flyer->stamp_time ? HTML::time(date('l ', $flyer->stamp_begin) . Date::format(Date::DMY_SHORT, $flyer->stamp_begin), $flyer->stamp_begin, true) : __('Date unknown');
			//$this->page_actions[] = array('link' => Route::model($event), 'text' => __('Add event'));

			// Facebook
			if (Kohana::config('site.facebook')) {
				Anqh::open_graph('title', __('Flyer') . ': ' . $flyer->name);
				Anqh::open_graph('url', URL::site(Route::get('flyer')->uri(array('id' => $flyer->id, 'action' => '')), true));
				$flyer->stamp_time and Anqh::open_graph('description', date('l ', $flyer->stamp_begin) . Date::format(Date::DMY_SHORT, $flyer->stamp_begin));
				Anqh::open_graph('image', URL::site($image->get_url('thumbnail'), true));
			}

		}

		// Comments section
		if (Permission::has($flyer, Model_Flyer::PERMISSION_COMMENTS, self::$user)) {
			$errors = array();
			$values = array();

			// Handle comment
			if (Permission::has($flyer, Model_Flyer::PERMISSION_COMMENT, self::$user) && $_POST) {
				$comment = Jelly::factory('image_comment');
				$comment->image = $image;
				if ($image->author) {
					$comment->user = $image->author;
				}
				$comment->author = self::$user;
				$comment->set(Arr::intersect($_POST, Model_Image_Comment::$editable_fields));
				try {
					$comment->save();
					$image->comment_count++;
					if ($image->author->id != self::$user->id) {
						$image->new_comment_count++;
					}
					$image->save();

					// Newsfeed
					if (!$comment->private) {
						NewsfeedItem_Galleries::comment_flyer(self::$user, $flyer, $image);
					}

					if (!$this->ajax) {
						$this->request->redirect(Route::get('flyer')->uri(array('id' => $image->id, 'action' => '')));
					}
				} catch (Validate_Exception $e) {
					$errors = $e->array->errors('validation');
					$values = $comment;
				}

			} else if (self::$user && $image->author->id == self::$user->id && $image->new_comment_count > 0) {

				// Clear new comment count?
				$image->new_comment_count = 0;
				$image->save();

			}

			$comments = $image->comments;
			$view = View_Module::factory('generic/comments', array(
				'mod_title'  => __('Comments'),
				'delete'     => Route::get('flyer_comment')->uri(array('id' => '%d', 'commentaction' => 'delete')) . '?token=' . Security::csrf(),
				'private'    => false,
				'comments'   => $comments,
				'errors'     => $errors,
				'values'     => $values,
				'pagination' => null,
				'user'       => self::$user,
			));

			if ($this->ajax) {
				echo $view;
				return;
			}
			Widget::add('main', $view);

		} else if (!self::$user) {

			// Guest user
			$view = View_Module::factory('generic/comments_guest', array(
				'mod_title'  => __('Comments'),
				'comments'   => $image->comment_count,
			));
			if ($this->ajax) {
				echo $view;
				return;
			}
			Widget::add('main', $view);

		}

		// Flyer
		Widget::add('wide', View_Module::factory('galleries/flyer', array(
			'mod_id'    => 'image',
			'mod_class' => 'gallery-image',
			'flyer'     => $image,
		)));

	}


	/**
	 * Action: browse flyers
	 */
	public function action_flyers() {
		$this->tab_id = 'flyers';

		$months = Model_Flyer::find_months();

		// Default to current month
		$year  = (int)$this->request->param('year');
		$month = (int)$this->request->param('month', false);
		if (!$year) {
			if (isset($months[(int)date('Y')][(int)date('n')])) {

				// Flyers for current month found
				$year = date('Y');
				$month = date('n');

			} else {

				// No flyers for current month found, default to last month
				$year  = max(array_keys($months));
				$month = max(array_keys($months[$year]));

			}
		} else if ($month === false) {
			$month = isset($months[$year]) ? min(array_keys($months[$year])) : 1;
		}

		// Quick validation
		$year  = min($year, max(array_keys($months)));
		$month = min(12, max(0, $month));

		$this->page_title = __('Flyers') . ' - ' . ($month ? HTML::chars(date('F Y', mktime(null, null, null, $month, 1, $year))) : (__('Date unknown') . ($year == 1970 ? '' : ' ' . $year)));

		// Month browser
		Widget::add('side', View_Module::factory('galleries/month_browser', array(
			'route'  => 'flyers',
			'action' => '',
			'year'   => $year,
			'month'  => $month,
			'months' => $months
		)));

		// Latest flyers
		$flyers = Model_Flyer::find_by_month($year, $month);
		if (count($flyers)) {
			Widget::add('main', View_Module::factory('galleries/flyers', array(
				'flyers' => $flyers,
			)));
		}

	}


	/**
	 * Action: gallery
	 */
	public function action_gallery() {

		/** @var  Model_Gallery  $gallery */
		$gallery_id = (int)$this->request->param('id');
		$gallery = Jelly::select('gallery')->load($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}

		// Are we approving pending images?
		if ($this->request->action == 'pending') {

			// Can we see galleries with un-approved images?
			Permission::required($gallery, Model_Gallery::PERMISSION_APPROVE_WAITING, self::$user);

			// Can we see all of them and approve?
			$approve = Permission::has($gallery, Model_Gallery::PERMISSION_APPROVE, self::$user);

			// Handle images?
			if ($_POST && Security::csrf_valid()) {
				$pending = $gallery->find_images_pending($approve ? null : self::$user);
				$images  = (array)Arr::get($_POST, 'image_id');
				$authors = array();
				if (count($pending) && count($images)) {
					foreach ($pending as $image) {
						$action = Arr::Get($images, $image->id, 'wait');
						switch ($action) {

							case 'approve':
								if ($approve) {
									$gallery->image_count++;
									$authors[$image->author->id] = $image->author->username;
									$image->status = Model_Image::VISIBLE;
									$image->save();
								}
						    break;

							case 'deny':
								$gallery->remove('images', $image->id);
						    $image->delete();
						    break;

						}
					}

					// Admin actions
					if ($approve) {

						// Set default image if none set
						if (!$gallery->default_image->id) {
							$gallery->default_image = $gallery->find_images()->current();
						}

						$gallery->update_copyright();
						$gallery->modified = time();
					}
					$gallery->save();

					// Redirect to normal gallery if all images approved/denied
					if (!count($gallery->find_images_pending($approve ? null : self::$user))) {
						$this->request->redirect(Route::model($gallery));
					} else {
						$this->request->redirect(Route::model($gallery, 'pending'));
					}

				}
			}

		} else {

			Permission::required($gallery, Model_Gallery::PERMISSION_READ, self::$user);

		}

		// Event info
		if ($gallery->event->loaded()) {

			// Event flyers
			if ($gallery->event->flyer_front->id || $gallery->event->flyer_back->id || $gallery->event->flyer_front_url || $gallery->event->flyer_back_url) {
				Widget::add('side', View_Module::factory('events/flyers', array(
					'event' => $gallery->event,
				)), Widget::TOP);
			}

			Widget::add('side', View_Module::factory('events/event_info', array(
				'event' => $gallery->event,
				'user'  => self::$user,
			)), Widget::TOP);
		}

		// Facebook
		if (Kohana::config('site.facebook')) {
			Anqh::open_graph('title', __('Gallery') . ': ' . $gallery->name);
			Anqh::open_graph('url', URL::site(Route::get('gallery')->uri(array('id' => $gallery->id, 'action' => '')), true));
			Anqh::open_graph('description', __2(':images image', ':images images', count($gallery->images), array(':images' => count($gallery->images))) . ' - ' . date('l ', $gallery->date) . Date::format(Date::DMY_SHORT, $gallery->date) . ($gallery->event->loaded() ? ' @ ' . $gallery->event->venue_name : ''));
			if ($gallery->event->loaded() && $gallery->event->flyer_front->loaded()) {
				Anqh::open_graph('image', URL::site($gallery->event->flyer_front->get_url('thumbnail'), true));
			} else {
				Anqh::open_graph('image', URL::site($gallery->default_image->get_url('thumbnail'), true));
			}
		}

		// Set title and tabs
		$this->_set_gallery($gallery);

		// Pictures
		Widget::add('main', View_Module::factory('galleries/gallery', array(
			'gallery' => $gallery,
			'pending' => $this->request->action == 'pending',
			'approve' => isset($approve) ? $approve : null,
			'user'    => self::$user,
		)));

	}


	/**
	 * Action: hover card
	 */
	public function action_hover() {
		$this->history = false;

		// Hover card works only with ajax
		if (!$this->ajax) {
			return $this->action_image();
		}

		$gallery_id = (int)$this->request->param('gallery_id');
		$image_id   = (int)$this->request->param('id');

		/** @var  Model_Gallery  $gallery */
		$gallery = Jelly::select('gallery')->load($gallery_id);
		if ($gallery->loaded()) {
			/** @var  Model_Image  $image */
			$image = Jelly::select('image')->load($image_id);
			if ($image->loaded()) {
				echo View_Module::factory('galleries/hovercard', array(
					'mod_title' => HTML::chars($gallery->name),
					'gallery'   => $gallery,
					'image'     => $image,
				));
			}
		}
	}


	/**
	 * Action: image
	 */
	public function action_image() {
		$gallery_id = (int)$this->request->param('gallery_id');
		$image_id   = $this->request->param('id');

		/** @var  Model_Gallery  $gallery */
		$gallery = Jelly::select('gallery')->load($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}

		// Are we approving pending images?
		if ($this->request->action == 'approve') {

			// Can we see galleries with un-approved images?
			Permission::required($gallery, Model_Gallery::PERMISSION_APPROVE_WAITING, self::$user);

			// Can we see all of them and approve?
			$approve = Permission::has($gallery, Model_Gallery::PERMISSION_APPROVE, self::$user);
			$images = $gallery->find_images_pending($approve ? null : self::$user);

		} else {

			Permission::required($gallery, Model_Gallery::PERMISSION_READ, self::$user);
			$images = $gallery->find_images();

		}

		// Set title and tabs
		$this->_set_gallery($gallery);
		$this->page_subtitle .= ' | ' . HTML::anchor(Route::model($gallery),  __('Back to Gallery'));

		// Find current, previous and next images
		$i = 0;
		$previous = $next = $current = null;
		foreach ($images as $image) {
			$i++;
			if (!is_null($current)) {

				// Current was found last round
				$next = $image;
				$i--;
				break;

			} else if ($image->id == $image_id) {

				// Current found now
				$current = $image;

			} else {

				// No current found
				$previous = $image;

			}
		}

		// Show image
		if (!is_null($current)) {

			// Facebook
			if (Kohana::config('site.facebook')) {
				Anqh::open_graph('title', __('Image') . ': ' . $gallery->name);
				Anqh::open_graph('url', URL::site(Route::get('gallery_image')->uri(array('id' => $current->id, 'gallery_id' => $gallery->id, 'action' => '')), true));
				$current->description and Anqh::open_graph('description', $current->description);
				Anqh::open_graph('image', URL::site($current->get_url('thumbnail'), true));
			}

			// Comments section
			if (!isset($approve) && Permission::has($gallery, Model_Gallery::PERMISSION_COMMENTS, self::$user)) {
				$errors = array();
				$values = array();

				// Handle comment
				if (Permission::has($gallery, Model_Gallery::PERMISSION_COMMENT, self::$user) && $_POST) {
					$comment = Jelly::factory('image_comment');
					$comment->image = $current;
					if ($current->author) {
						$comment->user = $current->author;
					}
					$comment->author = self::$user;
					$comment->set(Arr::intersect($_POST, Model_Image_Comment::$editable_fields));
					try {
						$comment->save();
						$current->comment_count++;
						if ($current->author->id != self::$user->id) {
							$current->new_comment_count++;
						}
						$current->save();
						$gallery->comment_count++;
						$gallery->save();

						// Newsfeed
						if (!$comment->private) {
							NewsfeedItem_Galleries::comment(self::$user, $gallery, $current);
						}

						if (!$this->ajax) {
							$this->request->redirect(Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => '')));
						}
					} catch (Validate_Exception $e) {
						$errors = $e->array->errors('validation');
						$values = $comment;
					}

				} else if (self::$user && $current->author->id == self::$user->id && $current->new_comment_count > 0) {

					// Clear new comment count?
					$current->new_comment_count = 0;
					$current->save();

				}

				$comments = $current->comments;
				$view = View_Module::factory('generic/comments', array(
					'mod_title'  => __('Comments'),
					'delete'     => Route::get('gallery_image_comment')->uri(array('id' => '%d', 'commentaction' => 'delete')) . '?token=' . Security::csrf(),
					'private'    => false, //Route::get('gallery_image_comment')->uri(array('id' => '%d', 'commentaction' => 'private')) . '?token=' . Security::csrf(),
					'comments'   => $comments,
					'errors'     => $errors,
					'values'     => $values,
					'pagination' => null,
					'user'       => self::$user,
				));

				if ($this->ajax) {
					echo $view;
					return;
				}
				Widget::add('main', $view);

			} else if (!self::$user) {

				// Guest user
				$view = View_Module::factory('generic/comments_guest', array(
					'mod_title'  => __('Comments'),
					'comments'   => $current->comment_count,
				));
				if ($this->ajax) {
					echo $view;
					return;
				}
				Widget::add('main', $view);

			}


			// Image
			if (!isset($approve)) {
				$current->view_count++;
				$current->save();
			}

			// Image actions
			if (Permission::has($gallery, Model_Gallery::PERMISSION_UPDATE, self::$user)) {
				$this->page_actions[] = array('link' => Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $current->id, 'action' => 'default')) . '?token=' . Security::csrf(), 'text' => __('Default'), 'class' => 'image-default');
			}
			if (Permission::has($current, Model_Image::PERMISSION_DELETE, self::$user)) {
				$this->page_actions[] = array('link' => Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $current->id, 'action' => 'delete')) . '?token=' . Security::csrf(), 'text' => __('Delete'), 'class' => 'image-delete');
			}

			Widget::add('wide', View_Module::factory('galleries/image', array(
				'mod_id'    => 'image',
				'mod_class' => 'gallery-image',
				'gallery'   => $gallery,
				'images'    => count($images),
				'current'   => $i,
				'image'     => $current,
				'next'      => $next,
				'previous'  => $previous,
				'approve'   => isset($approve) ? $approve : null,
			)));

			// Image info
			Widget::add('side', View_Module::factory('galleries/image_info', array(
				'mod_title' => __('Picture info'),
				'image'     => $current,
			)));

		}

	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$this->tab_id = 'latest';

		// Set actions
		if (Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_CREATE, self::$user)) {
			$this->page_actions[] = array('link' => Route::get('galleries')->uri(array('action' => 'upload')), 'text' => __('Upload images'), 'class' => 'images-add');
		}

		// Galleries with latest images
		$galleries = Model_Gallery::find_latest(16);
		if (count($galleries)) {
			Widget::add('main', View_Module::factory('galleries/galleries', array(
				'galleries' => $galleries,
			)));
		}

		// Latest flyers
		$flyers = Model_Flyer::find_latest(6);
		if (count($flyers)) {
			Widget::add('side', View_Module::factory('galleries/flyers', array(
				'mod_title' => __('Latest flyers'),
				'flyers'    => $flyers,
			)));
		}

	}


	/**
	 * Action: pending
	 */
	public function action_pending() {
		$this->history = false;

		return $this->action_gallery();
	}


	/**
	 * Action: update
	 */
	public function action_update() {
		$this->history = false;

		/** @var  Model_Gallery  $gallery */
		$gallery_id = (int)$this->request->param('id');
		$gallery = Jelly::select('gallery')->load($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}

		Permission::required($gallery, Model_Gallery::PERMISSION_UPDATE, self::$user);

		// Update copyrights
		$gallery
			->update_copyright()
			->save();

		Request::back(Route::get('galleries')->uri());
	}


	/**
	 * Action: upload
	 */
	public function action_upload() {
		Widget::add('side', View_Module::factory('galleries/help_upload', array(
			'mod_title' => __('Instructions'),
			'mod_class' => 'help',
		)));

		// Load existing gallery if any
		$gallery_id = (int)$this->request->param('gallery_id');
		if (!$gallery_id) {
			$gallery_id = (int)$this->request->param('id');
		}
		if ($gallery_id) {
			$gallery = Jelly::select('gallery')->load($gallery_id);
			if (!$gallery->loaded()) {
				throw new Model_Exception($gallery, $gallery_id);
			}
		} else {
			$this->page_title = __('Upload images');

			return $this->_edit_gallery(null, Arr::get($_REQUEST, 'event'));
		}

		Permission::required(new Model_Gallery, Model_Gallery::PERMISSION_UPLOAD, self::$user);

		// Handle post
		$errors = array();
		if ($_FILES) {
			$file = Arr::get($_FILES, 'file');
			if ($file) {

				// We need to flatten our file one level as ajax uploaded files are set up funnily.
				// Support for ajax uploads one by one for now..
				foreach ($file as $key => $value) {
					is_array($value) and $file[$key] = $value[0];
				}

				// Save image
				try {

					// Make sure we don't timeout. An external queue would be better thuough.
					set_time_limit(0);
					ignore_user_abort(true);

					// Duplicate filename check
					$uploaded = Session::instance()->get('uploaded', array());
					if (isset($uploaded[$gallery->id]) && in_array($file['name'], $uploaded[$gallery->id])) {
						throw new Kohana_Exception(__('Already uploaded'));
					}

					$image = Jelly::factory('image')
						->set(array(
							'author' => self::$user,
							'file'   => $file,
							'status' => Model_Image::NOT_ACCEPTED,
						));

					// Create bigger normal image
					$image->normal = 'wide';
					$image->save();

					// Save exif
					try {
						Jelly::factory('image_exif')
							->set(array('image' => $image))
							->save();
					} catch (Kohana_Exception $e) { }

					// Set the image as gallery image
					$gallery->add('images', $image);
					$gallery->save();

					// Mark filename as uploaded for current gallery
					$uploaded[$gallery->id][] = $file['name'];
					Session::instance()->set('uploaded', $uploaded);

					// Make sure the user has photo role to be able to see uploaded pictures
					if (!self::$user->has_role('photo')) {
						self::$user
							->add('roles', Model_Role::find('photo'))
							->save();
					}

					// Show image if uploaded with ajax
					if ($this->ajax) {
						echo json_encode(array(
							'ok'        => true,
							'thumbnail' => HTML::anchor(
								Route::get('gallery_image')->uri(array(
									'gallery_id' => Route::model_id($gallery),
									'id'         => $image->id,
									'action'     => 'approve',
								)),
								HTML::image($image->get_url('thumbnail'))
							)
						));
						return;
					}

					$this->request->redirect(Route::model($gallery));

				} catch (Validate_Exception $e) {
					$errors = $e->array->errors('validation');
				} catch (Kohana_Exception $e) {
					$errors = array('file' => $e->getMessage());
				}

			}
		}

		// Show errors if uploading with ajax, skip form
		if ($this->ajax && !empty($errors)) {
			echo json_encode(array('error' => Arr::get($errors, 'file')));
			return;
		}

		$this->_set_gallery($gallery);

		// Build simplified form
		$form = array(
			'ajaxify' => $this->ajax,
			'errors'  => $errors,
			'cancel'  => Request::back(Route::get('galleries')->uri(), true),
			'field' => array(
				'name' => 'file'
			),
		);

		Widget::add('head', HTML::script('js/jquery.html5_upload.js'));
		Widget::add('main', View_Module::factory('form/multiple_upload', array(
			'mod_title' => __('Upload images'),
			'form'      => $form,
		)));
	}


	/**
	 * Edit gallery
	 *
	 * @param  integer  $gallery_id
	 * @param  integer  $event_id
	 */
	protected function _edit_gallery($gallery_id = null, $event_id = null) {
		$this->history = false;

		if ($gallery_id) {

			// Editing old
			$gallery = Jelly::select('gallery')->load($gallery_id);
			if (!$gallery->loaded()) {
				throw new Model_Exception($gallery, $gallery_id);
			}
			Permission::required($gallery, Model_Gallery::PERMISSION_UPDATE, self::$user);
			$cancel = Route::model($gallery);
			$save   = null;
			$upload = false;

		} else {

			// Creating new
			$gallery = Jelly::factory('gallery');
			Permission::required($gallery, Model_Gallery::PERMISSION_CREATE, self::$user);
			$cancel = Request::back(Route::get('galleries')->uri(), true);
			$save   = __('Continue');
			$upload = true;

			if ($event_id) {
				/** @var  Model_Event  $event */
				$event = Jelly::select('event')->load($event_id);
			}
		}

		// Handle post
		$errors = array();
		if ($_POST || isset($_GET['from'])) {
			$event_id = $_POST ? (int)Arr::get($_POST, 'event') : (int)Arr::get($_GET, 'from');
			$event = Jelly::select('event')->load($event_id);

			if (!$gallery->loaded() && $event->loaded()) {

				// Redirect to existing gallery if trying to create duplicate
				$old = Model_Gallery::find_by_event($event_id);
				if ($old->loaded()) {
					$this->request->redirect(Route::model($old, 'upload'));
				}

				// Fill gallery info from event when creating new
				$gallery->name = $event->name;
				$gallery->date = $event->stamp_begin;
				$gallery->event = $event;

			} else if ($gallery->loaded()) {

				// Editing old
				$gallery->set(Arr::intersect($_POST, Model_Gallery::$editable_fields));

			}

			try {
				$gallery->save();
				$this->request->redirect(Route::model($gallery, $upload ? 'upload' : null));
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}

		// Build form
		$form = array(
			'attributes' => array(
				'onsubmit' => 'return false;',
			),
			'values' => $gallery,
			'errors' => $errors,
			'cancel' => $cancel,
			'save'   => false,
			'groups' => array(
				array(
					'fields' => array(
						'name'  => array(
							'label' => __('Event name'),
							'tip'   => __('Enter at least 3 characters')
						),
					),
				),
			)
		);

		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));

		$data = (isset($event) && $event->loaded())
			? array(
				'mod_title' => HTML::chars($event->name),
				'mod_subtitle' => HTML::time(Date('l ', $event->stamp_begin) . Date::format('DDMMYYYY', $event->stamp_begin), $event->stamp_begin, true),
				'event' => $event,
			)
			: array();
		Widget::add('main', View_Module::factory('galleries/event', $data));

		// Name autocomplete
		Widget::add('foot', HTML::script_source('
$("#field-name")
	.autocomplete({
		minLength: 3,
		source: function(request, response) {
			$.ajax({
				url: "/api/v1/events/search",
				dataType: "json",
				data: {
					q: request.term,
					limit: 25,
					filter: "past",
					search: "name",
					field: "id:name:city:stamp_begin",
					order: "stamp_begin.desc"
				},
				success: function(data) {
					response($.map(data.events, function(item) {
						return {
							label: item.name,
							stamp: item.stamp_begin,
							city: item.city,
							value: item.name,
							id: item.id
						}
					}))
				}
			});
		},
		select: function(event, ui) {
			window.location = "' .  URL::site(Route::get('galleries')->uri(array('action' => 'upload'))) . '?from=" + ui.item.id;
		},
	})
	.data("autocomplete")._renderItem = function(ul, item) {
		return $("<li></li>")
			.data("item.autocomplete", item)
			.append("<a>" + $.datepicker.formatDate("dd.mm.yy", new Date(item.stamp * 1000)) + " " + item.label + ", " + item.city + "</a>")
			.appendTo(ul);
	};
'));	}


	/**
	 * Set gallery specific title and tabs
	 *
	 * @param  Model_Gallery  $gallery
	 */
	protected function _set_gallery($gallery) {

		// Add new tab
		$this->tab_id = 'gallery';
		$this->tabs['browse']['url'] = Route::get('galleries')->uri(array('action' => 'browse', 'year' => date('Y', $gallery->date), 'month' => date('m', $gallery->date)));
		$this->tabs['gallery'] = array('url' => Route::model($gallery), 'text' => __('Gallery'));

		// Set title
		$images = count($gallery->images);
		$this->page_title = HTML::chars($gallery->name);
		$this->page_subtitle  = __2(':images image', ':images images', $images, array(':images' => $images)) . ' - ' . HTML::time(Date::format('DMYYYY', $gallery->date), $gallery->date, true);
	  $this->page_subtitle .= ' | ' . HTML::anchor(Route::model($gallery->event),  __('Go to event'));

		// Set actions
		if (Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_UPLOAD, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($gallery, 'upload'), 'text' => __('Upload images'), 'class' => 'images-add');
		}
		if (Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($gallery, 'update'), 'text' => __('Update gallery'), 'class' => 'gallery-update');
		}

	}

}

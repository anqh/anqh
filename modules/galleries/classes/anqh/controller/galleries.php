<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Galleries controller
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Galleries extends Controller_Page {

	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->page_title = __('Galleries');
	}


	/**
	 * Action: browse
	 */
	public function action_browse() {

		// Build available months
		$months = Model_Gallery::factory()->find_months();

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


		// Build page
		$this->view      = View_Page::factory(__('Galleries') . ' - ' . HTML::chars(date('F Y', mktime(null, null, null, $month, 1, $year))));
		$this->view->tab = 'galleries';
		$this->_set_page_actions(Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_CREATE, self::$user));
		$this->_set_flyer_actions();

		// Pagination
		$this->view->add(View_Page::COLUMN_CENTER, $this->section_month_pagination($months, 'galleries', 'browse', $year, $month));

		// Month browser
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_month_browser($months, 'galleries', 'browse', $year, $month));

		// Galleries
		$galleries = Model_Gallery::factory()->find_by_month($year, $month);
		if (count($galleries)) {
			$section = $this->section_galleries_thumbs($galleries);
			$section->wide = false;
			$this->view->add(View_Page::COLUMN_CENTER, $section);
		}

	}


	/**
	 * Action: comment
	 */
	public function action_comment() {
		$this->history = false;
		$comment_id    = (int)$this->request->param('id');
		$action        = $this->request->param('commentaction');

		// Load image comment
		$comment = new Model_Image_Comment($comment_id);
		if (($action == 'delete' || $action == 'private') && Security::csrf_valid() && $comment->loaded()) {
			$image   = $comment->image();
			$gallery = $image->gallery();
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

		// Load image comment
		$comment = Model_Image_Comment::factory($comment_id);
		if (($action == 'delete' || $action == 'private') && Security::csrf_valid() && $comment->loaded()) {
			$image = $comment->image;
			$flyer = Model_Flyer::factory()->find_by_image($image->id);
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
		$gallery = Model_Gallery::factory($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}

		Permission::required($gallery, Model_Gallery::PERMISSION_UPDATE, self::$user);

		if (Security::csrf_valid()) {
			foreach ($gallery->images() as $image) {
				if ($image->id == $image_id) {
					$gallery->default_image_id = $image_id;
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
		$gallery = Model_Gallery::factory($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}

		/** @var  Model_Image  $image */
		$image = Model_Image::factory($image_id);
		if (!$image->loaded()) {
			throw new Model_Exception($image, $image_id);
		}

		Permission::required($image, Model_Image::PERMISSION_DELETE, self::$user);

		$success = 0;
		if (Security::csrf_valid()) {
			foreach ($gallery->images() as $image) {
				if ($image->id == $image_id) {
					$gallery->image_count--;
					$gallery->save();
					$image->delete();
					$success = 1;
					break;
				}
			}
		}

		if ($this->_request_type === Controller::REQUEST_INITIAL) {
			$this->request->redirect(Route::model($gallery));
		} else if ($this->_request_type === Controller::REQUEST_AJAX) {
			$this->response->body($success);
		}
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

		$gallery = Model_Gallery::factory()->find_by_event($event->id);
		if ($gallery->loaded() && count($gallery->images())) {

			// Redirect to existing gallery
			$this->request->redirect(Route::model($gallery));

		} else {

			// Show empty gallery
			$this->view = new View_Page($event->name);

			// Set actions
			$this->page_actions[] = array(
				'link' => Route::model($event),
				'text' => '<i class="icon-calendar"></i> ' . __('Event') . ' &raquo;'
			);

			$this->view->add(View_Page::COLUMN_CENTER, $this->section_gallery_empty($event));
			//$this->request->redirect(Route::get('galleries')->uri(array('action' => 'upload')) . '?event=' . $event->id);

		}
	}


	/**
	 * Action: flyer
	 */
	public function action_flyer() {
		$flyer_id = $this->request->param('id');
		switch ($flyer_id) {

			// Random flyer
			case 'random':

			// Random undated flyer
			case 'undated':
				$flyer = Model_Flyer::factory()->find_random($flyer_id == 'undated');
				$this->request->redirect(Route::get('flyer')->uri(array('id' => $flyer->id)));
				break;

			// Known flyer
			default:
				/** @var  Model_Flyer  $flyer */
				$flyer = new Model_Flyer((int)$flyer_id);
				if (!$flyer->loaded()) {
					throw new Model_Exception($flyer, $flyer_id);
				}

		}

		/** @var  Model_Image  $image */
		$image = $flyer->image();

		/** @var  Model_Event  $event */
		$event = $flyer->event();


		// Handle post
		$errors = array();
		if ((isset($_POST['event_id']) || isset($_POST['name'])) && Security::csrf_valid()) {
			Permission::required($flyer, Model_Flyer::PERMISSION_UPDATE, self::$user);

			try {
				if ($event_id = (int)Arr::get($_POST, 'event_id')) {

					// Event given?
					/** @var  Model_Event  $event */
					$event = new Model_Event($event_id);
					if ($event->loaded()) {
						$flyer->set_fields(array(
							'event_id'    => $event->id,
							'stamp_begin' => $event->stamp_begin
						));
					}
				} else if (Arr::get($_POST, 'name')) {

					// Name and stamp given
					$flyer->set_fields(Arr::intersect($_POST, array('name', 'stamp_begin')));

				}

				// Save only if we got full date
				if ($flyer->has_full_date()) {
					$flyer->save();

					// Newsfeed
					NewsfeedItem_Galleries::flyer_edit(self::$user, $flyer);

					if ($event_id && $event->loaded() && !$event->flyer_front_image_id) {
						$event->flyer_front_image_id = $flyer->image_id;
						$event->flyer_front_url      = $flyer->image()->get_url();
						$event->save();
					}
				}

			  $this->request->redirect(Route::get('flyer')->uri(array('id' => $flyer->id)));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}


		// Comments section
		if (Permission::has($flyer, Model_Flyer::PERMISSION_COMMENTS, self::$user)) {
			$errors = array();
			$values = array();

			// Handle comment
			if (Permission::has($flyer, Model_Flyer::PERMISSION_COMMENT, self::$user) && $_POST) {
				try {
					$comment = Model_Image_Comment::factory()
						->add(self::$user->id, null, Arr::get($_POST, 'comment'), Arr::get($_POST, 'private'), $image);

					$image->comment_count++;
					if ($image->author_id != self::$user->id) {
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
				} catch (Validation_Exception $e) {
					$errors = $e->array->errors('validation');
					$values = $comment;
				}

			} else if (self::$user && $image->author_id == self::$user->id && $image->new_comment_count > 0) {

				// Clear new comment count?
				$image->new_comment_count = 0;
				$image->save();

			}

			// Get view
			$section_comments = $this->section_image_comments($image, 'flyer_comment');
			$section_comments->errors = $errors;
			$section_comments->values = $values;

		} else if (!self::$user) {

			// Guest user
			$section_comments = $this->section_image_comments_teaser($image->comment_count);

		}
		if (isset($section_comments) && $this->_request_type === Controller::REQUEST_AJAX) {
			$this->response->body($section_comments);

			return;
		}


		// Build page
		$this->view = View_Page::factory(__('Flyer'));
		$this->_set_page_actions();
		$this->_set_flyer_actions($flyer);


		// Set title
		Anqh::page_meta('title', __('Flyer') . ': ' . $event->name);
		Anqh::page_meta('url', URL::site(Route::get('flyer')->uri(array('id' => $flyer->id, 'action' => '')), true));
		Anqh::page_meta('twitter:card', 'photo');
		Anqh::page_meta('image', URL::site($image->get_url('thumbnail'), true));
		if ($event) {

			// Flyer is linked to an event
			$this->view->title     = $event->name;
			$this->view->subtitle  = Controller_Events::_event_subtitle($event);

			// Open graph
			Anqh::page_meta('description', date('l ', $event->stamp_begin) . Date::format(Date::DMY_SHORT, $event->stamp_begin) . ' @ ' . $event->venue_name);

		} else {

			// Flyer is not linked to an event
			$this->view->title    = $flyer->name;
			$this->view->subtitle = $flyer->has_full_date()
				? HTML::time(date('l ', $flyer->stamp_begin) . Date::format(Date::DMY_SHORT, $flyer->stamp_begin), $flyer->stamp_begin, true)
				: __('Date unknown');

			// Open graph
			$flyer->has_full_date() and Anqh::page_meta('description', date('l ', $flyer->stamp_begin) . Date::format(Date::DMY_SHORT, $flyer->stamp_begin));

		}
		Anqh::share(true);


		// Edit flyer
		if (Permission::has($flyer, Model_Flyer::PERMISSION_UPDATE, self::$user)) {
			$section = $this->section_flyer_edit($flyer);
			$section->error = $errors;
			$this->view->add(View_Page::COLUMN_TOP, $section);
		}


		// Flyer
		$image->view_count++;
		$image->save();
		$this->view->add(View_Page::COLUMN_TOP, $this->section_flyer($image));

		// Comments
		if (isset($section_comments)) {
			$this->view->add(View_Page::COLUMN_CENTER, $section_comments);
		}

	}


	/**
	 * Action: browse flyers
	 */
	public function action_flyers() {
		$months = Model_Flyer::find_months();

		// Default to current month
		$year  = (int)$this->request->param('year');
		$month = (int)$this->request->param('month');
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
		} else if (!$month) {
			$month = isset($months[$year]) ? min(array_keys($months[$year])) : 1;
		}

		// Quick validation
		$year  = min($year, max(array_keys($months)));
		$month = min(12, max(0, $month));

		// Build page
		$this->view = View_Page::factory(
			__('Flyers') .
				' - ' .
				($month ? HTML::chars(date('F Y', mktime(null, null, null, $month, 1, $year))) : (__('Date unknown') . ($year == 1970 ? '' : ' ' . $year)))
		);
		$this->view->tab   = 'flyers';
		$this->_set_page_actions();
		$this->_set_flyer_actions();

		// Pagination
		$this->view->add(View_Page::COLUMN_CENTER, $this->section_month_pagination($months, 'flyers', '', $year, $month));

		// Month browser
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_month_browser($months, 'flyers', '', $year, $month));

		// Latest flyers
		$flyers = Model_Flyer::factory()->find_by_month($year, $month);
		if (count($flyers)) {
			$section = $this->section_flyers_thumbs($flyers);
			$section->wide = false;
			$this->view->add(View_Page::COLUMN_CENTER, $section);
		}

	}


	/**
	 * Action: gallery
	 */
	public function action_gallery() {

		/** @var  Model_Gallery  $gallery */
		$gallery_id = (int)$this->request->param('id');
		$gallery = Model_Gallery::factory($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}

		Permission::required($gallery, Model_Gallery::PERMISSION_READ, self::$user);

		// External links
		$link_add = trim(Arr::get($_POST, 'link'));
		$link_del = Arr::get($_GET, 'delete_link');
		if ($link_add || is_numeric($link_del)) {
			if (Permission::has($gallery, Model_Gallery::PERMISSION_CREATE, self::$user) && Security::csrf_valid()) {
				$links  = $gallery->links;

				if ($link_add && Valid::url($link_add)) {

					// Add new link
					$links .= ($links ? "\n" : '') . self::$user->id . ',' . $link_add;

				} else if (is_numeric($link_del)) {

					// Remove link
					$old_links = explode("\n", $links);
					if ($old_links[$link_del]) {
						list($user_id, $url) = explode(',', $old_links[$link_del]);
						if (self::$user->id == $user_id || Permission::has($gallery, Model_Gallery::PERMISSION_UPDATE, self::$user)) {
							unset($old_links[$link_del]);
							$links = implode("\n", $old_links);
						}
					}

				}

				$gallery->links = $links;
				try {
					$gallery->save();
				} catch (Validation_Exception $e) {
				}
			}

			$this->request->redirect(Route::model($gallery));
		}


		// Build page
		$this->view = View_Page::factory(__('Gallery'));
		$this->_set_page_actions(Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_CREATE, self::$user));
		$this->_set_gallery($gallery);
		if (Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_UPDATE, self::$user)) {
			$this->view->actions[] = array(
				'link'  => Route::model($gallery, 'update'),
				'text'  => '<i class="icon-refresh"></i> ' . __('Update gallery'),
			);
		}

		// Share
		Anqh::page_meta('title', __('Gallery') . ': ' . $gallery->name);
		Anqh::page_meta('url', URL::site(Route::get('gallery')->uri(array('id' => $gallery->id, 'action' => '')), true));
		Anqh::page_meta('description', __($gallery->image_count == 1 ? ':images image' : ':images images', array(':images' => $gallery->image_count)) . ' - ' . date('l ', $gallery->date) . Date::format(Date::DMY_SHORT, $gallery->date) . ($event ? ' @ ' . $event->venue_name : ''));
		if ($event && $image = $event->flyer_front()) {
			Anqh::page_meta('image', URL::site($image->get_url('thumbnail'), true));
		} else if ($image = $gallery->default_image()) {
			Anqh::page_meta('image', URL::site($image->get_url('thumbnail'), true));
		}
		Anqh::share(true);

		// Event info
		if ($event = $gallery->event()) {

			// Event info
			$this->view->subtitle = Controller_Events::_event_subtitle($event);

			// Event flyer
			$this->view->add(View_Page::COLUMN_RIGHT, $this->section_event_image($event));

		}

		// Pictures
		$this->view->add(View_Page::COLUMN_CENTER, $this->section_gallery_thumbs($gallery));

		// External links
		if ($gallery->links || Permission::has($gallery, Model_Gallery::PERMISSION_CREATE, self::$user)) {
			$this->view->add(View_Page::COLUMN_CENTER, $this->section_gallery_links($gallery));
		}

	}


	/**
	 * Action: hover card
	 */
	public function action_hover() {
		$this->history = false;

		switch ($this->request->param('type')) {

			// Flyer hover card
			case  'flyer':
				if ($this->_request_type !== Controller::REQUEST_AJAX) {
					$this->action_flyer();

					return;
				}

				$flyer_id = (int)$this->request->param('id');
				$flyer    = new Model_Flyer((int)$flyer_id);
				if ($flyer->loaded()) {
					$this->response->body($this->section_flyer_hovercard($flyer));
				}
				break;

			// Image hover card
			case 'image';
				if ($this->_request_type !== Controller::REQUEST_AJAX) {
					$this->action_image();

					return;
				}

				$gallery_id = (int)$this->request->param('gallery_id');
				$image_id   = (int)$this->request->param('id');
				$gallery    = Model_Gallery::factory($gallery_id);
				if ($gallery->loaded()) {
					$image = Model_Image::factory($image_id);
					if ($image->loaded()) {
						$this->response->body($this->section_image_hovercard($image, $gallery));
					}
				}
				break;

			// Gallery hover card
			default:
				$gallery_id = (int)$this->request->param('id');
				$gallery    = Model_Gallery::factory($gallery_id);
				if ($gallery->loaded()) {
					$image = $gallery->default_image();
					if ($image->loaded()) {
						$this->response->body($this->section_image_hovercard($image, $gallery));
					}
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
		$gallery = Model_Gallery::factory($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}

		Permission::required($gallery, Model_Gallery::PERMISSION_READ, self::$user);
		$images = $gallery->images();

		// Find current, previous and next images
		$i = 0;

		/** @var  Model_Image  $next */
		/** @var  Model_Image  $previous */
		/** @var  Model_Image  $current */
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

				// Fix state to loaded to perform update instead of insert when saving
				$current->state(AutoModeler::STATE_LOADED);

			} else {

				// No current found
				$previous = $image;

			}
		}

		// Show image
		if (!is_null($current)) {

			// Comments section
			if (Permission::has($gallery, Model_Gallery::PERMISSION_COMMENTS, self::$user)) {
				$errors = array();
				$values = array();

				// Handle comment
				if (Permission::has($gallery, Model_Gallery::PERMISSION_COMMENT, self::$user) && $_POST) {
					try {
						$comment = Model_Image_Comment::factory()
							->add(self::$user->id, null, Arr::get($_POST, 'comment'), Arr::get($_POST, 'private'), $current);

						$current->comment_count++;
						$current->save();
						if ($current->author_id != self::$user->id) {
							$target = Model_User::find_user($current->author_id);
							Notification_Galleries::image_comment(self::$user, $target, $current, $comment->comment);
						}

						$gallery->comment_count++;
						$gallery->save();

						if (!$comment->private) {

							// Noted users
							foreach ($current->notes() as $note) {
								if ($note->user_id) {
									$target = Model_User::find_user($note->user_id);
									Notification_Galleries::image_comment(self::$user, $target, $current, $comment->comment);
								}
							}

							// Newsfeed
							NewsfeedItem_Galleries::comment(self::$user, $gallery, $current);

						}

						// Redirect back to image if not ajax
						if ($this->_request_type !== Controller::REQUEST_AJAX) {
							$this->request->redirect(Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $current->id, 'action' => '')));

							return;
						}

					} catch (Validation_Exception $e) {
						$errors = $e->array->errors('validation');
						$values = $comment;
					}

				} else if (self::$user) {

					// Clear new comment count?
					// @TODO: Remove, deprecated after new notification system
					if ($current->author_id == self::$user->id && $current->new_comment_count > 0) {
						$current->new_comment_count = 0;
						$current->save();
					}
					foreach ($current->notes() as $note) {
						if ($note->user_id == self::$user->id) {
							$note->state(AutoModeler::STATE_LOADED);
							$save = false;
							if ($note->new_comment_count > 0) {
								$note->new_comment_count = 0;
								$save = true;
							}
							if ($note->new_note > 0) {
								$note->new_note = null;
								$save = true;
							}
							if ($save) {
								$note->save();
							}
						}
					}

				}

				// Get view
				$section_comments = $this->section_image_comments($current);
				$section_comments->errors = $errors;
				$section_comments->values = $values;

			} else if (!self::$user) {

				// Guest user
				$section_comments = $this->section_image_comments_teaser($current->comment_count);

			}

			if (isset($section_comments) && $this->_request_type === Controller::REQUEST_AJAX) {
				$this->response->body($section_comments);

				return;
			}


			// Build page

			// Image actions
			if (Permission::has($gallery, Model_Gallery::PERMISSION_UPDATE, self::$user)) {
				$this->view->actions[] = array(
					'link'  => Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $current->id, 'action' => 'default')) . '?token=' . Security::csrf(),
					'text'  => '<i class="icon-home"></i> ' . __('Set gallery default'),
					'class' => 'btn-inverse image-default'
				);
			}
			if (Permission::has($current, Model_Image::PERMISSION_DELETE, self::$user)) {
				$this->view->actions[] = array(
					'link'  => Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $current->id, 'action' => 'delete')) . '?token=' . Security::csrf(),
					'text'  => '<i class="icon-trash"></i> ' . __('Delete'),
					'class' => 'btn-inverse image-delete'
				);
			}
			if (Permission::has($current, Model_Image::PERMISSION_REPORT, self::$user)) {
				$this->view->actions[] = array(
					'link'  => Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $current->id, 'action' => 'report')),
					'text'  => '<i class="icon-warning-sign"></i> ' . __('Report'),
					'class' => 'btn-inverse dialogify',
					'data-dialog-title' => __('Report image')
				);
			}

			// Gallery actions
			$this->_set_page_actions(Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_CREATE, self::$user));
			$this->_set_gallery($gallery);
			array_unshift($this->view->tabs, array(
				'link' => Route::model($gallery),
				'text' => '&laquo; ' . __('Gallery')
			));
			$this->view->tabs['gallery'] = array(
				'link' => Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $current->id)),
				'text' => '<i class="icon-camera-retro"></i> ' . __('Photo')
			);

			// Event info
			if ($event = $gallery->event()) {
				$this->view->subtitle = Controller_Events::_event_subtitle($event);
			}

			// Pagination
			$previous_url = $previous ? Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $previous->id, 'action' => '')) . '#title' : null;
			$next_url     = $next     ? Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $next->id, 'action' => '')) . '#title' : null;
			$this->view->add(View_Page::COLUMN_TOP, $this->section_image_pagination($previous_url, $next_url));

			// Image
			$current->view_count++;
			$current->save();
			$this->view->add(View_Page::COLUMN_TOP, $this->section_image($current, $gallery, $next_url));

			// Comments
			if (isset($section_comments)) {
				$this->view->add(View_Page::COLUMN_CENTER, $section_comments);
			}

			// Share
			Anqh::page_meta('title', __('Image') . ': ' . $gallery->name);
			Anqh::page_meta('url', URL::site(Route::url('gallery_image', array('id' => $current->id, 'gallery_id' => $gallery->id, 'action' => '')), true));
			if ($current->description) {
				Anqh::page_meta('description', $current->description);
			}
			Anqh::page_meta('image', URL::site($current->get_url('thumbnail'), true));
			Anqh::page_meta('twitter:card', 'photo');
			Anqh::share(true);

		}

	}


	/**
	 * Controller default action
	 */
	public function action_index() {

		// Build page
		$this->view      = View_Page::factory(__('Galleries'));
		$this->view->tab = 'latest';
		$this->_set_page_actions(Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_CREATE, self::$user));
		$this->_set_flyer_actions();

		// Galleries with latest images
		$galleries = Model_Gallery::factory()->find_latest(12);
		if (count($galleries)) {
			$this->view->add(View_Page::COLUMN_TOP, $this->section_galleries_thumbs($galleries));
		}

		// Latest flyers
		$flyers = Model_Flyer::factory()->find_latest(12);
		if (count($flyers)) {
			$section = $this->section_flyers_thumbs($flyers);
			$section->title  = __('Latest flyers');
			$this->view->add(View_Page::COLUMN_TOP, $section);
		}

	}


	/**
	 * Action: add note
	 */
	public function action_note() {
		$this->history = false;

		/** @var  Model_Gallery  $gallery */
		$gallery_id = (int)$this->request->param('gallery_id');
		$gallery    = new Model_Gallery($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}

		/** @var  Model_Image $image */
		$image_id = $this->request->param('id');
		$image    = new Model_Image($image_id);
		if (!$image->loaded()) {
			throw new Model_Exception($image, $image_id);
		}

		// Permission check
		Permission::required($image, Model_Image::PERMISSION_NOTE, self::$user);

		// Create note
		if (isset($_POST['name']) && trim($_POST['name'] != '')) {

			// Get note user
			$username = trim($_POST['name']);
			$user     = Model_User::find_user($username);
			if (!$user && $user_id = Arr::get($_POST, 'user_id')) {
				$user = Model_User::find_user($user_id);
			}

			try {
				$position = Arr::intersect($_POST, array('x', 'y', 'width', 'height'), true);
				$image->add_note(self::$user->id, count($position) == 4 ? $position : null, $user ? $user : $username);

				// Newsfeed & notification
				if ($user) {
					Notification_Galleries::image_note(self::$user, $user, $image);
					NewsfeedItem_Galleries::note(self::$user, $gallery, $image, $user);
				}

			} catch (Validation_Exception $e) {}
		}

		// Redirect back to image
		// @todo: ajaxify for more graceful approach
		$this->request->redirect(Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => '')));
	}


	/**
	 * Action: photographer profile
	 */
	public function action_photographer() {
		$user = Model_User::find_user(urldecode((string)$this->request->param('username')));
		if (!$user) {
			$this->request->redirect(Route::url('galleries'));

			return;
		}

		// Build page
		$this->view      = Controller_User::_set_page($user);
		$this->view->tab = 'photographer';

		// Galleries
		$galleries = Model_Gallery::factory()->find_by_user($user->id);
		$this->view->add(View_Page::COLUMN_CENTER, $this->section_galleries_thumbs($galleries, true));

		// Top images
		foreach (array(Model_Image::TOP_RATED, Model_Image::TOP_COMMENTED, Model_Image::TOP_VIEWED) as $type) {
			$section = $this->section_top($type, 6, $user->id);
			$section->class = 'full';

			$this->view->add(View_Page::COLUMN_RIGHT, $section);
		}

	}


	/**
	 * Action: report
	 */
	public function action_report() {
		$this->history = false;

		$gallery_id = (int)$this->request->param('gallery_id');
		$image_id   = $this->request->param('id');

		/** @var  Model_Gallery  $gallery */
		$gallery = Model_Gallery::factory($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}

		/** @var  Model_Image  $image */
		$image = Model_Image::factory($image_id);
		if (!$image->loaded()) {
			throw new Model_Exception($image, $image_id);
		}

		Permission::required($image, Model_Image::PERMISSION_REPORT, self::$user);

		$cancel_url = Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => ''));

		// Handle report
		if ($_POST && Security::csrf_valid()) {
			$reason = trim(Arr::get($_POST, 'reason'));

			Notification_Galleries::image_removal_request(self::$user, $image, $reason ? $reason : null);

			if ($this->_request_type === Controller::REQUEST_AJAX) {
				$this->response->body(new View_Alert(__('Report filed.'), null, View_Alert::SUCCESS));
			} else {
				$this->request->redirect($cancel_url);
			}

			return;
		}

		$section = $this->section_image_report($image);

		// Show only the form is AJAX
		if ($this->_request_type === Controller::REQUEST_AJAX) {
			$this->response->body($section);

			return;
		}

		// Build page
		$this->view = View_Page::factory(__('Report image'));
		$this->view->actions[] = array(
			'link'  => $cancel_url,
			'text'  => __('Cancel'),
			'class' => 'btn-inverse'
		);

		// Image
		$this->view->add(View_Page::COLUMN_TOP, $this->section_image($image, $gallery, $cancel_url));

		// Form
		$this->view->add(View_Page::COLUMN_TOP, $section);

	}


	/**
	 * Action: search
	 */
	public function action_search() {
		$images   = array();
		$username = Arr::get($_GET, 'user');

		$this->view = View_Page::factory(__('Search'));

		if ($username) {

			// Search user's images
			if ($user = Model_User::find_user($username)) {
				$this->view->title = __("Search results for ':query'", array(':query' => HTML::chars($user->username)));
				$images = Model_Image::factory()->find_by_user($user->id);
			} else {
				$this->view->title = __("Search results for ':query'", array(':query' => HTML::chars($username)));
			}

			// Build page
			$this->view->subtitle = __(':count images', array(':count' => count($images)));
			$this->_set_page_actions(Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_CREATE, self::$user));

			if (count($images)) {
				$this->view->add(View_Page::COLUMN_CENTER, $this->section_search_results($images));
			} else {
				$this->view->add(View_Page::COLUMN_CENTER, new View_Alert(__('No images found.'), null, View_Alert::INFO));
			}

			return;

		}

		// No results

	}


	/**
	 * Action: top images
	 */
	public function action_top() {

		// Build page
		$this->view      = View_Page::factory(__('Top 10'));
		$this->view->tab = 'top';
		$this->_set_page_actions(Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_CREATE, self::$user));
		$this->_set_flyer_actions();

		// Top images
		foreach (array(Model_Image::TOP_RATED, Model_Image::TOP_COMMENTED, Model_Image::TOP_VIEWED) as $type) {
			$this->view->add(View_Page::COLUMN_TOP, $this->section_top($type, 12));
		}

	}


	/**
	 * Action: remove note
	 */
	public function action_unnote() {
		$this->history = false;

		/** @var  Model_Image_Note  $note */
		$note_id = (int)$this->request->param('id');
		$note    = new Model_Image_Note($note_id);
		if (!$note->loaded()) {
			throw new Model_Exception($note, $note_id);
		}

		// Permission check
		Permission::required($note, Model_Image_Note::PERMISSION_DELETE, self::$user);

		$image   = $note->image();
		$gallery = $image->gallery();

		$note->delete();
		$image->update_description()->save();

		// Redirect back to image
		// @todo: ajaxify for more graceful approach
		$this->request->redirect(Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id($gallery), 'id' => $image->id, 'action' => '')));
	}


	/**
	 * Action: update
	 */
	public function action_update() {
		$this->history = false;

		/** @var  Model_Gallery  $gallery */
		$gallery_id = (int)$this->request->param('id');
		$gallery = Model_Gallery::factory($gallery_id);
		if (!$gallery->loaded()) {
			throw new Model_Exception($gallery, $gallery_id);
		}

		Permission::required($gallery, Model_Gallery::PERMISSION_UPDATE, self::$user);

		// Update copyrights
		$gallery
			->update_copyright()
			->save();

		// Resize images
		/* Uncomment to create new default size
		foreach ($gallery->images() as $image) {
			try {
				$image->resize(Model_Image::SIZE_WIDE);
			} catch (Kohana_Exception $e) {}
		}
		*/

		$this->request->redirect(Route::model($gallery));
	}


	/**
	 * Action: upload
	 */
	public function action_upload() {

		// Load existing gallery if any
		$gallery_id = (int)$this->request->param('gallery_id');
		if (!$gallery_id) {
			$gallery_id = (int)$this->request->param('id');
		}
		if ($gallery_id) {

			// Existing gallery
			$gallery = Model_Gallery::factory($gallery_id);
			if (!$gallery->loaded()) {
				throw new Model_Exception($gallery, $gallery_id);
			}

		} else {

			// New gallery
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

				// Needed for IE response
				if ($multiple = Arr::get($_REQUEST, 'multiple', false)) {
					$this->auto_render = false;
				}

				// Upload info for JSON
				$info = array();
				$info['name'] = HTML::chars($file['name']);
				$info['size'] = intval($file['size']);

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

					$image = Model_Image::factory();
					$image->normal = 'wide';
					$image->set_fields(array(
						'author_id' => self::$user->id,
						'file'      => $file,
						'created'   => time(),
					));
					$image->save();

					// Save exif
					try {
						$exif = Model_Image_Exif::factory();
						$exif->image_id = $image->id;
						$exif->save();
					} catch (Kohana_Exception $e) {
						throw $e;
					}

					// Set the image as gallery image
					$gallery->relate('images', array($image->id));
					$gallery->image_count++;
					if (!$gallery->default_image_id) {
						$gallery->default_image_id = $image->id;
					}
					$gallery->updated = time();
					$gallery->save();

					// Newsfeed item
					NewsfeedItem_Galleries::upload(self::$user, $gallery);

					// Mark filename as uploaded for current gallery
					$uploaded[$gallery->id][] = $file['name'];
					Session::instance()->set('uploaded', $uploaded);

					// Make sure the user has photo role to be able to see uploaded pictures
					if (!self::$user->has_role('photo')) {
						self::$user->add_role('photo');
					}

					// Show image if uploaded with ajax
					if ($this->ajax || $multiple) {
						$info['url']           = $image->get_url();
						$info['thumbnail_url'] = $image->get_url(Model_Image::SIZE_THUMBNAIL);
						$info['gallery_url']   = Route::url('gallery_image', array(
							'gallery_id' => Route::model_id($gallery),
							'id'         => $image->id,
						));
						$info['delete_url']    = Route::url('gallery_image', array(
							'gallery_id' => Route::model_id($gallery),
							'id'         => $image->id,
							'action'     => 'delete',
						)) . '?token=' . Security::csrf();
						$info['delete_type']   = 'GET';

						$this->response->headers('Content-Type', 'application/json');
						$this->response->body(json_encode($info));

						return;
					}

					$this->request->redirect(Route::model($gallery));

				} catch (Validation_Exception $e) {
					$errors = $e->array->errors('validation');
				} catch (Kohana_Exception $e) {
					$errors = array('file' => $e->getMessage());
				}

				// Show errors if uploading with ajax, skip form
				if (($this->ajax || $multiple) && !empty($errors)) {
					$info['error'] = Arr::get($errors, 'file');
					$this->response->status(400);
					$this->response->headers('Content-Type', 'application/json');
					$this->response->body(json_encode($info));

					return;
				}

			}
		}


		// Build page
		$this->view = View_Page::factory($gallery->name);
		$images = count($gallery->images());
		$this->view->subtitle = __($images == 1 ? ':images image' : ':images images', array(':images' => $images)) . ' - ' . HTML::time(Date::format('DMYYYY', $gallery->date), $gallery->date, true);

		// Upload
		$this->view->add(View_Page::COLUMN_CENTER, $this->section_upload());

		// Help
//		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_upload_help());

	}


	/**
	 * Edit gallery
	 *
	 * @param  integer  $gallery_id
	 * @param  integer  $event_id
	 *
	 * @throws  Model_Exception
	 */
	protected function _edit_gallery($gallery_id = null, $event_id = null) {
		$this->history = false;

		if ($gallery_id) {

			// Editing old
			$gallery = Model_Gallery::factory($gallery_id);
			if (!$gallery->loaded()) {
				throw new Model_Exception($gallery, $gallery_id);
			}
			Permission::required($gallery, Model_Gallery::PERMISSION_UPDATE, self::$user);
			$cancel = Route::model($gallery);
			$save   = null;
			$upload = false;

		} else {

			// Creating new
			$gallery = Model_Gallery::factory();
			Permission::required($gallery, Model_Gallery::PERMISSION_CREATE, self::$user);
			$cancel = Request::back(Route::url('galleries'), true);
			$save   = __('Continue');
			$upload = true;

			if ($event_id) {
				/** @var  Model_Event  $event */
				$event = Model_Event::factory($event_id);
			}
		}

		// Handle post
		$errors = array();
		if ($_POST || isset($_GET['from'])) {
			$event_id = $_POST ? (int)Arr::get($_POST, 'event') : (int)Arr::get($_GET, 'from');
			$event = Model_Event::factory($event_id);

			if (!$gallery->loaded() && $event->loaded()) {

				// Redirect to existing gallery if trying to create duplicate
				$old = Model_Gallery::factory()->find_by_event($event_id);
				if ($old->loaded()) {
					$this->request->redirect(Route::model($old, 'upload'));
				}

				// Fill gallery info from event when creating new
				$gallery->name     = $event->name;
				$gallery->date     = $event->stamp_begin;
				$gallery->event_id = $event->id;
				$gallery->created  = time();

			} else if ($gallery->loaded()) {

				// Editing old
				$gallery->set_fields(Arr::intersect($_POST, Model_Gallery::$editable_fields));

			}

			try {
				$gallery->updated = time();
				$gallery->save();
				$this->request->redirect(Route::model($gallery, $upload ? 'upload' : null));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}


		// Build page
		$this->view = View_Page::factory(__('Upload images'));

		// Gallery edit form
		$section = $this->section_gallery_edit(isset($event) ? $event : null);
		$section->errors = $errors;
		$this->view->add(View_Page::COLUMN_CENTER, $section);

//		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_upload_help());
	}


	/**
	 * Set flyer page actions.
	 *
	 * @param  Model_Flyer  $flyer
	 */
	protected function _set_flyer_actions(Model_Flyer $flyer = null) {
		$this->view->tabs[] = array(
			'link' => Route::get('flyer')->uri(array('id' => 'undated')),
			'text' => '<i class="icon-random"></i> ' . __('Random undated flyer'),
		);
		$this->view->tabs[] = array(
			'link' => Route::get('flyer')->uri(array('id' => 'random')),
			'text' => '<i class="icon-random"></i> ' . __('Random flyer'),
		);

		if ($flyer) {

			// Set browse flyers to
			if ($flyer->stamp_begin) {
				$this->view->tabs['flyers']['link'] = $flyer->has_full_date()
					? Route::url('flyers', array('year' => date('Y', $flyer->stamp_begin), 'month' => date('n', $flyer->stamp_begin)))
					: Route::url('flyers', array('year' => date('Y', $flyer->stamp_begin)));
			}

			Route::url('flyers', array(
				'action' => '',
				'year'   => date('Y', $flyer->stamp_begin),
				'month'  => date('m', $flyer->stamp_begin)
			));

			if ($event = $flyer->event()) {
				$this->view->tabs[] = array(
					'link'  => Route::model($event),
					'text'  => '<i class="icon-calendar"></i> ' . __('Event') . ' &raquo;',
				);
			}
		}
	}


	/**
	 * Set main level page actions.
	 *
	 * @param  boolean  $upload
	 */
	protected function _set_page_actions($upload = false) {
		$this->view->tabs['latest'] = array(
			'link' => Route::url('galleries'),
			'text' => '<i class="icon-camera-retro"></i> ' . __('Latest updates')
		);
		$this->view->tabs['galleries'] = array(
			'link' => Route::url('galleries', array('action' => 'browse')),
			'text' => '<i class="icon-camera-retro"></i> ' . __('Galleries')
		);
		$this->view->tabs['top'] = array(
			'link' => Route::url('galleries', array('action' => 'top')),
			'text' => '<i class="icon-camera-retro"></i> ' . __('Top 10')
		);
		$this->view->tabs['flyers'] = array(
			'link' => Route::url('flyers', array('action' => '')),
			'text' => '<i class="icon-picture"></i> ' . __('Flyers')
		);

		if ($upload) {
			$this->view->actions['upload'] = array(
				'link'  => Route::url('galleries', array('action' => 'upload')),
				'text'  => '<i class="icon-upload"></i> ' . __('Upload images'),
				'class' => 'btn-primary images-add'
			);
		}
	}


	/**
	 * Set gallery specific title and actions.
	 *
	 * @param  Model_Gallery  $gallery
	 */
	protected function _set_gallery(Model_Gallery $gallery) {

		// Set title
		$images = count($gallery->images());
		$this->view->tab      = 'gallery';
		$this->view->title    = $gallery->name;
		$this->view->subtitle = __($images == 1 ? ':images image' : ':images images', array(':images' => $images)) . ' - ' . HTML::time(Date::format('DMYYYY', $gallery->date), $gallery->date, true);

		// Set actions
		$this->view->tabs['galleries']['link'] = Route::url('galleries', array('action' => 'browse', 'year' => date('Y', $gallery->date), 'month' => date('m', $gallery->date)));
		if ($this->view->actions['upload'] && Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_UPLOAD, self::$user)) {
			$this->view->actions['upload']['link'] = Route::model($gallery, 'upload');
		}
		$this->view->tabs['gallery'] = array(
			'link' => Route::model($gallery),
			'text' => '<i class="icon-camera-retro"></i> ' . __('Gallery')
		);
		if ($event = $gallery->event()) {
			$this->view->tabs[] = array(
				'link' => Route::model($event),
				'text' => '<i class="icon-calendar"></i> ' . __('Event') . ' &raquo;'
			);
		}

	}


	/**
	 * DRY helper for random flyer actions
	 */
	protected function _set_random_actions() {
		$this->page_actions[] = array(
			'link' => Route::url('flyer', array('id' => 'undated')),
			'text' => '<i class="icon-random"></i> ' . __('Random undated flyer')
		);
		$this->page_actions[] = array(
			'link' => Route::url('flyer', array('id' => 'random')),
			'text' => '<i class="icon-random"></i> ' . __('Random flyer')
		);
	}


/**
	 * Get side image.
	 *
	 * @param   Model_Event  $event
	 * @return  View_Generic_SideImage
	 */
	protected function section_event_image(Model_Event $event) {

		// Display front flyer by default
		if ($image = $event->flyer_front()) {
			$flyer = Model_Flyer::factory()->find_by_image($image->id);
			$link  = Route::model($flyer);
		} else if (count($flyers = $event->flyers())) {
			$flyer = $flyers[0];
			$image = $flyer->image();
			$link  = Route::model($flyer);
		} else {
			$image = null;
			$link  = null;
		}

		return new View_Generic_SideImage($image, $link);
	}


	/**
	 * Get flyer view.
	 *
	 * @param   Model_Image  $image
	 * @return  View_Flyer_Full
	 */
	public function section_flyer(Model_Image $image) {
		return new View_Flyer_Full($image);
	}


	/**
	 * Get flyer edit view.
	 *
	 * @param   Model_Flyer  $flyer
	 * @return  View_Flyer_Edit
	 */
	public function section_flyer_edit(Model_Flyer $flyer) {
		return new View_Flyer_Edit($flyer);
	}


	/**
	 * Get flyer hover card.
	 *
	 * @param   Model_Flyer  $flyer
	 * @return  View_Flyer_HoverCard
	 */
	public function section_flyer_hovercard(Model_Flyer $flyer) {
		return new View_Flyer_HoverCard($flyer);
	}


	/**
	 * Get flyers' thumb view.
	 *
	 * @param   Model_Flyer[]  $flyers
	 * @return  View_Flyers_Thumbs
	 */
	public function section_flyers_thumbs($flyers) {
		$section = new View_Flyers_Thumbs($flyers);

		return $section;
	}


	/**
	 * Get galleries' thumb view.
	 *
	 * @param   Model_Gallery[]  $galleries
	 * @param   boolean          $years      Show year titles
	 * @return  View_Galleries_Thumbs
	 */
	public function section_galleries_thumbs($galleries, $years = false) {
		$section = new View_Galleries_Thumbs($galleries);
		$section->years = $years;

		return $section;
	}


	/**
	 * Get gallery edit form.
	 *
	 * @param   Model_Event  $event  Bound event, if any
	 * @return  View_Gallery_Edit
	 */
	public function section_gallery_edit(Model_Event $event = null) {
		$section = new View_Gallery_Edit($event);

		// Add suggestions if creating new
		if (!$event) {
			$events = Model_Event::factory()->find_favorites_past(self::$user, 10);
			if ($events && count($events)) {
				$section->events = $events;
			}
		}

		return $section;
	}


	/**
	 * Get empty event gallery.
	 *
	 * @param   Model_Event  $event
	 * @return  View_Alert
	 */
	public function section_gallery_empty(Model_Event $event) {
		$can_upload = Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_CREATE, self::$user);

		$section = new View_Alert(
			__('.. this event seems to be lacking in the image department.')
			. ($can_upload
				? '<br /><br />' . HTML::anchor(
						Route::url('galleries', array('action' => 'upload')) . '?from=' . $event->id,
						'<i class="icon-upload"></i> ' . __('Upload images'),
						array('class' => 'btn btn-primary')
					)
				: ''),
			__('Uh oh..'),
			View_Alert::INFO
		);

		return $section;
	}


	/**
	 * Get external links.
	 *
	 * @param   Model_Gallery  $gallery
	 * @return  View_Gallery_Links
	 */
	public function section_gallery_links(Model_Gallery $gallery) {
		return new View_Gallery_Links($gallery);
	}


	/**
	 * Get gallery upload event info.
	 *
	 * @param   Model_Event  $event
	 * @return  View_Gallery_Event
	 */
	public function gallery_event(Model_Event $event) {
		return new View_Gallery_Event($event);
	}


	/**
	 * Get gallery thumb view.
	 *
	 * @param   Model_Gallery  $gallery
	 * @return  View_Gallery_Thumbs
	 */
	public function section_gallery_thumbs(Model_Gallery $gallery) {
		$section = new View_Gallery_Thumbs($gallery);

		return $section;
	}


	/**
	 * Get image view.
	 *
	 * @param   Model_Image    $image
	 * @param   Model_Gallery  $gallery
	 * @param   string         $url
	 * @return  View_Image_Full
	 */
	public function section_image(Model_Image $image, Model_Gallery $gallery, $url = null) {
		$section = new View_Image_Full($image, $gallery);
		$section->url = $url;

		return $section;
	}


	/**
	 * Get image comments section.
	 *
	 * @param   Model_Image  $image
	 * @param   string       $route
	 * @return  View_Generic_Comments
	 */
	public function section_image_comments(Model_Image $image, $route = 'gallery_image_comment') {
		$section = new View_Generic_Comments($image->comments(self::$user));
		$section->delete  = Route::url($route, array('id' => '%d', 'commentaction' => 'delete')) . '?token=' . Security::csrf();
		$section->private = false;

		return $section;
	}


	/**
	 * Get comment section teaser.
	 *
	 * @param   integer  $comment_count
	 * @return  View_Generic_CommentsTeaser
	 */
	public function section_image_comments_teaser($comment_count = 0) {
		return new View_Generic_CommentsTeaser($comment_count);
	}


	/**
	 * Get image hover card.
	 *
	 * @param   Model_Image    $image
	 * @param   Model_Gallery  $gallery
	 * @return  View_Image_HoverCard
	 */
	public function section_image_hovercard(Model_Image $image, Model_Gallery $gallery) {
		return new View_Image_HoverCard($image, $gallery);
	}


	/**
	 * Get image info view.
	 *
	 * @param   Model_Image  $image
	 * @return  View_Image_Info
	 */
	public function section_image_info(Model_Image $image) {
		return new View_Image_Info($image);
	}


	/**
	 * Get image pagination.
	 *
	 * @param   string   $previous_url
	 * @param   string   $next_url
	 * @param   integer  $current
	 * @param   integer  $total
	 * @return  View_Generic_Pagination
	 */
	public function section_image_pagination($previous_url, $next_url) {
		$section = new View_Generic_Pagination(array(
			'previous_url'  => $previous_url,
			'next_url'      => $next_url,
			'previous_text' => '&lsaquo; ' . __('Previous image'),
			'next_text'     => __('Next image') . ' &rsaquo;',
		));
		$section->class = 'sticky';

		return $section;
	}


	/**
	 * Get image report form.
	 *
	 * @param   Model_Image  $image
	 * @return  View_Image_Report
	 */
	public function section_image_report(Model_Image $image) {
		return new View_Image_Report($image);
	}


	/**
	 * Get months browser.
	 *
	 * @param   array    $months
	 * @param   string   $route
	 * @param   string   $action
	 * @param   integer  $year
	 * @param   integer  $month
	 * @return  View_Generic_Months
	 */
	public function section_month_browser(array $months, $route = 'galleries', $action = 'browse', $year = null, $month = null) {
		$section = new View_Generic_Months($months, $route, array('action' => $action));
		$section->aside = true;
		$section->year  = $year;
		$section->month = $month;

		return $section;
	}


	/**
	 * Get pagination.
	 *
	 * @param   array    $months
	 * @param   string   $route
	 * @param   string   $action
	 * @param   integer  $year
	 * @param   integer  $month
	 * @return  View_Generic_Pagination
	 */
	public function section_month_pagination(array $months, $route, $action, $year, $month) {

		// Previous
		if (isset($months[$year][$month - 1])) {
			$previous_year  = $year;
			$previous_month = $month - 1;
		} else if (isset($months[$year - 1])) {
			$previous_year  = $year - 1;
			$_months        = array_keys($months[$previous_year]);
			$previous_month = reset($_months);
		} else {
			$previous_year  = $previous_month = null;
		}

		// Next
		if (isset($months[$year][$month + 1])) {
			$next_year  = $year;
			$next_month = $month + 1;
		} else if (isset($months[$year + 1])) {
			$next_year  = $year + 1;
			$next_month = array_keys($months[$next_year]);
			$next_month = $next_month[count($next_month) - 1];
		} else {
			$next_year  = $next_month = null;
		}

		return new View_Generic_Pagination(array(
			'previous_text' => '&laquo; ' . __('Previous month'),
			'next_text'     => __('Next month') . ' &raquo;',
			'previous_url'  => $previous_month
				? Route::url($route, array(
						'action' => $action,
						'year'   => $previous_year,
						'month'  => $previous_month,
					))
				: false,
			'next_url'  => $next_month
				? Route::url($route, array(
						'action' => $action,
						'year'   => $next_year,
						'month'  => $next_month,
					))
				: false,
		));
	}


	/**
	 * Get top images.
	 *
	 * @param   string   $type
	 * @param   integer  $top
	 * @param   integer  $user_id
	 * @return  View_Gallery_Top
	 */
	public function section_top($type, $top = 10, $user_id = null) {
		return new View_Gallery_Top($type, Model_Image::factory()->find_top($type, $top, null, $user_id));
	}


	/**
	 * Get image search results.
	 *
	 * @param   array  $images
	 * @return  View_Galleries_Search
	 */
	public function section_search_results(array $images = null) {
		return new View_Galleries_Search($images);
	}


	/**
	 * Get upload view.
	 *
	 * @return  View_Image_Upload
	 */
	public function section_upload() {
		$section = new View_Image_Upload();

		return $section;
	}


	/**
	 * Get upload help.
	 *
	 * @return  View_Image_UploadHelp
	 */
	public function section_upload_help() {
		return new View_Image_UploadHelp();
	}

}

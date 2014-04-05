<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh User controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_User extends Controller_Page {

	/**
	 * Action: comment
	 */
	public function action_comment() {
		$comment_id = (int)$this->request->param('id');
		$action     = $this->request->param('commentaction');

		// Load blog_comment
		$comment = Model_User_Comment::factory($comment_id);
		if (($action == 'delete' || $action == 'private') && Security::csrf_valid() && $comment->loaded()) {
			$user = Model_User::find_user($comment->user_id);
			switch ($action) {

				// Delete comment
				case 'delete':
			    if (Permission::has($comment, Model_User_Comment::PERMISSION_DELETE, self::$user)) {
				    $comment->delete();
				    $user->comment_count--;
				    $user->save();
			    }
			    break;

				// Set comment as private
			  case 'private':
				  if (Permission::has($comment, Model_User_Comment::PERMISSION_UPDATE, self::$user)) {
					  $comment->private = true;
					  $comment->save();
				  }
			    break;

			}
			if (!$this->ajax) {
				$this->request->redirect(Route::get('user')->uri(array('username' => urlencode($user->username))));
			}
		}

		if (!$this->ajax) {
			Request::back(Route::get('users')->uri());
		}
	}


	/**
	 * Action: List favorite events
	 */
	public function action_favorites() {
		$user = $this->_get_user();

		// Build page
		$this->view = self::_set_page($user);

		$this->view->tab       = 'favorites';
		$this->view->actions[] = array(
			'link'  => URL::site(Route::url('ical_favorites', array('username' => urlencode($user->username))), 'webcal'),
			'text'  => '<i class="icon-download-alt"></i> ' . __('Download as .ics'),
		);

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_favorites($user));

	}


	/**
	 * Action: favorites in iCalendar format
	 */
	public function action_favorites_ical() {
		$this->auto_render = false;
		$this->history     = false;

		$user = $this->_get_user();

		// Proper headers
		$this->response->headers(array(
			'Content-Type'        => 'text/calendar; charset=utf-8',
			'Content-Disposition' => 'inline; filename=favorites.ics'
		));

		// Create iCalendar
		$icalendar = new View_iCalendar();

		// Load favorites
		$upcoming  = Model_Event::factory()->find_favorites_upcoming($user, 0, 'DESC');
		$past      = Model_Event::factory()->find_favorites_past($user, 0);
		$favorites = array();
		foreach ($upcoming as $event) {
			$favorites[] = new View_Event_vEvent($event);
		}
		foreach ($past as $event) {
			$favorites[] = new View_Event_vEvent($event);
		}
		$icalendar->events  = $favorites;
		$icalendar->calname = Kohana::$config->load('site.site_name');

		$this->response->body($icalendar->render());
	}


	/**
	 * Action: Add to friends
	 */
	public function action_friend() {
		$this->history = false;

		// Load user
		$user = $this->_get_user();
		Permission::required($user, Model_User::PERMISSION_FRIEND, self::$user);

		if (Security::csrf_valid()) {
			self::$user->add_friend($user);

			// News feed
			NewsfeedItem_User::friend(self::$user, $user);

			// Notification
			Notification_User::friend(self::$user, $user);

		}

		// Ajax requests show friend
		if ($this->_request_type === Controller::REQUEST_AJAX) {
			$this->response->body($this->section_friend($user));

			return;
		}

		$this->request->redirect(URL::user($user));
	}


	/**
	 * Action: List friends
	 */
	public function action_friends() {
		$user = $this->_get_user();

		// Build page
		$this->view = self::_set_page($user);

		$this->view->tab = 'friends';

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_friends($user, Arr::get($_GET, 'of') == 'me'));

		// Show suggestions on our own page
		if ($user->id === self::$user->id) {
			$this->view->add(View_Page::COLUMN_RIGHT, $this->section_friend_suggestions($user));
		}

	}


	/**
	 * Action: hover card
	 */
	public function action_hover() {
		$this->history = false;

		// Hover card works only with ajax
		if ($this->_request_type !== Controller::REQUEST_AJAX) {
			$this->action_index();

			return;
		}

		if ($user = Model_User::find_user_light(urldecode((string)$this->request->param('username'))))	{
			$this->response->body(new View_User_HoverCard($user));
		} else {
			$this->response->body(__('Member not found o_O'));
		}
	}


	/**
	 * Action: Add to ignore
	 */
	public function action_ignore() {
		$this->history = false;

		// Load user
		$user = $this->_get_user();
		Permission::required($user, Model_User::PERMISSION_IGNORE, self::$user);

		if (Security::csrf_valid()) {
			self::$user->add_ignore($user);
		}

		$this->request->redirect(URL::user($user));
	}


	/**
	 * Action: ignores
	 */
	public function action_ignores() {
		$user = $this->_get_user();

		// Build page
		$this->view = self::_set_page($user);
		$this->view->tab = 'ignores';

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_ignores($user));

	}


	/**
	 * Action: image
	 */
	public function action_image() {
		$this->history = false;

		$user = $this->_get_user();
		Permission::required($user, Model_User::PERMISSION_UPDATE, self::$user);

		// Change default image
		if ($image_id = (int)Arr::get($_REQUEST, 'default')) {
			/** @var  Model_Image  $image */
			$image = Model_Image::factory($image_id);
			if (Security::csrf_valid() && $image->loaded() && $user->has('images', $image->id)) {
				$user->default_image_id = $image->id;
				$user->picture          = $image->get_url();
				$user->save();
			}
			$cancel = true;
		}

		// Delete existing
		if ($image_id = Arr::get($_REQUEST, 'delete')) {
			if ($image_id === 'facebook') {

				// Clear Facebook image
				if (Security::csrf_valid()) {
					$user->picture = null;
					$user->save();
				}

			} else if ((int)$image_id) {

				// Delete normal profile image
				/** @var  Model_Image  $image */
				$image = Model_Image::factory((int)$image_id);
				if (Security::csrf_valid() && $image->loaded() && $user->has('images', $image->id)) {
					$user->remove('image', $image->id);
					if ($image->id === $user->default_image_id) {
						$user->default_image_id = null;
						$user->picture          = null;
					}
					$user->save();
					$image->delete();
				}

			}
			$cancel = true;
		}

		// Cancel change
		if (isset($cancel) || isset($_REQUEST['cancel'])) {
			$this->request->redirect(URL::user($user));
		}

		$image = Model_Image::factory();
		$image->author_id = $user->id;
		$image->created   = time();

		// Handle post
		$errors = array();
		if ($_POST && $_FILES) {
			$image->file = Arr::get($_FILES, 'file');
			try {
				$image->save();

				// Add exif, silently continue if failed - not critical
				try {
					$exif = Model_Image_Exif::factory();
					$exif->image_id = $image->id;
					$exif->save();
				} catch (Kohana_Exception $e) { }

				// Set the image as user image
				$user->relate('images', array($image->id));
				$user->default_image_id = $image->id;
				$user->picture          = $image->get_url(); // @TODO: Legacy, will be removed after migration
				$user->save();

				// Newsfeed
				NewsfeedItem_User::default_image($user, $image);

				$this->request->redirect(URL::user($user));

			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			} catch (Kohana_Exception $e) {
				$errors = array('file' => __('Failed with image'));
			}
		}


		// Build page
		$this->view = self::_set_page($user);
		$this->view->tab = 'profile';

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_upload(URL::user($user), $errors));
	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$user = $this->_get_user();

		// Helper variables
		$owner = (self::$user && self::$user->id == $user->id);

		// Comments section
		if (Permission::has($user, Model_User::PERMISSION_COMMENTS, self::$user)) {
			$errors = array();
			$values = array();

			// Handle comment
			if (Permission::has($user, Model_User::PERMISSION_COMMENT, self::$user) && $_POST) {
				try {
					$comment = Model_User_Comment::factory()
						->add(self::$user->id, $user->id, Arr::get($_POST, 'comment'), Arr::get($_POST, 'private'));

					// Receiver
					$user->comment_count++;
					if (!$owner) {
						$user->new_comment_count++;
					}
					$user->save();

					// Sender
					self::$user->left_comment_count++;
					self::$user->save();

					if ($this->_request_type !== Controller::REQUEST_AJAX) {
						$this->request->redirect(Route::url('user', array('username' => urlencode($user->username))));

						return;
					}

				} catch (Validation_Exception $e) {
					$errors = $e->array->errors('validation');
					$values = $comment;
				}

			}

			// Mark own comments read
			if ($owner) {
				$user->mark_comments_read();
			}

			$section_comments = $this->section_comments($user, 'user_comment');
			$section_comments->errors = $errors;
			$section_comments->values = $values;

		} else if (!self::$user) {

			// Teaser for guests
			$section_comments = $this->section_comments_teaser($user->comment_count);

		}

		if (isset($section_comments) && $this->_request_type === Controller::REQUEST_AJAX) {
			$this->response->body($section_comments);

			return;
		}


		// Build page
		$this->view = self::_set_page($user);
		$this->view->tab = 'profile';

		// Owner / admin actions
		if (Permission::has($user, Model_User::PERMISSION_UPDATE, self::$user)) {
			$this->view->actions[] = array(
				'link'  => URL::user($user, 'image'),
				'text'  => '<i class="icon-picture"></i> ' . __('Add image'),
			);
		}

		// Newsfeed
		$this->view->add(View_Page::COLUMN_CENTER, $this->section_newsfeed($user));

		// Comments
		if (isset($section_comments)) {
			$this->view->add(View_Page::COLUMN_CENTER, $section_comments);
		}

		// Portrait
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_carousel($user));

		// Info
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_info($user));

	}


	/**
	 * Action: settings
	 */
	public function action_settings() {
		$this->history = false;

		$user = $this->_get_user();
		Permission::required($user, Model_User::PERMISSION_UPDATE, self::$user);

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			$user->set_fields(Arr::intersect($_POST, Model_User::$editable_fields));

			// Clear default image id if Facebook image is set
			if (Arr::get($_POST, 'picture')) {
				$user->default_image_id = null;
			}

			$user->modified = time();

			try {
				$user->save();
				$this->request->redirect(URL::user($user));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}


		// Build page
		$this->view = self::_set_page($user);
		$this->view->tab = 'settings';

		$this->view->add(View_Page::COLUMN_TOP, $this->section_settings($user, $errors));
	}


	/**
	 * Action: Remove from friends
	 */
	public function action_unfriend() {
		$this->history = false;

		// Load user
		$user = $this->_get_user();
		Permission::required($user, Model_User::PERMISSION_FRIEND, self::$user);

		if (Security::csrf_valid()) {
			self::$user->delete_friend($user);
		}

		$this->request->redirect(URL::user($user));
	}


	/**
	 * Action: Remove from ignore
	 */
	public function action_unignore() {
		$this->history = false;

		// Load user
		$user = $this->_get_user();
		Permission::required($user, Model_User::PERMISSION_IGNORE, self::$user);

		if (Security::csrf_valid()) {
			self::$user->delete_ignore($user);
		}

		$this->request->redirect(URL::user($user));
	}


	/**
	 * Get user or redirect to user list
	 *
	 * @param   boolean  $redirect
	 * @return  Model_User
	 */
	protected function _get_user($redirect = true) {

		// Get our user, default to logged in user if no username given
		$username = urldecode((string)$this->request->param('username'));
		$user = ($username == '') ? self::$user : Model_User::find_user($username);
		if (!$user && $redirect)	{
			$this->request->redirect(Route::get('users')->uri());
		}

		return $user;
	}


	/**
	 * Build user page with generic parameters.
	 *
	 * @param   Model_User  $user
	 * @return  View_Page
	 */
	public static function _set_page(Model_User $user) {

		// Build page
		$view = new View_Page($user->username);
		if ($user->name) {
//			$this->view->title_html = HTML::chars($user->username) . ' <small>' . HTML::chars($user->name) . '</small>';
		}
		if ($user->title) {
			$view->subtitle = HTML::chars($user->title);
		}

		// Set actions
		if (self::$user) {

			// Friend actions
			if (Permission::has($user, Model_User::PERMISSION_FRIEND, self::$user)) {
				if (self::$user->is_friend($user)) {
					$view->actions[] = array(
						'link'  => URL::user($user, 'unfriend') . '?token=' . Security::csrf(),
						'text'  => '<i class="icon-heart"></i> ' . __('Remove friend'),
						'class' => 'btn-inverse friend-delete'
					);
				} else {
					$view->actions[] = array(
						'link'  => URL::user($user, 'friend') . '?token=' . Security::csrf(),
						'text'  => '<i class="icon-heart"></i> ' . __('Add to friends'),
						'class' => 'btn-lovely friend-add'
					);
				}
			}

			// Ignore actions
			if (Permission::has($user, Model_User::PERMISSION_IGNORE, self::$user)) {
				if (self::$user->is_ignored($user)) {
					$view->actions[] = array(
						'link'  => URL::user($user, 'unignore') . '?token=' . Security::csrf(),
						'text'  => '<i class="icon-ban-circle"></i> ' . __('Unignore'),
						'class' => 'btn-inverse ignore-delete'
					);
				} else {
					$view->actions[] = array(
						'link'  => URL::user($user, 'ignore') . '?token=' . Security::csrf(),
						'text'  => '<i class="icon-ban-circle"></i> ' . __('Ignore'),
					);
				}
			}

			$view->tabs['profile'] = array(
				'link'  =>  URL::user($user),
				'text'  => '<i class="icon-user"></i> ' . __('Profile'),
			);
			$view->tabs['favorites'] = array(
				'link'  =>  URL::user($user, 'favorites'),
				'text'  => '<i class="icon-calendar"></i> ' . __('Favorites'),
			);
			$view->tabs['friends'] = array(
				'link'  =>  URL::user($user, 'friends'),
				'text'  => '<i class="icon-heart"></i> ' . __('Friends'),
			);

			// Owner / admin actions
			if (Permission::has($user, Model_User::PERMISSION_UPDATE, self::$user)) {
				$view->tabs['ignores'] = array(
					'link'  =>  URL::user($user, 'ignores'),
					'text'  => '<i class="icon-ban-circle"></i> ' . __('Ignores'),
				);
				$view->tabs['settings'] = array(
					'link'  =>  URL::user($user, 'settings'),
					'text'  => '<i class="icon-cog"></i> ' . __('Settings'),
				);
			}

			// Photographer profile
			$view->tabs['photographer'] = array(
				'link' => Route::url('photographer', array('username' => urlencode($user->username))),
				'text' => '<i class="icon-camera-retro"></i> ' . __('Photographer') . ' &raquo;',
			);

			// Blog
			$view->tabs['blog'] = array(
				'link' => Route::url('blog_user', array('username' => urlencode($user->username))),
				'text' => '<i class="icon-book"></i> ' . __('Blog') . ' &raquo;',
			);
		}

		return $view;
	}


	/**
	 * Get image slideshow.
	 *
	 * @param   Model_User  $user
	 * @return  View_User_Carousel
	 */
	public function section_carousel(Model_User $user) {
		return new View_User_Carousel($user);
	}


	/**
	 * Get comments section.
	 *
	 * @param   Model_User   $user
	 * @param   string       $route
	 * @return  View_Generic_Comments
	 */
	public function section_comments(Model_User $user, $route = 'user_comment') {

		// Pagination
		$per_page = 25;
		$pagination = new View_Generic_Pagination(array(
			'base_url'       => URL::user($user),
			'items_per_page' => $per_page,
			'total_items'    => max(1, count($user->comments(self::$user, null))),
		));

		$section = new View_Generic_Comments($user->comments(self::$user, $pagination));
		$section->delete       = Route::url($route, array('id' => '%d', 'commentaction' => 'delete')) . '?token=' . Security::csrf();
		$section->private      = Route::url($route, array('id' => '%d', 'commentaction' => 'private')) . '?token=' . Security::csrf();
		$section->new_comments = self::$user && self::$user->id === $user->id ? $user->new_comment_count : null;
		$section->pagination   = $pagination;

		return $section;
	}


	/**
	 * Get comment section teaser.
	 *
	 * @param   integer  $comment_count
	 * @return  View_Generic_CommentsTeaser
	 */
	public function section_comments_teaser($comment_count = 0) {
		return new View_Generic_CommentsTeaser($comment_count);
	}


	/**
	 * Get favorite events timeline.
	 *
	 * @param   Model_User  $user
	 * @return  View_Events_Timeline
	 */
	public function section_favorites(Model_User $user) {
		$upcoming = Model_Event::factory()->find_favorites_upcoming($user, 0, 'DESC');
		$past     = Model_Event::factory()->find_favorites_past($user, 0);

		$favorites = array();
		foreach ($upcoming as $event) {
			$favorites[] = $event;
		}
		foreach ($past as $event) {
			$favorites[] = $event;
		}

		return new View_Events_Timeline($favorites);
	}


	/**
	 * Get single friend from friend list.
	 *
	 * @param  Model_User  $user
	 */
	public function section_friend(Model_User $user) {
		return new View_Users_Friend($user);
	}


	/**
	 * Get friend suggestions.
	 *
	 * @param   Model_User  $user
	 * @return  View_Users_FriendSuggestions
	 */
	public function section_friend_suggestions(Model_User $user) {
		return new View_Users_FriendSuggestions($user);
	}


	/**
	 * Get friends list.
	 *
	 * @param   Model_User  $user
	 * @param   boolean     $friended  People who friended user
	 * @return  View_Users_Friends
	 */
	public function section_friends(Model_User $user, $friended = false) {
		return new View_Users_Friends($user, $friended);
	}


	/**
	 * Get ignore list.
	 *
	 * @param   Model_User  $user
	 * @return  View_Users_Ignores
	 */
	public function section_ignores(Model_User $user) {
		return new View_Users_Ignores($user);
	}


	/**
	 * Get user info.
	 *
	 * @param   Model_User  $user
	 * @return  View_User_Info
	 */
	public function section_info(Model_User $user) {
		return new View_User_Info($user);
	}


	/**
	 * Get newsfeed.
	 *
	 * @param   Model_User  $user
	 * @return  View_Newsfeed
	 */
	public function section_newsfeed(Model_User $user) {
		$section = new View_Newsfeed();
		$section->type  = View_Newsfeed::TYPE_PERSONAL;
		$section->user  = $user;
		$section->mini  = true;
		$section->limit = 5;

		return $section;
	}


	/**
	 * Get settings.
	 *
	 * @param   Model_User $user
	 * @param   array      $errors
	 * @return  View_User_Settings
	 */
	public function section_settings(Model_User $user, array $errors = null) {
		return new View_User_Settings($user, $errors);
	}


	/**
	 * Get image upload.
	 *
	 * @param   string  $cancel  URL
	 * @param   array   $errors
	 * @return  View_Generic_Upload
	 */
	public function section_upload($cancel = null, $errors = null) {
		$section = new View_Generic_Upload();
		$section->title  = __('Add profile image');
		$section->cancel = $cancel;
		$section->errors = $errors;

		return $section;
	}

}

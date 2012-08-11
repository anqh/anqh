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

		}

		$this->request->redirect(URL::user($user));
	}


	/**
	 * Action: List friends
	 */
	public function action_friends() {
		$user = $this->_get_user();

		// Set generic page parameters
		$this->_set_page($user);

		// Helper variables
		$owner = (self::$user && self::$user->id == $user->id);

		// Get friends and order by nick
		// @todo: needs serious optimization
		$friends = array();
	  foreach ($user->find_friends() as $friend_id) {
		  $friend = Model_User::find_user_light($friend_id);
		  $friends[$friend['username']] = $friend;
	  }
	  ksort($friends, SORT_LOCALE_STRING);

	  Widget::add('main', View_Module::factory('user/friends', array(
			'mod_title' => __('Friends'),
			'friends'   => $friends,
	  )));
	}


	/**
	 * Action: hover card
	 */
	public function action_hover() {

		// Hover card works only with ajax
		if (!$this->ajax) {
			return $this->action_index();
		}

		if ($user = Model_User::find_user_light(urldecode((string)$this->request->param('username'))))	{
			echo View_Module::factory('user/hovercard', array(
				'mod_title' => HTML::chars($user['username']) . ' <small>#' . $user['id'] . '</small>',
				'user'      => $user
			));
		} else {
			echo __('Member not found o_O');
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

		// Set generic page parameters
		$this->_set_page($user);

		// Helper variables
		$owner = (self::$user && self::$user->id == $user->id);

		// Get friends and order by nick
		// @todo: needs serious optimization
		$ignores = array();
	  foreach ($user->find_ignores() as $ignore_id) {
		  $ignore = Model_User::find_user_light($ignore_id);
		  $ignores[$ignore['username']] = $ignore;
	  }
	  ksort($ignores, SORT_LOCALE_STRING);

	  Widget::add('main', View_Module::factory('user/ignores', array(
			'mod_title' => __('Ignores'),
			'ignores'   => $ignores,
	  )));
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
		if ($image_id = (int)Arr::get($_REQUEST, 'delete')) {
			/** @var  Model_Image  $image */
			$image = Model_Image::factory($image_id);
			if (Security::csrf_valid() && $image->loaded() && $user->has('images', $image->id)) {
				$user->remove('image', $image->id);
				if ($image->id === $user->default_image_id) {
					$user->default_image_id = null;
					$user->picture          = null;
				}
				$user->save();
				$image->delete();
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
		$this->_set_page($user);

		$this->view->add(View_Page::COLUMN_MAIN, $this->section_upload(URL::user($user), $errors));
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

		} else {

			// Teaser for guests
			$section_comments = $this->section_comments_teaser($user->comment_count);

		}

		if (isset($section_comments) && $this->_request_type === Controller::REQUEST_AJAX) {
			$this->response->body($section_comments);

			return;
		}


		// Build page
		$this->_set_page($user);

		// Newsfeed
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_newsfeed($user));

		// Comments
		$this->view->add(View_Page::COLUMN_MAIN, $section_comments);

		// Portrait
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_carousel($user));

		// Info
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_info($user));

	}


	/**
	 * Action: settings
	 */
	public function action_settings() {
		$this->history = false;

		$user = $this->_get_user();
		Permission::required($user, Model_User::PERMISSION_UPDATE, self::$user);

		// Set generic page parameters
		$this->_set_page($user);

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			$user->set_fields(Arr::intersect($_POST, Model_User::$editable_fields));

			// GeoNames
			if ($_POST['city_id'] && $city = Geo::find_city((int)$_POST['city_id'])) {
				$user->geo_city_id = $city->id;
			}

			$user->modified = time();

			try {
				$user->save();
				$this->request->redirect(URL::user($user));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}

		Widget::add('main', View_Module::factory('user/settings', array(
			'user'   => $user,
			'errors' => $errors,
		)));

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
	 * Get image mod
	 *
	 * @param   Model_User  $user
	 * @return  View_Module
	 */
	protected function _get_mod_image(Model_User $user) {
		$image = $user->get_image_url();

		return View_Module::factory('generic/side_image', array(
			'mod_actions2' => Permission::has($user, Model_User::PERMISSION_UPDATE, self::$user)
				? array(
						array('link' => URL::user($user, 'image') . '?token=' . Security::csrf() . '&delete', 'text' => __('Delete'), 'class' => 'image-delete disabled'),
						array('link' => URL::user($user, 'image') . '?token=' . Security::csrf() . '&default', 'text' => __('Set as default'), 'class' => 'image-change disabled', 'data-change' => 'default'),
						array('link' => URL::user($user, 'image'), 'text' => __('Add image'), 'class' => 'image-add ajaxify')
					)
				: null,
			'image' => $image,
		));
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
	 * Set generic page parameters
	 *
	 * @param   Model_User  $user
	 */
	protected function _set_page(Model_User $user) {

		// Build page
		$this->view = new View_Page($user->username);
		if ($user->name) {
			$this->view->title_html = HTML::chars($user->username) . ' <small>' . HTML::chars($user->name) . '</small>';
		}
		if ($user->title) {
			$this->view->subtitle = HTML::chars($user->title);
		}

		// Set actions
		if (self::$user) {
			if (Permission::has($user, Model_User::PERMISSION_UPDATE, self::$user)) {
				$this->page_actions[] = array(
					'link'  =>  URL::user($user, 'settings'),
					'text'  => '<i class="icon-cog"></i> ' . __('Settings'),
					'class' => 'btn'
				);
				$this->page_actions[] = array(
					'link'  => URL::user($user, 'image'),
					'text'  => '<i class="icon-picture"></i> ' . __('Add image'),
					'class' => 'btn'
				);
			}

			// Friend actions
			if (Permission::has($user, Model_User::PERMISSION_FRIEND, self::$user)) {
				if (self::$user->is_friend($user)) {
					$this->page_actions[] = array(
						'link'  => URL::user($user, 'unfriend') . '?token=' . Security::csrf(),
						'text'  => '<i class="icon-heart"></i> ' . __('Remove friend'),
						'class' => 'btn friend-delete'
					);
				} else {
					$this->page_actions[] = array(
						'link'  => URL::user($user, 'friend') . '?token=' . Security::csrf(),
						'text'  => '<i class="icon-heart icon-white"></i> ' . __('Add to friends'),
						'class' => 'btn btn-primary friend-add'
					);
				}
			}

			// Ignore actions
			if (Permission::has($user, Model_User::PERMISSION_IGNORE, self::$user)) {
				if (self::$user->is_ignored($user)) {
					$this->page_actions[] = array(
						'link'  => URL::user($user, 'unignore') . '?token=' . Security::csrf(),
						'text'  => '<i class="icon-ban-circle"></i> ' . __('Unignore'),
						'class' => 'btn ignore-delete'
					);
				} else {
					$this->page_actions[] = array(
						'link'  => URL::user($user, 'ignore') . '?token=' . Security::csrf(),
						'text'  => '<i class="icon-ban-circle"></i> ' . __('Ignore'),
						'class' => 'btn ignore-add'
					);
				}
			}

		}

	}


	/**
	 * Get image slideshow.
	 *
	 * @param   Model_User  $user
	 * @return  View_Generic_Carousel
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

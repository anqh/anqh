<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh User controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_User extends Controller_Template {

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

		if (!$this->ajax) {
			$this->_set_page($user);
		}

		// Change existing
		if (isset($_REQUEST['default'])) {
			/** @var  Model_Image  $image */
			$image = Model_Image::factory((int)$_REQUEST['default']);
			if (Security::csrf_valid() && $image->loaded() && $user->has('images', $image->id)) {
				$user->default_image_id = $image->id;
				$user->picture          = $image->get_url();
				$user->save();
			}
			$cancel = true;
		}

		// Delete existing
		if (isset($_REQUEST['delete'])) {
			/** @var  Model_Image  $image */
			$image = Model_Image::factory((int)$_REQUEST['delete']);
			if (Security::csrf_valid() && $image->loaded() && $image->id != $user->default_image_id && $user->has('images', $image->id)) {
				$user->remove('images', $image->id);
				$user->picture = null;
				$user->save();
				$image->delete();
			}
			$cancel = true;
		}

		// Cancel change
		if (isset($cancel) || isset($_REQUEST['cancel'])) {
			if ($this->ajax) {
				$this->response->body($this->_get_mod_image($user));
				return;
			}

			$this->request->redirect(URL::user($user));
		}

		$image = Model_Image::factory();
		$image->author_id = $user->id;
		$image->created   = time();

		// Handle post
		$errors = array();
		if ($_POST && $_FILES && Security::csrf_valid()) {
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
				$user->relate('images', $image->id);
				$user->default_image_id = $image->id;
				$user->picture          = $image->get_url(); // @TODO: Legacy, will be removed after migration
				$user->save();

				// Newsfeed
				NewsfeedItem_User::default_image($user, $image);

				if ($this->ajax) {
					$this->response->body($this->_get_mod_image($user));
					return;
				}

				$this->request->redirect(URL::user($user));

			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			} catch (Kohana_Exception $e) {
				$errors = array('file' => __('Failed with image'));
			}
		}

		// Build form
		// @todo Create form!
		$form = array(
			'ajaxify'    => $this->ajax,
			'values'     => $image,
			'errors'     => $errors,
			'attributes' => array('enctype' => 'multipart/form-data'),
			'cancel'     => $this->ajax ? URL::user($user, 'image') . '?cancel' : URL::user($user),
			'groups'     => array(
				array(
					'fields' => array(
						'file' => array(),
					),
				),
			)
		);

		$view = View_Module::factory('form/anqh', array(
			'mod_title' => __('Add image'),
			'form'      => $form
		));

		if ($this->ajax) {
			echo $view;
			return;
		}

		Widget::add('main', $view);
	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$user = $this->_get_user();

		// Set generic page parameters
		$this->_set_page($user);

		// Helper variables
		$owner = (self::$user && self::$user->id == $user->id);

		// Comments section
		if (Permission::has($user, Model_User::PERMISSION_COMMENTS, self::$user)) {
			$errors = array();
			$values = array();

			// Handle comment
			if (Permission::has($user, Model_User::PERMISSION_COMMENT, self::$user) && $_POST) {
				$comment = Model_User_Comment::factory();
				$comment->set_fields(Arr::intersect($_POST, Model_User_Comment::$editable_fields));
				$comment->user_id   = $user->id;
				$comment->author_id = self::$user->id;
				$comment->created   = time();
				try {
					$comment->save();

					// Receiver
					$user->comment_count++;
					if (!$owner) {
						$user->new_comment_count++;
					}
					$user->save();

					// Sender
					self::$user->left_comment_count++;
					self::$user->save();

					if (!$this->ajax) {
						$this->request->redirect(Route::url('user', array('username' => urlencode($user->username))));
					}
				} catch (Validation_Exception $e) {
					$errors = $e->array->errors('validation');
					$values = $comment;
				}

			}

			// Pagination
			$per_page = 25;
			$pagination = Pagination::factory(array(
				'url'            => URL::user($user),
				'items_per_page' => $per_page,
				'total_items'    => max(1, count($user->comments(self::$user, null))),
			));

			$view = View_Module::factory('generic/comments', array(
				'mod_title'  => __('Comments'),
				'delete'     => Route::url('user_comment', array('id' => '%d', 'commentaction' => 'delete')) . '?' . Security::csrf_query(),
				'private'    => Route::url('user_comment', array('id' => '%d', 'commentaction' => 'private')) . '?' . Security::csrf_query(),
				'comments'   => $user->comments(self::$user, $pagination),
				'errors'     => $errors,
				'values'     => $values,
				'pagination' => $pagination,
				'user'       => self::$user,
			));

			if ($this->ajax) {
				$this->response->body($view);
				return;
			}
			Widget::add('main', $view, Widget::BOTTOM);
		}

		// Display news feed
		$newsfeed = new NewsFeed($user, Newsfeed::PERSONAL);
		$newsfeed->max_items = 5;
		Widget::add('main', View_Module::factory('generic/newsfeed', array(
			'newsfeed' => $newsfeed->as_array(),
			'mini'     => true
		)), Widget::TOP);

		// Slideshow
		if (count($user->images) > 1) {
			$images = array();
			foreach ($user->images as $image) $images[] = $image;
			Widget::add('side', View_Module::factory('generic/image_slideshow', array(
				'images'     => array_reverse($images),
				'classes'    => array($user->default_image->id => 'default active'),
			)));
		}

		// Portrait
		Widget::add('side', $this->_get_mod_image($user));

		// Info
		Widget::add('side', View_Module::factory('user/info', array(
			'user' => $user,
		)));

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

		// Set page title
		$this->page_title = HTML::chars($user->username);
		if ($user->title) {
			$this->page_subtitle = HTML::chars($user->title);
		}

		// Set actions
		if (self::$user) {
			if (Permission::has($user, Model_User::PERMISSION_UPDATE, self::$user)) {
				$this->page_actions[] = array('link' => URL::user($user, 'settings'), 'text' => __('Settings'), 'class' => 'settings');
			}

			// Friend actions
			if (Permission::has($user, Model_User::PERMISSION_FRIEND, self::$user)) {
				if (self::$user->is_friend($user)) {
					$this->page_actions[] = array('link' => URL::user($user, 'unfriend') . '?token=' . Security::csrf(), 'text' => __('Remove friend'), 'class' => 'friend-delete');
				} else {
					$this->page_actions[] = array('link' => URL::user($user, 'friend') . '?token=' . Security::csrf(), 'text' => __('Add to friends'), 'class' => 'friend-add');
				}
			}

			// Ignore actions
			if (Permission::has($user, Model_User::PERMISSION_IGNORE, self::$user)) {
				if (self::$user->is_ignored($user)) {
					$this->page_actions[] = array('link' => URL::user($user, 'unignore') . '?token=' . Security::csrf(), 'text' => __('Unignore'), 'class' => 'ignore-delete');
				} else {
					$this->page_actions[] = array('link' => URL::user($user, 'ignore') . '?token=' . Security::csrf(), 'text' => __('Ignore'), 'class' => 'ignore-add');
				}
			}

		}

	}

}

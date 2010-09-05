<?php defined('SYSPATH') or die('No direct script access.');
/**
 * User model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_User extends Jelly_Model implements Permission_Interface {

	/**
	 * Permission to post comments
	 */
	const PERMISSION_COMMENT = 'comment';

	/**
	 * Permission to read comments
	 */
	const PERMISSION_COMMENTS = 'comments';

	/**
	 * @var  Cache  cache instance
	 */
	protected static $_cache;

	/**
	 * @var  array  Static cache of Model_Users loaded
	 */
	protected static $_users = array();

	/**
	 * @var  array  User editable fields
	 */
	public static $editable_fields = array(
		'avatar', 'city', 'description', 'dob', 'email', 'gender', 'homepage',
		'image', 'name', 'picture', 'signature',
		'address_street', 'address_zip', 'address_city', 'title',
	);


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$visitor = Visitor::instance();

		$meta
			->name_key('username')
			->fields(array(
				'id' => new Field_Primary,
				'username' => new Field_String(array(
					'label'  => __('Username'),
					'unique' => true,
					'rules'  => array(
						'not_empty'  => null,
						'min_length' => array(max((int)Kohana::config('visitor.username.length_min'), 1)),
						'max_length' => array(min((int)Kohana::config('visitor.username.length_max'), 30)),
						'regex'      => array('/^[' . Kohana::config('visitor.username.chars') . ']+$/ui'),
					),
				)),
				'username_clean' => new Field_String(array(
					'unique' => true,
					'rules'  => array(
						'not_empty' => null,
					)
				)),
				'password' => new Field_Password(array(
					'label'     => __('Password'),
					'hash_with' => array($visitor, 'hash_password'),
					'rules'     => array(
						'not_empty'  => null,
						'min_length' => array(6),
					)
				)),
				'password_confirm' => new Field_Password(array(
					'label'     => __('Password confirmation'),
					'in_db'     => false,
					'callbacks' => array(
						'matches' => array('Model_User', '_check_password_matches')
					),
					'rules' => array(
						'not_empty'  => null,
						'min_length' => array(max((int)$visitor->get_config('password.length_min'), 1)),
					)
				)),
				'email' => new Field_Email(array(
					'label'  => __('Email'),
					'unique' => true,
					'filters' => array(
						'mb_strtolower' => null,
					),
				)),

				'name' => new Field_String(array(
					'label' => __('Name'),
					'rules' => array(
						'min_length' => array(1),
						'max_length' => array(50),
					),
				)),
				'dob' => new Field_Date(array(
					'null'   => true,
					'label'  => __('Date of Birth'),
					'format' => 'Y-m-d',
					'pretty_format' => 'j.n.Y',
				)),
				'gender' => new Field_Enum(array(
					'label'   => __('Gender'),
					'choices' => array(
						'f' => __('Female'),
						'm' => __('Male'),
					)
				)),
				'avatar' => new Field_String(array(
					'label' => __('Avatar'),
				)),
				'address_street' => new Field_String(array(
					'label' => __('Street address'),
					'rules' => array(
						'max_length' => array(50),
					),
				)),
				'address_zip' => new Field_String(array(
					'label' => __('Zip code'),
					'rules' => array(
						'min_length' => array(4),
						'max_length' => array(5),
						'digit'      => null
					),
				)),
				'address_city' => new Field_String(array(
					'label'   => __('City'),
					'rules'   => array(
						'max_length' => array(50)
					),
				)),
				'city'        => new Field_BelongsTo(array(
					'column'  => 'city_id',
					'foreign' => 'geo_city',
				)),
				'latitude'    => new Field_Float,
				'longitude'   => new Field_Float,
				'title'       => new Field_String(array(
					'label' => __('Title'),
				)),
				'signature'   => new Field_Text(array(
					'label' => __('Signature'),
				)),
				'description' => new Field_Text(array(
					'label' => __('Description'),
				)),
				'homepage'    => new Field_URL(array(
					'label' => __('Homepage'),
				)),

				'login_count' => new Field_Integer(array(
					'column'  => 'logins',
					'default' => 0,
				)),
				'last_login' => new Field_Timestamp,
				'created'    => new Field_Timestamp(array(
					'auto_now_create' => true,
				)),
				'modified' => new Field_Timestamp,

				// Foreign values, should make own models?
				'post_count' => new Field_Integer(array(
					'column'  => 'posts',
					'default' => 0,
				)),
				'new_comment_count' => new Field_Integer(array(
					'column'  => 'newcomments',
					'default' => 0,
				)),
				'comment_count' => new Field_Integer(array(
					'column'  => 'comments',
					'default' => 0,
				)),
				'left_comment_count' => new Field_Integer(array(
					'column'  => 'commentsleft',
					'default' => 0,
				)),

				'tokens' => new Field_HasMany(array(
					'foreign' => 'user_token'
				)),
				'roles' => new Field_ManyToMany,

				'picture' => new Field_String,
				'default_image' => new Field_BelongsTo(array(
					'foreign' => 'image',
					'column'  => 'default_image_id',
				)),
				'images'  => new Field_ManyToMany,
				'friends' => new Field_HasMany(array(
					'foreign' => 'friend',
				)),
				'comments' => new Field_HasMany(array(
					'foreign' => 'user_comment'
				)),
			));
	}


	/**
	 * Magic setter
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 */
	public function __set($key, $value)	{
		switch ($key) {

			// Date of birth
			case 'dob':
				$value = date::format(date::DATE_SQL, $value);
				break;

			// Always lowercase e-mail
			case 'email':
				$value = utf8::strtolower($value);
				break;

		}

		parent::__set($key, $value);
	}


	/**
	 * Validate callback wrapper for checking password match
	 *
	 * @static
	 * @param  Validate  $array
	 * @param  string    $field
	 */
	public static function _check_password_matches(Validate $array, $field) {
		if (empty($array['password']) || $array['password'] !== $array[$field]) {
			$array->error($field, 'matches', array('param1' => 'password'));
		}
	}


	/***** AUTH *****/

	/**
	 * Validates an array for a matching password and password_confirm field.
	 *
	 * @param  array    values to check
	 * @param  string   save the user if
	 * @return boolean
	 */
	public function change_password(array &$array, $save = false) {

		if ($status = $this->validate($array, false, array(), array(), array('rules' => 'password'))) {
			// Change the password
			$this->password = $array['password'];

			if ($save !== false && $status = $this->save()) {
				if (is_string($save)) {
					// Redirect to the success page
					url::redirect($save);
				}
			}
		}

		return $status;
	}


	/**
	 * Validates login information from an array, and optionally redirects
	 * after a successful login.
	 *
	 * @param  array    values to check
	 * @param  string   URI or URL to redirect to
	 * @return boolean
	 */
	public function login(array &$array, $redirect = false) {

		// Login starts out invalid
		$status = false;

		// Log login attempt
		$login = Jelly::factory('login')->set(array(
			'username' => $array['username'],
			'password' => !empty($array['password']),
		));

		if ($this->validate($array, false, array(), array(), array('rules' => 'login'))) {

			// Attempt to load the user
			$this->find_user($array['username']);
			if ($this->loaded()) {
				$login->uid = $this->id;
				$login->username = $this->username;

				if (Visitor::instance()->login($this, $array['password'])) 	{
					$login->success = 1;

					// Redirect after a successful login
					if (is_string($redirect))	{
						$login->save();
						url::redirect($redirect);
					}

					// Login is successful
					$status = true;

				} else {
					$array->add_error('username', 'invalid');
				}
			}
		}

		$login->save();
		return $status;
	}

	/***** /AUTH *****/


	/***** COMMENTS *****/

	/**
	 * Get user's comments
	 *
	 * @param  int    $page_num
	 * @param  int    $page_size
	 * @param  mixed  $user  Viewer
	 */
	public function find_comments($page_num, $page_size = 25, $user = null) {
		$user = self::find_user($user);

		// Try to fetch from cache first
		/*
		$cache_key = $this->cache->key('comments', $this->id, $page_num);
		if ($page_num <= User_Comment_Model::$cache_max_pages) {
			$comments = $this->cache->get($cache_key);
		}

		// Did we find any comments?
		if (!empty($comments)) {

			// Found from cache
			$comments = unserialize($comments);

		} else {
		*/

			// Not found from cache, load from DB
			$page_offset = ($page_num - 1) * $page_size;
			if ($user && $user->id == $this->id) {

				// All comments, my profile
				$comments = $this->user_comments->find_all($page_size, $page_offset);

			} else if ($user) {

				// Public and my comments
				$comments = $this->user_comments->and_open()->where('private', '=', 0)->or_where('author_id', '=', $user->id)->close()->find_all($page_size, $page_offset);

			} else {

				// Only public comments
				$comments = $this->user_comments->where('private', '=', 0)->find_all($page_size, $page_offset);

			}

			/*
			// cache only 3 first pages
			if ($page_num <= User_Comment_Model::$cache_max_pages) {
				$this->cache->set($cache_key, serialize($comments->as_array()), null, User_Comment_Model::$cache_max_age);
			}
		}
			*/

		return $comments;
	}


	/**
	 * Get user's new comment counts
	 *
	 * @return  array
	 */
	public function find_new_comments() {
		$new = array();

		// Profile comments
		if ($this->new_comment_count) {
			$new['new-comments'] = HTML::anchor(
				URL::user($this),
				__(':comments', array(':comments' => '<div></div><var>' . $this->new_comment_count . '</var>')),
				array('title' => __('New comments')
			));
		}

		// Blog comments
		$blog_comments = Model_Blog_Entry::find_new_comments($this);
		if (count($blog_comments)) {
			$new_comments = 0;
			foreach ($blog_comments as $blog_entry) {
				$new_comments += $blog_entry->new_comment_count;
			}
			$new['new-blog-comments'] = HTML::anchor(
				Route::model($blog_entry),
				__(':comments', array(':comments' => '<div></div><var>' . $new_comments . '</var>')),
				array('title' => __('New blog comments')
			));
		}
		unset($blog_comments);

		// Forum quotes
		$forum_quotes = Model_Forum_Quote::find_by_user($this);
		if (count($forum_quotes)) {
			$new_quotes = count($forum_quotes);
			$quote = $forum_quotes->current();
			$new['new-forum-quotes'] = HTML::anchor(
				Route::get('forum_post')->uri(array('topic_id' => Route::model_id($quote->topic), 'id' => $quote->post->id)) . '#post-' . $quote->post->id,
				__(':quotes', array(':quotes' => '<div></div><var>' . $new_quotes . '</var>')),
				array('title' => __('Forum quotes')
			));
		}

		// Images waiting for approval
		if (Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_APPROVE_WAITING, $this)) {
			$gallery_approvals = Model_Gallery::find_pending(Permission::has(new Model_Gallery, Model_Gallery::PERMISSION_APPROVE, $this) ? null : $this);
			if (count($gallery_approvals)) {
				$new_approvals = count($gallery_approvals);
				$new['new-gallery-approvals'] = HTML::anchor(
					Route::get('galleries')->uri(array('action' => 'approval')),
					__(':galleries', array(':galleries' => '<div></div><var>' . $new_approvals . '</var>')),
					array('title' => __('Galleries waiting for approval')
				));
			}
		}

		// Image comments
		$image_comments = Model_Image::find_new_comments($this);
		if (count($image_comments)) {
			$new_comments = 0;
			foreach ($image_comments as $image) {
				$new_comments += $image->new_comment_count;
			}
			$new['new-image-comments'] = HTML::anchor(
				Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id(Model_Gallery::find_by_image($image->id)), 'id' => $image->id, 'action' => '')),
				__(':comments', array(':comments' => '<div></div><var>' . $new_comments . '</var>')),
				array('title' => __('New image comments')
			));
		}
		unset($image_comments);

		// Private messages

		return $new;
	}


	/**
	 * Get user's total comment count
	 *
	 * @return  integer
	 */
	public function get_comment_count() {
		return (int)Jelly::select('user_comment')->where('user_id', '=', $this->id)->count();
	}

	/***** /COMMENTS *****/


	/***** EXTERNAL ACCOUNTS *****/

	/**
	 * Get 3rd party account by external id
	 *
	 * @param   string  $id
	 * @return  User_External_Model
	 */
	public function find_external_by_id($id) {
		return ORM::factory('user_external')->where(array('user_id' => $this->id, 'id' => $id))->find();
	}


	/**
	 * Get 3rd party account by external provider
	 *
	 * @param   string  $provider
	 * @return  User_External_Model
	 */
	public function find_external_by_provider($provider) {
		return ORM::factory('user_external')->where(array('user_id' => $this->id, 'provider' => $provider))->find();
	}


	/**
	 * Load one user by 3rd party account id
	 *
	 * @param   string  $id
	 * @param   string  $provider
	 * @return  User_Model
	 */
	public static function find_user_by_external($id, $provider) {
		$external_user = ORM::factory('user_external')->where(array('id' => $id, 'provider' => $provider))->find();

		return ($external_user->loaded()) ? $external_user->user : new User_Model();
	}


	/**
	 * Connect 3rd party account
	 *
	 * @param  string  $id
	 * @param  string  $provider
	 */
	public function map_external($id, $provider) {

		// Are we already connected?
		$external_user = $this->find_external_by_id($id);

		if ($this->loaded() && !$external_user->loaded()) {
			$external = new User_External_Model();
			$external->user_id = $this->id;
			$external->id = $id;
			$external->provider = $provider;
			$external->stamp = time();

			return $external->save();
		}

		return false;
	}

	/***** /EXTERNAL ACCOUNTS *****/


	/***** FRIENDS *****/

	/**
	 * Create friendship
	 *
	 * @param  User_Model  $friend
	 */
	public function add_friend(User_Model $friend) {

		// don't add duplicate friends or oneself
		if ($this->loaded() && $this->id != $friend->id && !$this->is_friend($friend)) {
			$friendship = new Friend_Model();
			$friendship->user_id = $this->id;
			$friendship->friend_id = $friend->id;
			$friendship->save();
			return true;
		}

		return false;
	}


	/**
	 * Delete friendship
	 *
	 * @param  User_Model  $friend
	 */
	public function delete_friend(User_Model $friend) {
		return $this->loaded()
			&& $this->is_friend($friend)
			&& (bool)count(db::build()
				->delete('friends')
				->where('user_id', '=', $this->id)
				->where('friend_id', '=', $friend->id)
				->execute());
	}


	/**
	 * Get user's friends
	 *
	 * @param   integer  $page_num
	 * @param   integer  $page_size
	 * @return  ORM_Iterator
	 */
	public function find_friends($page_num = 1, $page_size = 25) {
		$page_offset = ($page_num - 1) * $page_size;
		$friends = ORM::factory('friend')->where('user_id', '=', $this->id)->find_all($page_size, $page_offset);

		return $friends;
	}


	/**
	 * Get user's total friend count
	 *
	 * @return  int
	 */
	public function get_friend_count() {
		return (int)ORM::factory('friend')->where('user_id', '=', $this->id)->count_all();
	}


	/**
	 * Check for friendship
	 *
	 * @param  mixed  $friend  Model_User, $id
	 */
	public function is_friend($friend) {
		if (empty($friend) || !(is_int($friend) || $friend instanceof Model_User)) {
			return false;
		}

		return $this->has('friends', $friend);
		/*
		// Load friends
		if (!is_array($this->data_friends)) {
			$friends = array();
			if ($this->loaded()) {
				$users = db::build()->select('friend_id')->from('friends')->where('user_id', '=', $this->id)->execute()->as_array();
				foreach ($users as $user) {
					$friends[(int)$user['friend_id']] = (int)$user['friend_id'];
				}
			}
			$this->data_friends = $friends;
		}

		if ($friend instanceof User_Model) {
			$friend = $friend->id;
		}

		return isset($this->data_friends[(int)$friend]);
		*/
	}

	/***** /FRIENDS *****/


	/**
	 * Load one user.
	 *
	 * @static
	 * @param   mixed  $user  id, username, email, Model_User, user array or false for current session
	 * @return  Model_User  or null
	 */
	public static function find_user($id = false) {
		static $session = false;

		$user = null;
		$cache = false;

		// Try user models first (User_Model, session)
		if ($id instanceof Model_User) {

			// Model_User
			$user = $id;

		} else if ($id === false) {

			// Current session, fetch only once
			if ($session === false) {
				$session = Visitor::instance()->get_user();
			}
			$user = $session;

		}

		// Then try others (user_id, email, username_clean)
		if (!$user && $id !== true && !empty($id)) {
			if (is_numeric($id) || empty($id)) {
				$id = (int)$id;
			} else if (is_array($id)) {
				$id = (int)$id['id'];
			} else if (Validate::email($id)) {
				$id = mb_strtolower($id);
			} else {
				$id = Text::clean($id);
			}
			if (isset(self::$_users[$id])) {

				// Found from static cache
				return self::$_users[$id];

			} else if ($user = Cache::instance()->get_('user_' . $id)) {

				// Found from cache
				$user = unserialize($user);

			} else {

				// Not found from caches, try db
				if (is_int($id)) {
					$user = Jelly::select('user', $id);
				} else {
					$user = Jelly::select('user')->where(Validate::email($id) ? 'email' : 'username_clean', '=', $id)->limit(1)->execute();
				}
				$cache = true;

			}
		}

		// If user found, add to cache(s)
		if ($user && $user->loaded()) {
			self::$_users[$user->id] = self::$_users[Text::clean($user->username)] = self::$_users[mb_strtolower($user->email)] = $user;
			if ($cache) {
				Cache::instance()->set_('user_' . $user->id, serialize($user), 3600);
			}
		} else {
			$user = null;
		}

		return $user;
	}


	/**
	 * Check permission
	 *
	 * @param   string      $permission
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function has_permission($permission, $user) {
		switch ($permission) {

			case self::PERMISSION_READ:
		    return true;

			case self::PERMISSION_COMMENT:
			case self::PERMISSION_COMMENTS:
		    return (bool)$user;

			case self::PERMISSION_UPDATE:
				return $user && ($this->id == $user->id || $user->has_role('admin'));

			case self::PERMISSION_CREATE:
			case self::PERMISSION_DELETE:

		}

		return false;
	}


	/**
	 * Does the user have any of these roles
	 *
	 * @param  array|string  $roles
	 */
	public function has_role($roles) {
		foreach ($this->get('roles')->execute() as $role) {
			if (is_array($roles) && in_array($role->name, $roles)
				|| is_numeric($roles) && $role->id == $roles
				|| is_string($roles) && $role->name == $roles) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Allows a model to be loaded by username or email address.
	 *
	 * @param   mixed  $id  id, username, email
	 * @return  string
	 */
	public function unique_key($id)	{
		if (!empty($id) && is_string($id) && ! ctype_digit($id)) {
			return valid::email($id) ? 'email' : 'username';
		}

		return parent::unique_key($id);
	}


}

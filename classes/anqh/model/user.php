<?php defined('SYSPATH') or die('No direct script access.');
/**
 * User model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_User extends AutoModeler_ORM implements Permission_Interface {

	/**
	 * Permission to post comments
	 */
	const PERMISSION_COMMENT = 'comment';

	/**
	 * Permission to read comments
	 */
	const PERMISSION_COMMENTS = 'comments';

	/**
	 * Permission to add/remove friend
	 */
	const PERMISSION_FRIEND = 'friend';

	/**
	 * Permission to (un)ignore
	 */
	const PERMISSION_IGNORE = 'ignore';

	protected $_table_name = 'users';

	protected $_data = array(
		'id'                 => null,
		'username'           => null,
		'username_clean'     => null,
		'password'           => null,
		'email'              => null,

		// Personal information
		'name'               => null,
		'dob'                => null,
		'gender'             => null,
		'title'              => null,
		'signature'          => null,
		'description'        => null,
		'homepage'           => null,
		'avatar'             => null,
		'picture'            => null,
		'default_image_id'   => null,

		// Location
		'address_street'     => null,
		'address_zip'        => null,
		'address_city'       => null,
		'geo_city_id'        => null,
		'latitude'           => null,
		'longitude'          => null,

		// Stats
		'login_count'        => null,
		'last_login'         => null,
		'old_login'          => null,
		'post_count'         => 0,
		'new_comment_count'  => 0,
		'comment_count'      => 0,
		'left_comment_count' => 0,
		'created'            => null,
		'modified'           => null,
		'ip'                 => null,
		'hostname'           => null,

	);

	protected $_rules = array(
		'username'           => array(
			'not_empty',
			'length'              => array(':value', 1, 30),
			'regex'               => array(':value', '/^[a-zåäöA-ZÅÄÖ0-9_\.\-& ^]+$/ui'),
			'AutoModeler::unique' => array(':model', ':value', ':field')
		),
		'username_clean'     => array('not_empty', 'AutoModeler::unique' => array(':model', ':value', ':field')),
		'password'           => array('not_empty'),
		'email'              => array('not_empty', 'email', 'AutoModeler::unique' => array(':model', ':value', ':field')),

		'name'               => array('max_length' => array(':value', 50)),
		'dob'                => array('date'),
		'gender'             => array('in_array' => array(':value', array('f', 'm'))),
		'homepage'           => array('url'),
		'default_image_id'   => array('digit'),

		'address_street'     => array('max_length' => array(':value', 50)),
		'address_zip'        => array('digit', 'length' => array(':value', 4, 5)),
		'address_city'       => array('max_length' => array(':value', 50)),
		'city_id'            => array('digit'),
		'latitude'           => array('numeric'),
		'longitude'          => array('numeric'),

		'login_count'        => array('digit'),
		'last_login'         => array('digit'),
		'post_count'         => array('digit'),
		'new_comment_count'  => array('digit'),
		'comment_count'      => array('digit'),
		'left_comment_count' => array('digit'),
		'created'            => array('digit'),
		'modified'           => array('digit'),
	);

	protected $_has_many = array(
		'comments', 'images', 'roles', 'tokens',
	);

	/**
	 * @var  array  User's roles
	 */
	protected $_roles = array();

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
	 * Load user.
	 *
	 * @param  integer|string  $id
	 */
	public function __construct($id = null) {
		parent::__construct();

		if ($id !== null) {
			if (is_numeric($id)) {

				// Load by id
				$this->load(DB::select_array($this->fields())->where('id', '=', $id));

			} else {

				// Load by username
				$this->load(DB::select_array($this->fields())->where('username_clean', '=', UTF8::strtolower($id)));

			}
		}
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
				$value = Date::format(Date::DATE_SQL, $value);
				break;

			// Always lowercase e-mail
			case 'email':
				$value = UTF8::strtolower($value);
				break;

			// Hash password
			case 'password':
				$visitor = Visitor::instance();
				$value   = $visitor->hash_password($value);
				break;

			// Set cleaned username when setting username
			case 'username':
				$this->username_clean = Text::clean($value);
				break;

		}

		parent::__set($key, $value);
	}


	/**
	 * Validate callback wrapper for checking password match
	 *
	 * @static
	 * @param  Validation  $array
	 * @param  string      $field
	 */
	public static function _check_password_matches(Validation $array, $field) {
		if (empty($array['password']) || $array['password'] !== $array[$field]) {
			$array->error($field, 'matches', array('param1' => 'password'));
		}
	}


	/**
	 * Add a role to user.
	 *
	 * @param   integer|string  $role_id
	 * @return  boolean
	 */
	public function add_role($role_id) {

		// Do not try to insert duplicate role
		if (!$this->has_role($role_id)) {
			try {
				$role = new Model_Role($role_id);
				if ($role->loaded()) {

					// Empty roles from current user to force reload
					$this->_roles = array();

					return (bool)DB::insert('roles_users')
						->columns(array('role_id', 'user_id'))
						->values(array($role->id, $this->id))
						->execute($this->_db);
				}
			} catch (Exception $e) {}
		}

		return false;
	}


	/**
	 * Get city.
	 *
	 * @return  Model_Geo_City|null
	 */
	public function city() {
		return $this->geo_city_id ? new Model_Geo_City($this->geo_city_id) : null;
	}


	/***** COMMENTS *****/

	/**
	 * Get image comments
	 *
	 * @param   Model_User  $viewer
	 * @param   View_Generic_Pagination  $pagination
	 * @return  Model_User_Comment[]
	 */
	public function comments(Model_User $viewer = null, View_Generic_Pagination $pagination = null) {
		$query = Model_Comment::query_viewer(DB::select_array(Model_User_Comment::factory()->fields()), $viewer)
			->order_by('id', 'DESC');

		if ($pagination) {
			$query = $query
				->offset($pagination->offset)
				->limit($pagination->items_per_page);
		}

		return $this->find_related('user_comments', $query);

	}


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
				$comments = $this->user_comments
					->and_open()
					->where('private', '=', 0)
					->or_where('author_id', '=', $user->id)
					->close()
					->find_all($page_size, $page_offset);

			} else {

				// Only public comments
				$comments = $this->user_comments
					->where('private', '=', 0)
					->find_all($page_size, $page_offset);

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
	 * Mark user's comments read.
	 */
	public function mark_comments_read() {
		if ($this->new_comment_count) {
			$this->new_comment_count = 0;
			$this->save();

			Anqh::cache_delete('user_' . $this->id);
		}
	}

	/***** /COMMENTS *****/


	/***** EXTERNAL ACCOUNTS *****/

	/**
	 * Get 3rd party account by external id
	 *
	 * @param   string  $id
	 * @return  User_External_Model
	 * @deprecated
	 */
	public function find_external_by_id($id) {
		return ORM::factory('user_external')
			->where(array('user_id' => $this->id, 'id' => $id))
			->find();
	}


	/**
	 * Get 3rd party account by external provider
	 *
	 * @param   string  $provider
	 * @return  User_External_Model
	 * @deprecated
	 */
	public function find_external_by_provider($provider) {
		return ORM::factory('user_external')
			->where(array('user_id' => $this->id, 'provider' => $provider))
			->find();
	}


	/**
	 * Load one user by 3rd party account id
	 *
	 * @param   string  $id
	 * @param   string  $provider
	 * @return  User_Model
	 * @deprecated
	 */
	public static function find_user_by_external($id, $provider) {
		$external_user = ORM::factory('user_external')
			->where(array('id' => $id, 'provider' => $provider))
			->find();

		return ($external_user->loaded()) ? $external_user->user : new User_Model();
	}


	/**
	 * Connect 3rd party account
	 *
	 * @param  string  $id
	 * @param  string  $provider
	 * @deprecated
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


	/***** FRIENDS & FOES *****/

	/**
	 * Create friendship.
	 *
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function add_friend(Model_User $user) {
		if ($this->loaded() && $this->id != $user->id && !$this->is_friend($user)) {
			return Model_Friend::add($this->id, $user->id);
		}

		return false;
	}


	/**
	 * Add user to ignore.
	 *
	 * @param   Model_User  $ignore
	 * @return  boolean
	 */
	public function add_ignore(Model_User $ignore) {
		if ($this->loaded() && $this->id != $ignore->id	&& !$this->is_ignored($ignore)) {
			return Model_Ignore::add($this->id, $ignore->id);
		}

		return false;
	}


	/**
	 * Delete friendship.
	 *
	 * @param   Model_User  $friend
	 * @return  boolean
	 */
	public function delete_friend(Model_User $friend) {
		return $this->loaded() && Model_Friend::unfriend($this->id, $friend->id);
	}


	/**
	 * Remove ignore.
	 *
	 * @param   Model_User  $ignore
	 * @return  boolean
	 */
	public function delete_ignore(Model_User $ignore) {
		return $this->loaded() && Model_Ignore::unignore($this->id, $ignore->id);
	}


	/**
	 * Expire user caches.
	 *
	 * @static
	 * @param  integer  $user_id
	 */
	public static function expire_caches($user_id) {
		Anqh::cache_delete('user_' . (int)$user_id);
		Anqh::cache_delete('user_light_' . (int)$user_id);
	}


	/**
	 * Get users by birthday.
	 *
	 * @static
	 * @param   integer  $stamp
	 * @return  array    uid => dob
	 */
	public static function find_by_birthday($stamp) {
		$stamp = (int)$stamp;
		$date  = date('m-d', $stamp);
		$ckey  = 'birthday_' . $date;

		// Load from cache
		$birthdays = Anqh::cache_get($ckey);
		if (!is_array($birthdays)) {

			// Load from db
			$birthdays = DB::select('id', 'dob')
				->from('users')
				// Casting date to string should work in MySQL and PostgreSQL
				->where(DB::expr('SUBSTR(CAST(dob AS CHAR(10)), 6)'), '=', $date)
				->execute()
				->as_array('id', 'dob');
			asort($birthdays);
			Anqh::cache_set('birthday_' . $date, $birthdays, Date::HOUR);

		}

		return $birthdays;
	}


	/**
	 * Get user's friends
	 *
	 * @return  array
	 */
	public function find_friends() {
		return Model_Friend::find_by_user($this->id);
	}


	/**
	 * Get user's ignores or users ignoring user
	 *
	 * @param   boolean  $ignorers  true for ignorers, false for ignores
	 * @return  array
	 */
	public function find_ignores($ignorers = false) {
		return $ignorers ? Model_Ignore::find_by_ignorer($this->id) : Model_Ignore::find_by_user($this->id);
	}


	/**
	 * Get new users.
	 *
	 * @param   integer  $limit
	 * @return  array
	 */
	public static function find_new_users($limit = 1) {
		return (array)DB::select('id', 'created')
			->from('users')
			->order_by('id', 'DESC')
			->limit((int)$limit)
			->execute()
			->as_array('id', 'created');
	}


	/**
	 * Get user's total friend count
	 *
	 * @return  integer
	 */
	public function get_friend_count() {
		return count($this->find_friends());
	}


	/**
	 * Get user's default image url
	 *
	 * @param   string  $size
	 * @return  string
	 */
	public function get_image_url($size = null) {
		if ($this->default_image_id) {
			$image = Model_Image::factory($this->default_image_id)->get_url($size);
		} else if (Valid::url($this->picture)) {
			$image = $this->picture;
		} else {
			$image = null;
		}

		return $image;
	}


	/**
	 * Get enterprise Validation for checking password
	 *
	 * @static
	 * @param   array  $user_post
	 * @return  Validation
	 */
	public static function get_password_validation($user_post) {
		return Validation::factory(
			array(
				'password'         => Arr::get($user_post, 'password'),
				'password_confirm' => Arr::get($user_post, 'password_confirm'),
			))
			->rule('password_confirm', 'not_empty')
			->rule('password', 'matches', array(':validation', 'password', 'password_confirm')
		);
	}


	/**
	 * Check for friendship.
	 *
	 * @param   mixed  $friend  Model_User, array, $id
	 * @return  boolean
	 */
	public function is_friend($friend) {
		if (Kohana::$profiling === true && class_exists('Profiler', false)) {
			$benchmark = Profiler::start('Anqh', __METHOD__);
		}

		if ($friend instanceof Model_User) {
			$friend = (int)$friend->id;
		} else if (is_array($friend)) {
			$friend = (int)Arr::get($friend, 'id');
		} else {
			$friend = (int)$friend;
		}

		if (!$friend) {
			return false;
		}

		$is_friend = in_array($friend, (array)$this->find_friends());

		if (isset($benchmark)) {
			Profiler::stop($benchmark);
		}

		return $is_friend;
	}


	/**
	 * Check for friendship.
	 *
	 * @param   mixed    $ignore      Model_User, array, $id
	 * @param   boolean  $ignored_by  Check if the user ignored by $ignore or ignoring $ignore
	 * @return  boolean
	 */
	public function is_ignored($ignore, $ignored_by = false) {
		if (Kohana::$profiling === true && class_exists('Profiler', false)) {
			$benchmark = Profiler::start('Anqh', __METHOD__);
		}

		if ($ignore instanceof Model_User) {
			$ignore = (int)$ignore->id;
		} else if (is_array($ignore)) {
			$ignore = (int)Arr::get($ignore, 'id');
		} else {
			$ignore = (int)$ignore;
		}

		if (!$ignore) {
			return false;
		}

		$is_ignored = in_array($ignore, (array)$this->find_ignores($ignored_by));

		if (isset($benchmark)) {
			Profiler::stop($benchmark);
		}

		return $is_ignored;
	}

	/***** /FRIENDS & FOES *****/


	/**
	 * Load one user.
	 *
	 * @static
	 * @param   mixed  $id  id, username, email, Model_User, user array or false for current session
	 * @return  Model_User
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
			} else if (Valid::email($id)) {
				$id = mb_strtolower($id);
			} else {
				$id = Text::clean($id);
			}
			if (isset(self::$_users[$id])) {

				// Found from static cache
				return self::$_users[$id];

			} else if ($user = Anqh::cache_get('user_' . $id)) {

				// Found from cache
				$user = unserialize($user);

			} else {

				// Not found from caches, try db
				if (is_int($id)) {
					$user = Model_User::factory($id);
				} else {
					$user = Model_User::factory()->load(
						DB::select_array(Model_User::factory()->fields())
							->where(Valid::email($id) ? 'email' : 'username_clean', '=', $id)
					);
				}
				$cache = true;

			}
		}

		// If user found, add to cache(s)
		if ($user && $user->loaded()) {
			self::$_users[$user->id] = self::$_users[Text::clean($user->username)] = self::$_users[mb_strtolower($user->email)] = $user;
			if ($cache) {
				Anqh::cache_set('user_' . $user->id, serialize($user), Date::DAY);
			}
		} else {
			$user = null;
		}

		return $user;
	}


	/**
	 * Load one user light array
	 *
	 * @static
	 * @param   mixed  $id  User model, user array, user id, username
	 * @return  array|null
	 */
	public static function find_user_light($id = null) {
		$ckey = 'user_light_';
		if ($id instanceof Model_User) {

			// Got user model, no need to load, just fill caches
			/** @var  Model_User  $id */
			return $id->light_array();

		} else if (is_array($id)) {

			// Got user array, don't fill caches as we're not 100% sure it's valid
			return $id;

		} else if (is_int($id) || is_numeric($id)) {

			// Got id
			$id = (int)$id;

		} else if (is_string($id)) {

			// Got user name, find id
			$id = Model_User::user_id($id);

		} else {

			return null;

		}

		if ($id === 0) {
			return null;
		}

		// Try static cache
		$user = Anqh::cache_get($ckey . $id);
		if (!is_array($user)) {

			// Load from DB
			$user = Model_User::factory($id)->light_array();

			Anqh::cache_set($ckey . $id, $user, Date::DAY);
		}

		return $user;
	}


	/**
	 * Get light array from User Model
	 *
	 * @return  array
	 */
	public function light_array() {
		if ($this->loaded()) {
			return array(
				'id'         => (int)$this->id,
				'username'   => $this->username,
				'gender'     => $this->gender,
				'title'      => $this->title,
				'signature'  => $this->signature,
				'avatar'     => $this->avatar,
				'thumb'      => $this->get_image_url('thumbnail'),
				'last_login' => (int)$this->last_login,
			);
		}
	}


	/**
	 * Get user id from data
	 *
	 * @static
	 * @param   mixed  $user
	 * @return  integer
	 */
	public static function user_id($user) {
		if (is_int($user) || is_numeric($user)) {

			// Already got id
			return (int)$user;

		} else if (is_array($user)) {

			// Got user array
			return (int)Arr::get($user, 'id');

		}	else if ($user instanceof Model_User) {

			// Got user model
			return $user->id;

		} else if (is_string($user)) {

			// Got user name
			$username = Text::clean($user);
			if (!$id = (int)Anqh::cache_get('user_uid_' . $username)) {
				if ($user = Model_User::find_user($user)) {
					$id = $user->id;
					Anqh::cache_set('user_uid_' . $username, $id, Date::DAY);
				}
			}

			return $id;
		}

		return 0;
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
		    return $user && ($this->id == $user->id || (!$this->is_ignored($user) && !$this->is_ignored($user, true)));

			case self::PERMISSION_FRIEND:
		    return $user && ($this->id != $user->id) && !$this->is_ignored($user) && !$this->is_ignored($user, true);

			case self::PERMISSION_IGNORE:
		    return $user && ($this->id != $user->id);

			case self::PERMISSION_UPDATE:
				return $user && ($this->id == $user->id || $user->has_role('admin'));

			case self::PERMISSION_CREATE:
			case self::PERMISSION_DELETE:
		    return $user && ($this->id == $user->id);

		}

		return false;
	}


	/**
	 * Does the user have any of these roles.
	 *
	 * @param  array|string  $roles
	 */
	public function has_role($roles) {
		foreach ($this->roles() as $id => $role) {
			if ((is_array($roles) && in_array($role, $roles))
				|| (is_numeric($roles) && $id == $roles)
				|| (is_string($roles) && $role == $roles)
			) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Get user's images.
	 *
	 * @return  Model_Image[]
	 */
	public function images() {
		return $this->find_related('images');
	}


	/**
	 * Get user's roles.
	 *
	 * @return  array
	 */
	public function roles() {
		if (!$this->_roles) {
			$this->_roles = Model_Role::find_by_user($this);
		}

		return $this->_roles;
	}


	/**
	 * Save model and expire caches.
	 *
	 * @param   mixed  $validation
	 * @return  integer
	 */
	public function save($validation = null) {
		if ($result = parent::save($validation)) {
			self::expire_caches($this->id);
		}

		return $result;
	}

}

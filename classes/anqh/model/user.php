<?php defined('SYSPATH') or die('No direct script access.');
/**
 * User model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
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
	 * Permission to add/remove friend
	 */
	const PERMISSION_FRIEND = 'friend';

	/**
	 * Permission to (un)ignore
	 */
	const PERMISSION_IGNORE = 'ignore';

	protected $_table_name = 'users';

	protected $_data = array(
		'id'             => null,
		'username'       => null,
		'username_clean' => null,
		'password'       => null,
		'email'          => null,

		// Personal information
		'name'             => null,
		'dob'              => null,
		'gender'           => null,
		'title'            => null,
		'signature'        => null,
		'description'      => null,
		'homepage'         => null,
		'avatar'           => null,
		'picture'          => null,
		'default_image_id' => null,

		// Location
		'address_street' => null,
		'address_zip'    => null,
		'address_city'   => null,
		'city_id'        => null,
		'latitude'       => null,
		'longitude'      => null,

		// Stats
		'login_count'        => null,
		'last_login'         => null,
		'post_count'         => 0,
		'new_comment_count'  => 0,
		'comment_count'      => 0,
		'left_comment_count' => 0,
		'created'            => null,
		'modified'           => null,

	);

	protected $_rules = array(
		'username'       => array('not_empty', 'length' => array(':value', 1, 30), 'AutoModeler::unique' => array(':model', ':value', ':field')),
		'username_clean' => array('not_empty', 'AutoModeler::unique' => array(':model', ':value', ':field')),
		'password'       => array('not_empty'),
		'email'          => array('not_empty', 'email', 'AutoModeler::unique' => array(':model', ':value', ':field')),

		'name'             => array('max_length' => array(':value', 50)),
		'dob'              => array('date'),
		'gender'           => array('in_array' => array(':validate', array('f', 'm'))),
		'homepage'         => array('url'),
		'default_image_id' => array('digit'),

		'address_street' => array('max_length' => array(':value', 50)),
		'address_zip'    => array('digit', 'length' => array(':value', 4, 5)),
		'address_city'   => array('max_length' => array(':value', 50)),
		'city_id'        => array('digit'),
		'latitude'       => array('numeric'),
		'longitude'      => array('numeric'),

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
		'roles',
		'tokens',
		'images',
		'friends',
		'comments',
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
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$visitor = Visitor::instance();

		$meta->name_key('username');

		$meta->fields(array(
			'id' => new Jelly_Field_Primary,
			'username' => new Jelly_Field_String(array(
				'label'  => __('Username'),
				'unique' => true,
				'rules'  => array(
					'not_empty'  => null,
					'min_length' => array(max((int)Kohana::config('visitor.username.length_min'), 1)),
					'max_length' => array(min((int)Kohana::config('visitor.username.length_max'), 30)),
					'regex'      => array('/^[' . Kohana::config('visitor.username.chars') . ']+$/ui'),
				),
			)),
			'username_clean' => new Jelly_Field_String(array(
				'unique' => true,
				'rules'  => array(
					'not_empty' => null,
				)
			)),
			'password' => new Jelly_Field_Password(array(
				'label'     => __('Password'),
				'hash_with' => array($visitor, 'hash_password'),
				'rules'     => array(
					'not_empty'  => null,
					'min_length' => array(6),
				)
			)),
			'password_confirm' => new Jelly_Field_Password(array(
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
			'email' => new Jelly_Field_Email(array(
				'label'  => __('Email'),
				'unique' => true,
				'filters' => array(
					'mb_strtolower' => null,
				),
			)),

			'name' => new Jelly_Field_String(array(
				'label' => __('Name'),
				'rules' => array(
					'min_length' => array(1),
					'max_length' => array(50),
				),
			)),
			'dob' => new Jelly_Field_Date(array(
				'null'   => true,
				'label'  => __('Date of Birth'),
				'format' => 'Y-m-d',
				'pretty_format' => 'j.n.Y',
			)),
			'gender' => new Jelly_Field_Enum(array(
				'label'   => __('Gender'),
				'choices' => array(
					'f' => __('Female'),
					'm' => __('Male'),
				)
			)),
			'avatar' => new Jelly_Field_String(array(
				'label' => __('Avatar'),
			)),
			'address_street' => new Jelly_Field_String(array(
				'label' => __('Street address'),
				'rules' => array(
					'max_length' => array(50),
				),
			)),
			'address_zip' => new Jelly_Field_String(array(
				'label' => __('Zip code'),
				'rules' => array(
					'min_length' => array(4),
					'max_length' => array(5),
					'digit'      => null
				),
			)),
			'address_city' => new Jelly_Field_String(array(
				'label'   => __('City'),
				'rules'   => array(
					'max_length' => array(50)
				),
			)),
			'city' => new Jelly_Field_BelongsTo(array(
				'column'  => 'city_id',
				'foreign' => 'geo_city',
			)),
			'latitude' => new Jelly_Field_Float,
			'longitude' => new Jelly_Field_Float,
			'title' => new Jelly_Field_String(array(
				'label' => __('Title'),
			)),
			'signature' => new Jelly_Field_Text(array(
				'label' => __('Signature'),
			)),
			'description' => new Jelly_Field_Text(array(
				'label' => __('Description'),
			)),
			'homepage' => new Jelly_Field_URL(array(
				'label' => __('Homepage'),
			)),

			'login_count' => new Jelly_Field_Integer(array(
				'column'  => 'logins',
				'default' => 0,
			)),
			'last_login' => new Jelly_Field_Timestamp,
			'created' => new Jelly_Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'modified' => new Jelly_Field_Timestamp,

			// Foreign values, should make own models?
			'post_count' => new Jelly_Field_Integer(array(
				'column'  => 'posts',
				'default' => 0,
			)),
			'new_comment_count' => new Jelly_Field_Integer(array(
				'column'  => 'newcomments',
				'default' => 0,
			)),
			'comment_count' => new Jelly_Field_Integer(array(
				'column'  => 'comments',
				'default' => 0,
			)),
			'left_comment_count' => new Jelly_Field_Integer(array(
				'column'  => 'commentsleft',
				'default' => 0,
			)),

			'tokens' => new Jelly_Field_HasMany(array(
				'foreign' => 'user_token'
			)),
			'roles' => new Jelly_Field_ManyToMany,

			'picture' => new Jelly_Field_String,
			'default_image' => new Jelly_Field_BelongsTo(array(
				'foreign' => 'image',
				'column'  => 'default_image_id',
			)),
			'images'  => new Jelly_Field_ManyToMany,
			'friends' => new Jelly_Field_HasMany(array(
				'foreign' => 'friend'
			)),
			'comments' => new Jelly_Field_HasMany(array(
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
				$value = Date::format(Date::DATE_SQL, $value);
				break;

			// Always lowercase e-mail
			case 'email':
				$value = UTF8::strtolower($value);
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
	public static function _check_password_matches(Validation $array, $field) {
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
		$login = Model_Login::factory()->set(array(
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
						URL::redirect($redirect);
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
	 * Get user's new comment counts
	 *
	 * @return  array
	 */
	public function find_new_comments() {
		$new = array();

		// Profile comments
		if ($this->new_comment_count) {
			$new['new-comments'] = HTML::anchor(URL::user($this), $this->new_comment_count, array('title' => __('New comments')));
		}

		// Forum private messages
		$private_messages = Forum::find_new_private_messages($this);
		if (count($private_messages)) {
			$new_messages = 0;
			foreach ($private_messages as $private_message) {
				$new_messages += $private_message->unread;
			}
			$new['new-private-messages'] = HTML::anchor(Route::model($private_message->topic) . '?page=last#last', $new_messages, array('title' => __('New private messages')));
		}
		unset($private_messages);

		// Blog comments
		$blog_comments = Model_Blog_Entry::find_new_comments($this);
		if (count($blog_comments)) {
			$new_comments = 0;
			foreach ($blog_comments as $blog_entry) {
				$new_comments += $blog_entry->new_comment_count;
			}
			$new['new-blog-comments'] = HTML::anchor(Route::model($blog_entry), $new_comments, array('title' => __('New blog comments')));
		}
		unset($blog_comments);

		// Forum quotes
		$forum_quotes = Model_Forum_Quote::find_by_user($this);
		if (count($forum_quotes)) {
			$new_quotes = count($forum_quotes);
			$quote = $forum_quotes->current();
			$new['new-forum-quotes'] = HTML::anchor(
				Route::get('forum_post')->uri(array('topic_id' => Route::model_id($quote->topic), 'id' => $quote->post->id)) . '#post-' . $quote->post->id,
				$new_quotes,
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
					$new_approvals,
					array('title' => __('Galleries waiting for approval')
				));
			}
		}

		// Flyer comments
		$flyer_comments = Model_Flyer::find_new_comments($this);
		$flyers = array();
		if (count($flyer_comments)) {
			$new_comments = 0;
			foreach ($flyer_comments as $flyer) {
				$flyers[$flyer->image->id] = true;
				$new_comments += $flyer->image->new_comment_count;
			}
			$new['new-flyer-comments'] = HTML::anchor(
				Route::get('flyer')->uri(array('id' => $flyer->id, 'action' => '')),
				$new_comments,
				array('title' => __('New flyer comments')
			));
		}
		unset($flyer_comments);

		// Image comments
		$image_comments = Model_Image::find_new_comments($this);
		$note_comments  = Model_Image_Note::find_new_comments($this);
		if (count($image_comments) || count($note_comments)) {
			$new_comments = 0;
			$new_image = null;
			foreach ($image_comments as $image) {

				// @TODO: Until flyer comments are fixed..
				if (!isset($flyers[$image->id])) {
					$new_comments += $image->new_comment_count;
				  $new_image = $image;
				}

			}
			foreach ($note_comments as $note) {
				$new_comments += $note->new_comment_count;
			  $new_image = $note->image;
			}

			if ($new_comments) {
				$new['new-image-comments'] = HTML::anchor(
					Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id(Model_Gallery::find_by_image($new_image->id)), 'id' => $new_image->id, 'action' => '')),
					$new_comments,
					array('title' => __('New image comments')
				));
			}
		}
		unset($image_comments, $note_comments, $new_image);

		// Image tags
		$notes  = Model_Image_Note::find_new_notes($this);
		if (count($notes)) {
			$new_notes = 0;
			$new_note = null;
			foreach ($notes as $note) {
				$new_notes++;
			  $new_note_image = $note->image;
			}

			if ($new_notes) {
				$new['new-image-notes'] = HTML::anchor(
					Route::get('gallery_image')->uri(array('gallery_id' => Route::model_id(Model_Gallery::find_by_image($new_note_image->id)), 'id' => $new_note_image->id, 'action' => '')),
					$new_notes,
					array('title' => __('New image tags')
				));
			}
		}
		unset($note_comments, $new_note_image);

		// Private messages

		return $new;
	}


	/**
	 * Get user's total comment count
	 *
	 * @return  integer
	 */
	public function get_comment_count() {
		return (int)Jelly::query('user_comment')
			->where('user_id', '=', $this->id)
			->count();
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
	 * Create friendship
	 *
	 * @param  Model_User  $friend
	 */
	public function add_friend(Model_User $friend) {

		// Don't add duplicate friends or oneself
		if ($this->loaded()
			&& $this->id != $friend->id
			&& !$this->is_friend($friend)
			&& (bool)Model_Friend::factory()->set(array(
				'user'   => $this,
				'friend' => $friend
			))->save()) {

			// Clear cache
			Anqh::cache_delete('friends_' . $this->id);

			return true;
		}

		return false;
	}


	/**
	 * Add user to ignore
	 *
	 * @param  Model_User  $ignore
	 */
	public function add_ignore(Model_User $ignore) {
		if ($this->loaded()
			&& $this->id != $ignore->id
			&& !$this->is_ignored($ignore)
			&& (bool)Model_Ignore::factory()->set(array(
				'user'   => $this,
				'ignore' => $ignore
			))->save()) {

			// Clear caches
			Anqh::cache_delete('ignores_' . $this->id);
			Anqh::cache_delete('ignorers_' . $ignore->id);

			return true;
		}

		return false;
	}


	/**
	 * Delete friendship
	 *
	 * @param  Model_User  $friend
	 */
	public function delete_friend(Model_User $friend) {
		if ($this->loaded()
			&& $this->is_friend($friend)
			&& (bool)Jelly::query('friend')
				->where('user_id', '=', $this->id)
				->where('friend_id', '=', $friend->id)
				->select()
				->delete()) {

			// Clear cache
			Anqh::cache_delete('friends_' . $this->id);

		  return true;
		}

	  return false;
	}


	/**
	 * Remove ignore
	 *
	 * @param  Model_User  $ignore
	 */
	public function delete_ignore(Model_User $ignore) {
		if ($this->loaded()
			&& $this->is_ignored($ignore)
			&& (bool)Jelly::query('ignore')
				->where('user_id', '=', $this->id)
				->where('ignore_id', '=', $ignore->id)
				->select()
				->delete()) {

			// Clear caches
			Anqh::cache_delete('ignores_' . $this->id);
			Anqh::cache_delete('ignorers_' . $ignore->id);

		  return true;
		}

	  return false;
	}


	/**
	 * Get user's friends
	 *
	 * @return  array
	 */
	public function find_friends() {
		$ckey = 'friends_';

		// Try static cache
		$friends = Anqh::cache_get($ckey . $this->id);
		if (is_null($friends)) {

			// Load from DB
			$friends = array();
			foreach (Jelly::query('friend')->where('user_id', '=', $this->id)->select() as $friend) {
				$friends[] = $friend->friend->id;
			}

			Anqh::cache_set($ckey . $this->id, $friends, Date::HOUR);
		}

		return $friends;
	}


	/**
	 * Get user's ignores or users ignoring user
	 *
	 * @param   boolean  $ignorers  true for ignorers, false for ignores
	 * @return  array
	 */
	public function find_ignores($ignorers = false) {
		$ckey = $ignorers ? 'ignorers_' : 'ignores_';

		// Try static cache
		$ignores = Anqh::cache_get($ckey . $this->id);
		if (is_null($ignores)) {

			// Load from DB
			$ignores = array();
			foreach (Jelly::query('ignore')->where($ignorers ? 'ignore_id' : 'user_id', '=', $this->id)->select() as $ignore) {
				$ignores[] = $ignorers ? $ignore->user->id : $ignore->ignore->id;
			}

			Anqh::cache_set($ckey . $this->id, $ignores, Date::HOUR);
		}

		return $ignores;
	}


	/**
	 * Get user's total friend count
	 *
	 * @return  integer
	 */
	public function get_friend_count() {
		return (int)Jelly::query('friend')
			->where('user_id', '=', $this->id)
			->count();
	}


	/**
	 * Get user's default image url
	 *
	 * @param   string  $size
	 * @return  string
	 */
	public function get_image_url($size = null) {
		if ($this->default_image) {
			$image = Model_Image::find($this->default_image)->get_url($size);
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
	 * Check for friendship
	 *
	 * @param  mixed  $friend  Model_User, array, $id
	 */
	public function is_friend($friend) {
		if (Kohana::$profiling === true && class_exists('Profiler', false)) {
			$benchmark = Profiler::start('Anqh', __METHOD__);
		}

		if ($friend instanceof Model_User) {
			$friend = (int)$friend->id;
		} else if (is_array($friend)) {
			$friend = (int)Arr::get($friend, 'id');
		}

		if (!is_int($friend) || $friend == 0) {
			return false;
		}

		$is_friend = in_array($friend, $this->find_friends());

		if (isset($benchmark)) {
			Profiler::stop($benchmark);
		}

		return $is_friend;
	}


	/**
	 * Check for friendship
	 *
	 * @param  mixed    $friend      Model_User, array, $id
	 * @param  boolean  $ignored_by  Check if the user ignored by $ignore or ignoring $ignore
	 */
	public function is_ignored($ignore, $ignored_by = false) {
		if (Kohana::$profiling === true && class_exists('Profiler', false)) {
			$benchmark = Profiler::start('Anqh', __METHOD__);
		}

		if ($ignore instanceof Model_User) {
			$ignore = (int)$ignore->id;
		} else if (is_array($ignore)) {
			$ignore = (int)Arr::get($ignore, 'id');
		}

		if (!is_int($ignore) || $ignore == 0) {
			return false;
		}

		$is_ignored = in_array($ignore, $this->find_ignores($ignored_by));

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
					$user = Model_User::find($id);
				} else {
					$user = Jelly::query('user')
						->where(Valid::email($id) ? 'email' : 'username_clean', '=', $id)
						->limit(1)
						->select();
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
			/** @var  Model_User  $user */
			$user = $id->light_array();
			Anqh::cache_set($ckey . $id->id, $user, Date::DAY);
			return $user;

		} else if (is_array($id)) {

			// Got user array, don't fill caches as we're not 100% sure it's valid
			return $id;

		} else if (is_string($id)) {

			// Got user name, find id
			$id = Model_User::user_id($id);

		} else {

			// Got id
			$id = (int)$id;

		}

		if ($id == 0) {
			return null;
		}

		// Try static cache
		if (!$user = Anqh::cache_get($ckey . $id)) {

			// Load from DB
			$model = Jelly::query('user')
				->where(is_int($id) ? 'id' : 'username_clean', '=', $id)
				->limit(1)
				->select();
			$user = $model->light_array();

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
				'id'         => $this->id,
				'username'   => $this->username,
				'gender'     => $this->gender,
				'title'      => $this->title,
				'signature'  => $this->signature,
				'avatar'     => $this->avatar,
				'thumb'      => $this->get_image_url('thumbnail'),
				'last_login' => $this->last_login,
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
		if (is_int($user)) {

			// Already got id
			return $user;

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
	 * Does the user have any of these roles
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
	 * Get user's roles
	 *
	 * @return  array
	 */
	public function roles() {
		if (!$this->_roles) {
			$this->_roles = Model_Role::find_by_user($this);
		}

		return $this->_roles;
	}

}

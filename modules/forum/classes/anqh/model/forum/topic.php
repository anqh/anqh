<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Topic model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Topic extends AutoModeler_ORM implements Permission_Interface {

	/**
	 * Permission to post reply to topic
	 */
	const PERMISSION_POST = 'post';

	/** Normal topic */
	const STATUS_NORMAL = 0;

	/** Locked topic */
	const STATUS_LOCKED = 1;

	/** Sunk topic, don't update last posted */
	const STATUS_SINK = 2;

	/** Normal topic */
	const STICKY_NORMAL = 0;

	/** Sticky topic */
	const STICKY_STICKY = 1;

	protected $_table_name = 'forum_topics';

	protected $_data = array(
		'id'            => null,
		'forum_area_id' => null,
		'bind_id'       => null,

		'type'          => null,
		'status'        => self::STATUS_NORMAL,
		'sticky'        => self::STATUS_NORMAL,
		'read_only'     => null,
		'votes'         => null,
		'points'        => null,

		'name'          => null,
		'old_name'      => null,
		'author_id'     => null,
		'author_name'   => null,

		'first_post_id' => null,
		'last_post_id'  => null,
		'last_posted'   => null,
		'last_poster'   => null,
		'read_count'    => 0,
		'post_count'    => 0,
	);

	protected $_has_many = array(
		'posts'
	);

	protected $_rules = array(
		'forum_area_id' => array('not_empty', 'digit'),

		'status'        => array('in_array' => array(':value', array(self::STATUS_LOCKED, self::STATUS_SINK, self::STATUS_NORMAL))),
		'sticky'        => array('in_array' => array(':value', array(self::STICKY_NORMAL, self::STICKY_STICKY))),

		'name'          => array('not_empty', 'max_length' => array(':value', 128)),

		'first_post_id' => array('digit'),
		'last_post_id'  => array('digit'),
	);


	/**
	 * Magic setter
	 *
	 * @param  string  $key
	 * @param  mixed   $value
	 */
	public function __set($key, $value)	{
		switch ($key) {

			// Legacy status <-> type
			case 'status':
				if ($value == self::STATUS_LOCKED && $this->type < 10) {
					$this->type += 10;
				} else if ($value !== self::STATUS_LOCKED && $this->type >= 10) {
					$this->type -= 10;
				}
				break;

		}

		parent::__set($key, $value);
	}


	/**
	 * Get topic area
	 *
	 * @return  Model_Forum_Area
	 */
	public function area() {
		return new Model_Forum_Area($this->forum_area_id);
	}


	/**
	 * Find active topics.
	 *
	 * @param   integer  $limit
	 * @return  Model_Forum_Topic[]
	 */
	public function find_active($limit = 10) {
		return $this->load(
			DB::select_array($this->fields())
				->order_by('last_posted', 'DESC'),
			$limit
		);
	}


	/**
	 * Load topic by bound model
	 *
	 * @static
	 * @param   Model   $bind_model  Bound model
	 * @param   string  $bind_name   Bind config if multiple binds per model
	 * @return  Model_Forum_Topic
	 */
	public static function find_by_bind(Model $bind_model, $bind_name = null) {
		$model = Model::model_name($bind_model);

		// Get correct bind config
		if (!$bind_name) {
			foreach (Model_Forum_Area::get_binds(false) as $bind_name => $bind_config) {
				if ($bind_config['model'] == $model) {
					$config = $bind_config;
					break;
				}
			}
		} else {
			$config = Model_Forum_Area::get_binds($bind_name);
		}

		if ($config) {

			// Get area
			$area = Model_Forum_Area::factory();
			$area = $area->load(
				DB::select_array($area->fields())
					->where('area_type', '=', Model_Forum_Area::TYPE_BIND)
					->where('bind', '=', $bind_name)
			);
			if ($area->loaded()) {

				// Get topic
				$topic = Model_Forum_Topic::factory();
				$topic = $topic->load(
					DB::select_array($topic->fields())
						->where('forum_area_id', '=', $area->id)
						->where('bind_id', '=', $bind_model->id())
				);

				// If topic found, go there!
				if ($topic->loaded()) {
					return $topic;
				}

			}
		}

		return null;
	}


	/**
	 * Find latest topics
	 *
	 * @param   integer  $limit
	 * @return  Model_Forum_Topic[]
	 */
	public function find_by_latest_post($limit = 10) {
		return $this->load(
			DB::select_array($this->fields())
				->order_by('last_posted', 'DESC'),
			$limit
		);
	}


	/**
	 * Find new topics
	 *
	 * @param   integer  $limit
	 * @return  Model_Forum_Topic[]
	 */
	public function find_new($limit = 10) {
		return $this->load(
			DB::select_array($this->fields())
				->order_by('id', 'DESC'),
			$limit
		);
	}


	/**
	 * Find a post's number in topic.
	 *
	 * @param   integer  $post_id
	 * @return  integer
	 */
	public function get_post_number($post_id) {
		return (int)DB::select(array(DB::expr('COUNT(id)'), 'posts'))
			->from(Model_Forum_Post::factory()->get_table_name())
			->where('forum_topic_id', '=', $this->id)
			->where('id', '<', (int)$post_id)
			->execute($this->_db)
			->get('posts');
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

			case self::PERMISSION_DELETE:
				return $user && $user->has_role('admin');

			case self::PERMISSION_POST:
		    return $user && ($this->status !== self::STATUS_LOCKED || $user->has_role('admin'));

			case self::PERMISSION_READ:
				return Permission::has($this->area(), Model_Forum_Area::PERMISSION_READ, $user);

			case self::PERMISSION_UPDATE:
				return $user && (($this->status !== self::STATUS_LOCKED && $user->id == $this->author_id) || $user->has_role('admin'));

		}

	  return false;
	}


	/**
	 * Get area last topic
	 *
	 * @return  Model_Forum_Post
	 */
	public function last_post() {
		return Model_Forum_Post::factory($this->last_post_id);
	}


	/**
	 * Find topic posts by page.
	 *
	 * @param   integer  $offset
	 * @param   integer  $limit
	 * @return  Model_Forum_Post[]
	 */
	public function posts($offset, $limit) {
		$post = Model_Forum_Post::factory();

		$query = DB::select_array($post->fields())
			->where('forum_topic_id', '=', $this->id)
			->order_by('created', 'ASC');

		if ($offset || $limit) {
			return $post->load($query->offset($offset), $limit);
		} else {
			return $post->load($query, null);
		}
	}


	/**
	 * Refresh topic foreign values
	 *
	 * @param   boolean  $save
	 */
	public function refresh($save = true) {
		if (!$this->loaded()) {
			return false;
		}

		// Get all posts for current topic
		$posts = $this->posts();
		$this->post_count = count($posts);

		// First post
		$this->first_post_id = $posts[0]->id;

		// Last post
		$last_post = $posts[$this->post_count - 1];
		$this->last_post_id = $last_post->id;
		$this->last_posted  = $last_post->created;
		$this->last_poster  = $last_post->author_name;

		if ($save) {
			$this->save();
		}

		return true;
	}

}

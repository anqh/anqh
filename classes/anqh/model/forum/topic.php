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

	/**
	 * Normal topic
	 */
	const STATUS_NORMAL = 0;

	/**
	 * Locked topic
	 */
	const STATUS_LOCKED = 1;

	/**
	 * Sunk topic, don't update last posted
	 */
	const STATUS_SINK = 2;

	protected $_table_name = 'forum_topics';

	protected $_data = array(
		'id'            => null,
		'forum_area_id' => null,
		'bind_id'       => null,

		'type'          => null,
		'status'        => self::STATUS_NORMAL,
		'sticky'        => false,
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
		'read_count'    => null,
		'post_count'    => null,
	);

	protected $_has_many = array(
		'posts'
	);

	protected $_rules = array(
		'forum_area_id' => array('not_empty', 'digit'),

		'status'        => array('in_array', array(':value', array(self::STATUS_LOCKED, self::STATUS_SINK, self::STATUS_NORMAL))),

		'name'          => array('not_empty', 'max_length' => array(':value', 128)),

		'first_post_id' => array('digit'),
		'last_post_id'  => array('digit'),
	);


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
				->order_by('last_post_id', 'DESC'),
			$limit
		);
	}


	/**
	 * Load topic by bound model
	 *
	 * @static
	 * @param   Jelly_Model  $bind_model  Bound model
	 * @param   string       $bind_name   Bind config if multiple binds per model
	 * @return  Model_Forum_Topic
	 */
	public static function find_by_bind(Jelly_Model $bind_model, $bind_name = null) {
		$model = Jelly::class_name($bind_model);

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
			$area = Jelly::query('forum_area')
				->where('area_type', '=', Model_Forum_Area::TYPE_BIND)
				->and_where('bind', '=', $bind_name)
				->limit(1)
				->select();

			if ($area->loaded()) {

				// Get topic
				$topic = Jelly::query('forum_topic')
					->where('forum_area_id', '=', $area->id)
					->and_where('bind_id', '=', $bind_model->id())
					->limit(1)
					->select();

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
				->order_by('last_post_id', 'DESC'),
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
	 * Find topic posts by page
	 *
	 * @param   Pagination  $pagination
	 * @return  Jelly_Collection
	 */
	public function find_posts(Pagination $pagination) {
		return Jelly::query('forum_post')
			->with('topic')
			->where('forum_topic_id', '=', $this->id)
			->pagination($pagination)
			->select();
	}


	/**
	 * Find a post's number in topic
	 *
	 * @param   integer  $post_id
	 * @return  integer
	 */
	public function get_post_number($post_id) {
		return $this->get('posts')
			->where('id', '<', (int)$post_id)
			->count();
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
	 * Refresh topic foreign values
	 *
	 * @param   boolean  $save
	 */
	public function refresh($save = true) {
		if (!$this->loaded()) {
			return false;
		}

		// First post
		$first_post = Jelly::query('forum_post')
			->where('forum_topic_id', '=', $this->id)
			->order_by('id', 'ASC')
			->limit(1)
			->select();
		$this->first_post = $first_post;

		// Last post
		$last_post = Jelly::query('forum_post')
			->where('forum_topic_id', '=', $this->id)
			->order_by('id', 'DESC')
			->limit(1)
			->select();
		$this->last_post = $last_post;
		$this->last_posted = $last_post->created;
		$this->last_poster = $last_post->author_name;

		$this->post_count = count($this->posts);

		if ($save) {
			$this->save();
		}

		return true;
	}

}

<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Area model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Area extends AutoModeler_ORM implements Permission_Interface {

	/** Permission to post new topic */
	const PERMISSION_POST = 'post';

	/** Everybody can read area */
	const READ_NORMAL = 0;

	/** Only memberes can read area */
	const READ_MEMBERS = 1;

	/** Visible area */
	const STATUS_NORMAL = 0;

	/** Hidden area */
	const STATUS_HIDDEN = 1;

	/** Normal area */
	const TYPE_NORMAL = 0;

	/** Topics bound to foreign model */
	const TYPE_BIND = 1;

	/** Private area */
	const TYPE_PRIVATE = 2;

	/** Members can start new topics */
	const WRITE_NORMAL = 0;

	/** Only admins can start new topics */
	const WRITE_ADMINS = 1;

	protected $_table_name = 'forum_areas';

	protected $_data = array(
		'id'             => null,
		'forum_group_id' => null,
		'name'           => null,
		'description'    => null,
		'sort'           => 0,
		'created'        => null,
		'author_id'      => null,

		'access_read'    => self::READ_NORMAL,
		'access_write'   => self::WRITE_NORMAL,
		'status'         => self::STATUS_NORMAL,
		'area_type'      => self::TYPE_NORMAL,
		'bind'           => null,

		'post_count'     => null,
		'topic_count'    => null,
		'last_topic_id'  => null,
	);

	protected $_has_many = array(
		'forum_topics'
	);

	protected $_rules = array(
		'forum_group_id' => array('digit'),
		'name'           => array('not_empty', 'max_length' => array(':value', 64)),
		'description'    => array('max_length' => array(':value', 250)),

		'access_read'    => array('not_empty', 'in_array' => array(':value', array(self::READ_MEMBERS, self::READ_NORMAL))),
		'access_write'   => array('not_empty', 'in_array' => array(':value', array(self::WRITE_ADMINS, self::WRITE_NORMAL))),
		'status'         => array('not_empty', 'in_array' => array(':value', array(self::STATUS_HIDDEN, self::STATUS_NORMAL))),
		'area_type'      => array('not_empty', 'in_array' => array(':value', array(self::TYPE_PRIVATE, self::TYPE_BIND, self::TYPE_NORMAL))),
	);

	/** @var  array  User editable fields */
	public static $editable_fields = array(
		'group', 'name', 'description', 'sort', 'access_read', 'access_write', 'status', 'area_type', 'bind'
	);

	/** @var  Model_Forum_Topic|Model_Forum_Private_Topic  Unsaved topic */
	public $unsaved_topic;


	/**
	 * Get area bind config.
	 *
	 * @return  array
	 */
	public function bind_config() {
		if ($this->area_type == self::TYPE_BIND && $this->bind) {
			$config = Kohana::$config->load('forum.binds');

			return Arr::get($config, $this->bind);
		}

		return null;
	}


	/**
	 * Create new forum topic to area.
	 *
	 * @param   string            $name
	 * @param   string            $content
	 * @param   Model_User|array  $author
	 * @return  Model_Forum_Private_Topic|Model_Forum_Topic
	 */
	public function create_topic($name, $content, $author) {
		$topic = $this->area_type == self::TYPE_PRIVATE
			? new Model_Forum_Private_Topic()
			: new Model_Forum_Topic();

		$topic->forum_area_id = $this->id;
		$topic->name          = $name;
		if (is_array($author)) {
			$topic->author_id    = $author['id'];
			$topic->author_name  = $author['username'];
		} else if (is_object($author)) {
			$topic->author_id    = $author->id;
			$topic->author_name  = $author->username;
		}

		// Create post
		$topic->create_post($content, $author);

		return $topic;
	}


	/**
	 * Find area's paginated active topics
	 *
	 * @param   integer  $offset
	 * @param   integer  $limit
	 * @return  Model_Forum_Topic[]
	 */
	public function find_active_topics($offset = 0, $limit = 10) {
		$topic = new Model_Forum_Topic();

		return $topic->load(
			DB::select_array($topic->fields())
				->where('forum_area_id', '=', $this->id)
				->order_by('sticky', 'DESC')
				->order_by('last_posted', 'DESC')
				->offset(max(0, $offset)),
			$limit
		);
	}


	/**
	 * Get area by bind.
	 *
	 * @param   string  $bind
	 * @return  Model_Forum_Area
	 */
	public function find_by_bind($bind) {
		return $this->load(
			DB::select_array($this->fields())
				->where('area_type', '=', Model_Forum_Area::TYPE_BIND)
				->where('status', '=', Model_Forum_Area::STATUS_NORMAL)
				->where('bind', '=', $bind)
		);
	}


	/**
	 * Get list of possible model bindings
	 *
	 * @param   boolean|string  true = short list, false = full list, string = specific bind
	 * @return  array
	 */
	public static function get_binds($bind = true) {
		$config = Kohana::$config->load('forum.binds');
		if ($bind === true) {

			// Short list for selects etc
			$list = array();
			foreach ($config as $type => $data) {
				$list[$type] = $data['name'];
			}
			return $list;

		} else if ($bind === false) {

			// Full bind config
			return $config;

		} else if (is_string($bind)) {

			// Specific config
			return Arr::get($config, $bind);

		}
	}


	/**
	 * Get forum group.
	 *
	 * @return  Model_Forum_Group
	 */
	public function group() {
		try {
			return $this->forum_group_id ? Model_Forum_Group::factory($this->forum_group_id) : null;
		} catch (AutoModeler_Exception $e) {
			return null;
		}
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

			case self::PERMISSION_CREATE:
			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
		    return $user && $user->has_role('admin');

			case self::PERMISSION_POST:
		    return $user
			    && ($this->access_write != self::WRITE_ADMINS
				    && $this->area_type != self::TYPE_BIND
				    && $this->status != self::STATUS_HIDDEN
				    || $user->has_role('admin')
			    );

			case self::PERMISSION_READ:
				return $this->status == self::STATUS_NORMAL
					&& $this->area_type != self::TYPE_PRIVATE
					&& ($this->access_read == self::READ_NORMAL || $user);

		}

		return false;
	}


	/**
	 * Get area last topic
	 *
	 * @return  Model_Forum_Topic
	 */
	public function last_topic() {
		return Model_Forum_Topic::factory($this->last_topic_id);
	}


	/**
	 * Refresh area data.
	 * Potentially heavy function, use with caution!
	 *
	 * @param  boolean  $save
	 */
	public function refresh($save = true) {
		if (!$this->loaded()) {
			return false;
		}

		// Get table names
		if ($this instanceof Anqh_Model_Forum_Area) {
			$topic_table = Model_Forum_Topic::factory()->get_table_name();
			$post_table  = Model_Forum_Post::factory()->get_table_name();
		} else {
			$topic_table = Model_Forum_Private_Topic::factory()->get_table_name();
			$post_table  = Model_Forum_Private_Post::factory()->get_table_name();
		}

		// Stats
		$this->topic_count = (int)DB::select(array(DB::expr('COUNT(id)'), 'topics'))
				->from($topic_table)
				->where('forum_area_id', '=', $this->id)
				->execute($this->_db)
				->get('topics');
		$this->post_count = (int)DB::select(array(DB::expr('COUNT(id)'), 'posts'))
				->from($post_table)
				->where('forum_area_id', '=', $this->id)
				->execute($this->_db)
				->get('posts');

		// Last topic
		$this->last_topic_id = (int)DB::select(array(DB::expr('MAX(id)'), 'topic_id'))
				->from($topic_table)
				->where('forum_area_id', '=', $this->id)
				->execute($this->_db)
				->get('topic_id');

		if ($save) {
			$this->save();
		}

		return true;
	}

}

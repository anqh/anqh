<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Area model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Area extends Jelly_Model implements Permission_Interface {

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

	/** @var  array  User editable fields */
	public static $editable_fields = array(
		'group', 'name', 'description', 'sort', 'access_read', 'access_write', 'status', 'type', 'bind'
	);


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->sorting(array('sort' => 'ASC'));
		$meta->fields(array(
			'id' => new Jelly_Field_Primary,
			'group' => new Jelly_Field_BelongsTo(array(
				'label'   => __('Forum group'),
				'column'  => 'forum_group_id',
				'foreign' => 'forum_group',
			)),
			'name' => new Jelly_Field_String(array(
				'label' => __('Area name'),
				'rules' => array(
					'not_empty' => array(true),
					'max_length' => array(64),
				),
			)),
			'description' => new Jelly_Field_String(array(
				'label' => __('Description'),
				'rules' => array(
					'max_length' => array(250),
				),
			)),
			'sort' => new Jelly_Field_Integer(array(
				'label'   => __('Sort'),
				'default' => 0,
			)),
			'created' => new Jelly_Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'author' => new Jelly_Field_BelongsTo(array(
				'column'  => 'author_id',
				'foreign' => 'user',
			)),

			'access_read' => new Jelly_Field_Enum(array(
				'label'   => __('Read access'),
				'default' => self::READ_NORMAL,
				'choices' => array(
					self::READ_MEMBERS => 'Members only',
					self::READ_NORMAL  => 'Everybody',
				),
				'rules'   => array(
					'not_empty' => null,
				)
			)),
			'access_write' => new Jelly_Field_Enum(array(
				'null'    => true,
				'label'   => __('Write access'),
				'default' => self::WRITE_NORMAL,
				'choices' => array(
					self::WRITE_ADMINS => 'Admins only',
					self::WRITE_NORMAL => 'Members',
				),
				'rules'   => array(
					'not_empty' => null,
				)
			)),
			'status' => new Jelly_Field_Enum(array(
				'label'   => __('Status'),
				'default' => self::STATUS_NORMAL,
				'choices' => array(
					self::STATUS_HIDDEN => 'Hidden',
					self::STATUS_NORMAL => 'Normal',
				),
				'rules'   => array(
					'not_empty' => null,
				)
			)),
			'type' => new Jelly_Field_Enum(array(
				'label'   => __('Type'),
				'column'  => 'area_type',
				'default' => self::TYPE_NORMAL,
				'choices' => array(
					self::TYPE_PRIVATE => 'Private',
					self::TYPE_BIND    => 'Bind, topics bound to foreign model',
					self::TYPE_NORMAL  => 'Normal',
				),
				'rules'   => array(
					'not_empty' => null,
				)
			)),
			'bind' => new Jelly_Field_Enum(array(
				'label'   => __('Bind config'),
				'choices' => array('' => __('None')) + self::get_binds(),
			)),

			'post_count' => new Jelly_Field_Integer(array(
				'column' => 'posts',
			)),
			'topic_count' => new Jelly_Field_Integer(array(
				'column' => 'topics',
			)),
			'last_topic' => new Jelly_Field_BelongsTo(array(
				'column'  => 'last_topic_id',
				'foreign' => 'forum_topic',
			)),
			'topics' => new Jelly_Field_HasMany(array(
				'foreign' => 'forum_topic'
			))
		));

		return $meta;
	}


	/**
	 * Find area's paginated active topics
	 *
	 * @param   Pagination $pagination
	 * @return  Jelly_Collection
	 */
	public function find_active_topics(Pagination $pagination) {
		return Jelly::query('forum_topic')
			->with('last_post')
			->where('forum_area_id', '=', $this->id)
			->order_by('last_post_id', 'DESC')
			->pagination($pagination)
			->query();
	}


	/**
	 * Get list of possible model bindings
	 *
	 * @param   boolean|string  true = short list, false = full list, string = specific bind
	 * @return  array
	 */
	public static function get_binds($bind = true) {
		$config = Kohana::config('forum.binds');
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
				    && $this->type != self::TYPE_BIND
				    && $this->status != self::STATUS_HIDDEN
				    || $user->has_role('admin')
			    );

			case self::PERMISSION_READ:
				return $this->status == self::STATUS_NORMAL
					&& $this->type != self::TYPE_PRIVATE
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
		return $this->last_topic instanceof Model_Forum_Topic ?
			$this->last_topic :
			Jelly::query('forum_topic')
				->with('author')
				->where('id', '=', $this->last_topic)
				->limit(1)
				->select();
	}

}

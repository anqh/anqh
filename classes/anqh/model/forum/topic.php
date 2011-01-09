<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Topic model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Topic extends Jelly_Model implements Permission_Interface {

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


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->fields(array(
				'id'          => new Field_Primary,
				'area'        => new Field_BelongsTo(array(
					'column'  => 'forum_area_id',
					'foreign' => 'forum_area',
					'rules'   => array(
						'not_empty' => array(true),
					)
				)),
				'name'        => new Field_String(array(
					'label' => __('Topic'),
					'rules' => array(
						'not_empty'  => array(true),
						'max_length' => array(128),
					),
					'filters' => array(
						'trim' => null,
					),
				)),
				'old_name'    => new Field_String,
				'author'      => new Field_BelongsTo(array(
					'column'  => 'author_id',
					'foreign' => 'user',
				)),
				'author_name' => new Field_String,
				'created' => new Field_Timestamp(array(
					'auto_now_create' => true,
				)),
				'type'        => new Field_Integer,
				'status'      => new Field_Enum(array(
					'label'   => __('Status'),
					'default' => self::STATUS_NORMAL,
					'choices' => array(
						self::STATUS_LOCKED => __('Locked'),
						self::STATUS_SINK   => __('Sink'),
						self::STATUS_NORMAL => __('Normal'),
					)
				)),
				'sticky'      => new Field_Boolean(array(
					'label'   => __('Sticky'),
					'default' => false
				)),
				'read_only'   => new Field_Boolean,
				'first_post'  => new Field_BelongsTo(array(
					'column'  => 'first_post_id',
					'foreign' => 'forum_post',
				)),
				'last_post'   => new Field_BelongsTo(array(
					'column'  => 'last_post_id',
					'foreign' => 'forum_post',
				)),
				'last_posted' => new Field_Integer,
				'last_poster' => new Field_String,
				'read_count'   => new Field_Integer(array(
					'column' => 'reads'
				)),
				'post_count'   => new Field_Integer(array(
					'column' => 'posts',
				)),
				'votes'       => new Field_Integer,
				'points'      => new Field_Integer,
				'bind_id'     => new Field_Integer,
				'posts'       => new Field_HasMany(array(
					'foreign' => 'forum_post',
				))
			));
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
			$area = Jelly::select('forum_area')
				->where('area_type', '=', Model_Forum_Area::TYPE_BIND)
				->and_where('bind', '=', $bind_name)
				->limit(1)
				->execute();

			if ($area->loaded()) {

				// Get topic
				$topic = Jelly::select('forum_topic')
					->where('forum_area_id', '=', $area->id)
					->and_where('bind_id', '=', $bind_model->id())
					->limit(1)
					->execute();

				// If topic found, go there!
				if ($topic->loaded()) {
					return $topic;
				}

			}
		}

		return null;
	}


	/**
	 * Find a post's number in topic
	 *
	 * @param   integer  $post_id
	 * @return  integer
	 */
	public function get_post_number($post_id) {
		$query = DB::select(array('COUNT("id")', 'posts'))
			->from('forum_posts')
			->where('forum_topic_id', '=', $this->id)
			->and_where('id', '<', (int)$post_id)
			->execute()
			->current();

		return (int)Arr::get($query, 'posts', 0);
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
		    return $user && ($this->status != self::STATUS_LOCKED || $user->has_role('admin'));

			case self::PERMISSION_READ:
				return Permission::has($this->area, Model_Forum_Area::PERMISSION_READ, $user);

			case self::PERMISSION_UPDATE:
				return $user && (($this->status != self::STATUS_LOCKED && $user->id == $this->original('author')) || $user->has_role('admin'));

		}

	  return false;
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
		$first_post = Jelly::select('forum_post')->where('forum_topic_id', '=', $this->id)->order_by('id', 'ASC')->limit(1)->execute();
		$this->first_post = $first_post;

		// Last post
		$last_post = Jelly::select('forum_post')->where('forum_topic_id', '=', $this->id)->order_by('id', 'DESC')->limit(1)->execute();
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

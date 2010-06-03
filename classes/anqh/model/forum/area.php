<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Area model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Area extends Jelly_Model implements Interface_Permission {

	/**
	 * Permission to post new topic
	 */
	const PERMISSION_POST = 'post';

	/**
	 * Normal area
	 */
	const TYPE_NORMAL = 0;

	/**
	 * Read-only area
	 */
	const TYPE_READONLY = 1;

	/**
	 * Log-in required area
	 */
	const TYPE_LOGGED = 2;

	/**
	 * Private area
	 */
	const TYPE_PRIVATE = 4;

	/**
	 * Bound area, topics bound to other model
	 */
	const TYPE_BIND = 8;

	/**
	 * Hidden area
	 */
	const TYPE_HIDDEN = 128;


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->sorting(array('sort' => 'ASC'))
			->fields(array(
				'id' => new Field_Primary,
				'name' => new Field_String(array(
					'rules' => array(
						'not_empty' => array(true),
						'max_length' => array(64),
					),
				)),
				'description' => new Field_String(array(
					'rules' => array(
						'max_length' => array(250),
					),
				)),
				'sort' => new Field_Integer,
				'type' => new Field_Integer,
				'created' => new Field_Timestamp(array(
					'auto_now_create' => true,
				)),
				'num_posts' => new Field_Integer(array(
					'column' => 'posts',
				)),
				'num_topics' => new Field_Integer(array(
					'column' => 'topics',
				)),
				'access' => new Field_Integer,
				'author' => new Field_BelongsTo(array(
					'column'  => 'author_id',
					'foreign' => 'user',
				)),
				'bind' => new Field_String,
				'group' => new Field_BelongsTo(array(
					'column'  => 'forum_group_id',
					'foreign' => 'forum_group',
				)),
				'last_topic' => new Field_HasOne(array(
					'column'  => 'last_topic_id',
					'foreign' => 'forum_topic',
				)),
				'topics' => new Field_HasMany(array(
					'foreign' => 'forum_topic'
				))
			));
	}


	/**
	 * Check permission
	 *
	 * @param   string      $permission
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function has_permission($permission, $user) {
		$status = false;

		switch ($permission) {
			case self::PERMISSION_CREATE:
			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
		    $status = $user && $user->has_role('admin');
		    break;

			case self::PERMISSION_POST:
		    if ($user && !$this->is_type(self::TYPE_HIDDEN)) {
			    $status = (!$this->is_type(self::TYPE_READONLY | self::TYPE_BIND) || $user->has_role('admin'));
		    }
		    break;

			case self::PERMISSION_READ:
		    if (!$this->is_type(self::TYPE_HIDDEN)) {
			    $status = ($user || !$this->is_type(self::TYPE_LOGGED | self::TYPE_PRIVATE));
		    }
		    break;

		}

		return $status;
	}


	/**
	 * Check area access type
	 *
	 * @param   integer  $area_type
	 * @return  boolean
	 *
	 * @see  TYPE_NORMAL
	 * @see  TYPE_READONLY
	 * @see  TYPE_LOGGED
	 * @see  TYPE_PRIVATE
	 * @see  TYPE_BIND
	 */
	public function is_type($area_type) {
		return $area_type == self::TYPE_NORMAL ? ($this->access === 0) : (bool)$this->access & $area_type;
	}
}

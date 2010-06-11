<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Area model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Area extends Jelly_Model implements Permission_Interface {

	/**
	 * Permission to post new topic
	 */
	const PERMISSION_POST = 'post';

	/**
	 * Everybody can read area
	 */
	const READ_NORMAL = 0;

	/**
	 * Only memberes can read area
	 */
	const READ_MEMBERS = 1;

	/**
	 * Visible area
	 */
	const STATUS_NORMAL = 0;

	/**
	 * Hidden area
	 */
	const STATUS_HIDDEN = 1;

	/**
	 * Normal area
	 */
	const TYPE_NORMAL = 0;

	/**
	 * Topics bound to foreign model
	 */
	const TYPE_BIND = 1;

	/**
	 * Private area
	 */
	const TYPE_PRIVATE = 2;

	/**
	 * Members can start new topics
	 */
	const WRITE_NORMAL = 0;

	/**
	 * Only admins can start new topics
	 */
	const WRITE_ADMINS = 1;


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->sorting(array('sort' => 'ASC'))
			->fields(array(
				'id' => new Field_Primary,
				'group' => new Field_BelongsTo(array(
					'label'   => __('Forum group'),
					'column'  => 'forum_group_id',
					'foreign' => 'forum_group',
				)),
				'name' => new Field_String(array(
					'label' => __('Area name'),
					'rules' => array(
						'not_empty' => array(true),
						'max_length' => array(64),
					),
					'filters' => array(
						'trim' => null,
					),
				)),
				'description' => new Field_String(array(
					'label' => __('Description'),
					'rules' => array(
						'max_length' => array(250),
					),
					'filters' => array(
						'trim' => null,
					),
				)),
				'sort' => new Field_Integer(array(
					'label'   => __('Sort'),
					'default' => 0,
				)),
				'created' => new Field_Timestamp(array(
					'auto_now_create' => true,
				)),
				'author' => new Field_BelongsTo(array(
					'column'  => 'author_id',
					'foreign' => 'user',
				)),
				'bind' => new Field_String,

				'access_read' => new Field_Enum(array(
					'label'   => __('Read access'),
					'default' => self::READ_NORMAL,
					'choices' => array(
						self::READ_MEMBERS => 'Members only',
						self::READ_NORMAL  => 'Everybody',
					),
					'rules'   => array(
						'not_empty' => array(true),
					)
				)),
				'access_write' => new Field_Enum(array(
					'label'   => __('Write access'),
					'default' => self::WRITE_NORMAL,
					'choices' => array(
						self::WRITE_ADMINS => 'Admins only',
						self::WRITE_NORMAL => 'Members',
					),
					'rules'   => array(
						'not_empty' => array(true),
					)
				)),
				'status' => new Field_Enum(array(
					'label'   => __('Status'),
					'default' => self::STATUS_NORMAL,
					'choices' => array(
						self::STATUS_HIDDEN => 'Hidden',
						self::STATUS_NORMAL => 'Normal',
					),
					'rules'   => array(
						'not_empty' => array(true),
					)
				)),
				'type' => new Field_Enum(array(
					'label'   => __('Type'),
					'column'  => 'area_type',
					'default' => self::TYPE_NORMAL,
					'choices' => array(
						self::TYPE_PRIVATE => 'Private',
						self::TYPE_BIND    => 'Bind, topics bound to foreign model',
						self::TYPE_NORMAL  => 'Normal',
					),
					'rules'   => array(
						'not_empty' => array(true),
					)
				)),

				'num_posts' => new Field_Integer(array(
					'column' => 'posts',
				)),
				'num_topics' => new Field_Integer(array(
					'column' => 'topics',
				)),
				'last_topic' => new Field_BelongsTo(array(
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
		    $status = $user
			    && ($this->access_write != self::WRITE_ADMINS
				    && $this->type != self::TYPE_BIND
				    && $this->status != self::STATUS_HIDDEN
				    || $user->has_role('admin')
			    );
		    break;

			case self::PERMISSION_READ:
				$status = $this->status == self::STATUS_NORMAL
					&& $this->type != self::TYPE_PRIVATE
					&& ($this->access_read == self::READ_NORMAL || $user);
		    break;

		}

		return $status;
	}

}

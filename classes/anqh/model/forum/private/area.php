<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Private Area model
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Forum_Private_Area extends Model_Forum_Area {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->table('forum_areas')
			->fields(array(
				'last_topic' => new Field_BelongsTo(array(
					'column'  => 'last_topic_id',
					'foreign' => 'forum_private_topic',
				)),
				'topics' => new Field_HasMany(array(
					'foreign' => 'forum_private_topic'
				))
			));

		parent::initialize($meta);
	}


	/**
	 * Find active topics
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 *
	 * @todo  Remove
	 */
	public static function find_active($limit = 10, Model_User $user = null) {
		return null;
	}


	/**
	 * Find private areas
	 *
	 * @static
	 * @return  Jelly_Collection
	 */
	public static function find_areas() {
		return Jelly::select('forum_area')->where('type', '=', self::TYPE_PRIVATE)->execute();
	}


	/**
	 * Find private message topics
	 *
	 * @static
	 * @param   Model_User  $user
	 * @param   Pagination  $paginatinon
	 * @param   string      $type
	 * @return  Jelly_Collection
	 */
	public static function find_topics(Model_User $user, Pagination $paginatinon, $type = null) {
		$topics = Jelly::select('forum_private_topic')
			->join('forum_private_recipient')
			->on('forum_private_topic.:primary_key', '=', 'forum_private_recipient.forum_topic:foreign_key')
			->where('user_id', '=', $user->id)
			->order_by('last_post_id', 'DESC')
			->pagination($paginatinon);

		return $topics->execute();
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
			case self::PERMISSION_READ:
				return (bool)$user;

		}

		return false;
	}

}

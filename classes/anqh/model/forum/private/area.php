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

	protected $_has_many = array(
		'forum_private_topics'
	);


	/**
	 * Find active topics
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  null
	 *
	 * @todo  Remove
	 */
	public function find_active($limit = 10, Model_User $user = null) {
		return null;
	}


	/**
	 * Find private areas
	 *
	 * @return  Model_Forum_Private_Area[]
	 */
	public function find_areas() {
		return $this->load(
			DB::select_array($this->fields())
				->where('type', '=', self::TYPE_PRIVATE),
			null
		);
	}


	/**
	 * Find private message topics
	 *
	 * @static
	 * @param   Model_User  $user
	 * @param   Pagination  $pagination
	 * @param   string      $type
	 * @return  Model_Forum_Private_Topic[]
	 */
	public static function find_topics(Model_User $user, Pagination $pagination, $type = null) {
		$topic = Model_Forum_Private_Topic::factory();

		return $topic->load(
			DB::select_array($topic->fields())
				->join('forum_private_recipients')
				->on('forum_private_topics.id', '=', 'forum_private_recipients.forum_topic_id')
				->where('user_id', '=', $user->id)
				->order_by('last_post_id', 'DESC')
				->offset($pagination->offset),
			$pagination->items_per_page
		);
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

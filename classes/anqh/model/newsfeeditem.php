<?php defined('SYSPATH') or die('No direct script access.');
/**
 * NewsfeedItem model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_NewsfeedItem extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->sorting(array('id' => 'DESC'))
			->fields(array(
				'id'    => new Field_Primary,
				'user'  => new Field_BelongsTo,
				'stamp' => new Field_Timestamp(array(
					'auto_now_create' => true
				)),
				'class' => new Field_String,
				'type'  => new Field_String,
				'data'  => new Field_JSON,
			));
	}


	/**
	 * Find public Newsfeed items
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 */
	public static function find_items($limit = 20) {
		return Jelly::select('newsfeeditem')->limit($limit)->execute();
	}


	/**
	 * Find public Newsfeed items
	 *
	 * @static
	 * @param   Model_User  $user
	 * @param   integer     $limit
	 * @return  Jelly_Collection
	 */
	public static function find_items_personal(Model_User $user, $limit = 20) {
		return Jelly::select('newsfeeditem')->where('user_id', '=', $user->id)->limit($limit)->execute();
	}


	/**
	 * Find public Newsfeed items from user list
	 *
	 * @static
	 * @param   array    $users
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 */
	public static function find_items_users(array $users, $limit = 20) {
		return Jelly::select('newsfeeditem')->where('user_id', 'IN', $users)->limit($limit)->execute();
	}


}

<?php defined('SYSPATH') or die('No direct script access.');
/**
 * NewsfeedItem model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_NewsfeedItem extends Jelly_Model {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->sorting(array('id' => 'DESC'));
		$meta->fields(array(
			'id'    => new Jelly_Field_Primary,
			'user'  => new Jelly_Field_BelongsTo,
			'stamp' => new Jelly_Field_Timestamp(array(
				'auto_now_create' => true
			)),
			'class' => new Jelly_Field_String,
			'type'  => new Jelly_Field_String,
			'data'  => new Jelly_Field_JSON,
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
		return Jelly::query('newsfeeditem')->limit($limit)->select();
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
		return Jelly::query('newsfeeditem')
			->where('user:foreign_key', '=', $user->id)
			->limit($limit)
			->select();
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
		return Jelly::query('newsfeeditem')
			->where('user:foreign_key', 'IN', $users)
			->limit($limit)
			->select();
	}


}

<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Flyer model
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Flyer extends Jelly_Model implements Permission_Interface {

	/**
	 * Permission to post comments
	 */
	const PERMISSION_COMMENT = 'comment';

	/**
	 * Permission to read comments
	 */
	const PERMISSION_COMMENTS = 'comments';


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id'      => new Field_Primary,
			'image'   => new Field_BelongsTo,
			'event'   => new Field_BelongsTo,
		));
	}


	/**
	 * Find flyer by image id
	 *
	 * @param   integer  $image_id
	 * @return  Model_Flyer
	 */
	public static function find_by_image($image_id) {
		return Jelly::select('flyer')
			->where('image_id', '=', (int)$image_id)
			->limit(1)
			->execute();
	}


	/**
	 * Find latest flyers
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 */
	public static function find_latest($limit = 4) {
		return Jelly::select('flyer')
			->limit((int)$limit)
			->order_by('image_id', 'DESC')
			->execute();
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
			case self::PERMISSION_UPDATE:
				return $user && $user->has_role(array('admin', 'photo moderator'));

			case self::PERMISSION_COMMENT:
			case self::PERMISSION_COMMENTS:
			case self::PERMISSION_CREATE:
			case self::PERMISSION_READ:
		    return (bool)$user;
		}

		return false;
	}

}

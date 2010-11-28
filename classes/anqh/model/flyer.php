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
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->table('events_flyers');
		$meta->fields(array(
			'id'      => new Field_Primary,
			'image'   => new Field_BelongsTo,
			'event'   => new Field_BelongsTo,
		));
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
			case self::PERMISSION_CREATE:
			case self::PERMISSION_DELETE:
			case self::PERMISSION_READ:
			case self::PERMISSION_UPDATE:
		}

		return false;
	}

}

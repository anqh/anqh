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
	 * Permission to import flyers from flyer_url
	 */
	const PERMISSION_IMPORT = 'import';


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id'          => new Field_Primary,
			'image'       => new Field_BelongsTo,
			'event'       => new Field_BelongsTo,
			'name'        => new Field_String,
			'stamp_begin' => new Field_Timestamp
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
	 * Find flyers by year and month
	 *
	 * @static
	 * @param   integer  $year
	 * @param   integer  $month
	 * @return  Jelly_Collection
	 */
	public static function find_by_month($year, $month) {
		$start = mktime(0, 0, 0, $month, 1, $year);
		$end   = strtotime('+1 month', $start);
		return Jelly::select('flyer')
			->join('event')
			->on('flyer.event:foreign_key', '=', 'events.id')
			->where('stamp_begin', 'BETWEEN', array($start, $end))
			->order_by('stamp_begin', 'DESC')
			->order_by('events.id', 'DESC')
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
	 * Get months with flyers.
	 * Returns array of years => months => count
	 *
	 * @static
	 * @return  array
	 */
	public static function find_months() {
		$months = array();

		// Build counts
		$flyers = DB::query(
			Database::SELECT,
			"
SELECT TO_CHAR(TO_TIMESTAMP(events.stamp_begin), 'YYYY MM') AS month, COUNT(image_id) AS flyers
FROM flyers INNER JOIN events ON (flyers.event_id = events.id)
GROUP BY 1
"
		)->execute();

		foreach ($flyers as $flyer) {
			list($year, $month) = explode(' ', $flyer['month']);
			$months[(int)$year][(int)$month] = $flyer['flyers'];
		}

		// Sort years
		krsort($months);
		foreach ($months as &$year) {
			krsort($year);
		}

		return $months;
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
			case self::PERMISSION_IMPORT:
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

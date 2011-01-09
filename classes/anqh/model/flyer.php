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
			'name'        => new Field_String(array(
				'label' => __('Name'),
			)),
			'stamp_begin' => new Field_DateTime(array(
				'label'      => __('From'),
				'label_date' => __('Date'),
				'label_time' => __('At'),
				'rules' => array(
					'not_empty' => null,
				),
			)),
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
		if ($year == 1970 && $month == 0) {
			return Jelly::select('flyer')
				->where('stamp_begin', 'IS', null)
				->order_by('id', 'DESC')
				->execute();
		} else {
			$start = mktime(0, 0, 0, $month, 1, $year);
			$end   = strtotime('+1 month', $start);
			return Jelly::select('flyer')
				->where('stamp_begin', 'BETWEEN', array($start, $end))
				->order_by('stamp_begin', 'DESC')
				->order_by('event_id', 'DESC')
				->execute();
		}
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
			Database::SELECT, "
SELECT
	(CASE
		WHEN stamp_begin IS NULL THEN '1970 00'
		WHEN TO_CHAR(TO_TIMESTAMP(stamp_begin), 'DDD HH24 MI') = '001 00 00' THEN TO_CHAR(TO_TIMESTAMP(stamp_begin), 'YYYY 00')
		ELSE TO_CHAR(TO_TIMESTAMP(stamp_begin), 'YYYY MM')
	END) AS month,
	COUNT(image_id) AS flyers
FROM flyers
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
	 * Get flyers with new comments
	 *
	 * @static
	 * @param   Model_User $user
	 * @return  Jelly_Collection
	 */
	public static function find_new_comments(Model_User $user) {
		return Jelly::select('flyer')
			->join('image', 'INNER')
			->on('images.image:primary_key', '=', 'image:foreign_key')
			->where('author_id', '=', $user->id)
			->and_where('new_comment_count', '>', 0)
			->execute();
	}


	/**
	 * Does the flyer has a proper date or 1.1.2000 00:00:00 style
	 *
	 * @return  boolean
	 */
	public function has_full_date() {
		return $this->stamp_begin && date('j.n. H:i', $this->stamp_begin) != '1.1. 00:00';
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
				return $user && $user->has_role(array('admin', 'photo moderator'));

			case self::PERMISSION_COMMENT:
			case self::PERMISSION_COMMENTS:
			case self::PERMISSION_CREATE:
			case self::PERMISSION_READ:
		    return (bool)$user;

			case self::PERMISSION_UPDATE:
		    return $user && (!$this->has_full_date()/* || $user->has_role(array('admin', 'photo moderator'))*/);
		}

		return false;
	}

}

<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Flyer model
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Flyer extends AutoModeler_ORM implements Permission_Interface {

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

	protected $_table_name = 'flyers';

	protected $_data = array(
		'id'          => null,
		'image_id'    => null,
		'event_id'    => null,
		'name'        => null,
		'stamp_begin' => null,
	);

	protected $_rules = array(
		'image_id'    => array('not_empty'),
		'stamp_begin' => array('not_empty', 'digit'),
	);


	/**
	 * Override __set() to handle datetime.
	 *
	 * @param   string  $key
	 * @param   mixed   $value
	 */
	public function __set($key, $value) {
		if ($key == 'stamp_begin' && !is_numeric($value)) {
			$value = strtotime(is_array($value) ? $value['date'] . ' ' . $value['time'] : $value);
		}

		parent::__set($key, $value);
	}


	/**
	 * Get flyer event.
	 *
	 * @return  Model_Event
	 */
	public function event() {
		try {
			return $this->event_id ? new Model_Event($this->event_id) : null;
		} catch (AutoModeler_Exception $e) {
			return null;
		}
	}


	/**
	 * Find flyers by event id.
	 *
	 * @param   integer  $event_id
	 * @return  Database_Result
	 */
	public function find_by_event($event_id) {
		return $this->load(
			DB::select_array($this->fields())
				->where('event_id', '=', (int)$event_id),
			null
		);
	}


	/**
	 * Find flyer by image id.
	 *
	 * @param   integer  $image_id
	 * @return  Model_Flyer
	 */
	public function find_by_image($image_id) {
		return $this->load(
			DB::select_array($this->fields())
				->where('image_id', '=', (int)$image_id)
		);
	}


	/**
	 * Find flyers by year and month
	 *
	 * @param   integer  $year
	 * @param   integer  $month
	 * @return  Dabase_Result
	 */
	public function find_by_month($year, $month) {
		if ($year == 1970 && $month == 0) {
			return $this->load(
				DB::select_array($this->fields())
					->where('stamp_begin', 'IS', null)
					->order_by('id', 'DESC'),
				null
			);
		} else {
			$start = mktime(0, 0, 0, $month, 1, $year);
			$end   = strtotime('+1 month', $start);
			return $this->load(
				DB::select_array($this->fields())
					->where('stamp_begin', 'BETWEEN', array($start, $end))
					->order_by('stamp_begin', 'DESC')
					->order_by('event_id', 'DESC'),
				null
			);
		}
	}


	/**
	 * Find latest flyers
	 *
	 * @param   integer  $limit
	 * @return  Database_Result
	 */
	public function find_latest($limit = 4) {
		return $this->load(
			DB::select_array($this->fields())
				->order_by('image_id', 'DESC'),
			$limit
		);
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
	 * @param   Model_User  $user
	 * @return  Database_Result
	 */
	public function find_new_comments(Model_User $user) {
		return $this->load(
			DB::select_array($this->fields())
				->join('images', 'INNER')
				->on('images.id', '=', 'flyers.image_id')
				->where('author_id', '=', $user->id)
				->and_where('new_comment_count', '>', 0),
			null
		);
	}


	/**
	 * Get a random flyer
	 *
	 * @param   boolean  $unknown  Limit to unknown fliers (not linked to an event)
	 * @return  Model_Flyer
	 */
	public function find_random($unknown = false) {
		$query = DB::select_array($this->fields())
			->order_by(DB::expr('RANDOM()'));

		if ($unknown) {
			$query = $query->where('event_id', 'IS', null);
		}

		return $this->load($query);
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


	/**
	 * Get flyer image.
	 *
	 * @return  Model_Image
	 */
	public function image() {
		try {
			return $this->image_id ? Model_Image::factory($this->image_id) : null;
		} catch (AutoModeler_Exception $e) {
			return null;
		}
	}

}

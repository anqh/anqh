<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gallery model
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Gallery extends AutoModeler_ORM implements Permission_Interface {

	/**
	 * Permission to post comments
	 */
	const PERMISSION_COMMENT = 'comment';

	/**
	 * Permission to read comments
	 */
	const PERMISSION_COMMENTS = 'comments';

	/**
	 * Permission to upload images
	 */
	const PERMISSION_UPLOAD = 'upload';

	protected $_table_name = 'galleries';

	protected $_data = array(
		'id'               => null,
		'name'             => null,
		'links'            => null,
		'date'             => null,
		'event_id'         => null,
		'default_image_id' => null,
		'copyright'        => null,
		'dir'              => null,
		'mainfile'         => null,

		'image_count'      => null,
		'comment_count'    => null,
		'rate_count'       => null,
		'rate_total'       => null,
		'created'          => null,
		'updated'          => null,
	);

	protected $_rules = array(
		'name'             => array('not_empty', 'length' => array(':value', 3, 250)),
		'date'             => array('not_empty', 'digit'),
		'event_id'         => array('digit'),
		'default_image_id' => array('digit'),
	);

	protected $_has_many = array(
		'images'
	);


	/**
	 * @var  array  User editable fields
	 */
	public static $editable_fields = array(
		'name', 'event'
	);


	/**
	 * Get gallery default image.
	 *
	 * @return  Model_Image
	 */
	public function default_image() {
		try {
			return $this->default_image_id ? Model_Image::factory($this->default_image_id) : null;
		} catch (AutoModeler_Exception $e) {
			return null;
		}
	}


	/**
	 * Get event attached to gallery.
	 *
	 * @return  Model_Event
	 */
	public function event() {
		try {
			return $this->event_id ? Model_Event::factory($this->event_id) : null;
		} catch (AutoModeler_Exception $e) {
			return null;
		}
	}


	/**
	 * Find gallery by event id
	 *
	 * @param   integer  $event_id
	 * @return  Model_Gallery
	 */
	public function find_by_event($event_id) {
		return $this->load(
			DB::select_array($this->fields())
				->where('event_id', '=', (int)$event_id)
		);
	}


	/**
	 * Find gallery by image id
	 *
	 * @static
	 * @param   integer  $image_id
	 * @return  Model_Gallery
	 */
	public static function find_by_image($image_id) {
		try {
			return Model_Image::factory($image_id)->gallery();
		} catch (AutoModeler_Exception $e) {
			return null;
		}
	}


	/**
	 * Find multiple galleries by image ids
	 *
	 * @param   array  $image_ids
	 * @return  Model_Gallery[]
	 */
	public function find_by_images($image_ids) {
		return $this->load(
			DB::select_array($this->fields())
				->join('galleries_images')
				->on('galleries.id', '=', 'galleries_images.gallery_id')
				->where('image_id', 'IN', $image_ids),
			null
		);
	}


	/**
	 * Find galleries by year and month
	 *
	 * @param   integer  $year
	 * @param   integer  $month
	 * @return  Model_Gallery[]
	 */
	public function find_by_month($year, $month) {
		$start = mktime(0, 0, 0, $month, 1, $year);
		$end   = strtotime('+1 month', $start);

		return $this->load(
			DB::select_array($this->fields())
				->where('image_count', '>', 0)
				->where('date', 'BETWEEN', array($start, $end))
				->order_by('date', 'DESC'),
			null
		);
	}


	/**
	 * Find galleries by author.
	 *
	 * @param   integer  $user_id
	 * @return  Model_Gallery[]
	 */
	public function find_by_user($user_id) {
		$query_gallery = DB::select('gallery_id')
			->from('galleries_images')
			->join('images', 'INNER')
			->on('images.id', '=', 'image_id')
			->where('images.author_id', '=', (int)$user_id);

		return $this->load(
			DB::select_array($this->fields())
				->where('id', 'IN', $query_gallery)
				->order_by('date', 'DESC'),
			null);
	}


	/**
	 * Find galleries with latest images
	 *
	 * @param   integer  $limit
	 * @return  Model_Gallery[]
	 */
	public function find_latest($limit = 15) {
		return $this->load(
			DB::select_array($this->fields())
				->where('image_count', '>', 0)
				->and_where('updated', 'IS NOT', null)
				->order_by('updated', 'DESC'),
			$limit
		);
	}


	/**
	 * Get months with galleries.
	 * Returns array of years => months => count
	 *
	 * @return  array
	 */
	public function find_months() {
		$months = array();

		// Build counts
		$galleries = $this->load(
			DB::select_array($this->fields())
				->where('image_count', '>', 0),
			null
		);
		foreach ($galleries as $gallery) {
			list($year, $month) = explode(' ', date('Y n', $gallery->date));

			if (!isset($months[$year])) {
				$months[$year] = array();
			}
			if (!isset($months[$year][$month])) {
				$months[$year][$month] = 1;
			} else {
				$months[$year][$month]++;
			}
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
			case self::PERMISSION_UPDATE:
		    return $user && $user->has_role(array('admin', 'photo moderator'));

			case self::PERMISSION_DELETE:
		    return $user && $user->has_role('admin');

			case self::PERMISSION_COMMENT:
			case self::PERMISSION_COMMENTS:
			case self::PERMISSION_CREATE:
			case self::PERMISSION_UPLOAD:
		    return (bool)$user;

			case self::PERMISSION_READ:
		    return true;
		}

		return false;
	}


	/**
	 * Get visible gallery images
	 *
	 * @return  Model_Image[]
	 */
	public function images() {
		return $this->find_related(
			'images',
			DB::select_array(Model_Image::factory()->fields())
				->join('users', 'LEFT')
				->on('users.id', '=', 'images.author_id')
				->where('images.status', '=', Model_Image::VISIBLE)
				->order_by('users.username', 'ASC')
				->order_by('images.id', 'ASC'),
			null
		);
	}


	/**
	 * Update copyright info
	 *
	 * @return  Model_Gallery
	 */
	public function update_copyright() {
		$copyrights = $authors = array();

		// Load author ids
		foreach ($this->images() as $image) {
			if ($image->author_id) {
				$authors[$image->author_id] = '';
			}
		}

		// Load usernames
		foreach ($authors as $author_id => $author) {
			if ($author = Model_User::find_user($author_id)) {
				$copyrights[$author->username_clean] = $author->username;
			}
		}

		ksort($copyrights);
		$this->copyright = implode(', ', $copyrights);

		return $this;
	}

}

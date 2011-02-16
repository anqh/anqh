<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Gallery model
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Gallery extends Jelly_Model implements Permission_Interface {

	/**
	 * Permission to approve images
	 */
	const PERMISSION_APPROVE = 'approve';

	/**
	 * Permission to see images waiting for approval
	 */
	const PERMISSION_APPROVE_WAITING = 'approve_waiting';

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

	/**
	 * @var  array  User editable fields
	 */
	public static $editable_fields = array(
		'name', 'event'
	);


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id' => new Jelly_Field_Primary,
			'name' => new Jelly_Field_String(array(
				'label' => __('Name'),
				'rules' => array(
					'not_empty'  => null,
					'min_length' => array(3),
					'max_length' => array(250),
				),
			)),
			'links' => new Jelly_Field_Text,
			'created' => new Jelly_Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'modified' => new Jelly_Field_Timestamp(array(
				'column' => 'updated',
			)),
			'image_count' => new Jelly_Field_Integer,
			'comment_count' => new Jelly_Field_Integer,
			'rate_count' => new Jelly_Field_Integer,
			'rate_total' => new Jelly_Field_Integer,

			'date' => new Jelly_Field_Timestamp(array(
				'rules' => array(
					'not_empty' => null,
				),
			)),
			'event' => new Jelly_Field_BelongsTo(array(
				'allow_null'  => true,
				'empty_value' => null,
			)),

			'default_image' => new Jelly_Field_BelongsTo(array(
				'column'      => 'default_image_id',
				'foreign'     => 'image',
				'allow_null'  => true,
				'empty_value' => null,
			)),
			'images' => new Jelly_Field_ManyToMany,

			'copyright' => new Jelly_Field_String,
			'dir' => new Jelly_Field_String,
			'mainfile' => new Jelly_Field_String,
		));
	}


	public static function find($gallery_id) {
		return Jelly::query('gallery')
			->with('event')
			->with('default_image')
			->where('id', '=', $gallery_id)
			->limit(1)
			->select();
	}
	/**
	 * Find gallery by event id
	 *
	 * @param   integer  $event_id
	 * @return  Model_Gallery
	 */
	public static function find_by_event($event_id) {
		return Jelly::query('gallery')
			->where('event_id', '=', (int)$event_id)
			->limit(1)
			->select();
	}


	/**
	 * Find gallery by image id
	 *
	 * @param   integer  $image_id
	 * @return  Model_Gallery
	 */
	public static function find_by_image($image_id) {
		return Jelly::query('gallery')
			->join('galleries_images')
			->on('gallery.:primary_key', '=', 'galleries_images.gallery:foreign_key')
			->where('image_id', '=', (int)$image_id)
			->limit(1)
			->select();
	}


	/**
	 * Find multiple galleries by image ids
	 *
	 * @param   array  $image_ids
	 * @return  Jelly_Collection
	 */
	public static function find_by_images($image_ids) {
		return Jelly::query('gallery')
			->join('galleries_images')
			->on('gallery.:primary_key', '=', 'galleries_images.gallery:foreign_key')
			->where('image_id', 'IN', $image_ids)
			->select();
	}


	/**
	 * Find galleries by year and month
	 *
	 * @static
	 * @param   integer  $year
	 * @param   integer  $month
	 * @return  Jelly_Collection
	 */
	public static function find_by_month($year, $month) {
		return Jelly::query('gallery')
			->year_month($year, $month)
			->select();
	}


	/**
	 * Get visible gallery images
	 *
	 * @return  Jelly_Collection
	 */
	public function find_images() {
		return Jelly::query('image')
			->with('exif')
			->with('author')
			->join('galleries_images')
			->on('galleries_images.image_id', '=', 'image.id')
			->where('galleries_images.gallery_id', '=', $this->id)
			->and_where('image.status', '=', Model_Image::VISIBLE)
			->order_by('image:author.username', 'ASC')
			->order_by('images.id', 'ASC')
			->select();
		return $this
			->get('images')
			->with('author')
			->where('status', '=', Model_Image::VISIBLE)
			->order_by('username', 'ASC')
			->order_by('images.id', 'ASC')
			->select();
	}


	/**
	 * Get gallery images waiting for approval
	 *
	 * @param   Model_User  $user  image owner or null for all
	 * @return  Jelly_Collection
	 */
	public function find_images_pending(Model_User $user = null) {
		$images = $this->get('images')
			->where('status', 'IN', array(Model_Image::HIDDEN, Model_Image::NOT_ACCEPTED))
			->order_by('author_id');

		if ($user) {
			$images->and_where('author_id', '=', $user->id);
		}

		return $images->select();
	}


	/**
	 * Find galleries with latest images
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 */
	public static function find_latest($limit = 15) {
		return Jelly::query('gallery')
			->with('default_image')
			->latest()
			->limit((int)$limit)
			->select();
	}


	/**
	 * Get months with galleries.
	 * Returns array of years => months => count
	 *
	 * @static
	 * @return  array
	 */
	public static function find_months() {
		$months = array();

		// Build counts
		$galleries = Jelly::query('gallery')
			->where('image_count', '>', 0)
			->select();
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
	 * Get galleries with images waiting for approval
	 *
	 * @static
	 * @param   Model_User  $user  Null for all
	 * @return  Jelly_Collection
	 */
	public static function find_pending(Model_User $user = null) {
		$galleries = DB::select('gallery_id')
			->distinct(true)
			->from('galleries_images')
			->join('images', 'INNER')
			->on('images.id', '=', 'image_id')
			->where('images.status', 'IN', array(Model_Image::NOT_ACCEPTED, Model_Image::HIDDEN))
			->order_by('gallery_id', 'ASC');

		// If checking only one user
		if ($user) {
			$galleries->where('author_id', '=', $user->id);
		}

		return Jelly::query('gallery')
			->where('id', 'IN', $galleries)
			->select();
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
			case self::PERMISSION_APPROVE:
			case self::PERMISSION_UPDATE:
		    return $user && $user->has_role(array('admin', 'photo moderator'));

			case self::PERMISSION_APPROVE_WAITING:
		    return $user && $user->has_role(array('photo', 'admin', 'photo moderator'));

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
	 * Update copyright info
	 *
	 * @return  Model_Gallery
	 */
	public function update_copyright() {
		$copyrights = array();
		$authors    = $this->get('images')->select('author_id');
		$copyright  = Jelly::query('user')
			->where('id', 'IN', $authors)
			->select();
		foreach ($copyright as $author) $copyrights[$author->username_clean] = $author->username;
		ksort($copyrights);
		$this->copyright = implode(', ', $copyrights);

		return $this;
	}

}

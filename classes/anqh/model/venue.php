<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Venue model
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Venue extends Jelly_Model implements Permission_Interface {

	/**
	 * Permission to combine duplicate venues
	 */
	const PERMISSION_COMBINE = 'combine';

	/**
	 * @var  array  User editable fields
	 */
	public static $editable_fields = array(
		'category', 'name', 'description', 'homepage', 'hours', 'info', 'address', 'zip', 'city_name', 'city', 'latitude', 'longitude', 'event_host', 'tags',
	);


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->sorting(array('city_name' => 'ASC', 'name' => 'ASC'))
			->fields(array(
				'id' => new Field_Primary,
				'category' => new Field_BelongsTo(array(
					'label'   => 'Category',
					'foreign' => 'venue_category',
				)),
				'name' => new Field_String(array(
					'label' => __('Venue'),
					'rules' => array(
						'not_empty'  => null,
						'max_length' => array(32),
					),
				)),
				'description' => new Field_String(array(
					'label' => __('Short description'),
					'rules' => array(
						'max_length' => array(250),
					),
				)),
				'homepage' => new Field_URL(array(
					'label' => 'Homepage',
				)),
				'hours' => new Field_Text(array(
					'label' => __('Opening hours'),
					'rules' => array(
						'max_length' => array(250),
					),
				)),
				'info' => new Field_Text(array(
					'label' => __('Other information'),
					'rules' => array(
						'max_length' => array(512),
					),
				)),

				'address' => new Field_String(array(
					'label' => __('Street address'),
					'rules' => array(
						'max_length' => array(50),
					),
				)),
				'zip' => new Field_String(array(
					'label' => __('Zip code'),
					'rules' => array(
						'min_length' => array(4),
						'max_length' => array(5),
						'digit'      => null,
					),
				)),
				'city_name'  => new Field_String(array(
					'label' => __('City'),
					'rules' => array(
						'not_empty'  => null,
					),
				)),
				'city'       => new Field_BelongsTo(array(
					'foreign' => 'geo_city',
				)),
				'country' => new Field_BelongsTo(array(
					'foreign' => 'geo_country',
					'null'    => true,
				)),

				'latitude'   => new Field_Float,
				'longitude'  => new Field_Float,
				'event_host' => new Field_Boolean(array(
					'label' => __('Event host'),
				)),
				'created'    => new Field_Timestamp(array(
					'auto_now_create' => true,
				)),
				'modified'   => new Field_Timestamp(array(
					'auto_now_update' => true,
				)),

				'foursquare_id'          => new Field_Integer,
				'foursquare_category_id' => new Field_Integer,

				'author' => new Field_BelongsTo(array(
					'column'  => 'author_id',
					'foreign' => 'user',
				)),
				'default_image' => new Field_BelongsTo(array(
					'column'  => 'default_image_id',
					'foreign' => 'image',
				)),
				'images' => new Field_ManyToMany,
				'tags'   => new Field_ManyToMany(array(
					'label' => __('Tags'),
					'null'  => true,
				)),
				'events' => new Field_HasMany,
		));
	}


	/**
	 * Find all venues sorted by city and category
	 *
	 * @static
	 * @return  Jelly_Collection
	 */
	public static function find_all() {
		return Jelly::select('venue')
			->with('venue_category')
			->order_by('city_name', 'ASC')
			->order_by('name', 'ASC')
			->execute();
	}


	/**
	 * Find single venue by Foursquare id
	 *
	 * @static
	 * @param   integer  $foursquare_id
	 * @return  Model_Venue
	 */
	public static function find_by_foursquare($foursquare_id) {
		return Jelly::select('venue')->where('foursquare_id', '=', (int)$foursquare_id)->limit(1)->execute();
	}


	/**
	 * Find multiple venues by name
	 *
	 * @static
	 * @param   string  $name
	 * @return  Jelly_Collection
	 */
	public static function find_by_name($name) {
		return Jelly::select('venue')->where('name', 'ILIKE', trim($name))->execute();
	}


	/**
	 * Find new venues
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 */
	public static function find_new($limit = 20) {
		return Jelly::select('venue')->order_by('id', 'DESC')->limit((int)$limit)->execute();
	}


	/**
	 * Find updated venues
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 */
	public static function find_updated($limit = 20) {
		return Jelly::select('venue')->where('modified', 'IS NOT', null)->order_by('modified', 'DESC')->limit((int)$limit)->execute();
	}


	/**
	 * Check permission
	 *
	 * @param   string      $permission
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function has_permission($permission, $user) {
		$status = false;

		switch ($permission) {
			case self::PERMISSION_CREATE:
		    $status = $user && $user->loaded();
		    break;

			case self::PERMISSION_COMBINE:
			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
		    $status = $user && $user->has_role('admin', 'venue moderator');
		    break;

			case self::PERMISSION_READ:
		    $status = true;
		}

		return $status;
	}

}

<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Event model
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Event extends Jelly_Model implements Permission_Interface {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id'          => new Field_Primary,
			'name'        => new Field_String(array(
				'rules' => array(
					'not_empty'  => array(true),
					'min_length' => array(3),
					'max_length' => array(100),
				),
			)),
			'title'       => new Field_String,
			'homepage'    => new Field_URL,
			'stamp_begin' => new Field_Timestamp,
			'stamp_end'   => new Field_Timestamp,
			'venue_name'  => new Field_String(array(
				'rules' => array(
					'not_empty' => array(true),
				),
			)),
			'venue'       => new Field_BelongsTo,
			'venue_url'   => new Field_URL,
			'city_name'   => new Field_String,
			'city'        => new Field_BelongsTo(array(
				'foreign' => 'geo_city',
			)),
			'country'     => new Field_BelongsTo(array(
				'foreign' => 'geo_country',
			)),

			'dj'          => new Field_Text,
			'info'        => new Field_Text,
			'age'         => new Field_Integer(array(
				'rules' => array(
					'range' => array(0, 99),
				)
			)),
			'price'       => new Field_Float,
			'price2'      => new Field_Float,
			'music'       => new Field_Text,

			'created'     => new Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'modified'    => new Field_Timestamp(array(
				'auto_now_update' => true,
			)),
			'author'      => new Field_BelongsTo(array(
				'column'  => 'author_id',
				'foreign' => 'user',
			)),

			'num_modifies' => new Field_Integer(array(
				'column' => 'modifies',
			)),
			'num_views'    => new Field_Integer(array(
				'column' => 'views',
			)),

			'flyer_front' => new Field_BelongsTo(array(
				'column'  => 'flyer_front_image_id',
				'foreign' => 'image',
			)),
			'flyer_back'  => new Field_BelongsTo(array(
				'column'  => 'flyer_back_image_id',
				'foreign' => 'image',
			)),
			'tags'        => new Field_ManyToMany,
			'images'      => new Field_ManyToMany,
			'favorites'   => new Field_HasMany(array(
				'through' => 'users',
			))
		));
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
		    $status = (bool)$user;
		    break;

			case self::PERMISSION_DELETE:
			case self::PERMISSION_READ:
			case self::PERMISSION_UPDATE:
		}

		return $status;
	}

}

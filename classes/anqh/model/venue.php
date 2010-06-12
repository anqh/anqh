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
				'homepage' => new Field_URL,
				'hours' => new Field_String(array(
					'label' => __('Opening hours'),
					'rules' => array(
						'max_length' => array(250),
					),
				)),
				'info' => new Field_String(array(
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
				'city_name'  => new Field_String,
				'latitude'   => new Field_Float,
				'longitude'  => new Field_Float,
				'event_host' => new Field_Boolean,

				'default_image' => new Field_BelongsTo(array(
					'column'  => 'default_image_id',
					'foreign' => 'image',
				)),
				'images' => new Field_ManyToMany,
				'tags'   => new Field_ManyToMany,
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
		    $status = $user && $user->loaded();
		    break;

			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
		    $status = $user && $user->has_role('admin');
		    break;

			case self::PERMISSION_READ:
		    $status = true;
		}

		return $status;
	}

}

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
	 * Permission to add favorite
	 */
	const PERMISSION_FAVORITE = 'favorite';

	/**
	 * @var  array  User editable fields
	 */
	public static $editable_fields = array(
		'name', 'homepage', 'stamp_begin', 'stamp_end', 'date_begin', 'time_begin', 'time_end', 'venue', 'venue_name', 'city', 'city_name', 'age', 'price', 'price2', 'dj', 'info', 'tags'
	);

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'id'          => new Field_Primary,
			'name'        => new Field_String(array(
				'label' => __('Name'),
				'rules' => array(
					'not_empty'  => array(true),
					'min_length' => array(3),
					'max_length' => array(100),
				),
			)),
			'title'       => new Field_String,
			'homepage'    => new Field_URL(array(
				'label' => __('Homepage'),
			)),
			'stamp_begin' => new Field_Timestamp,
			'stamp_end'   => new Field_Timestamp,
			'date_begin'  => new Field_Timestamp(array(
				'in_db' => false,
				'rules' => array(
					'not_empty' => null,
					'date'      => null,
				),
				'pretty_format' => 'j.n.Y',
			)),
			'time_begin'  => new Field_String(array(
				'in_db' => false,
				'rules' => array(
					'time'      => null,
					'not_empty' => null,
				)
			)),
			'time_end'    => new Field_String(array(
				'in_db' => false,
				'rules' => array(
					'time' => null,
				)
			)),
			'venue'       => new Field_BelongsTo,
			'venue_name'  => new Field_String(array(
				'label' => __('Venue'),
			)),
			'venue_url'   => new Field_URL,
			'city'        => new Field_BelongsTo(array(
				'foreign' => 'geo_city',
			)),
			'city_name'   => new Field_String(array(
				'label' => __('City'),
				'rules' => array(
					'not_empty' => null,
				),
			)),
			'country'     => new Field_BelongsTo(array(
				'foreign' => 'geo_country',
			)),

			'dj'          => new Field_Text(array(
				'label' => __('Performers'),
			)),
			'info'        => new Field_Text(array(
				'label' => __('Other information'),
			)),
			'age'         => new Field_Integer(array(
				'label' => __('Age limit'),
				'null'  => true,
				'rules' => array(
					'range' => array(0, 99),
				)
			)),
			'price'       => new Field_Float(array(
				'label' => __('At the door'),
				'null'  => true,
			)),
			'price2'      => new Field_Float(array(
				'label' => __('Presale'),
				'null'  => true,
			)),
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
			'favorites'   => new Field_ManyToMany(array(
				'foreign' => 'user',
				'through' => 'favorites',
			)),
		));
	}


	/**
	 * Create favorite
	 *
	 * @param  Model_User  $user
	 */
	public function add_favorite(Model_User $user) {
		return $this->loaded()
			&& !$this->is_favorite($user)
			&& (bool)Jelly::factory('favorite')->set(array(
				'user'  => $user,
				'event' => $this
			))->save();
	}


	/**
	 * Delete favorite
	 *
	 * @param  Model_User  $user
	 */
	public function delete_favorite(Model_User $user) {
		return $this->loaded()
			&& $this->is_favorite($user)
			&& (bool)Jelly::delete('favorite')
				->where('user_id', '=', $user->id)
				->where('event_id', '=', $this->id)
				->execute();
	}


	/**
	 * Get users who have added event as their favorite
	 *
	 * @return  array
	 */
	public function find_favorites() {
		static $favorites;

		if (!is_array($favorites)) {
			$favorites = array();
			if ($this->loaded()) {
				$users = DB::select('user_id')->from('favorites')->where('event_id', '=', $this->id)->execute();
				foreach ($users as $user) {
					$favorites[(int)$user['user_id']] = (int)$user['user_id'];
				}
			}
		}

		return $favorites;
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
			case self::PERMISSION_FAVORITE:
		    return (bool)$user;
		    break;

			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
		    return $user && ($this->author->id == $user->id || $user->has_role('admin'));

			case self::PERMISSION_READ:
				return true;
		}

		return false;
	}


	/**
	 * Check for favorite
	 *
	 * @param  Model_User|integer  $user  id, User_Model
	 */
	public function is_favorite($user) {
		if (empty($user)) {
			return false;
		}

		if ($user instanceof Model_User) {
			$user = $user->id;
		}

		$favorites = $this->find_favorites();

		return isset($favorites[(int)$user]);
	}

}

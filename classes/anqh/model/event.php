<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Event model
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
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
		'name', 'homepage', 'stamp_begin', 'stamp_end', 'venue', 'venue_name', 'city', 'city_name', 'age', 'price', 'price2', 'dj', 'info', 'tags'
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
					'not_empty'  => array(true),
					'min_length' => array(3),
					'max_length' => array(64),
				),
			)),
			'title' => new Jelly_Field_String,
			'homepage' => new Jelly_Field_URL(array(
				'label' => __('Homepage'),
			)),
			'stamp_begin' => new Jelly_Field_DateTime(array(
				'label'      => __('From'),
				'label_date' => __('Date'),
				'label_time' => '',
				'rules'      => array(
					'not_empty' => null,
				),
			)),
			'stamp_end' => new Jelly_Field_DateTime(array(
				'label'      => __('To'),
				'label_time' => '-',
				'show_date'  => false,
				'rules'      => array(
					'not_empty' => null,
				),
			)),
			'venue' => new Jelly_Field_BelongsTo(array(
				'allow_null' => true,
				'empty_value' => null,
			)),
			'venue_name' => new Jelly_Field_String(array(
				'label' => __('Venue'),
			)),
			'venue_url' => new Jelly_Field_URL,
			'city' => new Jelly_Field_BelongsTo(array(
				'foreign'     => 'geo_city',
				'allow_null'  => true,
				'empty_value' => null,
			)),
			'city_name' => new Jelly_Field_String(array(
				'label' => __('City'),
				'rules' => array(
					'not_empty' => null,
				),
			)),
			'country' => new Jelly_Field_BelongsTo(array(
				'foreign'     => 'geo_country',
				'allow_null'  => true,
				'empty_value' => null,
			)),
			'venue_hidden' => new Jelly_Field_Boolean,

			'dj' => new Jelly_Field_Text(array(
				'label' => __('Performers'),
			)),
			'info' => new Jelly_Field_Text(array(
				'label'  => __('Other information'),
				'bbcode' => true,
			)),
			'age' => new Jelly_Field_Integer(array(
				'label' => __('Age limit'),
				'null'  => true,
				'rules' => array(
					'range' => array(0, 99),
				)
			)),
			'price' => new Jelly_Field_Float(array(
				'label' => __('Tickets'),
				'null'  => true,
			)),
			'price2' => new Jelly_Field_Float(array(
				'label'      => __('Presale tickets'),
				'allow_null' => true,
			)),
			'music' => new Jelly_Field_Text,

			'created' => new Jelly_Field_Timestamp(array(
				'auto_now_create' => true,
			)),
			'modified' => new Jelly_Field_Timestamp(array(
				'auto_now_update' => true,
			)),
			'author' => new Jelly_Field_BelongsTo(array(
				'column'  => 'author_id',
				'foreign' => 'user',
			)),

			'num_modifies' => new Jelly_Field_Integer(array(
				'column' => 'modifies',
			)),
			'num_views' => new Jelly_Field_Integer(array(
				'column' => 'views',
			)),

			'flyer_front_url' => new Jelly_Field_Url(array(
				'label' => __('Flyer front')
			)),
			'flyer_back_url' => new Jelly_Field_Url(array(
				'label' => __('Flyer back')
			)),
			'flyer_front' => new Jelly_Field_BelongsTo(array(
				'column'      => 'flyer_front_image_id',
				'foreign'     => 'image',
				'allow_null'  => true,
				'empty_value' => null,
			)),
			'flyer_back' => new Jelly_Field_BelongsTo(array(
				'column'      => 'flyer_back_image_id',
				'foreign'     => 'image',
				'allow_null'  => true,
				'empty_value' => null,
			)),
			'tags' => new Jelly_Field_ManyToMany(array(
				'label' => __('Music'),
			)),
			'flyers' => new Jelly_Field_ManyToMany(array(
				'foreign' => 'image',
				'through' => array(
					'model'   => 'flyer',
					'columns' => array('event_id', 'image_id')
				),
			)),
			'images' => new Jelly_Field_ManyToMany,
			'favorites' => new Jelly_Field_ManyToMany(array(
				'foreign' => 'user',
				'through' => array(
					'model'   => 'favorite',
					'columns' => array('event_id', 'user_id')
				),
			)),
			'favorite_count' => new Jelly_Field_Integer,
		));
	}


	/**
	 * Create favorite
	 *
	 * @param  Model_User  $user
	 */
	public function add_favorite(Model_User $user) {
		if ($this->loaded()
			&& !$this->is_favorite($user)
			&& (bool)Model_Favorite::factory()->set(array(
				'user'  => $user,
				'event' => $this
			))->save()) {
			$this->favorite_count++;
			$this->save();

			return true;
		}

		return false;
	}


	/**
	 * Add flyer to event
	 *
	 * @param   Model_Image  $image
	 * @return  bool
	 */
	public function add_flyer(Model_Image $image) {
		return $this->loaded() && Model_Flyer::factory()
			->set(array(
				'image' => $image,
				'event' => $this
			))->save();
	}


	/**
	 * Delete favorite
	 *
	 * @param  Model_User  $user
	 */
	public function delete_favorite(Model_User $user) {
		if ($this->loaded()
			&& $this->is_favorite($user)
			&& (bool)Jelly::query('favorite')
				->where('user_id', '=', $user->id)
				->where('event_id', '=', $this->id)
				->delete()) {
			$this->favorite_count--;
			$this->save();

			return true;
		}

		return false;
	}


	/**
	 * Load event with venue and flyers
	 *
	 * @static
	 * @param   integer  $event_id
	 * @return  Model_Event
	 */
	public static function find($event_id) {
		return Jelly::query('event', $event_id)
			->with('venue')
			->with('flyer_front')
			->with('flyer_back')
			->select();
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
				$users = DB::select('user_id')
					->from('favorites')
					->where('event_id', '=', $this->id)
					->execute();
				foreach ($users as $user) {
					$favorites[(int)$user['user_id']] = (int)$user['user_id'];
				}
			}
		}

		return $favorites;
	}


	/**
	 * Find events between given time period, return grouped by date
	 *
	 * @static
	 * @param   integer  $from  From timestamp
	 * @param   integer  $to    To timestamp
	 * @return  array
	 */
	public static function find_grouped_between($from, $to) {
		return Jelly::query('event')
			->between($from, $to, 'ASC')
			->execute_grouped();
	}


	/**
	 * Find past events, return grouped by date
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  array
	 */
	public static function find_grouped_past($limit = 10) {
		return Jelly::query('event')
			->with('venue')
			->past()
			->limit($limit)
			->execute_grouped();
	}


	/**
	 * Find upcoming events, return grouped by date
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  array
	 */
	public static function find_grouped_upcoming($limit = 10) {
		return Jelly::query('event')
			->upcoming()
			->limit($limit)
			->execute_grouped();
	}


	/**
	 * Find hot (=favorites) events
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 */
	public static function find_hot($limit = 20) {
		return Jelly::query('event')
			->where('stamp_begin', '>', time())
			->and_where('favorite_count', '>', 0)
			->order_by('favorite_count', 'DESC')
			->limit($limit)
			->select();
	}


	/**
	 * Find last modified events
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 */
	public static function find_modified($limit = 20) {
		return Jelly::query('event')
			->where('modified', 'IS NOT', null)
			->order_by('modified', 'DESC')
			->limit(20)
			->select();
	}


	/**
	 * Find new events
	 *
	 * @static
	 * @param   integer  $limit
	 * @return  Jelly_Collection
	 */
	public static function find_new($limit = 20) {
		return Jelly::query('event')
			->order_by('id', 'DESC')
			->limit($limit)
			->select();
	}


	/**
	 * Get forum topic like string
	 * [name] [date] @ [city]
	 *
	 * @return  string
	 */
	public function get_forum_topic() {
		$topic = $this->name . ' ' . Date::format(Date::DMY_SHORT, $this->stamp_begin);
	  if ($this->city_name) {
		  $topic .= ' @ ' . $this->city_name;
	  }

	  return $topic;
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
		    return $user && ($this->author->id == $user->id || $user->has_role('admin', 'event moderator'));

			case self::PERMISSION_READ:
				return true;
		}

		return false;
	}


	/**
	 * Remove duplicate venues
	 *
	 * @static
	 * @param   integer  $venue_id
	 * @param   integer  $duplicate_id
	 * @return  integer  Update count
	 */
	public static function merge_venues($venue_id, $duplicate_id) {
		return DB::update('events')
			->set(array('venue_id' => $venue_id))
			->where('venue_id', '=', $duplicate_id)
			->execute();
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
